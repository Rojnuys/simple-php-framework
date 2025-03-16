<?php

namespace App\Shortener;

use App\Shortener\DTO\UrlCodePairCreateDTO;
use App\Shortener\Entities\UrlCodePair;
use App\Shortener\Exceptions\CodeAlreadyExistException;
use App\Shortener\Exceptions\UrlCodePairDoesNotExistException;
use App\Shortener\Interfaces\ICodeGenerator;
use App\Shortener\Interfaces\IUrlCodePairRepository;
use App\Shortener\Interfaces\IUrlDecoder;
use App\Shortener\Interfaces\IUrlEncoder;
use App\Shortener\Interfaces\IUrlParser;
use App\Shortener\Interfaces\IUrlValidator;

class Shortener implements IUrlEncoder, IUrlDecoder
{
    protected const int NUMBER_OF_ATTEMPTS_TO_CREATE_CODE = 5;
    protected const int DEFAULT_CODE_LENGTH = 6;

    public function __construct(
        protected IUrlCodePairRepository $repository,
        protected IUrlValidator $urlValidator,
        protected IUrlParser $urlParser,
        protected ICodeGenerator $codeGenerator,
        protected int $codeLength = self::DEFAULT_CODE_LENGTH
    )
    {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function decode(string $code): string
    {
        try {
            $urlCodePair = $this->repository->getByCode($code);
            $urlCodePair->increaseCount();
            $this->repository->update($urlCodePair);
            return $urlCodePair->getUrl();
        } catch (UrlCodePairDoesNotExistException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function encode(string $url): string
    {
        $this->urlValidator->checkFormat($url);
        $this->urlValidator->checkAvailability($url);
        $url = $this->urlParser->parse($url);

        try {
            return $this->repository->getByUrl($url)->getCode();
        } catch (UrlCodePairDoesNotExistException) {
            return $this->createUrlCodePair($url)->getCode();
        }
    }

    protected function createUrlCodePair(string $url, int $attempt = 1): UrlCodePair
    {
        if ($attempt > static::NUMBER_OF_ATTEMPTS_TO_CREATE_CODE) {
            throw new \InvalidArgumentException('The service cannot create a code. Please try again.');
        }

        try {
            $code = $this->codeGenerator->generate($this->codeLength);
            return $this->repository->create(UrlCodePairCreateDTO::fromArray(['url' => $url, 'code' => $code]));
        } catch (CodeAlreadyExistException) {
            return $this->createUrlCodePair($url, $attempt + 1);
        }
    }
}
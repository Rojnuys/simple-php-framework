<?php

declare(strict_types=1);

namespace App\Shortener\Repositories;

use App\Shared\FileSystem\File\Exceptions\ReadFileException;
use App\Shared\FileSystem\File\Exceptions\WriteFileException;
use App\Shared\FileSystem\File\Interfaces\IFileReader;
use App\Shared\FileSystem\File\Interfaces\IFileWriter;
use App\Shortener\DTO\UrlCodePairCreateDTO;
use App\Shortener\Entities\UrlCodePair;
use App\Shortener\Exceptions\CodeAlreadyExistException;
use App\Shortener\Exceptions\UrlCodePairDoesNotExistException;
use App\Shortener\Interfaces\IUrlCodePairRepository;
use Generator;

class FileUrlCodePairRepository implements IUrlCodePairRepository
{
    public function __construct(protected IFileReader $fileReader, protected IFileWriter $fileWriter)
    {
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     * @throws ReadFileException
     */
    public function getByUrl(string $url): UrlCodePair
    {
        return $this->getFirstUrlCodePairByCondition(function (UrlCodePair $urlCodePair) use ($url) {
            return $urlCodePair->getUrl() === $url;
        });
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     * @throws ReadFileException
     */
    public function getByCode(string $code): UrlCodePair
    {
         return $this->getFirstUrlCodePairByCondition(function (UrlCodePair $urlCodePair) use ($code) {
            return $urlCodePair->getCode() === $code;
        });
    }

    /**
     * @throws CodeAlreadyExistException
     * @throws ReadFileException
     * @throws WriteFileException
     */
    public function create(UrlCodePairCreateDTO $dto): UrlCodePair
    {
        if ($this->isCodeAlreadyExists($dto->code)) {
            throw new CodeAlreadyExistException("The UrlCodePair with code {$dto->code} already exists.");
        }

        $newUrlCodePair = new UrlCodePair(uniqid('ucp_'), $dto->url, $dto->code);
        $this->fileWriter->append($this->formatUrlCodePair($newUrlCodePair));

        return $newUrlCodePair;
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     * @throws WriteFileException
     * @throws ReadFileException
     */
    public function update(UrlCodePair $urlCodePair): void
    {
        $updatedFile = '';
        $isExist = false;

        foreach ($this->getUrlCodePairGenerator() as $ucp) {
            if ($ucp->getId() === $urlCodePair->getId()) {
                $updatedFile .= $this->formatUrlCodePair($urlCodePair);
                $isExist = true;
                continue;
            }

            $updatedFile .= $this->formatUrlCodePair($ucp);
        }

        if (!$isExist) {
            throw new UrlCodePairDoesNotExistException();
        }

        $this->fileWriter->rewrite($updatedFile);
    }

    /**
     * @throws ReadFileException
     */
    protected function isCodeAlreadyExists(string $code): bool
    {
        try {
            $this->getByCode($code);
            return true;
        } catch (UrlCodePairDoesNotExistException) {
            return false;
        }
    }

    /**
     * @throws ReadFileException
     */
    protected function getUrlCodePairGenerator(): Generator
    {
        return $this->fileReader->readByLine(function (string $line) {
            return unserialize(trim($line));
        });
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     * @throws ReadFileException
     */
    protected function getFirstUrlCodePairByCondition(callable $conditionClb): UrlCodePair
    {
        /**
         * @var UrlCodePair $codePair
         */
        foreach ($this->getUrlCodePairGenerator() as $codePair) {
            if ($conditionClb($codePair)) {
                return $codePair;
            }
        }

        throw new UrlCodePairDoesNotExistException();
    }

    protected function formatUrlCodePair(UrlCodePair $urlCodePair): string
    {
        return serialize($urlCodePair) . PHP_EOL;
    }

    /**
     * @throws ReadFileException
     */
    public function getAll(): \Generator
    {
        return $this->getUrlCodePairGenerator();
    }
}
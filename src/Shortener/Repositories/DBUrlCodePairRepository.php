<?php

namespace App\Shortener\Repositories;

use App\Shortener\DTO\UrlCodePairCreateDTO;
use App\Shortener\Exceptions\UrlCodePairDoesNotExistException;
use App\Shortener\Interfaces\IUrlCodePairRepository;
use App\Shortener\Models\UrlCodePair;
use App\Shortener\Entities\UrlCodePair as UrlCode;

class DBUrlCodePairRepository implements IUrlCodePairRepository
{
    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function getByCode(string $code): UrlCode
    {
        $urlCodePair = UrlCodePair::getByCode($code);

        return new UrlCode(
            $urlCodePair->id,
            $urlCodePair->url,
            $urlCodePair->code,
            $urlCodePair->count,
        );
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function getByUrl(string $url): UrlCode
    {
        $urlCodePair = UrlCodePair::getByUrl($url);

        return new UrlCode(
            $urlCodePair->id,
            $urlCodePair->url,
            $urlCodePair->code,
            $urlCodePair->count,
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function create(UrlCodePairCreateDTO $dto): UrlCode
    {
        $urlCodePair = UrlCodePair::create($dto->url, $dto->code);

        return new UrlCode(
            $urlCodePair->id,
            $urlCodePair->url,
            $urlCodePair->code,
            $urlCodePair->count,
        );
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function update(\App\Shortener\Entities\UrlCodePair $urlCodePair): void
    {
        UrlCodePair::modify($urlCodePair);
    }

    public function getAll(): \Generator
    {
        $urlCodePairs = UrlCodePair::all();

        foreach ($urlCodePairs as $urlCodePair) {
            yield new UrlCode(
                $urlCodePair->id,
                $urlCodePair->url,
                $urlCodePair->code,
                $urlCodePair->count,
            );
        }
    }
}
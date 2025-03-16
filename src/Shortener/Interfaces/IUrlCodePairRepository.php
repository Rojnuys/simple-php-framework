<?php

declare(strict_types=1);

namespace App\Shortener\Interfaces;

use App\Shortener\DTO\UrlCodePairCreateDTO;
use App\Shortener\Entities\UrlCodePair;
use App\Shortener\Exceptions\CodeAlreadyExistException;
use App\Shortener\Exceptions\UrlCodePairDoesNotExistException;

interface IUrlCodePairRepository
{
    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function getByUrl(string $url): UrlCodePair;

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function getByCode(string $code): UrlCodePair;

    /**
     * @throws CodeAlreadyExistException
     */
    public function create(UrlCodePairCreateDTO $dto): UrlCodePair;

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public function update(UrlCodePair $urlCodePair): void;

    public function getAll(): \Generator;
}
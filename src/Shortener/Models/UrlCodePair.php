<?php

namespace App\Shortener\Models;

use App\Shortener\Exceptions\UrlCodePairDoesNotExistException;
use Illuminate\Database\Eloquent\Model;

class UrlCodePair extends Model
{
    protected $table = 'url_code_pair';
    public $timestamps = false;

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public static function getByUrl(string $url): self
    {
        $urlCode = self::where('url', $url)->first();
        if (is_null($urlCode)) {
            throw new UrlCodePairDoesNotExistException();
        }
        return $urlCode;
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public static function getByCode(string $code): self
    {
        $urlCode = self::where('code', $code)->first();
        if (is_null($urlCode)) {
            throw new UrlCodePairDoesNotExistException();
        }
        return $urlCode;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function create(string $url, string $code): self
    {
        $urlCode = new self();
        $urlCode->url = $url;
        $urlCode->code = $code;
        $urlCode->count = 0;
        $result = $urlCode->save();

        if (!$result) {
            throw new \InvalidArgumentException('This url or code already exists.');
        }

        return $urlCode;
    }

    /**
     * @throws UrlCodePairDoesNotExistException
     */
    public static function modify(\App\Shortener\Entities\UrlCodePair $urlCodePair): void
    {
        $urlCode = self::find($urlCodePair->getId());
        if (is_null($urlCode)) {
            throw new UrlCodePairDoesNotExistException();
        }
        $urlCode->url = $urlCodePair->getUrl();
        $urlCode->code = $urlCodePair->getCode();
        $urlCode->count = $urlCodePair->getCount();
        $urlCode->save();
    }
}
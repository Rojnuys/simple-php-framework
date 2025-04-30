<?php

namespace App\Controllers;

use App\Core\Framework\Attributes\Route;
use App\Core\Framework\Interfaces\IController;
use App\Core\Http\HttpRequest;
use App\Core\Http\JsonResponse;
use App\Core\Http\Redirect;
use App\Core\Http\ViewResponse;
use App\Shortener\Interfaces\IUrlCodePairRepository;
use App\Shortener\Interfaces\IUrlDecoder;
use App\Shortener\Interfaces\IUrlEncoder;
use Psr\Http\Message\ResponseInterface;

#[Route('/', methods: ['GET'])]
class UrlController implements IController
{
    #[Route('')]
    public function index(IUrlCodePairRepository $repository): ResponseInterface
    {
        return new ViewResponse('Shortener/index.php', ['urlCodePairs' => $repository->getAll()]);
    }

    #[Route('create')]
    public function create(): ResponseInterface
    {
        return new ViewResponse('Shortener/create.php');
    }

    #[Route('encode', methods: ['POST'])]
    public function encode(IUrlEncoder $encoder, HttpRequest $request): ResponseInterface
    {
        $url = trim($request->post('url'));

        if ($url === null) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Url must be provided'],
                400
            );
        } else {
            try {
                $code = $encoder->encode($url);
                $result = ['success' => true, 'code' => $code];
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(
                    ['success' => false, 'message' => $e->getMessage()],
                    400
                );
            }
        }

        return new JsonResponse($result);
    }

    #[Route('decode/<code>')]
    public function decode(string $code, IUrlDecoder $decoder): ResponseInterface
    {
        try {
            $url = $decoder->decode($code);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['success' => false, 'message' => 'There isn\'t a url code pair with code \'' . $code . '\''],
                404
            );
        }

        return new Redirect($url);
    }
}
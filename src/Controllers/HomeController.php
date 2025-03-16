<?php

namespace App\Controllers;

use App\Core\Framework\Attributes\Route;
use App\Core\Framework\Interfaces\IController;
use App\Core\Http\ViewResponse;
use App\Events\MyCustomEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

#[Route('/', methods: ['GET'])]
class HomeController implements IController
{
    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
    }

    #[Route('')]
    public function index(): ResponseInterface
    {
        return new ViewResponse('index.html');
    }

    #[Route('profile')]
    public function profile(): ResponseInterface
    {
        $event = new MyCustomEvent(10, 22);
        $this->eventDispatcher->dispatch($event);
        return new ViewResponse('profile.php', ['result' => $event->getResult()]);
    }
}
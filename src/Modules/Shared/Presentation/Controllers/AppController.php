<?php

declare(strict_types = 1);

namespace App\Modules\Shared\Presentation\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

abstract class AppController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function dispatch($command): JsonResponse
    {
        $envelope = $this->commandBus->dispatch($command);
        $response = $envelope->last(HandledStamp::class)->getResult();

        return $this->json($response);
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatchQuery($query): JsonResponse
    {
        $envelope = $this->queryBus->dispatch($query);
        $response = $envelope->last(HandledStamp::class)->getResult();

        return $this->json($response);
    }
}

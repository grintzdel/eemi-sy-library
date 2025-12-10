<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Controller;

use App\Modules\Shared\Domain\ValueObject\UserId;
use App\Modules\Shared\Presentation\Controllers\AppController;
use App\Modules\User\Application\Command\CreateUserCommand;
use App\Modules\User\Application\Query\GetAllUsersQuery;
use App\Modules\User\Application\Query\GetUserByIdQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
final class UserController extends AppController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        return $this->dispatchQuery(new GetAllUsersQuery());
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getUserById(string $id): JsonResponse
    {
        return $this->dispatchQuery(new GetUserByIdQuery(UserId::fromString($id)));
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        return $this->dispatch(CreateUserCommand::fromRequest($request));
    }
}

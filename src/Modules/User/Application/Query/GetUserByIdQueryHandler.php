<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

use App\Modules\User\Application\ViewModel\UserViewModel;
use App\Modules\User\Domain\Error\UserNotFoundError;
use App\Modules\User\Domain\Repository\IUserRepository;

readonly class GetUserByIdQueryHandler
{
    public function __construct(
        private IUserRepository $userRepository
    ) {
    }

    public function handle(GetUserByIdQuery $query): UserViewModel
    {
        $user = $this->userRepository->findById($query->id);

        if (!$user) {
            throw new UserNotFoundError();
        }

        return new UserViewModel(
            id: $user->getId(),
            name: $user->getName(),
            borrowedBooks: $user->getBorrowedBooks()
        );
    }
}

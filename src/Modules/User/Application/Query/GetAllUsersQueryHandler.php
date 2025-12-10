<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

use App\Modules\User\Application\ViewModel\UserViewModel;
use App\Modules\User\Domain\Repository\IUserRepository;

final readonly class GetAllUsersQueryHandler
{
    public function __construct(
        private IUserRepository $userRepository
    ) {
    }

    /**
     * @return UserViewModel[]
     */
    public function handle(GetAllUsersQuery $query): array
    {
        $users = $this->userRepository->findAll();

        return array_map(
            fn($user) => new UserViewModel(
                id: $user->getId(),
                name: $user->getName(),
                borrowedBooks: $user->getBorrowedBooks()
            ),
            $users
        );
    }
}

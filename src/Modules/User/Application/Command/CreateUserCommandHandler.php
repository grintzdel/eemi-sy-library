<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Command;

use App\Modules\User\Application\ViewModel\UserViewModel;
use App\Modules\User\Domain\Repository\IUserRepository;
use App\Modules\User\Infrastructure\Entity\UserEntity;

readonly class CreateUserCommandHandler
{
    public function __construct(
        private IUserRepository $userRepository
    ) {
    }

    public function handle(CreateUserCommand $command): UserViewModel
    {
        $user = new UserEntity();
        $user->setName($command->name);

        $this->userRepository->save($user);
        $this->userRepository->flush();

        return new UserViewModel(
            id: $user->getId(),
            name: $user->getName(),
            borrowedBooks: $user->getBorrowedBooks()
        );
    }
}

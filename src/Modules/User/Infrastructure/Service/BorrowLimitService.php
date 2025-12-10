<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Service;

use App\Modules\User\Domain\Error\BorrowLimitExceededError;
use App\Modules\User\Infrastructure\Entity\UserEntity;

final readonly class BorrowLimitService
{
    private const int MAX_BORROWED_BOOKS = 3;

    public function canBorrowBook(UserEntity $user): bool
    {
        return count($user->getBorrowedBooks()) < self::MAX_BORROWED_BOOKS;
    }

    /**
     * @throws BorrowLimitExceededError
     */
    public function ensureCanBorrowBook(UserEntity $user): void
    {
        if (!$this->canBorrowBook($user)) {
            throw new BorrowLimitExceededError($this->getMaximumBorrowLimit());
        }
    }

    public function getMaximumBorrowLimit(): int
    {
        return self::MAX_BORROWED_BOOKS;
    }

    public function getRemainingBorrowCapacity(UserEntity $user): int
    {
        return max(0, self::MAX_BORROWED_BOOKS - count($user->getBorrowedBooks()));
    }
}

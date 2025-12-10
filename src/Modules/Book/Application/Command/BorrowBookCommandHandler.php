<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

use App\Modules\Book\Application\ViewModel\SuccessViewModel;
use App\Modules\Book\Domain\Error\BookAlreadyBorrowedError;
use App\Modules\Book\Domain\Error\BookNotFoundError;
use App\Modules\Book\Domain\Repository\IBookRepository;
use App\Modules\User\Domain\Error\UserNotFoundError;
use App\Modules\User\Domain\Repository\IUserRepository;

final readonly class BorrowBookCommandHandler
{
    public function __construct(
        private IBookRepository $bookRepository,
        private IUserRepository $userRepository
    ) {
    }

    public function handle(BorrowBookCommand $command): SuccessViewModel
    {
        $book = $this->bookRepository->findByTitle($command->bookTitle);
        if (!$book) {
            throw new BookNotFoundError();
        }

        $user = $this->userRepository->findById($command->userId);
        if (!$user) {
            throw new UserNotFoundError();
        }

        if ($book->isBorrowed()) {
            throw new BookAlreadyBorrowedError();
        }

        $book->borrow();
        $user->addBorrowedBook($book);

        $this->bookRepository->flush();

        return new SuccessViewModel('Book borrowed successfully');
    }
}

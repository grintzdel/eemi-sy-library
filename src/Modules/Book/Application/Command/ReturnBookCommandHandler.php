<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

use App\Modules\Book\Application\ViewModel\SuccessViewModel;
use App\Modules\Book\Domain\Error\BookNotFoundError;
use App\Modules\Book\Domain\Repository\IBookRepository;
use App\Modules\User\Domain\Error\UserNotFoundError;
use App\Modules\User\Domain\Repository\IUserRepository;

final readonly class ReturnBookCommandHandler
{
    public function __construct(
        private IBookRepository $bookRepository,
        private IUserRepository $userRepository
    ) {
    }

    /**
     * @throws BookNotFoundError
     * @throws UserNotFoundError
     */
    public function handle(ReturnBookCommand $command): SuccessViewModel
    {
        $book = $this->bookRepository->findByTitle($command->bookTitle);
        if (!$book) {
            throw new BookNotFoundError();
        }

        $user = $this->userRepository->findById($command->userId->value);
        if (!$user) {
            throw new UserNotFoundError();
        }

        $book->returnBook();
        $user->removeBorrowedBook($book);

        $this->bookRepository->flush();

        return new SuccessViewModel('Book returned successfully');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Query;

use App\Modules\Book\Application\ViewModel\BookViewModel;
use App\Modules\Book\Domain\Error\BookNotFoundError;
use App\Modules\Book\Domain\Repository\IBookRepository;

final readonly class GetBookByIdQueryHandler
{
    public function __construct(
        private IBookRepository $bookRepository
    ) {
    }

    public function handle(GetBookByIdQuery $query): BookViewModel
    {
        $book = $this->bookRepository->findById($query->id->value);

        if (!$book) {
            throw new BookNotFoundError();
        }

        return new BookViewModel(
            id: $book->getId(),
            title: $book->getTitle(),
            author: $book->getAuthor(),
            isBorrowed: $book->isBorrowed(),
            borrowedAt: $book->getBorrowedAt(),
            returnedAt: $book->getReturnedAt()
        );
    }
}

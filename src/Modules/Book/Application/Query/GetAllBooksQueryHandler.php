<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Query;

use App\Modules\Book\Application\ViewModel\BookViewModel;
use App\Modules\Book\Domain\Repository\IBookRepository;

readonly class GetAllBooksQueryHandler
{
    public function __construct(
        private IBookRepository $bookRepository
    ) {
    }

    /**
     * @return BookViewModel[]
     */
    public function handle(GetAllBooksQuery $query): array
    {
        $books = $this->bookRepository->findAll();

        return array_map(
            fn($book) => new BookViewModel(
                id: $book->getId(),
                title: $book->getTitle(),
                author: $book->getAuthor(),
                isBorrowed: $book->isBorrowed(),
                borrowedAt: $book->getBorrowedAt(),
                returnedAt: $book->getReturnedAt()
            ),
            $books
        );
    }
}

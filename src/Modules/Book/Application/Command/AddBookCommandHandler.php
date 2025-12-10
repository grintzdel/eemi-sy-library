<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

use App\Modules\Book\Application\ViewModel\SuccessViewModel;
use App\Modules\Book\Domain\Repository\IBookRepository;
use App\Modules\Book\Infrastructure\Entity\BookEntity;

final readonly class AddBookCommandHandler
{
    public function __construct(
        private IBookRepository $bookRepository
    ) {
    }

    public function handle(AddBookCommand $command): SuccessViewModel
    {
        $book = new BookEntity();
        $book->setTitle($command->title);
        $book->setAuthor($command->author);

        $this->bookRepository->save($book);
        $this->bookRepository->flush();

        return new SuccessViewModel('Book added successfully');
    }
}

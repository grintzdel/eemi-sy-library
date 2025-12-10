<?php

declare(strict_types=1);

namespace App\Modules\Book\Presentation\Controller;

use App\Modules\Book\Application\Command\AddBookCommand;
use App\Modules\Book\Application\Command\BorrowBookCommand;
use App\Modules\Book\Application\Command\ReturnBookCommand;
use App\Modules\Book\Application\Query\GetAllBooksQuery;
use App\Modules\Book\Application\Query\GetBookByIdQuery;
use App\Modules\Shared\Domain\ValueObject\BookId;
use App\Modules\Shared\Presentation\Controllers\AppController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/books')]
final class BookController extends AppController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['GET'])]
    public function getAllBooks(): JsonResponse
    {
        return $this->dispatchQuery(new GetAllBooksQuery());
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getBookById(string $id): JsonResponse
    {
        return $this->dispatchQuery(new GetBookByIdQuery(BookId::fromString($id)));
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function addBook(Request $request): JsonResponse
    {
        return $this->dispatch(AddBookCommand::fromRequest($request));
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/borrow', methods: ['POST'])]
    public function borrowBook(Request $request): JsonResponse
    {
        return $this->dispatch(BorrowBookCommand::fromRequest($request));
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/return', methods: ['POST'])]
    public function returnBook(Request $request): JsonResponse
    {
        return $this->dispatch(ReturnBookCommand::fromRequest($request));
    }
}

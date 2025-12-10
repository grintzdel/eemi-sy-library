<?php

declare(strict_types=1);

namespace App\Modules\Book\Presentation\Controller;

use App\Modules\Book\Application\Command\AddBookCommand;
use App\Modules\Book\Application\Command\BorrowBookCommand;
use App\Modules\Book\Application\Query\GetAllBooksQuery;
use App\Modules\Book\Application\Query\GetBookByIdQuery;
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
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getBookById(int $id): JsonResponse
    {
        return $this->dispatchQuery(new GetBookByIdQuery($id));
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function addBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['author'])) {
            return $this->json(['error' => 'Title and author are required'], 400);
        }

        $command = new AddBookCommand(
            title: $data['title'],
            author: $data['author']
        );

        return $this->dispatch($command);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/borrow', methods: ['POST'])]
    public function borrowBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['bookTitle']) || !isset($data['userId'])) {
            return $this->json(['error' => 'Book title and user ID are required'], 400);
        }

        $command = new BorrowBookCommand(
            bookTitle: $data['bookTitle'],
            userId: (int) $data['userId']
        );

        return $this->dispatch($command);
    }
}

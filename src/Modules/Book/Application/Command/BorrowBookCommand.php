<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

use App\Modules\Shared\Domain\ValueObject\UserId;
use Symfony\Component\HttpFoundation\Request;

final readonly class BorrowBookCommand
{
    public string $bookTitle;
    public UserId $userId;

    private function __construct(
        string $bookTitle,
        UserId $userId
    ) {
        $this->bookTitle = $bookTitle;
        $this->userId = $userId;
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['bookTitle']) || !isset($data['userId'])) {
            throw new \InvalidArgumentException('Book title and user ID are required');
        }

        return new self(
            bookTitle: $data['bookTitle'],
            userId: UserId::fromString((string) $data['userId'])
        );
    }
}

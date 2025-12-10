<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

final readonly class BorrowBookCommand
{
    public function __construct(
        public string $bookTitle,
        public int    $userId
    ) {
    }
}

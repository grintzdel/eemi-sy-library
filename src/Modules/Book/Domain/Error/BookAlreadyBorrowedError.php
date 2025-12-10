<?php

declare(strict_types=1);

namespace App\Modules\Book\Domain\Error;

final class BookAlreadyBorrowedError extends BookError
{
    public function __construct()
    {
        parent::__construct('Book is already borrowed', 400);
    }
}

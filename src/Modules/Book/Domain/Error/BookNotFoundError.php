<?php

declare(strict_types=1);

namespace App\Modules\Book\Domain\Error;

final class BookNotFoundError extends BookError
{
    public function __construct()
    {
        parent::__construct('Book not found', 404);
    }
}

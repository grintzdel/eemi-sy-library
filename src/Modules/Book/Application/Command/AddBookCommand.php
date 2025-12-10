<?php

declare(strict_types=1);


namespace App\Modules\Book\Application\Command;

final readonly class AddBookCommand
{
    public function __construct(
        public string $title,
        public string $author
    ) {
    }
}

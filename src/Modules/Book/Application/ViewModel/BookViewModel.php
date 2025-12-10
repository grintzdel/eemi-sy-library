<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\ViewModel;

final readonly class BookViewModel
{
    public function __construct(
        public string     $id,
        public string     $title,
        public string     $author,
        public bool       $isBorrowed,
        public ?\DateTime $borrowedAt,
        public ?\DateTime $returnedAt
    ) {
    }
}

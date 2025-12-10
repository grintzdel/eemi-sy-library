<?php

declare(strict_types=1);

namespace App\Modules\User\Application\ViewModel;

class UserViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly array $borrowedBooks
    ) {
    }
}

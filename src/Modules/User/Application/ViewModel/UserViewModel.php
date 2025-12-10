<?php

declare(strict_types=1);

namespace App\Modules\User\Application\ViewModel;

final readonly class UserViewModel
{
    public function __construct(
        public string $id,
        public string $name,
        public array  $borrowedBooks
    ) {
    }
}

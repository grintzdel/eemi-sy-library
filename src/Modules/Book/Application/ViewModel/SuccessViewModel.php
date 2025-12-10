<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\ViewModel;

readonly class SuccessViewModel
{
    public function __construct(
        public string $message
    ) {
    }
}

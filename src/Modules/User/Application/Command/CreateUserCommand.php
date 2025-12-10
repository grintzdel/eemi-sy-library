<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Command;

class CreateUserCommand
{
    public function __construct(
        public readonly string $name
    ) {
    }
}

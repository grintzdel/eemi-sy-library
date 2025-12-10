<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Error;

class UserNotFoundError extends UserError
{
    public function __construct()
    {
        parent::__construct('User not found', 404);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Error;

final class BorrowLimitExceededError extends UserError
{
    public function __construct(int $maxBooks)
    {
        parent::__construct(
            sprintf('User cannot borrow more than %d books', $maxBooks),
            400
        );
    }
}

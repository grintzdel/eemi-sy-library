<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

class GetUserByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {
    }
}

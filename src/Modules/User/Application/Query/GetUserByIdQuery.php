<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

use App\Modules\Shared\Domain\ValueObject\UserId;

final readonly class GetUserByIdQuery
{
    public function __construct(
        public UserId $id
    ) {
    }
}

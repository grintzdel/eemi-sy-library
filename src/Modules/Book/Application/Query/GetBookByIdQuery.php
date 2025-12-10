<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Query;

readonly class GetBookByIdQuery
{
    public function __construct(
        public int $id
    ) {
    }
}

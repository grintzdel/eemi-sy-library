<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Query;

use App\Modules\Shared\Domain\ValueObject\BookId;

final readonly class GetBookByIdQuery
{
    public function __construct(
        public BookId $id
    ) {
    }
}

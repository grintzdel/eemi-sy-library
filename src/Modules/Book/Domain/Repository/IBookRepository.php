<?php

declare(strict_types=1);

namespace App\Modules\Book\Domain\Repository;

use App\Modules\Book\Infrastructure\Entity\BookEntity;

interface IBookRepository
{
    public function save(BookEntity $book): void;

    public function findByTitle(string $title): ?BookEntity;

    public function findById(int $id): ?BookEntity;

    public function findAll(): array;

    public function flush(): void;
}

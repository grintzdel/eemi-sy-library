<?php

declare(strict_types=1);

namespace App\Modules\Book\Infrastructure\Repository;

use App\Modules\Book\Domain\Repository\IBookRepository;
use App\Modules\Book\Infrastructure\Entity\BookEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BookRepositorySql implements IBookRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(BookEntity $book): void
    {
        $this->entityManager->persist($book);
    }

    public function findByTitle(string $title): ?BookEntity
    {
        return $this->entityManager
            ->getRepository(BookEntity::class)
            ->findOneBy(['title' => $title]);
    }

    public function findById(int $id): ?BookEntity
    {
        return $this->entityManager
            ->getRepository(BookEntity::class)
            ->find($id);
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(BookEntity::class)
            ->findAll();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}

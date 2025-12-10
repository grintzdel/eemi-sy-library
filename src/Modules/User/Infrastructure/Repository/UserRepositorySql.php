<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Repository;

use App\Modules\User\Domain\Repository\IUserRepository;
use App\Modules\User\Infrastructure\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserRepositorySql implements IUserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function findById(int $id): ?UserEntity
    {
        return $this->entityManager
            ->getRepository(UserEntity::class)
            ->find($id);
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(UserEntity::class)
            ->findAll();
    }

    public function save(UserEntity $user): void
    {
        $this->entityManager->persist($user);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}

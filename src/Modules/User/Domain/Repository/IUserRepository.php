<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Repository;

use App\Modules\User\Infrastructure\Entity\UserEntity;

interface IUserRepository
{
    public function findById(int $id): ?UserEntity;

    public function findAll(): array;

    public function save(UserEntity $user): void;

    public function flush(): void;
}

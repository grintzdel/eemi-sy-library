<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject;

final readonly class UserId
{
    public function __construct(
        public string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('User ID cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}
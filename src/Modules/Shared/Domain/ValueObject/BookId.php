<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject;

final readonly class BookId
{
    public function __construct(
        public string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Book ID cannot be empty');
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

    public function equals(BookId $other): bool
    {
        return $this->value === $other->value;
    }
}
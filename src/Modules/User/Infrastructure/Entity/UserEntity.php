<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Entity;

use App\Modules\Book\Infrastructure\Entity\BookEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'json')]
    private array $borrowedBooks = [];

    public function __construct()
    {
        $this->id = uniqid('user_', true);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getBorrowedBooks(): array
    {
        return $this->borrowedBooks;
    }

    public function addBorrowedBook(BookEntity $book): self
    {
        $this->borrowedBooks[] = $book->getId();
        return $this;
    }

    public function removeBorrowedBook(BookEntity $book): self
    {
        $key = array_search($book->getId(), $this->borrowedBooks);
        if ($key !== false) {
            unset($this->borrowedBooks[$key]);
            $this->borrowedBooks = array_values($this->borrowedBooks);
        }
        return $this;
    }
}

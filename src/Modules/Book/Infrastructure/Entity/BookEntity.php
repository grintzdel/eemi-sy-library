<?php

declare(strict_types=1);

namespace App\Modules\Book\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
class BookEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $author;

    #[ORM\Column(type: 'boolean')]
    private bool $borrowed = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $borrowedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $returnedAt = null;

    public function __construct()
    {
        $this->id = uniqid('book_', true);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function isBorrowed(): bool
    {
        return $this->borrowed;
    }

    public function borrow(): self
    {
        $this->borrowed = true;
        $this->borrowedAt = new \DateTime();
        return $this;
    }

    public function returnBook(): self
    {
        $this->borrowed = false;
        $this->returnedAt = new \DateTime();
        return $this;
    }

    public function getBorrowedAt(): ?\DateTime
    {
        return $this->borrowedAt;
    }

    public function getReturnedAt(): ?\DateTime
    {
        return $this->returnedAt;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Book\Application\Command;

use Symfony\Component\HttpFoundation\Request;

final readonly class AddBookCommand
{
    public string $title;
    public string $author;

    private function __construct(
        string $title,
        string $author
    ) {
        $this->title = $title;
        $this->author = $author;
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['author'])) {
            throw new \InvalidArgumentException('Title and author are required');
        }

        return new self(
            title: $data['title'],
            author: $data['author']
        );
    }
}

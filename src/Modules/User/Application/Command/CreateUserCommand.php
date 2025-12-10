<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Command;

use Symfony\Component\HttpFoundation\Request;

final readonly class CreateUserCommand
{
    public string $name;

    private function __construct(
        string $name
    ) {
        $this->name = $name;
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }

        return new self(
            name: $data['name']
        );
    }
}

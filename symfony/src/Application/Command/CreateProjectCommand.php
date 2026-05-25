<?php

declare(strict_types=1);

namespace App\Application\Command;

final readonly class CreateProjectCommand
{
    public function __construct(
        public string $name,
        public string $ownerId,
        public ?string $description = null,
    ) {}
}
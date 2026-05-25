<?php

declare(strict_types=1);

namespace App\Application\Command;

final readonly class CreateTaskCommand
{
    public function __construct(
        public string $title,
        public string $projectId,
        public string $priority = 'medium',
        public ?string $description = null,
        public ?string $assigneeId = null,
    ) {}
}
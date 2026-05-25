<?php

declare(strict_types=1);

namespace App\Application\Command;

final readonly class UpdateTaskStatusCommand
{
    public function __construct(
        public string $taskId,
        public string $newStatus,
    ) {}
}
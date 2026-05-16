<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function label(): string
    {
        return match($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::DONE => 'Terminé',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::TODO => $newStatus === self::IN_PROGRESS,
            self::IN_PROGRESS => $newStatus === self::DONE,
            self::DONE => false,
        };
    }
}
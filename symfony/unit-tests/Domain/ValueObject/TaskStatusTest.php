<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskStatusTest extends TestCase
{
    public function testLabels(): void
    {
        self::assertSame('À faire', TaskStatus::TODO->label());
        self::assertSame('En cours', TaskStatus::IN_PROGRESS->label());
        self::assertSame('Terminé', TaskStatus::DONE->label());
    }

    #[DataProvider('allowedTransitionsProvider')]
    public function testAllowedTransitions(TaskStatus $from, TaskStatus $to): void
    {
        self::assertTrue($from->canTransitionTo($to));
    }

    #[DataProvider('forbiddenTransitionsProvider')]
    public function testForbiddenTransitions(TaskStatus $from, TaskStatus $to): void
    {
        self::assertFalse($from->canTransitionTo($to));
    }

    public static function allowedTransitionsProvider(): iterable
    {
        yield 'todo to in_progress' => [TaskStatus::TODO, TaskStatus::IN_PROGRESS];
        yield 'in_progress to done' => [TaskStatus::IN_PROGRESS, TaskStatus::DONE];
    }

    public static function forbiddenTransitionsProvider(): iterable
    {
        yield 'todo to done' => [TaskStatus::TODO, TaskStatus::DONE];
        yield 'todo to todo' => [TaskStatus::TODO, TaskStatus::TODO];
        yield 'in_progress to todo' => [TaskStatus::IN_PROGRESS, TaskStatus::TODO];
        yield 'done to todo' => [TaskStatus::DONE, TaskStatus::TODO];
        yield 'done to in_progress' => [TaskStatus::DONE, TaskStatus::IN_PROGRESS];
        yield 'done to done' => [TaskStatus::DONE, TaskStatus::DONE];
    }
}

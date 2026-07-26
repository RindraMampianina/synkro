<?php

declare(strict_types=1);

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Exception\InvalidTaskTransitionException;
use App\Domain\ValueObject\TaskPriority;
use App\Domain\ValueObject\TaskStatus;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testNewTaskStartsAsTodoWithMediumPriorityByDefault(): void
    {
        $task = $this->createTask();

        self::assertSame(TaskStatus::TODO, $task->getStatus());
        self::assertSame(TaskPriority::MEDIUM, $task->getPriority());
        self::assertSame('Write tests', $task->getTitle());
        self::assertNull($task->getAssignee());
    }

    public function testValidTransitionFromTodoToInProgress(): void
    {
        $task = $this->createTask();

        $task->transitionTo(TaskStatus::IN_PROGRESS);

        self::assertSame(TaskStatus::IN_PROGRESS, $task->getStatus());
        self::assertNotNull($task->getUpdatedAt());
    }

    public function testValidTransitionFromInProgressToDone(): void
    {
        $task = $this->createTask();
        $task->transitionTo(TaskStatus::IN_PROGRESS);

        $task->transitionTo(TaskStatus::DONE);

        self::assertSame(TaskStatus::DONE, $task->getStatus());
    }

    public function testInvalidTransitionThrows(): void
    {
        $task = $this->createTask();

        $this->expectException(InvalidTaskTransitionException::class);
        $this->expectExceptionMessage('Cannot transition from todo to done');

        $task->transitionTo(TaskStatus::DONE);
    }

    public function testAssignToUser(): void
    {
        $task = $this->createTask();
        $assignee = new User('dev@synkro.com', 'Dev');

        $task->assignTo($assignee);

        self::assertSame($assignee, $task->getAssignee());
        self::assertNotNull($task->getUpdatedAt());
    }

    private function createTask(): Task
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = new Project('Synkro', $owner);

        return new Task('Write tests', $project, TaskPriority::MEDIUM);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\UpdateTaskStatusCommand;
use App\Application\Handler\UpdateTaskStatusHandler;
use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Exception\InvalidTaskTransitionException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Mercure\MercurePublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;

final class UpdateTaskStatusHandlerTest extends TestCase
{
    public function testUpdatesStatusAndPublishesEvent(): void
    {
        $task = $this->createTask();

        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $taskRepository->expects(self::once())
            ->method('findById')
            ->with('task-1')
            ->willReturn($task);

        $taskRepository->expects(self::once())
            ->method('save')
            ->with($task);

        $hub->expects(self::once())
            ->method('publish')
            ->willReturn('event-id');

        $handler = new UpdateTaskStatusHandler($taskRepository, new MercurePublisher($hub));
        $handler(new UpdateTaskStatusCommand('task-1', 'in_progress'));

        self::assertSame(TaskStatus::IN_PROGRESS, $task->getStatus());
    }

    public function testThrowsWhenTaskIsMissing(): void
    {
        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $taskRepository->method('findById')->willReturn(null);
        $taskRepository->expects(self::never())->method('save');
        $hub->expects(self::never())->method('publish');

        $handler = new UpdateTaskStatusHandler($taskRepository, new MercurePublisher($hub));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Task not found.');

        $handler(new UpdateTaskStatusCommand('missing', 'in_progress'));
    }

    public function testPropagatesInvalidTransition(): void
    {
        $task = $this->createTask();

        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $taskRepository->method('findById')->willReturn($task);
        $taskRepository->expects(self::never())->method('save');
        $hub->expects(self::never())->method('publish');

        $handler = new UpdateTaskStatusHandler($taskRepository, new MercurePublisher($hub));

        $this->expectException(InvalidTaskTransitionException::class);

        $handler(new UpdateTaskStatusCommand('task-1', 'done'));
    }

    private function createTask(): Task
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = new Project('Synkro', $owner);
        (new \ReflectionProperty(Project::class, 'id'))->setValue($project, 'project-1');

        return new Task('Status change', $project);
    }
}

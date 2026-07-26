<?php

declare(strict_types=1);

namespace App\Tests\Application\Handler;

use App\Application\Command\CreateTaskCommand;
use App\Application\Handler\CreateTaskHandler;
use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TaskPriority;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Mercure\MercurePublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;

final class CreateTaskHandlerTest extends TestCase
{
    public function testCreatesTaskAndPublishesEvent(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = $this->projectWithId($owner, 'project-1');

        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $projectRepository->expects(self::once())
            ->method('findById')
            ->with('project-1')
            ->willReturn($project);

        $taskRepository->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(Task::class));

        $hub->expects(self::once())
            ->method('publish')
            ->willReturn('event-id');

        $handler = new CreateTaskHandler(
            $taskRepository,
            $projectRepository,
            $userRepository,
            new MercurePublisher($hub),
        );

        $task = $handler(new CreateTaskCommand(
            title: 'Ma tâche',
            projectId: 'project-1',
            priority: 'high',
            description: 'Details',
        ));

        self::assertSame('Ma tâche', $task->getTitle());
        self::assertSame(TaskPriority::HIGH, $task->getPriority());
        self::assertSame(TaskStatus::TODO, $task->getStatus());
        self::assertSame($project, $task->getProject());
    }

    public function testThrowsWhenProjectIsMissing(): void
    {
        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $projectRepository = $this->createStub(ProjectRepositoryInterface::class);
        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $projectRepository->method('findById')->willReturn(null);
        $taskRepository->expects(self::never())->method('save');
        $hub->expects(self::never())->method('publish');

        $handler = new CreateTaskHandler(
            $taskRepository,
            $projectRepository,
            $userRepository,
            new MercurePublisher($hub),
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Project not found.');

        $handler(new CreateTaskCommand('Orphan', 'missing'));
    }

    public function testAssignsUserWhenAssigneeIdIsProvided(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $assignee = new User('dev@synkro.com', 'Dev');
        $project = $this->projectWithId($owner, 'project-1');

        $taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $projectRepository = $this->createStub(ProjectRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $hub = $this->createMock(HubInterface::class);

        $projectRepository->method('findById')->willReturn($project);
        $userRepository->expects(self::once())
            ->method('findById')
            ->with('user-2')
            ->willReturn($assignee);
        $taskRepository->expects(self::once())->method('save');
        $hub->expects(self::once())->method('publish')->willReturn('event-id');

        $handler = new CreateTaskHandler(
            $taskRepository,
            $projectRepository,
            $userRepository,
            new MercurePublisher($hub),
        );

        $task = $handler(new CreateTaskCommand(
            title: 'Assigned',
            projectId: 'project-1',
            assigneeId: 'user-2',
        ));

        self::assertSame($assignee, $task->getAssignee());
    }

    private function projectWithId(User $owner, string $id): Project
    {
        $project = new Project('Synkro', $owner);
        (new \ReflectionProperty(Project::class, 'id'))->setValue($project, $id);

        return $project;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateTaskCommand;
use App\Domain\Entity\Task;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TaskPriority;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateTaskHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(CreateTaskCommand $command): Task
    {
        $project = $this->projectRepository->findById($command->projectId);

        if (!$project) {
            throw new \DomainException('Project not found.');
        }

        $priority = TaskPriority::from($command->priority);

        $task = new Task(
            title: $command->title,
            project: $project,
            priority: $priority,
            description: $command->description,
        );

        if ($command->assigneeId) {
            $assignee = $this->userRepository->findById($command->assigneeId);
            if ($assignee) {
                $task->assignTo($assignee);
            }
        }

        $this->taskRepository->save($task);
        
        return $task;
    }
}
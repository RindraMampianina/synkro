<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\UpdateTaskStatusCommand;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\TaskStatus;
use App\Infrastructure\Mercure\MercurePublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateTaskStatusHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly MercurePublisher $mercurePublisher,
    ) {}

    public function __invoke(UpdateTaskStatusCommand $command): void
    {
        $task = $this->taskRepository->findById($command->taskId);

        if (!$task) {
            throw new \DomainException('Task not found.');
        }

        $task->transitionTo(TaskStatus::from($command->newStatus));
        $this->taskRepository->save($task);

        // Notifie tous les clients connectés au projet
        $this->mercurePublisher->publishTaskUpdated($task);
    }
}
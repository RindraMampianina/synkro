<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\UI\Api\Resource\TaskResource;

final class TaskProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?TaskResource
    {
        $task = $this->taskRepository->findById($uriVariables['id']);

        if (!$task) {
            return null;
        }

        $resource = new TaskResource();
        $resource->id = $task->getId();
        $resource->title = $task->getTitle();
        $resource->description = $task->getDescription();
        $resource->status = $task->getStatus()->value;
        $resource->priority = $task->getPriority()->value;
        $resource->projectId = $task->getProject()->getId();
        $resource->assigneeId = $task->getAssignee()?->getId();
        $resource->dueDate = $task->getDueDate();
        $resource->createdAt = $task->getCreatedAt();

        return $resource;
    }
}
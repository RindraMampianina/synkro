<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\UI\Api\Resource\TaskResource;

final class TaskCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projectId = $context['filters']['projectId'] ?? null;

        if ($projectId) {
            $project = $this->projectRepository->findById($projectId);
            if (!$project) {
                return [];
            }
            $tasks = $this->taskRepository->findByProject($project);
        } else {
            $tasks = [];
        }

        return array_map(function ($task) {
            $resource = new TaskResource();
            $resource->id = $task->getId();
            $resource->title = $task->getTitle();
            $resource->description = $task->getDescription();
            $resource->status = $task->getStatus()->value;
            $resource->priority = $task->getPriority()->value;
            $resource->projectId = $task->getProject()->getId();
            $resource->assigneeId = $task->getAssignee()?->getId();
            $resource->createdAt = $task->getCreatedAt();

            return $resource;
        }, $tasks);
    }
}
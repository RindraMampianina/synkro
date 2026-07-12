<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\UpdateTaskStatusCommand;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Infrastructure\Security\ProjectAccessChecker;
use App\UI\Api\Resource\TaskResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateTaskStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectAccessChecker $projectAccessChecker,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        /** @var TaskResource $data */
        $task = $this->taskRepository->findById($uriVariables['id']);
        if (!$task) {
            throw new NotFoundHttpException('Task not found.');
        }

        $this->projectAccessChecker->assertCanAccess($task->getProject());

        $this->bus->dispatch(new UpdateTaskStatusCommand(
            taskId: $uriVariables['id'],
            newStatus: $data->status,
        ));

        $updated = $this->taskRepository->findById($uriVariables['id']);
        if (!$updated) {
            throw new NotFoundHttpException('Task not found.');
        }

        $resource = new TaskResource();
        $resource->id = $updated->getId();
        $resource->title = $updated->getTitle();
        $resource->description = $updated->getDescription();
        $resource->status = $updated->getStatus()->value;
        $resource->priority = $updated->getPriority()->value;
        $resource->projectId = $updated->getProject()->getId();
        $resource->assigneeId = $updated->getAssignee()?->getId();
        $resource->createdAt = $updated->getCreatedAt();

        return $resource;
    }
}

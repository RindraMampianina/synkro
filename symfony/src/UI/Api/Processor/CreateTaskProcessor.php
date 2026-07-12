<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\CreateTaskCommand;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Security\ProjectAccessChecker;
use App\UI\Api\Resource\TaskResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class CreateTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectAccessChecker $projectAccessChecker,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        /** @var TaskResource $data */
        $project = $this->projectRepository->findById((string) $data->projectId);
        if (!$project) {
            throw new NotFoundHttpException('Project not found.');
        }

        $this->projectAccessChecker->assertCanAccess($project);

        $envelope = $this->bus->dispatch(new CreateTaskCommand(
            title: $data->title,
            projectId: $data->projectId,
            priority: $data->priority ?? 'medium',
            description: $data->description,
            assigneeId: $data->assigneeId,
        ));

        $task = $envelope->last(HandledStamp::class)->getResult();

        $resource = new TaskResource();
        $resource->id = $task->getId();
        $resource->title = $task->getTitle();
        $resource->description = $task->getDescription();
        $resource->status = $task->getStatus()->value;
        $resource->priority = $task->getPriority()->value;
        $resource->projectId = $task->getProject()->getId();
        $resource->createdAt = $task->getCreatedAt();

        return $resource;
    }
}

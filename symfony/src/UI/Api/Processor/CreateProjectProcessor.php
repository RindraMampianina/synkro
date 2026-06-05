<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\CreateProjectCommand;
use App\UI\Api\Resource\ProjectResource;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\Entity\User;

final class CreateProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        /** @var ProjectResource $data */
        $envelope = $this->bus->dispatch(new CreateProjectCommand(
            name: $data->name,
            ownerId: $data->ownerId,
            description: $data->description,
        ));

        $project = $envelope->last(HandledStamp::class)->getResult();

        $resource = new ProjectResource();
        $resource->id = $project->getId();
        $resource->name = $project->getName();
        $resource->description = $project->getDescription();
        $resource->ownerId = $project->getOwner()->getId();
        $resource->createdAt = $project->getCreatedAt();

        return $resource;
    }
}
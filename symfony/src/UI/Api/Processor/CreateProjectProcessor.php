<?php

declare(strict_types=1);

namespace App\UI\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Command\CreateProjectCommand;
use App\Domain\Repository\UserRepositoryInterface;
use App\UI\Api\Resource\ProjectResource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class CreateProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Security $security,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        /** @var ProjectResource $data */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            throw new \DomainException('Authenticated user not found.');
        }

        $ownerId = null;
        if (method_exists($currentUser, 'getId')) {
            $ownerId = $currentUser->getId();
        }

        if (!$ownerId) {
            $ownerId = $currentUser->getUserIdentifier() ?? null;
            if ($ownerId) {
                $user = $this->userRepository->findByEmail($ownerId);
                $ownerId = $user?->getId();
            }
        }

        if (!is_string($ownerId) || '' === trim($ownerId)) {
            throw new \DomainException('Unable to resolve project owner id.');
        }

        $envelope = $this->bus->dispatch(new CreateProjectCommand(
            name: $data->name,
            ownerId: $ownerId,
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
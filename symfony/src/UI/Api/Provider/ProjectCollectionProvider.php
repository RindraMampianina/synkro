<?php

declare(strict_types=1);

namespace App\UI\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\UI\Api\Resource\ProjectResource;
use Symfony\Bundle\SecurityBundle\Security;

final class ProjectCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentUser = $this->userRepository->findByEmail(
            $this->security->getUser()->getUserIdentifier()
        );

        $projects = $this->projectRepository->findByOwner($currentUser);

        return array_map(function ($project) {
            $resource = new ProjectResource();
            $resource->id = $project->getId();
            $resource->name = $project->getName();
            $resource->description = $project->getDescription();
            $resource->ownerId = $project->getOwner()->getId();
            $resource->createdAt = $project->getCreatedAt();

            return $resource;
        }, $projects);
    }
}
<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateProjectCommand;
use App\Domain\Entity\Project;
use App\Domain\Repository\ProjectRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(CreateProjectCommand $command): Project
    {
        $owner = $this->userRepository->findById($command->ownerId);

        if (!$owner) {
            throw new \DomainException('User not found.');
        }

        $project = new Project($command->name, $owner, $command->description);

        $this->projectRepository->save($project);

        return $project;
    }
}
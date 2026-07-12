<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\Project;
use App\Domain\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ProjectAccessChecker
{
    public function __construct(
        private readonly CurrentUserResolver $currentUserResolver,
    ) {}

    public function assertCanAccess(Project $project): User
    {
        $user = $this->currentUserResolver->requireUser();

        if (!$project->isAccessibleBy($user)) {
            throw new AccessDeniedException('You do not have access to this project.');
        }

        return $user;
    }
}

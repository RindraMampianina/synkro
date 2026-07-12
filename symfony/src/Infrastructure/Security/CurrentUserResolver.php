<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class CurrentUserResolver
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function requireUser(): User
    {
        $tokenUser = $this->security->getUser();
        if (!$tokenUser) {
            throw new AccessDeniedException('Authentication required.');
        }

        $user = $this->userRepository->findByEmail($tokenUser->getUserIdentifier());
        if (!$user) {
            throw new AccessDeniedException('User not found.');
        }

        return $user;
    }
}

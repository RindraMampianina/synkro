<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Project;
use App\Domain\Entity\User;

interface ProjectRepositoryInterface
{
    public function findById(string $id): ?Project;
    public function findByOwner(User $user): array;
    public function findByMember(User $user): array;
    public function save(Project $project): void;
    public function remove(Project $project): void;
}
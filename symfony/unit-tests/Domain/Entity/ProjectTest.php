<?php

declare(strict_types=1);

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Project;
use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    public function testOwnerIsMemberAndHasAccess(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $project = new Project('Alpha', $owner);

        self::assertTrue($project->getMembers()->contains($owner));
        self::assertTrue($project->isAccessibleBy($owner));
    }

    public function testMemberHasAccess(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $member = new User('dev@synkro.com', 'Dev');
        $project = new Project('Alpha', $owner);
        $project->addMember($member);

        self::assertTrue($project->isAccessibleBy($member));
    }

    public function testStrangerHasNoAccess(): void
    {
        $owner = new User('owner@synkro.com', 'Owner');
        $stranger = new User('other@synkro.com', 'Other');
        $project = new Project('Alpha', $owner);

        self::assertFalse($project->isAccessibleBy($stranger));
    }
}

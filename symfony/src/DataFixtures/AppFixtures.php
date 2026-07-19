<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Entity\Project;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\ValueObject\TaskPriority;
use App\Domain\ValueObject\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $demo = new User('demo@synkro.local', 'Demo User');
        $demo->setPassword($this->passwordHasher->hashPassword($demo, 'password123'));
        $manager->persist($demo);

        $project = new Project(
            'Onboarding Synkro',
            $demo,
            'Projet de démo pour explorer le board et le temps réel.'
        );
        $manager->persist($project);

        $todo = new Task('Configurer le compte', $project, TaskPriority::HIGH);
        $inProgress = new Task('Créer la première tâche', $project, TaskPriority::MEDIUM);
        $inProgress->transitionTo(TaskStatus::IN_PROGRESS);
        $done = new Task('Lire le README', $project, TaskPriority::LOW);
        $done->transitionTo(TaskStatus::IN_PROGRESS);
        $done->transitionTo(TaskStatus::DONE);

        $manager->persist($todo);
        $manager->persist($inProgress);
        $manager->persist($done);

        $manager->flush();
    }
}

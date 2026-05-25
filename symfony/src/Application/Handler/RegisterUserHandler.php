<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\RegisterUserCommand;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(RegisterUserCommand $command): User
    {
        if ($this->userRepository->findByEmail($command->email)) {
            throw new \DomainException('Email already exists.');
        }

        $user = new User($command->email, $command->fullName);

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $command->plainPassword)
        );

        $this->userRepository->save($user);

        return $user;
    }
}
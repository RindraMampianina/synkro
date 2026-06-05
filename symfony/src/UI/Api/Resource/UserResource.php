<?php

declare(strict_types=1);

namespace App\UI\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\UI\Api\Processor\RegisterUserProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'User',
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            processor: RegisterUserProcessor::class,
        ),
    ]
)]
final class UserResource
{
    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    public string $fullName = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $plainPassword = '';
}
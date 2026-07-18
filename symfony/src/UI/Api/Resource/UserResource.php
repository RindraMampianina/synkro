<?php

declare(strict_types=1);

namespace App\UI\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\UI\Api\Processor\RegisterUserProcessor;
use App\UI\Api\Provider\MeProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'User',
    operations: [
        new Post(
            uriTemplate: '/auth/register',
            processor: RegisterUserProcessor::class,
            denormalizationContext: ['groups' => ['user:write']],
            validationContext: ['groups' => ['user:write']],
        ),
        new Get(
            uriTemplate: '/me',
            provider: MeProvider::class,
            normalizationContext: ['groups' => ['user:read']],
        ),
    ]
)]
final class UserResource
{
    #[Groups(['user:read'])]
    public ?string $id = null;

    #[Assert\NotBlank(groups: ['user:write'])]
    #[Assert\Email(groups: ['user:write'])]
    #[Groups(['user:read', 'user:write'])]
    public string $email = '';

    #[Assert\NotBlank(groups: ['user:write'])]
    #[Groups(['user:read', 'user:write'])]
    public string $fullName = '';

    #[Assert\NotBlank(groups: ['user:write'])]
    #[Assert\Length(min: 8, groups: ['user:write'])]
    #[Groups(['user:write'])]
    public string $plainPassword = '';
}

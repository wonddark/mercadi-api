<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Registration;
use Doctrine\ORM\EntityManager;

class TestRegistrationEmail implements ProviderInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $test = $this->entityManager->getRepository(Registration::class)
            ->findOneBy(["email" => $uriVariables["email"]]);
        if ($test !== null) {
            return [$test];
        }
        return [];
    }
}

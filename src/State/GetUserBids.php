<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Bid;
use Doctrine\ORM\EntityManagerInterface;

class GetUserBids implements ProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $userId = $uriVariables["id"];
        return $this->manager->getRepository(Bid::class)->getUserBids($userId);
    }
}

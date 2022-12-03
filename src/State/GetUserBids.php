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
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $userId = $uriVariables["id"];
        $itemsPerPage = (int)$context["filters"]["itemsPerPage"] ?: 30;
        $page = (int)$context["filters"]["page"] ?: 1;
        return $this->manager->getRepository(Bid::class)->getUserBids($userId, $page, $itemsPerPage);
    }
}

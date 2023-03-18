<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;

class GetOfferFiltered implements ProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $pattern = array_key_exists("query", $context["filters"]) ? $context["filters"]["query"] : "";
        $itemsPerPage = (int)$context["filters"]["itemsPerPage"] ?: 30;
        $page = (int)$context["filters"]["page"] ?: 1;
        return $this
            ->manager
            ->getRepository(Offer::class)
            ->searchByNameOrDescription($pattern, $page, $itemsPerPage);
    }
}

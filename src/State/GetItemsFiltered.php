<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;

class GetItemsFiltered implements ProviderInterface
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
            ->getRepository(Item::class)
            ->searchByDescription($pattern, $page, $itemsPerPage);
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Bid;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetHighestBidPerItem implements ProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|null
    {
        $itemId = $uriVariables["id"];
        if (!$itemId) {
            throw new BadRequestHttpException("You must specify the item id");
        }

        $item = $this->manager->getRepository(Item::class)->find($itemId);
        if (!$item) {
            throw new NotFoundHttpException("Referred item cannot be fount");
        }

        return $this->manager->getRepository(Bid::class)->getHighestPerItem($itemId);
    }
}

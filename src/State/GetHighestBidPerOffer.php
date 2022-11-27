<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Bid;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetHighestBidPerOffer implements ProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|null
    {
        $offerId = $uriVariables["id"];
        if (!$offerId) {
            throw new BadRequestHttpException("You must specify the offer id");
        }

        $offer = $this->manager->getRepository(Offer::class)->find($offerId);
        if (!$offer) {
            throw new NotFoundHttpException("Referred offer cannot be fount");
        }

        return $this->manager->getRepository(Bid::class)->getHighestPerOffer($offerId);
    }
}

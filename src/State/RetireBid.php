<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bid;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RetireBid implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isDeletable()) {
            /** @var Offer $offer */
            $offer = $data->getOffer();
            if ($offer->getHighestBid()->getId() === $data->getId()) {
                $index = $offer->getBids()->indexOf($offer->getHighestBid());
                $offer->setHighestBid($offer->getBids()[$index - 1]);
                $this->manager->persist($offer);
            }
            $this->manager->getRepository(Bid::class)->remove($data);
            $this->manager->flush();
        } else {
            throw new UnprocessableEntityHttpException("Bid is not deletable");
        }
    }
}

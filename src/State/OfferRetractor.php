<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OfferRetractor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function process(
        /* @var Offer $data */
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isDeletable()) {
            $bids = $data->getBids();

            if ($bids->getHighestOffer() === $data->getQuantity()) {
                $index = $bids->getOffers()->indexOf($data);
                $previousOffer = $bids->getOffers()->toArray()[$index - 1];
                $bids->setHighestOffer($previousOffer->getQuantity());
                $this->entityManager->persist($bids);
            }
            $this->processor->process($data, $operation, $uriVariables, $context);
        } else {
            throw new UnprocessableEntityHttpException("Offer is not deletable");
        }
    }
}

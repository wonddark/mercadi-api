<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bidding;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BiddingCloser implements ProcessorInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(
        /* @var Bidding $data */
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isOpen()) {
            $data->setOpen(false);
            try {
                foreach ($data->getOffers() as $offer) {
                    $offer->setDeletable(false);
                    $this->entityManager->persist($offer);
                }
                $this->entityManager->persist($data);
                $this->entityManager->flush();
            } catch (Exception $exception) {
                throw new HttpException(500, $exception->getMessage());
            }
        } else {
            throw new UnprocessableEntityHttpException("Bidding is already closed");
        }
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bid;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CloseOffer implements ProcessorInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isIsOpen()) {
            $data->setIsOpen(false);
            $bids = $this
                ->entityManager
                ->getRepository(Bid::class)
                ->findBy(["offer" => $data]);
            try {
                foreach ($bids as $bid) {
                    $bid->setIsDeletable(false);
                    $this->entityManager->persist($bid);
                }
                $this->entityManager->persist($data);
                $this->entityManager->flush();
            } catch (Exception $exception) {
                throw new HttpException(500, $exception->getMessage());
            }
        } else {
            throw new UnprocessableEntityHttpException("Offer is already closed");
        }
    }
}

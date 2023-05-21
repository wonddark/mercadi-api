<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bid;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CloseBid implements ProcessorInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws NotSupported
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isOpen()) {
            $data->setOpen(false);
            $bids = $this
                ->entityManager
                ->getRepository(Bid::class)
                ->findBy(["item" => $data]);
            try {
                foreach ($bids as $bid) {
                    $bid->setDeletable(false);
                    $this->entityManager->persist($bid);
                }
                $this->entityManager->persist($data);
                $this->entityManager->flush();
            } catch (Exception $exception) {
                throw new HttpException(500, $exception->getMessage());
            }
        } else {
            throw new UnprocessableEntityHttpException("Bids are already closed");
        }
    }
}

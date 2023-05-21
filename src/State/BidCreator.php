<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bid;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Security;

class BidCreator implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly EntityManager $entityManager,
        private readonly Security $security
    ) {
    }

    /**
     * @throws NotSupported
     */
    public function process(
        /* @var Bid $data */
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        $highestBid = $this
            ->entityManager
            ->getRepository(Bid::class)
            ->getHighestPerItem($data->getItem()->getId());

        if ($data->getQuantity() >= $highestBid->getQuantity()) {
            $user = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    "account" => $this->security->getUser()
                ]);

            $offer = $this
                ->entityManager
                ->getRepository(Item::class)
                ->find($data->getItem()->getId());

            if ($user === $offer->getUser()) {
                throw new UnprocessableEntityHttpException(
                    "We don't allow users to make bid to its own offers"
                );
            }
            $data->setUser($user);
            $offer->setHighestBid($data);
            $this->processor->process(
                $data,
                $operation,
                $uriVariables,
                $context
            );
        } else {
            throw new UnprocessableEntityHttpException(
                "Bid's quantity must be greater than or equal to the higher active bid"
            );
        }
    }
}

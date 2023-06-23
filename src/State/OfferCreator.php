<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Item;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OfferCreator implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly EntityManager      $entityManager,
        private readonly Security           $security
    )
    {
    }

    /**
     * @throws NotSupported
     */
    public function process(
        /* @var Offer $data */
        mixed     $data,
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    ): void
    {
        $bids = $data->getBids();
        $highestOffer = $bids->getHighestOffer();

        if ($data->getQuantity() >= $highestOffer) {
            $user = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    "account" => $this->security->getUser()
                ]);

            /* @var Item $item */
            $item = $this
                ->entityManager
                ->getRepository(Item::class)
                ->find($bids->getItem()->getId());

            if ($user === $item->getUser()) {
                throw new UnprocessableEntityHttpException(
                    "We don't allow users to make bid to its own items"
                );
            }

            $data->setUser($user);
            $bids->setHighestOffer($data->getQuantity());
            $this->processor->process(
                $data,
                $operation,
                $uriVariables,
                $context
            );
        } else {
            throw new UnprocessableEntityHttpException(
                "Offer's quantity must be greater than or equal to the higher active offer"
            );
        }
    }
}

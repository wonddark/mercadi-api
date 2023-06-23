<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bidding;
use App\Entity\Item;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class ItemCreator implements ProcessorInterface
{
    private ProcessorInterface $processor;
    private Security $security;
    private EntityManager $entityManager;

    public function __construct(
        ProcessorInterface $processor,
        Security $security,
        EntityManager $entityManager
    ) {
        $this->processor = $processor;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws NotSupported
     * @throws Exception
     */
    public function process(
        /* @var Item $data */
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        /* @var User $user */
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy(["account" => $this->security->getUser()]);
        $data->setUser($user);
        $this->processor->process($data, $operation, $uriVariables, $context);
        if ($data->isBidding()) {
            $bids = new Bidding();
            $bids->setItem($data);
            $bids->setHighestOffer($data->getPrice());
            $data->setBids($bids);
            $offer = new Offer();
            $offer->setBids($bids);
            $bids->addOffer($offer);
            $bids->setTotalOffers(1);
            $offer->setUser($user);
            $offer->setQuantity($data->getPrice());
            $this->entityManager->persist($offer);
        }
        try {
            $this->entityManager->flush();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 500);
        }
    }
}

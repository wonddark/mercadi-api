<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bid;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Security\Core\Security;

class OfferCreator implements ProcessorInterface
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

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy(["account" => $this->security->getUser()]);
        $data->setUser($user);
        $this->processor->process($data, $operation, $uriVariables, $context);
        $bid = new Bid();
        $bid->setOffer($data);
        $bid->setUser($user);
        $bid->setDeletable(false);
        $bid->setQuantity($data->getInitialBid());
        try {
            $this->entityManager->persist($bid);
            $this->entityManager->flush();
        } catch (Exception $exception) {
            echo "Error\n";
            var_dump($exception);
        }
    }
}

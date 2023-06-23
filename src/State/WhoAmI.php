<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WhoAmI implements ProviderInterface
{
    private Security $security;
    private EntityManager $entityManager;

    public function __construct(Security $security, EntityManager $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws NotSupported
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $securityUser = $this->security->getUser();
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy(["account" => $securityUser]);
        if ($user !== null) {
            return $user;
        } else {
            throw new NotFoundHttpException();
        }
    }
}

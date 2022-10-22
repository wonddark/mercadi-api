<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Account;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivateAccount implements ProcessorInterface
{
    private ProcessorInterface $decorated;
    private EntityManagerInterface $manager;

    public function __construct(
        ProcessorInterface     $decorated,
        EntityManagerInterface $manager
    )
    {
        $this->decorated = $decorated;
        $this->manager = $manager;
    }

    public function process(
        mixed     $data,
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    )
    {
        if (!$data->isIsActive()) {
            $account = new Account();
            $account->setEmail($data->getEmail());
            $account->setPassword($data->getPassword());
            $user = new User();
            $user->setAccount($account);
            $user->setName($data->getName());
            $user->setLastname($data->getLastName());
            $this->manager->persist($account);
            $this->manager->persist($user);
            $this->manager->flush();
            $data->setIsActive(true);
        }
        return $this->decorated->process(
            $data,
            $operation,
            $uriVariables,
            $context
        );
    }
}

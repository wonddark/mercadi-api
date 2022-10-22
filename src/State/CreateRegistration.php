<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Account;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateRegistration implements ProcessorInterface
{
    private ProcessorInterface $processor;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ProcessorInterface $decorated,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->processor = $decorated;
        $this->passwordHasher = $passwordHasher;
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ) {
        $plainPassword = $data->getPassword();
        $account = new Account();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $account,
            $plainPassword
        );
        $data->setPassword($hashedPassword);
        return $this->processor->process(
            $data,
            $operation,
            $uriVariables,
            $context
        );
    }
}

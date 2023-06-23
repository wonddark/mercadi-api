<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Account;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateRegistration implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
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

        $this->decorated->process(
            $data,
            $operation,
            $uriVariables,
            $context
        );

        $email = (new Email())
            ->to($data->getEmail())
            ->subject('Thank you for registering at Stocked!')
            ->text("We are glad to see you around. Please confirm your registration using the following code: " .
                $data->getId())
            ->html('<p>We are glad to see you around. Please confirm your registration using the following code: ' .
                $data->getId() . '</p>');

        $this->mailer->send($email);
    }
}

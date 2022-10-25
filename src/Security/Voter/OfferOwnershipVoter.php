<?php

namespace App\Security\Voter;

use App\Entity\Offer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class OfferOwnershipVoter extends Voter
{
    private Security $security;
    public const EDIT = 'OFFER_EDIT';
    public const DELETE = 'OFFER_DELETE';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Offer;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT =>
                $this->security->isGranted("ROLE_ADMIN") ||
                $subject->getUser()->etAccount()->getId() == $user->getId(),
            self::DELETE =>
                $subject->getUser()->getAccount()->getId() == $user->getId(),
            default => false,
        };
    }
}

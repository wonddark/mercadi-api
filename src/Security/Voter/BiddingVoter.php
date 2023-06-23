<?php

namespace App\Security\Voter;

use App\Entity\Bidding;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BiddingVoter extends Voter
{
    private Security $security;
    public const EDIT_OPEN_STATE = 'BIDS_EDIT_OPEN_STATE';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute == self::EDIT_OPEN_STATE
            && $subject instanceof Bidding;
    }

    /**
     * @param string $attribute
     * @param Bidding $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(
        string $attribute,
        /* @var Bidding $subject */
        mixed $subject,
        TokenInterface $token
    ): bool {
        /* @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($attribute == self::EDIT_OPEN_STATE) {
            return $this->security->isGranted("ROLE_ADMIN")
                || $subject->getItem()->getUser()->getAccount()->getId() === $user->getId();
        }

        return false;
    }
}

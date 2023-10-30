<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{

    const SHOW    = 'show';
    const EDIT    = 'edit';
    const DELETE  = 'delete';
    const BALANCE = 'balance';

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [
            self::SHOW,
            self::EDIT,
            self::DELETE,
            self::BALANCE
        ])) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $targetUser = $subject;

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::SHOW, self::EDIT, self::DELETE, => $this->security->isGranted('ROLE_ADMIN') || $user === $targetUser,
            self::BALANCE => $user === $targetUser,
            default => false,
        };
    }

}
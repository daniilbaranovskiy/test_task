<?php

namespace App\Security;

use App\Entity\Orders;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrdersVoter extends Voter
{

    const SHOW   = 'show';
    const EDIT   = 'edit';
    const DELETE = 'delete';

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
            self::DELETE
        ])) {
            return false;
        }

        if (!$subject instanceof Orders) {
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
        $order = $subject;

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::SHOW => $this->security->isGranted('ROLE_ADMIN') || ($user === $order->getUser()),
            self::EDIT, self::DELETE => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }

}
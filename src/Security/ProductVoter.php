<?php

namespace App\Security;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{

    const NEW    = 'new';
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
            self::NEW,
            self::EDIT,
            self::DELETE
        ])) {
            return false;
        }

        if (!$subject instanceof Product) {
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
        $product = $subject;

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::NEW, self::EDIT, self::DELETE, => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }

}
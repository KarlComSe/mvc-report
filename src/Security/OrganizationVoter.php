<?php

namespace App\Security;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Organization>
 */
class OrganizationVoter extends Voter
{
    private const VIEW = 'view';
    private const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Organization) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Organization $organization */
        $organization = $subject;

        if (!$organization->isUserInOrganization($user)) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT => true,
            default => false,
        };
    }
}

<?php

namespace App\Voter;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    const VIEW = "view";
    const DELETE = "delete";
    const EDIT = "edit";

    protected function supports($attribute, $subject)
    {
        // TODO: Implement supports() method.
        if (!in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])) {
            return false;
        }

        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // TODO: Implement voteOnAttribute() method.
        $user = $token -> getUser();

        if (!$user instanceof User) {
            return false;
        }

        $isOwner = $subject->getOwner() === $user;
        $isAdmin = $user->hasRole("ROLE_ADMIN");

        return $isAdmin || $isOwner;
    }

}
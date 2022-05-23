<?php

namespace App\Voter;

use App\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter extends Voter
{
    public const EDIT = 'project_edit';
    public const VIEW = 'project_view';

    protected function supports($attribute, $subject): bool
    {

        if (!in_array($attribute, [self::EDIT, self::VIEW]))
        {
            return false;
        }

        if (!$subject instanceof Project)
        {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $isAdmin =  in_array('ROLE_ADMIN', $user->getRoles());
        $isAuthor = ($subject->getAuthor() === $user->getId());

        return $isAdmin || $isAuthor;
    }
}

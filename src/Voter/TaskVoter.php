<?php

namespace App\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TaskVoter extends Voter
{

    const COMPLETE = 'complete';
    const EDIT = 'edit';
    const DELETE = 'delete';


    protected function supports($attribute, $subject)
    {

        if (!in_array($attribute, [self::COMPLETE, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Task) {
            return false;
        }

        return true;

    }

    /**
     * @param $attribute
     * @param Task $subject
     * @param TokenInterface $token
     * @return bool|void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $isAdmin =  in_array('ROLE_ADMIN', $user->getRoles());
        $isAuthor = ($subject->getAuthor() === $user);

        return $isAdmin || $isAuthor;
    }


}
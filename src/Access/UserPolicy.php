<?php

namespace Ralkage\AccountLockout\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class UserPolicy extends AbstractPolicy
{
    public function unlock(User $actor, User $user)
    {
        if ($user->isAdmin() || $user->id === $actor->id) {
            return $this->deny();
        }
    }
}

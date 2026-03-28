<?php

namespace Ralkage\AccountLockout\Event;

use Flarum\User\User;

class AccountUnlocked
{
    public User $user;
    public User $actor;

    public function __construct(User $user, User $actor)
    {
        $this->user = $user;
        $this->actor = $actor;
    }
}

<?php

namespace Ralkage\AccountLockout\Event;

use Flarum\User\User;

class AccountLocked
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

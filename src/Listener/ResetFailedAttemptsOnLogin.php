<?php

namespace Ralkage\AccountLockout\Listener;

use Flarum\User\Event\LoggedIn;

class ResetFailedAttemptsOnLogin
{
    public function handle(LoggedIn $event): void
    {
        $user = $event->user;

        if ($user->login_failed_count > 0) {
            $user->login_failed_count = 0;
            $user->save();
        }
    }
}

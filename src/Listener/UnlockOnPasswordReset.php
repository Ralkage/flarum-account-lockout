<?php

namespace Ralkage\AccountLockout\Listener;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\PasswordChanged;

class UnlockOnPasswordReset
{
    protected SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(PasswordChanged $event): void
    {
        $user = $event->user;

        if (!$user->is_locked) {
            return;
        }

        $mode = $this->settings->get('ralkage-account-lockout.lockout_mode', 'timed');

        // Only auto-unlock for timed lockouts — manual lockouts are deliberate mod/admin actions
        if ($mode !== 'timed') {
            return;
        }

        $user->is_locked = false;
        $user->locked_until = null;
        $user->locked_at = null;
        $user->login_failed_count = 0;
        $user->save();
    }
}

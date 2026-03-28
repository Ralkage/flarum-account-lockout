<?php

namespace Ralkage\AccountLockout\Listener;

use Flarum\User\Event\Saving;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Ralkage\AccountLockout\Event\AccountUnlocked;

class SaveLockoutToDatabase
{
    protected Dispatcher $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function handle(Saving $event): void
    {
        $attributes = Arr::get($event->data, 'attributes', []);

        if (!array_key_exists('isLocked', $attributes)) {
            return;
        }

        $user = $event->user;
        $actor = $event->actor;

        $actor->assertCan('unlock', $user);

        if (!$attributes['isLocked']) {
            // Unlocking the user
            $user->is_locked = false;
            $user->locked_until = null;
            $user->locked_at = null;
            $user->login_failed_count = 0;

            $this->events->dispatch(new AccountUnlocked($user, $actor));
        }
    }
}

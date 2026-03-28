<?php

namespace Ralkage\AccountLockout;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\User\User;

class AddUserLockoutAttributes
{
    public function __invoke(UserSerializer $serializer, User $user)
    {
        $attributes = [];
        $canUnlock = $serializer->getActor()->can('unlock', $user);

        if ($canUnlock) {
            $attributes['isLocked'] = (bool) $user->is_locked;
            $attributes['lockedUntil'] = $serializer->formatDate($user->locked_until);
            $attributes['lockedAt'] = $serializer->formatDate($user->locked_at);
            $attributes['loginFailedCount'] = (int) $user->login_failed_count;
        }

        $attributes['canUnlock'] = $canUnlock;

        return $attributes;
    }
}

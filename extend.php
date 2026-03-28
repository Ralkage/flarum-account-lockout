<?php

namespace Ralkage\AccountLockout;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Extend;
use Flarum\User\Event\LoggedIn;
use Flarum\User\Event\PasswordChanged;
use Flarum\User\Event\Saving;
use Flarum\User\User;
use Ralkage\AccountLockout\Access\UserPolicy;
use Ralkage\AccountLockout\Exception\AccountLockedHandler;
use Ralkage\AccountLockout\Exception\AccountLockedException;
use Ralkage\AccountLockout\Exception\NotAuthenticatedWithAttemptsException;
use Ralkage\AccountLockout\Exception\NotAuthenticatedWithAttemptsHandler;
use Ralkage\AccountLockout\Listener\ResetFailedAttemptsOnLogin;
use Ralkage\AccountLockout\Listener\SaveLockoutToDatabase;
use Ralkage\AccountLockout\Listener\UnlockOnPasswordReset;
use Ralkage\AccountLockout\Middleware\CheckAccountLockout;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/resources/less/admin.less'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Model(User::class))
        ->cast('is_locked', 'boolean')
        ->cast('locked_until', 'datetime')
        ->cast('locked_at', 'datetime')
        ->cast('login_failed_count', 'integer'),

    (new Extend\ApiSerializer(UserSerializer::class))
        ->attributes(AddUserLockoutAttributes::class),

    (new Extend\Settings())
        ->default('ralkage-account-lockout.max_attempts', 5)
        ->default('ralkage-account-lockout.lockout_duration', 15)
        ->default('ralkage-account-lockout.lockout_mode', 'timed'),

    (new Extend\Middleware('api'))
        ->add(CheckAccountLockout::class),

    (new Extend\Event())
        ->listen(Saving::class, SaveLockoutToDatabase::class)
        ->listen(LoggedIn::class, ResetFailedAttemptsOnLogin::class)
        ->listen(PasswordChanged::class, UnlockOnPasswordReset::class),

    (new Extend\Policy())
        ->modelPolicy(User::class, UserPolicy::class),

    (new Extend\ErrorHandling())
        ->status('account_locked', 423)
        ->handler(AccountLockedException::class, AccountLockedHandler::class)
        ->handler(NotAuthenticatedWithAttemptsException::class, NotAuthenticatedWithAttemptsHandler::class),
];

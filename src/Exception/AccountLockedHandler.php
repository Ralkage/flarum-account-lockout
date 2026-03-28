<?php

namespace Ralkage\AccountLockout\Exception;

use Flarum\Foundation\ErrorHandling\HandledError;

class AccountLockedHandler
{
    public function handle(AccountLockedException $e): HandledError
    {
        return (new HandledError($e, 'account_locked', 423))
            ->withDetails([
                [
                    'retry_after' => $e->getRetryAfter(),
                ],
            ]);
    }
}

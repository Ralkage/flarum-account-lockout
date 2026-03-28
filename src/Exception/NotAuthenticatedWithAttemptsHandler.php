<?php

namespace Ralkage\AccountLockout\Exception;

use Flarum\Foundation\ErrorHandling\HandledError;

class NotAuthenticatedWithAttemptsHandler
{
    public function handle(NotAuthenticatedWithAttemptsException $e): HandledError
    {
        return (new HandledError($e, 'not_authenticated', 401))
            ->withDetails([
                [
                    'remaining_attempts' => $e->getRemainingAttempts(),
                    'max_attempts' => $e->getMaxAttempts(),
                ],
            ]);
    }
}

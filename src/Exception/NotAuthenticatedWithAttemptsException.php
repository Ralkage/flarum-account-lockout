<?php

namespace Ralkage\AccountLockout\Exception;

use Exception;

class NotAuthenticatedWithAttemptsException extends Exception
{
    protected int $remainingAttempts;
    protected int $maxAttempts;

    public function __construct(int $remainingAttempts, int $maxAttempts)
    {
        $this->remainingAttempts = $remainingAttempts;
        $this->maxAttempts = $maxAttempts;

        parent::__construct('Invalid credentials.');
    }

    public function getRemainingAttempts(): int
    {
        return $this->remainingAttempts;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }
}

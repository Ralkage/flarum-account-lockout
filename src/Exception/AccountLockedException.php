<?php

namespace Ralkage\AccountLockout\Exception;

use Exception;

class AccountLockedException extends Exception
{
    protected ?int $retryAfter;

    public function __construct(?int $retryAfter = null, ?string $message = null)
    {
        $this->retryAfter = $retryAfter;

        $message ??= $retryAfter
            ? "Account is locked. Try again in {$retryAfter} minutes."
            : 'Account is locked. Contact an administrator.';

        parent::__construct($message);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}

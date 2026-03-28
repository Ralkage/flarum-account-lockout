<?php

use Flarum\Database\Migration;

return Migration::addColumns('users', [
    'login_failed_count' => ['integer', 'unsigned' => true, 'default' => 0],
    'is_locked' => ['boolean', 'default' => false],
    'locked_until' => ['dateTime', 'nullable' => true],
    'locked_at' => ['dateTime', 'nullable' => true],
]);

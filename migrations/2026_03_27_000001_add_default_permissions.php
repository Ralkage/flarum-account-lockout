<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'user.unlock' => Group::MODERATOR_ID,
]);

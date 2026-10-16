<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->assigned_to === $user->id;
    }
}

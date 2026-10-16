<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function list(): Collection
    {
        return User::query()->withCount('projects')->orderBy('name')->get();
    }
}

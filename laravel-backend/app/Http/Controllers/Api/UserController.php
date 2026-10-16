<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $users)
    {
    }

    public function index(): JsonResponse
    {
        return $this->success(UserResource::collection($this->users->list()));
    }
}

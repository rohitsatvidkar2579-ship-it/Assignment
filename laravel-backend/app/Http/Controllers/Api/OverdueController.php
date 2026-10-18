<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Task;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OverdueController extends Controller
{
    use ApiResponse;

    public function sync(): JsonResponse
    {
        try {
            Http::baseUrl(config('services.django.url'))
                ->withHeaders(['X-Internal-Token' => config('services.django.token')])
                ->acceptJson()
                ->timeout(5)
                ->post(config('services.django.endpoints.sync'))
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Overdue sync failed: '.$e->getMessage());
        }

        return $this->success(null, 'Overdue sync triggered.');
    }

    public function transition(Request $request, Task $task): JsonResponse
    {
        Gate::authorize('updateStatus', $task);

        $data = $request->validate([
            'status' => ['required', Rule::in(TaskStatus::values())],
        ]);

        try {
            $response = Http::baseUrl(config('services.django.url'))
                ->withHeaders(['X-Internal-Token' => config('services.django.token')])
                ->acceptJson()
                ->timeout(5)
                ->post(str_replace('{task}', $task->id, config('services.django.endpoints.transition')), [
                    'status' => $data['status'],
                    'actor_role' => $request->user()->role,
                ]);
        } catch (ConnectionException $e) {
            throw new DomainException('Overdue service is unavailable.', 503);
        }

        if ($response->failed()) {
            throw new DomainException(
                $response->json('message') ?? 'Status transition rejected.',
                $response->status() >= 400 && $response->status() < 500 ? $response->status() : 502,
            );
        }

        return $this->success(null, 'Task transition applied.');
    }
}

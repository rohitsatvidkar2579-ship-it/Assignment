<?php

namespace App\Services;

use App\Exceptions\DomainException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OverdueClient
{
    public function sync(): void
    {
        try {
            $this->request()->post('/api/overdue/sync/')->throw();
        } catch (\Throwable $e) {
            Log::warning('Overdue sync failed: '.$e->getMessage());
        }
    }

    public function transition(int $taskId, string $status, string $role): void
    {
        try {
            $response = $this->request()->post("/api/tasks/{$taskId}/transition/", [
                'status' => $status,
                'actor_role' => $role,
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
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(config('services.django.url'))
            ->withHeaders(['X-Internal-Token' => config('services.django.token')])
            ->acceptJson()
            ->timeout(5);
    }
}

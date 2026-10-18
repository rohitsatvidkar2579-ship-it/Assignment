<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Traits\ApiResponse;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['status', 'project_id']);

        app(OverdueController::class)->sync();

        $tasks = Task::query()
            ->with(['assignee', 'project'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->orderBy('due_date')
            ->get();

        return $this->success(TaskResource::collection($tasks));
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate($this->storeRules());

        $task = $project->tasks()->create($data + [
            'created_by' => $request->user()->id,
            'status' => 'TODO',
        ]);

        app(OverdueController::class)->sync();

        $task = $task->refresh()->load(['assignee', 'project']);

        return $this->success(new TaskResource($task), 'Task created.', 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $task->update($request->validate($this->updateRules()));
        app(OverdueController::class)->sync();

        $task = $task->refresh()->load(['assignee', 'project']);

        return $this->success(new TaskResource($task), 'Task updated.');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        Gate::authorize('updateStatus', $task);

        $request->validate($this->statusRules());

        app(OverdueController::class)->transition($request, $task);

        $task = $task->refresh()->load(['assignee', 'project']);

        return $this->success(new TaskResource($task), 'Task status updated.');
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return $this->success(null, 'Task deleted.');
    }

    private function storeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'due_date' => ['required', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }

    private function updateRules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'due_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }

    private function statusRules(): array
    {
        return [
            'status' => ['required', Rule::in(TaskStatus::values())],
        ];
    }
}

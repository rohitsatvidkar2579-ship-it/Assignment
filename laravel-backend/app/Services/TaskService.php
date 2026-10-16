<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function __construct(private OverdueClient $overdue)
    {
    }

    public function list(User $user, array $filters = []): Collection
    {
        $this->overdue->sync();

        return Task::query()
            ->with(['assignee', 'project'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->orderBy('due_date')
            ->get();
    }

    public function create(Project $project, array $data, User $user): Task
    {
        $task = $project->tasks()->create($data + [
            'created_by' => $user->id,
            'status' => 'TODO',
        ]);

        $this->overdue->sync();

        return $task->refresh()->load(['assignee', 'project']);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        $this->overdue->sync();

        return $task->refresh()->load(['assignee', 'project']);
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function updateStatus(Task $task, string $status, User $actor): Task
    {
        $this->overdue->transition($task->id, $status, $actor->role);

        return $task->refresh()->load(['assignee', 'project']);
    }
}

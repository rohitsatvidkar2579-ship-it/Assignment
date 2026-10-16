<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function __construct(private OverdueClient $overdue)
    {
    }

    public function list(User $user): Collection
    {
        $this->overdue->sync();

        return Project::query()
            ->with(['creator', 'members'])
            ->withCount([
                'members',
                'tasks' => fn ($q) => $user->isAdmin() ? $q : $q->where('assigned_to', $user->id),
            ])
            ->when(! $user->isAdmin(), fn ($q) => $q->where(
                fn ($w) => $w->whereHas('members', fn ($m) => $m->whereKey($user->id))
                    ->orWhereHas('tasks', fn ($t) => $t->where('assigned_to', $user->id))
            ))
            ->latest()
            ->get();
    }

    public function show(Project $project, User $user): Project
    {
        $this->overdue->sync();

        return $project->load([
            'creator',
            'members',
            'tasks' => fn ($q) => $q
                ->with('assignee')
                ->when(! $user->isAdmin(), fn ($t) => $t->where('assigned_to', $user->id))
                ->orderBy('due_date'),
        ]);
    }

    public function create(array $data, User $user): Project
    {
        $memberIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $project = Project::create($data + ['created_by' => $user->id]);
        $project->members()->sync($memberIds);

        return $project->load(['creator', 'members'])->loadCount(['members', 'tasks']);
    }

    public function update(Project $project, array $data): Project
    {
        $memberIds = $data['user_ids'] ?? null;
        unset($data['user_ids']);

        $project->update($data);

        if ($memberIds !== null) {
            $project->members()->sync($memberIds);
        }

        return $project->load(['creator', 'members'])->loadCount(['members', 'tasks']);
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}

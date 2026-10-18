<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\ApiResponse;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        app(OverdueController::class)->sync();

        $projects = Project::query()
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

        return $this->success(ProjectResource::collection($projects));
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        app(OverdueController::class)->sync();

        $project->load([
            'creator',
            'members',
            'tasks' => fn ($q) => $q
                ->with('assignee')
                ->when(! $user->isAdmin(), fn ($t) => $t->where('assigned_to', $user->id))
                ->orderBy('due_date'),
        ]);

        return $this->success(new ProjectResource($project));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->storeRules());

        $memberIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $project = Project::create($data + ['created_by' => $request->user()->id]);
        $project->members()->sync($memberIds);

        $project->load(['creator', 'members'])->loadCount(['members', 'tasks']);

        return $this->success(new ProjectResource($project), 'Project created.', 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate($this->updateRules());

        $memberIds = $data['user_ids'] ?? null;
        unset($data['user_ids']);

        $project->update($data);

        if ($memberIds !== null) {
            $project->members()->sync($memberIds);
        }

        $project->load(['creator', 'members'])->loadCount(['members', 'tasks']);

        return $this->success(new ProjectResource($project), 'Project updated.');
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return $this->success(null, 'Project deleted.');
    }

    private function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\ApiResponse;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $projects)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->success(ProjectResource::collection($this->projects->list($request->user())));
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        return $this->success(new ProjectResource($this->projects->show($project, $request->user())));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projects->create($request->validated(), $request->user());

        return $this->success(new ProjectResource($project), 'Project created.', 201);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        return $this->success(new ProjectResource($this->projects->update($project, $request->validated())), 'Project updated.');
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->projects->delete($project);

        return $this->success(null, 'Project deleted.');
    }
}

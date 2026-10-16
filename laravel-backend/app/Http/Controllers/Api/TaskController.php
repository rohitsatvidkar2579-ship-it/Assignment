<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Http\Traits\ApiResponse;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(private TaskService $tasks)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->tasks->list($request->user(), $request->only(['status', 'project_id']));

        return $this->success(TaskResource::collection($tasks));
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $this->tasks->create($project, $request->validated(), $request->user());

        return $this->success(new TaskResource($task), 'Task created.', 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        return $this->success(new TaskResource($this->tasks->update($task, $request->validated())), 'Task updated.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('updateStatus', $task);

        $task = $this->tasks->updateStatus($task, $request->validated('status'), $request->user());

        return $this->success(new TaskResource($task), 'Task status updated.');
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->tasks->delete($task);

        return $this->success(null, 'Task deleted.');
    }
}

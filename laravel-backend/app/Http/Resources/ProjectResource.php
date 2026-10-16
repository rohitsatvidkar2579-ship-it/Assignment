<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'tasks_count' => $this->whenCounted('tasks'),
            'members_count' => $this->whenCounted('members'),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'is_completed' => (bool) $this->is_completed,
            'priority' => $this->priority,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

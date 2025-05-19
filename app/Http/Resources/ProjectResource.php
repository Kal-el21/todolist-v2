<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description, // ✅ Tambahkan ini
            'color' => $this->color,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),

            'tasks_count' => $this->whenCounted('tasks'),
            'completed_tasks_count' => $this->completed_tasks_count, // ✅ Tambahkan ini
            'pending_tasks_count' => $this->pending_tasks_count,     // ✅ Tambahkan ini
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

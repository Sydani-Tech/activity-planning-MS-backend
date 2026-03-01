<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'department_id' => $this->department_id,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'focal_person_id' => $this->focal_person_id,
            'focal_person' => new UserResource($this->whenLoaded('focalPerson')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'attachments' => $this->attachments, // Handle attachments array or JSON
            'status_updates' => $this->statusUpdates, // Assuming a relationship exists or JSON
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

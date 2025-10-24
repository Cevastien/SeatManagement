<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'queue_number' => $this->queue_number,
            'name' => $this->name,
            'party_size' => $this->party_size,
            'contact_number' => $this->contact_number,
            'is_priority' => $this->is_priority,
            'priority_type' => $this->priority_type,
            'status' => $this->status,
            'table_id' => $this->table_id,
            'estimated_wait_time' => $this->estimated_wait_time,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

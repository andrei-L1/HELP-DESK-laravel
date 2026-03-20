<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'action'     => $this->action,
            'old_value'  => $this->old_value,
            'new_value'  => $this->new_value,
            'user_name'  => $this->user ? trim($this->user->first_name . ' ' . $this->user->last_name) : 'System',
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

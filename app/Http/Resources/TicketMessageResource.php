<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'body'        => $this->body,
            'is_internal' => (bool) $this->is_internal,
            'is_mine'     => $this->user_id === $request->user()->id,
            'author'      => $this->user ? trim($this->user->first_name . ' ' . $this->user->last_name) : 'Unknown',
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}

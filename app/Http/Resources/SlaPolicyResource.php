<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'response_time'       => (int) $this->response_time,
            'resolution_time'     => (int) $this->resolution_time,
            'business_hours_only' => (bool) $this->business_hours_only,
        ];
    }
}

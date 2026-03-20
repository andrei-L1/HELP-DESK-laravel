<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAttachmentResource extends JsonResource
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
            'file_name'   => $this->file_name,
            'file_size'   => $this->file_size,
            'uploaded_at' => $this->uploaded_at?->toDateTimeString(),
            'uploaded_by' => $this->uploaded_by,
            'download_url'=> $this->when($request->user()->role->name === 'user', 
                fn() => route('user.tickets.attachments.download', [$this->ticket_id, $this->id])),
        ];
    }
}

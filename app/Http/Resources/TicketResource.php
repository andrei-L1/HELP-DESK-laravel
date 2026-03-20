<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roleName = $request->user()?->role?->name;

        return [
            'id'                 => $this->id,
            'ticket_number'      => $this->ticket_number,
            'subject'            => $this->subject,
            'status'             => $this->status->name ?? 'Unknown',
            'status_id'          => $this->status_id,
            'status_color'       => $this->status->color_hex ?? '#64748b',
            'priority'           => $this->priority->name ?? 'Unknown',
            'priority_id'        => $this->priority_id,
            'priority_color'     => $this->priority->color_hex ?? '#64748b',
            'category_id'        => $this->category_id,
            'category_title'     => $this->category->title ?? null,
            'department_id'      => $this->department_id,
            'department_name'    => $this->department->name ?? null,
            'created_by'         => $this->creator ? trim($this->creator->first_name . ' ' . $this->creator->last_name) : 'Unknown',
            'created_by_id'      => $this->created_by,
            'assigned_to'        => $this->assignee ? trim($this->assignee->first_name . ' ' . $this->assignee->last_name) : 'Unassigned',
            'assigned_to_id'     => $this->assigned_to,
            'assigned_to_name'   => $this->assignee ? trim($this->assignee->first_name . ' ' . $this->assignee->last_name) : null,
            'created_at'         => $this->created_at?->toDateTimeString(),
            'updated_at'         => $this->updated_at?->toDateTimeString(),
            
            // Detailed fields (only for show pages)
            'description'        => $this->when($request->routeIs('*.show'), $this->description),
            'due_at'             => $this->when($this->due_at, fn() => $this->due_at?->toDateTimeString()),
            'first_response_at'  => $this->when($this->first_response_at, fn() => $this->first_response_at?->toDateTimeString()),
            'resolved_at'        => $this->when($this->resolved_at, fn() => $this->resolved_at?->toDateTimeString()),
            'closed_at'          => $this->when($this->closed_at, fn() => $this->closed_at?->toDateTimeString()),
            'sla_policy_id'      => $this->sla_policy_id,

            // Relationships
            'messages'           => TicketMessageResource::collection($this->whenLoaded('messages')),
            'attachments'        => TicketAttachmentResource::collection($this->whenLoaded('attachments')),
            'activity_logs'      => TicketActivityLogResource::collection($this->whenLoaded('activityLogs')),
            'sla_policy'         => new SlaPolicyResource($this->whenLoaded('slaPolicy')),
        ];
    }
}

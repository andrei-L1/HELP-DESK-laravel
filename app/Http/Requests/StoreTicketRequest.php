<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // auth handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Fetch all valid priority names from DB (case-insensitive lower)
        $validPriorities = DB::table('ticket_priorities')
            ->whereNull('deleted_at')
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->all();

        return [
            'subject'       => 'required|string|max:200',
            'description'   => 'required|string|max:10000',
            'priority'      => ['required', 'string', Rule::in($validPriorities)],
            'category_id'   => 'nullable|integer|exists:ticket_categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ];
    }

    /**
     * Resolve priority_id by matching the submitted lowercase name against the DB.
     */
    public function getPriorityId(): ?int
    {
        $priorityName = ucfirst(strtolower($this->input('priority')));
        return DB::table('ticket_priorities')
            ->whereRaw('LOWER(name) = ?', [strtolower($this->input('priority'))])
            ->value('id');
    }

    /**
     * Get the "Open" status id.
     */
    public function getStatusId(): ?int
    {
        return DB::table('ticket_statuses')->where('name', 'Open')->value('id');
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Use middleware for auth
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject'       => 'required|string|max:200',
            'description'   => 'required|string',
            'priority'      => 'required|string|in:low,medium,high',
            'category_id'   => 'nullable|integer|exists:ticket_categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ];
    }

    public function getPriorityId(): ?int
    {
        $priorityName = ucfirst(strtolower($this->input('priority')));
        return DB::table('ticket_priorities')->where('name', $priorityName)->value('id');
    }

    public function getStatusId(): ?int
    {
        return DB::table('ticket_statuses')->where('name', 'Open')->value('id');
    }
}

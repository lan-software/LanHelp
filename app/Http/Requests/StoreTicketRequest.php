<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'category' => ['nullable', 'string', 'max:100'],
            'context_snapshot' => ['nullable', 'array'],
            'context_snapshot.source_product' => ['nullable', 'string', 'max:100'],
            'context_snapshot.source_domain' => ['nullable', 'string', 'max:100'],
            'context_snapshot.event_reference' => ['nullable', 'string', 'max:100'],
            'context_snapshot.seat_reference' => ['nullable', 'string', 'max:100'],
            'context_snapshot.tournament_reference' => ['nullable', 'string', 'max:100'],
            'context_snapshot.order_reference' => ['nullable', 'string', 'max:100'],
            'context_snapshot.team_reference' => ['nullable', 'string', 'max:100'],
            'context_snapshot.links' => ['nullable', 'array'],
            'context_snapshot.extra' => ['nullable', 'array'],
        ];
    }
}

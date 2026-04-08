<?php

namespace Illimi\Communication\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illimi\Communication\Enums\ConversationTypeEnum;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', Rule::in(ConversationTypeEnum::values())],
            'title' => ['nullable', 'string', 'max:255'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['required', 'string', 'exists:users,id', 'distinct'],
        ];
    }
}

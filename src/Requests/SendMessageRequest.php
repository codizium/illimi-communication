<?php

namespace Illimi\Communication\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'is_system_message' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $body = trim((string) $this->input('body', ''));
            $attachments = $this->file('attachments', []);

            if ($body === '' && empty($attachments)) {
                $validator->errors()->add('body', 'A message body or at least one attachment is required.');
            }
        });
    }
}

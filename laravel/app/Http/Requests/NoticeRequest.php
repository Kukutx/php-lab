<?php

namespace App\Http\Requests;

use App\Enums\NoticeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request: validation lives in its own class, so the controller only
 * receives data that is already valid. On failure Laravel redirects back
 * with the errors and the old input automatically (422 for JSON requests).
 */
class NoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // no authentication in this exercise
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'body' => ['required', 'string', 'min:20'],
            'category' => ['required', Rule::enum(NoticeCategory::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /** Field names used in error messages ("The text field is required."). */
    public function attributes(): array
    {
        return ['body' => 'text', 'published_at' => 'publish date'];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'body.min' => 'The notice text should be at least :min characters long.',
        ];
    }
}

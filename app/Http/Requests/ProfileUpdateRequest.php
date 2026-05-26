<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $domain = '@'.config('app.allowed_email_domain');
                    if (! str_ends_with(strtolower((string) $value), $domain)) {
                        $fail('Email must be a CUA address (@'.config('app.allowed_email_domain').').');
                    }
                },
            ],
        ];
    }
}

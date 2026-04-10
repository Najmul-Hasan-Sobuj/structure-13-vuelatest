<?php

namespace App\Http\Requests\Settings;

use App\Models\MailSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailSettingsUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('host') === '') {
            $this->merge(['host' => null]);
        }

        if ($this->input('encryption') === '') {
            $this->merge(['encryption' => null]);
        }

        if ($this->input('port') === '' || $this->input('port') === null) {
            $this->merge(['port' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $existing = MailSetting::query()->first();
        $hasStoredPassword = $existing !== null
            && $existing->password !== null
            && $existing->password !== '';

        return [
            'mailer' => ['required', 'string', 'in:smtp'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($hasStoredPassword): bool {
                    if (! filled($this->input('host'))) {
                        return false;
                    }

                    return ! $hasStoredPassword;
                }),
            ],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}

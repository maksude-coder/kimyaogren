<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Registration step 1 (design § 08): name, e-mail and password.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,filter', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::min(8)->numbers(), 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Adını yaz.',
            'name.max' => 'Ad en fazla :max karakter olabilir.',
            'email.required' => 'E-posta adresini yaz.',
            'email.email' => 'E-posta adresi eksik görünüyor (ör. elif@gmail.com)',
            'email.max' => 'E-posta adresi çok uzun.',
            'email.unique' => 'Bu e-posta ile bir hesap var. Giriş yapmayı dene.',
            'password.required' => 'Bir şifre belirle.',
            'password.max' => 'Şifre çok uzun.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }
}

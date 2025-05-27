<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 
                'string', 
                'min:5',           // Mínimo 5 caracteres
                'max:50',          // Máximo 50 caracteres
                Rule::unique(User::class)->ignore($this->user()->id),
                'regex:/^[a-zA-Z0-9_]+$/' // Solo letras, números y guiones bajos
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'El username es obligatorio.',
            'username.min' => 'El username debe tener al menos 5 caracteres.',
            'username.max' => 'El username no puede tener más de 50 caracteres.',
            'username.unique' => 'Este username ya está en uso.',
            'username.regex' => 'El username solo puede contener letras, números y guiones bajos.',
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}

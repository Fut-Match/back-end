<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização de usuário
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => [
                'sometimes',
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    /**
     * Mensagens customizadas em português
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.string' => 'O nome deve ser uma string válida',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres',
            
            'email.required' => 'O email é obrigatório',
            'email.string' => 'O email deve ser uma string válida',
            'email.email' => 'O email deve ter um formato válido',
            'email.max' => 'O email não pode ter mais de 255 caracteres',
            'email.unique' => 'Este email já está cadastrado no sistema',
            
            'password.required' => 'A senha é obrigatória',
            'password.string' => 'A senha deve ser uma string válida',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
        ];
    }

    /**
     * Personaliza os atributos para as mensagens de validação
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'senha',
        ];
    }

    /**
     * Prepara os dados para validação
     * Remove campos vazios/nulos para não interferirem na validação 'sometimes'
     */
    protected function prepareForValidation(): void
    {
        $this->merge(array_filter($this->all(), function ($value) {
            return $value !== null && $value !== '';
        }));
    }
}

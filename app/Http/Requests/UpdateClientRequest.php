<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' permite updates parciales (PATCH) sin exigir todos los campos.
            // Ojo: aquí NO repito required_without:ruc/dni como en el Store. En un update
            // parcial, si el front no manda ninguno de los dos, no podemos saber si el otro
            // ya existe en BD sin una regla custom que consulte el registro actual. Si te
            // importa impedir que un update deje a un cliente sin dni Y sin ruc, dímelo y
            // te agrego una regla que valide contra $this->client->dni / ->ruc.
            'dni' => ['sometimes', 'nullable', 'string', 'max:8', Rule::unique('clients', 'dni')->ignore($this->client)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'ruc' => ['sometimes', 'nullable', 'string', 'max:11', Rule::unique('clients', 'ruc')->ignore($this->client)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}

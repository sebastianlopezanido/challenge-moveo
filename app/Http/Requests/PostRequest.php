<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Si es un método POST (creación), `title` y `content` son obligatorios
        // Si es un método PATCH o PUT (actualización), son opcionales
        return [
            'title' => $this->isMethod('post') ? 'required|string|max:255' : 'sometimes|required|string|max:255',
            'content' => $this->isMethod('post') ? 'required|string' : 'sometimes|required|string',
        ];
    }
}

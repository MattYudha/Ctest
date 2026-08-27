<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We assume auth middleware handles user auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => 'required|array|min:' . config('face.enrollment_min_photos', 5),
            'images.*' => 'required|image|mimes:' . implode(',', config('face.allowed_extensions', ['jpg','jpeg','png'])) . '|max:' . config('face.max_file_size', 5120),
        ];
    }
}

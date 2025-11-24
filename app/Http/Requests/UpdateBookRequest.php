<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'publication_date' => 'sometimes|required|integer|min:1000|max:' . date('Y'),
            'author_id' => 'sometimes|required|integer|exists:authors,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'The book title is required.',
            'title.string' => 'The book title must be a string.',
            'title.max' => 'The book title may not be greater than 255 characters.',
            'publication_date.required' => 'The publication date is required.',
            'publication_date.integer' => 'The publication date must be a valid year.',
            'publication_date.min' => 'The publication date must be a valid year (minimum 1000).',
            'publication_date.max' => 'The publication date cannot be in the future.',
            'author_id.required' => 'The author ID is required.',
            'author_id.integer' => 'The author ID must be an integer.',
            'author_id.exists' => 'The selected author does not exist.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Http\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ArticleRequest extends FormRequest
{
    use ApiResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return
            [
                'search' => 'nullable|string|min:2',
                'source' => 'nullable|string',
                'category' => 'nullable|string',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'sort_by' => 'nullable|string|in:publishedAt,title',
                'sort_direction' => 'nullable|string|in:asc,desc',
                'platform' => 'nullable|string|in:news api,guardian,new york times',
                'per_page' => 'nullable|integer|min:1|max:50'
            ];
    }

    public function messages(): array
    {
        return [
            'search.min' => 'The search query must be at least 2 characters long.',
            'source.in' => 'The source must be one of the following: news api, guardian, new york times.',
            'to_date.after_or_equal' => 'The to date must be after or equal to the from date.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError($validator->errors()->toArray())
        );
    }
}

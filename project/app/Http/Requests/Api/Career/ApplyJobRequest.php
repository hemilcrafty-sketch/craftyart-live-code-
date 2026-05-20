<?php

namespace App\Http\Requests\Api\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplyJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_id' => 'nullable|integer|exists:job_openings,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:64',
            'resume_file' => 'required_without:resume_link|nullable|file|mimes:pdf,doc,docx|max:2048',
            'resume_link' => 'required_without:resume_file|nullable|string|max:255',
            'portfolio_link' => 'nullable|url|max:255',
            'cover_letter' => 'nullable|string|max:5000',
        ];
    }
    protected function prepareForValidation()
    {
        if ((int) $this->job_id === 0) {
            $this->merge([
                'job_id' => null
            ]);
        }
    }
    public function messages(): array
    {
        return [
            'job_id.exists' => 'Selected job is invalid',

            'name.required' => 'Name is required',

            'email.required' => 'Email is required',
            'email.email' => 'Enter a valid email address',

            'phone.required' => 'Phone number is required',

            'resume_file.required_without' => 'Either resume file or resume link is required',
            'resume_file.mimes' => 'Resume must be PDF, DOC, or DOCX',
            'resume_file.max' => 'Resume size must be less than 2MB',

            'resume_link.required_without' => 'Either resume link or resume file is required',
            'resume_link.max' => 'Resume link cannot exceed 255 characters',

            'portfolio_link.url' => 'Portfolio link must be a valid URL',

            'cover_letter.max' => 'Cover letter cannot exceed 5000 characters',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];

        foreach ($validator->errors()->toArray() as $field => $messages) {
            $errors[] = [
                'field' => $field,
                'message' => $messages[0],
            ];
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422)
        );
    }
}
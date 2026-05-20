<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUpdatePageRequest extends FormRequest
{
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
        $pageId = $this->input('id', null);
        return [
            'page_slug' => 'required|unique:special_pages,page_slug,' . $pageId,
            'meta_title' => 'required',
            'title' => 'required',
            'breadcrumb' => 'required',
            'colors' => 'required',
            // 'button' => 'required',
            // 'button_link' => 'required|url',
            'meta_desc' => 'required|string',
            'description' => 'required|string',
        ];
    }
}
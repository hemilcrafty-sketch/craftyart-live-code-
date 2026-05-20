<?php

namespace App\Http\Requests;

use App\Http\Controllers\Utils\ContentManager;
use App\Models\JobOpening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class JobOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:64',
            'department' => 'nullable|string|max:100',
            'experience' => 'required|string|max:100',
            'salary_range' => 'nullable|string|max:100',
            'description' => 'required|string',
            'responsibilities' => 'nullable|array',
            'requirements' => 'nullable|array',
            'perks' => 'nullable|array',
            'tools' => 'nullable|array',
            'interview_steps' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_actively_hiring' => 'nullable|boolean',
            'slug' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
        ];
    }

    /**
     * Get processed data ready for database insertion.
     */
    public function processedData(?JobOpening $job = null): array
    {
        $data = $this->validated();
        $path = 'uploadedFiles/job_openings/';

        // Handle booleans
        $data['is_active'] = $this->boolean('is_active');
        $data['is_actively_hiring'] = $this->boolean('is_actively_hiring');

        // Handle Main Icon
        $data['icon'] = ContentManager::saveImageToPath($this->input('icon'), $path . Str::random(20) . '_' . time());

        // Process Simple Arrays
        foreach (['responsibilities', 'requirements'] as $key) {
            $data[$key] = array_values(array_filter((array)($data[$key] ?? []), fn($v) => !empty(trim((string)$v))));
        }

        // Process Media Arrays
        $data['perks'] = $this->processMediaArray((array)($data['perks'] ?? []), $path);
        $data['tools'] = $this->processMediaArray((array)($data['tools'] ?? []), $path);

        // Process Interview Steps
        $data['interview_steps'] = array_values(array_filter((array)($data['interview_steps'] ?? []), function($step) {
            return !empty(trim((string)($step['title'] ?? ''))) || !empty(trim((string)($step['description'] ?? '')));
        }));

        return $data;
    }

    private function processMediaArray(array $items, string $path): array
    {
        $results = [];
        foreach ($items as $index => $item) {
            $icon = ContentManager::saveImageToPath($item['icon'] ?? null, $path . Str::random(20) . '_' . time());
            
            $title = trim($item['title'] ?? '');
            if ($title || $icon) {
                $results[] = ['icon' => $icon, 'title' => $title];
            }
        }
        return $results;
    }
}

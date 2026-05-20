<?php

namespace App\Http\Controllers;

use App\Models\JobOpening;
use App\Http\Controllers\Utils\ContentManager;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Utils\ApiController;

class JobOpeningApiController extends ApiController
{
    /**
     * Fetch a single active job by slug.
     */
    public function show(string $slug): array|string
    {
        $job = JobOpening::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$job) {
            return $this->failed(statusCode: 404, msg: 'Job not found.');
        }

        return $this->successed(msg: 'Job details loaded', datas: [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'icon' => ContentManager::getStorageLink($job->icon),
                'location' => $job->location,
                'type' => $job->type,
                'department' => $job->department,
                'experience' => $job->experience,
                'salary_range' => $job->salary_range,
                'description' => $job->description,
                'responsibilities' => (array)($job->responsibilities ?? []),
                'requirements' => (array)($job->requirements ?? []),
                'perks' => array_map(function ($perk) {
                    return [
                        'title' => $perk['title'] ?? (is_string($perk) ? $perk : ''),
                        'icon' => ContentManager::getStorageLink($perk['icon'] ?? null),
                    ];
                }, (array)($job->perks ?? [])),
                'tools' => array_map(function ($tool) {
                    return [
                        'title' => $tool['title'] ?? (is_string($tool) ? $tool : ''),
                        'icon' => ContentManager::getStorageLink($tool['icon'] ?? null),
                    ];
                }, (array)($job->tools ?? [])),
                'interview_steps' => (array)($job->interview_steps ?? []),
                'is_actively_hiring' => (bool)$job->is_actively_hiring,
            ]
        ]);
    }
}
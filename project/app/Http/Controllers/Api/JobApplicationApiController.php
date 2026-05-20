<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Career\ApplyJobRequest;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class JobApplicationApiController extends ApiController
{
    public function apply(ApplyJobRequest $request): array|string
    {
        $data = $request->validated();

        $jobId = $data['job_id'] ?? null;
        $alreadyApplied = JobApplication::where('job_id', $jobId)
            ->where(function ($q) use ($data) {
                $q->where('email', $data['email'])
                  ->orWhere('phone', $data['phone']);
            })
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($alreadyApplied) { 
            return $this->failed(statusCode: 500, msg: 'You have already applied recently.');

        }

        $resumePath = null;
        if ($request->hasFile('resume_file')) {
            $resumeFile = $request->file('resume_file');
            $resumeName = 'resume_' . time() . '_' . Str::random(10) . '.' . $resumeFile->getClientOriginalExtension();
            $resumePath = $resumeFile->storeAs('resume', $resumeName, 'local');
        }

        JobApplication::create([
            'job_id' => $jobId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'resume' => $resumePath,
            'resume_link' => $data['resume_link'] ?? null,
            'portfolio_link' => $data['portfolio_link'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
        ]);

        return $this->successed(msg:'Application submitted successfully');
    }

}
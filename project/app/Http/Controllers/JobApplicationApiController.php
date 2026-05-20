<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Utils\ContentManager;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Http\Controllers\Utils\ApiController;

class JobApplicationApiController extends ApiController
{
    public function apply(Request $request): array|string
    {
        $input = $request->all();
        if (isset($input['job_id']) && (int)$input['job_id'] === 0) {
            $input['job_id'] = null;
        }

        $validator = Validator::make($input, [
            'job_id' => 'nullable|integer|exists:job_openings,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:64',
            'resume_file' => 'required_without:resume_link|nullable|file|mimes:pdf,doc,docx|max:2048',
            'resume_link' => 'required_without:resume_file|nullable|string|max:255',
            'portfolio_link' => 'nullable|url|max:255',
            'cover_letter' => 'nullable|string|max:5000',
        ], [
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
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[] = [
                    'field' => $field,
                    'message' => $messages[0],
                ];
            }
            return $this->failed(statusCode: 422, msg: 'Validation failed', datas: ['errors' => $errors]);
        }

        $data = $validator->validated();

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
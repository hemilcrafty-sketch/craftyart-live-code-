<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Utils\ContentManager;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationController extends AppBaseController
{
    public function index(Request $request): View
    {
        $query = JobApplication::with('jobOpening')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        if ($request->filled('type') || $request->filled('actively_hiring')) {
            $query->whereHas('jobOpening', function ($q) use ($request) {
                if ($request->filled('type')) {
                    $q->where('type', $request->input('type'));
                }
                if ($request->filled('actively_hiring')) {
                    $q->where('is_actively_hiring', true);
                }
            });
        }

        $applications = $query->paginate(20);
        $jobOpenings = \App\Models\JobOpening::all();
        $jobTypes = \App\Models\JobOpening::distinct()->pluck('type');

        return view('job_applications.index', compact('applications', 'jobOpenings', 'jobTypes'));
    }

    public function show(JobApplication $job_application): View
    {
        return view('job_applications.show', ['application' => $job_application]);
    }


    public function downloadResume($id)
    {
        $application = JobApplication::findOrFail($id);
        $path = $application->resume;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'Resume file not found');
        }

        return response()->download(Storage::disk('local')->path($path));
    }

    public function destroy(JobApplication $job_application): RedirectResponse
    {
        if ($job_application->resume && Storage::disk('local')->exists($job_application->resume)) {
            Storage::disk('local')->delete($job_application->resume);
        }

        $job_application->delete();

        return redirect()->route('job_applications.index')->with('success', 'Application deleted.');
    }
}

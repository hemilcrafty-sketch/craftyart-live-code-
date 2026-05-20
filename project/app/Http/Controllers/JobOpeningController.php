<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobOpeningRequest;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

use App\Traits\HandlesMediaCleanup;

class JobOpeningController extends AppBaseController
{
    use HandlesMediaCleanup;

    public function index(Request $request): View
    {
        $query = JobOpening::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->input('search') . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('actively_hiring')) {
            $query->where('is_actively_hiring', true);
        }

        $jobOpenings = $query->paginate(20);
        $jobTypes = JobOpening::distinct()->whereNotNull('type')->pluck('type');

        return view('job_openings.index', compact('jobOpenings', 'jobTypes'));
    }

    public function create(): View
    {
        return view('job_openings.create');
    }

    public function store(JobOpeningRequest $request): RedirectResponse
    {
        $data = $request->processedData();
        $data['slug'] = $this->uniqueSlug($data['title'], $request->input('slug'));

        JobOpening::create($data);

        return redirect()->route('job_openings.index')->with('success', 'Job opening successfully created.');
    }

    public function edit(JobOpening $job_opening): View
    {
        return view('job_openings.edit', ['job' => $job_opening]);
    }

    public function update(JobOpeningRequest $request, JobOpening $job_opening): RedirectResponse
    {
        $data = $request->processedData($job_opening);
        $data['slug'] = $this->uniqueSlug($data['title'], $request->input('slug'), $job_opening->id);

        $this->cleanupFiles($job_opening->toArray(), $data);

        $job_opening->update($data);

        return redirect()->route('job_openings.index')->with('success', 'Job opening successfully updated.');
    }

    public function destroy(JobOpening $job_opening): RedirectResponse
    {
        $this->cleanupFiles($job_opening->toArray(), []);
        $job_opening->delete();
        return redirect()->route('job_openings.index')->with('success', 'Job opening deleted.');
    }

    /** 
     * Generates a unique slug, ensuring no collisions.
     */
    private function uniqueSlug(string $title, ?string $providedSlug = null, ?int $ignoreId = null): string
    {
        $base = ($providedSlug && trim($providedSlug) !== '') ? Str::slug($providedSlug) : Str::slug($title);
        $base = $base ?: 'job-' . time();

        $slug = $base;
        $count = 1;

        while (JobOpening::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-" . (++$count);
            if ($count > 50) return "{$base}-" . Str::random(10);
        }

        return $slug;
    }
}

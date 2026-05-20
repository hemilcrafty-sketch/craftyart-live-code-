<?php

namespace App\Http\Controllers\Designer;

use App\Helpers\JwtHelper;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Color;
use App\Models\Interest;
use App\Models\Language;
use App\Models\NewCategory;
use App\Models\Religion;
use App\Models\Size;
use App\Models\Style;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserData;
use App\Models\Creator\Designer\DesignSubmission;
use App\Models\Creator\Designer\DesignSeoDetail;
use App\Models\Creator\Designer\DesignerApplication;
use App\Models\Creator\Designer\DesignerType;
use App\Models\Creator\Designer\DesignerCategory;
use App\Models\Creator\Designer\DesignerGoal;
use App\Models\Vendor\VendorAccount;
use App\Services\FreelancerEarningsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
/**
 * Consolidated Designer Controller - All 25 Designer APIs
 * Merged from 6 controllers: Application, Enrollment, SEO, Earnings, Categories
 */
class DesignerController extends ApiController
{
    protected ?FreelancerEarningsService $earningsService = null;

    public function __construct(Request $request, ?FreelancerEarningsService $earningsService = null)
    {
        parent::__construct($request);
        $this->earningsService = $earningsService;
    }


    // ============================================================================
    // DesignerApplicationController METHODS
    // ============================================================================

    /**
     * Submit designer application (Public API - No Auth).
     * First-time: creates new application. Rejected: same email can apply again (updates rejected row to pending).
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (!UserData::where('email', $value)->exists()) {
                        $fail('You can apply only if you have an app account. Please register or log in from the app first.');
                    }
                    $existing = DesignerApplication::where('email', $value)->first();
                    if ($existing && in_array($existing->status, ['pending', 'approved'])) {
                        $fail($existing->status === 'approved'
                            ? 'This email is already associated with an approved designer account.'
                            : 'An application with this email is already under review.');
                    }
                },
            ],
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'experience' => 'nullable|string',
            'experience_level' => 'nullable|in:entry-level,mid-level,senior,expert',
            'skills' => 'nullable|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'design_samples' => 'nullable|array',
            'design_samples.*' => 'file|mimes:jpg,jpeg,png,pdf,ai,psd|max:10240', // 10MB max
            'selected_types' => 'nullable|array',
            'selected_types.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && !DesignerType::where('id', $value)->where('is_active', true)->exists()) {
                        $fail("Selected type ID {$value} does not exist or is inactive. Get valid IDs from POST /designer/enrollment/options or GET /designer/enrollment/types.");
                    }
                },
            ],
            'selected_categories' => 'nullable|array',
            'selected_categories.*' => [Rule::exists('new_categories', 'id')->where('status', 1)],
            'selected_goals' => 'nullable|array',
            'selected_goals.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && !DesignerGoal::where('id', $value)->where('is_active', true)->exists()) {
                        $fail("Selected goal ID {$value} does not exist or is inactive. Get valid IDs from POST /designer/enrollment/options or GET /designer/enrollment/goals.");
                    }
                },
            ],
        ], $this->getDesignerApplicationMessages());

        if ($validator->fails()) {
            return $this->failed(statusCode: 422, msg: "Validation failed", datas: $validator->errors()->toArray());
        }

        $data = $validator->validated();

        // Link to app user via user_data.uid only (no users table entry on apply). Set app_user_uid.
        $userData = UserData::where('email', $data['email'])->first();
        if ($request->filled('uid')) {
            $byUid = UserData::where('uid', $request->uid)->first();
            if ($byUid && ($byUid->email === $data['email'] || empty($byUid->email))) {
                $userData = $byUid;
            }
        }
        $uid = ($userData && !empty(trim((string)$userData->uid))) ? trim($userData->uid) : $request->input('uid');
        if ($uid !== null && $uid !== '') {
            $data['app_user_uid'] = $uid;
        }

        // Same key as designer_profiles.user_id: app account row in user_data (was left null before).
        if ($userData) {
            $data['user_id'] = $userData->id;
        }

        // Handle file uploads
        $uploadedSamples = [];
        if ($request->hasFile('design_samples')) {
            foreach ($request->file('design_samples') as $file) {
                $path = $file->store('designer_applications/samples', 'public');
                $uploadedSamples[] = $path;
            }
        }
        $data['uploaded_samples'] = $uploadedSamples;
        $data['status'] = 'pending';

        // Reapply: if existing application is rejected, update it instead of creating new
        $existingRejected = DesignerApplication::where('email', $data['email'])
            ->where('status', 'rejected')
            ->first();

        if ($existingRejected) {
            unset($data['email']);
            $data['rejection_reason'] = null;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
            $existingRejected->update($data);
            $application = $existingRejected;
            $message = "Application resubmitted successfully! We will review and get back to you soon.";
        } else {
            $application = DesignerApplication::create($data);
            $message = "Application submitted successfully! We will review and get back to you soon.";
        }


        return $this->successed(msg: $message, datas: [
            'application_id' => $application->id,
            'status' => $application->status,
        ]);
    }

    /**
     * Check application status (Public API)
     * Also allows approve/reject with proper authentication
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'action' => 'nullable|in:approve,reject', // Optional action
            'rejection_reason' => 'required_if:action,reject|string|min:10',
        ]);

        if ($validator->fails()) {
            return $this->failed(statusCode: 422, msg: "Validation failed", datas: [
                'errors' => $validator->errors()->toArray()
            ]);
        }

        $application = DesignerApplication::where('email', $request->email)
            ->with('reviewer')
            ->first();

        if (!$application) {
            return $this->failed(statusCode: 404, msg: "Application not found with this email");
        }

        // If action is provided, handle approve/reject
        if ($request->filled('action')) {
            // Check authentication for approve/reject actions
            $token = $request->bearerToken();

            if (!$token) {
                return $this->failed(msg: "Authentication required for approve/reject actions");
            }

            try {
                $decoded = JwtHelper::decode($token);
                $userId = $decoded->id ?? $decoded->user_id ?? null;

                if (!$userId) {
                    return $this->failed(msg: "Invalid authentication token");
                }

                // Check if user is admin/designer head
                $user = User::find($userId);
                if (!$user || !in_array($user->user_type, [1, 2])) { // 1=admin, 2=designer_head
                    return $this->failed(statusCode: 403, msg: "Only admin or designer head can approve/reject applications");
                }

                // Perform action
                if ($request->action === 'approve') {
                    if ($application->status === 'approved') {
                        return $this->failed(statusCode: 400, msg: "Application is already approved");
                    }

                    // Check if user_data exists
                    $userData = UserData::where('email', $application->email)->first();
                    if (!$userData) {
                        return $this->failed(statusCode: 404, msg: "User data not found for email: " . $application->email);
                    }

                    // Update user_data to mark as creator (no DesignerProfile needed)
                    $userData->update([
                        'creator' => 1,
                    ]);

                    // Update application status
                    $application->update([
                        'status' => 'approved',
                        'user_id' => $userData->id,
                        'app_user_uid' => $userData->uid ?? null,
                        'reviewed_by' => $userId,
                        'reviewed_at' => now(),
                        'rejection_reason' => null,
                    ]);

                    return $this->successed(msg: "Application approved successfully! Designer account activated. User can now submit designs.", datas: [
                        'application_id' => $application->id,
                        'status' => 'approved',
                        'user_data_id' => $userData->id,
                        'user_uid' => $userData->uid,
                    ]);
                }

                if ($request->action === 'reject') {
                    if ($application->status === 'rejected') {
                        return $this->failed(statusCode: 400, msg: "Application is already rejected");
                    }

                    $application->update([
                        'status' => 'rejected',
                        'reviewed_by' => $userId,
                        'reviewed_at' => now(),
                        'rejection_reason' => $request->rejection_reason,
                    ]);

                    return $this->successed(msg: "Application rejected successfully", datas: [
                        'application_id' => $application->id,
                        'status' => 'rejected',
                        'rejection_reason' => $application->rejection_reason,
                    ]);
                }

            } catch (\Exception $e) {
                return $this->failed(msg: "Invalid or expired token: " . $e->getMessage());
            }
        }

        // Just check status (no action)
        $responseData = [
            'application_id' => $application->id,
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'city' => $application->city,
            'state' => $application->state,
            'country' => $application->country,
            'experience' => $application->experience,
            'experience_level' => $application->experience_level,
            'skills' => $application->skills,
            'portfolio_links' => $application->portfolio_links,
            'selected_types' => $application->selected_types,
            'selected_categories' => $application->selected_categories,
            'selected_goals' => $application->selected_goals,
            'status' => $application->status,
            'submitted_at' => $application->created_at->toISOString(),
            'reviewed_at' => $application->reviewed_at?->toISOString(),
            'reviewed_by' => $application->reviewer?->name,
            'rejection_reason' => $application->rejection_reason,
            'can_reapply' => $application->status === 'rejected',
        ];

        $message = match ($application->status) {
            'pending' => 'Your application is under review. We will get back to you soon.',
            'approved' => 'Congratulations! Your application has been approved.',
            'rejected' => 'Your application was not approved. You can submit a new application.',
            default => 'Application found'
        };

        return $this->successed(msg: $message, datas: $responseData);
    }

    /**
     * Re-apply after rejection (Public API - No Auth)
     * When status is rejected, user can submit again with updated details using same email.
     */
    public function reapply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'experience' => 'nullable|string',
            'experience_level' => 'nullable|in:entry-level,mid-level,senior,expert',
            'skills' => 'nullable|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'design_samples' => 'nullable|array',
            'design_samples.*' => 'file|mimes:jpg,jpeg,png,pdf,ai,psd|max:10240',
            'selected_types' => 'nullable|array',
            'selected_types.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && !DesignerType::where('id', $value)->where('is_active', true)->exists()) {
                        $fail("Selected type ID {$value} does not exist or is inactive. Get valid IDs from POST /designer/enrollment/options or GET /designer/enrollment/types.");
                    }
                },
            ],
            'selected_categories' => 'nullable|array',
            'selected_categories.*' => [Rule::exists('new_categories', 'id')->where('status', 1)],
            'selected_goals' => 'nullable|array',
            'selected_goals.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && !DesignerGoal::where('id', $value)->where('is_active', true)->exists()) {
                        $fail("Selected goal ID {$value} does not exist or is inactive. Get valid IDs from POST /designer/enrollment/options or GET /designer/enrollment/goals.");
                    }
                },
            ],
        ], $this->getDesignerApplicationMessages());

        if ($validator->fails()) {
            return $this->failed(statusCode: 422, msg: "Validation failed", datas: ['errors' => $validator->errors()->toArray()]);
        }

        $application = DesignerApplication::where('email', $request->email)
            ->where('status', 'rejected')
            ->first();

        if (!$application) {
            return $this->failed(statusCode: 404, msg: "No rejected application found with this email. Use the apply endpoint for first-time application.");
        }

        $data = $validator->validated();
        unset($data['email']); // keep existing email

        // Handle file uploads
        $uploadedSamples = $application->uploaded_samples ?? [];
        if ($request->hasFile('design_samples')) {
            foreach ($request->file('design_samples') as $file) {
                $path = $file->store('designer_applications/samples', 'public');
                $uploadedSamples[] = $path;
            }
        }
        $data['uploaded_samples'] = $uploadedSamples;
        $data['status'] = 'pending';
        $data['rejection_reason'] = null;
        $data['reviewed_by'] = null;
        $data['reviewed_at'] = null;

        $application->update($data);

        // Broadcast event for real-time updates

        return $this->successed(msg: "Application resubmitted successfully! We will review and get back to you soon.", datas: [
            'application_id' => $application->id,
            'status' => $application->status,
        ]);
    }

    /**
     * Custom validation messages for apply/reapply so user gets clear errors.
     */
    private function getDesignerApplicationMessages(): array
    {
        return [
            'selected_types.*.exists' => 'Selected type ID :input does not exist or is inactive. Get valid IDs from GET /designer/enrollment/types.',
            'selected_categories.*.exists' => 'Selected category ID :input does not exist or is not active. Get valid IDs from GET /designer/enrollment/categories or GET /designer/enrollment/options.',
            'selected_goals.*.exists' => 'Selected goal ID :input does not exist or is inactive. Get valid IDs from GET /designer/enrollment/goals.',
        ];
    }

    // ============================================================================
    // DesignerEnrollmentController METHODS
    // ============================================================================

    /**
     * Check if user is already enrolled
     */
    public function checkEnrollment(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "User not found");
        }

        $application = DesignerApplication::where('user_id', $userData->id)
            ->where('status', 'approved')
            ->first();

        if ($application) {
            return $this->successed(
                msg: "User is enrolled as designer",
                datas: [
                    'is_enrolled' => true,
                    'is_creator' => $userData->creator == 1,
                    'application_id' => $application->id,
                    'approved_at' => $application->reviewed_at?->toIso8601String(),
                ]
            );
        }

        return $this->successed(
            msg: "User is not enrolled",
            datas: [
                'is_enrolled' => false,
                'is_creator' => $userData->creator == 1,
            ]
        );
    }

    /**
     * Get enrollment options (types, categories, goals) - All in one API
     */
    public function getEnrollmentOptions(Request $request)
    {
        // Fetch parent categories from new_categories table (parent_category_id = 0)
        $categories = NewCategory::where('status', 1)
            ->where('parent_category_id', 0)
            ->orderBy('sequence_number', 'asc')
            ->select('id', 'category_name', 'id_name', 'short_desc', 'category_thumb', 'status', 'sequence_number', 'parent_category_id')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->category_name,
                    'slug' => $category->id_name,
                    'description' => $category->short_desc,
                    'icon' => $category->category_thumb,
                    'is_active' => $category->status == 1,
                    'sort_order' => $category->sequence_number,
                ];
            })
            ->toArray();

        return $this->successed(msg: "Enrollment options fetched successfully", datas: [
            'types' => DesignerType::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'slug', 'description', 'is_active', 'sort_order'])
                ->toArray(),
            'categories' => $categories,
            'goals' => DesignerGoal::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'slug', 'description', 'is_active', 'sort_order'])
                ->toArray(),
        ]);
    }

    /**
     * Submit enrollment application
     */
    public function submitEnrollment(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "User not found");
        }

        // Check if already enrolled
        $existingApplication = DesignerApplication::where('user_id', $userData->id)
            ->where('status', 'approved')
            ->first();

        if ($existingApplication) {
            return $this->failed(
                statusCode: 400,
                msg: "You are already enrolled in the designer program"
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'experience' => 'required|string',
            'experience_level' => 'required|in:entry-level,mid-level,senior,expert',
            'skills' => 'required|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'uploaded_samples' => 'nullable|array',
            'selected_types' => 'required|array|min:1',
            'selected_types.*' => 'exists:crafty_creator_mysql.designer_types,id',
            'selected_categories' => 'required|array|min:1',
            'selected_categories.*' => 'exists:new_categories,id',
            'selected_goals' => 'required|array|min:1',
            'selected_goals.*' => 'exists:crafty_creator_mysql.designer_goals,id',
        ]);

        if ($validator->fails()) {
            return $this->failed(
                statusCode: 422,
                msg: "Validation failed",
                datas: ['errors' => $validator->errors()->toArray()]
            );
        }

        DB::beginTransaction();
        try {
            // Create application
            $application = DesignerApplication::create([
                'user_id' => $userData->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'experience' => $request->experience,
                'experience_level' => $request->experience_level,
                'skills' => $request->skills,
                'portfolio_links' => $request->portfolio_links,
                'uploaded_samples' => $request->uploaded_samples,
                'selected_types' => $request->selected_types,
                'selected_categories' => $request->selected_categories,
                'selected_goals' => $request->selected_goals,
                'status' => 'pending',
            ]);

            DB::commit();

            return $this->successed(
                msg: "Enrollment application submitted successfully! Your application is under review.",
                datas: [
                    'application_id' => $application->id,
                    'status' => $application->status,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to submit enrollment application",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get enrollment status
     */
    public function getEnrollmentStatus(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "User not found");
        }

        $application = DesignerApplication::where('user_id', $userData->id)->first();

        if (!$application) {
            return $this->successed(
                msg: "No application found",
                datas: [
                    'enrolled' => false,
                    'has_application' => false,
                ]
            );
        }

        return $this->successed(
            msg: "Application status retrieved",
            datas: [
                'enrolled' => $application->status === 'approved',
                'has_application' => true,
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'submitted_at' => $application->created_at->toIso8601String(),
                    'reviewed_at' => $application->reviewed_at?->toIso8601String(),
                    'rejection_reason' => $application->rejection_reason,
                ],
            ]
        );
    }

    /**
     * Get all active designer types for enrollment form
     */
    public function getTypes(): array|string
    {
        $types = DesignerType::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'is_active', 'sort_order'])
            ->toArray();

        return $this->successed(msg: "Designer types fetched successfully", datas: ['data' => $types]);
    }

    /**
     * Get all active designer categories for enrollment form
     */
    public function getCategories()
    {
        // Fetch parent categories from new_categories table (parent_category_id = 0)
        $categories = NewCategory::where('status', 1)
            ->where('parent_category_id', 0)
            ->orderBy('sequence_number', 'asc')
            ->select('id', 'category_name', 'id_name', 'short_desc', 'category_thumb', 'status', 'sequence_number', 'parent_category_id')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->category_name,
                    'slug' => $category->id_name,
                    'description' => $category->short_desc,
                    'icon' => $category->category_thumb,
                    'is_active' => $category->status == 1,
                    'sort_order' => $category->sequence_number,
                ];
            })
            ->toArray();

        return $this->successed(msg: "Designer categories fetched successfully", datas: ['data' => $categories]);
    }

    /**
     * Get all active designer goals for enrollment form
     */
    public function getGoals()
    {
        $goals = DesignerGoal::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'is_active', 'sort_order'])
            ->toArray();

        return $this->successed(msg: "Designer goals fetched successfully", datas: ['data' => $goals]);
    }

    // ============================================================================
    // DesignerSeoController METHODS
    // ============================================================================

    /**
     * SEO Metadata for designer submissions (crafty_creator).
     * 1. GET metadata-options: categories (new_categories parent) + filters.
     * 2. POST submit metadata: save/update design_seo_details for a design_submission_id.
     */

    public function getMetadataOptions(Request $request)
    {
        $categories = NewCategory::where('status', 1)
            ->where('parent_category_id', 0)
            ->orderBy('sequence_number', 'asc')
            ->select('id', 'category_name', 'id_name', 'short_desc', 'category_thumb', 'sequence_number', 'parent_category_id')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->category_name,
                    'slug' => $c->id_name,
                    'description' => $c->short_desc,
                    'icon' => $c->category_thumb,
                    'sort_order' => $c->sequence_number,
                ];
            })
            ->values()
            ->toArray();

        $filters = [
            'languages' => $this->getFilterItems(Language::class, 'name'),
            'themes' => $this->getFilterItems(Theme::class, 'name'),
            'styles' => $this->getFilterItems(Style::class, 'name'),
            'religions' => $this->getFilterItems(Religion::class, 'religion_name'),
            'interests' => $this->getFilterItems(Interest::class, 'name'),
            'colors' => Color::where('status', 1)->get(['id', 'code'])->map(fn($c) => ['id' => $c->id, 'code' => $c->code])->values()->toArray(),
            'sizes' => Size::where('status', 1)->get(['id', 'size_name', 'id_name', 'width', 'height'])->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->size_name ?? $s->id_name,
                'id_name' => $s->id_name,
                'width' => $s->width,
                'height' => $s->height,
            ])->values()->toArray(),
            'orientations' => [
                ['value' => 'portrait', 'label' => 'Portrait'],
                ['value' => 'landscape', 'label' => 'Landscape'],
                ['value' => 'square', 'label' => 'Square'],
            ],
        ];

        return $this->successed(msg: "Metadata options fetched successfully", datas: [
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    private function getFilterItems(string $model, string $nameAttr = 'name'): array
    {
        if (!class_exists($model)) {
            return [];
        }
        $q = $model::query();
        try {
            $m = new $model;
            if (Schema::hasColumn($m->getTable(), 'status')) {
                $q->where('status', 1);
            }
        } catch (\Throwable $e) {
        }
        $items = $q->get();
        return $items->map(fn($i) => [
            'id' => $i->id,
            'name' => $i->{$nameAttr} ?? $i->name ?? (string)$i->id,
            'slug' => $i->id_name ?? (string)$i->id,
        ])->values()->toArray();
    }

    public function submitMetadata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_submission_id' => 'required|integer',
            'post_name' => 'nullable|string|max:255',
            'id_name' => 'nullable|string|max:255',
            'h2_tag' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
            'primary_category_id' => 'nullable|integer|exists:new_categories,id',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
            'is_featured' => 'nullable|boolean',
            'is_trending' => 'nullable|boolean',
            'filters' => 'nullable|array',
            'filters.language_ids' => 'nullable|array',
            'filters.language_ids.*' => 'integer',
            'filters.theme_ids' => 'nullable|array',
            'filters.theme_ids.*' => 'integer',
            'filters.style_ids' => 'nullable|array',
            'filters.style_ids.*' => 'integer',
            'filters.orientation' => 'nullable|string|in:portrait,landscape,square',
            'filters.size_id' => 'nullable|integer',
            'filters.religion_ids' => 'nullable|array',
            'filters.religion_ids.*' => 'integer',
            'filters.interest_ids' => 'nullable|array',
            'filters.interest_ids.*' => 'integer',
            'filters.colors' => 'nullable|array',
            'filters.colors.*' => 'string',
            'aspect_ratio' => 'nullable|numeric',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->failed(msg: "Validation failed", datas: ['errors' => $validator->errors()->toArray()]);
        }

        $designSubmissionId = (int)$request->design_submission_id;
        $design = DesignSubmission::find($designSubmissionId);
        if (!$design) {
            return $this->failed(msg: "Design submission not found");
        }

        $data = $validator->validated();
        $designSubId = $data['design_submission_id'];
        unset($data['design_submission_id']);

        $payload = [
            'design_submission_id' => $designSubId,
            'post_name' => $data['post_name'] ?? null,
            'id_name' => $data['id_name'] ?? null,
            'h2_tag' => $data['h2_tag'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'description' => $data['description'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'primary_category_id' => $data['primary_category_id'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'is_trending' => $data['is_trending'] ?? false,
            'filters' => $data['filters'] ?? null,
            'aspect_ratio' => $data['aspect_ratio'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
        ];

        // Generate unique slug from meta_title
        if (!empty($data['meta_title'])) {
            $baseSlug = \Illuminate\Support\Str::slug($data['meta_title']);
            $slug = $baseSlug;
            $counter = 1;

            // Check if updating existing record
            $existingSeo = DesignSeoDetail::where('design_submission_id', $designSubId)->first();

            // Keep checking until we find a unique slug (excluding current record if updating)
            while (true) {
                $query = DesignSeoDetail::where('slug', $slug);

                // If updating, exclude the current record from duplicate check
                if ($existingSeo) {
                    $query->where('id', '!=', $existingSeo->id);
                }

                if (!$query->exists()) {
                    break;
                }

                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $payload['slug'] = $slug;
        }

        $attrs = array_filter($payload, fn($v) => $v !== null && $v !== '');
        $seo = DesignSeoDetail::updateOrCreate(
            ['design_submission_id' => $designSubId],
            $attrs
        );

        // Auto-detect if design was rejected by SEO and reapply
        $wasRejected = $design->status === 'rejected_by_seo';
        $rejectionReason = $design->seo_head_notes;

        if ($wasRejected) {
            // Automatically reapply when updating SEO after rejection
            $design->update([
                'status' => 'pending_seo',
                'seo_head_notes' => null, // Clear rejection notes
            ]);

            // Refresh the model to get updated status
            $design->refresh();

            $message = 'SEO metadata updated and design resubmitted for SEO review successfully!';
        } else {
            $message = 'SEO metadata saved successfully';
        }

        $responseData = [
            'design_submission_id' => $designSubId,
            'design_seo_detail_id' => $seo->id,
            'slug' => $seo->slug,
            'status' => $design->status,
            'resubmitted' => $wasRejected, // Indicates if it was auto-resubmitted
        ];

        // Include previous rejection reason if design was rejected
        if ($wasRejected) {
            $responseData['previous_rejection_reason'] = $rejectionReason;
        }

        return $this->successed(msg: $message, datas: $responseData);
    }

    // ============================================================================
    // DESIGN SUBMISSION API
    // ============================================================================

    /**
     * Submit a new design for review
     * POST /api/designer/design/submit
     */
    public function submitDesign(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        $validator = Validator::make($request->all(), [
            'string_id' => 'nullable|string|max:128',
            'template_id' => 'nullable|string|max:255',
            'caricature_ids' => 'nullable|array',
            'ratio' => 'nullable|numeric',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:new_categories,id',
            'thumbs' => 'nullable|array',
            'video' => 'nullable|string',
            'designs' => 'nullable|string',
            'msg' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->failed(
                statusCode: 422,
                msg: "Validation failed",
                datas: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            DB::beginTransaction();

            // Generate unique string_id if not provided
            $stringId = $request->string_id ?? 'DS-' . Str::random(10) . '-' . time();

            // Create design submission
            $submission = DesignSubmission::create([
                'string_id' => $stringId,
                'template_id' => $request->template_id,
                'designer_id' => $userData->id,
                'user_id' => $this->uid,
                'caricature_ids' => $request->caricature_ids,
                'ratio' => $request->ratio,
                'width' => $request->width,
                'height' => $request->height,
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'thumbs' => $request->thumbs,
                'video' => $request->video,
                'designs' => $request->designs,
                'msg' => $request->msg,
                'is_live' => -1, // Draft status
                'tags' => $request->tags,
                'status' => 'pending_designer_head', // Initial status
                'total_sales' => 0,
                'total_revenue' => 0,
            ]);

            DB::commit();

            return $this->successed(
                msg: "Design submitted successfully! Your design is under review.",
                datas: [
                    'submission_id' => $submission->id,
                    'string_id' => $submission->string_id,
                    'status' => $submission->status,
                    'title' => $submission->title,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to submit design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get a specific design submission
     * GET /api/designer/design/{id}
     */
    public function getDesign(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        try {
            // Get design submission - designer can only see their own designs
            $design = DesignSubmission::where('id', $id)
                ->where('designer_id', $userData->id)
                ->with('seoDetails')
                ->first();

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design not found or you don't have permission to view it"
                );
            }

            // Get category name if category_id exists
            $categoryName = null;
            if ($design->category_id) {
                $category = NewCategory::find($design->category_id);
                $categoryName = $category ? $category->category_name : null;
            }

            $responseData = [
                'id' => $design->id,
                'string_id' => $design->string_id,
                'template_id' => $design->template_id,
                'title' => $design->title,
                'description' => $design->description,
                'category_id' => $design->category_id,
                'category_name' => $categoryName,
                'width' => $design->width,
                'height' => $design->height,
                'ratio' => $design->ratio,
                'thumbs' => $design->thumbs,
                'video' => $design->video,
                'designs' => $design->designs,
                'tags' => $design->tags,
                'caricature_ids' => $design->caricature_ids,
                'status' => $design->status,
                'is_live' => $design->is_live,
                'total_sales' => $design->total_sales,
                'total_revenue' => (float)$design->total_revenue,
                'crafty_design_id' => $design->crafty_design_id,
                'designer_head_notes' => $design->designer_head_notes,
                'seo_head_notes' => $design->seo_head_notes,
                'published_at' => $design->published_at?->toIso8601String(),
                'created_at' => $design->created_at->toIso8601String(),
                'updated_at' => $design->updated_at->toIso8601String(),
            ];

            // Include SEO details if available
            if ($design->seoDetails) {
                $responseData['seo_details'] = [
                    'slug' => $design->seoDetails->slug,
                    'meta_title' => $design->seoDetails->meta_title,
                    'meta_description' => $design->seoDetails->meta_description,
                    'meta_keywords' => $design->seoDetails->meta_keywords,
                    'og_title' => $design->seoDetails->og_title,
                    'og_description' => $design->seoDetails->og_description,
                    'og_image' => $design->seoDetails->og_image,
                ];
            }

            return $this->successed(
                msg: "Design retrieved successfully",
                datas: $responseData
            );

        } catch (\Exception $e) {
            return $this->failed(
                statusCode: 500,
                msg: "Failed to retrieve design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get all designs for the authenticated designer
     * GET /api/designer/designs
     */
    public function getDesigns(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        try {
            $query = DesignSubmission::where('designer_id', $userData->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by is_live if provided
            if ($request->has('is_live')) {
                $query->where('is_live', $request->is_live);
            }

            // Order by created_at desc (newest first)
            $designs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            $designsData = $designs->map(function ($design) {
                $categoryName = null;
                if ($design->category_id) {
                    $category = NewCategory::find($design->category_id);
                    $categoryName = $category ? $category->category_name : null;
                }

                return [
                    'id' => $design->id,
                    'string_id' => $design->string_id,
                    'title' => $design->title,
                    'category_id' => $design->category_id,
                    'category_name' => $categoryName,
                    'status' => $design->status,
                    'is_live' => $design->is_live,
                    'total_sales' => $design->total_sales,
                    'total_revenue' => (float)$design->total_revenue,
                    'thumbs' => $design->thumbs,
                    'published_at' => $design->published_at?->toIso8601String(),
                    'created_at' => $design->created_at->toIso8601String(),
                ];
            });

            return $this->successed(
                msg: "Designs retrieved successfully",
                datas: [
                    'designs' => $designsData,
                    'pagination' => [
                        'total' => $designs->total(),
                        'per_page' => $designs->perPage(),
                        'current_page' => $designs->currentPage(),
                        'last_page' => $designs->lastPage(),
                        'from' => $designs->firstItem(),
                        'to' => $designs->lastItem(),
                    ]
                ]
            );

        } catch (\Exception $e) {
            return $this->failed(
                statusCode: 500,
                msg: "Failed to retrieve designs",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update a design submission
     * PUT /api/designer/design/{id}
     */
    public function updateDesign(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        try {
            // Get design submission - designer can only update their own designs
            $design = DesignSubmission::where('id', $id)
                ->where('designer_id', $userData->id)
                ->first();

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design not found or you don't have permission to update it"
                );
            }

            // Only allow updates if design is in draft or rejected status
            if (!in_array($design->status, ['pending_designer_head', 'rejected_by_designer_head', 'rejected_by_seo'])) {
                return $this->failed(
                    statusCode: 403,
                    msg: "Cannot update design in current status: {$design->status}"
                );
            }

            $validator = Validator::make($request->all(), [
                'template_id' => 'nullable|string|max:255',
                'caricature_ids' => 'nullable|array',
                'ratio' => 'nullable|numeric',
                'width' => 'nullable|integer|min:1',
                'height' => 'nullable|integer|min:1',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:new_categories,id',
                'thumbs' => 'nullable|array',
                'video' => 'nullable|string',
                'designs' => 'nullable|string',
                'msg' => 'nullable|string',
                'tags' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->failed(
                    statusCode: 422,
                    msg: "Validation failed",
                    datas: ['errors' => $validator->errors()->toArray()]
                );
            }

            DB::beginTransaction();

            // Update only provided fields
            $updateData = array_filter($validator->validated(), function ($value) {
                return $value !== null;
            });

            // If design was rejected, reset status to pending
            if (in_array($design->status, ['rejected_by_designer_head', 'rejected_by_seo'])) {
                $updateData['status'] = 'pending_designer_head';
                $updateData['designer_head_notes'] = null;
                $updateData['seo_head_notes'] = null;
            }

            $design->update($updateData);

            DB::commit();

            return $this->successed(
                msg: "Design updated successfully",
                datas: [
                    'submission_id' => $design->id,
                    'string_id' => $design->string_id,
                    'status' => $design->status,
                    'title' => $design->title,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to update design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete a design submission
     * DELETE /api/designer/design/{id}
     */
    public function deleteDesign(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        try {
            // Get design submission - designer can only delete their own designs
            $design = DesignSubmission::where('id', $id)
                ->where('designer_id', $userData->id)
                ->first();

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design not found or you don't have permission to delete it"
                );
            }

            // Only allow deletion if design is not live
            if ($design->status === 'live' || $design->is_live === 1) {
                return $this->failed(
                    statusCode: 403,
                    msg: "Cannot delete a live design. Please contact support."
                );
            }

            DB::beginTransaction();

            $design->delete();

            DB::commit();

            return $this->successed(
                msg: "Design deleted successfully",
                datas: [
                    'submission_id' => $id,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to delete design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Approve design submission (Designer Head)
     * POST /api/designer/design/{id}/approve
     */
    public function approveDesign(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "Unauthorized");
        }

        // Check if user is admin or designer head (you can add role check here)
        // For now, any authenticated user can approve

        try {
            $design = DesignSubmission::find($id);

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design submission not found"
                );
            }

            // Check if design is in correct status for approval
            if (!in_array($design->status, ['pending_designer_head', 'rejected_by_designer_head'])) {
                return $this->failed(
                    statusCode: 400,
                    msg: "Design cannot be approved in current status: {$design->status}"
                );
            }

            DB::beginTransaction();

            $design->update([
                'status' => 'pending_seo',
                'designer_head_reviewed_by' => $userData->id,
                'designer_head_reviewed_at' => now(),
                'designer_head_notes' => $request->input('notes', 'Approved by designer head'),
            ]);

            DB::commit();

            return $this->successed(
                msg: "Design approved successfully! Now pending SEO review.",
                datas: [
                    'submission_id' => $design->id,
                    'string_id' => $design->string_id,
                    'status' => $design->status,
                    'title' => $design->title,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to approve design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Reject design submission (Designer Head)
     * POST /api/designer/design/{id}/reject
     */
    public function rejectDesign(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "Unauthorized");
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return $this->failed(
                statusCode: 422,
                msg: "Validation failed",
                datas: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $design = DesignSubmission::find($id);

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design submission not found"
                );
            }

            // Check if design is in correct status for rejection
            if (!in_array($design->status, ['pending_designer_head', 'approved_by_designer_head'])) {
                return $this->failed(
                    statusCode: 400,
                    msg: "Design cannot be rejected in current status: {$design->status}"
                );
            }

            DB::beginTransaction();

            $design->update([
                'status' => 'rejected_by_designer_head',
                'designer_head_reviewed_by' => $userData->id,
                'designer_head_reviewed_at' => now(),
                'designer_head_notes' => $request->reason,
            ]);

            DB::commit();

            return $this->successed(
                msg: "Design rejected. Designer can update and resubmit.",
                datas: [
                    'submission_id' => $design->id,
                    'string_id' => $design->string_id,
                    'status' => $design->status,
                    'title' => $design->title,
                    'rejection_reason' => $request->reason,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to reject design",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Approve SEO details (SEO Head)
     * POST /api/designer/design/{id}/seo/approve
     */
    public function approveSeo(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "Unauthorized");
        }

        try {
            $design = DesignSubmission::find($id);

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design submission not found"
                );
            }

            // Check if design is in correct status for SEO approval
            if (!in_array($design->status, ['pending_seo', 'rejected_by_seo'])) {
                return $this->failed(
                    statusCode: 400,
                    msg: "Design cannot be SEO approved in current status: {$design->status}"
                );
            }

            DB::beginTransaction();

            $design->update([
                'status' => 'live',
                'is_live' => 1,
                'seo_head_reviewed_by' => $userData->id,
                'seo_head_reviewed_at' => now(),
                'seo_head_notes' => $request->input('notes', 'Approved by SEO head'),
                'published_at' => now(),
            ]);

            DB::commit();

            return $this->successed(
                msg: "SEO approved! Design is now live.",
                datas: [
                    'submission_id' => $design->id,
                    'string_id' => $design->string_id,
                    'status' => $design->status,
                    'is_live' => $design->is_live,
                    'title' => $design->title,
                    'published_at' => $design->published_at->toIso8601String(),
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to approve SEO",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Reject SEO details (SEO Head)
     * POST /api/designer/design/{id}/seo/reject
     */
    public function rejectSeo(Request $request, $id)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->first();
        if (!$userData) {
            return $this->failed(msg: "Unauthorized");
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return $this->failed(
                statusCode: 422,
                msg: "Validation failed",
                datas: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $design = DesignSubmission::find($id);

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design submission not found"
                );
            }

            // Check if design is in correct status for SEO rejection
            if (!in_array($design->status, ['pending_seo', 'approved_by_seo'])) {
                return $this->failed(
                    statusCode: 400,
                    msg: "Design cannot be SEO rejected in current status: {$design->status}"
                );
            }

            DB::beginTransaction();

            $design->update([
                'status' => 'rejected_by_seo',
                'seo_head_reviewed_by' => $userData->id,
                'seo_head_reviewed_at' => now(),
                'seo_head_notes' => $request->reason,
            ]);

            DB::commit();

            return $this->successed(
                msg: "SEO rejected. Designer can update SEO details and resubmit.",
                datas: [
                    'submission_id' => $design->id,
                    'string_id' => $design->string_id,
                    'status' => $design->status,
                    'title' => $design->title,
                    'rejection_reason' => $request->reason,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to reject SEO",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    // ============================================================================
    // FreelancerEarningsController METHODS
    // ============================================================================

    /**
     * Get freelancer earnings dashboard
     * GET /api/designer/earnings/dashboard
     */
    public function dashboard(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not a freelancer designer");
        }

        try {
            // Get earnings summary
            $summary = $this->earningsService->getFreelancerEarningsSummary($this->uid);

            // Get recent transactions
            $recentTransactions = $this->earningsService->getFreelancerTransactionHistory($this->uid, 10);

            // Get top performing designs - use DB query to avoid cross-database issues
            $topDesignsData = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('designer_id', $userData->id)
                ->where('status', 'live')
                ->orderBy('total_revenue', 'desc')
                ->limit(5)
                ->get(['id', 'string_id', 'title', 'total_sales', 'total_revenue', 'crafty_design_id', 'published_at']);

            $topDesigns = $topDesignsData->map(function ($design) {
                return [
                    'id' => $design->id,
                    'string_id' => $design->string_id,
                    'title' => $design->title,
                    'total_sales' => $design->total_sales,
                    'total_revenue' => (float)$design->total_revenue,
                    'crafty_design_id' => $design->crafty_design_id,
                    'published_at' => $design->published_at,
                ];
            });

            return $this->successed(
                msg: "Earnings dashboard retrieved successfully",
                datas: [
                    'summary' => $summary,
                    'recent_transactions' => $recentTransactions,
                    'top_designs' => $topDesigns,
                ]
            );

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve dashboard: " . $e->getMessage());
        }
    }

    /**
     * Get freelancer transaction history
     * GET /api/designer/earnings/transactions
     */
    public function transactions(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not a freelancer designer");
        }

        $limit = (int)$request->get('limit', 50);
        $limit = min($limit, 100); // Max 100

        try {
            $transactions = $this->earningsService->getFreelancerTransactionHistory($this->uid, $limit);

            return $this->successed(
                msg: "Transaction history retrieved successfully",
                datas: [
                    'transactions' => $transactions,
                    'count' => count($transactions),
                ]
            );

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve transactions: " . $e->getMessage());
        }
    }

    /**
     * Get earnings by design
     * GET /api/designer/earnings/by-design/{design_submission_id}
     */
    public function earningsByDesign(Request $request, int $designSubmissionId)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not a freelancer designer");
        }

        try {
            // Use DB query to avoid cross-database issues
            $design = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('id', $designSubmissionId)
                ->where('designer_id', $userData->id)
                ->first();

            if (!$design) {
                return $this->failed(statusCode: 404, msg: "Design not found or not owned by you");
            }

            // Get transactions for this design using direct DB query to avoid cross-database relationship issues
            // revenue_history is in crafty_vendor_mysql, purchase_history is in crafty_revenue_mysql
            $revenueRecords = DB::connection('crafty_vendor_mysql')
                ->table('revenue_history')
                ->where('vendor_type', 'freelancer')
                ->where('user_id', $this->uid)
                ->orderBy('created_at', 'desc')
                ->get();

            // Filter by product_id from purchase_history
            $transactions = [];
            foreach ($revenueRecords as $revenue) {
                // Check if this revenue's purchase has the matching product_id
                $purchase = DB::connection('crafty_revenue_mysql')
                    ->table('purchase_history')
                    ->where('id', $revenue->purchase_id)
                    ->where('product_id', $design->crafty_design_id)
                    ->first();

                if ($purchase) {
                    $transactions[] = [
                        'id' => $revenue->id,
                        'amount' => $revenue->vendor_amount / 100,
                        'purchase_amount' => $revenue->purchase_amount / 100,
                        'percentage' => $revenue->vendor_percentage,
                        'currency' => $revenue->currency,
                        'created_at' => $revenue->created_at,
                    ];
                }
            }

            return $this->successed(
                msg: "Design earnings retrieved successfully",
                datas: [
                    'design' => [
                        'id' => $design->id,
                        'title' => $design->title,
                        'total_sales' => $design->total_sales,
                        'total_revenue' => (float)$design->total_revenue,
                    ],
                    'transactions' => $transactions,
                    'total_transactions' => count($transactions),
                ]
            );

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve design earnings: " . $e->getMessage());
        }
    }

    /**
     * Get wallet balance
     * GET /api/designer/earnings/balance
     *
     * Balance is calculated from revenue_history, not stored in vendor_accounts
     * Shows total earnings from all designs with breakdown
     */
    public function balance(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not a freelancer designer");
        }

        try {
            // Calculate balance from revenue_history
            $revenueData = DB::connection('crafty_vendor_mysql')
                ->table('revenue_history')
                ->where('user_id', $this->uid)
                ->where('vendor_type', 'freelancer')
                ->where('status', 'active')
                ->selectRaw('
          SUM(vendor_amount) as total_earning,
          COUNT(*) as total_transactions
        ')
                ->first();

            // Get withdrawn amount
            $withdrawnData = DB::connection('crafty_vendor_mysql')
                ->table('vendor_withdraw')
                ->where('user_id', $this->uid)
                ->where('vendor_type', 'freelancer')
                ->whereIn('status', ['completed', 'processing'])
                ->selectRaw('SUM(amount) as withdrawn_amount')
                ->first();

            $totalEarning = $revenueData->total_earning ?? 0;
            $withdrawnAmount = $withdrawnData->withdrawn_amount ?? 0;
            $availableBalance = $totalEarning - $withdrawnAmount;

            // Get earnings breakdown by design
            $designsData = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('designer_id', $userData->id)
                ->where('status', 'live')
                ->where('total_revenue', '>', 0)
                ->orderBy('total_revenue', 'desc')
                ->get(['id', 'title', 'total_sales', 'total_revenue', 'crafty_design_id']);

            $earningsByDesign = $designsData->map(function ($design) {
                return [
                    'design_id' => $design->id,
                    'design_title' => $design->title,
                    'crafty_design_id' => $design->crafty_design_id,
                    'total_sales' => $design->total_sales,
                    'total_revenue' => (float)$design->total_revenue,
                ];
            })->toArray();

            return $this->successed(
                msg: "Balance retrieved successfully",
                datas: [
                    'balance' => [
                        'total_earning' => $totalEarning / 100, // Convert from paise to rupees
                        'available_balance' => $availableBalance / 100,
                        'withdrawn_amount' => $withdrawnAmount / 100,
                        'pending_balance' => 0, // Calculated from pending withdrawals if needed
                        'currency' => 'INR',
                        'total_transactions' => $revenueData->total_transactions ?? 0,
                    ],
                    'earnings_by_design' => $earningsByDesign,
                    'total_designs_with_earnings' => count($earningsByDesign),
                    'note' => 'total_earning is sum from all designs. See earnings_by_design for breakdown.',
                ]
            );

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve balance: " . $e->getMessage());
        }
    }

    /**
     * Create test purchase for design (Testing Only)
     * POST /api/designer/test/create-purchase
     *
     * This API mimics real purchase flow:
     * 1. Creates purchase_history in crafty_revenue_mysql
     * 2. Creates revenue_history in crafty_vendor_mysql
     * 3. Updates design_submissions stats
     * 4. Creates vendor_account if needed (balance is calculated from revenue_history)
     */
    public function createTestPurchase(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $userData = UserData::whereUid($this->uid)->whereCreator(1)->whereStatus(1)->first();
        if (!$userData) {
            return $this->failed(msg: "User is not an approved designer");
        }

        $validator = Validator::make($request->all(), [
            'design_submission_id' => 'required|integer|exists:crafty_creator_mysql.design_submissions,id',
            'amount' => 'required|numeric|min:1',
            'buyer_uid' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->failed(
                statusCode: 422,
                msg: "Validation failed",
                datas: ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            // Get design submission
            $design = DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('id', $request->design_submission_id)
                ->where('designer_id', $userData->id)
                ->first();

            if (!$design) {
                return $this->failed(
                    statusCode: 404,
                    msg: "Design not found or not owned by you"
                );
            }

            if (!$design->crafty_design_id) {
                return $this->failed(
                    statusCode: 400,
                    msg: "Design must be published (have crafty_design_id) before creating purchases"
                );
            }

            // Get freelancer commission percentage from wallet settings
            $walletSettings = DB::connection('crafty_vendor_mysql')
                ->table('wallet_settings')
                ->where('is_active', 1)
                ->orderBy('id')
                ->first();

            // Use freelancer_commission_rate (fallback to platform_commission_rate if not set)
            $designerPercentage = $walletSettings
                ? (float)($walletSettings->freelancer_commission_rate ?? $walletSettings->platform_commission_rate ?? 30.00)
                : 30.00; // Default 30% if no settings found

            DB::beginTransaction();

            $amount = (float)$request->amount;
            $buyerUid = $request->buyer_uid ?? 'test_buyer_' . time();

            // Calculate designer amount
            $designerAmount = round(($amount * $designerPercentage) / 100, 2);

            // Convert to paise/cents for storage (multiply by 100)
            $amountPaisa = (int)round($amount * 100);
            $designerAmountPaisa = (int)round($designerAmount * 100);

            // 1. Create purchase in crafty_revenue_mysql.purchase_history
            $purchaseId = DB::connection('crafty_revenue_mysql')
                ->table('purchase_history')
                ->insertGetId([
                    'emp_id' => 0,
                    'by_sales_team' => 0,
                    'user_id' => $buyerUid,
                    'contact_no' => null,
                    'product_id' => (string)$design->crafty_design_id,
                    'product_type' => 'template',
                    'subscription_id' => null,
                    'subscription_is_active' => 0,
                    'order_id' => 'TEST_ORDER_' . time() . '_' . Str::random(8),
                    'transaction_id' => 'TEST_TXN_' . time() . '_' . Str::random(8),
                    'payment_id' => 'TEST_PAY_' . time() . '_' . Str::random(8),
                    'currency_code' => 'INR',
                    'amount' => $amount,
                    'paid_amount' => $amount,
                    'net_amount' => $amount,
                    'promo_code_id' => 0,
                    'discount' => 0,
                    'payment_method' => 'test',
                    'from_where' => 'api_test',
                    'fbc' => null,
                    'gclid' => null,
                    'isManual' => 1,
                    'url' => null,
                    'validity' => 365,
                    'yearly' => 0,
                    'plan_limit' => null,
                    'subscription_status' => null,
                    'is_trial' => 0,
                    'is_e_mandate' => 0,
                    'payment_status' => 'success',
                    'status' => 1,
                    'email_sent' => 0,
                    'wp_sent' => 0,
                    'used' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'expired_at' => now()->addYear(),
                ]);

            // 2. Create revenue record in crafty_vendor_mysql.revenue_history
            $revenueId = DB::connection('crafty_vendor_mysql')
                ->table('revenue_history')
                ->insertGetId([
                    'string_id' => 'earn_' . Str::random(16),
                    'purchase_id' => $purchaseId,
                    'payout_reference' => null,
                    'user_id' => $this->uid,
                    'vendor_amount' => $designerAmountPaisa, // Store in paise
                    'vendor_percentage' => (int)$designerPercentage,
                    'purchase_user_id' => $buyerUid,
                    'purchase_amount' => $amountPaisa, // Store in paise
                    'currency' => 'INR',
                    'type' => 'template',
                    'vendor_type' => 'freelancer',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // 3. Update design_submissions total_sales and total_revenue
            DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('id', $request->design_submission_id)
                ->increment('total_sales', 1);

            DB::connection('crafty_creator_mysql')
                ->table('design_submissions')
                ->where('id', $request->design_submission_id)
                ->increment('total_revenue', $designerAmount);

            // 4. Ensure vendor_account exists (balance is calculated from revenue_history, not stored)
            $vendorAccount = VendorAccount::where('user_id', $this->uid)->first();

            if (!$vendorAccount) {
                VendorAccount::create([
                    'user_id' => $this->uid,
                    'contact_id' => '',
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return $this->successed(
                msg: "Test purchase created successfully! Freelancer commission: {$designerPercentage}%",
                datas: [
                    'purchase_id' => $purchaseId,
                    'revenue_id' => $revenueId,
                    'design_id' => $design->id,
                    'design_title' => $design->title,
                    'purchase_amount' => $amount,
                    'designer_amount' => $designerAmount,
                    'designer_percentage' => $designerPercentage,
                    'buyer_uid' => $buyerUid,
                    'note' => "Commission rate is from wallet settings (http://localhost/craftyart-panel/vendor/wallet-settings)",
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed(
                statusCode: 500,
                msg: "Failed to create test purchase",
                datas: ['error' => $e->getMessage()]
            );
        }
    }

    // ============================================================================
    // DesignerCategoryApiController METHODS
    // ============================================================================

    /**
     * Get all designer categories
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        try {
            $query = DesignerCategory::query();

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Order by sort_order
            $categories = $query->orderBy('sort_order')->get();

            return $this->successed(msg: "Categories retrieved successfully", datas: [
                'categories' => $categories,
                'total' => $categories->count()
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve categories: " . $e->getMessage());
        }
    }

    /**
     * Get single category by ID
     *
     * @param Request $request
     * @param int $id
     */
    public function show(Request $request, $id)
    {
        try {
            $category = DesignerCategory::find($id);

            if (!$category) {
                return $this->failed(statusCode: 404, msg: "Category not found");
            }

            return $this->successed(msg: "Category retrieved successfully", datas: [
                'category' => $category
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to retrieve category: " . $e->getMessage());
        }
    }

    /**
     * Create new category
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->failed(statusCode: 422, msg: "Validation failed", datas: ['errors' => $validator->errors()->toArray()]);
            }

            $category = DesignerCategory::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);

            return $this->successed(msg: "Category created successfully", datas: [
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to create category: " . $e->getMessage());
        }
    }

    /**
     * Update existing category
     *
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        try {
            $category = DesignerCategory::find($id);

            if (!$category) {
                return $this->failed(statusCode: 404, msg: "Category not found");
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->failed(statusCode: 422, msg: "Validation failed", datas: ['errors' => $validator->errors()->toArray()]);
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? $category->sort_order,
            ]);

            return $this->successed(msg: "Category updated successfully", datas: [
                'category' => $category->fresh()
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to update category: " . $e->getMessage());
        }
    }

    /**
     * Toggle category active status
     *
     * @param Request $request
     * @param int $id
     */
    public function toggleActive(Request $request, $id)
    {
        try {
            $category = DesignerCategory::find($id);

            if (!$category) {
                return $this->failed(statusCode: 404, msg: "Category not found");
            }

            $category->is_active = !$category->is_active;
            $category->save();

            return $this->successed(msg: "Category active status updated successfully", datas: [
                'category' => $category
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to update category active status: " . $e->getMessage());
        }
    }

    /**
     * Delete category
     *
     * @param Request $request
     * @param int $id
     */
    public function destroy(Request $request, $id)
    {
        try {
            $category = DesignerCategory::find($id);

            if (!$category) {
                return $this->failed(statusCode: 404, msg: "Category not found");
            }

            $category->delete();

            return $this->successed(msg: "Category deleted successfully", datas: [
                'deleted_id' => $id
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: "Failed to delete category: " . $e->getMessage());
        }
    }

}

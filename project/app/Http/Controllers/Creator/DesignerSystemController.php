<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Creator\Designer\DesignerApplication;
use App\Models\Creator\Designer\DesignSubmission;
use App\Models\Creator\Designer\DesignSeoDetail;
use App\Models\Vendor\WalletSetting;
use App\Models\Vendor\RevenueHistory;
use App\Models\UserData;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\Utils\StorageUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Freelancer designer system. DB: crafty_creator.
 * Only user_data email can apply. On approve: user_data.creator=1 (no DesignerProfile needed).
 * Design + SEO by freelancer; design head approve → SEO manager approve → publish to crafty_db.designs.
 * Commission tracking via revenue_history with vendor_type='freelancer' using platform_commission_rate.
 */
class DesignerSystemController extends Controller
{
    public function applications(Request $request)
    {
        $status = $request->get('status', 'pending');
        $userDataEmails = UserData::take(20)->get()->pluck('email')->filter()->unique()->values()->toArray();

        $applications = DesignerApplication::with('reviewer')
            ->whereIn('email', $userDataEmails)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('creator.designer.applications', compact('applications', 'status'));
    }

    public function approveApplication(Request $request, $id)
    {
        $application = DesignerApplication::findOrFail($id);

        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Application already processed');
        }

        $request->validate([
            'designer_employment_type' => 'nullable|in:freelancer,company',
        ]);

        $walletSetting = WalletSetting::getDefault();
        $commissionRate = $walletSetting ? (float) $walletSetting->platform_commission_rate : 30.00;
        $withdrawalThreshold = $walletSetting ? (float) $walletSetting->min_withdrawal_threshold : 500.00;

        $defaultPassword = 'Designer@123';
        $hashedPassword = Hash::make($defaultPassword);
        $employmentType = $request->input('designer_employment_type', 'freelancer');

        DB::beginTransaction();
        try {
            // Check if user_data exists
            $userData = DB::connection('mysql')->table('user_data')
                ->where('email', $application->email)
                ->first();

            if (!$userData) {
                DB::rollBack();
                return redirect()->back()->with('error', 'User data not found for email: ' . $application->email);
            }

            // Update user_data to mark as creator
            DB::connection('mysql')->table('user_data')
                ->where('email', $application->email)
                ->update([
                    'creator' => 1,
                    'password' => $hashedPassword,
                    'updated_at' => now(),
                ]);

            // Update application status (user_id = user_data.id)
            $application->update([
                'status' => 'approved',
                'user_id' => $userData->id,
                'app_user_uid' => $userData->uid ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // REMOVED: DesignerProfile and DesignerWallet creation
            // Freelancer designers use user_data table directly
            // Commission tracking is done via revenue_history with vendor_type='freelancer'

            DB::commit();

            $message = 'Application approved! Designer account activated. Login via app with: ' . $application->email . ' / ' . $defaultPassword;
            return redirect()->back()->with('success', $message . ' (Wallet: ' . $commissionRate . '% commission, min withdraw ₹' . $withdrawalThreshold . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Designer application approval failed', [
                'application_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    public function rejectApplication(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $application = DesignerApplication::findOrFail($id);
        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Application already processed');
        }

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Application rejected');
    }

    public function designers(Request $request)
    {
        // Get approved designers from user_data (creator=1) with their applications
        $approvedApplications = DesignerApplication::where('status', 'approved')
            ->with('reviewer')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        // Get user_data for each approved application and calculate stats
        $designers = collect();
        foreach ($approvedApplications as $application) {
            $userData = UserData::where('email', $application->email)
                ->where('creator', 1)
                ->first();

            if ($userData) {
                // Calculate stats from design_submissions
                $totalDesigns = DesignSubmission::where('designer_id', $userData->id)->count();
                $liveDesigns = DesignSubmission::where('designer_id', $userData->id)
                    ->where('status', 'live')
                    ->count();

                // Calculate total earnings from revenue_history
                $totalEarnings = RevenueHistory::where('user_id', $userData->uid)
                    ->where('vendor_type', 'freelancer')
                    ->where('type', '!=', 'withdraw')
                    ->sum('vendor_amount') ?? 0;

                // Attach application and stats to userData for view
                $userData->application = $application;
                $userData->total_designs = $totalDesigns;
                $userData->live_designs = $liveDesigns;
                $userData->total_earnings = $totalEarnings;
                $userData->is_active = true; // All approved creators are active
                $userData->designer_employment_type = 'freelancer';

                // Get commission rate from wallet_settings
                $walletSetting = WalletSetting::getDefault();
                $userData->commission_rate = $walletSetting ? $walletSetting->platform_commission_rate : 30.00;

                $designers->push($userData);
            }
        }

        // Convert to paginator manually
        $page = $request->get('page', 1);
        $perPage = 20;
        $items = $designers->forPage($page, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $designers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('creator.designer.designers', compact('paginator'));
    }

    public function designSubmissions(Request $request)
    {
        $status = $request->get('status', 'all');
        $freelancerOnly = $request->boolean('freelancer', true);
        $searchQuery = trim($request->get('query', ''));

        $query = DesignSubmission::with(['designer', 'seoDetails']);

        // user_data lives on mysql (crafty_db); design_submissions on crafty_creator_mysql.
        // whereHas() runs the subquery on the parent connection — use whereIn + UserData::pluck instead.
        if ($freelancerOnly) {
            $freelancerIds = UserData::query()->where('creator', 1)->pluck('id')->all();
            if (count($freelancerIds) === 0) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('designer_id', $freelancerIds);
            }
        }

        if ($searchQuery !== '') {
            $like = '%' . $searchQuery . '%';
            $designerIdsForSearch = UserData::query()
                ->where(function ($uq) use ($like) {
                    $uq->where('email', 'like', $like)
                        ->orWhere('name', 'like', $like);
                })
                ->pluck('id')
                ->all();

            $query->where(function ($q) use ($like, $designerIdsForSearch) {
                $q->where('title', 'like', $like)
                    ->orWhere('string_id', 'like', $like)
                    ->orWhere('template_id', 'like', $like)
                    ->orWhere('app_user_uid', 'like', $like);

                if (count($designerIdsForSearch) > 0) {
                    $q->orWhereIn('designer_id', $designerIdsForSearch);
                }
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $designs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $total = $designs->total();
        $from = $total > 0 ? (($designs->currentPage() - 1) * $designs->perPage()) + 1 : 0;
        $to = min($designs->currentPage() * $designs->perPage(), $total);
        $countStr = $total === 0 ? 'Showing 0-0 of 0 entries' : "Showing {$from}-{$to} of {$total} entries";

        $newCategories = NewCategory::pluck('category_name', 'id');

        return view('creator.designer.design_submissions', compact('designs', 'status', 'freelancerOnly', 'countStr', 'newCategories', 'searchQuery'));
    }

    public function approveDesign(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $design = DesignSubmission::findOrFail($id);
        if ($design->status !== 'pending_designer_head') {
            return redirect()->back()->with('error', 'Design already processed');
        }
        $design->update([
            'status' => 'pending_seo',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => auth()->id(),
            'designer_head_reviewed_at' => now(),
        ]);
        $design->refresh();
        WebSocketBroadcastController::broadcastDesignSubmissionStatusChanged($design);

        return redirect()->back()->with('success', 'Design approved! Sent to SEO head.');
    }

    public function rejectDesign(Request $request, $id)
    {
        $request->validate(['notes' => 'required|string|min:10']);
        $design = DesignSubmission::findOrFail($id);
        if ($design->status !== 'pending_designer_head') {
            return redirect()->back()->with('error', 'Design already processed');
        }
        $design->update([
            'status' => 'rejected_by_designer_head',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => auth()->id(),
            'designer_head_reviewed_at' => now(),
        ]);
        $design->refresh();
        WebSocketBroadcastController::broadcastDesignSubmissionStatusChanged($design);

        return redirect()->back()->with('success', 'Design rejected');
    }

    public function editDesign(Request $request, $id)
    {
        $design = DesignSubmission::with(['designer', 'seoDetails'])->findOrFail($id);

        return view('creator.designer.edit_design', compact('design'));
    }

    public function updateDesign(Request $request, $id)
    {
        $design = DesignSubmission::findOrFail($id);

        if (!in_array($design->status, ['pending_designer_head', 'rejected_by_designer_head'], true)) {
            return redirect()
                ->route('designer_system.design.edit', $design->id)
                ->with('error', 'This design has already been approved by the design manager. Draft fields can no longer be edited on this page.');
        }

        $nullableNumeric = ['width', 'height', 'category_id', 'ratio'];
        $merge = [];
        foreach ($nullableNumeric as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $merge[$field] = null;
            }
        }
        if ($merge !== []) {
            $request->merge($merge);
        }

        $validated = $request->validate([
            'string_id' => ['nullable', 'string', 'max:128', Rule::unique(DesignSubmission::class, 'string_id')->ignore($design->id)],
            'template_id' => 'nullable|string|max:255',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
            'ratio' => 'nullable|numeric',
            'msg' => 'nullable|string|max:5000',
            'video' => 'nullable|string|max:5000',
            'thumbs' => 'nullable|string',
            'caricature_ids' => 'nullable|string',
            'designs' => 'nullable|string',
            'thumb_images' => 'nullable|array',
            'thumb_images.*' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video_file' => 'nullable|file|mimes:mp4,webm,mov,quicktime|max:102400',
        ]);

        $thumbs = is_array($design->thumbs) ? $design->thumbs : [];
        if ($request->has('thumbs')) {
            $raw = $request->input('thumbs');
            if ($raw === null || trim((string) $raw) === '') {
                $thumbs = [];
            } else {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    return redirect()->back()->withErrors(['thumbs' => 'Thumbs must be valid JSON (object or array).'])->withInput();
                }
                $thumbs = $decoded;
            }
        }

        $designerIdForPath = (int) $design->designer_id;
        $thumbs = $this->appendMultipleDesignThumbUploads($thumbs, $request, $designerIdForPath);
        if ($thumbs === []) {
            $thumbs = null;
        }

        $caricatureIds = $design->caricature_ids;
        if ($request->has('caricature_ids')) {
            $raw = $request->input('caricature_ids');
            if ($raw === null || trim((string) $raw) === '') {
                $caricatureIds = null;
            } else {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    return redirect()->back()->withErrors(['caricature_ids' => 'Caricature IDs must be a valid JSON array.'])->withInput();
                }
                $caricatureIds = $decoded;
            }
        }

        $designsBody = $design->designs;
        if ($request->has('designs')) {
            $raw = $request->input('designs');
            if ($raw === null || trim((string) $raw) === '') {
                $designsBody = null;
            } else {
                json_decode($raw);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->withErrors(['designs' => 'Designs must be valid JSON.'])->withInput();
                }
                $designsBody = $raw;
            }
        }

        $isLive = $design->is_live;
        if ($request->has('is_live')) {
            $rawLive = $request->input('is_live');
            if ($rawLive === null || $rawLive === '') {
                $isLive = null;
            } elseif (in_array((int) $rawLive, [-1, 0, 1], true)) {
                $isLive = (int) $rawLive;
            }
        }

        $stringId = array_key_exists('string_id', $validated)
            ? trim((string) ($validated['string_id'] ?? ''))
            : trim((string) ($design->string_id ?? ''));
        if ($stringId === '') {
            $stringId = $this->generateUniqueDesignSubmissionStringIdIgnoring((int) $design->id);
        }

        $videoStored = array_key_exists('video', $validated)
            ? (trim((string) ($validated['video'] ?? '')) !== '' ? trim((string) $validated['video']) : null)
            : $design->video;
        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            $videoStored = $request->file('video_file')->store('designs/' . $designerIdForPath . '/video', 'public');
        }

        $update = [
            'string_id' => $stringId,
            'template_id' => array_key_exists('template_id', $validated)
                ? (trim((string) $validated['template_id']) !== '' ? trim($validated['template_id']) : null)
                : $design->template_id,
            'width' => array_key_exists('width', $validated) ? $validated['width'] : $design->width,
            'height' => array_key_exists('height', $validated) ? $validated['height'] : $design->height,
            'ratio' => array_key_exists('ratio', $validated) ? $validated['ratio'] : $design->ratio,
            'msg' => array_key_exists('msg', $validated)
                ? (trim((string) ($validated['msg'] ?? '')) !== '' ? trim($validated['msg']) : null)
                : $design->msg,
            'video' => $videoStored,
            'is_live' => $isLive,
            'thumbs' => $thumbs,
            'caricature_ids' => $caricatureIds,
            'designs' => $designsBody,
        ];

        $design->update($update);

        return redirect()
            ->route('designer_system.design.edit', $design->id)
            ->with('success', 'Design #' . $design->id . ' updated successfully.');
    }

    /**
     * Append files from thumb_images[] (multiple input, same pattern as template edit_item Images).
     * New entries use keys extra_0, extra_1, … (skip occupied keys).
     */
    private function appendMultipleDesignThumbUploads(array $thumbs, Request $request, int $designerId): array
    {
        $files = $request->file('thumb_images', []);
        if ($files === null) {
            return $thumbs;
        }
        if (!is_array($files)) {
            $files = [$files];
        }
        $n = 0;
        while (array_key_exists('extra_' . $n, $thumbs)) {
            $n++;
        }
        $prefix = 'designs/' . $designerId . '/thumbs';
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            while (array_key_exists('extra_' . $n, $thumbs)) {
                $n++;
            }
            $thumbs['extra_' . $n] = $file->store($prefix, 'public');
            $n++;
        }

        return $thumbs;
    }

    /**
     * 8-char string_id using HelperController::generateStringIds; loop guarantees uniqueness on
     * design_submissions (excluding current row), designs, and categories.
     */
    private function generateUniqueDesignSubmissionStringIdIgnoring(int $ignoreSubmissionId): string
    {
        do {
            $id = HelperController::generateStringIds(8, '', DesignSubmission::class, 'string_id');
        } while (
            DesignSubmission::where('string_id', $id)->where('id', '!=', $ignoreSubmissionId)->exists()
            || Design::where('string_id', $id)->exists()
            || Category::where('string_id', $id)->exists()
        );

        return $id;
    }

    public function seoSubmissions(Request $request)
    {
        $params = array_merge($request->only(['freelancer', 'query']), ['status' => 'pending_seo']);
        return redirect()->route('designer_system.design_submissions', $params);
    }

    public function showDesignSeo($id)
    {
        $design = DesignSubmission::with(['designer', 'seoDetails'])->findOrFail($id);

        if ($design->status === 'pending_designer_head') {
            return redirect()
                ->route('designer_system.design.edit', $design->id)
                ->with('error', 'This design is still waiting for design manager approval. Use Approve or Reject on the Edit design page first; after approval you can open SEO review.');
        }

        if ($design->status === 'rejected_by_designer_head') {
            return redirect()
                ->route('designer_system.design.edit', $design->id)
                ->with('error', 'This design was rejected by the design manager. It cannot go to SEO until it is resubmitted and approved.');
        }

        // Load all filter data
        $languages = \App\Models\Language::where('status', 1)->orderBy('name')->get();
        $styles = \App\Models\Style::where('status', 1)->orderBy('name')->get();
        $religions = \App\Models\Religion::where('status', 1)->orderBy('religion_name')->get();
        $colors = \App\Models\Color::all();
        $newSearchTags = \App\Models\NewSearchTag::orderBy('name')->get();
        $specialKeywords = \App\Models\SpecialKeyword::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('category_name')->get();
        $searchTags = \App\Models\SearchTag::orderBy('name')->get();

        // Category-specific filters
        $themes = [];
        $interests = [];
        $sizes = [];

        if ($design->category_id) {
            $category = NewCategory::find($design->category_id);
            if ($category) {
                $rootParentId = $category->getRootParentId();
                $rootParentId = $rootParentId ?: $design->category_id;
                $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);

                $themes = \App\Models\Theme::whereJsonContains('new_category_id', $catId)
                    ->where('status', 1)->orderBy('name')->get();
                $interests = \App\Models\Interest::whereJsonContains('new_category_id', $catId)
                    ->where('status', 1)->orderBy('name')->get();
                $sizes = \App\Models\Size::whereJsonContains('new_category_id', $catId)
                    ->where('status', 1)->orderBy('size_name')->get();
            }
        } else {
            // Load all if no category
            $themes = \App\Models\Theme::where('status', 1)->orderBy('name')->get();
            $interests = \App\Models\Interest::where('status', 1)->orderBy('name')->get();
            $sizes = \App\Models\Size::where('status', 1)->orderBy('size_name')->get();
        }

        $allCategories = NewCategory::getAllCategoriesWithSubcategories(1);
        $newCatId = $design->seoDetails?->primary_category_id ?? $design->category_id;
        $selectCategory = $newCatId ? NewCategory::find($newCatId) : null;

        $publishedDesignForSeo = $design->crafty_design_id
            ? Design::query()->find($design->crafty_design_id)
            : null;
        $designerSeoStringId = $this->designerSeoFormStringId($design, $publishedDesignForSeo);
        $designerSeoIdNameStripPrefixes = $this->designerSeoIdNameStripPrefixes($design, $publishedDesignForSeo);

        return view('creator.designer.edit_seo_design', compact(
            'design',
            'languages',
            'themes',
            'styles',
            'religions',
            'interests',
            'colors',
            'sizes',
            'newSearchTags',
            'specialKeywords',
            'categories',
            'searchTags',
            'allCategories',
            'selectCategory',
            'designerSeoStringId',
            'designerSeoIdNameStripPrefixes'
        ));
    }

    /**
     * Same as office edit_seo_item: crafty_db.designs.string_id when published, else design_submissions.string_id, else p{id}.
     */
    private function designerSeoFormStringId(DesignSubmission $design, ?Design $publishedCraftyRow): string
    {
        $pub = $publishedCraftyRow?->string_id ? trim((string) $publishedCraftyRow->string_id) : '';
        if ($pub !== '') {
            return $pub;
        }
        $sub = trim((string) ($design->string_id ?? ''));
        if ($sub !== '') {
            return $sub;
        }

        return 'p' . $design->id;
    }

    /**
     * Prefixes to strip when reading id_name from DB or old() (migration from p{id} to real string_id).
     *
     * @return list<string>
     */
    private function designerSeoIdNameStripPrefixes(DesignSubmission $design, ?Design $publishedCraftyRow): array
    {
        return array_values(array_unique(array_filter([
            $publishedCraftyRow?->string_id ? trim((string) $publishedCraftyRow->string_id) : null,
            trim((string) ($design->string_id ?? '')) !== '' ? trim((string) $design->string_id) : null,
            'p' . $design->id,
            $this->designerSeoFormStringId($design, $publishedCraftyRow),
        ], fn ($v) => $v !== null && $v !== '')));
    }

    /**
     * First publish: reuse submission string_id when unique on designs; else random 8-char.
     */
    private function designerPublishPreferSubmissionStringId(DesignSubmission $design): string
    {
        $candidate = trim((string) ($design->string_id ?? ''));
        if ($candidate === '') {
            return TemplateController::generateId(8);
        }
        $ignoreCraftyId = (int) ($design->crafty_design_id ?? 0);
        $taken = Design::query()
            ->where('string_id', $candidate)
            ->when($ignoreCraftyId > 0, fn ($q) => $q->where('id', '!=', $ignoreCraftyId))
            ->exists();

        return $taken ? TemplateController::generateId(8) : $candidate;
    }

    /**
     * Create or update design_seo_details (full SEO form from admin SEO page).
     */
    public function updateSeoDetails(Request $request, $id)
    {
        $design = DesignSubmission::with('seoDetails')->findOrFail($id);

        if (in_array($design->status, ['pending_designer_head', 'rejected_by_designer_head'], true)) {
            return redirect()
                ->route('designer_system.design.edit', $design->id)
                ->with('error', 'SEO fields cannot be edited until the design manager has approved this submission.');
        }

        $nullableNumeric = ['new_category_id', 'legacy_category_id', 'width', 'height', 'template_size'];
        $merge = [];
        foreach ($nullableNumeric as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $merge[$field] = null;
            }
        }
        if ($merge !== []) {
            $request->merge($merge);
        }

        $seoId = $design->seoDetails?->id;

        $publishedDesignForVal = $design->crafty_design_id
            ? Design::query()->find($design->crafty_design_id)
            : null;
        $formStrId = $this->designerSeoFormStringId($design, $publishedDesignForVal);
        $idNameStripPrefixes = $this->designerSeoIdNameStripPrefixes($design, $publishedDesignForVal);

        $validated = $request->validate([
            'post_name' => [
                'nullable',
                'string',
                'max:60',
                function (string $attribute, $value, \Closure $fail) use ($request) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $meta = $request->input('meta_title');
                    if ($meta !== null && $meta !== '' && strcasecmp(trim((string) $value), trim((string) $meta)) === 0) {
                        $fail('Post Name and Meta Title must not be the same.');
                    }
                },
            ],
            'id_name' => [
                'nullable',
                'string',
                'max:280',
                function (string $attribute, $value, \Closure $fail) use ($design, $formStrId, $idNameStripPrefixes) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $segment = trim((string) $value);
                    foreach ($idNameStripPrefixes as $pfx) {
                        if ($pfx !== '' && Str::startsWith($segment, $pfx . '-')) {
                            $segment = substr($segment, strlen($pfx) + 1);
                            break;
                        }
                    }
                    if (preg_match('/\s/', $segment)) {
                        $fail('ID Name must not contain whitespace.');

                        return;
                    }
                    if (preg_match('/[A-Z]/', $segment)) {
                        $fail('ID Name must be lowercase.');

                        return;
                    }
                    if (preg_match('/[^\w\s-]/', $segment)) {
                        $fail('ID Name contains invalid characters.');

                        return;
                    }
                    $slugSeg = Str::slug($segment, '-');
                    if ($slugSeg === '') {
                        $fail('ID Name must contain a valid URL segment.');

                        return;
                    }
                    if (strlen($slugSeg) > 200) {
                        $fail('ID Name segment is too long.');

                        return;
                    }
                    if ($formStrId !== '') {
                        $full = $formStrId . '-' . $slugSeg;
                        $q = Design::query()->where('id_name', $full);
                        if ($design->crafty_design_id) {
                            $q->where('id', '!=', $design->crafty_design_id);
                        }
                        if ($q->exists()) {
                            $fail('This ID name is already used by another design.');
                        }
                    }
                },
            ],
            'h2_tag' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:2000',
            'keywords' => 'nullable|string',
            'new_category_id' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, $value, \Closure $fail) {
                    $row = NewCategory::query()->find((int) $value);
                    if (!$row || (int) $row->parent_category_id === 0) {
                        $fail('Use a child category (not a parent-only category).');
                    }
                },
            ],
            'legacy_category_id' => 'nullable|integer|min:0',
            'canonical_link' => 'nullable|string|max:2048',
            'special_keywords' => 'nullable|array',
            'special_keywords.*' => 'integer',
            'width' => 'nullable|integer|min:0|max:50000',
            'height' => 'nullable|integer|min:0|max:50000',

            // New filter fields
            'lang_id' => 'nullable|array',
            'lang_id.*' => 'integer',
            'theme_id' => 'nullable|array',
            'theme_id.*' => 'integer',
            'styles' => 'nullable|array',
            'styles.*' => 'integer',
            'orientation' => 'nullable|in:portrait,landscape,square',
            'template_size' => 'nullable|integer',
            'religion_id' => 'nullable|array',
            'religion_id.*' => 'integer',
            'interest_id' => 'nullable|array',
            'interest_id.*' => 'integer',
            'is_premium' => 'nullable|boolean',
            'is_freemium' => 'nullable|boolean',
            'date_range' => 'nullable|string',
            'color_ids' => 'nullable|string',
            'new_keywords' => [
                'nullable',
                'string',
                'max:8000',
                function (string $attribute, $value, \Closure $fail) {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }
                    foreach (array_map('trim', explode(',', (string) $value)) as $t) {
                        if ($t === '') {
                            continue;
                        }
                        if (strlen($t) > 255) {
                            $fail('Each sub category tag must be 255 characters or fewer.');

                            return;
                        }
                    }
                },
            ],
            'ratio_field' => 'nullable|numeric',
            'status_field' => 'nullable|boolean',
            'post_thumb' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'additional_thumb' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'remove_additional_thumb' => 'nullable|boolean',
        ]);

        $keywordsRaw = $validated['keywords'] ?? '';
        unset($validated['keywords']);
        $keywordList = array_values(array_filter(array_map('trim', explode(',', (string) $keywordsRaw))));

        $baseFilters = [];
        if ($design->seoDetails && is_array($design->seoDetails->filters)) {
            $baseFilters = $design->seoDetails->filters;
        }
        $validated['filters'] = $this->mergeDesignerSeoFormIntoFilters($baseFilters, $request);

        $validated['keywords'] = $keywordList;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_trending'] = $request->boolean('is_trending');

        if (array_key_exists('id_name', $validated)) {
            $rawId = $validated['id_name'];
            if ($rawId === null || $rawId === '') {
                $validated['id_name'] = null;
            } else {
                $segment = trim((string) $rawId);
                foreach ($idNameStripPrefixes as $pfx) {
                    if ($pfx !== '' && Str::startsWith($segment, $pfx . '-')) {
                        $segment = substr($segment, strlen($pfx) + 1);
                        break;
                    }
                }
                $validated['id_name'] = Str::slug($segment, '-') ?: null;
            }
        }

        $nid = $validated['new_category_id'] ?? null;
        $validated['primary_category_id'] = ($nid !== null && (int) $nid > 0) ? (int) $nid : null;
        unset($validated['new_category_id']);

        unset($validated['post_thumb'], $validated['additional_thumb'], $validated['remove_additional_thumb']);

        $existingSeo = $design->seoDetails;
        if ($request->hasFile('post_thumb')) {
            $postThumbFile = $request->file('post_thumb');
            if ($existingSeo && $existingSeo->post_thumb) {
                StorageUtils::delete($existingSeo->post_thumb);
            }
            $newName = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $postThumbFile->getClientOriginalExtension();
            StorageUtils::storeAs($postThumbFile, 'uploadedFiles/thumb_file', $newName);
            $validated['post_thumb'] = 'uploadedFiles/thumb_file/' . $newName;
        }

        if ($request->boolean('remove_additional_thumb')) {
            if ($existingSeo && $existingSeo->additional_thumb) {
                StorageUtils::delete($existingSeo->additional_thumb);
            }
            $validated['additional_thumb'] = null;
        } elseif ($request->hasFile('additional_thumb')) {
            $addThumbFile = $request->file('additional_thumb');
            if ($existingSeo && $existingSeo->additional_thumb) {
                StorageUtils::delete($existingSeo->additional_thumb);
            }
            $newName = bin2hex(random_bytes(20)) . Carbon::now()->timestamp . '.' . $addThumbFile->getClientOriginalExtension();
            StorageUtils::storeAs($addThumbFile, 'uploadedFiles/thumb_file', $newName);
            $validated['additional_thumb'] = 'uploadedFiles/thumb_file/' . $newName;
        }

        foreach (['canonical_link', 'special_keywords', 'legacy_category_id'] as $nonColumn) {
            unset($validated[$nonColumn]);
        }

        if ($design->seoDetails) {
            $design->seoDetails->update($validated);
        } else {
            $validated['design_submission_id'] = $design->id;
            DesignSeoDetail::create($validated);
        }

        // If design is published, update the crafty_db.designs table with filter fields
        if ($design->crafty_design_id) {
            $designUpdate = [];

            // Process filter fields for crafty_db.designs
            if ($request->has('lang_id')) {
                $designUpdate['lang_id'] = json_encode($request->input('lang_id', []));
            }
            if ($request->has('theme_id')) {
                $designUpdate['theme_id'] = json_encode($request->input('theme_id', []));
            }
            if ($request->has('styles')) {
                $designUpdate['style_id'] = json_encode($request->input('styles', []));
            }
            if ($request->has('orientation')) {
                $designUpdate['orientation'] = $request->input('orientation');
            }
            if ($request->has('template_size')) {
                $designUpdate['template_size'] = $request->input('template_size');
            }
            if ($request->has('religion_id')) {
                $designUpdate['religion_id'] = json_encode($request->input('religion_id', []));
            }
            if ($request->has('interest_id')) {
                $designUpdate['interest_id'] = json_encode($request->input('interest_id', []));
            }
            if ($request->has('is_premium')) {
                $designUpdate['is_premium'] = $request->boolean('is_premium');
            }
            if ($request->has('is_freemium')) {
                $designUpdate['is_freemium'] = $request->boolean('is_freemium');
            }

            // Process date range (daterangepicker: "MM/DD/YYYY - MM/DD/YYYY")
            if ($request->filled('date_range')) {
                $parts = array_map('trim', explode(' - ', (string) $request->date_range, 2));
                if (count($parts) === 2) {
                    $designUpdate['start_date'] = $parts[0];
                    $designUpdate['end_date'] = $parts[1];
                }
            }

            if ($request->filled('canonical_link')) {
                $designUpdate['canonical_link'] = trim((string) $request->canonical_link);
            }

            if ($request->has('special_keywords')) {
                $sk = array_values(array_filter(array_map('intval', $request->input('special_keywords', []))));
                $designUpdate['special_keywords'] = json_encode($sk);
            }

            if ($request->filled('legacy_category_id')) {
                $designUpdate['category_id'] = (int) $request->legacy_category_id;
            }

            // Process colors
            if ($request->filled('color_ids')) {
                $colors = array_filter(explode(',', $request->color_ids));
                $designUpdate['color_id'] = json_encode($colors);
            }

            // Process new keywords (sub category tags) — new_search_tags requires new_category_id + status
            if ($request->filled('new_keywords')) {
                $tagIds = $this->resolveNewSearchTagIdsFromCommaSeparatedKeywords($request);
                $designUpdate['new_related_tags'] = json_encode($tagIds);
            }

            // Process ratio and status
            if ($request->filled('ratio_field')) {
                $designUpdate['ratio'] = $request->input('ratio_field');
            }
            if ($request->has('status_field')) {
                $designUpdate['status'] = $request->boolean('status_field');
            }

            if ($request->hasFile('post_thumb') || $request->hasFile('additional_thumb') || $request->boolean('remove_additional_thumb')) {
                $design->refresh();
                $design->load('seoDetails');
                $s = $design->seoDetails;
                if ($s) {
                    if (!empty($s->post_thumb)) {
                        $designUpdate['post_thumb'] = $s->post_thumb;
                    }
                    $designUpdate['additional_thumb'] = $s->additional_thumb;
                }
            }

            // Update the published design if we have changes
            if (!empty($designUpdate)) {
                \App\Models\Design::where('id', $design->crafty_design_id)->update($designUpdate);
            }
        }

        return redirect()->route('designer_system.design.seo', $design->id)->with('success', 'SEO data saved successfully.');
    }

    /**
     * Legacy designs.category_id: parent new_categories.id when the template uses a child category; else the category id itself.
     */
    private function resolveDesignLegacyCategoryId(int $newCategoryId): int
    {
        if ($newCategoryId <= 0) {
            return 0;
        }
        $cat = NewCategory::query()->find($newCategoryId);
        if (!$cat) {
            return 0;
        }
        $parentId = (int) ($cat->parent_category_id ?? 0);

        return $parentId !== 0 ? $parentId : $newCategoryId;
    }

    /**
     * Visiting /design/{id}/publish in the browser uses GET — publish must be POST from the SEO page form.
     */
    public function publishDesignRedirect(string $id)
    {
        return redirect()
            ->route('designer_system.design.seo', $id)
            ->with('info', 'Open this page and use the green “Publish to crafty_db.designs” button. Publishing only works via POST (form submit), not by opening this URL directly.');
    }

    public function publishDesign(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);
        $design = DesignSubmission::with(['seoDetails', 'designer'])->findOrFail($id);
        if ($design->status !== 'pending_seo') {
            return redirect()->back()->with('error', 'Design is not pending SEO approval');
        }

        $seo = $design->seoDetails;
        if (!$seo || !trim((string) $seo->meta_title) || !trim((string) $seo->meta_description)) {
            return redirect()->back()->with('error', 'Save SEO data first: Meta title and Meta description are required before publishing.');
        }

        if (!trim((string) $seo->slug)) {
            $baseSlug = \Illuminate\Support\Str::slug($seo->meta_title) . '-' . $design->id;
            try {
                $seo->update(['slug' => $baseSlug]);
            } catch (\Throwable $e) {
                $seo->update(['slug' => $baseSlug . '-' . substr(uniqid(), -4)]);
            }
        }

        $design->refresh();
        $design->load('seoDetails');
        $seo = $design->seoDetails;
        $postName = $seo->post_name ?? $design->title;

        $width = (int) ($seo->width ?? $design->width ?? 1080);
        $height = (int) ($seo->height ?? $design->height ?? 1080);
        if ($height < 1) {
            $height = 1;
        }
        // Same style as TemplateController SEO save (numeric ratio, not "1080:1080")
        $ratio = round($width / $height, 2);

        $postThumb = '';
        if ($seo && !empty($seo->post_thumb)) {
            $postThumb = (string) $seo->post_thumb;
        } elseif ($design->preview_images && is_array($design->preview_images) && count($design->preview_images) > 0) {
            $postThumb = $design->preview_images[0];
        } elseif ($design->thumbs && is_array($design->thumbs) && !empty($design->thumbs['thumb'])) {
            $postThumb = (string) $design->thumbs['thumb'];
        } elseif ($design->design_file_path) {
            $postThumb = $design->design_file_path;
        }

        $thumbPaths = [];
        if ($seo && !empty($seo->post_thumb)) {
            $thumbPaths[] = (string) $seo->post_thumb;
        }
        if ($seo && !empty($seo->additional_thumb)) {
            $thumbPaths[] = (string) $seo->additional_thumb;
        }
        if ($thumbPaths === []) {
            if ($design->preview_images && is_array($design->preview_images) && count($design->preview_images) > 0) {
                $thumbPaths = array_values($design->preview_images);
            } elseif ($design->thumbs && is_array($design->thumbs) && !empty($design->thumbs['thumb'])) {
                $thumbPaths = [(string) $design->thumbs['thumb']];
            } elseif ($postThumb !== '') {
                $thumbPaths = [$postThumb];
            }
        }

        $filters = is_array($seo->filters ?? null) ? $seo->filters : [];

        $newCategoryId = (int) ($seo->primary_category_id ?? $design->category_id ?? 0);
        $categoryId = $this->resolveDesignLegacyCategoryId($newCategoryId);

        // string_id: keep crafty row; new row prefers design_submissions.string_id (edit_seo_item parity), else generate 8-char
        if ($design->crafty_design_id) {
            $existingDesign = Design::query()->find($design->crafty_design_id);
            $stringId = $existingDesign && $existingDesign->string_id
                ? trim((string) $existingDesign->string_id)
                : $this->designerPublishPreferSubmissionStringId($design);
        } else {
            $stringId = $this->designerPublishPreferSubmissionStringId($design);
        }

        // id_name = {string_id}-{slug} (matches edit_seo_item flow)
        $slugSource = $seo->id_name ? (string) $seo->id_name : (string) $postName;
        $slugPart = Str::slug($slugSource, '-');
        if ($slugPart === '') {
            $slugPart = Str::slug($postName, '-') ?: 'design';
        }
        $fullIdName = $stringId . '-' . $slugPart;
        $canonicalLink = rtrim(trim(HelperController::$webPageUrl, '/'), '/') . '/templates/p/' . $fullIdName;

        $keywords = is_array($seo->keywords ?? null) ? $seo->keywords : [];
        $relatedTagsJson = json_encode(array_values($keywords));

        /** @var UserData|null $freelancer */
        $freelancer = $design->designer;
        $creatorId = ($freelancer && !empty($freelancer->uid)) ? (string) $freelancer->uid : null;

        $publisherId = (int) (auth()->id() ?? 0);

        $craftyPayload = [
            'creator_id' => $creatorId,
            'emp_id' => $publisherId,
            'seo_emp_id' => $publisherId,
            'seo_assigner_id' => $publisherId,
            'post_name' => $postName,
            'id_name' => $fullIdName,
            'h2_tag' => $seo->h2_tag ?? $postName,
            'meta_title' => $seo->meta_title ?? $postName,
            'meta_description' => $seo->meta_description ?? $design->description ?? '',
            'description' => $seo->description ?? $design->description ?? '',
            'new_category_id' => $newCategoryId,
            'category_id' => $categoryId,
            // Office rows store ratio as string (e.g. "0.4", "1.75"); optional override from SEO form (edit_seo_item parity)
            'ratio' => (string) $ratio,
            'width' => $width,
            'height' => $height,
            'post_thumb' => $postThumb ?: 'placeholder.jpg',
            'canonical_link' => $canonicalLink,
            'related_tags' => $relatedTagsJson,
            'app_id' => 1,
            'is_premium' => array_key_exists('is_premium', $filters) ? ($filters['is_premium'] ? 1 : 0) : 0,
            'is_freemium' => array_key_exists('is_freemium', $filters) ? ($filters['is_freemium'] ? 1 : 0) : 0,
            'editor_choice' => 0,
            'status' => array_key_exists('publish_status_live', $filters) ? ($filters['publish_status_live'] ? 1 : 0) : 1,
            'deleted' => 0,
            'latest' => 1,
            'no_index' => 0,
            'pinned' => 0,
            'views' => 0,
            'web_views' => 0,
            'trending_views' => 0,
        ];

        if ($seo->additional_thumb) {
            $craftyPayload['additional_thumb'] = $seo->additional_thumb;
        }

        foreach ($this->buildDesignsRowOfficeShape($filters, $thumbPaths, $width, $height) as $key => $value) {
            if ($value !== null) {
                $craftyPayload[$key] = $value;
            }
        }

        if (!empty($filters['canonical_link']) && is_string($filters['canonical_link']) && trim($filters['canonical_link']) !== '') {
            $craftyPayload['canonical_link'] = trim($filters['canonical_link']);
        }

        if (!empty($filters['special_keyword_ids']) && is_array($filters['special_keyword_ids'])) {
            $craftyPayload['special_keywords'] = json_encode(array_values(array_map('intval', $filters['special_keyword_ids'])));
        }

        if (!empty($filters['legacy_category_id'])) {
            $craftyPayload['category_id'] = (int) $filters['legacy_category_id'];
        }

        if (!empty($filters['ratio_field']) || (isset($filters['ratio_field']) && $filters['ratio_field'] === '0')) {
            $craftyPayload['ratio'] = (string) $filters['ratio_field'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $craftyPayload['start_date'] = $filters['start_date'];
            $craftyPayload['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['new_related_tag_ids']) && is_array($filters['new_related_tag_ids'])) {
            $craftyPayload['new_related_tags'] = json_encode(array_values(array_map('intval', $filters['new_related_tag_ids'])));
        }

        $designsConn = (new Design)->getConnectionName();
        $designsTable = (new Design)->getTable();
        if (Schema::connection($designsConn)->hasColumn($designsTable, 'designer_id')) {
            $craftyPayload['designer_id'] = (int) $design->designer_id;
        }

        if ($design->crafty_design_id) {
            Design::where('id', $design->crafty_design_id)->update($craftyPayload);
        } else {
            $craftyPayload['string_id'] = $stringId;
            $craftyDesign = Design::create($craftyPayload);
            $design->crafty_design_id = $craftyDesign->id;
        }

        // Update design_submissions status
        $design->update([
            'status' => 'live',
            'seo_head_notes' => $request->notes,
            'seo_head_reviewed_by' => auth()->id(),
            'seo_head_reviewed_at' => now(),
            'published_at' => now(),
            'crafty_design_id' => $design->crafty_design_id,
        ]);
        $design->refresh();
        WebSocketBroadcastController::broadcastDesignSubmissionStatusChanged($design);

        // Update designer_drafts is_live status for freelancer (if exists)
        DB::connection('mysql')->table('designer_drafts')
            ->where('design_submission_id', $design->id)
            ->where('freelancer_designer_id', $design->designer_id)
            ->update([
                'is_live' => 1, // Mark as live/approved
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('designer_system.design.seo', $design->id)
            ->with('success', 'SEO approved: row created/updated in crafty_db.designs. Open template editor: edit_seo_item/' . $design->crafty_design_id);
    }

    public function rejectDesignBySeo(Request $request, $id)
    {
        $request->validate(['notes' => 'required|string|min:10']);
        $design = DesignSubmission::findOrFail($id);
        if ($design->status !== 'pending_seo') {
            return redirect()
                ->route('designer_system.design.seo', $design->id)
                ->with('error', 'Design is not pending SEO approval.');
        }
        $design->update([
            'status' => 'rejected_by_seo',
            'seo_head_notes' => $request->notes,
            'seo_head_reviewed_by' => auth()->id(),
            'seo_head_reviewed_at' => now(),
        ]);
        $design->refresh();
        WebSocketBroadcastController::broadcastDesignSubmissionStatusChanged($design);

        return redirect()
            ->route('designer_system.design.seo', $design->id)
            ->with('success', 'Design rejected by SEO. The freelancer can fix SEO and reapply.');
    }

    /**
     * Reapply SEO after rejection - changes status back to pending_seo
     */
    public function reapplySeo(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);

        $design = DesignSubmission::findOrFail($id);

        if ($design->status !== 'rejected_by_seo') {
            return redirect()->back()->with('error', 'Only rejected designs can be reapplied');
        }

        // Change status back to pending_seo for review
        $design->update([
            'status' => 'pending_seo',
            'designer_head_notes' => $request->notes ? 'Reapplied: ' . $request->notes : 'Reapplied for SEO approval',
            'designer_head_reviewed_at' => now(),
        ]);
        $design->refresh();
        WebSocketBroadcastController::broadcastDesignSubmissionStatusChanged($design);

        return redirect()->route('designer_system.design.seo', $design->id)
            ->with('success', 'Design reapplied for SEO approval successfully!');
    }

    /**
     * Persist Filter Row + Others row from the designer SEO form into design_seo_details.filters
     * so first publish (no crafty row yet) still gets lang/theme/style/etc. on crafty_db.designs.
     *
     * @param  array<string, mixed>  $base  Existing design_seo_details.filters (merged with form fields)
     * @return array<string, mixed>
     */
    private function mergeDesignerSeoFormIntoFilters(array $base, Request $request): array
    {
        $out = $base;

        if ($request->has('lang_id')) {
            $out['language_ids'] = array_values(array_filter(array_map('intval', $request->input('lang_id', []))));
        }
        if ($request->has('theme_id')) {
            $out['theme_ids'] = array_values(array_filter(array_map('intval', $request->input('theme_id', []))));
        }
        if ($request->has('styles')) {
            $out['style_ids'] = array_values(array_filter(array_map('intval', $request->input('styles', []))));
        }
        if ($request->has('interest_id')) {
            $out['interest_ids'] = array_values(array_filter(array_map('intval', $request->input('interest_id', []))));
        }
        if ($request->has('religion_id')) {
            $out['religion_ids'] = array_values(array_filter(array_map('intval', $request->input('religion_id', []))));
        }
        if ($request->filled('color_ids')) {
            $out['colors'] = array_values(array_filter(array_map('trim', explode(',', (string) $request->color_ids))));
        }
        if ($request->filled('orientation')) {
            $out['orientation'] = $request->input('orientation');
        }
        if ($request->has('template_size')) {
            $ts = $request->input('template_size');
            if ($ts !== null && $ts !== '') {
                $out['size_id'] = (int) $ts;
            } else {
                unset($out['size_id']);
            }
        }
        if ($request->filled('ratio_field') || $request->input('ratio_field') === '0' || $request->input('ratio_field') === 0) {
            $out['ratio_field'] = $request->input('ratio_field');
        }
        if ($request->has('status_field')) {
            $out['publish_status_live'] = $request->boolean('status_field');
        }
        if ($request->has('is_premium')) {
            $out['is_premium'] = $request->boolean('is_premium');
        }
        if ($request->has('is_freemium')) {
            $out['is_freemium'] = $request->boolean('is_freemium');
        }
        if ($request->filled('date_range')) {
            $parts = array_map('trim', explode(' - ', (string) $request->date_range, 2));
            if (count($parts) === 2) {
                $out['start_date'] = $parts[0];
                $out['end_date'] = $parts[1];
            }
        }

        if ($request->has('canonical_link')) {
            $c = trim((string) $request->input('canonical_link', ''));
            if ($c !== '') {
                $out['canonical_link'] = $c;
            } else {
                unset($out['canonical_link']);
            }
        }

        if ($request->has('special_keywords')) {
            $sk = array_values(array_filter(array_map('intval', $request->input('special_keywords', []))));
            if ($sk !== []) {
                $out['special_keyword_ids'] = $sk;
            } else {
                unset($out['special_keyword_ids']);
            }
        }

        if ($request->has('legacy_category_id')) {
            $lc = $request->input('legacy_category_id');
            if ($lc !== null && $lc !== '') {
                $out['legacy_category_id'] = (int) $lc;
            } else {
                unset($out['legacy_category_id']);
            }
        }

        if ($request->has('new_category_id')) {
            $pc = $request->input('new_category_id');
            if ($pc !== null && $pc !== '' && (int) $pc > 0) {
                $out['new_category_id'] = (int) $pc;
            } else {
                unset($out['new_category_id']);
            }
        }
        if ($request->filled('new_keywords')) {
            $tagIds = $this->resolveNewSearchTagIdsFromCommaSeparatedKeywords($request);
            if ($tagIds !== []) {
                $out['new_related_tag_ids'] = $tagIds;
            }
        }

        return $out;
    }

    /**
     * Create or resolve new_search_tags rows scoped to the primary new_category_id (same JSON shape as NewSearchTagController::store).
     *
     * @return list<int>
     *
     * @throws ValidationException
     */
    private function resolveNewSearchTagIdsFromCommaSeparatedKeywords(Request $request): array
    {
        $raw = trim((string) $request->input('new_keywords', ''));
        if ($raw === '') {
            return [];
        }

        $primaryCat = (int) $request->input('new_category_id', 0);
        if ($primaryCat < 1) {
            throw ValidationException::withMessages([
                'new_category_id' => ['Select a primary (child) category before adding sub category tags.'],
                'new_keywords' => ['Sub category tags require a valid primary category.'],
            ]);
        }

        $catJson = json_encode([(string) $primaryCat]);
        $empId = auth()->id();
        $tagIds = [];

        foreach (explode(',', $raw) as $tagName) {
            $tagName = trim($tagName);
            if ($tagName === '') {
                continue;
            }

            $tag = NewSearchTag::firstOrCreate(
                [
                    'name' => $tagName,
                    'new_category_id' => $catJson,
                ],
                [
                    'status' => 1,
                    'emp_id' => $empId,
                ]
            );
            $tagIds[] = (int) $tag->id;
        }

        return $tagIds;
    }

    /**
     * Map SEO filters (panel JSON / API shape) to crafty_db.designs columns like office templates:
     * style_id, interest_id, lang_id, theme_id, religion_id, color_id as JSON strings;
     * thumb_array, orientation, total_pages, cta, new_related_tags, etc.
     *
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $thumbPaths
     * @return array<string, mixed>
     */
    private function buildDesignsRowOfficeShape(array $filters, array $thumbPaths, int $width, int $height): array
    {
        $orientation = $filters['orientation'] ?? null;
        if (!in_array($orientation, ['portrait', 'landscape', 'square'], true)) {
            if ($width > $height) {
                $orientation = 'landscape';
            } elseif ($width < $height) {
                $orientation = 'portrait';
            } else {
                $orientation = 'square';
            }
        }
        // Stored values in legacy rows are portrait/landscape only
        if ($orientation === 'square') {
            $orientation = 'portrait';
        }

        $sizeId = $filters['size_id'] ?? null;
        $templateSize = ($sizeId !== null && $sizeId !== '') ? (string) (int) $sizeId : null;

        $out = [
            'style_id' => $this->encodeDesignsJsonIdList($filters['style_ids'] ?? []),
            'interest_id' => $this->encodeDesignsJsonIdList($filters['interest_ids'] ?? []),
            'lang_id' => $this->encodeDesignsJsonIdList($filters['language_ids'] ?? []),
            'theme_id' => $this->encodeDesignsJsonIdList($filters['theme_ids'] ?? []),
            'religion_id' => $this->encodeDesignsJsonIdList($filters['religion_ids'] ?? []),
            'color_id' => $this->encodeDesignsColorList($filters['colors'] ?? []),
            'orientation' => $orientation,
            'total_pages' => 1,
            'default_thumb_pos' => 0,
            'cta' => '[]',
            'new_related_tags' => '[]',
            'auto_create' => 0,
            'animation' => 0,
            'size' => 0,
        ];

        if ($thumbPaths !== []) {
            $out['thumb_array'] = json_encode(array_values($thumbPaths));
        }

        if ($templateSize !== null) {
            $out['template_size'] = $templateSize;
        }

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $ids
     */
    private function encodeDesignsJsonIdList(array $ids): ?string
    {
        $out = [];
        foreach (array_values($ids) as $v) {
            if ($v === '' || $v === null) {
                continue;
            }
            if (is_numeric($v)) {
                $out[] = (string) (int) $v;
            }
        }

        return $out === [] ? null : json_encode($out);
    }

    /**
     * @param  array<int|string, mixed>  $colors
     */
    private function encodeDesignsColorList(array $colors): ?string
    {
        $out = [];
        foreach (array_values($colors) as $c) {
            if (is_string($c) && $c !== '') {
                $out[] = $c;
            }
        }

        return $out === [] ? null : json_encode($out);
    }

    // REMOVED: Old designer withdrawal methods
    // Freelancer withdrawals are now handled via vendor withdrawal system
    // See: /vendor/withdrawals (VendorManagementController)
    // API: POST /freelancer/withdraw (VendorController::freelancerWithdraw)
}

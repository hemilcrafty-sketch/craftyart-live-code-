<?php

namespace App\Http\Controllers\Video;

use App\Enums\UserRole;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Http\Controllers\Controller;
use App\Models\Video\VideoSearchTag;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoSlugHistory;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoType;
use App\Models\Video\VideoLanguage;
use App\Models\Video\VideoTheme;
use App\Models\Video\VideoStyle;
use App\Models\Video\VideoReligion;
use App\Models\Video\VideoInterest;
use App\Models\Video\VideoSize;
use App\Models\User;

use App\Models\Video\VideoVirtualCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VideoTemplateController extends AppBaseController
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $userType = Auth::user()->user_type;

        $isAdminOrManager = RoleManager::isAdminOrSeoManager($userType);
        $isAdminOrSeoManagerOrDesignerManager = RoleManager::isAdminOrSeoManagerOrDesignerManager($userType);
        $isSeoExecutive = RoleManager::isSeoExecutive($userType);

        $sortingField = $request->get('sort_by', 'created_at');
        $sortingOrder = $request->get('sort_order', 'desc');
        if ($sortingField == '') {
            $sortingField = 'id';
        }
        $perPage = $request->get('per_page', 10);
        $query = $request->get('query', '');

        $itemsQuery = VideoTemplate::with('videoCat')
            ->where('is_deleted', 0);

        // seo_employee filter (Assign to)
        if ($request->filled('seo_employee')) {
            if ($request->seo_employee === 'assigned') {
                $itemsQuery->whereNotNull('seo_emp_id')->where('seo_emp_id', '!=', 0);
            } elseif ($request->seo_employee === 'unassigned') {
                $itemsQuery->where(function ($q) {
                    $q->whereNull('seo_emp_id')->orWhere('seo_emp_id', 0);
                });
            }
        }

        // seo_category_assigne filter (Category Name)
        if ($request->filled('seo_category_assigne')) {
            if ($request->seo_category_assigne === 'assigned') {
                $itemsQuery->whereNotNull('category_id')->where('category_id', '!=', 0);
            } elseif ($request->seo_category_assigne === 'unassigned') {
                $itemsQuery->where(function ($q) {
                    $q->whereNull('category_id')->orWhere('category_id', 0);
                });
            }
        }

        // premium_type filter
        if ($request->filled('premium_type')) {
            if ($request->premium_type === 'free') {
                $itemsQuery->where('is_premium', 0)->where('is_freemium', 0);
            } elseif ($request->premium_type === 'freemium') {
                $itemsQuery->where('is_freemium', 1);
            } elseif ($request->premium_type === 'premium') {
                $itemsQuery->where('is_premium', 1);
            }
        }

        // template_status filter
        if ($request->filled('template_status')) {
            if ($request->template_status === 'not-live') {
                $itemsQuery->where('status', 0);
            } elseif ($request->template_status === 'live') {
                $itemsQuery->where('status', 1);
            }
        }

        $this->applyNoIndexFilter($request, $itemsQuery);

        // Role-based filtering
        if ((int) $userType === UserRole::SEO_INTERN->id()) {
            // Interns see only templates assigned to them for SEO (seo_emp_id), not designer emp_id.
            $itemsQuery->where('seo_emp_id', $currentuserid);
        } elseif ($isSeoExecutive) {
            // Only templates explicitly assigned for SEO (seo_emp_id) — e.g. by SEO Manager —
            // to this user or to their team members. Unassigned templates are not listed.
            $teamMemberIds = User::where('team_leader_id', $currentuserid)
                ->where('status', 1)
                ->pluck('id')
                ->toArray();

            $allUserIds = array_merge([$currentuserid], $teamMemberIds);

            $itemsQuery->whereIn('seo_emp_id', $allUserIds);
        } elseif (!$isAdminOrManager) {
            $itemsQuery->where('emp_id', $currentuserid);
        }

        $items = $itemsQuery->where(function ($queryBuilder) use ($query) {
            if (!empty($query)) {
                $queryBuilder->where('id', 'like', "%$query%")
                    ->orWhere('relation_id', 'like', "%$query%")
                    ->orWhere('video_name', 'like', "%$query%")
                    ->orWhere('string_id', 'like', "%$query%")
                    ->orWhereHas('videoCat', function ($subQuery) use ($query) {
                        $subQuery->where('category_name', 'like', "%$query%");
                    });
            }
        })
            ->orderBy($sortingField, $sortingOrder)
            ->paginate($perPage);

        // Grouped VideoCategories (parent + children) for Category Name dropdown
        $parentCategories = VideoCategory::where('parent_category_id', 0)->get();
        $groupedVideoCategories = [];

        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }
        // category_id -> seo_emp_id for Assign to dropdown refresh
        $categoryIds = collect($items->items())->pluck('category_id')->unique()->filter();
        $categoriesForSEO = VideoCategory::whereIn('id', $categoryIds)->get()->keyBy('id');
        $categorySeoEmpIds = [];
        foreach ($categoriesForSEO as $category) {
            $id = $category->seo_emp_id ?? null;
            if (!empty($id)) {
                $categorySeoEmpIds[$category->id] = $id;
            }
        }

        $seoUsers = collect();
        if (RoleManager::isSeoExecutive($userType)) {
            // Executive (or manager treated as SEO lead): assign to own team interns only
            $seoUsers = User::where('user_type', UserRole::SEO_INTERN->id())
                ->where('team_leader_id', $currentuserid)
                ->where('status', 1)
                ->get()
                ->keyBy('id');
        } elseif (RoleManager::isAdminOrSeoManagerOrDesignerManager($userType)) {
            if (RoleManager::isAdmin($userType)) {
                $seoUsers = User::whereIn('user_type', [
                    UserRole::SEO_EXECUTIVE->id(),
                    UserRole::SEO_INTERN->id(),
                ])
                    ->where('status', 1)
                    ->get()
                    ->keyBy('id');
            } else {
                // SEO Manager / Designer Manager: assign to SEO Executives only (not interns)
                $seoUsers = User::where('user_type', UserRole::SEO_EXECUTIVE->id())
                    ->where('status', 1)
                    ->get()
                    ->keyBy('id');
            }
        }

        $datas['item'] = $items;
        $datas['count_str'] = $items->total() > 0
            ? 'Showing ' . $items->firstItem() . ' to ' . $items->lastItem() . ' of ' . $items->total() . ' entries'
            : 'Showing 0 to 0 of 0 entries';

        // Apply same role-based filters to noIndexStats
        $noIndexStats = $this->noIndexListingStats(VideoTemplate::class, static function ($q) use ($userType, $currentuserid, $isSeoExecutive, $isAdminOrManager) {
            $q->where('is_deleted', 0);

            // Apply same role-based filtering as main query
            if ((int) $userType === UserRole::SEO_INTERN->id()) {
                $q->where('seo_emp_id', $currentuserid);
            } elseif ($isSeoExecutive) {
                $teamMemberIds = User::where('team_leader_id', $currentuserid)
                    ->where('status', 1)
                    ->pluck('id')
                    ->toArray();
                $allUserIds = array_merge([$currentuserid], $teamMemberIds);
                $q->whereIn('seo_emp_id', $allUserIds);
            } elseif (!$isAdminOrManager) {
                $q->where('emp_id', $currentuserid);
            }
        });

        return view('videos/show_item', [
            'itemArray' => $datas,
            'groupedVideoCategories' => $groupedVideoCategories,
            'categorySeoEmpIds' => $categorySeoEmpIds,
            'seoUsers' => $seoUsers,
            'noIndexStats' => $noIndexStats,
        ]);
    }


    public function assignSeo(Request $request)
    {
        if (!RoleManager::isSeoExecutive(Auth::user()->user_type) && !RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type)) {
            return response()->json([
                'status' => false,
                'error' => 'Not permission to assign',
            ]);
        }
        $res = VideoTemplate::where('id', $request->id)->where('is_deleted', 0)->first();
        if (!$res) {
            return response()->json(['status' => false, 'error' => 'Item not found.']);
        }

        $assigner = Auth::user();
        $targetId = (int) ($request->seo_emp_id ?? 0);

        if ($targetId !== 0) {
            $targetUser = User::where('id', $targetId)->where('status', 1)->first();
            if (!$targetUser) {
                return response()->json(['status' => false, 'error' => 'Invalid assignee.']);
            }
            if (RoleManager::isAdmin($assigner->user_type)) {
                if (
                    !in_array((int) $targetUser->user_type, [
                        UserRole::SEO_EXECUTIVE->id(),
                        UserRole::SEO_INTERN->id(),
                    ], true)
                ) {
                    return response()->json(['status' => false, 'error' => 'Invalid assignee.']);
                }
            } elseif (RoleManager::isSeoExecutive($assigner->user_type)) {
                if (
                    (int) $targetUser->user_type !== UserRole::SEO_INTERN->id()
                    || (int) $targetUser->team_leader_id !== (int) $assigner->id
                ) {
                    return response()->json([
                        'status' => false,
                        'error' => 'You can only assign templates to interns on your team.',
                    ]);
                }
            } else {
                if ((int) $targetUser->user_type !== UserRole::SEO_EXECUTIVE->id()) {
                    return response()->json([
                        'status' => false,
                        'error' => 'You can only assign templates to SEO Executives.',
                    ]);
                }
            }
        }

        $res->seo_emp_id = $targetId ?: 0;
        $res->seo_assigner_id = $assigner->id;
        $res->save();
        return response()->json(['status' => true, 'success' => 'done']);
    }

    public function assignCategory(Request $request)
    {
        if (!RoleManager::isAdminOrSeoManagerOrDesignerManager(Auth::user()->user_type)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $request->validate([
            'video_id' => 'required|integer',
            'category_id' => 'required|integer',
        ]);
        $item = VideoTemplate::where('id', $request->video_id)->where('is_deleted', 0)->first();
        $category = VideoCategory::find($request->category_id);
        if (!$item || !$category) {
            return response()->json(['status' => false, 'message' => 'Invalid data.']);
        }
        if ($category->parent_category_id == 0) {
            return response()->json([
                'error' => 'You cannot assign template to parent Category'
            ]);
        }
        $item->category_id = $request->category_id;
        $item->save();
        $parentCategory = $category->parent_category_id ? VideoCategory::find($category->parent_category_id) : null;
        $seoEmpId = $category->seo_emp_id ?? null;
        $users = collect();
        if (!empty($seoEmpId)) {
            $users = User::where('id', $seoEmpId)->select('id', 'name')->get();
        }
        return response()->json([
            'status' => true,
            'message' => 'Category assigned successfully.',
            'category_name' => $category->category_name,
            'parent_name' => $parentCategory->category_name ?? '',
            'seo_users' => $users,
        ]);
    }


    public function create(Request $request)
    {
        //        $datas['cat'] = VideoCategory::all();
        $datas['templateType'] = VideoType::all();
        $datas['appId'] = $request->input('passingAppId');
        $datas['searchTagArray'] = VideoSearchTag::all();
        $parentCategories = VideoCategory::where('parent_category_id', 0)->get();
        $groupedVideoCategories = [];

        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }
        $datas['groupedVideoCategories'] = $groupedVideoCategories;
        return view('videos/create_item')->with('datas', $datas);
    }

    public function store(Request $request)
    {
        $currentuserid = Auth::user()->id;

        // Validate relation_id is numeric and file uploads (WebP thumb, MP4 video — team spec)
        $this->validate($request, [
            'relation_id' => 'required|integer|min:0',
            'video_thumb' => 'required|file|mimes:webp|max:100',
            'video_file' => 'required|file|mimes:mp4|max:10240',
            'zip_file' => 'required|file|mimes:zip|max:15000',
        ], [
            'video_thumb.mimes' => 'Video Thumb must be in WebP format only!',
            'video_thumb.max' => 'Video Thumb size must be less than 100 KB!',
            'video_file.mimes' => 'Video File must be in MP4 format only!',
            'video_file.max' => 'Video File size must be less than 10 MB!',
        ]);

        $res = new VideoTemplate;

        $video_thumb = $request->file('video_thumb');
        if ($video_thumb != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($video_thumb, 'uploadedFiles/vThumb_file', $new_name);
            $res->video_thumb = 'uploadedFiles/vThumb_file/' . $new_name;
        }

        $video_file = $request->file('video_file');
        if ($video_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_file->getClientOriginalExtension();
            StorageUtils::storeAs($video_file, 'uploadedFiles/video_file', $new_name);
            $res->video_url = 'uploadedFiles/video_file/' . $new_name;
        }

        $zip_file = $request->file('zip_file');
        if ($zip_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $zip_file->getClientOriginalExtension();
            StorageUtils::storeAs($zip_file, 'uploadedFiles/vZip_file', $new_name);
            $res->video_zip_url = 'uploadedFiles/vZip_file/' . $new_name;
            $res->folder_name = $new_name;
        }

        $res->emp_id = $currentuserid;
        $res->relation_id = (int) $request->input('relation_id'); // Cast to integer
        $res->string_id = $this->generateId();
        $res->category_id = $request->input('category_id');

        $res->video_name = $request->input('video_name');
        $res->pages = $request->input('pages');
        $res->width = $request->input('width');
        $res->height = $request->input('height');
        $res->watermark_height = $request->input('watermark_height');
        $res->template_type = $request->input('template_type');
        $res->do_front_lottie = $request->input('do_front_lottie');

        $img_array = array();

        if ($request->has('img_key')) {
            $count = count($request->img_key);
            $img_key = $request->img_key;
            $isShape = $request->img_shape;


            for ($i = 0; $i < $count; $i++) {
                $img_array[] = array(
                    'key' => $img_key[$i],
                    'isShape' => $isShape[$i],
                );
            }
        }

        $res->editable_image = json_encode($img_array);


        $text_array = array();

        if ($request->has('editable_text_id')) {
            $count = count($request->editable_text_id);
            $keys = $request->key;
            $titles = $request->title;
            $editable_text_id = $request->editable_text_id;
            $font_family = $request->font_family;


            for ($i = 0; $i < $count; $i++) {
                $text_array[] = array(
                    'key' => $keys[$i],
                    'title' => $titles[$i],
                    'value' => $editable_text_id[$i],
                    'font_family' => $font_family[$i],
                );
            }
        }

        $res->editable_text = json_encode($text_array);
        if ($request->input('keywords') != null) {
            $res->keyword = $request->input('keywords');
        }

        $encrypted = $request->input('encrypted');
        $res->encrypted = $encrypted;
        if ($encrypted == '1') {
            $res->encryption_key = $request->input('encryption_key');
        } else {
            $res->encryption_key = null;
        }

        $res->change_music = $request->input('change_music');
        $res->is_premium = $request->input('is_premium');
        $res->status = $request->input('status');
        $this->applyVideoSitemapFieldsFromRequest($request, $res, true);
        $res->save();

        return response()->json([
            'success' => 'Data Added successfully.',
            'template' => [
                'id' => $res->id,
                'string_id' => $res->string_id,
                'video_name' => $res->video_name,
                'relation_id' => $res->relation_id,
                'video_thumb' => $res->video_thumb,
                'category_id' => $res->category_id,
            ]
        ]);
    }

    public function edit($id)
    {
        $vData = VideoTemplate::where('id', $id)->where('is_deleted', 0)->first();
        if (!$vData) {
            abort(404);
        }

        $user = auth()->user();
        $userType = $user->user_type;

        if (RoleManager::isAdminOrSeoManager($userType)) {
            // Admin and SEO Manager can open design edit for any template
        } elseif (RoleManager::onlyDesignerAccess($userType)) {
            if ($vData->emp_id != $user->id && !RoleManager::isAdminOrDesignerManager($userType)) {
                abort(403, 'Access denied. You can only edit your own templates.');
            }
        } else {
            abort(403, 'You have no permission to edit templates.');
        }

        $datas['cat'] = VideoCategory::all();
        $datas['templateType'] = VideoType::all();
        $datas['searchTagArray'] = VideoSearchTag::all();
        $datas['item'] = VideoTemplate::find($id);
        $datas['editable_image'] = json_decode($datas['item']->editable_image ?? '[]', true) ?? [];
        $datas['editable_text'] = json_decode($datas['item']->editable_text ?? '[]', true) ?? [];
        return view('videos/edit_item')->with('dataArray', $datas);
    }

    public function update(Request $request)
    {
        $res = VideoTemplate::where('id', $request->id)->where('is_deleted', 0)->first();
        if (!$res) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        $user = auth()->user();
        $userType = $user->user_type;

        if (RoleManager::isAdminOrSeoManager($userType)) {
            // Admin and SEO Manager can update any template
        } elseif (RoleManager::onlyDesignerAccess($userType)) {
            if ($res->emp_id != $user->id && !RoleManager::isAdminOrDesignerManager($userType)) {
                return response()->json(['status' => false, 'error' => 'Access denied. You can only edit your own templates.']);
            }
        } else {
            return response()->json(['status' => false, 'error' => 'You have no permission']);
        }

        $video_thumb = $request->file('video_thumb');
        if ($video_thumb != null) {
            $extension = strtolower($video_thumb->getClientOriginalExtension());
            if ($extension !== 'webp') {
                return response()->json(['status' => false, 'error' => 'Video Thumb must be in WebP format only!'], 422);
            }

            $maxThumbSize = 100 * 1024;
            if ($video_thumb->getSize() > $maxThumbSize) {
                $currentSize = round($video_thumb->getSize() / 1024, 2);

                return response()->json(['status' => false, 'error' => "Video Thumb size must be less than 100 KB! Current size: {$currentSize} KB"], 422);
            }

            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_thumb->getClientOriginalExtension();
            StorageUtils::storeAs($video_thumb, 'uploadedFiles/vThumb_file', $new_name);
            $res->video_thumb = 'uploadedFiles/vThumb_file/' . $new_name;
        }

        $video_file = $request->file('video_file');
        if ($video_file != null) {
            $extension = strtolower($video_file->getClientOriginalExtension());
            if ($extension !== 'mp4') {
                return response()->json(['status' => false, 'error' => 'Video File must be in MP4 format only!'], 422);
            }

            $maxVideoSize = 10 * 1024 * 1024;
            if ($video_file->getSize() > $maxVideoSize) {
                $currentSize = round($video_file->getSize() / (1024 * 1024), 2);

                return response()->json(['status' => false, 'error' => "Video File size must be less than 10 MB! Current size: {$currentSize} MB"], 422);
            }

            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_file->getClientOriginalExtension();
            StorageUtils::storeAs($video_file, 'uploadedFiles/video_file', $new_name);
            $res->video_url = 'uploadedFiles/video_file/' . $new_name;
        }

        $zip_file = $request->file('zip_file');
        if ($zip_file != null) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $zip_file->getClientOriginalExtension();
            StorageUtils::storeAs($zip_file, 'uploadedFiles/vZip_file', $new_name);
            $res->video_zip_url = 'uploadedFiles/vZip_file/' . $new_name;
            $res->folder_name = $new_name;
        }

        // Validate and cast relation_id to integer
        if ($request->has('relation_id')) {
            $relationId = $request->input('relation_id');
            if (!is_numeric($relationId)) {
                return response()->json(['status' => false, 'error' => 'Relation ID must be a number']);
            }
            $res->relation_id = (int) $relationId;
        }

        // Don't update these fields - they're managed in SEO edit
        // $res->category_id = $request->input('category_id');
        // $res->video_name = $request->input('video_name');
        if ($request->has('pages')) {
            $res->pages = $request->input('pages');
        }
        if ($request->has('width')) {
            $res->width = $request->input('width');
        }
        if ($request->has('height')) {
            $res->height = $request->input('height');
        }
        if ($request->has('watermark_height')) {
            $res->watermark_height = $request->input('watermark_height');
        }
        if ($request->has('template_type')) {
            $res->template_type = $request->input('template_type');
        }
        if ($request->has('do_front_lottie')) {
            $res->do_front_lottie = $request->input('do_front_lottie');
        }

        $img_array = array();

        if ($request->has('img_key')) {
            $count = count($request->img_key);
            $img_key = $request->img_key;
            $isShape = $request->img_shape;


            for ($i = 0; $i < $count; $i++) {
                $img_array[] = array(
                    'key' => $img_key[$i],
                    'isShape' => $isShape[$i],
                );
            }
        }

        $res->editable_image = json_encode($img_array);


        $text_array = array();

        if ($request->has('editable_text_id')) {
            $count = count($request->editable_text_id);
            $keys = $request->key;
            $titles = $request->title;
            $editable_text_id = $request->editable_text_id;
            $font_family = $request->font_family;


            for ($i = 0; $i < $count; $i++) {
                $text_array[] = array(
                    'key' => $keys[$i],
                    'title' => $titles[$i],
                    'value' => $editable_text_id[$i],
                    'font_family' => $font_family[$i],
                );
            }
        }

        $res->editable_text = json_encode($text_array);

        // Don't update these fields - they're managed in SEO edit
        // if ($request->input('keywords') != null) {
        //     $res->keyword = $request->input('keywords');
        // }

        if ($request->has('encrypted')) {
            $encrypted = $request->input('encrypted');
            $res->encrypted = $encrypted;
            if ($encrypted == '1') {
                $res->encryption_key = $request->input('encryption_key');
            } else {
                $res->encryption_key = null;
            }
        }

        if ($request->has('change_music')) {
            $res->change_music = $request->input('change_music');
        }

        // Don't update these fields - they're managed in SEO edit
        // $res->is_premium = $request->input('is_premium');
        // $res->status = $request->input('status');
        $res->save();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Video updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $currentuserid = Auth::user()->id;

        $idAdmin = RoleManager::isAdminOrDesignerManager(Auth::user()->user_type);

        if ($idAdmin) {
            $res = VideoTemplate::find($id);
            $res->is_deleted = 1;
            $res->save();
        }

        // try {
        //    unlink(storage_path("app/public/".$video_thumb));
        // } catch (\Exception $e) {
        // }

        // try {
        //     unlink(storage_path("app/public/".$video_url));
        // } catch (\Exception $e) {
        // }

        // try {
        //     unlink(storage_path("app/public/".$video_zip_url));
        // } catch (\Exception $e) {
        // }

        // VideoTemplate::destroy(array('id', $id));
        return redirect('show_v_item');
    }

    function sortArray($a, $b)
    {
        return strnatcasecmp($a['imageName'], $b['imageName']);
    }


    public static function generateId($length = 8)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $string_id = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (VideoTemplate::where('string_id', $string_id)->exists());
        return $string_id;
    }

    /**
     * Same rules as show(): SEO executives may work only on templates whose seo_emp_id
     * is assigned to them or a team member (not unassigned).
     */
    private function seoExecutiveCanEditVideoTemplateSeo(VideoTemplate $template, int $currentUserId): bool
    {
        $seoEmpId = $template->seo_emp_id;
        if ($seoEmpId === null || (int) $seoEmpId === 0) {
            return false;
        }
        $teamMemberIds = User::where('team_leader_id', $currentUserId)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        $allowedIds = array_merge([$currentUserId], $teamMemberIds);

        return in_array((int) $seoEmpId, $allowedIds, true);
    }

    public function editSeo($id)
    {
        $currentuserid = Auth::user()->id;
        $userType = Auth::user()->user_type;

        $vData = VideoTemplate::where('id', $id)->where('is_deleted', 0)->first();
        if (!$vData) {
            abort(404);
        }

        if (RoleManager::isSeoExecutive($userType)) {
            if (!$this->seoExecutiveCanEditVideoTemplateSeo($vData, $currentuserid)) {
                abort(403, 'Access denied. You can only edit templates assigned to you or your team for SEO.');
            }
        } elseif ((int) $userType === UserRole::SEO_INTERN->id()) {
            if ((int) ($vData->seo_emp_id ?? 0) !== (int) $currentuserid) {
                abort(403, 'Access denied. You can only edit templates assigned to you for SEO.');
            }
        } elseif (!RoleManager::isAdminOrSeoManager($userType)) {
            // Other users can only edit their own items
            if ($vData->emp_id != $currentuserid) {
                abort(403, 'Access denied. You can only edit your own items.');
            }
        }
        // Admin and SEO Manager can edit all items

        $datas['cat'] = VideoCategory::all();
        $allCategoriesCollection = VideoCategory::getAllCategoriesWithSubcategories();
        $datas['allCategories'] = $allCategoriesCollection->map(function ($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'subcategories' => $category->subcategories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'category_name' => $sub->category_name,
                        'subcategories' => $sub->subcategories ?? []
                    ];
                })->toArray()
            ];
        })->toArray();

        $datas['searchTagArray'] = VideoSearchTag::all();
        $datas['item'] = VideoTemplate::find($id);

        // Get selected category for display
        $selectedCategory = VideoCategory::find($datas['item']->category_id);
        $datas['select_category'] = $selectedCategory ? $selectedCategory->toArray() : [];

        // Get filter data
        $datas['langArray'] = VideoLanguage::all();
        $datas['themeArray'] = VideoTheme::all();
        $datas['styleArray'] = VideoStyle::all();
        $datas['sizes'] = $this->resolveVideoSizesForCategory((int) ($datas['item']->category_id ?? 0));
        $datas['religions'] = VideoReligion::all();
        $datas['interestArray'] = VideoInterest::all();
        $datas['keywordArray'] = VideoSearchTag::all();
        $datas['virtualCategory'] = VideoVirtualCategory::whereStatus(1)->get();
        $parentCategories = VideoCategory::where('parent_category_id', 0)->get();
        $groupedVideoCategories = [];

        foreach ($parentCategories as $parent) {
            $children = VideoCategory::where('parent_category_id', $parent->id)->get();
            $groupedVideoCategories[$parent->id] = [
                'parent' => $parent,
                'children' => $children
            ];
        }
        $datas['groupedVideoCategories'] = $groupedVideoCategories;

        return view('videos/edit_seo_item')->with('dataArray', $datas);
    }

    public function updateSeo(Request $request)
    {
        try {
            $currentuserid = Auth::user()->id;
            $userType = Auth::user()->user_type;

            $res = VideoTemplate::whereId($request->id)->where('is_deleted', 0)->first();
            if (!$res) {
                return response()->json([
                    'error' => 'Video template not found.'
                ], 404);
            }

            $slugError = VideoSlugHistory::checkSlugValidation($request->input('slug'), $request->id);
            if ($slugError) {
                return response()->json([
                    'error' => $slugError,
                ]);
            }

            if (RoleManager::isSeoExecutive($userType)) {
                if (!$this->seoExecutiveCanEditVideoTemplateSeo($res, $currentuserid)) {
                    return response()->json([
                        'error' => 'Access denied. You can only edit templates assigned to you or your team for SEO.'
                    ]);
                }
            } elseif ((int) $userType === UserRole::SEO_INTERN->id()) {
                if ((int) ($res->seo_emp_id ?? 0) !== (int) $currentuserid) {
                    return response()->json([
                        'error' => 'Access denied. You can only edit templates assigned to you for SEO.'
                    ]);
                }
            } elseif (!RoleManager::isAdminOrSeoManager($userType)) {
                // Other users can only edit their own items
                if ($res->emp_id != $currentuserid) {
                    return response()->json([
                        'error' => 'Access denied. You can only edit your own items.'
                    ]);
                }
            }

            $seoExecOrInternLocksCategoryNoindexCanonical = in_array((int) $userType, [
                UserRole::SEO_EXECUTIVE->id(),
                UserRole::SEO_INTERN->id(),
            ], true);

            $categoryIdInput = $request->input('category_id');
            if ($categoryIdInput === null || $categoryIdInput === '' || $categoryIdInput === '0') {
                return response()->json([
                    'error' => 'Please Select Category',
                ]);
            }

            // Update basic fields
            $res->video_name = $request->input('video_name');
            if (!$seoExecOrInternLocksCategoryNoindexCanonical) {
                $res->category_id = $categoryIdInput;
            }
            // Virtual category: only change when the field is submitted (disabled controls are omitted from FormData)
            if ($request->has('virtual_category_id')) {
                $virtualCategoryId = $request->input('virtual_category_id');
                $res->virtual_category_id = ($virtualCategoryId !== null && $virtualCategoryId !== '')
                    ? (int) $virtualCategoryId
                    : 0;
            }
            if ($res->category_id) {
                $cat = VideoCategory::whereId($res->category_id)->first();
                if ($cat->parent_category_id == 0) {
                    return response()->json([
                        'error' => 'You cannot assign template to parent Category'
                    ]);
                }
            }

            if ($request->input('keywords') != null) {
                if ($request->input('keywords') != null) {
                    $res->keyword = json_encode(
                        array_map('intval', $request->input('keywords', []))
                    );
                }
            }
            $res->slug = $request->input('slug');

            // Only update is_premium if provided (field may be disabled for some roles)
            if ($request->has('is_premium')) {
                $res->is_premium = $request->input('is_premium');
            }

            $res->status = $request->input('status');
            if (!$seoExecOrInternLocksCategoryNoindexCanonical) {
                $res->no_index = $request->input('no_index', 1); // Default 1 if not provided
            }

            // Update SEO fields

            // Only update h2_tag if provided (field may be disabled for some roles)
            if ($request->has('h2_tag')) {
                $res->h2_tag = $request->input('h2_tag');
            }

            if (!$seoExecOrInternLocksCategoryNoindexCanonical) {
                $res->canonical_link = $request->input('canonical_link');
            }

            // Only update meta_title if provided (field may be disabled for some roles)
            if ($request->has('meta_title')) {
                $res->meta_title = $request->input('meta_title');
            }

            // Only update description if provided (field may be disabled for some roles)
            if ($request->has('description')) {
                $res->description = $request->input('description');
            }

            // Only update meta_description if provided (field may be disabled for some roles)
            if ($request->has('meta_description')) {
                $res->meta_description = $request->input('meta_description');
            }

            // Update filter fields
            if ($request->has('lang_id')) {
                $res->lang_id = json_encode($request->input('lang_id'));
            } else {
                $res->lang_id = json_encode([], true);
            }

            if ($request->has('theme_id')) {
                $res->theme_id = json_encode($request->input('theme_id'));
            } else {
                $res->theme_id = json_encode([], true);
            }

            if ($request->has('styles')) {
                $res->style_id = json_encode($request->input('styles'));
            } else {
                $res->style_id = json_encode([], true);
            }

            $res->orientation = $request->input('orientation', $res->orientation);
            $res->template_size = $request->input('template_size', $res->template_size);

            if ($request->has('religion_id')) {
                $res->religion_id = json_encode($request->input('religion_id'));
            } else {
                $res->religion_id = json_encode([], true);
            }

            if ($request->has('interest_id')) {
                $res->interest_id = json_encode($request->input('interest_id'));
            } else {
                $res->interest_id = json_encode([], true);
            }

            // Only update is_freemium if provided (field may be disabled for some roles)
            if ($request->has('is_freemium')) {
                $res->is_freemium = $request->input('is_freemium');
            }

            // Handle date range
            if ($request->input('date_range') && $request->input('date_range') != '') {
                $dateRange = explode(' - ', $request->input('date_range'));
                if (count($dateRange) == 2) {
                    // Convert from "MM/DD/YYYY" to "YYYY-MM-DD" for database storage
                    try {
                        $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dateRange[0]))->format('Y-m-d');
                        $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dateRange[1]))->format('Y-m-d');
                        $res->start_date = $startDate;
                        $res->end_date = $endDate;
                    } catch (\Exception $e) {
                        // If parsing fails, try to use the dates as-is
                        $res->start_date = trim($dateRange[0]);
                        $res->end_date = trim($dateRange[1]);
                    }
                }
            }

            // Only update color_ids if provided (field may be disabled for some roles)
            if ($request->has('color_ids')) {
                $res->color_ids = $request->input('color_ids');
            }

            $this->applyVideoSitemapFieldsFromRequest($request, $res, false);
            $res->save();

            return response()->json([
                'success' => true,
                'message' => 'SEO data updated successfully.'
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Video Template SEO Update Error: ' . $e->getMessage(), [
                'template_id' => $request->id ?? null,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return user-friendly error response
            return response()->json([
                'error' => 'An error occurred while updating SEO data. Please try again or contact support if the issue persists.',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function noindex_update(Request $request, $id)
    {
        // Only Admin or SEO Manager can update No Index status
        if (!\App\Http\Controllers\Utils\RoleManager::isAdminOrSeoManager(auth()->user()->user_type)) {
            return response()->json([
                'error' => "Ask admin or manager for changes"
            ]);
        }

        try {
            $res = \App\Models\Video\VideoTemplate::where('id', $id)->where('is_deleted', 0)->firstOrFail();

            // Toggle No Index status
            $res->no_index = $res->no_index == '1' ? '0' : '1';

            if ($res->save()) {
                return response()->json([
                    'success' => 'done'
                ]);
            } else {
                return response()->json(['error' => 'Failed to save'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data not found'], 404);
        }
    }

    /**
     * Sizes in crafty_video_db, scoped by video main_categories root (parity with item Size + NewCategory).
     */
    protected function resolveVideoSizesForCategory(int $categoryId)
    {
        if ($categoryId === 0) {
            return VideoSize::where('status', 1)->orderBy('id')->get();
        }
        $category = VideoCategory::find($categoryId);
        if (!$category) {
            return VideoSize::where('status', 1)->orderBy('id')->get();
        }
        $rootParentId = $category->getRootParentId();
        $rootParentId = $rootParentId ?: $categoryId;
        $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);

        return VideoSize::whereJsonContains('category_id', $catId)->where('status', 1)->orderBy('id')->get();
    }

    public function loadVideoSizeAndTheme(Request $request)
    {
        try {
            $catId = $request->cateId;
            if (!$catId || $catId == '0') {
                return response()->json([
                    'status' => true,
                    'sizes' => [],
                    'themes' => []
                ]);
            }

            $category = VideoCategory::find($catId);
            if (!$category) {
                return response()->json([
                    'status' => true,
                    'sizes' => [],
                    'themes' => []
                ]);
            }

            $rootParentId = $category->getRootParentId();
            $rootParentId = $rootParentId ?: (int) $catId;
            $rootCatId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);
            $currentCatId = is_string($catId) ? $catId : json_encode($catId);

            // Sizes: Load only from root parent category (existing behavior)
            $sizes = VideoSize::whereJsonContains('category_id', $rootCatId)->where('status', 1)->orderBy('id')->get();

            // Themes: Load from both root parent category AND current selected category
            $themes = VideoTheme::where('status', 1)
                ->where(function($query) use ($rootCatId, $currentCatId) {
                    $query->whereJsonContains('category_id', $rootCatId)
                          ->orWhereJsonContains('category_id', $currentCatId);
                })
                ->orderBy('id')
                ->get();

            return response()->json([
                'status' => true,
                'sizes' => $sizes,
                'themes' => $themes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

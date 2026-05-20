<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\JSONUtils;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\UserData;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\DesignerDraft;
use App\Models\Design;
use Carbon\Carbon;

class DesignerDraftController extends ApiController
{
    private array $allowedExt = ['jpeg', 'jpg', 'webp'];

    private array $defaultFrame = [
        'name' => 'Untitled design',
        'frame' => [
            'width' => 1080,
            'height' => 1080,
        ],
        'pages' => [
            [
                'pageId' => "Str::random(20)",
                'name' => "Page 1",
                'objects' => [
                    [
                        'id' => "Str::random(20)",
                        'angle' => 0,
                        'stroke' => null,
                        'strokeWidth' => 0,
                        'left' => 0,
                        'top' => 0,
                        'width' => 1080,
                        'height' => 1080,
                        'opacity' => 1,
                        'originX' => "left",
                        'originY' => "top",
                        'scaleX' => 1,
                        'scaleY' => 1,
                        'type' => "Background",
                        'flipX' => false,
                        'flipY' => false,
                        'skewX' => 0,
                        'skewY' => 0,
                        'visible' => true,
                        'fill' => "#ffffff",
                        'src' => "",
                        'metadata' => [],
                    ]
                ]
            ]
        ]
    ];

    function getCreators(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc !== 1) return $this->failed(msg: "Unauthorized");

        $users = UserData::select(['name', 'email', 'creator'])->where("creator", 1)->get();

        $response['datas'] = $users;

        return $this->successed(datas: $response);
    }

    function findCreator(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        if ($request->apply) return $this->setCreator($request);

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc !== 1) return $this->failed(msg: "Unauthorized");

        $creator = UserData::select(['name', 'email', 'creator'])->where("email", $request->email)->first();
        if (!$creator) return $this->failed(msg: "Not found");

        $response['data'] = $creator;

        return $this->successed(datas: $response);
    }

    function setCreator(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc !== 1) return $this->failed(msg: "Unauthorized");

        $creator = UserData::where("email", $request->email)->first();
        if (!$creator) return $this->failed(msg: "Not found");

        $creator->creator = $creator->creator == 1 ? 0 : 1;
        $creator->save();

        $response['data'] = $creator;

        return $this->successed(datas: $response);
    }

    function create(Request $request): array|string
    {

        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc) return $this->failed(msg: "Head of creator can't create designs");

        $jsonString = $request->get('i');
        if ($jsonString == null) return $this->failed(msg: "Parameters missing");

        $defaultFrame = $this->defaultFrame;
        $uid = $this->uid;

        try {
            $json = json_decode(base64_decode($jsonString));
            $w = isset($json->w) ? (int)$json->w : null;
            $h = isset($json->h) ? (int)$json->h : null;

            $minSize = 40;
            $maxSize = 4000;

            if ($w && $h) {
                if (
                    $h < $minSize ||
                    $h > $maxSize ||
                    $w < $minSize ||
                    $w > $maxSize
                ) {
                    return $this->failed(msg: "Width and height must be between " . $minSize . " and " . $maxSize . ".");
                }

                $string_id = HelperController::generateID('', 30);
                while (DesignerDraft::where('string_id', $string_id)->exists()) {
                    $string_id = HelperController::generateID('', 30);
                }

                $templateId = HelperController::generateID('', 8);
                while (Design::where('string_id', $templateId)->exists()) {
                    $templateId = HelperController::generateID('', 8);
                }

                $defaultFrame['frame']['width'] = $w;
                $defaultFrame['frame']['height'] = $h;
                $defaultFrame['pages'][0]['pageId'] = HelperController::generateID('', 20);
                $defaultFrame['pages'][0]['objects'][0]['id'] = HelperController::generateID('', 20);
                $defaultFrame['pages'][0]['objects'][0]['width'] = $w;
                $defaultFrame['pages'][0]['objects'][0]['height'] = $h;

                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp;
                $jsonFilePath = 'uploadedFiles/fab_designs/' . $new_name . '.json';
                StorageUtils::put($jsonFilePath, json_encode($defaultFrame));

                $image = Image::canvas(500, ($h / $w) * 500, "#ffffff");
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp;
                $filePath = 'uploadedFiles/thumb_file/' . $new_name . '.jpg';
                StorageUtils::put($filePath, $image->encode('jpg'));

                $imageArray = [];
                $imageArray[] = $filePath;

                $tempRatio = $w / $h;
                $tempRatio = round($tempRatio, 2);

                $res = new DesignerDraft();
                $res->string_id = $string_id;
                $res->template_id = $templateId;
                $res->user_id = $uid;
                $res->name = $defaultFrame['name'];
                $res->ratio = $tempRatio;
                $res->width = $w;
                $res->height = $h;
                $res->thumbs = json_encode($imageArray);
                $res->designs = $jsonFilePath;
                $res->save();
                return $this->successed(datas: ['ul' => $string_id]);
            } else {
                return $this->failed();
            }

        } catch (\Exception $e) {
            return $this->failed();
        }
    }

    function getAll(Request $request): array|string
    {
        return $this->getAllV2($request);
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

//        if ($this->isTester()) $this->uid = 'kk4Qk6xR8whuK4xNP5rYM4cy8ad2';

        $limit = HelperController::getPaginationLimit(size: 50);
        $page = $request->get("page", 1);
        $type = $request->get("type", 0);

        $user = UserData::where("uid", $this->uid)->first();

        $query = DesignerDraft::select(['user_id', 'string_id', 'template_id', 'name', 'ratio', 'width', 'height', 'thumbs', 'msg'])->orderBy('id', 'DESC');

        $datas = [];
        $singleData = null;
        if ($user->hoc) {
            if ($page == 1) {
                $notApproved = (clone $query)->where('is_live', 0)->paginate($limit, ['*'], 'page', $page);
                $notApprovedUserIds = $notApproved->getCollection()->pluck('user_id')->unique();
                $notApprovedUser = UserData::whereIn('uid', $notApprovedUserIds)->get()->keyBy('uid');

                foreach ($notApproved as $data) {
                    $user_ = $notApprovedUser->get($data->user_id);
                    $data->user_email = $user_ ? $user_->email : null;
                    $data->user_name = $user_ ? $user_->name : null;
                    unset($data->user_id);
                }

                $approved = (clone $query)->where('is_live', 1)->paginate($limit, ['*'], 'page', $page);
                $approvedUserIds = $approved->getCollection()->pluck('user_id')->unique();
                $approvedUser = UserData::whereIn('uid', $approvedUserIds)->get()->keyBy('uid');

                $approvedTemplateIds = $approved->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $approvedTemplateIds)->get()->keyBy('string_id');

                foreach ($approved->items() as $data) {
                    $user_ = $approvedUser->get($data->user_id);
                    $data->user_email = $user_ ? $user_->email : null;
                    $data->user_name = $user_ ? $user_->name : null;
                    unset($data->user_id);

                    $temp = $approvedTemps->get($data->template_id);
                    $id = $temp ? $temp->id : '--';
                    $data->name = "($id) $data->name";
                    unset($data->template_id);
                }

                $rejected = collect();

                $datas[] = [
                    "title" => "Not Approved",
                    "datas" => $this->processThumbs($notApproved->items()),
                    "length" => $notApproved->total(),
                    "type" => 0,
                    "pageNo" => $page,
                    "isLastPage" => $notApproved->currentPage() >= $notApproved->lastPage()
                ];

                $datas[] = [
                    "title" => "Approved",
                    "datas" => $this->processThumbs($approved->items()),
                    "length" => $approved->total(),
                    "type" => 1,
                    "pageNo" => $page,
                    "isLastPage" => $approved->currentPage() >= $approved->lastPage()
                ];
            } else {
                $list = (clone $query)->where('is_live', $type)->paginate($limit, ['*'], 'page', $page);
                $userIds = $list->getCollection()->pluck('user_id')->unique();
                $users = UserData::whereIn('uid', $userIds)->get()->keyBy('uid');

                $templateIds = $list->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $templateIds)->get()->keyBy('string_id');

                foreach ($list->items() as $data) {
                    $user_ = $users->get($data->user_id);
                    $data->user_email = $user_ ? $user_->email : null;
                    $data->user_name = $user_ ? $user_->name : null;
                    unset($data->user_id);

                    $temp = $approvedTemps->get($data->template_id);
                    if ($temp) {
                        $data->name = "($temp->id) $data->name";
                        unset($data->template_id);
                    }
                }

                $singleData = [
                    "title" => "",
                    "datas" => $this->processThumbs($list->items()),
                    "length" => $list->total(),
                    "type" => $type,
                    "pageNo" => $page,
                    "isLastPage" => $list->currentPage() >= $list->lastPage()
                ];
            }
        } else {
            if ($page == 1) {
                $notApproved = (clone $query)->where('user_id', $this->uid)->where('is_live', -1)->paginate($limit, ['*'], 'page', $page);
                $rejected = (clone $query)->where('user_id', $this->uid)->where('is_live', -2)->paginate($limit, ['*'], 'page', $page);
                $approved = (clone $query)->where('user_id', $this->uid)->where('is_live', 1)->paginate($limit, ['*'], 'page', $page);

                $approvedTemplateIds = $approved->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $approvedTemplateIds)->get()->keyBy('string_id');

                foreach ($approved->items() as $data) {
                    $temp = $approvedTemps->get($data->template_id);
                    $id = $temp ? $temp->id : '--';
                    $data->name = "($id) $data->name";
                    unset($data->template_id);
                }

                $datas[] = [
                    "title" => "Running",
                    "datas" => $this->processThumbs($notApproved->items()),
                    "length" => $notApproved->total(),
                    "type" => -1,
                    "pageNo" => $page,
                    "isLastPage" => $notApproved->currentPage() >= $notApproved->lastPage()
                ];

                $datas[] = [
                    "title" => "Rejected",
                    "datas" => $this->processThumbs($rejected->items()),
                    "length" => $rejected->total(),
                    "type" => -2,
                    "pageNo" => $page,
                    "isLastPage" => $rejected->currentPage() >= $rejected->lastPage()
                ];

                $datas[] = [
                    "title" => "Approved",
                    "datas" => $this->processThumbs($approved->items()),
                    "length" => $approved->total(),
                    "type" => 1,
                    "pageNo" => $page,
                    "isLastPage" => $approved->currentPage() >= $approved->lastPage()
                ];
            } else {
                $list = (clone $query)->where('user_id', $this->uid)->where('is_live', $type)->paginate($limit, ['*'], 'page', $page);

                $templateIds = $list->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $templateIds)->get()->keyBy('string_id');

                foreach ($list->items() as $data) {
                    $temp = $approvedTemps->get($data->template_id);
                    if ($temp) {
                        $data->name = "($temp->id) $data->name";
                        unset($data->template_id);
                    }
                }

                $singleData = [
                    "title" => "",
                    "datas" => $this->processThumbs($list->items()),
                    "length" => $list->total(),
                    "type" => $type,
                    "pageNo" => $page,
                    "isLastPage" => $list->currentPage() >= $list->lastPage()
                ];
            }
        }

        $response = [
            'list' => $datas,
            'data' => $singleData,
            'hoc' => $user->hoc,
        ];

        return $this->successed(datas: $response);
    }

    function getAllV2(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $limit = HelperController::getPaginationLimit(size: 50);
        $page = $request->get("page", 1);
        $type = $request->get("type", 0);

        $userData = UserData::where("uid", $this->uid)->first();

        $query = DesignerDraft::select(['user_id', 'string_id', 'template_id', 'name', 'ratio', 'width', 'height', 'thumbs', 'msg', 'video'])->orderBy('id', 'DESC');

        $datas = [];
        $singleData = null;
        if ($userData->hoc) {
            if ($page == 1) {
                $notApproved = (clone $query)->where('is_live', 0)->paginate($limit, ['*'], 'page', $page);
                $notApprovedUserIds = $notApproved->getCollection()->pluck('user_id')->unique();
                $notApprovedUser = UserData::whereIn('uid', $notApprovedUserIds)->get()->keyBy('uid');

                foreach ($notApproved as $data) {
                    $user = $notApprovedUser->get($data->user_id);
                    $data->user_email = $user ? $user->email : null;
                    $data->user_name = $user ? $user->name : null;
                    if ($data->video) {
                        $data->video = HelperController::$mediaUrl . $data->video;
                    }
                    unset($data->user_id);
                }

                $approved = (clone $query)->where('is_live', 1)->paginate($limit, ['*'], 'page', $page);
                $approvedUserIds = $approved->getCollection()->pluck('user_id')->unique();
                $approvedUser = UserData::whereIn('uid', $approvedUserIds)->get()->keyBy('uid');

                $approvedTemplateIds = $approved->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $approvedTemplateIds)->get()->keyBy('string_id');

                foreach ($approved->items() as $data) {
                    $user = $approvedUser->get($data->user_id);
                    $data->user_email = $user ? $user->email : null;
                    $data->user_name = $user ? $user->name : null;
                    unset($data->user_id);

                    $temp = $approvedTemps->get($data->template_id);
                    $id = $temp ? $temp->id : '--';
                    $data->name = "($id) $data->name";

                    if ($data->video) {
                        $data->video = HelperController::$mediaUrl . $data->video;
                    }
                    unset($data->template_id);
                }

                $rejected = collect();

                $datas[] = [
                    "title" => "Not Approved",
                    "datas" => $this->processThumbs($notApproved->items()),
                    "length" => $notApproved->total(),
                    "type" => 0,
                    "pageNo" => $page,
                    "isLastPage" => $notApproved->currentPage() >= $notApproved->lastPage()
                ];

                $datas[] = $this->getPortfolio(request: $request, userData: $userData, isHod: true, page: $page, limit: $limit);

            } else {
                if ($type == 1) {
                    $singleData = $this->getPortfolio(request: $request, userData: $userData, isHod: true, page: $page, limit: $limit);
                } else {
                    $list = (clone $query)->where('is_live', $type)->paginate($limit, ['*'], 'page', $page);
                    $userIds = $list->getCollection()->pluck('user_id')->unique();
                    $users = UserData::whereIn('uid', $userIds)->get()->keyBy('uid');

                    $templateIds = $list->getCollection()->pluck('template_id')->unique();
                    $approvedTemps = Design::whereIn('string_id', $templateIds)->get()->keyBy('string_id');

                    foreach ($list->items() as $data) {
                        $user_ = $users->get($data->user_id);
                        $data->user_email = $user_ ? $user_->email : null;
                        $data->user_name = $user_ ? $user_->name : null;
                        unset($data->user_id);

                        $temp = $approvedTemps->get($data->template_id);
                        if ($temp) {
                            $data->name = "($temp->id) $data->name";
                            unset($data->template_id);
                        }

                        if ($data->video) {
                            $data->video = HelperController::$mediaUrl . $data->video;
                        }
                    }

                    $singleData = [
                        "title" => "",
                        "datas" => $this->processThumbs($list->items()),
                        "length" => $list->total(),
                        "type" => $type,
                        "pageNo" => $page,
                        "isLastPage" => $list->currentPage() >= $list->lastPage()
                    ];
                }
            }
        } else {
            if ($page == 1) {
                $notApproved = (clone $query)->where('user_id', $this->uid)->where('is_live', -1)->paginate($limit, ['*'], 'page', $page);
                $rejected = (clone $query)->where('user_id', $this->uid)->where('is_live', -2)->paginate($limit, ['*'], 'page', $page);
                $approved = (clone $query)->where('user_id', $this->uid)->where('is_live', 1)->paginate($limit, ['*'], 'page', $page);

                $approvedTemplateIds = $approved->getCollection()->pluck('template_id')->unique();
                $approvedTemps = Design::whereIn('string_id', $approvedTemplateIds)->get()->keyBy('string_id');

                foreach ($approved->items() as $data) {
                    $temp = $approvedTemps->get($data->template_id);
                    $id = $temp ? $temp->id : '--';
                    $data->name = "($id) $data->name";
                    unset($data->template_id);
                }

                $datas[] = [
                    "title" => "Running",
                    "datas" => $this->processThumbs($notApproved->items()),
                    "length" => $notApproved->total(),
                    "type" => -1,
                    "pageNo" => $page,
                    "isLastPage" => $notApproved->currentPage() >= $notApproved->lastPage()
                ];

                $datas[] = [
                    "title" => "Rejected",
                    "datas" => $this->processThumbs($rejected->items()),
                    "length" => $rejected->total(),
                    "type" => -2,
                    "pageNo" => $page,
                    "isLastPage" => $rejected->currentPage() >= $rejected->lastPage()
                ];

                $datas[] = $this->getPortfolio(request: $request, userData: $userData, isHod: false, page: $page, limit: $limit);

            } else {
                if ($type == 1) {
                    $singleData = $this->getPortfolio(request: $request, userData: $userData, isHod: false, page: $page, limit: $limit);
                } else {
                    $list = (clone $query)->where('user_id', $this->uid)->where('is_live', $type)->paginate($limit, ['*'], 'page', $page);

                    $templateIds = $list->getCollection()->pluck('template_id')->unique();
                    $approvedTemps = Design::whereIn('string_id', $templateIds)->get()->keyBy('string_id');

                    foreach ($list->items() as $data) {
                        $temp = $approvedTemps->get($data->template_id);
                        if ($temp) {
                            $data->name = "($temp->id) $data->name";
                            unset($data->template_id);
                        }
                    }

                    $singleData = [
                        "title" => "",
                        "datas" => $this->processThumbs($list->items()),
                        "length" => $list->total(),
                        "type" => $type,
                        "pageNo" => $page,
                        "isLastPage" => $list->currentPage() >= $list->lastPage()
                    ];
                }
            }
        }

        $response = [
            'list' => $datas,
            'data' => $singleData,
            'hoc' => $userData->hoc,
        ];

        return $this->successed(datas: $response);
    }

    public function getPortfolio(Request $request, UserData $userData, $isHod, $page, $limit): array|string
    {

        if ($isHod) $query = Design::whereNotNull('creator_id');
        else $query = Design::where('creator_id', $userData->uid);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('h2_tag', 'like', '%' . $searchTerm . '%')
                    ->orWhere('id_name', 'like', '%' . $searchTerm . '%');
            });
        }

        $filter = $request->input('filter');
        if ($filter && isset($filter['id'], $filter['type'])) {
            $filterId = $filter['id'];
            $filterType = $filter['type'];
            if ($filterType === 'child') {
                $query->where('new_category_id', $filterId);
            } elseif ($filterType === 'All' || $filterType === 'all') {
                $category = NewCategory::find($filterId);
                if ($category) {
                    if ($category->parent_category_id != 0) {
                        $query->where('new_category_id', $filterId);
                    } else {
                        $childIds = NewCategory::where('parent_category_id', $filterId)->pluck('id')->toArray();
                        $query->whereIn('new_category_id', $childIds);
                    }
                }
            } elseif ($filterType === 'tag') {
                $tagId = (int)$filterId;
                $parentId = (int)($filter['parent_id'] ?? 0);
                $query->where('new_category_id', $parentId)
                    ->whereJsonContains('new_related_tags', $tagId);
            }
        }
        $query->orderBy('id', 'DESC');
        $transformedCategories = [];
        if ($page == 1) {
            $allTemplates = (clone $query)->get();
            $allNewCategoryIds = $allTemplates->pluck('new_category_id')->unique()->values();
            $allNewCategories = NewCategory::whereIn('id', $allNewCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get()
                ->keyBy('id');
            $tagsByCategory = $allTemplates->groupBy('new_category_id')->map(function ($designs) {
                return $designs->pluck('new_related_tags')
                    ->filter()
                    ->flatten()
                    ->unique()->values();
            });
            $allTagIds = $tagsByCategory->flatten()->unique()->values();
            $searchTags = NewSearchTag::whereIn('id', $allTagIds)->get()->keyBy('id');
            $categoryTagMap = $tagsByCategory->map(function ($tagIds) use ($searchTags) {
                return $tagIds->map(function ($tagId) use ($searchTags) {
                    $tag = $searchTags->get($tagId);
                    return $tag ? [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'parent_category_id' => $tag->category_id ?? null,
                    ] : null;
                })->filter()->values();
            });
            $parentCategoryIds = $allNewCategories->pluck('parent_category_id')->filter()->unique();
            $parentCategories = NewCategory::whereIn('id', $parentCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get();
            $transformedCategories = $parentCategories->map(function ($parent) use ($allNewCategories, $categoryTagMap) {
                $children = $allNewCategories->filter(function ($cat) use ($parent) {
                    return $cat->parent_category_id == $parent->id;
                })->values();
                $subCategories = collect();
                foreach ($children as $child) {
                    $tags = collect();
                    $tags->push([
                        'id' => $child->id,
                        'name' => 'All',
                        'type' => 'all',
                        'display_name' => $child->category_name,
                    ]);
                    if ($categoryTagMap->has($child->id)) {
                        foreach ($categoryTagMap[$child->id] as $tag) {
                            $tags->push([
                                'id' => $tag['id'],
                                'name' => $tag['name'],
                                'display_name' => $tag['name'],
                                'parent_id' => $child->id,
                                'type' => 'tag',
                            ]);
                        }
                    }
                    $subCategories->push([
                        'id' => $child->id,
                        'name' => $child->category_name,
                        'display_name' => $child->category_name,
                        'parent_id' => $child->parent_category_id,
                        'type' => 'child',
                        'tags' => $tags->toArray()
                    ]);
                }
                return [
                    'id' => $parent->id,
                    'name' => $parent->category_name,
                    'display_name' => $parent->category_name,
                    'parent_id' => $parent->parent_category_id,
                    'type' => 'parent',
                    'sub_categories' => collect([
                        [
                            'id' => $parent->id,
                            'name' => 'All',
                            'type' => 'all',
                            'display_name' => $parent->category_name,
                        ]
                    ])->merge($subCategories)->toArray()
                ];
            })->values();
            $allCategoriesOption = collect([
                [
                    'id' => 0,
                    'name' => 'All Categories',
                    'display_name' => 'Category',
                    'parent_id' => 0,
                    'type' => 'parent',
                    'default' => true,
                ]
            ]);
            $transformedCategories = $allCategoriesOption->merge($transformedCategories)->values();
        }
        $templates = $query->paginate($limit, ['*'], 'page', $page);

        $approvedUserIds = $templates->getCollection()->pluck('creator_id')->unique();
        $approvedUser = UserData::whereIn('uid', $approvedUserIds)->get()->keyBy('uid');

        $item_rows = collect($templates->items())->map(function ($item) use ($approvedUser) {

            /** @var Design $item */

            $user_ = $approvedUser->get($item->creator_id);

            $thumbs = json_decode($item->thumb_array, true) ?? [];
            $prefetchedThumbs = array_map(function ($thumb) {
                return HelperController::$mediaUrl . $thumb;
            }, $thumbs);

            return [
                "string_id" => $item->creator_draft_id,
                "template_id" => $item->string_id,
                "name" => "($item->id) $item->post_name",
                "ratio" => $item->ratio,
                "width" => $item->width,
                "height" => $item->height,
                "thumbs" => $prefetchedThumbs,
                "msg" => null,
                "user_email" => $user_ ? $user_->email : null,
                "user_name" => $user_ ? $user_->name : null,
                "video" => $item->video_thumb ? HelperController::$mediaUrl . $item->video_thumb : null,
            ];

        })->filter()->values();

        return [
            "title" => "Approved",
            "datas" => $item_rows,
            "length" => $templates->total(),
            "type" => 1,
            "pageNo" => $page,
            "isLastPage" => $templates->currentPage() >= $templates->lastPage(),
            'categories' => $transformedCategories,
        ];
    }

    function get(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $id = $request->get('i');
        if ($id == null) return $this->failed(msg: "Parameters missing");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc) {
            $data = DesignerDraft::where('string_id', $id)->where('is_live', 0)->first();
        } else {
            if ($this->isTester())
                $data = DesignerDraft::where('string_id', $id)->whereIn('is_live', [-1, -2])->first();
            else
                $data = DesignerDraft::where('string_id', $id)->where('user_id', $this->uid)->whereIn('is_live', [-1, -2])->first();
        }

        if (!$data) return $this->failed(msg: "Data not found");

        $path = "/" . $data->designs;
        $frame = json_decode(StorageUtils::get($path));

        while (is_string($frame)) {
            $frame = json_decode($frame);
        }

        return $this->successed(datas: [
            'type' => 0,
            'name' => $data->name,
            'data' => JSONUtils::applyPageStringId($frame, $data->template_id),
            'ul' => $data->string_id,
            'is_purchased' => 1,
            'thumb' => DesignerDraftController::getDesignThumb($data->thumbs)
        ]);
    }

    function save(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc) return $this->failed(msg: "Head of creator can't change the design");

        $post_name = $request->get('n');
        $draft_id = $request->get('i');
        $draft_save_data = $request->get('d');
        $draft_thumbs = $request->file('t');

        if ($draft_id == null || $draft_save_data == null || $draft_thumbs == null || $post_name == null) {
            return $this->failed(msg: "Parameters missing");
        }

        foreach ($draft_thumbs as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($fileExtension), $this->allowedExt)) {
                return $this->failed(msg: "Invalid file");
            }
        }

        $draft_id = CryptoJsAes::decrypt($draft_id, $this->aesPassword);
        $draft_save_data = CryptoJsAes::decrypt($draft_save_data, $this->aesPassword);

        $data = DesignerDraft::where('string_id', $draft_id)->where('user_id', $this->uid)->whereIn('is_live', [-1, -2])->first();
        if (!$data) return $this->failed(msg: "Data not found");

        try {
            foreach (json_decode($data->thumbs) as $thumb) {
                StorageUtils::delete($thumb);
            }
        } catch (\Exception $e) {

        }

        $jsonData = json_decode($draft_save_data);
        if (!DesignerDraftController::checkJsonIsValid($jsonData)) {
            return $this->failed(msg: "Invalid data");
        }

        $thumbArray = [];
        foreach ($draft_thumbs as $file) {
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            StorageUtils::storeAs($file, 'uploadedFiles/thumb_file', $new_name);
            $thumbArray[] = 'uploadedFiles/thumb_file/' . $new_name;
        }

        $oldThumbArray = json_decode($data->thumbs);
        foreach ($oldThumbArray as $thumb) {
            StorageUtils::delete($thumb);
        }

        $jsonData->name = $post_name;

        $oldPath = $data->designs;
        $jsonFilePath = 'uploadedFiles/fab_designs/' . StorageUtils::getNewName() . '.json';
        StorageUtils::put($jsonFilePath, json_encode($jsonData));

        $data->designs = $jsonFilePath;
        $data->name = $post_name;
        $data->thumbs = json_encode($thumbArray);
        $data->save();

        StorageUtils::delete($oldPath);

        return $this->successed(datas: ['type' => 0, 'ul' => $data->string_id, 'is_purchased' => 1]);
    }

    function export(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if ($user->hoc) return $this->failed(msg: "Head of creator can't save the design");

        $keyArrays = ["i", "type", "d"];

        foreach ($keyArrays as $key) {
            if (!request()->has($key)) return $this->failed(msg: "Parameters missing");
        }

        $draft_id = $request->get('i');
        $type = (int)$request->get('type', -1);
        $draft_save_data = $request->get('d');
        $draft_thumbs = $request->file('t');
        $caricatureIds = $request->get('caricature_ids');
        $video = $request->file('file');

        $draft_save_data = CryptoJsAes::decrypt($draft_save_data, $this->aesPassword);

        foreach ($draft_thumbs as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($fileExtension), $this->allowedExt)) {
                return $this->failed(msg: "Invalid file");
            }
        }

        if (!is_string($draft_id) || is_null($draft_save_data) || $draft_thumbs == null) {
            return $this->failed(msg: "Params are invalid");
        }

        if (!is_numeric($type) || $type < 0 || $type > 1) {
            return $this->failed(msg: "Type is invalid");
        }

        if ($type == 1 && !$video) {
            return $this->failed(msg: "Video file is missing");
        }

        if ($video && $video->getClientOriginalExtension() !== 'mp4') {
            return $this->failed(msg: "Video file is invalid");
        }

        $data = DesignerDraft::where('string_id', $draft_id)->where('user_id', $this->uid)->whereIn('is_live', [-1, -2])->first();
        if (!$data) return $this->failed(msg: "Data not found");

        $jsonData = json_decode($draft_save_data);

        if (!DesignerDraftController::checkJsonIsValid($jsonData)) {
            return $this->failed(msg: "Invalid data");
        }

        $data->name = $jsonData->name;

        StorageUtils::put($data->designs, json_encode($jsonData));

        $videoFile = null;
        if ($video) {
            $new_name = StorageUtils::getNewName() . '.' . $video->getClientOriginalExtension();
            StorageUtils::storeAs($video, 'uploadedFiles/v', $new_name);
            $videoFile = 'uploadedFiles/v/' . $new_name;
        }

        StorageUtils::delete($data->video);

        $thumbArray = [];
        foreach ($draft_thumbs as $file) {
            $new_name = StorageUtils::getNewName() . '.' . $file->getClientOriginalExtension();
            StorageUtils::storeAs($file, 'uploadedFiles/thumb_file', $new_name);
            $thumbArray[] = 'uploadedFiles/thumb_file/' . $new_name;
        }

        $oldThumbArray = json_decode($data->thumbs);
        foreach ($oldThumbArray as $thumb) {
            StorageUtils::delete($thumb);
        }

        $data->video = $videoFile;
        $data->thumbs = json_encode($thumbArray);
        $data->caricature_ids = $caricatureIds;
        $data->is_live = 0;
        $data->msg = null;
        $data->save();

        return $this->successed(msg: "Done");
    }

    function reject(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if (!$user->hoc) return $this->failed(msg: "Unauthorized");

        $keyArrays = ["i", "msg"];

        foreach ($keyArrays as $key) {
            if (!request()->has($key)) {
                return $this->failed(msg: "Parameters missing");
            }
        }

        $draft_id = $request->get('i');
        $msg = $request->get('msg');

        if (!is_string($draft_id) || is_null($msg)) {
            return $this->failed(msg: "Params are invalid");
        }

        $data = DesignerDraft::where('string_id', $draft_id)->where('is_live', 0)->first();
        if (!$data) return $this->failed(msg: "Data not found");


        StorageUtils::delete($data->video);

        $data->is_live = -2;
        $data->msg = $msg;
        $data->video = null;
        $data->save();

        return $this->successed(msg: "Done");
    }

    function approve(Request $request): array|string
    {
        if ($this->isFakeRequestAndCreator($request)) return $this->failed(msg: "Unauthorized");

        $user = UserData::where("uid", $this->uid)->first();
        if (!$user->hoc) return $this->failed(msg: "Unauthorized");

        $keyArrays = ["i"];

        foreach ($keyArrays as $key) {
            if (!request()->has($key)) return $this->failed(msg: "Parameters missing");
        }

        $draft_id = $request->get('i');

        if (!is_string($draft_id)) return $this->failed(msg: "Params are invalid");

        $data = DesignerDraft::where('string_id', $draft_id)->where('is_live', 0)->first();
        if (!$data) return $this->failed(msg: "Data not found");

        $res = new Design();
        $res->app_id = 1;

        $res->string_id = $data->template_id;
        $res->creator_id = $data->user_id;
        $res->caricature_ids = json_encode($data->caricature_ids);
        $res->creator_draft_id = $draft_id;
        $res->emp_id = 0;

        $res->post_name = $data->name;
        $res->ratio = $data->ratio;

        $res->width = $data->width;
        $res->height = $data->height;
        $res->status = 0;
        $res->is_premium = 0;

        $thumbs = json_decode($data->thumbs, true);

        $res->animation = $data->video ? 1 : 0;
        $res->video_thumb = $data->video;
        $res->post_thumb = $thumbs[0];
        $res->thumb_array = json_encode($thumbs);

        $res->fab_designs = $data->designs;
        $res->size = 0;

        $mainWidth = $data->width;
        $mainHeight = $data->height;

        if ($mainWidth == $mainHeight) {
            $res->orientation = "square";
        } else if ($mainWidth < $mainHeight) {
            $res->orientation = "portrait";
        } else {
            $res->orientation = "landscape";
        }

        $res->save();

        $data->is_live = 1;
        $data->save();

        return $this->successed(msg: "Done");
    }

    private function processThumbs($items): array
    {
        return collect($items)->map(function ($item) {
            $thumbs = json_decode($item->thumbs ?? $item->thumb_array, true) ?? [];
            $prefetchedThumbs = array_map(function ($thumb) {
                return HelperController::$mediaUrl . $thumb;
            }, $thumbs);
            $item->thumbs = $prefetchedThumbs;
            return $item;
        })->toArray();
    }

    private function formatCount($count): string
    {
        if ($count >= 1000000) return round($count / 1000000, 1) . 'M';
        if ($count >= 1000) return round($count / 1000, 1) . 'K';
        return (string)$count;
    }

    public static function checkJsonIsValid($jsonData): bool
    {

        $jsonData->pages = array_filter($jsonData->pages, function ($page) {
            return !empty($page->objects) &&
                isset($page->objects[0]->type) &&
                $page->objects[0]->type === "Background";
        });

        $jsonData->pages = array_values($jsonData->pages);

        $validData = true;
        if (empty($jsonData->pages) || !is_array($jsonData->pages)) {
            $validData = false;
        } else {
            foreach ($jsonData->pages as $page) {
                if (empty($page->objects) || $page->objects[0]->type !== "Background") {
                    $validData = false;
                    break;
                }
            }
        }
        return $validData;
    }

    public static function getDesignThumb($thumbs): string|null
    {
        if (!$thumbs) return null;
        $thumbArray = json_decode($thumbs, true) ?? [];
        if (empty($thumbArray)) return null;
        return HelperController::generatePublicUrl($thumbArray[0]);
    }
}


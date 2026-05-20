<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\RateController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\RawDatas;
use App\Models\Video\VideoTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserData;

class VideoSearchApiController extends ApiController
{
    private array $extra = ["aboard", "about", "above", "across", "after", "against", "along", "amid", "amidst", "among", "amongst", "around, as, at", "before", "behind", "below", "beneath", "beside", "between", "beyond", "but, by", "concerning", "considering", "despite", "down", "during", "except", "for", "from, in", "inside", "into", "like", "near, of", "off, on", "onto", "out", "outside", "over", "past", "regarding", "round", "since", "through", "throughout", "till", "until, to", "toward", "towards", "under", "underneath", "unlike", "until", "unto, up", "upon", "with", "within", "without, am, is", "are", "was", "were", "been", "being", "have", "has", "had, do", "does", "did", "can", "could", "may", "might", "shall", "should", "will", "would", "must"];

    public function exactKeywordTemplates($rates, array $keywords, int $limit, $excludeTemplate = null): array
    {

        $hasShowAll = false;

        $user_data = UserData::where("uid", $this->uid)->first();

        if ($user_data && ($user_data->can_update == 1 || $user_data->can_update == '1')) {
            $hasShowAll = true;
        }

        $status_condition = $hasShowAll ? "!=" : "=";
        $status = $hasShowAll ? "-1" : "1";

        $itemData = VideoTemplate::with(['videoCat', 'virtualCat'])
            ->where("string_id", "!=", $excludeTemplate)
            ->where('status', $status_condition, $status)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereJsonContains('keyword', $keyword);
                }
            })
            ->orderByRaw('id DESC')
            ->limit($limit)
            ->get();

        $item_rows = [];

        foreach ($itemData as $item) {
            $item_rows[] = HelperController::getVideoItemData(item: $item, rates: $rates);
        }

        return [
            'isLastPage' => true,
            'datas' => $item_rows,
        ];

    }

    public function searchTemplates($rates, $keywords, $page, $cat_id, $limit, $excludeIdName = "love", $ratio = null): array
    {
        $hasShowAll = UserData::where('uid', $this->uid)
            ->where('can_update', 1)
            ->exists();

        $statusCondition = $hasShowAll ? '!=' : '=';
        $status = $hasShowAll ? '-1' : '1';

        $ratioCondition = $ratio ? '=' : '!=';
        $ratio = strval($ratio ?? -1);

        $keywords = str_replace('-', ' ', $keywords);
        $description = str_replace(',', ' ', $keywords);
        $desc_array = array_filter(explode(' ', $description)); // Remove empty strings

        $catCondition = '!=';
        $catId = -1;
        $fieldName = is_numeric($cat_id) ? 'id' : 'id_name';
        $newCond = $fieldName === 'id_name' ? null : -1;

        if ($cat_id !== null && $cat_id !== $newCond) {
            $catRow = NewCategory::where($fieldName, $cat_id)->first();
            if ($catRow) {
                $catCondition = '=';
                $catId = $catRow->id;
            }
        }

        // Build the CASE statement for keyword_match_priority
        $caseStatement = 'CASE ';
        $caseBindings = [];
        $caseStatement .= 'WHEN related_tags LIKE ? THEN ? ';
        $caseBindings[] = "%\"{$keywords}\"%";
        $caseBindings[] = count($desc_array) + 2;

        $caseStatement .= 'WHEN related_tags LIKE ? THEN ? ';
        $caseBindings[] = "%\"" . implode(' ', array_reverse($desc_array)) . "\"%";
        $caseBindings[] = count($desc_array) + 1;

        $caseStatement .= 'WHEN SOUNDEX(related_tags) = SOUNDEX(?) THEN ? ';
        $caseBindings[] = $keywords;
        $caseBindings[] = count($desc_array) + 0.5;

        foreach ($desc_array as $index => $keyword) {
            $caseStatement .= 'WHEN related_tags LIKE ? THEN ? ';
            $caseBindings[] = "%\"{$keyword}\"%";
            $caseBindings[] = count($desc_array) - $index;

            $caseStatement .= 'WHEN related_tags LIKE ? THEN ? ';
            $caseBindings[] = "% {$keyword} %";
            $caseBindings[] = count($desc_array) - $index;

            $caseStatement .= 'WHEN related_tags LIKE ? THEN ? ';
            $caseBindings[] = "%{$keyword}%";
            $caseBindings[] = count($desc_array) - $index;

            $caseStatement .= 'WHEN SOUNDEX(related_tags) = SOUNDEX(?) THEN ? ';
            $caseBindings[] = $keyword;
            $caseBindings[] = count($desc_array) - $index - 0.5;
        }

        $caseStatement .= 'ELSE 0 END AS keyword_match_priority';

        // Build the query
        $query = Design::select('*')
            ->selectRaw($caseStatement, $caseBindings)
            ->where('id_name', '!=', $excludeIdName)
            ->where('new_category_id', $catCondition, $catId)
            ->where('ratio', $ratioCondition, $ratio)
            ->where('status', $statusCondition, $status)
            ->where(function ($q) use ($keywords, $desc_array) {
                $q->where('related_tags', 'LIKE', "%\"{$keywords}\"%")
                    ->orWhere('related_tags', 'LIKE', "%\"" . implode(' ', array_reverse($desc_array)) . "\"%")
                    ->orWhereRaw('SOUNDEX(related_tags) = SOUNDEX(?)', [$keywords]);

                $uniquePatterns = []; // Track unique LIKE patterns to avoid duplicates
                foreach ($desc_array as $keyword) {
                    $patterns = [
                        "%\"{$keyword}\"%",
                        "% {$keyword} %",
                        "%{$keyword}%"
                    ];

                    foreach ($patterns as $pattern) {
                        if (!in_array($pattern, $uniquePatterns)) {
                            $q->orWhere('related_tags', 'LIKE', $pattern);
                            $uniquePatterns[] = $pattern;
                        }
                    }
                    $q->orWhereRaw('SOUNDEX(related_tags) = SOUNDEX(?)', [$keyword]);
                }
            })
            ->orderByDesc('keyword_match_priority')
            ->orderByDesc('created_at');

        // Fetch paginated results
        $items = $query->offset(($page - 1) * $limit)->take($limit)->get();

        $item_rows = [];
        if ($items->isNotEmpty()) {
            $categoryIds = $items->pluck('new_category_id')->unique();
            $categories = NewCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $catRow = $categories[$item->new_category_id] ?? null;
                $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
                if ($catRow) {
                    $catLink = $catRow->cat_link;
                }

                $item_rows[] = HelperController::getItemData(
                    uid: $this->uid,
                    catRow: $catRow,
                    item: $item,
                    thumbArray: json_decode($item->thumb_array, true),
                    catLink: $catLink,
                    rates: $rates
                );
            }
        }

        return [
            'isLastPage' => count($item_rows) < $limit,
            'datas' => $item_rows,
        ];
    }

    function searchReferTemplates(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"), true);
        }

        $refWidth = $request->get('w');
        $refHeight = $request->get('h');
        $keywords = $request->get('keywords');
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = $request->has('limit') ? $request->get('limit') : HelperController::getPaginationLimit(size: 50);

        $item_rows = array();

        if ($keywords == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"), true);
        }

        $rates = RateController::getRates();
        if (!$refWidth || !$refHeight) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", $this->searchTemplates($rates, $keywords, $page, $request->get('id'), $limit)), true);
        }

        $keywords = str_replace('-', ' ', $keywords);
        $description = str_replace(',', ' ', $keywords);
        $desc_array = explode(' ', $description);

        if (count($desc_array) > 1) {
            $desc_array = array_filter($desc_array, function ($word) {
                return !in_array($word, $this->extra);
            });
        }
        $tempRatio = $refWidth / $refHeight;
        $tempRatio = round($tempRatio, 2);

        $sql = "SELECT *,  CASE ";

        $sql .= "WHEN related_tags LIKE '%\"" . $keywords . "\"%' THEN " . (count($desc_array) + 2) . " ";
        $sql .= "WHEN related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' THEN " . (count($desc_array) + 1) . " ";

        foreach ($desc_array as $index => $keyword) {
            $sql .= "WHEN related_tags LIKE '%\"" . $keyword . "\"%' THEN " . (count($desc_array) - $index) . " ";
        }
        foreach ($desc_array as $index => $keyword) {
            $sql .= "WHEN related_tags LIKE '% " . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
            $sql .= "WHEN related_tags LIKE '% " . $keyword . "%' THEN " . (count($desc_array) - $index) . " ";
            $sql .= "WHEN related_tags LIKE '%" . $keyword . " %' THEN " . (count($desc_array) - $index) . " ";
        }
        $sql .= "ELSE 0 END AS keyword_match_priority
                    FROM designs
                    WHERE ratio = " . $tempRatio . "
                    AND status=1
                    AND (
                        related_tags LIKE '%\"" . $keywords . "\"%' OR related_tags LIKE '%\"" . implode(' ', array_reverse($desc_array)) . "\"%' ";

        foreach ($desc_array as $keyword) {
            $sql .= "OR related_tags LIKE '%\"" . $keyword . "\"%' ";
        }
        foreach ($desc_array as $keyword) {
            $sql .= "OR related_tags LIKE '% " . $keyword . " %' ";
            $sql .= "OR related_tags LIKE '% " . $keyword . "%' ";
            $sql .= "OR related_tags LIKE '%" . $keyword . " %' ";
        }
        $sql .= ") ORDER BY keyword_match_priority DESC, created_at DESC";

        $res = DB::select($sql);

        $itemData = $this->limitArray($res, $limit, ($page - 1) * $limit);

        if ($itemData != null) {
            $categoryIds = collect($itemData)->pluck('new_category_id')->unique();
            $categories = NewCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

            $item_rows = [];

            foreach ($itemData as $item) {
                $catRow = $categories[$item->new_category_id] ?? null;
                $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
                if ($catRow != null) {
                    $catLink = $catRow->cat_link;
                }

                $item_rows[] = HelperController::getItemData(
                    uid: $this->uid,
                    catRow: $catRow,
                    item: $item,
                    thumbArray: json_decode($item->thumb_array),
                    swap: false,
                    catLink: $catLink,
                    fromEditor: true,
                    rates: $rates
                );
            }
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Loaded", [
            'isLastPage' => count($item_rows) < $limit,
            'datas' => $item_rows
        ]), true);
    }

    function searchElements(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $elementType = $request->get('element_type');
        $cat_id = $request->get('id');
        $keywords = $request->get('keywords');
        $page = $request->get('page');
        $hasShowAll = $request->has('showAll');

        $status_condition = "=";
        $status = "1";
        if ($hasShowAll) {
            $status_condition = "!=";
            $status = "-1";
        }

        if ($elementType == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Parameters missing!"));
        }

        $item_rows = array();

        if ($keywords == null) {
            return $item_rows;
        }
        $limit = HelperController::getPaginationLimit(size: 50);

        $description = str_replace(',', ' ', $keywords);
        $desc_array = explode(' ', $description);

        if ($elementType == 1) {
            $tableName = "bg_items";
            $catColumnId = "bg_cat_id";
            $catColumnName = "bg_name";
        } else if ($elementType == 2) {
            $tableName = "sticker_items";
            $catColumnId = "stk_cat_id";
            $catColumnName = "sticker_name";
        } else {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Incorrect Type!"));
        }

        $query = DB::table($tableName)->where("status", $status_condition, $status);
        if ($cat_id != null && $cat_id > 0) $query->where($catColumnId, $cat_id);
        $query->where(function ($query) use ($catColumnName, $desc_array, $keywords) {
            $query->orWhere($catColumnName, 'like', '%' . $keywords . '%');
            foreach ($desc_array as $condition) {
                $query->orWhere($catColumnName, 'like', '%' . $condition . '%');
            }
        });
        $query->orderBy(DB::raw("CASE
                                            WHEN $catColumnName LIKE '%$keywords%' THEN 1
                                            WHEN $catColumnName LIKE '$keywords%' THEN 2
                                            WHEN $catColumnName LIKE '%$keywords' THEN 4
                                            ELSE 3
                                        END"
        ));

        $itemData = $query->paginate($limit, ['*'], 'page', $page);

        $rawDatas = RawDatas::where("trashed", 0)
            ->where("asset_type", 0)
            ->where("deleted", 0)
            ->where("status", 1)
            ->where(function ($query) use ($desc_array, $keywords) {
                $query->orWhere('name', 'like', '%' . $keywords . '%');
                foreach ($desc_array as $condition) {
                    $query->orWhere('name', 'like', '%' . $condition . '%');
                }
            })->orderBy(DB::raw("CASE
                                            WHEN name LIKE '%$keywords%' THEN 1
                                            WHEN name LIKE '$keywords%' THEN 2
                                            WHEN name LIKE '%$keywords' THEN 4
                                            ELSE 3
                                        END"
            ))
            ->paginate($limit, ['*'], 'page', $page);

        foreach ($itemData->items() as $item) {
            if ($elementType == 1) {
                $item_rows[] = array(
                    'category_id' => $item->bg_cat_id,
                    'id' => $item->id,
                    'name' => $item->bg_name,
                    'thumb' => HelperController::$mediaUrl . $item->bg_thumb,
                    'file' => HelperController::$mediaUrl . $item->bg_image,
                    'type' => $item->bg_type,
                    'width' => $item->width,
                    'height' => $item->height,
                    'latest' => 0,
                    'is_premium' => $item->is_premium,
                );
            } else {
                $item_rows[] = array(
                    'category_id' => $item->stk_cat_id,
                    'id' => $item->id,
                    'name' => $item->sticker_name,
                    'thumb' => HelperController::$mediaUrl . $item->sticker_thumb,
                    'file' => HelperController::$mediaUrl . $item->sticker_image,
                    'type' => $item->sticker_type,
                    'width' => $item->width,
                    'height' => $item->height,
                    'latest' => 0,
                    'is_premium' => $item->is_premium,
                );
            }
        }

        foreach ($rawDatas->items() as $item) {
            $item_rows[] = array(
                'category_id' => 0,
                'id' => $item->id,
                'name' => $item->name,
                'thumb' => HelperController::$mediaUrl . $item->thumbnail,
                'file' => HelperController::$mediaUrl . $item->image,
                'type' => 0,
                'width' => $item->width,
                'height' => $item->height,
                'latest' => 0,
                'is_premium' => 0,
            );
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "",
            [
                'current_page' => $page,
                'isLastPage' => $itemData->currentPage() >= $itemData->lastPage() && $rawDatas->currentPage() >= $rawDatas->lastPage(),
                'datas' => $item_rows,
            ]
        ));
    }

    function limitArray($array, $limit, $offset = 0): array
    {
        $return = array();
        $end = ($limit + $offset);
        $count = 0;
        foreach ($array as $key => $val) {
            if ($count++ >= $offset) {
                $return[$key] = $val;
            }
            if ($count == $end) break;
        }
        return $return;
    }
}

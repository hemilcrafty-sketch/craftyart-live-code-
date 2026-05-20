<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Models\Color;
use App\Models\Video\VideoSize;
use App\Models\Video\VideoTemplate;
use App\Models\Video\VideoInterest;
use App\Models\Video\VideoLanguage;
use App\Models\Video\VideoReligion;
use App\Models\Video\VideoStyle;
use App\Models\Video\VideoTheme;
use App\Models\Video\VideoCategory;
use Cache;
use Illuminate\Http\Request;

class VideoFilterController extends ApiController
{

    function getFilters(Request $request): string|array
    {

        try {
            if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");
            if (!$request->id) return $this->failed(msg: "Params missing");

            $id = $request->id;
            $callback = function () use ($request, $id) {

                $category = VideoCategory::findId(isStatus: 1, id: $id);
                if (!$category) $this->failed(msg: "Data not found");
//                return $category;
                $rootParentId = $category->parent;
                $rootParentId = $rootParentId ? $rootParentId['id'] : $id;
                $catId = is_string($rootParentId) ? $rootParentId : json_encode($rootParentId);

                $colors = Color::where('status', 1)->orderBy("id", "desc")->get();
                $interests = VideoInterest::select(['id', 'id_name', 'name'])->whereJsonContains('category_id', $catId)->where('status', 1)->orderBy("id", "desc")->get();
                $sizes = VideoSize::select(["id", "size_name as name", "width_ration as p_width", "height_ration as p_height", "width as l_width", "height as l_height", "id_name"])->where('status', 1)->get();
                $styles = VideoStyle::select(['id', 'id_name', 'name'])->where('status', 1)->orderBy("id", "desc")->get();

                $languages = $this->getCounts(
                    VideoLanguage::class,
                    ['id', 'id_name', 'name'],
                    'lang_id'
                );

                $religions = $this->getCounts(
                    VideoReligion::class,
                    ['id', 'name', 'id_name'],
                    'religion_id'
                );

                $themes = $this->getCounts(
                    VideoTheme::class,
                    ['id', 'id_name', 'name'],
                    'style_id',
                    'category_id',
                    $catId
                );

                $response['colors'] = $colors;
                $response['interests'] = $interests;
                $response['languages'] = $languages;
                $response['religions'] = $religions;
                $response['sizes'] = $sizes;
                $response['styles'] = $styles;
                $response['themes'] = $themes;

                return $this->successed(datas: ['datas' => $response], showDecoded: true);
            };

            if (HelperController::$cacheEnabled) $response = Cache::tags(["vi_filter_api"])->remember("vi_filter_api", HelperController::$cacheTimeOut, $callback);
            else $response = $callback();

            return $this->sendResponse(response: $response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function getCounts($model, $selectFields, $jsonField, $filterColumn = null, $filterValue = null, $orderColumn = "id", $orderDirection = "desc"): array
    {
        $query = $model::select($selectFields)->where('status', 1)->orderBy($orderColumn, $orderDirection);
        if ($filterColumn && $filterValue) {
            $query->whereJsonContains($filterColumn, strval($filterValue));
        }
        $records = $query->get();
        $counts = [];
        foreach ($records as $row) {
            $count = VideoTemplate::whereJsonContains($jsonField, strval($row->id))->count();
            $counts[] = [
                'data' => $row,
                'count' => $count,
            ];
        }
        // Sort by count in descending order
        usort($counts, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $response = [];
        foreach ($counts as $data) {
            $response[] = $data['data'];
        }
        return $response;
    }
}

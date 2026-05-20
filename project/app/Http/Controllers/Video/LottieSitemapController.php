<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Utils\ApiController;
use App\Models\Video\VideoCategory;
use App\Models\Video\VideoVirtualCategory;
use Illuminate\Http\Request;

class LottieSitemapController extends ApiController
{

    function keywords(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $datas = VideoCategory::query()
            ->where('parent_category_id', 0)
            ->whereHas('videoTemplates', function ($q) {
                $q->whereDoFrontLottie(1);
            })
            ->with([
                'videoTemplates' => function ($q) {
                    $q->whereDoFrontLottie(1);
                },
                'subcategories' => function ($q) {
                    $q->whereHas('videoTemplates', function ($t) {
                        $t->whereDoFrontLottie(1);
                    })->with([
                        'videoTemplates' => function ($t) {
                            $t->whereDoFrontLottie(1);
                        }
                    ]);
                },
                'virtualPages'
            ])
            ->orderBy('id')
            ->get();

        $catDatas = array();

        foreach ($datas as $newCat) {
            $childs = [];

            // Subcategories
            foreach ($newCat->subcategories ?? [] as $sub) {
                $vPages = [];

                foreach ($sub->virtualPages ?? [] as $virtualPage) {
                    $vPages[] = [
                        "title" => $virtualPage->category_name,
                        "link" => $virtualPage->slug,
                        "type" => "virtualPage", // optional
                    ];

                }
                $childs[] = [
                    "title" => $sub->category_name,
                    "link" => $sub->slug,
                    "childs" => $vPages,
                    "type" => "subCategory", // optional
                ];
            }

            // Virtual Pages
            foreach ($newCat->virtualPages ?? [] as $vp) {
                $childs[] = [
                    "title" => $vp->category_name, // or name field if different
                    "link" => $vp->slug,
                    "type" => "virtualPage", // optional
                ];
            }

            $catDatas[] = [
                "title" => $newCat->category_name,
                "link" => $newCat->slug,
                "type" => "categoryPage",
                "childs" => $childs,
            ];
        }

        $res['success'] = true;
        $res['datas'] = [
            'categories' => $catDatas,
        ];

        return $this->successed(datas: $res);
    }

    function keywords2(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $datas = VideoCategory::query()
            /*->whereStatus(1)*/
            ->where('parent_category_id', '>', 0)
            ->where(function ($q) {
                $q->whereHas('videoTemplates', fn($t) => $t->/*whereStatus(1)->*/ whereDoFrontLottie(1));
            })
            ->with([
                'videoTemplates' => fn($t) => $t->/*whereStatus(1)->*/ whereDoFrontLottie(1),
            ])->orderBy('id')
            ->get();

        $catDatas = array();

        foreach ($datas as $newCat) {
            $childs = [];
            foreach ($newCat->subcategories ?? [] as $sub) {
                $childs[] = [
                    "title" => $sub->category_name,
                    "link" => $sub->slug,
                ];
            }
            $catDatas[] = [
                "title" => $newCat->category_name,
                "link" => $newCat->slug,
                "childs" => $childs,
            ];
        }

        $datas = VideoVirtualCategory::all();
        $pages = array();

        foreach ($datas as $page) {
            $catDatas[] = [
                "title" => $page->category_name,
                "link" => $page->slug,
                "childs" => [],
            ];
        }

        $res['success'] = true;
        $res['datas'] = [
            'categories' => $catDatas,
            'pages' => $pages
        ];

        return $this->successed(datas: $res);
    }

    function sitemap(): array|string
    {
        return $this->failed();
    }
}

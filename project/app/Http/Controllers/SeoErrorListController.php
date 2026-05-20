<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\NewCategory;
use App\Models\PageSlugHistory;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SeoErrorListController extends AppBaseController
{

    public function index(Request $request): Factory|View|Application
    {
        $categoriesWithNoTemplate = self::getCategoryWithNoTemplate();
        $unliveCatSAndKPages = self::getSAndKPageWithUnliveCat();
        $orphanErrorPage = self::getOrphanErrorPage();
        $kAndSIndexWithNoRedirection = self::getKAndSIndexWithNoRedirection();
        $pageSlugHistoryError = self::PageSlugHistoryError();


        // Pass both to the view using compact
        return view('seo_error_list.index', compact(
            'categoriesWithNoTemplate',
            'unliveCatSAndKPages','orphanErrorPage','kAndSIndexWithNoRedirection','pageSlugHistoryError'
        ));
    }


    public static function getCategoryWithNoTemplate(): array
    {
        $result = [];

        $parentCategories = NewCategory::where('parent_category_id', 0)->get();

        foreach ($parentCategories as $parent) {
            $parentDesignCount = Design::where('new_category_id', $parent->id)->count();
            $childCatIds = NewCategory::where('parent_category_id', $parent->id)->pluck('id')->toArray();

            if (empty($childCatIds)) {
                if ($parentDesignCount === 0) {
                    $result[] = [
                        'id' => $parent->id,
                        'category_name' => $parent->category_name,
                        'type' => 'parent',
                        'reason' => 'No templates and no child categories.'
                    ];
                }
                continue;
            }


            $childDesignCount = Design::whereIn('new_category_id', $childCatIds)->count();

            if ($parentDesignCount === 0 && $childDesignCount === 0) {
                $result[] = [
                    'id' => $parent->id,
                    'category_name' => $parent->category_name,
                    'type' => 'parent',
                    'reason' => 'No templates in parent and all child categories.'
                ];
            }

            foreach ($childCatIds as $childId) {
                $child = NewCategory::find($childId);
                if (!$child)
                    continue;

                $designCount = Design::where('new_category_id', $childId)->count();

                if ($designCount === 0) {
                    $result[] = [
                        'id' => $childId,
                        'category_name' => $child->category_name,
                        'reason' => 'No templates in this category.'
                    ];
                }
            }
        }
        return $result;
    }

    public static function getSAndKPageWithUnliveCat(): array
    {
        $result = [];

        $specialPages = SpecialPage::where('status', 1)
            ->where('cat_id', '!=', 0)
            ->select('id', 'page_slug', 'cat_id')
            ->get();

        $specialKeywords = SpecialKeyword::where('status', 1)
            ->where('cat_id', '!=', 0)
            ->select('id', 'name', 'cat_id')
            ->get();


        $catIds = $specialPages->pluck('cat_id')
            ->merge($specialKeywords->pluck('cat_id'))
            ->unique()
            ->values();

        // Step 3: Load all needed categories in a single query
        $categories = NewCategory::whereIn('id', $catIds)->where('total_templates', '>', 0)->pluck('status', 'id'); // [cat_id => status]

        // Step 4: Loop SpecialPages
        foreach ($specialPages as $page) {
            $catStatus = $categories[$page->cat_id] ?? null;
            if ($catStatus !== 1) {
                $result[] = [
                    'type' => 'SpecialPage',
                    'id' => $page->id,
                    'name' => $page->page_slug,
                    'cat_id' => $page->cat_id,
                    'reason' => 'Assigned category is not live.'
                ];
            }
        }

        // Step 5: Loop SpecialKeywords
        foreach ($specialKeywords as $keyword) {
            $catStatus = $categories[$keyword->cat_id] ?? null;
            if ($catStatus !== 1) {
                $result[] = [
                    'type' => 'SpecialKeyword',
                    'id' => $keyword->id,
                    'name' => $keyword->name,
                    'cat_id' => $keyword->cat_id,
                    'reason' => 'Assigned category is not live.'
                ];
            }
        }
        return $result;
    }

    public static function getOrphanErrorPage(): array
    {
        $result = [];

        // Get orphaned special pages (cat_id = 0 or null)
        $specialPages = SpecialPage::where('status', 1)
            ->where(function ($query) {
                $query->where('cat_id', 0)->orWhereNull('cat_id');
            })
            ->where('no_index', 0)
            ->select('id', 'page_slug', 'cat_id')
            ->get();

        // Get orphaned special keywords
        $specialKeywords = SpecialKeyword::where('status', 1)
            ->where(function ($query) {
                $query->where('cat_id', 0)->orWhereNull('cat_id');
            })
            ->where('no_index', 0)
            ->select('id', 'name', 'cat_id')
            ->get();

        // Get orphaned products
        $products = Design::where('status', 1)
            ->where(function ($query) {
                $query->where('new_category_id', 0)->orWhereNull('new_category_id');
            })
            ->where('no_index', 0)
            ->select('id', 'id_name', 'new_category_id')
            ->get();

        foreach ($specialPages as $page) {
            $history = PageSlugHistory::where('old_slug', $page->page_slug)->first();

            if (!$history || HelperController::getFrontendPageUrl(2,$history->old_slug) === $history->new_slug) {
                $result[] = [
                    'type' => 'SpecialPage',
                    'id' => $page->id,
                    'name' => $page->page_slug,
                    'reason' => $history ? 'Old and new slug are the same.' : 'Slug not found in history.',
                ];
            }
        }

        foreach ($specialKeywords as $keyword) {
            $history = PageSlugHistory::where('old_slug', $keyword->name)->first();

            if (!$history || HelperController::getFrontendPageUrl(3,$history->old_slug) === $history->new_slug) {
                $result[] = [
                    'type' => 'SpecialKeyword',
                    'id' => $keyword->id,
                    'name' => $keyword->name,
                    'reason' => $history ? 'Old and new slug are the same.' : 'Slug not found in history.',
                ];
            }
        }

        foreach ($products as $product) {
            $history = PageSlugHistory::where('old_slug', $product->id_name)->first();

            if (!$history || HelperController::getFrontendPageUrl(0,$history->old_slug) === $history->new_slug) {
                $result[] = [
                    'type' => 'Design',
                    'id' => $product->id,
                    'name' => $product->id_name,
                    'reason' => $history ? 'Old and new slug are the same.' : 'Slug not found in history.',
                ];
            }
        }

        return $result;
    }

    public static function getKAndSIndexWithNoRedirection(): array
    {

        $result = [];

        $specialPages = SpecialPage::where('status', 1)
            ->where(function ($query) {
                $query->where('no_index', 1)
                    ->orWhereNotNull('canonical_link')
                    ->orWhere('canonical_link', '!=', '');
            })
            ->select('id', 'page_slug')
            ->get();

        $specialKeywords = SpecialKeyword::where('status', 1)
            ->where(function ($query) {
                $query->where('no_index', 1)
                    ->orWhereNotNull('canonical_link')
                    ->orWhere('canonical_link', '!=', '');
            })
            ->select('id', 'name')
            ->get();

        foreach ($specialPages as $page) {
            $history = PageSlugHistory::where('old_slug', $page->page_slug)->first();

            if (!$history || HelperController::getFrontendPageUrl(2,$history->old_slug) === $history->new_slug) {
                $result[] = [
                    'type' => 'SpecialPage',
                    'id' => $page->id,
                    'name' => $page->page_slug,
                    'reason' => $history ? 'Old and new slug are the same.' : 'Slug not found in history.',
                ];
            }
        }

        foreach ($specialKeywords as $keyword) {
            $history = PageSlugHistory::where('old_slug', $keyword->name)->first();

            if (!$history || HelperController::getFrontendPageUrl(3,$history->old_slug) === $history->new_slug) {
                $result[] = [
                    'type' => 'SpecialKeyword',
                    'id' => $keyword->id,
                    'name' => $keyword->name,
                    'reason' => $history ? 'Old and new slug are the same.' : 'Slug not found in history.',
                ];
            }
        }

        return $result;
    }

    public static function PageSlugHistoryError()
    {
        $result = [];

        $pageSlugHistoryDatas = PageSlugHistory::where(function ($query) {
            $query->where('type', 0)
                ->orWhere('type', 1);
        })->select('old_slug', 'type')->get();

        foreach ($pageSlugHistoryDatas as $item) {
            $oldSlug = $item->old_slug;
            $type = $item->type;

            if ($type == 0) {
                $keyword = SpecialKeyword::where('name', $oldSlug)
                    ->where('no_index', 0)
                    ->where(function ($q) {
                        $q->whereNull('canonical_link')
                            ->orWhere('canonical_link', '');
                    })
                    ->first();

                if ($keyword) {
                    $result[] = [
                        'type' => 'SpecialKeyword',
                        'id' => $keyword->id,
                        'name' => $keyword->name,
                        'reason' => 'Old slug found with index and canonical_link is null/empty.',
                    ];
                }
            }

            if ($type == 1) {
                $page = SpecialPage::where('page_slug', $oldSlug)
                    ->where('no_index', 0)
                    ->where(function ($q) {
                        $q->whereNull('canonical_link')
                            ->orWhere('canonical_link', '');
                    })
                    ->first();

                if ($page) {
                    $result[] = [
                        'type' => 'SpecialPage',
                        'id' => $page->id,
                        'name' => $page->page_slug,
                        'reason' => 'Old slug found with index and canonical_link is null/empty.',
                    ];
                }
            }
        }

        return $result;
    }

}
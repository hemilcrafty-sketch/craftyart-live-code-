<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationController extends Controller
{

    public static function buildCanonicalLink($canonicalLink, $frontendUrl, $page): string
    {
        if (!empty($canonicalLink)) {
            return $canonicalLink;
        }
        if ($page != 1) {
            return "$frontendUrl/$page";
        }
        return $frontendUrl;
    }

    public static function getPagination(LengthAwarePaginator $data, array $filter = [], ?string $pageLink = null): array
    {
        $data->withPath('');
        $currentPage = $data->currentPage();
        $keyMappings = [
            'is_premium' => 'license',
            'tags' => 'query',
        ];

        foreach ($keyMappings as $oldKey => $newKey) {
            if (array_key_exists($oldKey, $filter)) {
                $filter[$newKey] = $filter[$oldKey];
                unset($filter[$oldKey]);
            }
        }

//        $filter = collect($filter)->except('page')->toArray();

        $nextPage = $currentPage + 1;
        $previousPage = $currentPage - 1;

        $pagination['next_url'] = $data->hasMorePages() ? self::buildQueryUri($filter, $nextPage, $pageLink) : null;
        $pagination['prev_url'] = $currentPage > 1 ? self::buildQueryUri($filter, $previousPage, $pageLink) : null;

        if ($pageLink) {
            $pagination['seo_next_url'] = $data->hasMorePages() && empty($filter) ? self::buildQueryUri([], $nextPage, $pageLink) : null;
            $pagination['seo_prev_url'] = $currentPage > 1 && empty($filter) ? self::buildQueryUri([], $previousPage, $pageLink) : null;
        } else {
            $pagination['seo_next_url'] = $data->hasMorePages() && empty($filter) ? $pagination['next_url'] : null;
            $pagination['seo_prev_url'] = $currentPage > 1 && empty($filter) ? $pagination['prev_url'] : null;
        }

        $pagination['links'] = PaginationController::paginate($currentPage, $data->lastPage(), 1, $filter, $pageLink);

        $pagination['isLastPage'] = $currentPage >= $data->lastPage();
        $pagination['total'] = $data->total();
        $pagination['per_page'] = $data->perPage();
        $pagination['current_page'] = $currentPage;
        $pagination['last_page'] = $data->lastPage();
        $pagination['from'] = $data->firstItem();
        $pagination['to'] = $data->lastItem();

        return $pagination;
    }

    public static function paginate($currentPage, $totalPages, $visiblePagesAround = 1, array $filter = [], ?string $pageLink = null): array
    {
        if (is_array($visiblePagesAround)) {
            $visiblePagesAround = 1; // default value
        }

        $pages = [];

        $pages[] = [
            "page" => 1,
            "label" => 1,
            "url" => self::buildQueryUri($filter, pageLink: $pageLink),
            "active" => $currentPage == 1
        ];

        $startPage = max(2, $currentPage - $visiblePagesAround);
        $endPage = min($totalPages - 1, $currentPage + $visiblePagesAround);

        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = [
                "page" => $i,
                "label" => $i,
                "url" => self::buildQueryUri($filter, $i, $pageLink),
                "active" => $currentPage == $i
            ];
        }

        if ($totalPages > 1) {
            $pages[] = [
                "page" => $totalPages,
                "label" => $totalPages,
                "url" => self::buildQueryUri($filter, $totalPages, $pageLink),
                "active" => $currentPage == $totalPages
            ];
        }

        return $pages;
    }

    static function buildQueryUri(array $input, int $page = 1, ?string $pageLink = null): string
    {
        $query = [];

        foreach ($input as $key => $value) {
            // Handle array-like strings: "['val1','val2']"
            if (preg_match("/^\[.*\]$/", $value)) {
                $items = json_decode(str_replace("'", '"', $value), true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $query[] = urlencode($key) . '=' . urlencode($item);
                    }
                }
            } else {
                // Simple value
                $query[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        if (!$pageLink) {
            $query[] = 'page=' . $page;
            return '?' . implode('&', $query);
        }

        if ($page != 1) {
            $pageLink = $pageLink . '/' . $page;
        }

        if (count($query) !== 0) {
            $pageLink = $pageLink . '?' . implode('&', $query);
        }

        return $pageLink;
    }
}

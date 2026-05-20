<?php

namespace App\Http\Controllers\Utils;

class PaginationController2 extends Controller
{

    public static function getPagination($data): array
    {
        $data->withPath('');
        $pagination['current_page'] = $data->currentPage();
        $pagination['next_url'] = $data->nextPageUrl();
        $pagination['prev_url'] = $data->previousPageUrl();

        $pagination['links'] = PaginationController2::paginate($data->currentPage(), $data->lastPage());

        return $pagination;
    }

    public static function paginate($currentPage, $totalPages, $visiblePagesAround = 1)
    {
        $pages = [];

        // Always include the first page
        $pages[] = ["label" => 1, "url" => "?page=1", "active" => $currentPage == 1];

        // Add ellipsis if the current page is far enough from the first page
        if ($currentPage > $visiblePagesAround + 2) {
             $pages[] = ["label" => '...', "url" => null, "active" => false];
        }

        // Calculate the range of pages around the current page
        $startPage = max(2, $currentPage - $visiblePagesAround);
        $endPage = min($totalPages - 1, $currentPage + $visiblePagesAround);

        // Add the pages around the current page
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = [
                "label" => $i,
                "url" => "?page=" . $i,
                "active" => $currentPage == $i
            ];
        }

        // Add ellipsis if the current page is far enough from the last page
        if ($currentPage < $totalPages - $visiblePagesAround - 1) {
             $pages[] = ["label" => '...', "url" => null, "active" => false];
        }

        // Always include the last page
        if ($totalPages > 1) {
            $pages[] = [
                "label" => $totalPages,
                "url" => "?page=" . $totalPages,
                "active" => $currentPage == $totalPages
            ];
        }

        return $pages;
    }

}

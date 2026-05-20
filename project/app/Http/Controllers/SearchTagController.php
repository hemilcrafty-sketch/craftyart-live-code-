<?php

namespace App\Http\Controllers;

use App\Models\SearchTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SearchTagController extends AppBaseController
{

    public function show_search_tag(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $searchTagArray = $this->applyFiltersAndPagination($request, SearchTag::with('assignedSeo'), $searchableFields);
        $assignSubCat = User::where('user_type', 5)->get();

        return view('filters.search_tag', compact('searchTagArray', 'searchableFields', 'assignSubCat'));
    }


    public function submitSearchTag(Request $request): JsonResponse|bool
    {
        $user = auth()->user();
        $res = null;

        if ($request->has('id') && !empty($request->input('id'))) {
            // Edit mode
            $res = SearchTag::find($request->input('id'));

            if (!$res) {
                return response()->json([
                    'error' => 'Search Tag not found.'
                ]);
            }
        } else {
            // Create mode
            $existing = SearchTag::where("name", $request->input('name'))->first();
            if ($existing) {
                return response()->json([
                    'error' => 'Tag Already exist.'
                ]);
            }

            $res = new SearchTag;
            $res->emp_id = $user->id;

            // Only set seo_emp_id during create
        }

        // Access control (after $res is set)
        $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id, [$res->seo_emp_id ?? 0]);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $res->seo_emp_id = $request->seo_emp_id ?? $res->seo_emp_id;
        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->save();

        return response()->json([
            'success' => $request->has('id') ? 'Tag Updated successfully.' : 'Tag Added successfully.'
        ]);
    }




    public function deleteSearchTag(Request $request, SearchTag $searchTag): JsonResponse
    {
        SearchTag::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }
}

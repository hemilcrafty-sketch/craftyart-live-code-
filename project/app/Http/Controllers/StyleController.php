<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class StyleController extends AppBaseController
{

    public function show_style(Request $request, Style $style): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'id_name', "value" => 'ID Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $filteredStyles = $this->applyFiltersAndPagination($request, Style::query(), $searchableFields);

        return view('filters.styles')->with([
            'styleArray' => $filteredStyles,
            'searchableFields' => $searchableFields
        ]);
    }


    public function submitStyle(Request $request): JsonResponse|bool
    {
        $user = auth()->user();

        if ($request->has('id') && $request->id) {
            $res = Style::find($request->id);
            if (!$res) {
                return response()->json(['error' => 'Style not found.']);
            }
        } else {
            $existing = Style::where("name", $request->input('name'))->first();
            if ($existing) {
                return response()->json(['error' => 'Style already exists.']);
            }

            $res = new Style();
            $res->emp_id = $user->id;
        }
        $accessCheck = $this->isAccessByRole("seo_all", $request->id, $res->emp_id);
        // dd($request->id, $res->emp_id);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        $res->name = $request->input('name');
        $res->status = $request->input('status');
        $res->id_name = $request->input('id_name');
        $res->save();

        $msg = $request->has('id') ? 'Data updated successfully.' : 'Data added successfully.';
        return response()->json(['success' => $msg]);
    }


    public function deleteStyle(Request $request, Style $style): JsonResponse
    {
        Style::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

}


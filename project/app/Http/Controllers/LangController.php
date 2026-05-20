<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LangController extends AppBaseController
{

    public function showLang(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'Id'],
            ["id" => 'name', "value" => 'Name'],
            ["id" => 'id_name', "value" => 'ID Name'],
            ["id" => 'status', "value" => 'Status']
        ];

        $langArray = $this->applyFiltersAndPagination($request, Language::query(), $searchableFields);

        return view('filters.languages', compact('langArray', 'searchableFields'));
    }


    public function storeOrUpdateLang(Request $request): JsonResponse|string|null
    {
        $user = auth()->user();

        // Validate uniqueness for create
        if (!$request->id && Language::where("name", $request->name)->exists()) {
            return response()->json(['error' => 'Language already exists.']);
        }

        if ($request->id) {
            // Edit
            $lang = Language::find($request->id);
            if (!$lang) {
                return response()->json(['error' => 'Language not found.']);
            }


        } else {
            // Create
            $lang = new Language();
            $lang->emp_id = $user->id;
        }

        $accessCheck = $this->isAccessByRole("seo_all", $request->id, $lang->emp_id);
        if ($accessCheck) {
            return response()->json([
                'error' => $accessCheck,
            ]);
        }

        // Common fields
        $lang->name = $request->name;
        $lang->id_name = $request->id_name;
        $lang->status = $request->status;
        $lang->save();

        return response()->json([
            'success' => $request->id ? 'Language updated successfully.' : 'Language added successfully.'
        ]);
    }


    public function deleteLang(Request $request, Language $language): JsonResponse
    {
        Language::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

}


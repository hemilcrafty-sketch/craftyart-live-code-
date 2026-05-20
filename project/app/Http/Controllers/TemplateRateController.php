<?php

namespace App\Http\Controllers;

use App\Models\TemplateRate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// AppBaseController

class TemplateRateController extends AppBaseController
{


    public function index(Request $request): Factory|View|Application
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'id'],
            ["id" => 'name', "value" => 'name'],
            ["id" => 'value', "value" => 'value']
        ];

        $routeName = $request->route()->getName();
        // dd($routeName);

        $getTemplateData = $this->applyFiltersAndPagination(
            $request,
            TemplateRate::query(),
            $searchableFields,
            [],
            'asc'
        );

        
        return view('template_rate.index', compact('getTemplateData', 'searchableFields'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'value' => 'required|json',
            'type' => 'nullable|integer'
        ]);

        $slugName = Str::slug($request->name, '_');
        $type = $request->input('type', 0);

        if (!empty($request->id)) {
            $rate = TemplateRate::findOrFail($request->id);
            $rate->update([
                'value' => $request->value,
                'type' => $type
            ]);
            $message = 'Template Rate updated';
        } else {
            if (TemplateRate::where('name', $slugName)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Name already exists.'
                ]);
            }
            TemplateRate::create([
                'name' => $slugName,
                'value' => $request->value,
                'type' => $type
            ]);
            $message = 'Template Rate saved';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function edit($id): JsonResponse
    {
        $rate = TemplateRate::findOrFail($id);
        return response()->json($rate);
    }

    // public function destroy($id): JsonResponse
    // {
    //     $rate = TemplateRate::findOrFail($id);
    //     $rate->delete();

    //     return response()->json(['success' => true, 'message' => 'Template rate deleted successfully.']);
    // }
}
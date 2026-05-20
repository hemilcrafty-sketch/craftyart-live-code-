<?php

namespace App\Http\Controllers;

use App\Models\Religion;
use Exception;
use Illuminate\Http\Request;


class RelegionController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $searchableFields = [
            ['id' => 'id', 'value' => 'Id'],
            ['id' => 'religion_name', 'value' => 'Religion Name'],
            ['id' => 'id_name', 'value' => 'ID Name'],
            ['id' => 'status', 'value' => 'Status'],
        ];

        $religions = $this->applyFiltersAndPagination($request, Religion::query(), $searchableFields);

        return view('relegion.index', compact('religions', 'searchableFields'));
    }


    public function submit(Request $request)
    {
        // dd($request->all());
        try {
            // Update case
            if ($request->religion_id) {
                $religion = Religion::findOrFail($request->religion_id);

                $accessCheck = $this->isAccessByRole("seo_all", $request->religion_id, $religion->emp_id);

                if ($accessCheck) {
                    return response()->json([
                        'error' => $accessCheck,
                    ]);
                }

                $religion->update($request->only(['religion_name', 'id_name', 'status']));

                return response()->json([
                    'status' => true,
                    'success' => 'Religion has been updated successfully.',
                ]);
            }
            // Create case
            else {
                Religion::create([
                    'religion_name' => $request['religion_name'],
                    'id_name' => $request['id_name'],
                    'status' => $request['status'],
                    'emp_id' => auth()->id(),
                ]);

                return response()->json([
                    'status' => true,
                    'success' => 'Religion has been added successfully.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }





    /** Remove the specified resource from storage.*/
    public function destroy(Religion $religion)
    {
        try {
            $religion->delete();
            return response()->json([
                'status' => true,
                'success' => "Religion has been deleted successfully.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


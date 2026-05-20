<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\InAppMessage;
use App\Models\InAppType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InAppMessageController extends AppBaseController
{

    public function index()
    {

    }

    public function show()
    {
        $data['type'] = InAppType::all();
        $data['list'] = InAppMessage::all();
        return view('in_app_message')->with('datas', $data);
    }

    public function add(Request $request)
    {
        $this->validate($request, ['image' => 'required|image|mimes:jpg,png,gif|max:2048']);

        $res = new InAppMessage();

        $date_range = $request->input('date_range');
        if ($date_range != null) {
            $date_range = str_replace(' ', '', $date_range);
            $split_date = explode("-", $date_range);
            if (sizeof($split_date) < 2) {
                return response()->json([
                    'error' => 'Please select end date.'
                ]);
            }
            $res->start_date = $split_date[0];
            $res->end_date = $split_date[1];
        }

        $image = $request->file('image');
        $bytes = random_bytes(20);
        $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
        StorageUtils::storeAs($image, 'uploadedFiles/message_file', $new_name);
        $res->image = 'uploadedFiles/message_file/' . $new_name;

        $res->open_type = $request->input('open_type');
        $res->can_cancle = $request->input('can_cancle');
        $res->is_banner = $request->input('is_banner');
        $res->keyword = $request->input('keyword');
        $res->link = $request->input('link');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function update(Request $request)
    {

        $res = InAppMessage::find($request->id);

        $date_range = $request->input('date_range');
        if ($date_range != null) {
            $date_range = str_replace(' ', '', $date_range);
            $split_date = explode("-", $date_range);
            if (sizeof($split_date) < 2) {
                return response()->json([
                    'error' => 'Please select end date.'
                ]);
            }
            $res->start_date = $split_date[0];
            $res->end_date = $split_date[1];
        } else {
            $res->start_date = null;
            $res->end_date = null;
        }

        $image = $request->file('image');
        if ($image != null) {
            $this->deleteImage($res->image);
            $bytes = random_bytes(20);
            $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            StorageUtils::storeAs($image, 'uploadedFiles/message_file', $new_name);
            $res->image = 'uploadedFiles/message_file/' . $new_name;
        }
        $res->keyword = null;
        $res->link = null;
        $open_type = $request->input('open_type');
        $res->open_type = $open_type;
        if ($open_type == 2) {
            $res->keyword = $request->input('keyword');
        }
        if ($open_type == 3 || $open_type == 4) {
            $res->link = $request->input('link');
        }
        $res->can_cancle = $request->input('can_cancle');
        $res->is_banner = $request->input('is_banner');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function delete(Request $request)
    {
        $res = InAppMessage::find($request->id);
        $this->deleteImage($res->image);
        InAppMessage::destroy(array('id', $request->id));
        return response()->json([
            'success' => 'Deleted successfully.'
        ]);
    }

    public function deleteImage($image)
    {
        try {
            unlink(storage_path("app/public/" . $image));
        } catch (\Exception $e) {
        }
    }

}

<?php

namespace App\Http\Controllers\CustomOrder;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController;
use App\Models\CustomOrder\OrderTable;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use DB;
use Carbon\Carbon;
use Auth;
use Spatie\Async\Pool;

class CustomOrderController extends AppBaseController
{

    public function index()
    {

    }

    public function show(Request $request)
    {

        $paginate_count = 20;

        $temp_data = [];
        $temp_data_count = 0;
        $sorting_by = $request->has('sorting_by') ? $request->get('sorting_by') : '-1';

        $condition = '=';
        if($sorting_by == '-1') {
            $condition = '!=';
        }

        if($request->has('query')) {
            $temp_data_count = OrderTable::where('status', $condition, $sorting_by)->orderBy('id', 'desc')->count();
            $temp_data = OrderTable::where('status', $condition, $sorting_by)->orderBy('id', 'desc')->paginate($paginate_count);
        } else {
            $temp_data_count = OrderTable::where('status', $condition, $sorting_by)->orderBy('id', 'desc')->count();
            $temp_data = OrderTable::where('status', $condition, $sorting_by)->orderBy('id', 'desc')->paginate($paginate_count);
        }

        $total = $temp_data_count;
        $count = $total;
        $total_diff = $paginate_count - 1;
        $diff = $paginate_count - 1;

        if($total < $paginate_count) {
            $diff = ($total - 1);
        }

        if($request->has('page')) {
            $count = $request->input('page') * $paginate_count;
            if($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            }
        } else {
           $count = $paginate_count;
            if($count > $total) {
                $diff = $total_diff - ($count - $total);
                $count = $total;
            } 
        }

        if($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing ".($count - $diff). "-".$count. " of ".$total." entries";
        }


        $data['sortingFields'] = array(-1 => "All", 0 => "Payment Pending", 1 => "Panding Orders", 2 => "Completed orders", 3 => "Canceled orders", 4 => "Refund In Progress", 5 => "Refunded");
        $data['sortingField'] = $sorting_by;
        $data['count_str'] = $ccc;
        $data['item'] = $temp_data;

        return view('custom_order/show_order')->with('orderArray', $data);
    }

}

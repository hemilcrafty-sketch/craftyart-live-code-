<?php

namespace App\Http\Controllers;

use App\Models\BusinessSupportPurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusinessSupportController extends AppBaseController
{
  public function showBusinessSupport(Request $request)
  {
    $temp_data = BusinessSupportPurchaseHistory::query();

    if ($request->has('query')) {
      $query = $request->input('query');
      $temp_data = $temp_data->where(function ($queryBuilder) use ($query) {
        $queryBuilder->where('product_id', 'LIKE', '%' . $query . '%')
          ->orWhere('user_id', 'LIKE', '%' . $query . '%')
          ->orWhere('payment_id', 'LIKE', '%' . $query . '%')
          ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
          ->orWhere('amount', 'LIKE', '%' . $query . '%')
          ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
          ->orWhere('from_where', 'LIKE', '%' . $query . '%');
      });
    }

    $temp_data = $temp_data->orderBy('id', 'desc')->paginate(10);
    $temp_data_count = $temp_data->total();
    $total = $temp_data_count;
    $count = $total;
    $diff = 9;

    if ($total < 10) {
      $diff = ($total - 1);
    }

    if ($request->has('page')) {
      $count = $request->input('page') * 10;
      if ($count > $total) {
        $diff = 9 - ($count - $total);
        $count = $total;
      }
    } else {
      $count = 10;
      if ($count > $total) {
        $diff = 9 - ($count - $total);
        $count = $total;
      }
    }

    if ($total == 0) {
      $ccc = "Showing 0-0 of 0 entries";
    } else {
      $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
    }

    $data['count_str'] = $ccc;
    $data['transcationArray'] = $temp_data;

    return view('subscription/show_ai_credit_purchases')->with('datas', $data);
  }

  /**
   * Update followup description for business support purchase
   */
  public function updateFollowup(Request $request): JsonResponse
  {
    $purchase = BusinessSupportPurchaseHistory::findOrFail($request->id);

    $purchase->description = $request->description ?? '';
    $purchase->save();

    return response()->json([
      'success' => true,
      'message' => 'Followup updated successfully'
    ]);
  }
}


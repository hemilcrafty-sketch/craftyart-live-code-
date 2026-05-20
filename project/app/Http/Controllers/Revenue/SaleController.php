<?php

namespace App\Http\Controllers\Revenue;

use App\Http\Controllers\AppBaseController;
use App\Models\Revenue\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SaleController extends AppBaseController
{
  /**
   * Display a listing of sales
   */
  public function index(Request $request)
  {
    $sales = Sale::query();

    // Search functionality
    if ($request->has('query')) {
      $query = $request->input('query');
      $sales = $sales->where(function ($queryBuilder) use ($query) {
        $queryBuilder->where('user_name', 'LIKE', '%' . $query . '%')
          ->orWhere('email', 'LIKE', '%' . $query . '%')
          ->orWhere('contact_no', 'LIKE', '%' . $query . '%')
          ->orWhere('reference_id', 'LIKE', '%' . $query . '%')
          ->orWhere('payment_link_id', 'LIKE', '%' . $query . '%')
          ->orWhere('phonepe_order_id', 'LIKE', '%' . $query . '%');
      });
    }

    // Filter by status
    if ($request->has('status') && $request->status !== '') {
      $sales = $sales->where('status', $request->status);
    }

    // Filter by payment method
    if ($request->has('payment_method') && $request->payment_method !== '') {
      $sales = $sales->where('payment_method', $request->payment_method);
    }

    // Filter by date range
    if ($request->has('from_date') && $request->from_date !== '') {
      $sales = $sales->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->has('to_date') && $request->to_date !== '') {
      $sales = $sales->whereDate('created_at', '<=', $request->to_date);
    }

    $sales = $sales->orderBy('id', 'desc')->paginate(10);
    $total = $sales->total();
    $count = $sales->count();
    $currentPage = $sales->currentPage();
    $perPage = $sales->perPage();

    $from = ($currentPage - 1) * $perPage + 1;
    $to = min($currentPage * $perPage, $total);

    $countStr = $total == 0 ? "Showing 0-0 of 0 entries" : "Showing {$from}-{$to} of {$total} entries";

    $data = [
      'count_str' => $countStr,
      'sales' => $sales
    ];

    return view('revenue.sales.index')->with('datas', $data);
  }

  /**
   * Display the specified sale
   */
  public function show($id)
  {
    $sale = Sale::with(['salesPerson', 'order'])->findOrFail($id);
    return view('revenue.sales.show')->with('sale', $sale);
  }

  /**
   * Get sales statistics
   */
  public function statistics(): JsonResponse
  {
    $totalSales = Sale::count();
    $totalRevenue = Sale::where('status', 'success')->sum('amount');
    $pendingSales = Sale::where('status', 'pending')->count();
    $failedSales = Sale::where('status', 'failed')->count();

    return response()->json([
      'total_sales' => $totalSales,
      'total_revenue' => $totalRevenue,
      'pending_sales' => $pendingSales,
      'failed_sales' => $failedSales
    ]);
  }
}

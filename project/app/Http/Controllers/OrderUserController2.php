<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class OrderUserController extends AppBaseController
{
    const FOLLOWUP_LABELS = [
        'interested' => 'Interested',
        'highly_interested' => 'Highly Interested',
        'need_time' => 'Need Time',
        'call_cut' => 'Call Cut',
        'not_reachable' => 'Not Reachable',
        'switched_off' => 'Switched Off',
        'not_interested' => 'Not Interested',
        'personal_use_only' => 'Personal Use Only',
        'call_me_after_sometime' => 'Call Me After Sometime',
        'call_not_receive' => 'Call Not Receive',
        'active_plan' => 'Active Plan',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
    ];



    public function index(Request $request)
    {
        $searchTerm = $request->get('search');
        $query = Order::query()
            ->with(['user'])
            ->where('is_deleted', 0)
            ->whereIn('status', ['failed', 'pending']);


        // changes Distribute lead to Every Sales User
        $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);

        if ($isSalesEmployee) {

            $userId = auth()->user()->id;

            $query->where(function ($subQuery) use ($userId) {
                $subQuery->whereNull('emp_id')->orWhere('emp_id',0)->orWhere('emp_id',$userId);
            });
            
            
            // $salesEmployeeIds = User::whereUserType(auth()->user()->user_type)
            //     ->whereStatus(1)
            //     ->orderBy('id')
            //     ->pluck('id')
            //     ->toArray();

            // $totalSalesEmployees = count($salesEmployeeIds);
            // $employeeIndex = array_search($userId, $salesEmployeeIds);

            // if ($employeeIndex !== false && $totalSalesEmployees > 0) {

            //     $query->where(function ($q) use (
            //         $userId,
            //         $employeeIndex,
            //         $totalSalesEmployees
            //     ) {

            //         /** 1️⃣ OWN LEADS */
            //         $q->where('orders.emp_id', $userId);

            //         /** 2️⃣ UNASSIGNED DISTRIBUTED GROUPS */
            //         $q->orWhereIn('orders.id', function ($sub) use (
            //             $employeeIndex,
            //             $totalSalesEmployees
            //         ) {

            //             $sub->select('o.id')
            //                 ->from('orders as o')
            //                 ->join(DB::raw("
            //             (
            //                 SELECT 
            //                     user_id,
            //                     plan_id,
            //                     ROW_NUMBER() OVER (
            //                         ORDER BY user_id
            //                     ) - 1 AS grp_index
            //                 FROM orders
            //                 WHERE is_deleted = 0
            //                 AND emp_id = 0
            //                 AND status IN ('failed','pending')
            //                 GROUP BY user_id, plan_id
            //             ) g
            //         "), function ($join) {
            //                     $join->on('o.user_id', '=', 'g.user_id');
            //                 })
            //                 ->whereRaw('g.grp_index % ? = ?', [
            //                     $totalSalesEmployees,
            //                     $employeeIndex
            //                 ]);
            //         });
            //     });
            // }
        }

        // Filters
        $filterType = $request->get('filter_type', 'all');
        $statusFilter = $request->get('status_filter', 'all');
        $typeFilter = $request->get('type_filter');
        $amountSort = $request->get('amount_sort');
        $whatsappFilter = $request->get('whatsapp_filter');
        $emailFilter = $request->get('email_filter');
        $fromWhere = $request->get('from_where');
        $followupFilter = $request->get('followup_filter');
        $followupLabelFilter = $request->get('followup_label_filter'); // New filter

        if ($filterType === 'remove_duplicate') {
            $query->whereIn('id', function ($sub) {
                $sub->select(DB::raw('MAX(id)'))
                    ->from('orders')
                    ->whereIn('status', ['failed', 'pending'])
                    ->groupBy('user_id', 'plan_id');
            });
        }

        if (in_array($statusFilter, ['pending', 'failed'])) {
            $query->where('status', $statusFilter);
        }

        if (in_array($typeFilter, ['old_sub', 'new_sub', 'template', 'video', 'caricature'])) {
            $query->where('type', $typeFilter);
        }

        if (!empty($fromWhere)) {
            $query->where(function ($q) use ($fromWhere) {

                if ($fromWhere === 'meta_google') {
                    $q->whereNotNull('fbc')
                        ->where(function ($qq) {
                            $qq->whereNotNull('gclid')
                                ->orWhereNotNull('wbraid')
                                ->orWhereNotNull('gbraid');
                        });
                }

                elseif ($fromWhere === 'meta') {
                    $q->whereNotNull('fbc')
                        ->whereNull('gclid')
                        ->whereNull('wbraid')
                        ->whereNull('gbraid');
                }

                elseif ($fromWhere === 'google') {
                    $q->whereNull('fbc')
                        ->where(function ($qq) {
                            $qq->whereNotNull('gclid')
                                ->orWhereNotNull('wbraid')
                                ->orWhereNotNull('gbraid');
                        });
                }

                elseif ($fromWhere === 'seo') {
                    $q->whereNull('fbc')
                        ->whereNull('gclid')
                        ->whereNull('wbraid')
                        ->whereNull('gbraid');
                }

            });
        }

        // New followup label filter
        if (!empty($followupLabelFilter) && array_key_exists($followupLabelFilter, self::FOLLOWUP_LABELS)) {
            $query->where('followup_label', $followupLabelFilter);
        }

        $filters = [
            'whatsapp_template_count' => $whatsappFilter,
            'email_template_count' => $emailFilter,
            'followup_call' => $followupFilter,
        ];

        foreach ($filters as $column => $filter) {
            match ($filter) {
                'sent' => $query->where("$column", '>', 0),
                'not_sent' => $query->where("$column", '<=', 0),
                'called' => $query->where("$column", 1),
                'not_called' => $query->where("$column", 0),
                default => null,
            };
        }

        // changes Remove Unnecessary Search Filter
        // Search
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Sorting
        $sortBy = in_array($amountSort, ['asc', 'desc']) ? 'amount' : 'id';
        $sortOrder = $amountSort ?: 'desc';
        $request->merge(['sort_by' => $sortBy, 'sort_order' => $sortOrder]);

        $searchableFields = [];
        $OrderUsers = $this->applyFiltersAndPagination($request, $query, $searchableFields);

        $datas = [];
        $datas['packageArray'] = Subscription::all();

        return view('order_user.index', compact(
            'OrderUsers',
            'searchableFields',
            'filterType',
            'statusFilter',
            'typeFilter',
            'amountSort',
            'whatsappFilter',
            'emailFilter',
            'fromWhere',
            'followupFilter',
            'followupLabelFilter', // Pass to view
            'datas',
            'searchTerm'
        ))->with('followupLabels', self::FOLLOWUP_LABELS); // Pass labels to view
    }

    // public function index(Request $request)
    // {
    //     $searchTerm = $request->get('search');
    //     $query = Order::query()
    //         ->with(['user'])
    //         ->where('is_deleted', 0)
    //         ->whereIn('status', ['failed', 'pending']);

    //     // $query->where(function ($q) {
    //     //         $q->where('orders.type', 'caricature')
    //     //             ->orWhereNotIn('orders.user_id', function ($sub) {
    //     //                 $sub->select('user_id')
    //     //                     ->from('transaction_logs')
    //     //                     ->where('expired_at', '>', now());
    //     //             });
    //     //     });    


    //     // changes Distribute lead to Every Sales User
    //     $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
    //     if ($isSalesEmployee) {
    //         $salesEmployees = User::whereUserType(auth()->user()->user_type)->get();
    //         $salesEmployeeIds = $salesEmployees->pluck('id')->toArray();

    //         if (!empty($salesEmployeeIds)) {
    //             $userId = auth()->user()->id;
    //             $employeeIndex = array_search($userId, $salesEmployeeIds);

    //             if ($employeeIndex !== false) {
    //                 $totalSalesEmployees = count($salesEmployeeIds);

    //                 $query->where(function ($q) use ($userId, $employeeIndex, $totalSalesEmployees) {
    //                     $q->where('emp_id', $userId)
    //                         ->orWhere(function ($subQuery) use ($employeeIndex, $totalSalesEmployees) {
    //                             $subQuery->where('emp_id', 0)
    //                                 ->whereRaw("(orders.user_id % ?) = ?", [
    //                                     $totalSalesEmployees,
    //                                     $employeeIndex
    //                                 ]);
    //                         });
    //                 });
    //             }
    //         }
    //     }


    //     // Filters
    //     $filterType = $request->get('filter_type', 'all');
    //     $statusFilter = $request->get('status_filter', 'all');
    //     $typeFilter = $request->get('type_filter');
    //     $amountSort = $request->get('amount_sort');
    //     $whatsappFilter = $request->get('whatsapp_filter');
    //     $emailFilter = $request->get('email_filter');
    //     $followupFilter = $request->get('followup_filter');
    //     $followupLabelFilter = $request->get('followup_label_filter'); // New filter

    //     if ($filterType === 'remove_duplicate') {
    //         $query->whereIn('id', function ($sub) {
    //             $sub->select(DB::raw('MAX(id)'))
    //                 ->from('orders')
    //                 ->whereIn('status', ['failed', 'pending'])
    //                 ->groupBy('user_id', 'plan_id');
    //         });
    //     }

    //     if (in_array($statusFilter, ['pending', 'failed'])) {
    //         $query->where('status', $statusFilter);
    //     }

    //     if (in_array($typeFilter, ['old_sub', 'new_sub', 'template', 'video', 'caricature'])) {
    //         $query->where('type', $typeFilter);
    //     }

    //     // New followup label filter
    //     if (!empty($followupLabelFilter) && array_key_exists($followupLabelFilter, self::FOLLOWUP_LABELS)) {
    //         $query->where('followup_label', $followupLabelFilter);
    //     }

    //     $filters = [
    //         'whatsapp_template_count' => $whatsappFilter,
    //         'email_template_count' => $emailFilter,
    //         'followup_call' => $followupFilter,
    //     ];

    //     foreach ($filters as $column => $filter) {
    //         match ($filter) {
    //             'sent' => $query->where("$column", '>', 0),
    //             'not_sent' => $query->where("$column", '<=', 0),
    //             'called' => $query->where("$column", 1),
    //             'not_called' => $query->where("$column", 0),
    //             default => null,
    //         };
    //     }

    //     // changes Remove Unnecessary Search Filter
    //     // Search
    //     if (!empty($searchTerm)) {
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->orWhereHas('user', function ($uq) use ($searchTerm) {
    //                     $uq->where('name', 'LIKE', "%{$searchTerm}%")
    //                         ->orWhere('email', 'LIKE', "%{$searchTerm}%")
    //                         ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%");
    //                 });
    //         });
    //     }

    //     // Sorting
    //     $sortBy = in_array($amountSort, ['asc', 'desc']) ? 'amount' : 'id';
    //     $sortOrder = $amountSort ?: 'desc';
    //     $request->merge(['sort_by' => $sortBy, 'sort_order' => $sortOrder]);

    //     $searchableFields = [];
    //     $OrderUsers = $this->applyFiltersAndPagination($request, $query, $searchableFields);

    //     $datas = [];
    //     $datas['packageArray'] = Subscription::all();

    //     return view('order_user.index', compact(
    //         'OrderUsers',
    //         'searchableFields',
    //         'filterType',
    //         'statusFilter',
    //         'typeFilter',
    //         'amountSort',
    //         'whatsappFilter',
    //         'emailFilter',
    //         'followupFilter',
    //         'followupLabelFilter', // Pass to view
    //         'datas',
    //         'searchTerm'
    //     ))->with('followupLabels', self::FOLLOWUP_LABELS); // Pass labels to view
    // }

    public function followupUpdate(Request $request): JsonResponse
    {
        $orderUser = Order::whereId($request->id)->first();


        if ($request->has('followup_call') && $request->followup_call == 0) {
            $orderUser->followup_call = 0;
            $orderUser->followup_note = null;
            $orderUser->followup_label = null;
        } else {
            $orderUser->followup_call = 1;
            $orderUser->followup_note = $request->followup_note ?? '';
            $orderUser->followup_label = $request->followup_label;
        }

        $orderUser->emp_id = auth()->user()->id;

        $orderUser->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }
}
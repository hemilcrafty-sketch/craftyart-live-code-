<?php

namespace App\Http\Controllers;

use App\Models\ManageSubscription;
use App\Models\Size;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\UserActivity;
use App\Models\Draft;
use App\Models\ExportTable;
use App\Models\Subscription;
use App\Models\Pricing\SubPlan;
use App\Models\Pricing\OfferPackage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Revenue\MasterPurchaseHistory;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    }

    public function show(Request $request)
    {
        $type = $request->input('type', 'all'); // default to 'all'

        if ($type === 'active') {
            $resultData = $this->allActiveSubscriber($request);
        } elseif ($type === 'expired') {
            $resultData = $this->allExpiredSubscriber($request);
        } elseif ($type === 'upcomming') {
            $resultData = $this->allUpcommingSubscriber($request);
        } else {
            $resultData = $this->allUser($request);
        }

        $userArray = $resultData['userArray'] ?? [];
        $userCount = $resultData['userCount'] ?? [];

        $activeSubscriberCount = HelperController::getAllActiveSubscribers($request);
        $expiredSubscriberCount = HelperController::getAllExpiredSubscribers($request);
        $upcommingExpiredSubscriberCount = HelperController::getAllUpCommingExpiredSubscribers($request);

        return view('users/show_users', compact(
            'userArray',
            'userCount',
            'activeSubscriberCount',
            'expiredSubscriberCount',
            'upcommingExpiredSubscriberCount',
            'type' // 🔥 include this
        ));
    }


    public function allUpcommingSubscriber($request)
    {
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);

        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function backupallUpcommingSubscriber($request)
    {
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);
        $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        $temp_data = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })
            ->orderBy('user_data.created_at', 'desc')
            ->paginate(10);

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
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function allExpiredSubscriber($request)
    {
        $currentDate = Carbon::now();
        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '<', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function backupallExpiredSubscriber($request)
    {
        if ($request->has('query')) {
            $currentDate = Carbon::now();
            $currentDate = now();
            $searchQuery = $request->input('query');
            $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->where(function ($query) use ($searchQuery) {
                    $query->orWhere('user_data.name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.country_code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.email', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.login_type', 'LIKE', '%' . $searchQuery . '%');
                })->orderBy('user_data.created_at', 'desc')
                ->count();


            $temp_data = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->where(function ($query) use ($searchQuery) {
                    $query->orWhere('user_data.name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.country_code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.email', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.login_type', 'LIKE', '%' . $searchQuery . '%');
                })
                ->orderBy('user_data.created_at', 'desc')
                ->paginate(10);



        } else {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })->orderBy('user_data.created_at', 'desc')->count();



            $temp_data = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->orderBy('user_data.created_at', 'desc')
                ->paginate(10);


        }
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
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function allActiveSubscriber(Request $request)
    {
        $currentDate = Carbon::now();
        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '>', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];

    }
    public function allActiveSubscriberBackup($request)
    {
        if ($request->has('query')) {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->where(function ($query) use ($request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%');
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])
                ->count();

            $temp_data = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->where(function ($query) use ($request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%');
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])
                ->paginate(10);
        } else {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])->count();

            $temp_data = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })->orderBy('user_data.created_at', 'desc')->paginate(10);


        }
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
        $data['users'] = $temp_data;
        $userCount = UserData::count();
        $userArray = $data;

        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function allUser($request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $per_page = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::query();

        if ($query) {
            $queryBuilder->where('uid', 'LIKE', '%' . $query . '%')
                ->orWhere('name', 'LIKE', '%' . $query . '%')
                ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                ->orWhere('number', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->orWhere('login_type', 'LIKE', '%' . $query . '%');
        }

        $temp_data_count = $queryBuilder->count();
        $temp_data = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($per_page);

        $total = $temp_data_count;
        $count = min($total, $per_page);
        $start = ($request->input('page', 1) - 1) * $per_page + 1;
        $end = min($total, $start + $per_page - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function backupAllUser($request)
    {
        if ($request->has('query')) {
            $temp_data_count = UserData::where('uid', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('created_at', 'desc')
                ->count();

            $temp_data = UserData::where('uid', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = UserData::orderBy('created_at', 'desc')->count();
            $temp_data = UserData::orderBy('created_at', 'desc')->paginate(10);
        }
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
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function user_detail(UserData $userData, $id)
    {

            $user = UserData::where('uid', $id)->orWhere('email', $id)->first();
            if (!$user) {
                $users = UserData::select('email')->where('contact_no', $id)->get();
                return $users;
            }
            $purchase = MasterPurchaseHistory::whereUserId($id)->whereNotNull('contact_no')->first();
            $datas['user'] = [
                'name' => $user->name,
                'email' => $user->email,
                'contact_no' => $user->contact_no ?? $purchase?->contact_no,
                'profile_pic' => $user->photo_uri
            ];
            $datas['userAct'] = UserActivity::where('id', $user->id)->get();

            $multiData = array();
            $transData = MasterPurchaseHistory::whereUserId($user->uid)->orderBy('id', 'DESC')->get();

            if ($transData != null && $transData->count() != 0) {
                foreach ($transData as $transLog) {
                    $subRow = null;
                    $isSub = false;
                    if ($transLog->product_type == "old_sub") {
                        $isSub = true;
                        $subRow = Subscription::find($transLog->product_id);
                    } else if ($transLog->product_type == "new_sub") {
                        $isSub = true;
                        $subRow = SubPlan::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                // $query->where('id', $transLog->product_id)
                                    // ->orWhere('string_id', $transLog->product_id);
                                $query->where('string_id', $transLog->product_id);
                            })->first();
                    } else if ($transLog->product_type == "offer_sub") {
                        $isSub = true;
                        $subRow = OfferPackage::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                // $query->where('id', $transLog->product_id)
                                    // ->orWhere('string_id', $transLog->product_id);
                                $query->where('string_id', $transLog->product_id);
                            })->first();
                    }

                    if ($isSub && !$subRow) continue;

                    if ($subRow) {
                        if ($transLog->currency_code == 'Trial') {
                            $amount = "Trial";
                        } else {
                            $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                            $amount = $currency_code . $transLog->paid_amount;
                        }

                        $purchaseDate = $transLog->created_at;
                        $billingDate = Carbon::parse($transLog->expired_at);
                        $days = $purchaseDate->diffInDays($billingDate, false);

                        $package_name = $subRow->package_name;
                        if ($subRow->plan) {
                            $package_name = "$package_name ({$subRow->plan->name})";
                        }
                        $multiData[] = array(
                            'id' => $transLog->id,
                            'package_name' => $package_name,
                            'subscription_id' => $transLog->subscription_id ?? '--',
                            'transaction_id' => $transLog->transaction_id,
                            'recurred' => $transLog->is_e_mandate == 1 ? 'True' : 'False',
                            'amount' => $amount,
                            'paid_amount' => $transLog->paid_amount,
                            'method' => $transLog->payment_method,
                            'purchase_date' => $purchaseDate->format('d/m/Y H:i:s'),
                            'billing_date' => $billingDate->format('d/m/Y H:i:s'),
                            'validity' => $days . " Days",
                            'status' => self::checkSubsStatus($billingDate->isFuture() ? 1 : 0, $transLog->payment_status),
                            'color' => self::getSubsColor($billingDate->isFuture() ? 1 : 0, $transLog->payment_status),
                            'type' => $transLog->product_type,
                            'currency_code' => $transLog->currency_code,
                            'show_refund_btn' => $transLog->payment_status !== 'refunded'
                        );
                    }

                }
            }


            $datas['subsHistory'] = $multiData;

            $drafts = [];
            $draftDatas = Draft::with(['template'])->whereUserId($user->uid)->orderBy('id', 'DESC')->get();
            foreach ($draftDatas as $draft) {
                if ($draft->template) {
                    $thumb = HelperController::$mediaUrl . $draft->template->post_thumb;
                } else {
                    $thumbs = $draft->thumbs;
                    $thumb = empty($thumbs) ? null : $thumbs[0];
                }
                $drafts[] = [
                    "id" => $draft->string_id,
                    "thumb" => $thumb,
                    "created_at" => $draft->created_at->format('d/m/Y H:i:s'),
                ];
            }

            $exports = [];
            $exportDatas = ExportTable::with(['draft'])->whereUid($user->uid)->orderBy('id', 'DESC')->get();
            foreach ($exportDatas as $export) {
                if ($export->draft) {
                    if ($export->draft->template) $thumb = HelperController::$mediaUrl . $export->draft->template->post_thumb;
                    else {
                        $thumbs = $export->draft->thumbs;
                        $thumb = empty($thumbs) ? null : $thumbs[0];
                    }
                } else {
                    $thumb = null;
                }
                $exports[] = [
                    "id" => $export->id,
                    "draft_id" => $export->path,
                    "thumb" => $thumb,
                    "created_at" => $export->created_at->format('d/m/Y H:i:s'),
                ];
            }

            $datas['drafts'] = $drafts;
            $datas['export'] = $exports;

            return view('users/user_detail')->with('userData', $datas);
    }

    public function user_detail2(UserData $userData, $id)
    {

            $user = UserData::where('uid', $id)->orWhere('email', $id)->first();
            if (!$user) abort(404);
            $purchase = MasterPurchaseHistory::whereUserId($id)->whereNotNull('contact_no')->first();
            $datas['user'] = [
                'name' => $user->name,
                'email' => $user->email,
                'contact_no' => $user->contact_no ?? $purchase?->contact_no,
                'profile_pic' => $user->photo_uri
            ];
            $datas['userAct'] = UserActivity::where('id', $user->id)->get();

            $multiData = array();
            $transData = TransactionLog::whereUserId($user->uid)->orderBy('id', 'DESC')->get();
            if ($transData != null && $transData->count() != 0) {
                foreach ($transData as $transLog) {
                    $subRow = null;
                    if ($transLog->type == 0) $subRow = Subscription::find($transLog->plan_id);
                    else if ($transLog->type == 1) {
                        $subRow = SubPlan::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                $query->where('id', $transLog->plan_id)
                                    ->orWhere('string_id', $transLog->plan_id);
                            })->first();
                    } else if ($transLog->type == 2) {
                        $subRow = OfferPackage::with(['plan'])
                            ->where(function ($query) use ($transLog) {
                                $query->where('id', $transLog->plan_id)
                                    ->orWhere('string_id', $transLog->plan_id);
                            })->first();
                    }

                    if (!$subRow) continue;
                    if ($transLog->type == 1 && !$subRow->plan) continue;

                    if ($transLog->currency_code == 'Trial') {
                        $amount = "Trial";
                    } else {
                        $currency_code = $transLog->currency_code === "INR" ? "₹" : "$";
                        $amount = $currency_code . $transLog->paid_amount;
                    }

                    $purchaseDate = $transLog->created_at;
                    $billingDate = Carbon::parse($transLog->expired_at);
                    $days = $purchaseDate->diffInDays($billingDate, false);
                    $multiData[] = array(
                        'package_name' => $transLog->type == 0 ? $subRow->package_name : $subRow->plan->name,
                        'subscription_id' => $transLog->subscription_id ?? '--',
                        'transaction_id' => $transLog->transaction_id,
                        'recurred' => $transLog->is_e_mandate == 1 ? 'True' : 'False',
                        'amount' => $amount,
                        'method' => $transLog->payment_method,
                        'purchase_date' => $purchaseDate->format('d/m/Y H:i:s'),
                        'billing_date' => $billingDate->format('d/m/Y H:i:s'),
                        'validity' => $days . " Days",
                        'status' => HelperController::checkSubsStatus($billingDate->isFuture() ? 1 : 0),
                        'color' => HelperController::getSubsColor($billingDate->isFuture() ? 1 : 0)
                    );
                }
            }

            $datas['subsHistory'] = $multiData;

            $drafts = [];
            $draftDatas = Draft::with(['template'])->whereUserId($user->uid)->orderBy('id', 'DESC')->get();
            foreach ($draftDatas as $draft) {
                if ($draft->template) {
                    $thumb = HelperController::$mediaUrl . $draft->template->post_thumb;
                } else {
                    $thumbs = $draft->thumbs;
                    $thumb = empty($thumbs) ? null : $thumbs[0];
                }
                $drafts[] = [
                    "id" => $draft->string_id,
                    "thumb" => $thumb,
                    "created_at" => $draft->created_at->format('d/m/Y H:i:s'),
                ];
            }

            $exports = [];
            $exportDatas = ExportTable::with(['draft'])->whereUid($user->uid)->orderBy('id', 'DESC')->get();
            foreach ($exportDatas as $export) {
                if ($export->draft) {
                    if ($export->draft->template) $thumb = HelperController::$mediaUrl . $export->draft->template->post_thumb;
                    else {
                        $thumbs = $export->draft->thumbs;
                        $thumb = empty($thumbs) ? null : $thumbs[0];
                    }
                } else {
                    $thumb = null;
                }
                $exports[] = [
                    "id" => $export->id,
                    "draft_id" => $export->path,
                    "thumb" => $thumb,
                    "created_at" => $export->created_at->format('d/m/Y H:i:s'),
                ];
            }

            $datas['drafts'] = $drafts;
            $datas['export'] = $exports;

            return view('users/user_detail')->with('userData', $datas);
    }


    /* Export */

    public function export(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $queryBuilder = UserData::query();

        if ($query) {
            $queryBuilder->where('uid', 'LIKE', '%' . $query . '%')
                ->orWhere('name', 'LIKE', '%' . $query . '%')
                ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                ->orWhere('number', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->orWhere('login_type', 'LIKE', '%' . $query . '%');
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        $fileName = 'users.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $columns = ['ID', 'UID', 'Photo URI', 'Name', 'Email', 'Number', 'Login Type', 'Is Premium', 'Created At'];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->uid,
                    $user->photo_uri,
                    $user->name,
                    $user->email,
                    $user->number,
                    $user->login_type,
                    $user->is_premium == '1' ? 'TRUE' : 'FALSE',
                    $user->created_at,
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function exportActiveSubscribers(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $currentDate = Carbon::now();

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '>', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        // Export logic
        $fileName = 'active_subscribers.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['ID', 'UID', 'Name', 'Country Code', 'Number', 'Email', 'Login Type', 'Created At']);

            // Write CSV data
            foreach ($users as $user) {
                fputcsv($file, [$user->id, $user->uid, $user->name, $user->country_code, $user->number, $user->email, $user->login_type, $user->created_at]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExpiredSubscribers(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $currentDate = Carbon::now();

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '<', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        // Export logic
        $fileName = 'active_subscribers.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['ID', 'UID', 'Name', 'Country Code', 'Number', 'Email', 'Login Type', 'Created At']);

            // Write CSV data
            foreach ($users as $user) {
                fputcsv($file, [$user->id, $user->uid, $user->name, $user->country_code, $user->number, $user->email, $user->login_type, $user->created_at]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public static function checkSubsStatus($status, $payment_status): string
    {
        if ($payment_status == 'refunded') {
            return "Refunded";
        }

        if ($status == '1') {
            return "Active";
        } else {
            return "Expired";
        }
    }

    public static function getSubsColor($status, $payment_status): string
    {
        if ($payment_status == 'paid' && $status == '1') {
            return "#2EC4B6";
        } else {
            return "#FF0000";
        }
    }

}

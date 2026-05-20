<?php

namespace App\Http\Controllers\Revenue;

use App\Helpers\JwtHelper;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\CryptoJsAes;
use App\Http\Controllers\Utils\PaginationController;
use App\Models\Pricing\OfferPage;
use App\Models\Revenue\UserSubscriptions;
use App\Models\User;
use App\Models\UserData;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Revenue\MasterPurchaseHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RevenueController extends ApiController
{

    private string $sumKey = 'net_amount';

    private array $filters = [
        [
            'key' => 'source',
            'label' => 'Source:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'google', 'value' => 'Google'],
                ['key' => 'meta', 'value' => 'Meta'],
                ['key' => 'meta-google', 'value' => 'Meta-Google'],
                ['key' => 'seo', 'value' => 'SEO'],
                ['key' => 'sales', 'value' => 'Sales'],
                ['key' => 'e_mandate', 'value' => 'E-Mandate'],
            ]
        ],

        [
            'key' => 'buyerType',
            'label' => 'Type:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'old_sub', 'value' => 'Old Sub'],
                ['key' => 'new_sub', 'value' => 'New Sub'],
                ['key' => 'offer_sub', 'value' => 'Offer Sub'],
                ['key' => 'template', 'value' => 'Template'],
                ['key' => 'video', 'value' => 'Videos'],
                ['key' => 'caricature', 'value' => 'Caricatures'],
                ['key' => 'ai_credit', 'value' => 'AI Credits'],
            ]
        ],

        [
            'key' => 'promocode',
            'label' => 'Promo:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'true', 'value' => 'True'],
                ['key' => 'false', 'value' => 'False'],
            ]
        ],

        [
            'key' => 'return_user',
            'label' => 'Retention:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'true', 'value' => 'True'],
                ['key' => 'false', 'value' => 'False'],
            ]
        ],

        [
            'key' => 'time_range',
            'label' => '',
            'defaultValue' => 'today',
            'isTimeRange' => true,
            'options' => [
                ['key' => 'none', 'value' => 'All Time'],
                ['key' => 'till_date', 'value' => "Month's till date"],
                ['key' => 'today', 'value' => 'Today'],
                ['key' => 'yesterday', 'value' => 'Yesterday'],
                ['key' => 'last_7_days', 'value' => 'Last 7 Days'],
                ['key' => 'last_30_days', 'value' => 'Last 30 Days'],
                ['key' => 'current_month', 'value' => 'Current Month'],
                ['key' => 'last_month', 'value' => 'Last Month'],
                ['key' => 'current_financial_year', 'value' => 'Current Financial Year'],
                ['key' => 'last_financial_year', 'value' => 'Last Financial Year'],
                ['key' => 'current_year', 'value' => 'Current Year'],
                ['key' => 'last_year', 'value' => 'Last Year'],
                ['key' => 'custom', 'value' => 'Custom'],
            ]
        ],
    ];

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::whereEmail($credentials['email'])->whereUserType(1)->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {

            $jwtPayload = [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://picsum.photos/100/100',
                'role' => 'Admin',
            ];

            $userData = $jwtPayload;
            $userData['token'] = JwtHelper::generateByHours($jwtPayload, 24);

            return response()->json([
                'statusCode' => 200,
                'success' => true,
                'msg' => 'Login successful',
                'user' => $userData
            ]);
        }

        return response()->json([
            'statusCode' => 401,
            'success' => false,
            'msg' => 'Invalid credentials'
        ], 401);
    }

    public function index(Request $request): mixed
    {

        $this->checkAuth($request);

        $timeRangeLabel = $request->input('time_range', 'till_date');
        $source = $request->input('source', 'all');
        $buyerType = $request->input('buyerType', 'all');

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $timeRange = $this->getTimePeriod($timeRangeLabel);

        if (is_string($timeRange)) {
            return response()->json(['success' => false, 'msg' => $timeRange]);
        }

        $query = MasterPurchaseHistory::with(['userData'])->wherePaymentStatus('paid');

        if ($buyerType !== "all") $query->whereProductType($buyerType);

        if ($source !== "all") {
            if ($source === "google") {
                $query->whereNotNull('gclid')->whereNull('fbc')->whereIsEMandate(0);
            } else if ($source === "meta") {
                $query->whereNotNull('fbc')->whereNull('gclid')->whereIsEMandate(0);
            } else if ($source === "meta-google") {
                $query->whereNotNull('fbc')->whereNotNull('gclid')->whereIsEMandate(0);
            } else if ($source === "seo") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(0);
            } else if ($source === "sales") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(1);
            } else if ($source === "e_mandate") {
                $query->whereIsEMandate(1);
            }
        }

        if ($return_user !== "all") {
            $query->where('total_purchases', $return_user === 'true' ? '>' : '=', 1);
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        $clonedQuery = clone $query;

        if (!empty($timeRange)) {
            $globalStart = $timeRange[0];
            $globalEnd = $timeRange[1];
        } else {
            $globalStart = Carbon::parse($clonedQuery->min('created_at'))->startOfDay();
            $globalEnd = Carbon::parse($clonedQuery->max('created_at'))->endOfDay();
        }

        $totalDays = $globalStart->diffInDays($globalEnd) + 1;

        if (!empty($timeRange)) $query->whereBetween('created_at', $timeRange);

        $totalPaidAmount = $query->sum($this->sumKey);

        $now = Carbon::now();

        $extra1 = [];
        if ($timeRangeLabel === 'till_date') {
            $extra1[] = [
                "title" => "Today's Revenue",
                "value" => "₹" . number_format($clonedQuery->whereBetween('created_at', $this->getTimePeriod('today'))->sum($this->sumKey), 2),
                "trend" => 0,
                "link" => "/transactions"
            ];
        }

        array_push($extra1,
            [
                "title" => 'Total Revenue',
                "value" => "₹" . number_format($totalPaidAmount, 2),
                "trend" => 0,
                "link" => "/transactions"
            ],

            [
                "title" => 'Average Day Revenue',
                "value" => "₹" . number_format($totalPaidAmount / $totalDays, 2) . " - ($totalDays Days)",
                "trend" => 0,
                "link" => "/transactions"
            ]
        );

        if ($timeRangeLabel === 'till_date') {
            $totalMonthDays = $now->copy()->startOfMonth()->diffInDays($now->copy()->endOfMonth()) + 1;
            $extra1[] = [
                "title" => 'Estimated Revenue',
                "value" => "₹" . number_format(($totalPaidAmount / $totalDays) * $totalMonthDays, 2) . " - ($totalMonthDays Days)",
                "trend" => 0,
                "link" => "/transactions"
            ];
        }

        array_push($extra1,
            [
                "title" => 'Total Transactions',
                "value" => $query->count(),
                "trend" => 0,
                "link" => "/transactions"
            ],
            [
                "title" => "Today's EMandate",
                "value" => MasterPurchaseHistory::whereNotNull('subscription_id')->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])->count(),
                "trend" => 0,
                "link" => "/e-mandate"
            ]
        );

        $filters = collect($this->filters)->keyBy('key');
        $timeRange = $filters->get('time_range', []);
        $timeRange['defaultValue'] = 'till_date';
        $filters->put('time_range', $timeRange);

        $datas['datas'] = $extra1;
        $datas['filters'] = $filters->values()->toArray();
        return response()->json($datas);
    }

    private function checkAuth(Request $request): void
    {
        $errorData = response()->json([
            'statusCode' => 401,
            'success' => false,
            'msg' => 'Unauthenticated. Invalid or missing token.'
        ], 401);

        $token = $request->bearerToken();

        if (empty($token)) abort($errorData);

        try {
            $decoded = JwtHelper::decode($token);
            $user = User::whereEmail($decoded->email)->whereUserType(1)->first();
            if (!$user) abort($errorData);
        } catch (Exception $e) {
            abort($errorData);
        }

    }

    private function getTimePeriod(string $timeRange, $forEMandate = false): string|array
    {
        $now = Carbon::now();

        if ($forEMandate) {
            $periods = [
                'none' => [],
                'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'tomorrow' => [$now->copy()->addDay()->startOfDay(), $now->copy()->addDay()->endOfDay(),],
                'day_after_tomorrow' => [$now->copy()->addDays(2)->startOfDay(), $now->copy()->addDays(2)->endOfDay()],
                'in_3_days' => [$now->copy()->addDays(3)->startOfDay(), $now->copy()->addDays(3)->endOfDay()],
                'in_4_days' => [$now->copy()->addDays(4)->startOfDay(), $now->copy()->addDays(4)->endOfDay()],
                'in_5_days' => [$now->copy()->addDays(5)->startOfDay(), $now->copy()->addDays(5)->endOfDay()],
                'in_6_days' => [$now->copy()->addDays(6)->startOfDay(), $now->copy()->addDays(6)->endOfDay()],
                'in_7_days' => [$now->copy()->addDays(7)->startOfDay(), $now->copy()->addDays(7)->endOfDay()],
            ];
        } else {
            $periods = [
                'none' => [],
                'till_date' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
                'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
                'last_30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
                'current_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
                'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
                'current_financial_year' => [
                    $now->month >= 4
                        ? Carbon::create($now->year, 4, 1)->startOfDay()
                        : Carbon::create($now->year - 1, 4, 1)->startOfDay(),

                    $now->month >= 4
                        ? Carbon::create($now->year + 1, 3, 31)->endOfDay()
                        : Carbon::create($now->year, 3, 31)->endOfDay(),
                ],
                'last_financial_year' => [
                    $now->month >= 4
                        ? Carbon::create($now->year - 1, 4, 1)->startOfDay()
                        : Carbon::create($now->year - 2, 4, 1)->startOfDay(),

                    $now->month >= 4
                        ? Carbon::create($now->year, 3, 31)->endOfDay()
                        : Carbon::create($now->year - 1, 3, 31)->endOfDay(),
                ],
                'current_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
                'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            ];
        }

        if (!array_key_exists($timeRange, $periods)) {
            if (empty($timeRange)) return 'Invalid request';

            try {
                if (str_starts_with($timeRange, 'custom:')) {
                    $parts = explode(' to ', str_replace('custom: ', '', $timeRange));
                    $start = Carbon::parse($parts[0])->startOfDay();
                    $end = Carbon::parse($parts[1])->endOfDay();
                    return [$start, $end];
                }
                return 'Invalid date format';
            } catch (Exception $e) {
                return 'Invalid date format';
            }
        }

        return $periods[$timeRange];
    }

    public function analytics(Request $request): JsonResponse
    {
        $this->checkAuth($request);

        $timeRange = $request->input('time_range', 'today');
        $source = $request->input('source', 'all');
        $buyerType = $request->input('buyerType', 'all');

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $timeRange = $this->getTimePeriod($timeRange);

        if (is_string($timeRange)) {
            return response()->json(['success' => false, 'msg' => $timeRange]);
        }

        $isLifetime = empty($timeRange);

        $query = MasterPurchaseHistory::with(['userData'])->wherePaymentStatus('paid');;

        if ($buyerType !== "all") $query->whereProductType($buyerType);
        if ($source !== "all") {
            if ($source === "google") {
                $query->whereNotNull('gclid')->whereNull('fbc')->whereIsEMandate(0);
            } else if ($source === "meta") {
                $query->whereNotNull('fbc')->whereNull('gclid')->whereIsEMandate(0);
            } else if ($source === "meta-google") {
                $query->whereNotNull('fbc')->whereNotNull('gclid')->whereIsEMandate(0);
            } else if ($source === "seo") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(0);
            } else if ($source === "sales") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(1);
            } else if ($source === "e_mandate") {
                $query->whereIsEMandate(1);
            }
        }

        if ($return_user !== "all") {
            $query->where('total_purchases', $return_user === 'true' ? '>' : '=', 1);
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!$isLifetime) $query->whereBetween('created_at', $timeRange);

        $transactions = $query->orderByDesc('id')->get();

        $determineSource = function ($t) {
            if ($t->is_e_mandate == 1) return 'E-Mandate';
            if ($t->fbc && $t->gclid) return 'Meta-Google';
            if ($t->gclid && !$t->fbc) return 'Google';
            if ($t->fbc && !$t->gclid) return 'Meta';
            if ($t->by_sales_team == 1) return 'Sales';
            return 'SEO';
        };

        // Helper: Colors
        $getSourceColor = function ($name) {
            $colors = [
                'Google' => '#ff0000',
                'Meta' => '#1877F2',
                'Meta-Google' => '#0F9D58',
                'SEO' => '#F4B400',
                'Sales' => '#8E24AA',
                'E-Mandate' => '#FF6D01',
            ];
            return $colors[$name] ?? '#9CA3AF';
        };

        // 1. Summary Metrics
        $totalIncome = $transactions->sum($this->sumKey);

        // 2. Sources Aggregation
        // Group transactions by calculated source
        $sourceGroups = $transactions->groupBy($determineSource);
        $sources = [];

        $allKnownSources = ['Google', 'Meta', 'Meta-Google', 'SEO', 'Sales', 'E-Mandate'];

        foreach ($allKnownSources as $srcName) {
            $group = $sourceGroups->get($srcName, collect([]));
            $total = $group->sum($this->sumKey);
            $percentage = $totalIncome > 0 ? ($total / $totalIncome) * 100 : 0;

            $sources[] = [
                'name' => $srcName,
                'total' => $total,
                'revenue' => $total,
                'percent' => round($percentage, 2),
                'growth' => 0, // Placeholder
                'color' => $getSourceColor($srcName),
                'avgOrder' => 0,
                'conversionRate' => 0
            ];
        }

        // 3. Chart Data (Daily)
        $chartData = [];

        if ($isLifetime) {
            $start = $transactions->min('created_at');
            $end = $transactions->max('created_at');
        } else {
            $start = $timeRange[0];
            $end = $timeRange[1];
        }

        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            $byHour = $transactions->groupBy(function ($item) {
                return $item->created_at->format('H');
            });

            // Loop 0 to 24 (Total 25 data points)
            for ($i = 0; $i <= 24; $i++) {
                $breakdown = [];
                foreach ($allKnownSources as $s) {
                    $breakdown[$s] = 0;
                }

                if ($i < 24) {
                    // Standard Hours (00:00 to 23:00)
                    $hourKey = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $txs = $byHour->get($hourKey, collect([]));
                    $income = $txs->sum($this->sumKey);

                    foreach ($txs as $t) {
                        $s = $determineSource($t);
                        $breakdown[$s] += $t[$this->sumKey];
                    }

                    $label = Carbon::createFromTime($i, 0)->format('H:00');
                    $dateStr = $start->format('Y-m-d') . " $hourKey:00:00";
                } else {
                    // Last Entry: 23:59:59
                    // Income is 0 for this closing tick (unless you specifically query for this second)
                    $income = 0;
                    $label = '23:59';
                    $dateStr = $start->format('Y-m-d') . " 23:59:59";
                }

                $chartData[] = [
                    'name' => $label,
                    'label' => $label,
                    'date' => $dateStr,
                    'revenue' => $income,
                    'outcome' => 0,
                    'breakdown' => $breakdown,
                    ...$breakdown
                ];
            }
        } else {
            // Create period to ensure days with 0 data are included
            $period = CarbonPeriod::create($start, $end);

            // Group transactions by Date (Y-m-d)
            $byDate = $transactions->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $dayTxs = $byDate->get($dateStr, collect([]));
                $dayIncome = $dayTxs->sum($this->sumKey);

                // Calculate breakdown for this specific day
                $breakdown = [];
                foreach ($allKnownSources as $s) {
                    $breakdown[$s] = 0;
                }
                foreach ($dayTxs as $t) {
                    $s = $determineSource($t);
                    $breakdown[$s] += $t[$this->sumKey];
                }

                $chartData[] = [
                    'name' => $date->format('M j'),
                    'label' => $date->format('M j'),
                    'date' => $dateStr,
                    'revenue' => $dayIncome,
                    'outcome' => 0,
                    'breakdown' => $breakdown,
                    ...$breakdown
                ];
            }

        }

        // 4. Recent Transactions (Formatted)


        $recentTransactions = $transactions->take(10)->map(function ($t) use ($determineSource) {
            /** @var MasterPurchaseHistory $t */
            return [
                'id' => $t->id,
                'title' => $t->product_id,
                'user_id' => $t->user_id,
                'name' => $t->userData?->name ?? "Unknown",
                'email' => $t->userData?->email ?? "Unknown",
                'contact_no' => $t->contact_no ?? $t->userData?->contact_no ?? "--",
                'transaction_id' => $t->transaction_id,
                'subscription_id' => $t->subscription_id,
                'date' => $t->created_at->format('Y-m-d H:i:s'),
                'amount' => $t[$this->sumKey],
                'status' => $t->status == 1 ? 'Completed' : 'Refunded',
                'type' => $t->product_type,
                'source' => $determineSource($t),
                'avatar' => $t->userData?->photo_uri ?? "",
                'url' => $t->url
            ];

        })->values();

        // 5. Income Types (Filter out zero values)
        $incomeTypes = collect($sources)
            ->filter(fn($s) => $s['revenue'] > 0)
            ->map(function ($s) {
                return [
                    'name' => $s['name'],
                    'value' => (float)number_format($s['revenue'], 1, '.', ''),
                    'color' => $s['color']
                ];
            })
            ->values();

        // 6. Construct Final Response
        // Note: Returning directly matches DashboardResponse interface
        return response()->json([
            'filters' => $this->filters,
            'meta' => [
                'currency' => 'INR',
                'currencySymbol' => '₹',
                'generatedAt' => now()->toIso8601String()
            ],
            'summary' => [
                'totalRevenue' => $totalIncome,
                'revenueTrend' => 0
            ],
            'chartData' => $chartData,
            'sources' => $sources,
            'incomeTypes' => $incomeTypes,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function logs(Request $request): mixed
    {
        $this->checkAuth($request);

        try {
            $user_id = $request->input('user_id');
            $timeRange = $request->input('time_range', empty($user_id) ? 'today' : 'none');
            $source = $request->input('source', 'all');
            $buyerType = $request->input('buyerType', 'all');
            $url = $request->input('url', 'all');

            $return_user = $request->input('return_user', 'all'); //all, true, false
            $usedPromoCode = $request->input('promocode', 'all'); //all, true, false
            $paymentStatus = $request->input('payment_status', 'paid'); //all, true, false

            $page = $request->input('page', 1);

            $timeRange = $this->getTimePeriod($timeRange);

            if (is_string($timeRange)) {
                return response()->json(['success' => false, 'msg' => $timeRange]);
            }

            $userData = null;
            if ($user_id) {
                $userData = UserData::whereUid($user_id)->orWhere('email', $user_id)->first();
                if (!$userData) {
                    return response()->json(['success' => false, 'msg' => 'No Data Found']);
                }
            }

            $query = MasterPurchaseHistory::with(['userData', 'order']);

            if ($userData) $query->whereUserId($userData->uid);
            if ($paymentStatus != "all") $query->wherePaymentStatus($paymentStatus);
            if ($buyerType !== "all") $query->whereProductType($buyerType);
            if ($source !== "all") {
                if ($source === "google") {
                    $query->whereNotNull('gclid')->whereNull('fbc')->whereIsEMandate(0);
                } else if ($source === "meta") {
                    $query->whereNotNull('fbc')->whereNull('gclid')->whereIsEMandate(0);
                } else if ($source === "meta-google") {
                    $query->whereNotNull('fbc')->whereNotNull('gclid')->whereIsEMandate(0);
                } else if ($source === "seo") {
                    $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(0);
                } else if ($source === "sales") {
                    $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(1);
                } else if ($source === "e_mandate") {
                    $query->whereIsEMandate(1);
                }
            }

            if ($return_user !== "all") {
                $query->where('total_purchases', $return_user === 'true' ? '>' : '=', 1);
            }

            if ($usedPromoCode !== "all") {
                $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
            }

            if ($url !== "all") {
                $query->whereRaw("url REGEXP ?", ['/'.$url.'(\\?|$)']);
            }

            if (!empty($timeRange)) $query->whereBetween('created_at', $timeRange);

            $limit = 20;
            $totalIncome = $query->sum($this->sumKey);
            $transactions = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

            $determineSource = function ($t) {
                if ($t->is_e_mandate == 1) return 'E-Mandate';
                if ($t->fbc && $t->gclid) return 'Meta-Google';
                if ($t->gclid && !$t->fbc) return 'Google';
                if ($t->fbc && !$t->gclid) return 'Meta';
                if ($t->by_sales_team == 1) return 'Sales';
                return 'SEO';
            };

            // 1. Summary Metrics
            $collection = $transactions->getCollection();

            $transactionList = $collection->map(function ($t) use ($determineSource) {
                /** @var MasterPurchaseHistory $t */

//            'caricatures' => $addCaricature ? self::$MAX_ADDON_CARICATURES : 0,
//            'meta_course' => $metaCourse,
//            'on_demand_service' => $onDemandService,
                $addons = [];
                if ($t->order && !empty($t->order->raw_notes)) {
                    $raw_notes = $t->order->raw_notes;
                    if (!empty($raw_notes['caricatures'])) {
                        $addons[] = $raw_notes['caricatures'] . " caricatures";
                    }
                    if (!empty($raw_notes['meta_course'])) {
                        $addons[] = "Meta Course";
                    }
                    if (!empty($raw_notes['on_demand_service'])) {
                        $addons[] = "On Demand Service";
                    }
                }

                return [
                    'id' => $t->id,
                    'title' => $t->product_id,
                    'user_id' => $t->user_id,
                    'name' => $t->userData?->name ?? "Unknown",
                    'email' => $t->userData?->email ?? "Unknown",
                    'contact_no' => $t->contact_no ?? $t->userData?->contact_no ?? "--",
                    'transaction_id' => $t->transaction_id,
                    'subscription_id' => $t->subscription_id,
                    'addons' => implode(", ", $addons),
                    'date' => $t->created_at->format('Y-m-d H:i:s'),
                    'amount' => $t[$this->sumKey],
                    'status' => $t->status == 1 ? 'Completed' : 'Refunded',
                    'type' => $t->product_type,
                    'source' => $determineSource($t),
                    'avatar' => $t->userData?->photo_uri ?? "",
                    'payment_status' => $t->payment_status,
                    'url' => $t->url
                ];

            })->values();

            $filters = collect($this->filters)->keyBy('key');
            if (!empty($user_id)) {
                $timeRange = $filters->get('time_range', []);
                $timeRange['defaultValue'] = 'none';
                $filters->put('time_range', $timeRange);
            }

            $filters['payment_status'] = [
                'key' => 'payment_status',
                'label' => 'Status:',
                'defaultValue' => 'paid',
                'options' => [
                    ['key' => 'all', 'value' => 'All'],
                    ['key' => 'paid', 'value' => 'Paid'],
                    ['key' => 'refunded', 'value' => 'Refunded'],
                ]
            ];

            $datas = OfferPage::select(['slug'])
                ->get()
                ->map(function ($item) {
                    return [
                        'key' => $item->slug,
                        'value' => Str::title(str_replace('-', ' ', $item->slug))
                    ];
                })
                ->values()
                ->toArray();

            $filters['urls'] = [
                'key' => 'url',
                'label' => 'URL:',
                'defaultValue' => 'all',
                'options' => [
                    ['key' => 'all', 'value' => 'All'],
                    ...$datas
                ]
            ];

            return response()->json([
                'filters' => $filters->values()->toArray(),
                'meta' => [
                    'currency' => 'INR',
                    'currencySymbol' => '₹',
                    'generatedAt' => now()->toIso8601String()
                ],
                'totalIncome' => $totalIncome,
                'transactions' => $transactionList,
                "pagination" => PaginationController::getPagination($transactions, [], ''),
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }


    }

    public function e_mandates(Request $request): JsonResponse
    {
        $this->checkAuth($request);

        $timeRange = $request->input('time_range', 'today');
        $source = $request->input('source', 'all');
        $buyerType = $request->input('buyerType', 'all');

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $page = $request->input('page', 1);

        $timeRange = $this->getTimePeriod($timeRange, true);

        if (is_string($timeRange)) {
            return response()->json(['success' => false, 'msg' => $timeRange]);
        }

        $query = MasterPurchaseHistory::with(['userData'])
            ->whereNotNull('subscription_id')
            ->where('subscription_is_active', 1)
            ->where('subscription_status', 'active')
            ->withCount([
                'userSubscriptions as subscription_purchases_count' => function ($q) {
                    $q->whereNotNull('subscription_id');
                }
            ]);

        if ($return_user !== "all") {
            $query->whereRaw(
                '(
            SELECT COUNT(*)
            FROM purchase_history AS ph2
            WHERE ph2.user_id = purchase_history.user_id
              AND ph2.subscription_id IS NOT NULL
        ) = ?',
                [((int)$return_user)]
            );
        }

        if ($buyerType !== "all") $query->whereProductType($buyerType);
        if ($source !== "all") {
            if ($source === "google") {
                $query->whereNotNull('gclid')->whereNull('fbc')->whereIsEMandate(0);
            } else if ($source === "meta") {
                $query->whereNotNull('fbc')->whereNull('gclid')->whereIsEMandate(0);
            } else if ($source === "meta-google") {
                $query->whereNotNull('fbc')->whereNotNull('gclid')->whereIsEMandate(0);
            } else if ($source === "seo") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(0);
            } else if ($source === "sales") {
                $query->whereNull('fbc')->whereNull('gclid')->whereIsEMandate(0)->whereBySalesTeam(1);
            } else if ($source === "e_mandate") {
                $query->whereIsEMandate(1);
            }
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!empty($timeRange)) $query->whereBetween('expired_at', $timeRange);

        $totalIncome = $query->sum($this->sumKey);

        if (!empty($timeRange)) {
            $globalStart = $timeRange[0];
            $globalEnd = $timeRange[1];

            $willGlobalStart = $timeRange[0];
            $willGlobalEnd = $timeRange[1];
        } else {
            $rangeQuery = clone $query;
            $globalStart = Carbon::parse($rangeQuery->min('created_at'))->startOfDay();
            $globalEnd = Carbon::parse($rangeQuery->max('created_at'))->endOfDay();

            $willGlobalStart = Carbon::parse($rangeQuery->min('expired_at'))->startOfDay();
            $willGlobalEnd = Carbon::parse($rangeQuery->max('expired_at'))->endOfDay();
        }

        $limit = 200;
        $transactions = $query->orderBy('expired_at')->paginate($limit, ['*'], 'page', $page);

        $determineSource = function ($t) {
            if ($t->is_e_mandate == 1) return 'E-Mandate';
            if ($t->fbc && $t->gclid) return 'Meta-Google';
            if ($t->gclid && !$t->fbc) return 'Google';
            if ($t->fbc && !$t->gclid) return 'Meta';
            if ($t->by_sales_team == 1) return 'Sales';
            return 'SEO';
        };

        // 1. Summary Metrics
        $collection = $transactions->getCollection();

        $transactionList = $collection->map(function ($t) use ($determineSource) {
            /** @var MasterPurchaseHistory $t */
            return [
                'id' => $t->id,
                'title' => $t->product_id,
                'user_id' => $t->user_id,
                'name' => $t->userData?->name ?? "Unknown",
                'email' => $t->userData?->email ?? "Unknown",
                'contact_no' => $t->contact_no ?? $t->userData?->contact_no ?? "--",
                'transaction_id' => $t->transaction_id,
                'subscription_id' => $t->subscription_id,
                'date' => $t->created_at->format('Y-m-d H:i:s'),
                'expired_at' => $t->expired_at,
                'amount' => $t[$this->sumKey],
                'status' => $t->status == 1 ? 'Completed' : 'Refunded',
                'type' => $t->product_type,
                'source' => $determineSource($t) . " ($t->subscription_purchases_count) - $t->subscription_status",
                'avatar' => $t->userData?->photo_uri ?? "",
                'url' => $t->url
            ];
        })->values();

        $query = MasterPurchaseHistory::whereNotNull('subscription_id');

        if ($return_user !== "all") {
            $query->whereRaw(
                '(
            SELECT COUNT(*)
            FROM purchase_history AS ph2
            WHERE ph2.user_id = purchase_history.user_id
              AND ph2.subscription_id IS NOT NULL
        ) = ?',
                [(int)$return_user]
            );
        }


        $total = (clone $query)->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $active = (clone $query)->where('subscription_is_active', 1)->where('subscription_status', 'active')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $pending = (clone $query)->where('subscription_is_active', 1)->where('subscription_status', 'pending')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $halted = (clone $query)->where('subscription_status', 'halted')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $cancelled = (clone $query)->where('subscription_status', 'cancelled')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $paused = (clone $query)->where('subscription_status', 'paused')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $expired = (clone $query)->where('subscription_status', 'expired')->whereBetween('expired_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $successed = (clone $query)->where('is_e_mandate', 1)->whereBetween('created_at', [$globalStart, $globalEnd])->distinct('subscription_id')->count('subscription_id');
        $inrCollect = '₹' . (clone $query)->where('subscription_is_active', 1)->where('subscription_status', 'active')->where('currency_code', 'INR')->whereBetween('expired_at', [$willGlobalStart, $willGlobalEnd])->sum('next_amount');
        $usdCollect = '$' . (clone $query)->where('subscription_is_active', 1)->where('subscription_status', 'active')->where('currency_code', 'USD')->whereBetween('expired_at', [$willGlobalStart, $willGlobalEnd])->sum('next_amount');

        $collectedInrCollect = (clone $query)->where('is_e_mandate', 0)->whereBetween('created_at', [$globalStart, $globalEnd])->sum($this->sumKey);
        $collectedUsdCollect = (clone $query)->where('is_e_mandate', 1)->whereBetween('created_at', [$globalStart, $globalEnd])->sum($this->sumKey);

        $extra = [
            [
                "title" => 'Total',
                "value" => $total,
                "trend" => 0,
            ],
            [
                "title" => 'Active',
                "value" => $active,
                "trend" => 0,
            ],
            [
                "title" => 'Successed',
                "value" => $successed,
                "trend" => 0,
            ],
            [
                "title" => 'Pending',
                "value" => $pending,
                "trend" => 0,
            ],
            [
                "title" => 'Halted',
                "value" => $halted,
                "trend" => 0,
            ],
            [
                "title" => 'Paused',
                "value" => $paused,
                "trend" => 0,
            ],
            [
                "title" => "Canceled",
                "value" => $cancelled,
                "trend" => 0,
            ],
            [
                "title" => "Expired",
                "value" => $expired,
                "trend" => 0,
            ],
            [
                "title" => "Active %",
                "value" => round(($active + $successed) / ($total == 0 ? 1 : $total) * 100, 2) . "%",
                "trend" => 0,
            ],
            [
                "title" => "Will be collect",
                "value" => "$inrCollect - $usdCollect",
                "trend" => 0,
            ],
            [
                "title" => "First Time",
                "value" => "₹" . round($collectedInrCollect, 2),
                "trend" => 0,
            ],
            [
                "title" => "Auto Collection",
                "value" => "₹" . round($collectedUsdCollect, 2),
                "trend" => 0,
            ],
            [
                "title" => "Total",
                "value" => "₹" . round($collectedInrCollect + $collectedUsdCollect, 2),
                "trend" => 0,
            ],
        ];

        $filters = collect($this->filters)->keyBy('key');

        $filters['return_user'] = [
            'key' => 'return_user',
            'label' => 'Retention:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => '1', 'value' => '1'],
                ['key' => '2', 'value' => '2'],
                ['key' => '3', 'value' => '3'],
                ['key' => '4', 'value' => '4'],
                ['key' => '5', 'value' => '5'],
            ]
        ];

        $filters['time_range'] = [
            'key' => 'time_range',
            'label' => '',
            'defaultValue' => 'today',
            'isTimeRange' => true,
            'options' => [
                ['key' => 'none', 'value' => 'All Time'],
                ['key' => 'yesterday', 'value' => 'Yesterday'],
                ['key' => 'today', 'value' => 'Today'],
                ['key' => 'tomorrow', 'value' => 'Tomorrow'],
                ['key' => 'day_after_tomorrow', 'value' => 'Day After Tomorrow'],
                ['key' => 'in_3_days', 'value' => 'In 3 Days'],
                ['key' => 'in_4_days', 'value' => 'In 4 Days'],
                ['key' => 'in_5_days', 'value' => 'In 5 Days'],
                ['key' => 'in_6_days', 'value' => 'In 6 Days'],
                ['key' => 'in_7_days', 'value' => 'In 7 Days'],
                ['key' => 'custom', 'value' => 'Custom'],
            ]
        ];

        return response()->json([
            'filters' => $filters->values()->toArray(),
            'meta' => [
                'currency' => 'INR',
                'currencySymbol' => '₹',
                'generatedAt' => now()->toIso8601String()
            ],
            'summary' => $extra,
            'totalIncome' => $totalIncome,
            'transactions' => $transactionList,
            "pagination" => PaginationController::getPagination($transactions, [], ''),
            "timeRange" => $timeRange,
            "a" => $globalStart,
            "b" => $globalEnd,
        ]);
    }

    public function new_subs(Request $request): mixed
    {
        try {
            $this->checkAuth($request);

            $datas = UserSubscriptions::where('first_amount', 0)->get();
            foreach ($datas as $data) {
                $firstAmount = MasterPurchaseHistory::whereSubscriptionId($data->gateway_subscription_id)->oldest()->first()->paid_amount;
                $data->first_amount = $firstAmount;
                $data->save();
            }

            $timeRange = $request->input('time_range', 'today');

            $page = $request->input('page', 1);

            $timeRange = $this->getTimePeriod($timeRange);

            if (is_string($timeRange)) {
                return response()->json(['success' => false, 'msg' => $timeRange]);
            }

            $query = UserSubscriptions::query();

            if (!empty($timeRange)) $query->whereBetween('created_at', $timeRange);

            if (!empty($timeRange)) {
                $globalStart = $timeRange[0];
                $globalEnd = $timeRange[1];
            } else {
                $rangeQuery = clone $query;
                $globalStart = Carbon::parse($rangeQuery->min('created_at'))->startOfDay();
                $globalEnd = Carbon::parse($rangeQuery->max('created_at'))->endOfDay();
            }

            $total = UserSubscriptions::whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $active = UserSubscriptions::whereIn('status', ['active', 'authenticated'])->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $cancelled = UserSubscriptions::where('status', 'cancelled')->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $paused = UserSubscriptions::where('status', 'paused')->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $halted = UserSubscriptions::where('status', 'halted')->whereBetween('created_at', [$globalStart, $globalEnd])->count();


            $manual = UserSubscriptions::whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $manualTimeRevenueInr = number_format(UserSubscriptions::whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('first_amount'), 2, '.', '');
            $firstTime = UserSubscriptions::where('paid_count', 2)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $firstTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 2)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');
            $secondTime = UserSubscriptions::where('paid_count', 3)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $secondTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 3)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');
            $thirdTime = UserSubscriptions::where('paid_count', 4)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $thirdTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 4)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');
            $fourthTime = UserSubscriptions::where('paid_count', 5)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $fourthTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 5)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');
            $fifthTime = UserSubscriptions::where('paid_count', 6)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $fifthTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 6)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');
            $sixthTime = UserSubscriptions::where('paid_count', 7)->whereBetween('created_at', [$globalStart, $globalEnd])->count();
            $sixthTimeRevenueInr = number_format(UserSubscriptions::where('paid_count', 7)->whereCurrency('INR')->whereBetween('created_at', [$globalStart, $globalEnd])->sum('amount'), 2, '.', '');

            $firstTimeTotal = $firstTime + $secondTime + $thirdTime + $fourthTime + $fifthTime + $sixthTime;
            $secondTimeTotal = $secondTime + $thirdTime + $fourthTime + $fifthTime + $sixthTime;
            $thirdTimeTotal = $thirdTime + $fourthTime + $fifthTime + $sixthTime;
            $fourthTimeTotal = $fourthTime + $fifthTime + $sixthTime;
            $fifthTimeTotal = $fifthTime + $sixthTime;
            $sixthTimeTotal = $sixthTime;

            $extra = [
                [
                    "title" => 'Total',
                    "value" => $total,
                    "trend" => 0,
                ],
                [
                    "title" => 'Active',
                    "value" => $active . " - (" . round(($active) / ($total == 0 ? 1 : $total) * 100, 2) . "%)",
                    "trend" => 0,
                ],
                [
                    "title" => 'Paused',
                    "value" => $paused,
                    "trend" => 0,
                ],
                [
                    "title" => "Canceled",
                    "value" => $cancelled,
                    "trend" => 0,
                ],
                [
                    "title" => "Halted",
                    "value" => $halted,
                    "trend" => 0,
                ],
                [
                    "title" => "Initiate",
                    "value" => $manual . " - (₹$manualTimeRevenueInr)",
                    "trend" => 0,
                ],
                [
                    "title" => "First Time",
                    "value" => $firstTimeTotal . " - (" . round(($firstTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$firstTimeRevenueInr)",
                    "trend" => 0,
                ],
                [
                    "title" => "Second Time",
                    "value" => $secondTimeTotal . " - (" . round(($secondTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$secondTimeRevenueInr)",
                    "trend" => 0
                ],
                [
                    "title" => "Third Time",
                    "value" => $thirdTimeTotal . " - (" . round(($thirdTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$thirdTimeRevenueInr)",
                    "trend" => 0
                ],
                [
                    "title" => "Fourth Time",
                    "value" => $fourthTimeTotal . " - (" . round(($fourthTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$fourthTimeRevenueInr)",
                    "trend" => 0
                ],
                [
                    "title" => "Fifth Time",
                    "value" => $fifthTimeTotal . " - (" . round(($fifthTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$fifthTimeRevenueInr)",
                    "trend" => 0
                ],
                [
                    "title" => "Sixth Time",
                    "value" => $sixthTimeTotal . " - (" . round(($sixthTimeTotal) / ($total == 0 ? 1 : $total) * 100, 2) . "%) - (₹$sixthTimeRevenueInr)",
                    "trend" => 0
                ],
                [
                    "title" => "Total",
                    "value" => '₹' . ((float)$firstTimeRevenueInr + (float)$secondTimeRevenueInr + (float)$thirdTimeRevenueInr + (float)$fourthTimeRevenueInr + (float)$fifthTimeRevenueInr + (float)$sixthTimeRevenueInr + (float)$manualTimeRevenueInr),
                    "trend" => 0
                ]
            ];

            $filters = collect($this->filters)->keyBy('key');

            $filters['return_user'] = [
                'key' => 'return_user',
                'label' => 'Retention:',
                'defaultValue' => 'all',
                'options' => [
                    ['key' => 'all', 'value' => 'All'],
                    ['key' => '1', 'value' => '1'],
                    ['key' => '2', 'value' => '2'],
                    ['key' => '3', 'value' => '3'],
                    ['key' => '4', 'value' => '4'],
                    ['key' => '5', 'value' => '5'],
                ]
            ];

            return response()->json([
                'filters' => $filters->values()->toArray(),
                'meta' => [
                    'currency' => 'INR',
                    'currencySymbol' => '₹',
                    'generatedAt' => now()->toIso8601String()
                ],
                'summary' => $extra,
                'totalIncome' => 0,
                'transactions' => [],
                "pagination" => PaginationController::getPagination(MasterPurchaseHistory::paginate(100, ['*'], 'page', $page), [], ''),
                "timeRange" => $timeRange,
                "a" => $globalStart,
                "b" => $globalEnd,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function top_users(Request $request): JsonResponse
    {
        $this->checkAuth($request);

        $page = $request->input('page', 1);
        $sortKey = $request->input('sort_key', 'amount');
        $sortBy = $request->input('sort_by', 'desc');

        $limit = 20;

        $sortKeys = [
            'last_purchase' => 'created_at',
            'amount' => 'total_amount',
            'purchases' => 'total_purchases',
        ];

        if (!array_key_exists($sortKey, $sortKeys)) {
            $sortKey = 'amount';
        }

        if ($sortBy !== 'asc' && $sortBy !== 'desc') {
            $sortBy = "desc";
        }

        $transactions = MasterPurchaseHistory::with('userData')
            ->select('user_id')
            ->selectRaw("
            MAX(id) as id,
            SUM($this->sumKey) as total_amount,
            COUNT(*) as total_orders,
            MAX(total_purchases) as total_purchases,
            MAX(created_at) as created_at
        ")
            ->groupBy('user_id')
            ->orderBy($sortKeys[$sortKey], $sortBy)
            ->paginate($limit, ['*'], 'page', $page);

        $collection = $transactions->getCollection();
        $transactionList = $collection->map(function ($t) {
            /** @var MasterPurchaseHistory $t */

            $transDatas = MasterPurchaseHistory::whereUserId($t->user_id)->whereProductType('old_sub')->whereStatus(1)->exists();

            return [
                'id' => $t->id,
                'user_id' => $t->user_id,
                'name' => $t->userData?->name ?? 'Unknown',
                'email' => $t->userData?->email ?? 'Unknown',
                'contact_no' => $t->userData?->contact_no ?? '--',
                'total_orders' => $t->total_orders,
                'amount' => "₹" . number_format($t->total_amount, 2, '.', ''),
                'avatar' => $t->userData?->photo_uri ?? '',
                'last_purchase' => $t->created_at->format('Y-m-d H:i:s'),
                'is_sub_active' => $transDatas,
                'url' => $t->url
            ];

        })->values();

        return response()->json([
            'filters' => [
                [
                    'key' => 'sort_key',
                    'label' => 'Sort Key:',
                    'defaultValue' => 'all',
                    'options' => [
                        ['key' => 'last_purchase', 'value' => 'Last Purchase'],
                        ['key' => 'amount', 'value' => 'Amount'],
                        ['key' => 'purchases', 'value' => 'Purchases'],
                    ]
                ],
                [
                    'key' => 'sort_by',
                    'label' => 'Sort By:',
                    'defaultValue' => 'all',
                    'options' => [
                        ['key' => 'asc', 'value' => 'asc'],
                        ['key' => 'desc', 'value' => 'desc'],
                    ]
                ],
            ],
            'top_users' => $transactionList,
            "pagination" => PaginationController::getPagination($transactions, [], ''),
        ]);
    }

}

<?php

namespace App\Http\Controllers\Revenue;

use App\Enums\UserRole;
use App\Helpers\JwtHelper;

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\Order;
use App\Models\Revenue\UserSubscriptions;
use App\Models\Revenue\MasterPurchaseHistory;

use App\Models\User;
use App\Models\UserData;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RevenueV2Controller extends ApiController
{

    private string $sumKey = 'net_amount';
    public User|null $employee = null;

    private array $addOnPrices = [
        'caricatures' => 249,
        'meta_course' => 149,
        'on_demand_service' => 499,
    ];

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
            ]
        ],
        [
            'key' => 'e_mandate',
            'label' => 'EMandate',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'true', 'value' => 'True'],
                ['key' => 'false', 'value' => 'False'],
            ]
        ],
        [
            'key' => 'close_by',
            'label' => 'Close By',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'true', 'value' => 'Sales'],
                ['key' => 'false', 'value' => 'Auto'],
            ]
        ],
        [
            'key' => 'buyerType',
            'label' => 'Type:',
            'defaultValue' => 'all',
            'options' => [
                ['key' => 'all', 'value' => 'All'],
                ['key' => 'old_sub', 'value' => 'Old Sub', 'color' => '#14B8A6'],
                ['key' => 'new_sub', 'value' => 'New Sub', 'color' => '#F97316'],
                ['key' => 'offer_sub', 'value' => 'Offer Sub', 'color' => '#A855F7'],
                ['key' => 'template', 'value' => 'Template', 'color' => '#8B5CF6'],
                ['key' => 'video', 'value' => 'Videos', 'color' => '#10B981'],
                ['key' => 'caricature', 'value' => 'Caricatures', 'color' => '#3B82F6'],
                ['key' => 'ai_credit', 'value' => 'AI Credits', 'color' => '#6366F1'],
                ['key' => 'business_support', 'value' => 'Business', 'color' => '#6366F1'],
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

    public function checkAuth(Request $request): void
    {
        $errorData = response()->json([
            'statusCode' => 401,
            'success' => false,
            'msg' => 'Unauthenticated. Invalid or missing token.'
        ], 401);

        $token = $request->bearerToken();

        if (empty($token))
            abort($errorData);

        try {
            $decoded = JwtHelper::decode($token);
            $this->employee = User::whereEmail($decoded->email)->first();
            if (!$this->employee)
                abort($errorData);
        } catch (Exception $e) {
            abort($errorData);
        }

    }

    public function login(Request $request): array|string
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::whereEmail($credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {

            $jwtPayload = [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://picsum.photos/100/100',
                'role' => 'Admin',
            ];

            $userData = $jwtPayload;
            $userData['token'] = JwtHelper::generateByHours($jwtPayload, 24);

            return $this->successed(msg: 'Login successful', datas: [
                'statusCode' => 200,
                'user' => $userData
            ]);
        }

        return $this->failed(msg: 'Invalid credentials');
    }

    public function index(Request $request): array|string
    {
        $this->checkAuth($request);

        if ($this->employee->user_type !== 1)
            return $this->failed(msg: "Unauthorized");

        $timeRangeLabel = $request->input('time_range', 'till_date');
        $source = $request->input('source', 'all');
        $buyerType = $request->input('buyerType', ['all']);

        $isEMandate = $request->input('e_mandate', 'all');
        $closeBy = $request->input('close_by', 'all');
        $salesUser = $request->input('sales_user', ['all']);

        $return_user = $request->input('return_user', 'all');
        $usedPromoCode = $request->input('promocode', 'all');

        $timeRange = $this->getTimePeriod($timeRangeLabel);

        if (is_string($timeRange)) {
            return $this->failed(statusCode: 400, msg: $timeRange);
        }

        $query = MasterPurchaseHistory::with(['userData'])->wherePaymentStatus('paid');

        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }

        if ($source !== "all") {
            if ($source === "google") {
                $query->whereNotNull('gclid')->whereNull('fbc');
            } else if ($source === "meta") {
                $query->whereNotNull('fbc')->whereNull('gclid');
            } else if ($source === "meta-google") {
                $query->whereNotNull('fbc')->whereNotNull('gclid');
            } else if ($source === "seo") {
                // Fixed: Decoupled SEO source from sales team filter to allow 'SEO + Sales' reporting.
                $query->whereNull('fbc')->whereNull('gclid');
            }
        }

        if ($isEMandate !== "all") {
            $query->where('is_e_mandate', $isEMandate === 'true' ? '!=' : '=', 0);
        }

        if ($closeBy != 'all') {
            $query->where('by_sales_team', $closeBy === 'true' ? '!=' : '=', 0);
            if ($closeBy) {
                if (!in_array('all', $salesUser)) {
                    $query->whereIn('emp_id', $salesUser);
                }
            }
        }

        if ($return_user !== "all") {
            if ($return_user === 'true') {
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) > 1');
                })->where('product_type', '!=', 'business_support')
                    ->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('purchase_history as mph2')
                            ->where('product_type', '!=', 'business_support')
                            ->whereColumn('mph2.user_id', 'purchase_history.user_id')
                            ->whereColumn('mph2.id', '<', 'purchase_history.id');
                    });
            } else {
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) = 1');
                })->where('product_type', '!=', 'business_support');
            }
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

        if (!empty($timeRange))
            $query->whereBetween('created_at', $timeRange);

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

        array_push(
            $extra1,
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

        array_push(
            $extra1,
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

        $filters = $this->getFilterWithSales();
        $timeRange = $filters->get('time_range', []);
        $timeRange['defaultValue'] = 'till_date';
        $filters->put('time_range', $timeRange);

        $datas['datas'] = $extra1;
        $datas['filters'] = $filters->values()->toArray();
        return $this->successed(msg: 'Dashboard data fetched successfully', datas: $datas);
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
            if (empty($timeRange))
                return 'Invalid request';

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

    public function analytics(Request $request): array|string
    {
        $this->checkAuth($request);

        if ($this->employee->user_type !== 1)
            return $this->failed(msg: "Unauthorized");

        $timeRange = $request->input('time_range', 'today');
        $source = $request->input('source', ['all']);
        $buyerType = $request->input('buyerType', ['all']);

        $return_user = $request->input('return_user', 'all');
        $usedPromoCode = $request->input('promocode', 'all');

        $isEMandate = $request->input('e_mandate', 'all');
        $closeBy = $request->input('close_by', 'all');
        $salesUser = $request->input('sales_user', ['all']);

        $search = $request->input('search', '');

        $timeRange = $this->getTimePeriod($timeRange);

        if (is_string($timeRange)) {
            return $this->failed(statusCode: 400, msg: $timeRange);
        }

        $isLifetime = empty($timeRange);

        $query = MasterPurchaseHistory::with(['userData'])->wherePaymentStatus('paid');


        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }
        if (!in_array('all', $source)) {
            $query->where(function ($q) use ($source, $closeBy) {
                foreach ($source as $src) {
                    switch ($src) {
                        case "google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('gclid')
                                    ->whereNull('fbc');
                            });
                            break;
                        case "meta":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                        case "meta-google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNotNull('gclid');
                            });
                            break;
                        case "seo":
                            $q->orWhere(function ($subQuery) {
                                // Fixed: Decoupled SEO source from sales team filter to allow 'SEO + Sales' reporting.
                                $subQuery->whereNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                    }
                }
            });
        }

        if ($isEMandate !== "all") {
            $query->where('is_e_mandate', $isEMandate === 'true' ? '!=' : '=', 0);
        }

        if ($closeBy != 'all') {
            $query->where('by_sales_team', $closeBy === 'true' ? '!=' : '=', 0);
            if ($closeBy) {
                if (!in_array('all', $salesUser)) {
                    $query->whereIn('emp_id', $salesUser);
                }
            }
        }

        if ($return_user !== "all") {
            if ($return_user === 'true') {
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) > 1');
                })->where('product_type', '!=', 'business_support')
                    ->whereExists(function ($sub) {
                        $sub->select(DB::connection()->raw(1))
                            ->from('purchase_history as mph2')
                            ->where('product_type', '!=', 'business_support')
                            ->whereColumn('mph2.user_id', 'purchase_history.user_id')
                            ->whereColumn('mph2.id', '<', 'purchase_history.id');
                    });
            } else {
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) = 1');
                })->where('product_type', '!=', 'business_support');
            }
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!$isLifetime)
            $query->whereBetween('created_at', $timeRange);

        // Add search functionality (same as logs function)
        if (!empty($search)) {
            $matchingUserIds = UserData::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->pluck('uid')->toArray();

            // Add search conditions
            $query->where(function ($q) use ($search, $matchingUserIds) {
                // Match on purchase_history fields
                $q->where('user_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('subscription_id', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");

                // Add matching user_ids from name/email search
                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('user_id', $matchingUserIds);
                }
            });
        }

        $transactions = $query->orderByDesc('id')->get();

        $determineSource = function ($t) {
            if ($t->is_e_mandate == 1)
                return 'E-Mandate';
            if ($t->fbc && $t->gclid)
                return 'Meta-Google';
            if ($t->gclid && !$t->fbc)
                return 'Google';
            if ($t->fbc && !$t->gclid)
                return 'Meta';
            if ($t->by_sales_team == 1)
                return 'Sales';
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
            $start = $transactions->min('created_at') ?? now()->toDateTime();
            $end = $transactions->max('created_at') ?? now()->toDateTime();
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
            ];

        })->values();

        // 5. Income Types (Filter out zero values) - Only Sources
        $incomeTypes = collect($sources)
            ->filter(fn($s) => $s['revenue'] > 0)
            ->map(function ($s) {
                return [
                    'name' => $s['name'],
                    'value' => (float) number_format($s['revenue'], 1, '.', ''),
                    'color' => $s['color']
                ];
            })
            ->values();

        // 6. Buyer Types Aggregation - Separate Object
        // Get buyer types dynamically from $filters array
        $buyerTypeFilter = collect($this->filters)->firstWhere('key', 'buyerType');
        $allowedBuyerTypes = collect($buyerTypeFilter['options'] ?? [])
            ->filter(fn($option) => $option['key'] !== 'all')
            ->keyBy('key')
            ->toArray();

        $buyerTypeGroups = $transactions->groupBy('product_type');
        $buyerTypes = [];

        foreach ($buyerTypeGroups as $type => $group) {
            // Only include buyer types that are in the filters array
            if (!isset($allowedBuyerTypes[$type])) {
                continue;
            }

            $count = $group->count();
            $total = $group->sum($this->sumKey);

            $typeOption = $allowedBuyerTypes[$type];

            $buyerTypes[] = [
                'name' => $typeOption['value'],
                'count' => $count,
                'value' => (float) number_format($total, 1, '.', ''),
                'color' => $typeOption['color'] ?? '#9CA3AF'
            ];
        }

        // 7. Sales Users Aggregation - Similar to Buyer Types
        // Get sales users from filters
        $filtersWithSales = $this->getFilterWithSales();
        $salesUserFilter = collect($filtersWithSales)->firstWhere('key', 'sales_user');
        $allowedSalesUsers = collect($salesUserFilter['options'] ?? [])
            ->filter(fn($option) => $option['key'] !== 'all')
            ->keyBy('key')
            ->toArray();

        // Filter transactions where by_sales_team = 1
        $salesTransactions = $transactions->where('by_sales_team', 1);
        $salesUserGroups = $salesTransactions->groupBy('emp_id');
        $salesUsers = [];

        // Define colors for sales users
        $salesColors = ['#8E24AA', '#E91E63', '#FF5722', '#FF9800', '#FFC107', '#4CAF50', '#00BCD4', '#2196F3', '#3F51B5', '#9C27B0'];
        $colorIndex = 0;

        foreach ($salesUserGroups as $empId => $group) {
            // Only include sales users that are in the filters array
            if (!isset($allowedSalesUsers[$empId])) {
                continue;
            }

            $count = $group->count();
            $total = $group->sum($this->sumKey);

            $salesUserOption = $allowedSalesUsers[$empId];

            $salesUsers[] = [
                'name' => $salesUserOption['value'],
                'count' => $count,
                'value' => (float) number_format($total, 1, '.', ''),
                'color' => $salesColors[$colorIndex % count($salesColors)]
            ];

            $colorIndex++;
        }

        // 8. Construct Final Response
        // Note: Returning directly matches DashboardResponse interface
        return $this->successed(msg: 'Analytics data fetched successfully', datas: [
            'filters' => $this->getFilterWithSales(),
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
            'buyerTypes' => $buyerTypes,
            'salesUsers' => $salesUsers,
            'recentTransactions' => $recentTransactions,
        ], showDecoded: true);
    }

    public function logs(Request $request): mixed
    {
        $this->checkAuth($request);

        if ($this->employee->user_type !== 1)
            return $this->failed(msg: "Unauthorized");

        $user_id = $request->input('user_id');
        $timeRange = $request->input('time_range', empty($user_id) ? 'today' : 'none');
        $source = $request->input('source', ['all']); // Changed to array with default ['all']
        $buyerType = $request->input('buyerType', ['all']);

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $isEMandate = $request->input('e_mandate', 'all');
        $closeBy = $request->input('close_by', 'all');
        $salesUser = $request->input('sales_user', ['all']);

        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        $paymentStatus = $request->input('payment_status', 'paid'); //all, true, false

        $timeRange = $this->getTimePeriod($timeRange);

        if (is_string($timeRange)) {
            return $this->failed(statusCode: 400, msg: $timeRange);
        }

        $query = MasterPurchaseHistory::with(['userData']);
        if ($user_id)
            $query->whereUserId($user_id);
        if ($paymentStatus != "all")
            $query->wherePaymentStatus($paymentStatus);

        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }
        if (!in_array('all', $source)) {
            $query->where(function ($q) use ($source, $closeBy) {
                foreach ($source as $src) {
                    switch ($src) {
                        case "google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('gclid')
                                    ->whereNull('fbc');
                            });
                            break;
                        case "meta":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                        case "meta-google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNotNull('gclid');
                            });
                            break;
                        case "seo":
                            $q->orWhere(function ($subQuery) {
                                // Fixed: Decoupled SEO source from sales filter for log visibility.
                                $subQuery->whereNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                    }
                }
            });
        }

        if ($isEMandate !== "all") {
            $query->where('is_e_mandate', $isEMandate === 'true' ? '!=' : '=', 0);
        }

        if ($closeBy != 'all') {
            $query->where('by_sales_team', $closeBy === 'true' ? '!=' : '=', 0);
            if ($closeBy) {
                if (!in_array('all', $salesUser)) {
                    $query->whereIn('emp_id', $salesUser);
                }
            }
        }

        if ($return_user !== "all") {

            if ($return_user === 'true') {
                // Repeat users → exclude first entry
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) > 1');
                })->where('product_type', '!=', 'business_support')
                    ->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('purchase_history as mph2')
                            ->where('product_type', '!=', 'business_support')
                            ->whereColumn('mph2.user_id', 'purchase_history.user_id')
                            ->whereColumn('mph2.id', '<', 'purchase_history.id');
                    });

            } else {
                // Only users having exactly one entry
                $query->whereIn('user_id', function ($sub) {
                    $sub->select('user_id')
                        ->from('purchase_history')
                        ->where('product_type', '!=', 'business_support')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) = 1');
                })->where('product_type', '!=', 'business_support');
            }
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!empty($timeRange))
            $query->whereBetween('created_at', $timeRange);

        $limit = 20;
        if (!empty($search)) {
            $matchingUserIds = UserData::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->pluck('uid')->toArray();

            // Add search conditions
            $query->where(function ($q) use ($search, $matchingUserIds) {
                // Match on purchase_history fields
                $q->where('user_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('subscription_id', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");

                // Add matching user_ids from name/email search
                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('user_id', $matchingUserIds);
                }
            });
        }

        $totalIncome = $query->sum($this->sumKey);
        $transactions = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        $determineSource = function ($t) {
            if ($t->is_e_mandate == 1)
                return 'E-Mandate';
            if ($t->fbc && $t->gclid)
                return 'Meta-Google';
            if ($t->gclid && !$t->fbc)
                return 'Google';
            if ($t->fbc && !$t->gclid)
                return 'Meta';
            if ($t->by_sales_team == 1)
                return 'Sales';
            return 'SEO';
        };

        // 1. Summary Metrics
        $collection = $transactions->getCollection();


        $transactionList = $collection->map(function ($t) use ($determineSource) {
            $addons = null;
            if ($t->order && empty($t->order->raw_notes)) {
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
                'addons' => $addons,
                'date' => $t->created_at->format('Y-m-d H:i:s'),
                'amount' => $t[$this->sumKey],
                'status' => $t->status == 1 ? 'Completed' : 'Refunded',
                'type' => $t->product_type,
                'source' => $determineSource($t),
                'avatar' => $t->userData?->photo_uri ?? "",
                'payment_status' => $t->payment_status,
                'url' => strtok($t->url, '?'),
            ];

        })->values();

        try {

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

            return $this->successed(msg: 'Transaction logs fetched successfully', datas: [
                'filters' => $filters->values()->toArray(),
                'meta' => [
                    'currency' => 'INR',
                    'currencySymbol' => '₹',
                    'generatedAt' => now()->toIso8601String()
                ],
                'totalIncome' => $totalIncome,
                'transactions' => $transactionList,
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->failed(msg: 'Something went wrong');
        }
    }

    public function e_mandates(Request $request): array|string
    {
        $this->checkAuth($request);

        if ($this->employee->user_type !== 1)
            return $this->failed(msg: "Unauthorized");

        $timeRange = $request->input('time_range', 'today');
        $source = $request->input('source', ['all']); // Changed to array with default ['all']
        $buyerType = $request->input('buyerType', ['all']);

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $closeBy = $request->input('close_by', 'all');
        $salesUser = $request->input('sales_user', ['all']);

        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        $timeRange = $this->getTimePeriod($timeRange, true);

        if (is_string($timeRange)) {
            return $this->failed(statusCode: 400, msg: $timeRange);
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
                [((int) $return_user)]
            );
        }

        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }
        if (!in_array('all', $source)) {
            $query->where(function ($q) use ($source, $closeBy) {
                foreach ($source as $src) {
                    switch ($src) {
                        case "google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('gclid')
                                    ->whereNull('fbc');
                            });
                            break;
                        case "meta":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                        case "meta-google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNotNull('gclid');
                            });
                            break;
                        case "seo":
                            if ($closeBy != 'true') {
                                $q->orWhere(function ($subQuery) {
                                    $subQuery->whereNull('fbc')
                                        ->whereNull('gclid')
                                        ->where('by_sales_team', 0);
                                });
                            }
                            break;
                    }
                }
            });
        }

        if ($closeBy != 'all') {
            $query->where('by_sales_team', $closeBy === 'true' ? '!=' : '=', 0);
            if ($closeBy) {
                if (!in_array('all', $salesUser)) {
                    $query->whereIn('emp_id', $salesUser);
                }
            }
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!empty($timeRange))
            $query->whereBetween('expired_at', $timeRange);

        // Add search functionality (same as logs function)
        if (!empty($search)) {
            $matchingUserIds = UserData::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->pluck('uid')->toArray();

            // Add search conditions
            $query->where(function ($q) use ($search, $matchingUserIds) {
                // Match on purchase_history fields
                $q->where('user_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('subscription_id', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");

                // Add matching user_ids from name/email search
                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('user_id', $matchingUserIds);
                }
            });
        }

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
            if ($t->is_e_mandate == 1)
                return 'E-Mandate';
            if ($t->fbc && $t->gclid)
                return 'Meta-Google';
            if ($t->gclid && !$t->fbc)
                return 'Google';
            if ($t->fbc && !$t->gclid)
                return 'Meta';
            if ($t->by_sales_team == 1)
                return 'Sales';
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
                [(int) $return_user]
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


        $filters = $this->getFilterWithSales()->keyBy('key')->except(['e_mandate']);

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

        return $this->successed(msg: 'E-mandate data fetched successfully', datas: [
            'filters' => $filters->values()->toArray(),
            'meta' => [
                'currency' => 'INR',
                'currencySymbol' => '₹',
                'generatedAt' => now()->toIso8601String()
            ],
            'summary' => $extra,
            'totalIncome' => $totalIncome,
            'transactions' => $transactionList,
            'pagination' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
            "timeRange" => $timeRange,
            "a" => $globalStart,
            "b" => $globalEnd,
        ]);
    }

    public function new_subs(Request $request): array|string
    {
        $this->checkAuth($request);
        try {

            if ($this->employee->user_type !== 1)
                return $this->failed(msg: "Unauthorized");

            // ----------------------------
            // 1️⃣ Update first_amount (only if needed)
            // ----------------------------
            UserSubscriptions::where('first_amount', 0)
                ->chunkById(100, function ($datas) {
                    foreach ($datas as $data) {
                        $firstAmount = MasterPurchaseHistory::whereSubscriptionId($data->gateway_subscription_id)
                            ->oldest()
                            ->value('paid_amount');

                        if ($firstAmount) {
                            $data->update(['first_amount' => $firstAmount]);
                        }
                    }
                });

            // ----------------------------
            // 2️⃣ Get Filters From Request
            // ----------------------------
            $timeRangeInput = $request->input('time_range', 'today');
            $source = $request->input('source', ['all']);
            $buyerType = $request->input('buyerType', ['all']);
            $salesUserId = $request->input('sales_user', ['all']);
            $return_user = $request->input('return_user', 'all');
            $page = $request->input('page', 1);

            $timeRange = $this->getTimePeriod($timeRangeInput);

            if (is_string($timeRange)) {
                return $this->failed(statusCode: 400, msg: $timeRange);
            }

            // ----------------------------
            // 3️⃣ Base Query With All Filters
            // ----------------------------
            $baseQuery = UserSubscriptions::query();

            if (!empty($timeRange)) {
                $baseQuery->whereBetween('created_at', $timeRange);
            }

            $baseQuery = $this->applySourceFilter($baseQuery, $source);
            $baseQuery = $this->applyBuyerTypeFilter($baseQuery, $buyerType);
            if ($return_user !== "all") {
                $baseQuery->where('paid_count', $return_user === 'true' ? '>' : '=', 1);
            }
            //            $baseQuery = $this->applyReturnUserFilter($baseQuery, $return_user);

            // ----------------------------
            // 4️⃣ Counts & Revenue
            // ----------------------------
            $total = (clone $baseQuery)->count();
            $active = (clone $baseQuery)->whereIn('status', ['active', 'authenticated'])->count();
            $cancelled = (clone $baseQuery)->where('status', 'cancelled')->count();
            $paused = (clone $baseQuery)->where('status', 'paused')->count();
            $halted = (clone $baseQuery)->where('status', 'halted')->count();

            // Calculate maximum possible paid_count based on TIME RANGE filter
            // Always show minimum 6 payment times (paid_count 2-7)
            // No maximum limit - show all possible payment times
            if (!empty($timeRange) && is_array($timeRange) && count($timeRange) == 2) {
                $startDate = Carbon::parse($timeRange[0]);
                $endDate = Carbon::parse($timeRange[1]);
                $daysBetween = $startDate->diffInDays($endDate);

                // First payment after 7 days, then monthly (30 days)
                // Calculate possible payments based on time range
                if ($daysBetween < 7) {
                    $maxPossiblePayments = 0; // Only initiate
                } else {
                    $maxPossiblePayments = floor(($daysBetween - 7) / 30) + 1;
                }

                // paid_count = 1 (initiate) + payments
                // Minimum 6 payment times (paid_count 2-7), no maximum
                $maxPaidCount = max(7, 1 + $maxPossiblePayments);
            } else {
                // No time range filter - show all possible payment times
                // Calculate from first data date to current date
                $firstDataDate = (clone $baseQuery)->min('created_at');
                if ($firstDataDate) {
                    $firstDate = Carbon::parse($firstDataDate);
                    $currentDate = Carbon::now();
                    $daysBetween = $firstDate->diffInDays($currentDate);

                    if ($daysBetween < 7) {
                        $maxPossiblePayments = 0;
                    } else {
                        $maxPossiblePayments = floor(($daysBetween - 7) / 30) + 1;
                    }

                    // Minimum 6 payment times, no maximum
                    $maxPaidCount = max(7, 1 + $maxPossiblePayments);
                } else {
                    // No data, show minimum 6 payment times
                    $maxPaidCount = 7;
                }
            }

            $manualRevenueInr = number_format(
                (clone $baseQuery)->where('currency', 'INR')->sum('first_amount'),
                2,
                '.',
                ''
            );

            // Paid count breakdown (dynamic based on actual data)
            $counts = [];
            $revenues = [];

            // Start from paid_count = 2 and go up to the maximum paid_count found
            for ($i = 2; $i <= $maxPaidCount; $i++) {
                $counts[$i] = (clone $baseQuery)->where('paid_count', '>=', $i)->count();

                $revenues[$i] = number_format(
                    (clone $baseQuery)
                        ->where('paid_count', '>=', $i)
                        ->where('currency', 'INR')
                        ->sum('amount'),
                    2,
                    '.',
                    ''
                );
            }

            // ----------------------------
            // 5️⃣ Summary Response
            // ----------------------------

            // Format time range dates for display
            // If no time range filter, show first data date and current date
            if (!empty($timeRange) && is_array($timeRange)) {
                $timeRangeStartDate = date('d M Y', strtotime($timeRange[0]));
                $timeRangeEndDate = date('d M Y', strtotime($timeRange[1]));
            } else {
                // No time range - show first data date and current date
                $firstDataDate = (clone $baseQuery)->min('created_at');
                $timeRangeStartDate = $firstDataDate ? date('d M Y', strtotime($firstDataDate)) : 'N/A';
                $timeRangeEndDate = date('d M Y'); // Current date
            }

            $summary = [
                ["title" => "Time Range Start", "value" => $timeRangeStartDate, "trend" => 0],
                ["title" => "Time Range End", "value" => $timeRangeEndDate, "trend" => 0],
                ["title" => "Total", "value" => $total, "trend" => 0],
                [
                    "title" => "Active",
                    "value" => $active . " - (" . round($active / ($total ?: 1) * 100, 2) . "%)",
                    "trend" => 0
                ],
                ["title" => "Paused", "value" => $paused, "trend" => 0],
                ["title" => "Canceled", "value" => $cancelled, "trend" => 0],
                ["title" => "Halted", "value" => $halted, "trend" => 0],
                ["title" => "Initiate", "value" => (clone $baseQuery)->count() . " - (₹$manualRevenueInr)", "trend" => 0],
            ];

            // Dynamic payment time titles
            $ordinalSuffixes = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth'];

            // Generate payment time entries dynamically
            // Always show at least 6 payment times, no maximum limit
            for ($i = 2; $i <= $maxPaidCount; $i++) {
                $paymentNumber = $i - 1; // Convert paid_count to payment number (2 = First Time, 3 = Second Time, etc.)

                // Generate ordinal title
                if ($paymentNumber <= 10) {
                    $title = $ordinalSuffixes[$paymentNumber - 1] . " Time";
                } else {
                    // For numbers > 10, use numeric format with suffix (11th, 12th, 13th, etc.)
                    $suffix = 'th';
                    if ($paymentNumber % 10 == 1 && $paymentNumber % 100 != 11)
                        $suffix = 'st';
                    elseif ($paymentNumber % 10 == 2 && $paymentNumber % 100 != 12)
                        $suffix = 'nd';
                    elseif ($paymentNumber % 10 == 3 && $paymentNumber % 100 != 13)
                        $suffix = 'rd';
                    $title = $paymentNumber . $suffix . " Time";
                }

                $summary[] = [
                    "title" => $title,
                    "value" => $counts[$i] .
                        " - (" . round($counts[$i] / ($total ?: 1) * 100, 2) . "%)" .
                        " - (₹" . $revenues[$i] . ")",
                    "trend" => 0
                ];
            }

            $summary[] = [
                "title" => "Total Revenue",
                "value" => "₹" . (array_sum($revenues) + (float) $manualRevenueInr),
                "trend" => 0
            ];

            // ----------------------------
            // 6️⃣ Response
            // ----------------------------
            $paginatedData = MasterPurchaseHistory::paginate(100, ['*'], 'page', $page);

            return $this->successed(msg: 'Subscription data fetched successfully', datas: [
                'filters' => collect($this->filters)->keyBy('key')->except(['e_mandate', 'close_by'])->values()->toArray(),
                'meta' => [
                    'currency' => 'INR',
                    'currencySymbol' => '₹',
                    'generatedAt' => now()->toIso8601String()
                ],
                'summary' => $summary,
                'pagination' => [
                    'total' => $paginatedData->total(),
                    'per_page' => $paginatedData->perPage(),
                    'current_page' => $paginatedData->currentPage(),
                    'last_page' => $paginatedData->lastPage(),
                    'from' => $paginatedData->firstItem(),
                    'to' => $paginatedData->lastItem(),
                ],
                'timeRange' => $timeRange,
            ]);

        } catch (\Exception $e) {
            return $this->failed(statusCode: 500, msg: $e->getMessage());
        }
    }

    public function getFilterWithSales(): Collection
    {
        $filters = collect($this->filters)->keyBy('key');

        $roleIds = collect([
            UserRole::SALES_MANAGER,
            UserRole::SALES,
        ])->map(fn($role) => $role->id());

        $options = User::whereIn('user_type', $roleIds)
            ->whereStatus(1)
            ->get()
            ->map(fn($user) => [
                'key' => (string) $user->id,
                'value' => $user->name
            ])
            ->prepend([
                'key' => 'all',
                'value' => 'All'
            ])
            ->values()
            ->toArray();

        $filters['sales_user'] = [
            'key' => 'sales_user',
            'label' => 'Sales By',
            'defaultValue' => 'all',
            'options' => $options
        ];

        return $filters->values();
    }

    private function applySourceFilter($query, $source)
    {
        if (!in_array('all', $source)) {
            $query->where(function ($q) use ($source) {
                foreach ($source as $src) {
                    switch ($src) {

                        case "google":
                            $q->orWhere(
                                fn($sq) => $sq->whereNotNull('gclid')
                                    ->whereNull('fbc')
                            );
                            break;

                        case "meta":
                            $q->orWhere(
                                fn($sq) => $sq->whereNotNull('fbc')
                                    ->whereNull('gclid')
                            );
                            break;

                        case "meta-google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNotNull('gclid');
                            });
                            break;
                        case "seo":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                    }
                }
            });
        }
        return $query;
    }

    private function applyBuyerTypeFilter($query, $buyerType)
    {
        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }
        return $query;
    }

    private function applyReturnUserFilter($query, $return_user)
    {
        if ($return_user !== 'all') {
            $query->where('paid_count', '>=', 2);
        }
        return $query;
    }

    public function top_users(Request $request): array|string
    {
        $this->checkAuth($request);

        if ($this->employee->user_type !== 1)
            return $this->failed(msg: "Unauthorized");

        $page = $request->input('page', 1);
        $sortKey = $request->input('sort_key', 'amount');
        $sortBy = $request->input('sort_by', 'desc');
        $search = $request->input('search', '');

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
        ");

        // Add search functionality (same as logs function)
        if (!empty($search)) {
            $matchingUserIds = UserData::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");
            })->pluck('uid')->toArray();

            // Add search conditions
            $transactions->where(function ($q) use ($search, $matchingUserIds) {
                // Match on purchase_history fields
                $q->where('user_id', 'like', "%{$search}%");

                // Add matching user_ids from name/email/contact search
                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('user_id', $matchingUserIds);
                }
            });
        }

        $transactions = $transactions->groupBy('user_id')
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
            ];

        })->values();

        return $this->successed(msg: 'Top users fetched successfully', datas: [
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
            'pagination' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }

    public function sales_emp(Request $request): array|string
    {
        $this->checkAuth($request);

        if (!in_array($this->employee->user_type, [1, UserRole::SALES_MANAGER->id()]))
            return $this->failed(msg: "Unauthorized");

        try {
            if ($request->has('emp_id')) {
                return $this->sales_emp_transaction($request);
            }
            $timeRange = $request->input('time_range', 'today');
            $page = $request->input('page', 1);
            $sortKey = $request->input('sort_key', 'amount');
            $sortBy = $request->input('sort_by', 'desc');

            $timeRange = $this->getTimePeriod($timeRange);

            if (is_string($timeRange)) {
                return $this->failed(statusCode: 400, msg: $timeRange);
            }

            $isLifetime = empty($timeRange);

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

            $query = MasterPurchaseHistory::with('employee')
                ->select('emp_id')
                ->where('emp_id', '!=', 0)
                ->where('by_sales_team', 1)
                ->selectRaw("
                    MAX(id) as id,
                    SUM($this->sumKey) as total_amount,
                    COUNT(*) as total_orders,
                    MAX(total_purchases) as total_purchases,
                    MAX(created_at) as created_at
                ");

            if (!$isLifetime)
                $query->whereBetween('created_at', $timeRange);

            $transactions = $query->groupBy('emp_id')
                ->orderBy($sortKeys[$sortKey], $sortBy)
                ->paginate($limit, ['*'], 'page', $page);

            $collection = $transactions->getCollection();

            $transactionList = $collection->map(function ($t) {
                /** @var MasterPurchaseHistory $t */

                return [
                    'id' => $t->id,
                    'user_id' => $t->employee?->email ?? 'Unknown',
                    'name' => $t->employee?->name ?? 'Unknown',
                    'email' => $t->employee?->email ?? 'Unknown',
                    'total_orders' => $t->total_orders,
                    'amount' => "₹" . number_format($t->total_amount, 2, '.', ''),
                    'avatar' => '',
                    'last_purchase' => $t->created_at->format('Y-m-d H:i:s'),
                    'is_sub_active' => false,
                ];

            })->values();

            return $this->successed(msg: 'Top users fetched successfully', datas: [
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
                ],
                'top_users' => $transactionList,
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return $this->failed(msg: 'Something went wrong');
        }
    }

    private function sales_emp_transaction(Request $request): array|string
    {

        $emp_id = $request->input('emp_id');
        $timeRange = $request->input('time_range', empty($emp_id) ? 'today' : 'none');
        $source = $request->input('source', ['all']); // Changed to array with default ['all']
        $buyerType = $request->input('buyerType', ['all']);

        $return_user = $request->input('return_user', 'all'); //all, true, false
        $usedPromoCode = $request->input('promocode', 'all'); //all, true, false

        $isEMandate = $request->input('e_mandate', 'all');
        $closeBy = $request->input('close_by', 'all');
        $salesUser = $request->input('sales_user', ['all']);

        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        $timeRange = $this->getTimePeriod($timeRange);

        if (is_string($timeRange)) {
            return $this->failed(statusCode: 400, msg: $timeRange);
        }

        $employee = User::whereEmail($emp_id)->first();
        if (!$employee)
            return $this->failed(statusCode: 400, msg: "Not found");

        $emp_id = $employee->id;

        $query = MasterPurchaseHistory::with(['userData'])->wherePaymentStatus('paid')->whereEmpId($emp_id);

        if (!in_array('all', $buyerType)) {
            $query->whereIn('product_type', $buyerType);
        }
        if (!in_array('all', $source)) {
            $query->where(function ($q) use ($source, $closeBy) {
                foreach ($source as $src) {
                    switch ($src) {
                        case "google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('gclid')
                                    ->whereNull('fbc');
                            });
                            break;
                        case "meta":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNull('gclid');
                            });
                            break;
                        case "meta-google":
                            $q->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('fbc')
                                    ->whereNotNull('gclid');
                            });
                            break;
                        case "seo":
                            if ($closeBy != 'true') {
                                $q->orWhere(function ($subQuery) {
                                    $subQuery->whereNull('fbc')
                                        ->whereNull('gclid')
                                        ->where('by_sales_team', 0);
                                });
                            }
                            break;
                    }
                }
            });
        }

        if ($isEMandate !== "all") {
            $query->where('is_e_mandate', $isEMandate === 'true' ? '!=' : '=', 0);
        }

        if ($closeBy != 'all') {
            $query->where('by_sales_team', $closeBy === 'true' ? '!=' : '=', 0);
            if ($closeBy) {
                if (!in_array('all', $salesUser)) {
                    $query->whereIn('emp_id', $salesUser);
                }
            }
        }

        if ($return_user !== "all") {
            $query->where('total_purchases', $return_user === 'true' ? '>' : '=', 1);
        }

        if ($usedPromoCode !== "all") {
            $query->where('promo_code_id', $usedPromoCode === 'true' ? '!=' : '=', 0);
        }

        if (!empty($timeRange))
            $query->whereBetween('created_at', $timeRange);

        $limit = 20;
        if (!empty($search)) {
            $matchingUserIds = UserData::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->pluck('uid')->toArray();

            // Add search conditions
            $query->where(function ($q) use ($search, $matchingUserIds) {
                // Match on purchase_history fields
                $q->where('user_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('subscription_id', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");

                // Add matching user_ids from name/email search
                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('user_id', $matchingUserIds);
                }
            });
        }

        $totalIncome = $query->sum($this->sumKey);
        $transactions = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        $determineSource = function ($t) {
            if ($t->is_e_mandate == 1)
                return 'E-Mandate';
            if ($t->fbc && $t->gclid)
                return 'Meta-Google';
            if ($t->gclid && !$t->fbc)
                return 'Google';
            if ($t->fbc && !$t->gclid)
                return 'Meta';
            if ($t->by_sales_team == 1)
                return 'Sales';
            return 'SEO';
        };

        // 1. Summary Metrics
        $collection = $transactions->getCollection();


        $transactionList = $collection->map(function ($t) use ($determineSource) {
            $addons = null;
            if ($t->order && empty($t->order->raw_notes)) {
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
                'addons' => $addons,
                'date' => $t->created_at->format('Y-m-d H:i:s'),
                'amount' => $t[$this->sumKey],
                'status' => $t->status == 1 ? 'Completed' : 'Refunded',
                'type' => $t->product_type,
                'source' => $determineSource($t),
                'avatar' => $t->userData?->photo_uri ?? "",
            ];

        })->values();

        try {
            return $this->successed(msg: 'Transaction logs fetched successfully', datas: [
                'filters' => $this->filters,
                'meta' => [
                    'currency' => 'INR',
                    'currencySymbol' => '₹',
                    'generatedAt' => now()->toIso8601String()
                ],
                'totalIncome' => $totalIncome,
                'transactions' => $transactionList,
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->failed(msg: 'Something went wrong');
        }

    }

    /**
     * Get add-on analytics for old_sub product type
     * Returns statistics about add-ons usage and average order values
     */
    public function add_on_analytics(Request $request): array|string
    {
        /*
        |--------------------------------------------------------------------------
        | Authentication & Authorization
        |--------------------------------------------------------------------------
        */
        $this->checkAuth($request);

        try {
            // Validate admin access before analytics execution
            if ($this->employee->user_type !== 1) {
                return $this->failed(msg: "Unauthorized");
            }

            /*
            |--------------------------------------------------------------------------
            | Apply Request Filters
            |--------------------------------------------------------------------------
            */
            $timeRangeFilter = $request->input('time_range', 'today');
            $sourceFilter = $request->input('source', 'all');
            $salesCloseFilter = $request->input('close_by', 'all');
            $salesUserFilter = $request->input('sales_user', ['all']);
            $returnUserFilter = $request->input('return_user', 'all');
            $promoCodeUsageFilter = $request->input('promocode', 'all');

            $parsedTimeRange = $this->getTimePeriod($timeRangeFilter);

            if (is_string($parsedTimeRange)) {
                return $this->failed(statusCode: 400, msg: $parsedTimeRange);
            }

            /*
            |--------------------------------------------------------------------------
            | Build Base Query
            |--------------------------------------------------------------------------
            */
            // Base query - old_sub AND business_support with payment_status = paid
            $purchaseHistoryQuery = MasterPurchaseHistory::with('order')->where('payment_status', 'paid');

            // Apply source-based traffic filtering (Google / Meta / SEO / Mixed)
            if ($sourceFilter !== "all") {
                if ($sourceFilter === "google") {
                    $purchaseHistoryQuery->whereNotNull('gclid')->whereNull('fbc');
                } else if ($sourceFilter === "meta") {
                    $purchaseHistoryQuery->whereNotNull('fbc')->whereNull('gclid');
                } else if ($sourceFilter === "meta-google") {
                    $purchaseHistoryQuery->whereNotNull('fbc')->whereNotNull('gclid');
                } else if ($sourceFilter === "seo") {
                    $purchaseHistoryQuery->whereNull('fbc')->whereNull('gclid');
                }
            }

            if ($salesCloseFilter !== 'all') {
                $purchaseHistoryQuery->where('by_sales_team', $salesCloseFilter === 'true' ? '!=' : '=', 0);
                if ($salesCloseFilter === 'true' && !in_array('all', $salesUserFilter)) {
                    $purchaseHistoryQuery->whereIn('emp_id', $salesUserFilter);
                }
            }

            // Identify repeat customers by excluding first successful purchase
            if ($returnUserFilter !== "all") {
                if ($returnUserFilter === 'true') {
                    $purchaseHistoryQuery->whereIn('user_id', function ($subQuery) {
                        $subQuery->select('user_id')
                            ->from('purchase_history')
                            ->where('product_type', '!=', 'business_support')
                            ->groupBy('user_id')
                            ->havingRaw('COUNT(*) > 1');
                    })->where('product_type', '!=', 'business_support')
                        ->whereExists(function ($subQuery) {
                            $subQuery->select(DB::raw(1))
                                ->from('purchase_history as mph2')
                                ->where('product_type', '!=', 'business_support')
                                ->whereColumn('mph2.user_id', 'purchase_history.user_id')
                                ->whereColumn('mph2.id', '<', 'purchase_history.id');
                        });
                } else {
                    // Only users having exactly one entry
                    $purchaseHistoryQuery->whereIn('user_id', function ($subQuery) {
                        $subQuery->select('user_id')
                            ->from('purchase_history')
                            ->where('product_type', '!=', 'business_support')
                            ->groupBy('user_id')
                            ->havingRaw('COUNT(*) = 1');
                    })->where('product_type', '!=', 'business_support');
                }
            }

            if ($promoCodeUsageFilter !== "all") {
                $purchaseHistoryQuery->where('promo_code_id', $promoCodeUsageFilter === 'true' ? '!=' : '=', 0);
            }

            if (!empty($parsedTimeRange)) {
                $purchaseHistoryQuery->whereBetween('created_at', $parsedTimeRange);
            }

            $totalRevenue = (clone $purchaseHistoryQuery)->sum($this->sumKey);

            /*
            |--------------------------------------------------------------------------
            | Analytics Aggregation
            |--------------------------------------------------------------------------
            */
            // Clone query to fetch all non-paginated data for statistics
            $statisticsQuery = clone $purchaseHistoryQuery;
            $completedPayments = $statisticsQuery->get();

            $usersWithoutAddons = [];
            $addonCounts = [
                'caricatures' => 0,
                'meta_course' => 0,
                'on_demand_service' => 0,
                'business_support' => 0,
            ];
            $addonTotalRevenue = [
                'caricatures' => 0,
                'meta_course' => 0,
                'on_demand_service' => 0,
                'business_support' => 0,
            ];

            $userAggregates = [];

            // Aggregate addon revenue per unique user
            foreach ($completedPayments as $payment) {
                $userId = $payment->user_id;

                if (!isset($userAggregates[$userId])) {
                    $userAggregates[$userId] = [
                        'net_amount' => 0,
                        'addon_revenue' => 0,
                        'addon_count' => 0,
                        'has_addons' => false,
                        'addons' => []
                    ];
                }

                $userAggregates[$userId]['net_amount'] += $payment->net_amount;

                // Handle business_support first (independent addon)
                if (
                    $payment->product_type === 'business_support' &&
                    !isset($userAggregates[$userId]['addons']['business_support'])
                ) {
                    $userAggregates[$userId]['addons']['business_support'] = true;
                    $userAggregates[$userId]['addon_revenue'] += $payment->net_amount;
                    $userAggregates[$userId]['addon_count']++;
                    $userAggregates[$userId]['has_addons'] = true;

                    $addonCounts['business_support']++;
                    $addonTotalRevenue['business_support'] += $payment->net_amount;
                }

                // Handle raw_notes addons
                $rawNotes = $payment->order?->raw_notes;

                if (!empty($rawNotes)) {
                    $rawNotesArray = is_string($rawNotes) ? json_decode($rawNotes, true) : $rawNotes;

                    if (is_array($rawNotesArray)) {
                        $addonMap = [
                            'caricatures' => $this->addOnPrices['caricatures'],
                            'meta_course' => $this->addOnPrices['meta_course'],
                            'on_demand_service' => $this->addOnPrices['on_demand_service'],
                        ];

                        foreach ($addonMap as $addonKey => $addonPrice) {
                            if (!empty($rawNotesArray[$addonKey]) && !isset($userAggregates[$userId]['addons'][$addonKey])) {
                                $userAggregates[$userId]['addons'][$addonKey] = true;
                                $userAggregates[$userId]['addon_revenue'] += $addonPrice;
                                $userAggregates[$userId]['addon_count']++;
                                $userAggregates[$userId]['has_addons'] = true;

                                $addonCounts[$addonKey]++;
                                $addonTotalRevenue[$addonKey] += $addonPrice;
                            }
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Compute Addon Statistics
            |--------------------------------------------------------------------------
            */
            $ordersByAddonCount = array_fill(1, 4, 0);
            $revenueByAddonCount = array_fill(1, 4, 0);
            $addonRevenueOnlyByCount = array_fill(1, 4, 0);

            $totalUsersWithAddons = 0;
            $globalAddonRevenue = 0;

            foreach ($userAggregates as $userId => $userAnalytics) {
                if ($userAnalytics['has_addons']) {
                    $totalUsersWithAddons++;
                    $globalAddonRevenue += $userAnalytics['addon_revenue'];

                    $addonCount = min($userAnalytics['addon_count'], 4); // Cap max grouped addons at 4

                    $ordersByAddonCount[$addonCount]++;
                    $revenueByAddonCount[$addonCount] += $userAnalytics['net_amount'];
                    $addonRevenueOnlyByCount[$addonCount] += $userAnalytics['addon_revenue'];
                } else {
                    $usersWithoutAddons[] = $userAnalytics;
                }
            }

            $totalUniqueUsers = count($userAggregates);
            $averageOrderValue = $totalUniqueUsers > 0 ? $totalRevenue / $totalUniqueUsers : 0;

            $globalAverageAddonValue = $totalUsersWithAddons > 0
                ? $globalAddonRevenue / $totalUsersWithAddons
                : 0;

            // Calculate cumulative addon performance buckets (1+ / 2+ / 3+ / 4+ addons)
            $cumulativeOrderCount = array_fill(1, 4, 0);
            $cumulativeTotalRevenue = array_fill(1, 4, 0);
            $cumulativeAddonRevenue = array_fill(1, 4, 0);

            for ($i = 1; $i <= 4; $i++) {
                for ($j = $i; $j <= 4; $j++) {
                    $cumulativeOrderCount[$i] += $ordersByAddonCount[$j];
                    $cumulativeTotalRevenue[$i] += $revenueByAddonCount[$j];
                    $cumulativeAddonRevenue[$i] += $addonRevenueOnlyByCount[$j];
                }
            }

            $cumulativeAverageFullCartValue = [];
            $cumulativeAverageAddonValue = [];

            for ($i = 1; $i <= 4; $i++) {
                $hasOrders = $cumulativeOrderCount[$i] > 0;
                $cumulativeAverageFullCartValue[$i] = $hasOrders ? $cumulativeTotalRevenue[$i] / $cumulativeOrderCount[$i] : 0;
                $cumulativeAverageAddonValue[$i] = $hasOrders ? $cumulativeAddonRevenue[$i] / $cumulativeOrderCount[$i] : 0;
            }

            $addonPerformanceMetrics = [];
            for ($i = 1; $i <= 4; $i++) {
                $addonPerformanceMetrics[] = [
                    'addon_count' => $i,
                    'order_count' => $cumulativeOrderCount[$i],
                    'total_revenue' => round($cumulativeTotalRevenue[$i], 2),
                    'total_addon_revenue' => round($cumulativeAddonRevenue[$i], 2),
                    'average_value' => round($cumulativeAverageAddonValue[$i], 2),
                    'average_full_cart_value' => round($cumulativeAverageFullCartValue[$i], 2),
                    'percentage' => $totalUsersWithAddons > 0 ? round(($cumulativeOrderCount[$i] / $totalUsersWithAddons) * 100, 2) : 0,
                ];
            }

            // Calculate individual addon averages
            $caricaturesAverage = $addonCounts['caricatures'] > 0 ? $addonTotalRevenue['caricatures'] / $addonCounts['caricatures'] : 0;
            $metaCourseAverage = $addonCounts['meta_course'] > 0 ? $addonTotalRevenue['meta_course'] / $addonCounts['meta_course'] : 0;
            $onDemandServiceAverage = $addonCounts['on_demand_service'] > 0 ? $addonTotalRevenue['on_demand_service'] / $addonCounts['on_demand_service'] : 0;
            $businessSupportAverage = $addonCounts['business_support'] > 0 ? $addonTotalRevenue['business_support'] / $addonCounts['business_support'] : 0;

            /*
            |--------------------------------------------------------------------------
            | Pagination & Response Formatting
            |--------------------------------------------------------------------------
            */
            $page = $request->input('page', 1);
            $limit = 20;

            $paginatedTransactions = $purchaseHistoryQuery->with(['userData'])
                ->orderByDesc('id')
                ->paginate($limit, ['*'], 'page', $page);

            $sourceResolver = function ($transaction) {
                if ($transaction->is_e_mandate == 1)
                    return 'E-Mandate';
                if ($transaction->fbc && $transaction->gclid)
                    return 'Meta-Google';
                if ($transaction->gclid && !$transaction->fbc)
                    return 'Google';
                if ($transaction->fbc && !$transaction->gclid)
                    return 'Meta';
                if ($transaction->by_sales_team == 1)
                    return 'Sales';
                return 'SEO';
            };

            $transactionCollection = $paginatedTransactions->getCollection();
            $formattedTransactions = $transactionCollection->map(function ($transaction) use ($sourceResolver) {
                $appliedAddons = [];

                if ($transaction->product_type === 'business_support') {
                    $appliedAddons[] = "Business Support";
                } else if (!empty($transaction->order?->raw_notes)) {
                    $rawNotes = is_string($transaction->order?->raw_notes)
                        ? json_decode($transaction->order?->raw_notes, true)
                        : $transaction->order?->raw_notes;

                    if (is_array($rawNotes)) {
                        if (!empty($rawNotes['caricatures'])) {
                            $appliedAddons[] = $rawNotes['caricatures'] . " Caricatures";
                        }
                        if (!empty($rawNotes['meta_course'])) {
                            $appliedAddons[] = "Meta Course";
                        }
                        if (!empty($rawNotes['on_demand_service'])) {
                            $appliedAddons[] = "On Demand Service";
                        }
                    }
                }

                return [
                    'id' => $transaction->id,
                    'title' => $transaction->product_id,
                    'user_id' => $transaction->user_id,
                    'name' => $transaction->userData?->name ?? "Unknown",
                    'email' => $transaction->userData?->email ?? "Unknown",
                    'contact_no' => $transaction->contact_no ?? $transaction->userData?->contact_no ?? "--",
                    'transaction_id' => $transaction->transaction_id,
                    'subscription_id' => $transaction->subscription_id,
                    'addons' => !empty($appliedAddons) ? $appliedAddons : null,
                    'addon_count' => count($appliedAddons),
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'amount' => $transaction->net_amount,
                    'status' => $transaction->status == 1 ? 'Completed' : 'Refunded',
                    'type' => $transaction->product_type,
                    'source' => $sourceResolver($transaction),
                    'avatar' => $transaction->userData?->photo_uri ?? "",
                ];
            })->values();

            $addonPenetrationRate = $totalUniqueUsers > 0
                ? round(($totalUsersWithAddons / $totalUniqueUsers) * 100, 2)
                : 0;

            $analyticsResponse = [
                'success' => true,
                'data' => [
                    'overview' => [
                        'total_users' => $totalUniqueUsers,
                        'total_revenue' => round($totalRevenue, 2),
                        'avg_order_value' => round($averageOrderValue, 2),
                        'orders_with_addons' => $totalUsersWithAddons,
                        'orders_without_addons' => count($usersWithoutAddons),
                        'addon_penetration_rate' => $addonPenetrationRate,
                    ],
                    'addon_average_value' => round($globalAverageAddonValue, 2),
                    'by_addon_count' => $addonPerformanceMetrics,
                    'by_addon_type' => [
                        [
                            'addon_name' => 'Caricatures',
                            'addon_key' => 'caricatures',
                            'order_count' => $addonCounts['caricatures'],
                            'total_revenue' => round($addonTotalRevenue['caricatures'], 2),
                            'average_value' => round($caricaturesAverage, 2),
                            'percentage' => $totalUsersWithAddons > 0 ? round(($addonCounts['caricatures'] / $totalUsersWithAddons) * 100, 2) : 0,
                        ],
                        [
                            'addon_name' => 'Meta Course',
                            'addon_key' => 'meta_course',
                            'order_count' => $addonCounts['meta_course'],
                            'total_revenue' => round($addonTotalRevenue['meta_course'], 2),
                            'average_value' => round($metaCourseAverage, 2),
                            'percentage' => $totalUsersWithAddons > 0 ? round(($addonCounts['meta_course'] / $totalUsersWithAddons) * 100, 2) : 0,
                        ],
                        [
                            'addon_name' => 'On Demand Service',
                            'addon_key' => 'on_demand_service',
                            'order_count' => $addonCounts['on_demand_service'],
                            'total_revenue' => round($addonTotalRevenue['on_demand_service'], 2),
                            'average_value' => round($onDemandServiceAverage, 2),
                            'percentage' => $totalUsersWithAddons > 0 ? round(($addonCounts['on_demand_service'] / $totalUsersWithAddons) * 100, 2) : 0,
                        ],
                        [
                            'addon_name' => 'Business Support',
                            'addon_key' => 'business_support',
                            'order_count' => $addonCounts['business_support'],
                            'total_revenue' => round($addonTotalRevenue['business_support'], 2),
                            'average_value' => round($businessSupportAverage, 2),
                            'percentage' => $totalUsersWithAddons > 0 ? round(($addonCounts['business_support'] / $totalUsersWithAddons) * 100, 2) : 0,
                        ],
                    ],
                ],
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'total' => $paginatedTransactions->total(),
                    'per_page' => $paginatedTransactions->perPage(),
                    'current_page' => $paginatedTransactions->currentPage(),
                    'last_page' => $paginatedTransactions->lastPage(),
                    'from' => $paginatedTransactions->firstItem(),
                    'to' => $paginatedTransactions->lastItem(),
                ],
                'filters' => $this->getFilterWithSales()->values()->toArray(),
            ];

            return $this->successed(msg: 'Add-on analytics fetched successfully', datas: $analyticsResponse);
        } catch (\Exception $e) {
            return $this->failed();
        }
    }

}

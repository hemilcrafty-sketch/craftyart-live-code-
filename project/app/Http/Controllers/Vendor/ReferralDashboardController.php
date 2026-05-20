<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\VendorController;
use App\Models\UserData;
use App\Models\Vendor\RevenueHistory;
use App\Models\Vendor\VendorWithdraw;
use App\Models\Vendor\UserBankDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show referral users dashboard with statistics and user list.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        if ($perPage < 1) {
            $perPage = 20;
        }
        $query = $request->get('query', '');
        $statusFilter = $request->get('status', 'all'); // all, active, pending, failed
        $tab = $request->get('tab', 'referrers');
        if (!in_array($tab, ['referrers', 'invited'], true)) {
            $tab = 'referrers';
        }

        // Get unique referrer IDs from referral_user_id field
        $referrerIds = UserData::whereNotNull('referral_user_id')
            ->distinct()
            ->pluck('referral_user_id')
            ->filter() // Remove null/empty values
            ->unique()
            ->values()
            ->toArray();

        // Overall Statistics - Count only referrers that actually exist in the database
        $totalReferralUsers = !empty($referrerIds)
            ? UserData::whereIn('id', $referrerIds)->count()
            : 0;
        $totalInvitedUsers = UserData::whereNotNull('referral_user_id')->count();

        // Get all referrers (users who have referred others)
        // Only query users that actually exist in the database
        $referrersQuery = !empty($referrerIds)
            ? UserData::whereIn('id', $referrerIds)->whereNotNull('id')
            : UserData::whereRaw('1 = 0'); // Empty query if no referrers

        // Apply search filter
        if (!empty($query)) {
            $referrersQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('refer_id', 'LIKE', "%{$query}%")
                    ->orWhere('uid', 'LIKE', "%{$query}%");
            });
        }

        $referrers = null;
        $referrerStats = [];
        if ($tab === 'referrers') {
            $referrers = $referrersQuery->paginate($perPage)->withQueryString();

            foreach ($referrers->items() as $referrer) {
                $invitedCount = UserData::where('referral_user_id', $referrer->id)->count();

                $stats = VendorController::affiliateSummary($referrer->uid, 'affiliate');
                $earnings = (int) ($stats['earnings'] ?? 0);
                $withdrawn = (int) ($stats['withdraw'] ?? 0);
                $available = max(0, $earnings - $withdrawn);

                $recentActivity = RevenueHistory::where('user_id', $referrer->uid)
                    ->where('vendor_type', 'affiliate')
                    ->where('type', '!=', 'withdraw')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();

                $pendingWithdrawals = VendorWithdraw::where('user_id', $referrer->uid)
                    ->where('vendor_type', 'affiliate')
                    ->whereIn('status', ['pending', 'processing'])
                    ->count();

                $referrerStats[$referrer->uid] = [
                    'invited_count' => $invitedCount,
                    'total_earned' => $earnings,
                    'total_withdrawn' => $withdrawn,
                    'available' => $available,
                    'recent_activity' => $recentActivity,
                    'pending_withdrawals' => $pendingWithdrawals,
                ];
            }
        }

        // Overall totals
        $totalEarnings = RevenueHistory::where('vendor_type', 'affiliate')
            ->where('type', '!=', 'withdraw')
            ->sum('vendor_amount');

        $totalWithdrawn = RevenueHistory::where('vendor_type', 'affiliate')
            ->where('type', 'withdraw')
            ->sum('vendor_amount');

        $totalAvailable = max(0, $totalEarnings - $totalWithdrawn);

        // Status filter for invited users
        $invitedUsersQuery = UserData::whereNotNull('referral_user_id');

        if (!empty($query)) {
            $invitedUsersQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('uid', 'LIKE', "%{$query}%")
                    ->orWhereHas('referrer', function ($qr) use ($query) {
                        $qr->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%")
                            ->orWhere('refer_id', 'LIKE', "%{$query}%")
                            ->orWhere('uid', 'LIKE', "%{$query}%");
                    });
            });
        }

        // Filter by referrer if specified
        if ($request->filled('referrer_uid')) {
            $referrerUid = $request->get('referrer_uid');
            $referrer = UserData::where('uid', $referrerUid)->first();
            if ($referrer) {
                $invitedUsersQuery->where('referral_user_id', $referrer->id);
            }
        }

        // belongsTo must include parent key `id` in select or the relation stays empty
        $invitedUsers = null;
        if ($tab === 'invited') {
            $invitedUsers = $invitedUsersQuery
                ->with([
                    'referrer' => function ($q) {
                        $q->select('id', 'uid', 'name', 'email', 'refer_id');
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'invited_page')
                ->withQueryString();
        }

        return view('referral.dashboard', compact(
            'tab',
            'referrers',
            'referrerStats',
            'invitedUsers',
            'totalReferralUsers',
            'totalInvitedUsers',
            'totalEarnings',
            'totalWithdrawn',
            'totalAvailable',
            'query',
            'statusFilter',
            'perPage'
        ));
    }

    /**
     * Show detailed view for a specific referrer.
     */
    public function show($uid)
    {
        $referrer = UserData::where('uid', $uid)->firstOrFail();

        $invitedUsers = UserData::where('referral_user_id', $referrer->id)
            ->with([
                'referrer' => function ($q) {
                    $q->select('id', 'uid', 'name', 'email', 'refer_id');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get referral history
        $referralHistory = RevenueHistory::where('user_id', $uid)
            ->where('vendor_type', 'affiliate')
            ->orderBy('created_at', 'desc')
            ->with(['purchaseHistory', 'purchaseUser'])
            ->paginate(20);

        // Get withdrawals
        $withdrawals = VendorWithdraw::where('user_id', $uid)
            ->where('vendor_type', 'affiliate')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get bank details for this referrer
        $bankDetails = UserBankDetails::where('user_id', $uid)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats
        $stats = VendorController::affiliateSummary($uid, 'affiliate');
        $earnings = (int) ($stats['earnings'] ?? 0);
        $withdrawn = (int) ($stats['withdraw'] ?? 0);
        $available = max(0, $earnings - $withdrawn);

        return view('referral.show', compact(
            'referrer',
            'invitedUsers',
            'referralHistory',
            'withdrawals',
            'bankDetails',
            'earnings',
            'withdrawn',
            'available'
        ));
    }
}

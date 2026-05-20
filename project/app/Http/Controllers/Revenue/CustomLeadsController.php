<?php

namespace App\Http\Controllers\Revenue;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\RoleManager;
use App\Models\Revenue\CustomLead;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class CustomLeadsController extends AppBaseController
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

    public function index(Request $request): Factory|View|Application
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $filters = json_encode([
            [
                "type" => "payment_status",
                "rules" => [
                    [
                        "attribute" => "payment_status",
                        "operator" => "equal_to",
                        "value" => "successful",
                        "data" => [
                            "activeSelectLabel" => "Successful"
                        ]
                    ]
                ]
            ]
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('COSMO_FEED_API_TOKEN',''),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get(env('COSMO_FEED_BASE_URL','')."/api/wallet/list_transactions", [
                'filters' => $filters,
                'sort' => 'null',
                'page' => $page,
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                return view('custom_leads.index', [
                    'transactions' => new LengthAwarePaginator([], 0, 10, 1),
                    'error' => 'Failed to fetch transactions',
                ]);
            }

            $data = $response->json();
            $transactions = $data['data']['data'] ?? [];
            $total = $data['data']['total'] ?? 0;

            // Attach followup data from existing records (don't create new ones)
            $transactionsWithFollowup = $this->attachFollowupData($transactions);

            $paginator = new LengthAwarePaginator(
                $transactionsWithFollowup,
                $total,
                $limit,
                $page,
                [
                    'path' => route('custom_leads.index'),
                    'query' => $request->query(),
                ]
            );

            return view('custom_leads.index', [
                'transactions' => $paginator,
                'followupLabels' => self::FOLLOWUP_LABELS,
            ]);

        } catch (\Exception $e) {
            return view('custom_leads.index', [
                'transactions' => new LengthAwarePaginator([], 0, 10, 1),
                'error' => $e->getMessage(),
                'followupLabels' => self::FOLLOWUP_LABELS,
            ]);
        }
    }

    /**
     * Attach followup data from existing records (don't create new ones)
     */
    private function attachFollowupData(array $transactions): array
    {
        $result = [];

        // Get all cosmofeed IDs from transactions
        $cosmofeedIds = array_filter(array_column($transactions, '_id'));

        // Fetch existing followup records in one query
        $existingLeads = CustomLead::whereIn('cosmofeed_transaction_id', $cosmofeedIds)
            ->get()
            ->keyBy('cosmofeed_transaction_id');

        foreach ($transactions as $transaction) {
            $cosmofeedId = $transaction['_id'] ?? null;

            // Attach followup data if exists, otherwise set defaults
            if ($cosmofeedId && isset($existingLeads[$cosmofeedId])) {
                $customLead = $existingLeads[$cosmofeedId];
                $transaction['followup_call'] = $customLead->followup_call;
                $transaction['followup_note'] = $customLead->followup_note;
                $transaction['followup_label'] = $customLead->followup_label;
                $transaction['emp_id'] = $customLead->emp_id;
                $transaction['local_id'] = $customLead->id;
            } else {
                // No followup record exists - set defaults
                $transaction['followup_call'] = 0;
                $transaction['followup_note'] = null;
                $transaction['followup_label'] = null;
                $transaction['emp_id'] = null;
                $transaction['local_id'] = null;
            }

            $result[] = $transaction;
        }

        return $result;
    }

    /**
     * Update followup for a custom lead
     */
    public function followupUpdate(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $cosmoFeedId = $request->cosmofeed_transaction_id;

        if (!$cosmoFeedId) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required'
            ], 400);
        }

        // Find existing record or prepare to create new one
        $customLead = CustomLead::where('cosmofeed_transaction_id', $cosmoFeedId)->first();

        // If record doesn't exist and user is enabling followup, create it
        if (!$customLead && $request->followup_call == 1) {
            // Fetch transaction details from API to populate the record
            $transactionData = $this->fetchTransactionFromAPI($cosmoFeedId);

            if (!$transactionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found in API'
                ], 404);
            }

            $customLead = new CustomLead();
            $customLead->cosmofeed_transaction_id = $cosmoFeedId;
            $customLead->email = $transactionData['buyerDetails']['email'] ?? null;
            $customLead->phone = $transactionData['buyerDetails']['phone'] ?? null;
            $customLead->country_code = $transactionData['buyerDetails']['countryCode'] ?? null;
            $customLead->amount_paid = $transactionData['amountPaid'] ?? 0;
            $customLead->currency = $transactionData['currency'] ?? 'INR';
            $customLead->product_title = $transactionData['productTitle'] ?? null;
            $customLead->product_quantity = $transactionData['productQuantity'] ?? [];
            $customLead->refund_status = $transactionData['refund'] ?? 'none';
            $customLead->payment_status = $transactionData['status'] ?? 'successful';
            $customLead->transaction_date = $transactionData['date'] ?? now();
        }

        // If trying to update non-existent record with followup_call = 0, just return success
        if (!$customLead) {
            return response()->json([
                'success' => true,
                'message' => 'No followup to remove'
            ]);
        }

        // Check authorization
        $isSalesUser = RoleManager::isSalesEmployee($currentUser->user_type);
        $isAdminOrManager = RoleManager::isAdmin($currentUser->user_type) ||
                           RoleManager::isManager($currentUser->user_type) ||
                           RoleManager::isSalesManager($currentUser->user_type);

        // Admin, Manager, and Sales Manager can always update followup
        if ($isAdminOrManager) {
            // Allow update
        }
        // Sales user can only update if lead is not assigned or assigned to them
        elseif ($isSalesUser) {
            if (!empty($customLead->emp_id) && $customLead->emp_id != 0 && $customLead->emp_id != $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This lead is assigned to another Sales user. Only the assigned user can update followup.'
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update followup'
            ], 403);
        }

        // Update followup
        if ($request->has('followup_call') && $request->followup_call == 0) {
            $customLead->followup_call = 0;
            $customLead->followup_note = null;
            $customLead->followup_label = null;
        } else {
            $customLead->followup_call = 1;
            $customLead->followup_note = $request->followup_note ?? '';
            $customLead->followup_label = $request->followup_label;
        }

        // Set emp_id to current Sales user (take ownership)
        $customLead->emp_id = $currentUser->id;

        $customLead->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully',
            'data' => [
                'followup_call' => $customLead->followup_call,
                'followup_note' => $customLead->followup_note,
                'followup_label' => $customLead->followup_label,
                'emp_id' => $customLead->emp_id,
            ]
        ]);
    }

    /**
     * Fetch transaction details from cosmofeed API by transaction ID
     */
    private function fetchTransactionFromAPI(string $cosmofeedId): ?array
    {
        try {
            // We need to fetch from the API - since we don't have a direct endpoint for single transaction,
            // we'll search through the list. In production, you might want to cache this or use a better approach.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('COSMO_FEED_API_TOKEN',''),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get(env('COSMO_FEED_BASE_URL','')."/api/wallet/list_transactions", [
                'filters' => json_encode([
                    [
                        "type" => "payment_status",
                        "rules" => [
                            [
                                "attribute" => "payment_status",
                                "operator" => "equal_to",
                                "value" => "successful",
                                "data" => [
                                    "activeSelectLabel" => "Successful"
                                ]
                            ]
                        ]
                    ]
                ]),
                'sort' => 'null',
                'page' => 1,
                'limit' => 100, // Fetch more to find the transaction
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $transactions = $data['data']['data'] ?? [];

                // Find the transaction with matching _id
                foreach ($transactions as $transaction) {
                    if (($transaction['_id'] ?? '') === $cosmofeedId) {
                        return $transaction;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ContactUsWeb;
use App\Http\Controllers\Utils\RoleManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContectUsWebControlller extends AppBaseController
{
    public function index(Request $request)
    {
        $searchableFields = [
            ["id" => 'id', "value" => 'id'],
            ["id" => 'name', "value" => 'name'],
            ["id" => 'email', "value" => 'Email'],
            ["id" => 'message', "value" => 'Message'],
            // ["id" => 'ip_address', "value" => 'Ip Address'],
            // ["id" => 'user_agent', "value" => 'User Agent'],
        ];
        
        $ContactUses = $this->applyFiltersAndPagination($request, ContactUsWeb::query(), $searchableFields);
        
        // Followup labels - same as unified leads
        $followupLabels = [
            'interested' => 'Interested',
            'not_interested' => 'Not Interested',
            'callback_later' => 'Callback Later',
            'wrong_number' => 'Wrong Number',
            'no_response' => 'No Response',
            'already_subscribed' => 'Already Subscribed',
            'price_issue' => 'Price Issue',
            'technical_issue' => 'Technical Issue',
            'other' => 'Other'
        ];
        
        return view("contact_us_web.index", compact('ContactUses', 'searchableFields', 'followupLabels'));
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $contactUs = ContactUsWeb::whereId($request->id)->first();

        if (!$contactUs) {
            return response()->json([
                'success' => false,
                'message' => 'Contact record not found'
            ], 404);
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
        // Sales user can only update if not assigned or assigned to them
        elseif ($isSalesUser) {
            if (!empty($contactUs->emp_id) && $contactUs->emp_id != 0 && $contactUs->emp_id != $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This contact is assigned to another Sales user. Only the assigned user can update followup.'
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
            $contactUs->followup_call = 0;
            $contactUs->followup_note = null;
            $contactUs->followup_label = null;
        } else {
            $contactUs->followup_call = 1;
            $contactUs->followup_note = $request->followup_note ?? '';
            $contactUs->followup_label = $request->followup_label;
        }

        // Set emp_id to current user (take ownership)
        $contactUs->emp_id = $currentUser->id;

        $contactUs->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Creator\Designer\DesignSubmission;
use App\Models\NewCategory;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebSocketBroadcastController extends Controller
{
    /**
     * Broadcast order event directly using HTTP API (bypassing Pusher library)
     */
    public static function broadcastOrderCreatedDirect($order)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'orders';
            $event = 'new-order-created';

            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($order->emp_id)) {
                $employee = \App\Models\User::find($order->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }

            // Format plan items for display
            $planItems = $order->plan_items;
            $planItemsDisplay = '-';
            if ($planItems && $planItems->isNotEmpty()) {
                $firstItem = $planItems->first();
                if (is_object($firstItem) && isset($firstItem->string_id)) {
                    $stringIds = $planItems->pluck('string_id')->filter()->toArray();
                    $planItemsDisplay = implode(', ', $stringIds);
                } else {
                    $planItemsDisplay = $firstItem ?? '-';
                }
            }

            // Prepare order data
            $orderData = [
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id ?? 'unknown',
                    'user_name' => $order->user?->name ?? '-',
                    'email' => $order->user?->email ?? '-',
                    'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                    'amount' => $order->amount ?? '0',
                    'amount_with_symbol' => $order->amount_with_symbol ?? '₹' . ($order->amount ?? '0'),
                    'status' => $order->status ?? 'pending',
                    'type' => $order->type ?? 'old_sub',
                    'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'plan_items' => $planItemsDisplay,
                    'is_subscription_active' => $order->isSubscriptionActive(),
                    'email_template_count' => $order->email_template_count ?? 0,
                    'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                    'from_where' => $order->from_where ?? '-',
                    'followup_call' => $order->followup_call ?? 0,
                    'follow_by' => $followByName,
                    'emp_id' => $order->emp_id ?? 0,
                ]
            ];

            $data = json_encode($orderData);

            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);

            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            \Log::info('WebSocketBroadcast: Sending direct HTTP API request', [
                'url' => $url,
                'body_size' => strlen($body)
            ]);

            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Direct HTTP API success', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Direct HTTP API failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $error
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Direct HTTP API exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    /**
     * Broadcast order status change (for removal from pending/failed list)
     */
    public static function broadcastOrderStatusChanged($order, $oldStatus, $newStatus)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'orders';
            $event = 'order-status-changed';

            // Prepare status change data
            $statusData = [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'should_remove' => !in_array($newStatus, ['pending', 'failed']),
            ];

            $data = json_encode($statusData);

            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);

            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            \Log::info('WebSocketBroadcast: Sending status change event', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Status change event sent successfully', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Status change event failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Status change exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    /**
     * Broadcast order followup change (for real-time checkbox and follow_by updates)
     */
    public static function broadcastOrderFollowUpChanged($order)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'orders';
            $event = 'order-followup-changed';

            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($order->emp_id)) {
                $employee = \App\Models\User::find($order->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }

            // Get followup label display text
            $followupLabelDisplay = '';
            if (!empty($order->followup_label)) {
                $labels = \App\Http\Controllers\OrderUserController::FOLLOWUP_LABELS;
                $followupLabelDisplay = $labels[$order->followup_label] ?? $order->followup_label;
            }

            // Prepare followup change data
            $followupData = [
                'order_id' => $order->id,
                'followup_call' => $order->followup_call ?? 0,
                'followup_note' => $order->followup_note ?? '',
                'followup_label' => $order->followup_label ?? '',
                'followup_label_display' => $followupLabelDisplay,
                'follow_by' => $followByName,
                'emp_id' => $order->emp_id ?? 0,
            ];

            $data = json_encode($followupData);

            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);

            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            \Log::info('WebSocketBroadcast: Sending followup change event', [
                'order_id' => $order->id,
                'followup_call' => $order->followup_call,
                'follow_by' => $followByName
            ]);

            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Followup change event sent successfully', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Followup change event failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Followup change exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    public static function broadcastTransactionFollowUpChanged($transaction)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'orders';
            $event = 'transaction-followup-changed';

            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($transaction->emp_id)) {
                $employee = \App\Models\User::find($transaction->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }

            // Get followup label display text
            $followupLabelDisplay = '';
            if (!empty($transaction->followup_label)) {
                $labels = \App\Http\Controllers\RecentExpireController::FOLLOWUP_LABELS;
                $followupLabelDisplay = $labels[$transaction->followup_label] ?? $transaction->followup_label;
            }

            // Prepare followup change data
            $followupData = [
                'transaction_id' => $transaction->id,
                'followup_call' => $transaction->followup_call ?? 0,
                'followup_note' => $transaction->followup_note ?? '',
                'followup_label' => $transaction->followup_label ?? '',
                'followup_label_display' => $followupLabelDisplay,
                'emp_name' => $followByName,
                'emp_id' => $transaction->emp_id ?? 0,
            ];

            $data = json_encode($followupData);

            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);

            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            \Log::info('WebSocketBroadcast: Sending transaction followup change event', [
                'transaction_id' => $transaction->id,
                'followup_call' => $transaction->followup_call,
                'emp_name' => $followByName
            ]);

            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Transaction followup change event sent successfully', [
                    'transaction_id' => $transaction->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Transaction followup change event failed', [
                    'transaction_id' => $transaction->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Transaction followup change exception', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    public static function broadcastWpFeedbackFollowUpChanged($feedbackRequest)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'wp-feedback';
            $event = 'feedback-followup-changed';

            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($feedbackRequest->emp_id)) {
                $employee = \App\Models\User::find($feedbackRequest->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }

            // Get followup label display text
            $followupLabelDisplay = '';
            if (!empty($feedbackRequest->followup_label)) {
                $labels = \App\Http\Controllers\Automation\WpFeedbackController::FOLLOWUP_LABELS;
                $followupLabelDisplay = $labels[$feedbackRequest->followup_label] ?? $feedbackRequest->followup_label;
            }

            // Prepare followup change data
            $followupData = [
                'request_id' => $feedbackRequest->id,
                'followup_call' => $feedbackRequest->followup_call ?? 0,
                'followup_note' => $feedbackRequest->followup_note ?? '',
                'followup_label' => $feedbackRequest->followup_label ?? '',
                'followup_label_display' => $followupLabelDisplay,
                'emp_name' => $followByName,
                'emp_id' => $feedbackRequest->emp_id ?? 0,
            ];

            $data = json_encode($followupData);

            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);

            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            \Log::info('WebSocketBroadcast: Sending wp_feedback followup change event', [
                'request_id' => $feedbackRequest->id,
                'followup_call' => $feedbackRequest->followup_call,
                'emp_name' => $followByName
            ]);

            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: WP Feedback followup change event sent successfully', [
                    'request_id' => $feedbackRequest->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: WP Feedback followup change event failed', [
                    'request_id' => $feedbackRequest->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }

        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: WP Feedback followup change exception', [
                'request_id' => $feedbackRequest->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    /**
     * Notify creator panel (Pusher channel designer-applications) when a public API application is created or resubmitted.
     * Matches resources/views/creator/designer/applications.blade.php: event "new-application".
     */
    public static function broadcastDesignerApplicationCreated($application): bool
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'designer-applications';
            $event = 'new-application';

            $payload = [
                'id' => $application->id,
                'name' => (string) ($application->name ?? ''),
                'email' => (string) ($application->email ?? ''),
                'phone' => (string) ($application->phone ?? ''),
                'city' => (string) ($application->city ?? ''),
                'state' => (string) ($application->state ?? ''),
                'experience' => (string) ($application->experience ?? ''),
                'created_at' => $application->created_at
                    ? $application->created_at->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s'),
            ];

            $data = json_encode($payload);

            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data,
            ]);

            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}",
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                return true;
            }

            \Log::warning('WebSocketBroadcast: designer application event failed', [
                'application_id' => $application->id,
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $error,
            ]);

            return false;
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: designer application exception', [
                'application_id' => $application->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Creator panel: resources/views/creator/designer/design_submissions.blade.php — channel "design-submissions", event "new-submission".
     * Fired when a freelancer submits via POST /api/designer/design/submit (and on reapplied update).
     */
    public static function broadcastDesignSubmissionCreated(DesignSubmission $design): bool
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'design-submissions';
            $event = 'new-submission';

            $payload = self::designSubmissionPanelListPayload($design);

            $data = json_encode($payload);

            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data,
            ]);

            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}",
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                return true;
            }

            \Log::warning('WebSocketBroadcast: design submission created event failed', [
                'design_submission_id' => $design->id,
                'http_code' => $httpCode,
                'response' => $response,
                'curl_error' => $error,
            ]);

            return false;
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: design submission created exception', [
                'design_submission_id' => $design->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Same channel; event "submission-status-changed" — panel reloads when any viewer has the list open.
     */
    public static function broadcastDesignSubmissionStatusChanged(DesignSubmission $design): bool
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');

            $channel = 'design-submissions';
            $event = 'submission-status-changed';

            $payload = self::designSubmissionPanelListPayload($design);

            $data = json_encode($payload);

            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data,
            ]);

            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);

            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}",
            ]);

            $authSignature = hash_hmac('sha256', $stringToSign, $secret);

            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return true;
            }

            \Log::warning('WebSocketBroadcast: design submission status event failed', [
                'design_submission_id' => $design->id,
                'http_code' => $httpCode,
                'response' => $response,
            ]);

            return false;
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: design submission status exception', [
                'design_submission_id' => $design->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Payload for design_submissions.blade.php real-time row (no full page reload).
     *
     * @return array<string, mixed>
     */
    private static function designSubmissionPanelListPayload(DesignSubmission $design): array
    {
        $design->loadMissing(['designer', 'seoDetails']);

        $seo = $design->seoDetails;
        $catId = $seo?->primary_category_id ?? $design->category_id ?? null;
        $catName = '—';
        if ($catId) {
            $catName = NewCategory::query()->where('id', $catId)->value('category_name') ?: '—';
        }

        $cw = $seo?->width ?? $design->width;
        $ch = $seo?->height ?? $design->height;
        $r = $design->ratio;
        $whDisplay = '—';
        if ($cw || $ch) {
            $whDisplay = ($cw ?? '—') . '×' . ($ch ?? '—');
        } elseif ($r !== null && $r !== '') {
            $whDisplay = 'r ' . (is_numeric($r) ? (string) round((float) $r, 3) : (string) $r);
        }

        $titleDisplay = (string) ($seo?->post_name ?? $design->title ?? '');
        $thumbUrl = $design->previewImageUrl() ?? '';

        $designer = $design->designer;
        $designerRowMissing = (bool) ($design->designer_id && !$designer);

        $isFreelancer = false;
        if ($design->designer_id) {
            $isFreelancer = (bool) UserData::query()->where('id', $design->designer_id)->value('creator');
        }

        $categoryId = $design->category_id;
        $categoryIdWarning = (bool) ($categoryId && !NewCategory::query()->where('id', $categoryId)->exists());

        return [
            'id' => $design->id,
            'string_id' => (string) ($design->string_id ?? ''),
            'template_id' => (string) ($design->template_id ?? ''),
            'designer_name' => $designer ? (string) ($designer->name ?? '—') : '—',
            'designer_email' => $designer ? (string) ($designer->email ?? '') : '',
            'designer_id' => (int) ($design->designer_id ?? 0),
            'designer_row_missing' => $designerRowMissing,
            'app_uid' => (string) ($design->user_id ?? ''),
            'category_name' => Str::limit((string) $catName, 24),
            'category_id' => $categoryId,
            'category_id_warning' => $categoryIdWarning,
            'video' => (string) ($design->video ?? ''),
            'wh_display' => $whDisplay,
            'title_display' => $titleDisplay,
            'has_seo' => $seo !== null,
            'thumb_url' => $thumbUrl,
            'status' => (string) ($design->status ?? ''),
            'crafty_design_id' => $design->crafty_design_id,
            'created_at' => $design->created_at
                ? $design->created_at->format('Y-m-d H:i:s')
                : now()->format('Y-m-d H:i:s'),
            'created_display' => $design->created_at
                ? $design->created_at->format('d M Y')
                : now()->format('d M Y'),
            'edit_url' => route('designer_system.design.edit', $design->id),
            'seo_url' => route('designer_system.design.seo', $design->id),
            'is_freelancer' => $isFreelancer,
        ];
    }
}

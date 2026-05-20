<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Utils\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        try {

            $userId = $request->header('X-User-Id');

            if (!$userId) {
                return response()->json(['error' => 'User ID required'], 403);
            }

            $socketId = $request->input('socket_id');
            $channelName = $request->input('channel_name');

            if (!$socketId || !$channelName) {
                return response()->json(['error' => 'Socket ID and channel name required'], 403);
            }

            if (!$this->validateChannelAccess($userId, $channelName)) {
                return response()->json(['error' => 'Channel access denied'], 403);
            }

            // Generate authentication response
            $authResponse = $this->generateAuthResponse($socketId, $channelName);

            return response()->json($authResponse);
        } catch (Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }

    protected function validateChannelAccess($userId, $channelName): bool
    {
        // Only allow private channels
        if (!str_starts_with($channelName, 'private-')) {
            return false;
        }

        $channelParts = explode('-', $channelName, 2);
        $channelType = $channelParts[1] ?? '';

        return $this->validatePrivateChannel($userId, $channelType);
    }

    protected function validatePrivateChannel($userId, $channelType): bool
    {
        if (str_starts_with($channelType, 'user-')) {
            $channelUserId = substr($channelType, strlen('user-'));

            return (string) $userId === (string) $channelUserId;
        }

        if (str_starts_with($channelType, 'session-')) {
            $email = substr($channelType, strlen('session-'));

            return $this->validateSessionChannel($userId, $email);
        }

        return false;
    }

    protected function validateSessionChannel($userId, $email): bool
    {
        $decodedEmail = urldecode($email);

        // Basic validation
        return !empty($decodedEmail);
    }

    /**
     * @throws Exception
     */
    protected function generateAuthResponse($socketId, $channelName): array
    {
        $pusherConfig = config('broadcasting.connections.pusher');
        // Log::info("Pusher Config".json_encode($pusherConfig));
        if (!$pusherConfig) {
            throw new Exception('Pusher configuration not found');
        }

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $pusherConfig['secret']);

        return [
            'auth' => $pusherConfig['key'] . ':' . $signature
        ];
    }
}

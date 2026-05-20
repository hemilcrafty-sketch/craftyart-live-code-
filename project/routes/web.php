<?php

use App\Models\UserData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

// Route::get('/', [App\Http\Controllers\AppController::class, 'index']);

Route::get('/clear-cache', function () {
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::post('/broadcasting/user-auth', function (Request $request) {

    $token = $request->header('Authorization');
    if (!$token) return response()->json(['message' => 'Missing token'], 403);

    $token = str_replace('Bearer ', '', $token);

    $user = UserData::where('api_token', $token)->first();
    if (!$user) return response()->json(['message' => 'Invalid user token'], 403);

    if ("private-user.$user->uid" !== $request->channel_name) {
        return response()->json(['message' => 'Not allowed for this channel'], 403);
    }

    $pusher = new Pusher(
        env('PUSHER_APP_KEY'),
        env('PUSHER_APP_SECRET'),
        env('PUSHER_APP_ID'),
        [
            'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'useTLS' => true,
            'host' => env('PUSHER_HOST'),
            'port' => env('PUSHER_PORT'),
            'scheme' => env('PUSHER_SCHEME'),
        ]
    );

    return $pusher->authorizeChannel($request->channel_name, $request->socket_id);
});

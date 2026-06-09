<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;

class PushNotificationController extends Controller
{
    public function default()
    {
        return response()->json([
            'data' => [
                'body' => PushNotification::DEFAULT_BODY,
                'description' => PushNotification::DEFAULT_DESCRIPTION,
            ],
        ]);
    }
}

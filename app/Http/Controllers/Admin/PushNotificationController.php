<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\PushToken;
use App\Services\ExpoPushNotificationService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function index()
    {
        $notifications = PushNotification::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $activeTokenCount = PushToken::query()
            ->where('provider', 'expo')
            ->whereNull('disabled_at')
            ->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'activeTokenCount' => $activeTokenCount,
            'defaultBody' => PushNotification::DEFAULT_BODY,
            'defaultDescription' => PushNotification::DEFAULT_DESCRIPTION,
        ]);
    }

    public function store(Request $request, ExpoPushNotificationService $pushService)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string', 'max:500'],
            'open_external' => ['sometimes', 'boolean'],
            'link_url' => ['nullable', 'url:http,https', 'max:2000', 'required_if:open_external,1'],
        ]);

        $openExternal = $request->boolean('open_external');

        $notification = PushNotification::query()->create([
            'body' => $validated['body'],
            'description' => $validated['description'],
            'data' => [
                'source' => 'cms',
                'action' => $openExternal ? 'external_url' : 'open_app',
            ] + ($openExternal ? ['url' => $validated['link_url']] : []),
        ]);

        $summary = $pushService->sendBroadcast($notification);

        return redirect()
            ->route('admin.notifications.index')
            ->with('status', sprintf(
                'Notifikacija poslana. Uredjaja: %d, uspjesno: %d, greske: %d.',
                $summary['recipient_count'],
                $summary['success_count'],
                $summary['failure_count'],
            ));
    }
}

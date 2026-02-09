<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogStoryRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin/stories*')) {
            $hasSession = $request->hasSession();
            $session = $hasSession ? $request->session() : null;
            Log::info('story.request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $session?->getId(),
                'session_token' => $session?->token(),
                'posted_token' => $request->input('_token'),
            ]);
        }

        return $next($request);
    }
}

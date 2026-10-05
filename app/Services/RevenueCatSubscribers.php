<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Reads a RevenueCat customer through the REST API (v1, secret key) to confirm a web purchase made
 * with RevenueCat Billing on snovi.fm/pretplata before a voucher code is issued or extended. The
 * browser only sends the anonymous app user id; everything else comes from RevenueCat.
 */
class RevenueCatSubscribers
{
    /** Stores RevenueCat reports for RevenueCat Billing (Stripe-backed) web purchases. */
    private const WEB_STORES = ['rc_billing', 'stripe'];

    public static function configured(): bool
    {
        return filled(config('services.revenuecat.secret_key'));
    }

    /**
     * The customer's active web subscription, the one that ends last.
     *
     * @return array{product_id: string, plan: 'yearly'|'monthly', expires_at: Carbon}|null
     */
    public function activeWebSubscription(string $appUserId): ?array
    {
        if (!self::configured()) {
            throw new RuntimeException('RevenueCat secret key nije podešen (REVENUECAT_SECRET_KEY).');
        }

        $response = Http::timeout(20)
            ->withToken((string) config('services.revenuecat.secret_key'))
            ->acceptJson()
            ->get('https://api.revenuecat.com/v1/subscribers/'.rawurlencode($appUserId));

        if (!$response->successful()) {
            Log::warning('RevenueCat subscriber lookup failed', ['status' => $response->status(), 'appUserId' => $appUserId]);
            throw new RuntimeException('RevenueCat nije dostupan (HTTP '.$response->status().').');
        }

        $allowSandbox = (bool) config('services.revenuecat.allow_sandbox');
        $best = null;

        foreach ((array) $response->json('subscriber.subscriptions', []) as $productId => $subscription) {
            $expiresAt = isset($subscription['expires_date']) ? Carbon::parse($subscription['expires_date']) : null;

            if (!in_array($subscription['store'] ?? null, self::WEB_STORES, true)
                || ($subscription['is_sandbox'] ?? false) && !$allowSandbox
                || !empty($subscription['refunded_at'])
                || !$expiresAt
                || $expiresAt->isPast()) {
                continue;
            }

            if (!$best || $expiresAt->gt($best['expires_at'])) {
                $best = [
                    'product_id' => (string) $productId,
                    'plan' => self::planFor((string) $productId),
                    'expires_at' => $expiresAt,
                ];
            }
        }

        return $best;
    }

    /** snovi_y / snovi_yearly -> yearly, snovi_m / snovi_monthly -> monthly. */
    public static function planFor(string $productId): string
    {
        return preg_match('/(month|_m$|1m)/i', $productId) ? 'monthly' : 'yearly';
    }
}

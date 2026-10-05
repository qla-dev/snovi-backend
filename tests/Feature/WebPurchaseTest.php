<?php

namespace Tests\Feature;

use App\Mail\WebPurchaseVoucher;
use App\Models\GiftCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebPurchaseTest extends TestCase
{
    private const USER = '$RCAnonymousID:abc123';

    protected function setUp(): void
    {
        parent::setUp();

        // The older migrations are MySQL-only; the in-memory sqlite DB only needs gift_codes.
        foreach ([
            '2026_06_16_000001_create_gift_codes_table.php',
            '2026_07_08_000001_add_expires_at_to_gift_codes_table.php',
            '2026_07_09_000001_add_email_to_gift_codes_table.php',
            '2026_10_05_000002_add_web_purchase_columns_to_gift_codes_table.php',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => "database/migrations/{$migration}"]);
        }

        config(['services.revenuecat.secret_key' => 'sk_test', 'services.revenuecat.allow_sandbox' => false]);
        Mail::fake();
        Http::fake([
            'api.revenuecat.com/v1/subscribers/*' => fn () => Http::response(['subscriber' => ['subscriptions' => $this->subscriptions]]),
        ]);
    }

    /** What the fake RevenueCat API returns; tests change it between requests. */
    private array $subscriptions = [];

    private function fakeSubscriber(string $productId, string $expires, bool $sandbox = false, string $store = 'rc_billing'): void
    {
        $this->subscriptions = [
            $productId => ['store' => $store, 'expires_date' => $expires, 'is_sandbox' => $sandbox, 'refunded_at' => null],
        ];
    }

    public function test_purchase_issues_one_code_and_one_email(): void
    {
        $expires = now()->addYear()->startOfSecond();
        $this->fakeSubscriber('snovi_y', $expires->toIso8601String());

        $first = $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER, 'email' => 'Mama@Example.com'])
            ->assertStatus(201)
            ->assertJsonPath('data.plan', 'yearly')
            ->assertJsonPath('data.emailSent', true);

        $code = $first->json('data.code');
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{12}$/', $code);
        $this->assertSame("https://snovi.fm/promo-code/{$code}", $first->json('data.link'));
        Mail::assertSent(WebPurchaseVoucher::class, fn ($mail) => $mail->hasTo('mama@example.com') && $mail->giftCode->code === $code);

        // Refreshing the success page returns the same code and does not email again.
        $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER, 'email' => 'mama@example.com'])
            ->assertStatus(201)
            ->assertJsonPath('data.code', $code);
        Mail::assertSentCount(1);
        $this->assertSame(1, GiftCode::count());

        $html = (new WebPurchaseVoucher(GiftCode::first()))->render();
        $this->assertStringContainsString("https://snovi.fm/promo-code/{$code}", $html);
        $this->assertStringContainsString('Godišnji plan', $html);
    }

    public function test_sandbox_app_store_and_missing_purchases_get_no_code(): void
    {
        $this->fakeSubscriber('snovi_y', now()->addYear()->toIso8601String(), sandbox: true);
        $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER, 'email' => 'a@b.ba'])->assertStatus(404);

        $this->fakeSubscriber('com.snovifm.premium', now()->addYear()->toIso8601String(), store: 'app_store');
        $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER, 'email' => 'a@b.ba'])->assertStatus(404);

        $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER])->assertStatus(422);
        $this->assertSame(0, GiftCode::count());
        Mail::assertNothingSent();
    }

    public function test_monthly_code_redeems_with_real_end_and_follows_renewals(): void
    {
        $firstEnd = now()->addMonth()->startOfSecond();
        $this->fakeSubscriber('snovi_m', $firstEnd->toIso8601String());
        $code = $this->postJson('/api/web-purchases/claim', ['appUserId' => self::USER, 'email' => 'a@b.ba'])->json('data.code');

        $this->postJson('/api/gift-codes/redeem', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('data.planLabel', 'Mjesečni plan')
            ->assertJsonPath('data.ends', $firstEnd->toIso8601String());

        // A month later RevenueCat reports the renewed period; status moves the end forward.
        $renewedEnd = now()->addMonths(2)->startOfSecond();
        $this->travel(32)->days();
        $this->fakeSubscriber('snovi_m', $renewedEnd->toIso8601String());

        $this->postJson('/api/gift-codes/status', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('data.active', true)
            ->assertJsonPath('data.ends', $renewedEnd->toIso8601String());
    }
}

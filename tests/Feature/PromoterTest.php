<?php

namespace Tests\Feature;

use App\Models\GiftCode;
use App\Models\Promoter;
use App\Models\User;
use Tests\TestCase;

class PromoterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The older migrations are MySQL-only; the in-memory sqlite DB only needs these tables.
        foreach ([
            '2026_06_16_000001_create_gift_codes_table.php',
            '2026_07_08_000001_add_expires_at_to_gift_codes_table.php',
            '2026_07_09_000001_add_email_to_gift_codes_table.php',
            '2026_10_05_000002_add_web_purchase_columns_to_gift_codes_table.php',
            '2026_11_03_000001_create_promoters_table.php',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => "database/migrations/{$migration}"]);
        }
    }

    private function promoter(array $attributes = []): Promoter
    {
        return Promoter::create([
            'username' => 'ana.anic',
            'first_name' => 'Ana',
            'last_name' => 'Anić',
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function code(array $attributes = []): GiftCode
    {
        return GiftCode::create([
            'code' => GiftCode::generateUniqueCode(),
            'used' => false,
            'expires_at' => now()->addYear(),
            ...$attributes,
        ]);
    }

    public function test_promoter_page_lists_only_codes_that_can_be_activated(): void
    {
        $promoter = $this->promoter();
        $free = $this->code(['promoter_id' => $promoter->id]);
        $this->code(['promoter_id' => $promoter->id, 'used' => true, 'used_date' => now(), 'email' => 'mama@example.com']);
        $this->code(['promoter_id' => $promoter->id, 'expires_at' => now()->subDay()]);
        $this->code();

        $response = $this->getJson('/api/promoters/Ana.Anic')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ana Anić')
            ->assertJsonPath('data.url', 'https://snovi.fm/promoters/ana.anic')
            ->assertJsonPath('data.codes_total', 3)
            ->assertJsonPath('data.codes_used', 1)
            ->assertJsonCount(1, 'data.codes')
            ->assertJsonPath('data.codes.0.code', $free->code);

        $this->assertStringNotContainsString('mama@example.com', $response->getContent());
    }

    public function test_hidden_or_missing_promoter_is_not_found(): void
    {
        $this->promoter(['is_active' => false]);

        $this->getJson('/api/promoters/ana.anic')->assertNotFound();
        $this->getJson('/api/promoters/nobody')->assertNotFound();
    }

    public function test_code_preview_names_the_promoter(): void
    {
        $promoter = $this->promoter();
        $code = $this->code(['promoter_id' => $promoter->id]);
        $used = $this->code(['used' => true, 'email' => 'tata@example.com']);

        $this->getJson("/api/gift-codes/{$code->code}/preview")
            ->assertOk()
            ->assertJsonPath('data.state', 'valid')
            ->assertJsonPath('data.promoter.name', 'Ana Anić');

        $this->getJson("/api/gift-codes/{$used->code}/preview")
            ->assertOk()
            ->assertJsonPath('data.state', 'used')
            ->assertJsonPath('data.promoter', null)
            ->assertDontSee('tata@example.com');

        $this->getJson('/api/gift-codes/AAAAAA111111/preview')->assertNotFound();
    }

    public function test_admin_assigns_frees_and_generates_codes(): void
    {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->actingAs((new User())->forceFill(['id' => 1]));
        $promoter = $this->promoter();
        $kept = $this->code(['promoter_id' => $promoter->id]);
        $released = $this->code(['promoter_id' => $promoter->id]);
        $used = $this->code(['promoter_id' => $promoter->id, 'used' => true]);
        $assigned = $this->code();
        $webCode = $this->code(['source' => 'web']);

        $this->get(route('admin.promoters.edit', $promoter))
            ->assertOk()
            ->assertSee($assigned->code)
            ->assertDontSee($webCode->code);

        $this->put(route('admin.promoters.update', $promoter), [
            'username' => 'Ana.Anic',
            'first_name' => 'Ana',
            'last_name' => 'Anić',
            'instagram' => '@ana.snovi',
            'is_active' => 1,
            'gift_code_ids' => [$kept->id, $assigned->id, $webCode->id],
            'new_codes' => 2,
        ])->assertRedirect(route('admin.promoters.edit', $promoter));

        $this->assertSame('ana.snovi', $promoter->fresh()->instagram);
        $this->assertSame($promoter->id, $kept->fresh()->promoter_id);
        $this->assertSame($promoter->id, $assigned->fresh()->promoter_id);
        $this->assertSame($promoter->id, $used->fresh()->promoter_id);
        $this->assertNull($released->fresh()->promoter_id);
        $this->assertNull($webCode->fresh()->promoter_id);
        $this->assertSame(5, $promoter->giftCodes()->count());

        $this->get(route('admin.promoters.index'))->assertOk()->assertSee('Ana Anić')->assertSee('@ana.snovi');
        $this->get(route('admin.gift-codes.index'))->assertOk()->assertSee(route('admin.promoters.edit', $promoter));
    }
}

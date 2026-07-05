<?php

namespace Tests\Feature;

use App\Models\Story;
use App\Support\DemoMode;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    protected function tearDown(): void
    {
        DemoMode::setEnabled(false);

        parent::tearDown();
    }

    public function test_demo_mode_can_be_toggled(): void
    {
        $this->assertFalse(DemoMode::isEnabled());

        DemoMode::setEnabled(true);
        $this->assertTrue(DemoMode::isEnabled());

        DemoMode::setEnabled(false);
        $this->assertFalse(DemoMode::isEnabled());
    }

    public function test_first_five_items_are_unlocked_when_demo_mode_is_off(): void
    {
        $story = new Story(['locked' => true]);

        $this->assertTrue(DemoMode::shouldUnlockStory($story, 0));
        $this->assertTrue(DemoMode::shouldUnlockStory($story, 4));
        $this->assertFalse(DemoMode::shouldUnlockStory($story, 5));
    }
}

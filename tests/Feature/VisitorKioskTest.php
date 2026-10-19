<?php

namespace Tests\Feature;

use App\Livewire\VisitorKiosk;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitorKioskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_kiosk_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('OrbitEntry');
        $response->assertSee('Library Suite');
    }

    public function test_card_check_in_creates_active_visit(): void
    {
        Livewire::test(VisitorKiosk::class)
            ->set('cardnumber', 'LIB-9999')
            ->call('processCard')
            ->assertDispatched('play-audio', sound: 'checkin');

        $this->assertDatabaseHas('visits', [
            'cardnumber' => 'LIB-9999',
            'checked_out' => null,
        ]);
    }

    public function test_subsequent_swipe_checks_out_active_visitor(): void
    {
        $visit = Visit::create([
            'cardnumber' => 'LIB-9999',
            'checked_in' => now()->subHours(2),
        ]);

        Livewire::test(VisitorKiosk::class)
            ->set('cardnumber', 'LIB-9999')
            ->call('processCard')
            ->assertDispatched('play-audio', sound: 'checkout');

        $this->assertNotNull($visit->fresh()->checked_out);
    }

    public function test_empty_card_submission_dispatches_error(): void
    {
        Livewire::test(VisitorKiosk::class)
            ->set('cardnumber', '')
            ->call('processCard')
            ->assertDispatched('play-audio', sound: 'error');
    }

    public function test_manual_checkout_action_works(): void
    {
        $visit = Visit::create([
            'cardnumber' => 'LIB-8888',
            'checked_in' => now()->subMinutes(45),
        ]);

        Livewire::test(VisitorKiosk::class)
            ->call('forceCheckout', $visit->id)
            ->assertDispatched('play-audio', sound: 'checkout');

        $this->assertNotNull($visit->fresh()->checked_out);
    }
}

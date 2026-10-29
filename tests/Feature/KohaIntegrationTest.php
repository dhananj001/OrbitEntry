<?php

namespace Tests\Feature;

use App\Livewire\VisitorKiosk;
use App\Models\KohaPatron;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class KohaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure temporary borrowers table exists on Koha connection for testing
        if (! Schema::connection('koha')->hasTable('borrowers')) {
            Schema::connection('koha')->create('borrowers', function ($table) {
                $table->increments('borrowernumber');
                $table->string('cardnumber', 32)->nullable()->unique();
                $table->string('surname')->nullable();
                $table->string('firstname')->nullable();
                $table->string('preferred_name')->nullable();
                $table->string('branchcode', 10)->default('');
                $table->string('categorycode', 10)->default('');
            });
        }
    }

    public function test_koha_patron_is_resolved_and_welcomed_by_name(): void
    {
        // Seed test patron in Koha database
        KohaPatron::create([
            'cardnumber' => 'SW24BCA001',
            'firstname' => 'Ambika',
            'surname' => 'Patil',
            'branchcode' => 'GECW',
            'categorycode' => 'STD',
        ]);

        Livewire::test(VisitorKiosk::class)
            ->set('cardnumber', 'SW24BCA001')
            ->call('processCard')
            ->assertSet('feedbackType', 'checkin')
            ->assertSee('Ambika Patil (Student - GECW) checked in successfully');

        $this->assertDatabaseHas('visits', [
            'cardnumber' => 'SW24BCA001',
            'checked_out' => null,
        ]);
    }

    public function test_graceful_fallback_when_card_is_not_in_koha(): void
    {
        Livewire::test(VisitorKiosk::class)
            ->set('cardnumber', 'GUEST-999')
            ->call('processCard')
            ->assertSet('feedbackType', 'checkin')
            ->assertSee('Visitor #GUEST-999 checked in successfully');

        $this->assertDatabaseHas('visits', [
            'cardnumber' => 'GUEST-999',
            'checked_out' => null,
        ]);
    }

    public function test_visit_model_correctly_relates_to_koha_patron(): void
    {
        KohaPatron::create([
            'cardnumber' => 'SW24CIV001',
            'firstname' => 'Abhinaya',
            'surname' => 'R',
            'branchcode' => 'GECW',
            'categorycode' => 'STD',
        ]);

        $visit = Visit::create([
            'cardnumber' => 'SW24CIV001',
            'checked_in' => now(),
        ]);

        $this->assertNotNull($visit->patron);
        $this->assertEquals('Abhinaya R', $visit->patron->full_name);
        $this->assertEquals('Abhinaya R', $visit->visitor_name);
    }
}

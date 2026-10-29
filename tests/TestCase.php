<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // When running tests, ensure the borrowers table exists in the test database
        if (config('database.connections.koha.driver') === 'sqlite') {
            try {
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
            } catch (\Throwable) {
                // Ignore if connection is unreachable in non-database unit tests
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('koha:import-sample {--file=sample_borrowers.sql : Path to the Koha SQL dump file}')]
#[Description('Import Koha patron sample borrowers into the local development database')]
class ImportSampleKohaPatrons extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = base_path((string) $this->option('file'));

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        // Ensure sqlite file exists if using sqlite
        $connectionConfig = config('database.connections.koha');
        if (($connectionConfig['driver'] ?? '') === 'sqlite') {
            $dbPath = $connectionConfig['database'] ?? database_path('koha.sqlite');
            if (! file_exists($dbPath) && $dbPath !== ':memory:') {
                touch($dbPath);
                $this->info("Created SQLite file at: {$dbPath}");
            }
        }

        $this->info('Preparing Koha borrowers table in secondary database...');

        // Create borrowers table if not present
        Schema::connection('koha')->dropIfExists('borrowers');
        Schema::connection('koha')->create('borrowers', function ($table) {
            $table->increments('borrowernumber');
            $table->string('cardnumber', 32)->nullable()->unique();
            $table->string('surname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('preferred_name')->nullable();
            $table->string('branchcode', 10)->default('');
            $table->string('categorycode', 10)->default('');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->date('dateofbirth')->nullable();
            $table->date('dateenrolled')->nullable();
            $table->date('dateexpiry')->nullable();
        });

        $this->info("Parsing patrons from {$filePath}...");

        $content = file_get_contents($filePath);
        if (! $content) {
            $this->error('Failed to read SQL file.');

            return self::FAILURE;
        }

        // Find the INSERT INTO `borrowers` VALUES statement
        if (! preg_match('/INSERT INTO `borrowers` VALUES\s*(.*?);/s', $content, $matches)) {
            $this->error('Could not locate INSERT INTO `borrowers` statement in SQL file.');

            return self::FAILURE;
        }

        $valuesBlock = $matches[1];

        // Match individual row value sets like (1, '...', '...', ...),
        preg_match_all('/\((.*?)\)(?:,|\s*$)/s', $valuesBlock, $rowMatches);

        $rows = $rowMatches[1] ?? [];
        $importedCount = 0;

        $this->output->progressStart(count($rows));

        DB::connection('koha')->beginTransaction();

        try {
            foreach ($rows as $row) {
                // Parse SQL comma-separated values respecting strings
                $parsed = str_getcsv($row, ',', "'");

                if (count($parsed) < 37) {
                    $this->output->progressAdvance();

                    continue;
                }

                $borrowernumber = (int) trim($parsed[0]);
                $cardnumber = trim($parsed[1]);
                $surname = trim($parsed[2]);
                $firstname = trim($parsed[3]);
                $preferredName = trim($parsed[4] ?? '');
                $branchcode = trim($parsed[35] ?? '');
                $categorycode = trim($parsed[36] ?? '');

                if (empty($cardnumber) || $cardnumber === 'NULL') {
                    $this->output->progressAdvance();

                    continue;
                }

                DB::connection('koha')->table('borrowers')->updateOrInsert(
                    ['cardnumber' => $cardnumber],
                    [
                        'borrowernumber' => $borrowernumber,
                        'surname' => $surname === 'NULL' ? null : $surname,
                        'firstname' => $firstname === 'NULL' ? null : $firstname,
                        'preferred_name' => $preferredName === 'NULL' ? null : $preferredName,
                        'branchcode' => $branchcode === 'NULL' ? '' : $branchcode,
                        'categorycode' => $categorycode === 'NULL' ? '' : $categorycode,
                    ]
                );

                $importedCount++;
                $this->output->progressAdvance();
            }

            DB::connection('koha')->commit();
            $this->output->progressFinish();
            $this->info(" Successfully imported {$importedCount} Koha patrons into the development database!");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::connection('koha')->rollBack();
            $this->output->progressFinish();
            $this->error('Import failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

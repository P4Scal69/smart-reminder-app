<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarkMigrationSeeder extends Seeder
{
    public function run()
    {
        $migrationName = '2025_12_18_111019_create_personal_access_tokens_table';

        if (DB::table('migrations')->where('migration', $migrationName)->exists()) {
            echo "Migration already recorded.\n";
            return;
        }

        $max = DB::table('migrations')->max('batch');
        $batch = $max ? $max : 1;

        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch,
        ]);

        echo "Inserted migration record for {$migrationName} (batch {$batch}).\n";
    }
}

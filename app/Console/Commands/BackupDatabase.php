<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a mysqldump copy of the central SQL database in the storage directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
        $storagePath = storage_path('app/backups');

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $passwordParam = env('DB_PASSWORD') ? '--password="' . env('DB_PASSWORD') . '"' : '';
        $command = sprintf(
            'mysqldump --user="%s" %s --host="%s" "%s" > "%s"',
            env('DB_USERNAME'),
            $passwordParam,
            env('DB_HOST'),
            env('DB_DATABASE'),
            $storagePath . '/' . $filename
        );

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Database backup created successfully: {$filename}");
            \Log::info("Database backup created successfully: {$filename}");
        } else {
            $this->error("Database backup failed.");
            \Log::error("Database backup failed. Command executed: " . preg_replace('/--password=".*?"/', '--password="***"', $command));
        }
    }
}

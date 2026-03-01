<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Safely modifying Enum columns using raw SQL since doctrine/dbal has spotty enum support
        DB::statement("ALTER TABLE activities MODIFY COLUMN status ENUM('pending', 'ongoing', 'completed', 'delayed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the Enum back (warning: will truncate 'delayed' data to empty/default if any exist)
        DB::statement("ALTER TABLE activities MODIFY COLUMN status ENUM('pending', 'ongoing', 'completed') DEFAULT 'pending'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to include 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('maestro','alumno','admin') NOT NULL DEFAULT 'alumno'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('maestro','alumno') NOT NULL DEFAULT 'alumno'");
    }
};

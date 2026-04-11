<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('marked_via')->nullable()->after('notes')->comment('qr|manual');
            $table->foreignId('marked_by')->nullable()->after('marked_via')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['marked_by']);
            $table->dropColumn(['marked_by', 'marked_via']);
        });
    }
};

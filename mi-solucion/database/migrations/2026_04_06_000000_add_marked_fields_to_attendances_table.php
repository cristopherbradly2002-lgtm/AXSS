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
            if (!Schema::hasColumn('attendances', 'marked_via')) {
                $table->string('marked_via')->nullable()->after('attended');
            }
            if (!Schema::hasColumn('attendances', 'marked_by')) {
                $table->unsignedBigInteger('marked_by')->nullable()->after('marked_via');
            }
            if (!Schema::hasColumn('attendances', 'marked_at')) {
                $table->timestamp('marked_at')->nullable()->after('marked_by');
            }
            if (!Schema::hasColumn('attendances', 'marked_at_tz')) {
                $table->string('marked_at_tz')->nullable()->after('marked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'marked_via')) {
                $table->dropColumn('marked_via');
            }
            if (Schema::hasColumn('attendances', 'marked_by')) {
                $table->dropColumn('marked_by');
            }
            if (Schema::hasColumn('attendances', 'marked_at')) {
                $table->dropColumn('marked_at');
            }
            if (Schema::hasColumn('attendances', 'marked_at_tz')) {
                $table->dropColumn('marked_at_tz');
            }
        });
    }
};

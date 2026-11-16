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
        Schema::table('educations', function (Blueprint $table) {
            if (!Schema::hasColumn('educations', 'university_other')) {
                $table->string('university_other', 255)->nullable()->after('university_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educations', function (Blueprint $table) {
            if (Schema::hasColumn('educations', 'university_other')) {
                $table->dropColumn('university_other');
            }
        });
    }
};

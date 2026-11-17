<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'is_residence_exempt')) {
                $table->boolean('is_residence_exempt')->default(false)->after('has_previous_degree');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'is_residence_exempt')) {
                $table->dropColumn('is_residence_exempt');
            }
        });
    }
};

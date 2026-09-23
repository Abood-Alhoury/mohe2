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
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('scientific_production_approved')->nullable()->after('committee_track');
            $table->date('scientific_production_date')->nullable()->after('scientific_production_approved');
            $table->string('scientific_production_decision_no')->nullable()->after('scientific_production_date');
            $table->text('scientific_production_notes')->nullable()->after('scientific_production_decision_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'scientific_production_approved',
                'scientific_production_date',
                'scientific_production_decision_no',
                'scientific_production_notes',
            ]);
        });
    }
};

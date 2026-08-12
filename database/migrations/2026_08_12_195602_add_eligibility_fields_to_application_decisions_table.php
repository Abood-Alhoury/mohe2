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
        Schema::table('application_decisions', function (Blueprint $table) {
            $table->string('eligibility_decision_no', 100)->nullable()->after('application_id');
            $table->date('eligibility_decision_date')->nullable()->after('eligibility_decision_no');
            $table->string('eligibility_file_path', 500)->nullable()->after('eligibility_decision_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_decisions', function (Blueprint $table) {
            $table->dropColumn(['eligibility_decision_no', 'eligibility_decision_date', 'eligibility_file_path']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'eligibility_decision_no')) {
                $table->string('eligibility_decision_no', 100)->nullable()->after('interview_notes');
            }
            if (!Schema::hasColumn('applications', 'eligibility_decision_date')) {
                $table->date('eligibility_decision_date')->nullable()->after('eligibility_decision_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'eligibility_decision_date')) {
                $table->dropColumn('eligibility_decision_date');
            }
            if (Schema::hasColumn('applications', 'eligibility_decision_no')) {
                $table->dropColumn('eligibility_decision_no');
            }
        });
    }
};

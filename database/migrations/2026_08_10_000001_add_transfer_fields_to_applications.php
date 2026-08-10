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
            $table->foreignId('parent_application_id')->nullable()->after('candidate_id')->constrained('applications')->nullOnDelete();
            $table->string('new_uni_request_no', 100)->nullable()->after('work_department');
            $table->date('new_uni_request_date')->nullable()->after('new_uni_request_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['parent_application_id']);
            $table->dropColumn(['parent_application_id', 'new_uni_request_no', 'new_uni_request_date']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // مسار التدريس المعتمد من قبل اللجنة (نظري / تطبيقي)
            $table->string('committee_track')->nullable()->after('status')
                  ->comment('المسار المعتمد من اللجنة: theoretical أو applied');

            // هل قبلت اللجنة احتساب الخبرة التدريسية؟
            $table->boolean('experience_approved')->nullable()->after('committee_track')
                  ->comment('1: خبرة مقبولة ومستوفية للشروط، 0: خبرة غير مقبولة');

            // سبب الرفض في حال عدم الموافقة على التعادل
            $table->text('rejection_reason')->nullable()->after('experience_approved')
                  ->comment('أسباب رفض المعادلة أو ملاحظات اللجنة');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['committee_track', 'experience_approved', 'rejection_reason']);
        });
    }
};
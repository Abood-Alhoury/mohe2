<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        // Seed the standard system application statuses
        $now = now();
        $statuses = [
            ['id' => 1, 'name' => 'مسودة', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'جديد', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'تحت التدقيق الأولي', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'بانتظار الوثائق', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'لجنة عامة', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'بانتظار لجنة إنتاج علمي', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'بانتظار المقابلة', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'بانتظار إصدار القرار', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'تم الصدور', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'مرفوض', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'معلق', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('application_statuses')->insert($statuses);
    }

    public function down(): void
    {
        Schema::dropIfExists('application_statuses');
    }
};

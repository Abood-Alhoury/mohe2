<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        // Seed the 8 required request types
        $now = now();
        $types = [
            ['id' => 1, 'name' => 'ماجستير داخلي - تطبيقي', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'ماجستير داخلي - نظري', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'دكتورة داخلي', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'عضو هيئة تدريسية - سماح', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'ماجستير خارجي - تطبيقي', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'ماجستير خارجي - نظري', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'دكتورة خارجية', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'مسودة', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('application_request_types')->insert($types);
    }

    public function down(): void
    {
        Schema::dropIfExists('application_request_types');
    }
};

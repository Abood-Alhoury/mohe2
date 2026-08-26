<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert current values in applications to integer IDs if needed
        $statusesMap = DB::table('application_statuses')->pluck('id', 'name')->toArray();
        $reqTypesMap = DB::table('application_request_types')->pluck('id', 'name')->toArray();

        $apps = DB::table('applications')->get();
        foreach ($apps as $a) {
            $stVal = $a->status;
            $reqVal = $a->request_type;

            $stId = is_numeric($stVal) ? (int)$stVal : ($statusesMap[$stVal] ?? 2);
            $reqId = is_numeric($reqVal) ? (int)$reqVal : ($reqTypesMap[$reqVal] ?? 1);

            DB::table('applications')->where('id', $a->id)->update([
                'status' => $stId,
                'request_type' => $reqId,
            ]);
        }

        // 2. Drop redundant columns status_id and request_type_id if they exist
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'request_type_id')) {
                $table->dropConstrainedForeignId('request_type_id');
            }
            if (Schema::hasColumn('applications', 'status_id')) {
                $table->dropConstrainedForeignId('status_id');
            }
        });

        // 3. Alter status and request_type to integer foreign keys with default 2
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('status')->default(2)->change()->constrained('application_statuses');
            $table->foreignId('request_type')->nullable()->change()->constrained('application_request_types');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('status', 100)->default('قيد الدراسة')->change();
            $table->string('request_type', 100)->nullable()->change();
            $table->foreignId('request_type_id')->nullable()->constrained('application_request_types');
            $table->foreignId('status_id')->nullable()->constrained('application_statuses');
        });
    }
};

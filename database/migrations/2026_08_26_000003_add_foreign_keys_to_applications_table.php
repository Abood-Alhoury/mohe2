<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('request_type_id')->nullable()->after('application_no')->constrained('application_request_types')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->after('status')->constrained('application_statuses')->nullOnDelete();
        });

        // Fetch all lookup maps
        $requestTypes = DB::table('application_request_types')->pluck('id', 'name')->toArray();
        $statuses = DB::table('application_statuses')->pluck('id', 'name')->toArray();

        $apps = DB::table('applications')->get();

        foreach ($apps as $app) {
            // 1. Resolve Status ID
            $statusId = null;
            $statusStr = trim($app->status ?? '');
            if (isset($statuses[$statusStr])) {
                $statusId = $statuses[$statusStr];
            } else {
                // Fallback matching
                if (str_contains($statusStr, 'مسودة')) {
                    $statusId = $statuses['مسودة'] ?? 1;
                } elseif (str_contains($statusStr, 'جديد')) {
                    $statusId = $statuses['جديد'] ?? 2;
                } elseif (str_contains($statusStr, 'تدقيق')) {
                    $statusId = $statuses['تحت التدقيق الأولي'] ?? 3;
                } elseif (str_contains($statusStr, 'وثائق')) {
                    $statusId = $statuses['بانتظار الوثائق'] ?? 4;
                } elseif (str_contains($statusStr, 'مقابلة')) {
                    $statusId = $statuses['بانتظار المقابلة'] ?? 7;
                } elseif (str_contains($statusStr, 'قرار')) {
                    $statusId = $statuses['بانتظار إصدار القرار'] ?? 8;
                } elseif (str_contains($statusStr, 'صدور')) {
                    $statusId = $statuses['تم الصدور'] ?? 9;
                } elseif (str_contains($statusStr, 'رفض')) {
                    $statusId = $statuses['مرفوض'] ?? 10;
                } else {
                    $statusId = $statuses['تحت التدقيق الأولي'] ?? 3;
                }
            }

            // 2. Resolve Request Type ID
            $reqTypeId = null;
            $reqStr = trim($app->request_type ?? '');

            if (isset($requestTypes[$reqStr])) {
                $reqTypeId = $requestTypes[$reqStr];
            } else {
                if (str_contains($reqStr, 'تطبيقي') && (str_contains($reqStr, 'داخلي') || str_contains($reqStr, 'سوري'))) {
                    $reqTypeId = $requestTypes['ماجستير داخلي - تطبيقي'] ?? 1;
                } elseif (str_contains($reqStr, 'تطبيقي') && str_contains($reqStr, 'خارجي')) {
                    $reqTypeId = $requestTypes['ماجستير خارجي - تطبيقي'] ?? 5;
                } elseif (str_contains($reqStr, 'نظري') && str_contains($reqStr, 'خارجي')) {
                    $reqTypeId = $requestTypes['ماجستير خارجي - نظري'] ?? 6;
                } elseif (str_contains($reqStr, 'سماح') || str_contains($reqStr, 'تدريسية')) {
                    $reqTypeId = $requestTypes['عضو هيئة تدريسية - سماح'] ?? 4;
                } elseif ((str_contains($reqStr, 'دكتورة') || str_contains($reqStr, 'دكتوراه')) && str_contains($reqStr, 'خارجي')) {
                    $reqTypeId = $requestTypes['دكتورة خارجية'] ?? 7;
                } elseif (str_contains($reqStr, 'دكتورة') || str_contains($reqStr, 'دكتوراه')) {
                    $reqTypeId = $requestTypes['دكتورة داخلي'] ?? 3;
                } elseif (str_contains($reqStr, 'مسودة') || $app->status === 'مسودة') {
                    $reqTypeId = $requestTypes['مسودة'] ?? 8;
                } else {
                    // Default to master internal theoretical
                    $reqTypeId = $requestTypes['ماجستير داخلي - نظري'] ?? 2;
                }
            }

            // Find canonical names
            $canonicalReqName = array_search($reqTypeId, $requestTypes) ?: $app->request_type;
            $canonicalStatusName = array_search($statusId, $statuses) ?: $app->status;

            DB::table('applications')->where('id', $app->id)->update([
                'request_type_id' => $reqTypeId,
                'status_id' => $statusId,
                'request_type' => $canonicalReqName,
                'status' => $canonicalStatusName,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('request_type_id');
            $table->dropConstrainedForeignId('status_id');
        });
    }
};

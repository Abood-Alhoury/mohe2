<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CentralArchiveSampleSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('decisions');

        // Create sample decision PDF if not exists
        $samplePdfPath = 'decisions/sample_official_decision.pdf';
        if (!Storage::disk('public')->exists($samplePdfPath)) {
            $html = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
                . '<h2>الجمهورية العربية السورية - مجلس التعليم العالي</h2>'
                . '<p>وثيقة قرار رسمي مؤرشفة أصولاً</p>'
                . '</body></html>';
            try {
                $output = Pdf::loadHTML($html)->setPaper('A4', 'portrait')->output();
                Storage::disk('public')->put($samplePdfPath, $output);
            } catch (\Throwable $e) {
                Storage::disk('public')->put($samplePdfPath, '%PDF-1.4 sample');
            }
        }

        // Countries
        $syriaId = DB::table('lookup_countries')->where('name', 'like', '%سوري%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'سوريا', 'created_at' => now(), 'updated_at' => now()]);
        $egyptId = DB::table('lookup_countries')->where('name', 'like', '%مصر%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'مصر', 'created_at' => now(), 'updated_at' => now()]);
        $jordanId = DB::table('lookup_countries')->where('name', 'like', '%أردن%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'الأردن', 'created_at' => now(), 'updated_at' => now()]);

        // Universities
        $jazeeraUniId = DB::table('lookup_universities')->where('name', 'like', '%الجزيرة%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة الجزيرة الخاصة', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $rasheedUniId = DB::table('lookup_universities')->where('name', 'like', '%الرشيد%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة الرشيد الدولية', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $eblaUniId = DB::table('lookup_universities')->where('name', 'like', '%إيبلا%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة إيبلا الخاصة', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $qasiounUniId = DB::table('lookup_universities')->where('name', 'like', '%قاسيون%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة قاسيون الخاصة', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $damascusUniId = DB::table('lookup_universities')->where('name', 'like', '%دمشق%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة دمشق', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $yarmoukUniId = DB::table('lookup_universities')->where('name', 'like', '%اليرموك%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة اليرموك', 'country_id' => $jordanId, 'created_at' => now(), 'updated_at' => now()]);

        $cairoUniId = DB::table('lookup_universities')->where('name', 'like', '%القاهرة%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة القاهرة', 'country_id' => $egyptId, 'created_at' => now(), 'updated_at' => now()]);

        $aleppoUniId = DB::table('lookup_universities')->where('name', 'like', '%حلب%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة حلب الحكومية', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        // Status IDs
        $statusApprovedId = DB::table('application_statuses')->where('name', 'تم الصدور')->value('id') ?? 8;
        $statusRejectedId = DB::table('application_statuses')->where('name', 'مرفوض')->value('id') ?? 9;

        // Request Type IDs
        $reqAppliedId = DB::table('application_request_types')->where('name', 'like', '%تطبيقي%')->value('id') ?? 1;
        $reqTheoreticalId = DB::table('application_request_types')->where('name', 'like', '%نظري%')->value('id') ?? 2;
        $reqPhdId = DB::table('application_request_types')->where('name', 'دكتورة داخلي')->value('id') ?? 3;
        $reqFacultyId = DB::table('application_request_types')->where('name', 'like', '%سماح%')->value('id') ?? 4;
        $reqForeignPhdId = DB::table('application_request_types')->where('name', 'دكتورة خارجية')->value('id') ?? 7;

        $records = [
            [
                'app_no'       => '#108',
                'name'         => 'د. علي محمد الجاسم',
                'national_id'  => '01020033445',
                'req_type'     => $reqPhdId,
                'req_name'     => 'دكتورة داخلي',
                'work_uni_id'  => $jazeeraUniId,
                'status'       => $statusApprovedId,
                'degree'       => 'دكتوراه في القانون التجاري',
                'granting_uni' => $damascusUniId,
                'dec_no'       => '105/2026',
                'dec_date'     => '2026-09-21',
                'elig_no'      => '502/2026',
                'elig_date'    => '2026-09-21',
                'notes'        => 'تعد درجة الدكتوراه في القانون التجاري مؤهلة للتعيين والتدريس باختصاص القانون في الجامعات الخاصة السورية.',
            ],
            [
                'app_no'       => '#107',
                'name'         => 'م. حسام العمرلي',
                'national_id'  => '02050403010',
                'req_type'     => $reqAppliedId,
                'req_name'     => 'ماجستير داخلي - تطبيقي',
                'work_uni_id'  => $rasheedUniId,
                'status'       => $statusApprovedId,
                'degree'       => 'ماجستير تقنيات الويب',
                'granting_uni' => $yarmoukUniId,
                'dec_no'       => '89/2026',
                'dec_date'     => '2026-09-19',
                'elig_no'      => null,
                'elig_date'    => null,
                'notes'        => 'تعد درجة الماجستير محققة لشروط الشهادة والاختصاص لتدريس الجوانب التطبيقية فقط.',
            ],
            [
                'app_no'       => '#106',
                'name'         => 'أ.د. سامر كمال النوري',
                'national_id'  => '01010099887',
                'req_type'     => $reqFacultyId,
                'req_name'     => 'عضو هيئة تدريسية - سماح',
                'work_uni_id'  => $eblaUniId,
                'status'       => $statusApprovedId,
                'degree'       => 'أستاذ - هندسة إنشائية',
                'granting_uni' => $aleppoUniId,
                'dec_no'       => '44/2026',
                'dec_date'     => '2026-09-15',
                'elig_no'      => null,
                'elig_date'    => null,
                'notes'        => 'الموافقة على تكليف عضو الهيئة التدريسية بالجامعة الحكومية بالتدريس الساعات المحددة أصولاً.',
            ],
            [
                'app_no'       => '#105',
                'name'         => 'أحمد خالد الباشا',
                'national_id'  => '04030201099',
                'req_type'     => $reqTheoreticalId,
                'req_name'     => 'ماجستير داخلي - نظري',
                'work_uni_id'  => $jazeeraUniId,
                'status'       => $statusApprovedId,
                'degree'       => 'ماجستير بحثي بالفيزياء',
                'granting_uni' => $damascusUniId,
                'dec_no'       => '62/2026',
                'dec_date'     => '2026-09-12',
                'elig_no'      => '411/2026',
                'elig_date'    => '2026-09-12',
                'notes'        => 'الموافقة على تكليف الحائز على ماجستير بحثي بتدريس المقررات النظرية بنصاب 12 ساعة.',
            ],
            [
                'app_no'       => '#104',
                'name'         => 'سمير أحمد درويش',
                'national_id'  => '03040506070',
                'req_type'     => $reqTheoreticalId,
                'req_name'     => 'ماجستير داخلي - نظري',
                'work_uni_id'  => $qasiounUniId,
                'status'       => $statusRejectedId,
                'degree'       => 'ماجستير غير معتمد',
                'granting_uni' => null,
                'dec_no'       => '12/غ.م',
                'dec_date'     => '2026-09-08',
                'elig_no'      => null,
                'elig_date'    => null,
                'notes'        => 'عدم الموافقة على طلب المعادلة لعدم استيفاء شروط الإقامة النظامية ببلد الدراسة.',
            ],
            [
                'app_no'       => '#103',
                'name'         => 'د. ليلى فؤاد صقر',
                'national_id'  => '01050505051',
                'req_type'     => $reqForeignPhdId,
                'req_name'     => 'دكتورة خارجية',
                'work_uni_id'  => $eblaUniId,
                'status'       => $statusApprovedId,
                'degree'       => 'دكتوراه في الكيمياء العضوية',
                'granting_uni' => $cairoUniId,
                'dec_no'       => '210/2025',
                'dec_date'     => '2025-12-20',
                'elig_no'      => '680/2025',
                'elig_date'    => '2025-12-20',
                'notes'        => 'تعد درجة الدكتوراه الممنوحة من جامعة القاهرة معادلة ومؤهلة للتدريس بالجامعات الخاصة.',
            ],
        ];

        foreach ($records as $rec) {
            // Find or create profile
            $candId = DB::table('equivalence_profiles')->where('national_id', $rec['national_id'])->value('id');
            if (!$candId) {
                $candId = DB::table('equivalence_profiles')->insertGetId([
                    'full_name'      => $rec['name'],
                    'national_id'    => $rec['national_id'],
                    'nationality_id' => $syriaId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // Find or create application
            $app = DB::table('applications')->where('application_no', $rec['app_no'])->first();
            if (!$app) {
                $appId = DB::table('applications')->insertGetId([
                    'application_no'     => $rec['app_no'],
                    'candidate_id'       => $candId,
                    'request_type'       => $rec['req_type'],
                    'work_university_id' => $rec['work_uni_id'],
                    'status'             => $rec['status'],
                    'rejection_reason'   => $rec['status'] == $statusRejectedId ? $rec['notes'] : null,
                    'created_at'         => Carbon::parse($rec['dec_date']),
                    'updated_at'         => Carbon::parse($rec['dec_date']),
                ]);
            } else {
                $appId = $app->id;
            }

            // Create Education entry if not exists
            if (!DB::table('educations')->where('application_id', $appId)->exists()) {
                $levelId = 2; // Default Master
                if ($rec['req_type'] == $reqPhdId || $rec['req_type'] == $reqForeignPhdId) {
                    $levelId = 3; // Doctorate
                } elseif ($rec['req_type'] == $reqFacultyId) {
                    $levelId = 3; // Faculty (PhD)
                }

                DB::table('educations')->insert([
                    'application_id'        => $appId,
                    'education_level_id'    => $levelId,
                    'university_id'         => $rec['granting_uni'] ?? $damascusUniId,
                    'exact_specialization'  => $rec['degree'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            // Create Decision entry if not exists
            if (!DB::table('application_decisions')->where('application_id', $appId)->exists()) {
                DB::table('application_decisions')->insert([
                    'application_id'            => $appId,
                    'decision_no'               => $rec['dec_no'],
                    'decision_date'             => $rec['dec_date'],
                    'file_path'                 => $samplePdfPath,
                    'eligibility_decision_no'   => $rec['elig_no'],
                    'eligibility_decision_date' => $rec['elig_date'],
                    'eligibility_file_path'     => $rec['elig_no'] ? $samplePdfPath : null,
                    'notes'                     => $rec['notes'],
                    'created_at'                => Carbon::parse($rec['dec_date']),
                    'updated_at'                => Carbon::parse($rec['dec_date']),
                ]);
            }
        }
    }
}

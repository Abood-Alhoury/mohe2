<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class FreshDamascusMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Disable foreign key checks for clean truncation
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // Truncate tables
        DB::table('application_decisions')->truncate();
        DB::table('application_messages')->truncate();
        DB::table('application_courses')->truncate();
        DB::table('education_residences')->truncate();
        DB::table('education_attachments')->truncate();
        DB::table('educations')->truncate();
        DB::table('applications')->truncate();
        DB::table('equivalence_profiles')->truncate();

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. Prepare Sample PDF Attachments in Public Storage
        Storage::disk('public')->makeDirectory('decisions');
        Storage::disk('public')->makeDirectory('attachments');

        // Sample Equivalence Decision PDF
        $eqPdfHtml = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
            . '<h2 style="color: #1e3a8a;">قرار معادلة الشهادة العلمية الصادر رسمياً</h2>'
            . '<p>الجمهورية العربية السورية - مجلس التعليم العالي</p>'
            . '<hr style="border: 1px solid #cbd5e1; margin: 20px 0;">'
            . '<p style="font-size: 15px; line-height: 1.8;">هذه نسخة PDF رسمية تجريبية لقرار المعادلة النهائي لاختبار العرض والتحميل والدمج الأصولي للملفات.</p>'
            . '</body></html>';
        $eqPdfContent = Pdf::loadHTML($eqPdfHtml)->setPaper('A4', 'portrait')->output();
        Storage::disk('public')->put('decisions/sample_equivalence_decision.pdf', $eqPdfContent);

        // Sample Eligibility Decision PDF
        $eligPdfHtml = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
            . '<h2 style="color: #0284c7;">قرار الأهلية الأكاديمية الرسمي</h2>'
            . '<p>الجمهورية العربية السورية - لجنة التأهيل ومعادلة الدرجات العلمية</p>'
            . '<hr style="border: 1px solid #cbd5e1; margin: 20px 0;">'
            . '<p style="font-size: 15px; line-height: 1.8;">هذه نسخة PDF تجريبية لقرار الأهلية لاختبار المعاينة والدمج المزدوج مع قرار المعادلة.</p>'
            . '</body></html>';
        $eligPdfContent = Pdf::loadHTML($eligPdfHtml)->setPaper('A4', 'portrait')->output();
        Storage::disk('public')->put('decisions/sample_eligibility_decision.pdf', $eligPdfContent);

        // Sample Certificate Attachment PDF
        $attPdfHtml = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
            . '<h2 style="color: #047857;">مصدقة الشهادة العلمية المرفقة</h2>'
            . '<p>وثيقة رسمية ممسوحة ضوئياً المرفقة بطلب تعادل الشهادة لاختبار المعاينة والدمج</p>'
            . '</body></html>';
        $attPdfContent = Pdf::loadHTML($attPdfHtml)->setPaper('A4', 'portrait')->output();
        Storage::disk('public')->put('attachments/sample_certificate.pdf', $attPdfContent);

        // 3. Lookup Countries & Universities
        $syriaCountryId = DB::table('lookup_countries')->where('name', 'like', '%سوري%')->value('id');
        if (!$syriaCountryId) {
            $syriaCountryId = DB::table('lookup_countries')->insertGetId(['name' => 'سوريا', 'created_at' => now(), 'updated_at' => now()]);
        }

        $egyptCountryId = DB::table('lookup_countries')->where('name', 'like', '%مصر%')->value('id');
        if (!$egyptCountryId) {
            $egyptCountryId = DB::table('lookup_countries')->insertGetId(['name' => 'مصر', 'created_at' => now(), 'updated_at' => now()]);
        }

        // Damascus University (جامعة دمشق)
        $uniDamascusId = DB::table('lookup_universities')->where('name', 'like', '%دمشق%')->value('id');
        if (!$uniDamascusId) {
            $uniDamascusId = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة دمشق',
                'country_id' => $syriaCountryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $uniAleppoId = DB::table('lookup_universities')->where('name', 'like', '%حلب%')->value('id');
        if (!$uniAleppoId) {
            $uniAleppoId = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة حلب',
                'country_id' => $syriaCountryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $uniIdlibId = DB::table('lookup_universities')->where('name', 'like', '%إدلب%')->value('id');
        if (!$uniIdlibId) {
            $uniIdlibId = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة إدلب',
                'country_id' => $syriaCountryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $uniTishreenId = DB::table('lookup_universities')->where('name', 'like', '%تشرين%')->value('id');
        if (!$uniTishreenId) {
            $uniTishreenId = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة تشرين',
                'country_id' => $syriaCountryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $uniCairoId = DB::table('lookup_universities')->where('name', 'like', '%القاهرة%')->value('id');
        if (!$uniCairoId) {
            $uniCairoId = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة القاهرة',
                'country_id' => $egyptCountryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Education Levels
        $levelSecondaryId = DB::table('lookup_education_levels')->where('name', 'ثانوية عامة')->value('id') ?? 1;
        $levelBachelorId  = DB::table('lookup_education_levels')->where('name', 'إجازة جامعية')->value('id') ?? 2;
        $levelMasterId    = DB::table('lookup_education_levels')->where('name', 'ماجستير')->value('id') ?? 3;

        $attachmentTypeId = DB::table('lookup_attachment_types')->value('id') ?? 1;

        // -------------------------------------------------------------
        // CANDIDATE 1: جلال مرعي العيسى (ماجستير سوري - بانتظار إصدار القرار)
        // -------------------------------------------------------------
        $profile1 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'جلال مرعي العيسى',
            'father_name' => 'مرعي',
            'mother_name' => 'فاطمة',
            'national_id' => '01050809011',
            'dob'         => '1993-04-12',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'محاضر في إدارة الأعمال (خبرة 4 سنوات)',
            'phone'       => '0113322114',
            'mobile'      => '0933554433',
            'email'       => 'jalal.issa@damascus.edu.sy',
            'address'     => 'دمشق - المزة جبل',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app1 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile1,
            'application_no'       => 'MA-SY-463201',
            'request_type'         => 'تعادل للمرة الأولى - ماجستير سوري',
            'work_university_id'   => $uniDamascusId,
            'work_faculty'         => 'كلية الاقتصاد',
            'work_department'      => 'قسم إدارة الأعمال',
            'new_uni_request_no'   => '4024b7d4-db7f-47b1-99f0-c4cffa67c69f',
            'new_uni_request_date' => '2026-06-20',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => 'بانتظار إصدار القرار',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $ed1_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniIdlibId,
            'general_specialization'=> 'العلوم الإدارية',
            'exact_specialization'  => 'إدارة الأعمال',
            'section_name'          => 'قسم إدارة الأعمال',
            'registration_date'     => '2012-09-15',
            'grant_date'            => '2017-07-01',
            'rank'                  => 'جيد جداً (78.5%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
        $ed1_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniAleppoId,
            'general_specialization'=> 'الاقتصاد',
            'exact_specialization'  => 'إدارة الأعمال',
            'section_name'          => 'قسم إدارة الأعمال',
            'registration_date'     => '2017-10-01',
            'grant_date'            => '2021-06-15',
            'rank'                  => 'امتياز (88%)',
            'supervisor_name'       => 'أ.د. حامد العلي',
            'thesis_title'          => 'إدارة الجودة الشاملة وأثرها على أداء المؤسسات التعليمية',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed1_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed1_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Add Decision for Candidate 1
        DB::table('application_decisions')->insert([
            'application_id'            => $app1,
            'eligibility_decision_no'   => '463/أ.هـ',
            'eligibility_decision_date' => '2026-06-20',
            'eligibility_file_path'     => 'decisions/sample_eligibility_decision.pdf',
            'decision_no'               => '463/2026',
            'decision_date'             => '2026-06-20',
            'file_path'                 => 'decisions/sample_equivalence_decision.pdf',
            'notes'                     => 'قرار تكليف وأهلية مجهز لاختبار التحميل والدمج',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        // -------------------------------------------------------------
        // CANDIDATE 2: د. يوسف الحوري (ماجستير سوري - بانتظار المقابلة)
        // -------------------------------------------------------------
        $profile2 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'د. يوسف الحوري',
            'father_name' => 'أحمد',
            'mother_name' => 'سميرة',
            'national_id' => '01020304055',
            'dob'         => '1991-08-15',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس معلوماتي خبير',
            'phone'       => '0112233445',
            'mobile'      => '0944887766',
            'email'       => 'youssef.alhoury@damascus.edu.sy',
            'address'     => 'دمشق - البرامكة',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app2 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile2,
            'application_no'       => 'MA-SY-589236',
            'request_type'         => 'تعادل للمرة الأولى - ماجستير سوري',
            'work_university_id'   => $uniDamascusId,
            'work_faculty'         => 'كلية الهندسة المعلوماتية',
            'work_department'      => 'قسم هندسة البرمجيات ونظم المعلومات',
            'new_uni_request_no'   => '589236/د',
            'new_uni_request_date' => '2026-07-15',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => 'بانتظار المقابلة',
            'interview_date'       => '2026-08-20',
            'interview_time'       => '10:30',
            'interview_notes'      => 'مقابلة علمية في القاعة 101 بكلية الهندسة المعلوماتية بجامعة دمشق',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $ed2_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'هندسة المعلوماتية',
            'exact_specialization'  => 'هندسة البرمجيات',
            'section_name'          => 'قسم هندسة البرمجيات',
            'grant_date'            => '2015-07-01',
            'rank'                  => 'امتياز (85.2%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
        $ed2_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'الذكاء الاصطناعي',
            'exact_specialization'  => 'معالجة اللغات الطبيعية للغة العربية',
            'section_name'          => 'قسم الذكاء الاصطناعي',
            'grant_date'            => '2020-06-15',
            'rank'                  => 'امتياز مع مرتبة الشرف (92%)',
            'supervisor_name'       => 'أ.د. باسل الخطيب',
            'thesis_title'          => 'نماذج التعلم العميق المتقدمة لنظم الاسترجاع الدلالي للنصوص العربية',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed2_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed2_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // -------------------------------------------------------------
        // CANDIDATE 3: م. محمد نور الدين (ماجستير تطبيقي - تحت التدقيق الأولي)
        // -------------------------------------------------------------
        $profile3 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'محمد نور الدين العبد',
            'father_name' => 'نور الدين',
            'mother_name' => 'منى',
            'national_id' => '01030405066',
            'dob'         => '1994-11-05',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس إنشائي استشاري',
            'phone'       => '0114455667',
            'mobile'      => '0955667788',
            'email'       => 'nour.abdo@damascus.edu.sy',
            'address'     => 'دمشق - كفرسوسة',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app3 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile3,
            'application_no'       => 'MA-AP-213973',
            'request_type'         => 'تعادل للمرة الأولى - ماجستير تطبيقي',
            'work_university_id'   => $uniDamascusId,
            'work_faculty'         => 'كلية الهندسة المدنية',
            'work_department'      => 'قسم الهندسة الإنشائية',
            'new_uni_request_no'   => '213973/ت',
            'new_uni_request_date' => '2026-08-01',
            'is_first_time'        => true,
            'study_system'         => 'ساعات معتمدة',
            'has_previous_degree'  => true,
            'status'               => 'تحت التدقيق الأولي',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $ed3_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'الهندسة المدنية',
            'exact_specialization'  => 'الهندسة الإنشائية',
            'section_name'          => 'قسم الهندسة الإنشائية',
            'grant_date'            => '2018-06-25',
            'rank'                  => 'جيد جداً (81%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
        $ed3_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $egyptCountryId,
            'university_id'         => $uniCairoId,
            'general_specialization'=> 'الهندسة التطبيقية',
            'exact_specialization'  => 'تصميم المنشآت الخرسانية المقاومة للزلازل',
            'section_name'          => 'قسم الهندسة الإنشائية التطبيقية',
            'grant_date'            => '2022-10-10',
            'rank'                  => 'جيد جداً مرتفع (84%)',
            'supervisor_name'       => 'أ.د. شريف عبد اللطيف',
            'thesis_title'          => 'التحليل الديناميكي والتصميم الإنشائي للمباني العالية في المناطق الزلزالية',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed3_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed3_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // -------------------------------------------------------------
        // CANDIDATE 4: د. عبد الرحمن النعمان (ماجستير سوري - بانتظار إصدار القرار)
        // -------------------------------------------------------------
        $profile4 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'عبد الرحمن النعمان',
            'father_name' => 'عبد القادر',
            'mother_name' => 'خديجة',
            'national_id' => '01040506077',
            'dob'         => '1988-02-18',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'استشاري تقانة معلومات وويب',
            'phone'       => '0116677889',
            'mobile'      => '0966778899',
            'email'       => 'abed.noman@damascus.edu.sy',
            'address'     => 'دمشق - الشعلان',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app4 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile4,
            'application_no'       => 'MA-SY-793847',
            'request_type'         => 'تعادل للمرة الأولى - ماجستير سوري',
            'work_university_id'   => $uniDamascusId,
            'work_faculty'         => 'كلية العلوم',
            'work_department'      => 'قسم تقانة المعلومات والويب',
            'new_uni_request_no'   => '793847/ق',
            'new_uni_request_date' => '2026-08-10',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => 'بانتظار إصدار القرار',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $ed4_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app4,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'هندسة المعلوماتية',
            'exact_specialization'  => 'برمجيات',
            'section_name'          => 'قسم البرمجيات',
            'grant_date'            => '2010-07-01',
            'rank'                  => 'جيد جداً (79%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
        $ed4_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app4,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'علوم وبحوث هندسة المعلوماتية',
            'exact_specialization'  => 'علوم ويب وتطبيقات السحابة',
            'section_name'          => 'قسم تقانة المعلومات',
            'grant_date'            => '2012-06-30',
            'rank'                  => 'امتياز (89.5%)',
            'supervisor_name'       => 'أ.د. غسان مسعود',
            'thesis_title'          => 'تطوير النظم السحابية الموزعة لخدمة المؤسسات الحكومية',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed4_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed4_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Add Decision for Candidate 4
        DB::table('application_decisions')->insert([
            'application_id'            => $app4,
            'eligibility_decision_no'   => '106/أ.هـ',
            'eligibility_decision_date' => '2026-08-10',
            'eligibility_file_path'     => 'decisions/sample_eligibility_decision.pdf',
            'decision_no'               => '106/2026',
            'decision_date'             => '2026-08-10',
            'file_path'                 => 'decisions/sample_equivalence_decision.pdf',
            'notes'                     => 'قرار تعادل وأهلية مجهز لاختبار المعاينة والتحميل',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        // -------------------------------------------------------------
        // CANDIDATE 5: أحمد محمود عطايا (ماجستير تطبيقي - بانتظار المقابلة)
        // -------------------------------------------------------------
        $profile5 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'أحمد محمود عطايا',
            'father_name' => 'محمود',
            'mother_name' => 'هناء',
            'national_id' => '01050607088',
            'dob'         => '1995-09-30',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس أنظمة طاقة وتكييف',
            'phone'       => '0117788990',
            'mobile'      => '0977889900',
            'email'       => 'ahmad.ataya@damascus.edu.sy',
            'address'     => 'دمشق - التجارة',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app5 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile5,
            'application_no'       => 'MA-AP-701534',
            'request_type'         => 'تعادل للمرة الأولى - ماجستير تطبيقي',
            'work_university_id'   => $uniDamascusId,
            'work_faculty'         => 'كلية الهندسة الميكانيكية والكهربائية',
            'work_department'      => 'قسم هندسة الطاقة والتكييف',
            'new_uni_request_no'   => '701534/م',
            'new_uni_request_date' => '2026-08-05',
            'is_first_time'        => true,
            'study_system'         => 'ساعات معتمدة',
            'has_previous_degree'  => true,
            'status'               => 'بانتظار المقابلة',
            'interview_date'       => '2026-08-22',
            'interview_time'       => '11:00',
            'interview_notes'      => 'مقابلة تخصصية بقاعة الاجتماعات الرئيسية بكلية الهمك بجامعة دمشق',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $ed5_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app5,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniTishreenId,
            'general_specialization'=> 'الهندسة الميكانيكية',
            'exact_specialization'  => 'القوى الميكانيكية والتكييف',
            'section_name'          => 'قسم القوى الميكانيكية',
            'grant_date'            => '2016-07-05',
            'rank'                  => 'جيد جداً (82%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
        $ed5_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app5,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaCountryId,
            'university_id'         => $uniDamascusId,
            'general_specialization'=> 'الطاقات المتجددة والتكييف',
            'exact_specialization'  => 'أنظمة التكييف والأنظمة الشمسية التطبيقية',
            'section_name'          => 'قسم هندسة الطاقة',
            'grant_date'            => '2021-11-20',
            'rank'                  => 'امتياز (88.5%)',
            'supervisor_name'       => 'أ.د. عصام الكردي',
            'thesis_title'          => 'تحسين كفاءة أنظمة التكييف الشمسي الهجين للمباني العامة',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed5_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed5_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'created_at' => now(), 'updated_at' => now()]
        ]);

        $this->command->info('✅ Cleaned and seeded 5 realistic Damascus University Master applications with sample PDF attachments & decisions!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ForeignMasterTheoreticalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Truncate all application & candidate tables
        Schema::disableForeignKeyConstraints();

        DB::table('application_decisions')->truncate();
        DB::table('application_messages')->truncate();
        DB::table('application_courses')->truncate();
        DB::table('education_residences')->truncate();
        DB::table('education_attachments')->truncate();
        DB::table('educations')->truncate();
        DB::table('applications')->truncate();
        DB::table('equivalence_profiles')->truncate();

        Schema::enableForeignKeyConstraints();

        // 2. Prepare Sample PDF Files in Storage
        Storage::disk('public')->makeDirectory('decisions');
        Storage::disk('public')->makeDirectory('attachments');

        $samplePdf = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
            . '<h2 style="color: #1e3a8a;">وثيقة رسمية مصدقة لطلب تعادل ماجستير خارجي - نظري</h2>'
            . '<p>وزارة التعليم العالي والبحث العلمي - مديرية التعادل والإنتاج العلمي</p>'
            . '<hr style="border: 1px solid #cbd5e1; margin: 20px 0;">'
            . '<p style="font-size: 15px; line-height: 1.8;">ملف تجريبي مصدق ومرفق بالمعاملة لأغراض التدقيق والفحص الأكاديمي.</p>'
            . '</body></html>';
        $pdfOutput = Pdf::loadHTML($samplePdf)->setPaper('A4', 'portrait')->output();
        Storage::disk('public')->put('attachments/sample_certificate.pdf', $pdfOutput);
        Storage::disk('public')->put('attachments/sample_passport_stamps.pdf', $pdfOutput);
        Storage::disk('public')->put('decisions/sample_equivalence_decision.pdf', $pdfOutput);
        Storage::disk('public')->put('decisions/sample_eligibility_decision.pdf', $pdfOutput);

        // 3. Setup Lookup Countries & Universities
        $syriaId = DB::table('lookup_countries')->where('name', 'like', '%سوري%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'سوريا', 'created_at' => now(), 'updated_at' => now()]);

        $egyptId = DB::table('lookup_countries')->where('name', 'like', '%مصر%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'مصر', 'created_at' => now(), 'updated_at' => now()]);

        $jordanId = DB::table('lookup_countries')->where('name', 'like', '%أردن%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'الأردن', 'created_at' => now(), 'updated_at' => now()]);

        $lebanonId = DB::table('lookup_countries')->where('name', 'like', '%لبنان%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'لبنان', 'created_at' => now(), 'updated_at' => now()]);

        // Universities
        $damascusUniId = DB::table('lookup_universities')->where('name', 'like', '%دمشق%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة دمشق', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $aleppoUniId = DB::table('lookup_universities')->where('name', 'like', '%حلب%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة حلب', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $cairoUniId = DB::table('lookup_universities')->where('name', 'like', '%القاهرة%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة القاهرة', 'country_id' => $egyptId, 'created_at' => now(), 'updated_at' => now()]);

        $jordanUniId = DB::table('lookup_universities')->where('name', 'like', '%الجامعة الأردنية%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'الجامعة الأردنية', 'country_id' => $jordanId, 'created_at' => now(), 'updated_at' => now()]);

        $aubUniId = DB::table('lookup_universities')->where('name', 'like', '%بيروت%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'الجامعة الأمريكية في بيروت', 'country_id' => $lebanonId, 'created_at' => now(), 'updated_at' => now()]);

        $spuUniId = DB::table('lookup_universities')->where('name', 'like', '%السورية الخاصة%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'الجامعة السورية الخاصة', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $kalamoonUniId = DB::table('lookup_universities')->where('name', 'like', '%القلمون%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة القلمون الخاصة', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        // Education Levels
        $levelBachelorId = DB::table('lookup_education_levels')->where('name', 'إجازة جامعية')->value('id') ?? 2;
        $levelMasterId   = DB::table('lookup_education_levels')->where('name', 'ماجستير')->value('id') ?? 3;
        $attachmentTypeId = DB::table('lookup_attachment_types')->value('id') ?? 1;

        // IDs of Request Type & Statuses
        $reqTypeForeignTheoretical = DB::table('application_request_types')->where('name', 'ماجستير خارجي - نظري')->value('id') ?? 6;
        $statusUnderStudy          = DB::table('application_statuses')->where('name', 'تحت التدقيق الأولي')->value('id') ?? 2;
        $statusAwaitingInterview   = DB::table('application_statuses')->where('name', 'بانتظار المقابلة')->value('id') ?? 6;
        $statusAwaitingDecision    = DB::table('application_statuses')->where('name', 'بانتظار إصدار القرار')->value('id') ?? 7;

        // =========================================================================
        // ROW 1: د. ريم عبد الرحمن الشامي (ماجستير خارجي - نظري / تحت التدقيق الأولي)
        // =========================================================================
        $profile1 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'ريم عبد الرحمن الشامي',
            'father_name' => 'عبد الرحمن',
            'mother_name' => 'هدى الحافظ',
            'national_id' => '01020304051',
            'dob'         => '1994-06-14',
            'gender'      => 'أنثى',
            'is_syrian'   => true,
            'job_title'   => 'صيدلانية / باحثة في الكيمياء الدوائية',
            'phone'       => '0114455661',
            'mobile'      => '0944112233',
            'email'       => 'reem.shami@spu.edu.sy',
            'address'     => 'دمشق - المزرعة - شارع الملك العادل',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app1 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile1,
            'application_no'       => 'MA-FOR-319082',
            'request_type'         => $reqTypeForeignTheoretical,
            'work_university_id'   => $spuUniId,
            'work_faculty'         => 'كلية الصيدلة',
            'work_department'      => 'قسم الكيمياء الصيدلية والعقاقير',
            'new_uni_request_no'   => 'SPU/PHARM/2026-104',
            'new_uni_request_date' => '2026-08-01',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => $statusUnderStudy,
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Educations App 1:
        // Bachelor: Damascus University
        $ed1_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $damascusUniId,
            'faculty'               => 'كلية الصيدلة',
            'general_specialization'=> 'الصيدلة والكيمياء الصيدلية',
            'exact_specialization'  => 'صيدلة عامة',
            'section_name'          => 'الصيدلة',
            'registration_date'     => '2012-09-15',
            'graduation_date'       => '2017-07-10',
            'grant_date'            => '2017-08-20',
            'rank'                  => 'جيد جداً (82.4%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Master: Cairo University (Egypt)
        $ed1_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $egyptId,
            'university_id'         => $cairoUniId,
            'faculty'               => 'كلية الصيدلة',
            'general_specialization'=> 'العلوم الصيدلية',
            'exact_specialization'  => 'الكيمياء الصيدلية وتصميم الدواء',
            'section_name'          => 'قسم الكيمياء الدوائية',
            'registration_date'     => '2018-10-01',
            'graduation_date'       => '2021-06-30',
            'grant_date'            => '2021-09-15',
            'defense_date'          => '2021-06-15',
            'rank'                  => 'امتياز مع مرتبة الشرف (89.5%)',
            'supervisor_name'       => 'أ.د. عصام الفقي',
            'thesis_title'          => 'تخليق ودراسة مشتقات كيميائية جديدة كمضادات للأورام السرطانية',
            'duration_years'        => 3,
            'study_language'        => 'الإنجليزية',
            'experience_from_year'  => 2021,
            'experience_to_year'    => 2024,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Residences (Proof of physical presence abroad in Egypt)
        DB::table('education_residences')->insert([
            [
                'education_id'  => $ed1_ma,
                'page_number'   => '12',
                'exit_airport'  => 'مطار دمشق الدولي',
                'exit_date'     => '2018-09-25',
                'entry_airport' => 'مطار القاهرة الدولي',
                'entry_date'    => '2018-09-25',
                'stamp_details' => 'تأشيرة إقامة دراسية - جمهورية مصر العربية',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'education_id'  => $ed1_ma,
                'page_number'   => '14',
                'exit_airport'  => 'مطار القاهرة الدولي',
                'exit_date'     => '2021-07-05',
                'entry_airport' => 'مطار دمشق الدولي',
                'entry_date'    => '2021-07-05',
                'stamp_details' => 'ختم مغادرة نهائية بعد إنهاء متطلبات الماجستير',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);

        // Attachments App 1
        DB::table('education_attachments')->insert([
            ['education_id' => $ed1_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الصيدلة - جامعة دمشق', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed1_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة الماجستير في العلوم الصيدلية - جامعة القاهرة', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed1_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_passport_stamps.pdf', 'notes' => 'جواز السفر وأختام الإقامة في جمهورية مصر العربية', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Courses App 1
        DB::table('application_courses')->insert([
            ['application_id' => $app1, 'faculty' => 'كلية الصيدلة', 'department' => 'قسم الكيمياء الصيدلية', 'course_name' => 'الكيمياء الصيدلية 1', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app1, 'faculty' => 'كلية الصيدلة', 'department' => 'قسم الكيمياء الصيدلية', 'course_name' => 'الكيمياء الصيدلية 2', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================================
        // ROW 2: م. كنان هشام النجار (ماجستير خارجي - نظري / بانتظار المقابلة)
        // =========================================================================
        $profile2 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'كنان هشام النجار',
            'father_name' => 'هشام',
            'mother_name' => 'ميادة السراج',
            'national_id' => '02040608092',
            'dob'         => '1992-11-20',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس برمجيات / مدرس نظم معلومات',
            'phone'       => '0116677882',
            'mobile'      => '0955223344',
            'email'       => 'kinan.najjar@kalamoon.edu.sy',
            'address'     => 'دمشق - أبو رمانة - ساحة النجمة',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app2 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile2,
            'application_no'       => 'MA-FOR-642195',
            'request_type'         => $reqTypeForeignTheoretical,
            'work_university_id'   => $kalamoonUniId,
            'work_faculty'         => 'كلية الهندسة والتكنولوجيا',
            'work_department'      => 'قسم الذكاء الاصطناعي والبيانات الضخمة',
            'new_uni_request_no'   => 'UOK/ENG/2026-44',
            'new_uni_request_date' => '2026-07-20',
            'is_first_time'        => true,
            'study_system'         => 'ساعات معتمدة',
            'has_previous_degree'  => true,
            'status'               => $statusAwaitingInterview,
            'interview_date'       => '2026-09-02',
            'interview_time'       => '11:00 صباحاً',
            'interview_notes'      => 'مقابلة علمية واختبار تخصصي في القاعة 202 بمبنى وزارة التعليم العالي والبحث العلمي',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Educations App 2:
        // Bachelor: Aleppo University
        $ed2_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $aleppoUniId,
            'faculty'               => 'كلية الهندسة المعلوماتية',
            'general_specialization'=> 'هندسة المعلوماتية',
            'exact_specialization'  => 'هندسة البرمجيات ونظم المعلومات',
            'section_name'          => 'هندسة البرمجيات',
            'registration_date'     => '2010-09-15',
            'graduation_date'       => '2015-07-01',
            'grant_date'            => '2015-08-30',
            'rank'                  => 'جيد جداً (80.1%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Master: University of Jordan (Jordan)
        $ed2_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $jordanId,
            'university_id'         => $jordanUniId,
            'faculty'               => 'كلية الملك عبد الله الثاني لتكنولوجيا المعلومات',
            'general_specialization'=> 'علم الحاسوب',
            'exact_specialization'  => 'الذكاء الاصطناعي وتنقيب البيانات',
            'section_name'          => 'قسم علم الحاسوب',
            'registration_date'     => '2017-02-10',
            'graduation_date'       => '2020-01-25',
            'grant_date'            => '2020-02-15',
            'defense_date'          => '2020-01-15',
            'rank'                  => 'امتياز (3.82 من 4.0)',
            'supervisor_name'       => 'أ.د. حازم القضاة',
            'thesis_title'          => 'خوارزميات التعلم العميق في معالجة الصور الطبية للكشف المبكر عن الأمراض',
            'duration_years'        => 3,
            'study_language'        => 'الإنجليزية',
            'experience_from_year'  => 2020,
            'experience_to_year'    => 2024,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Residences App 2
        DB::table('education_residences')->insert([
            [
                'education_id'  => $ed2_ma,
                'page_number'   => '8',
                'exit_airport'  => 'مركز نصيب الحدودي',
                'exit_date'     => '2017-02-01',
                'entry_airport' => 'مركز جابر الحدودي - الأردن',
                'entry_date'    => '2017-02-01',
                'stamp_details' => 'إقامة طلابية سارية المفعول - المملكة الأردنية الهاشمية',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'education_id'  => $ed2_ma,
                'page_number'   => '10',
                'exit_airport'  => 'مطار الملكة علياء الدولي',
                'exit_date'     => '2020-02-20',
                'entry_airport' => 'مطار دمشق الدولي',
                'entry_date'    => '2020-02-20',
                'stamp_details' => 'مغادرة نهائية بعد الحصول على وثيقة التخرج المصدقة',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed2_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الهندسة المعلوماتية - جامعة حلب', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed2_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة ماجستير علم الحاسوب والذكاء الاصطناعي - الجامعة الأردنية', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed2_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_passport_stamps.pdf', 'notes' => 'حركات الدخول والخروج والإقامة الدراسية في الأردن', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('application_courses')->insert([
            ['application_id' => $app2, 'faculty' => 'كلية الهندسة والتكنولوجيا', 'department' => 'قسم الذكاء الاصطناعي', 'course_name' => 'أساسيات الذكاء الاصطناعي', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app2, 'faculty' => 'كلية الهندسة والتكنولوجيا', 'department' => 'قسم الذكاء الاصطناعي', 'course_name' => 'التعلم الآلي وتعلم العمق', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================================
        // ROW 3: أ. ميس حسام البيرقدار (ماجستير خارجي - نظري / بانتظار إصدار القرار)
        // =========================================================================
        $profile3 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'ميس حسام البيرقدار',
            'father_name' => 'حسام',
            'mother_name' => 'رنا الكيالي',
            'national_id' => '01030507093',
            'dob'         => '1995-03-28',
            'gender'      => 'أنثى',
            'is_syrian'   => true,
            'job_title'   => 'محاضرة في إدارة الأعمال والتسويق الرقمي',
            'phone'       => '0112233443',
            'mobile'      => '0933998877',
            'email'       => 'mays.bairakdar@damascus.edu.sy',
            'address'     => 'دمشق - المالكي - جادة عبد المنعم رياض',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app3 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile3,
            'application_no'       => 'MA-FOR-875410',
            'request_type'         => $reqTypeForeignTheoretical,
            'work_university_id'   => $damascusUniId,
            'work_faculty'         => 'كلية الاقتصاد',
            'work_department'      => 'قسم إدارة الأعمال',
            'new_uni_request_no'   => 'DAM/ECON/2026-312',
            'new_uni_request_date' => '2026-06-15',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => $statusAwaitingDecision,
            'interview_date'       => '2026-08-18',
            'interview_time'       => '10:00 صباحاً',
            'interview_notes'      => 'اجتازت المرشحة المقابلة الشفهية والعملية بنجاح أمام اللجنة المختصة',
            'user_id'              => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Educations App 3:
        // Bachelor: Damascus University
        $ed3_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $damascusUniId,
            'faculty'               => 'كلية الاقتصاد',
            'general_specialization'=> 'العلوم الإدارية والمالية',
            'exact_specialization'  => 'إدارة الأعمال',
            'section_name'          => 'قسم إدارة الأعمال',
            'registration_date'     => '2013-09-15',
            'graduation_date'       => '2017-07-01',
            'grant_date'            => '2017-08-15',
            'rank'                  => 'جيد جداً (83.2%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Master: American University of Beirut (Lebanon)
        $ed3_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $lebanonId,
            'university_id'         => $aubUniId,
            'faculty'               => 'كلية سليمان العليان لإدارة الأعمال (OSB)',
            'general_specialization'=> 'إدارة الأعمال',
            'exact_specialization'  => 'التسويق الاستراتيجي والسلوك الاستهلاكي الرقمي',
            'section_name'          => 'قسم التسويق والإدارة العامة',
            'registration_date'     => '2018-09-20',
            'graduation_date'       => '2021-06-25',
            'grant_date'            => '2021-07-15',
            'defense_date'          => '2021-06-10',
            'rank'                  => 'امتياز مع مرتبة الشرف الأولى (3.91 من 4.0)',
            'supervisor_name'       => 'أ.د. نديم صليبا',
            'thesis_title'          => 'أثر التحول الرقمي وتجربة المستخدم على ولاء العملاء في القطاع المصرفي المعاصر',
            'duration_years'        => 3,
            'study_language'        => 'الإنجليزية',
            'experience_from_year'  => 2021,
            'experience_to_year'    => 2025,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Residences App 3 (Lebanon)
        DB::table('education_residences')->insert([
            [
                'education_id'  => $ed3_ma,
                'page_number'   => '6',
                'exit_airport'  => 'نقطة جديدة يابوس الحدودية',
                'exit_date'     => '2018-09-10',
                'entry_airport' => 'نقطة المصنع الحدودية - لبنان',
                'entry_date'    => '2018-09-10',
                'stamp_details' => 'إقامة دراسية في الجمهورية اللبنانية',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'education_id'  => $ed3_ma,
                'page_number'   => '11',
                'exit_airport'  => 'نقطة المصنع الحدودية',
                'exit_date'     => '2021-07-20',
                'entry_airport' => 'نقطة جديدة يابوس الحدودية',
                'entry_date'    => '2021-07-20',
                'stamp_details' => 'عودة نهائية للقطر بعد إتمام مناقشة ونيل درجة الماجستير أصولاً',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed3_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الاقتصاد - جامعة دمشق', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed3_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة ماجستير إدارة الأعمال (MBA) - الجامعة الأمريكية في بيروت', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed3_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_passport_stamps.pdf', 'notes' => 'بيان حركة القدوم والمغادرة والإقامة بلبنان', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('application_courses')->insert([
            ['application_id' => $app3, 'faculty' => 'كلية الاقتصاد', 'department' => 'قسم إدارة الأعمال', 'course_name' => 'مبادئ إدارة الأعمال', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app3, 'faculty' => 'كلية الاقتصاد', 'department' => 'قسم إدارة الأعمال', 'course_name' => 'التسويق الرقمي الحديث', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sample Decisions ready for Candidate 3 (Awaiting decision issuance / has decision)
        DB::table('application_decisions')->insert([
            'application_id'            => $app3,
            'eligibility_decision_no'   => '89/أ.هـ',
            'eligibility_decision_date' => '2026-08-26',
            'eligibility_file_path'     => 'decisions/sample_eligibility_decision.pdf',
            'decision_no'               => '89/2026',
            'decision_date'             => '2026-08-26',
            'file_path'                 => 'decisions/sample_equivalence_decision.pdf',
            'notes'                     => 'قرار تعادل وأهلية جاهز للمعاينة والتحميل بعد اجتياز المقابلة',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
    }
}

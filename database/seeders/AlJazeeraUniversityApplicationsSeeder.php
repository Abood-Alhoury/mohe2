<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AlJazeeraUniversityApplicationsSeeder extends Seeder
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
            . '<h2 style="color: #1e3a8a;">وثيقة رسمية - جامعة الجزيرة الخاصة</h2>'
            . '<p>وزارة التعليم العالي والبحث العلمي - مديرية تعادل الشهادات</p>'
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

        // Universities
        $alJazeeraUni = DB::table('lookup_universities')->where('name', 'like', '%الجزيرة%')->first();
        $alJazeeraUniId = $alJazeeraUni ? $alJazeeraUni->id : DB::table('lookup_universities')->insertGetId([
            'name' => 'جامعة الجزيرة الخاصة',
            'country_id' => $syriaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $damascusUniId = DB::table('lookup_universities')->where('name', 'like', '%دمشق%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة دمشق', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $aleppoUniId = DB::table('lookup_universities')->where('name', 'like', '%حلب%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة حلب', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $tishreenUniId = DB::table('lookup_universities')->where('name', 'like', '%تشرين%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة تشرين', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

        $cairoUniId = DB::table('lookup_universities')->where('name', 'like', '%القاهرة%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة القاهرة', 'country_id' => $egyptId, 'created_at' => now(), 'updated_at' => now()]);

        // User for AlJazeera University
        $alJazeeraUser = DB::table('users')->where('email', 'uni_aljazeera@uni.edu.sy')->first();
        $userId = $alJazeeraUser ? $alJazeeraUser->id : 3;

        // Education Levels
        $levelBachelorId = DB::table('lookup_education_levels')->where('name', 'إجازة جامعية')->value('id') ?? 2;
        $levelMasterId   = DB::table('lookup_education_levels')->where('name', 'ماجستير')->value('id') ?? 3;
        $attachmentTypeId = DB::table('lookup_attachment_types')->value('id') ?? 1;

        // Request Types IDs:
        // 1. ماجستير داخلي - تطبيقي (1)
        // 2. ماجستير داخلي - نظري (2)
        // 5. ماجستير خارجي - تطبيقي (5)
        // 6. ماجستير خارجي - نظري (6)
        $reqTypeInternalTheoretical = DB::table('application_request_types')->where('name', 'ماجستير داخلي - نظري')->value('id') ?? 2;
        $reqTypeForeignTheoretical  = DB::table('application_request_types')->where('name', 'ماجستير خارجي - نظري')->value('id') ?? 6;
        $reqTypeInternalApplied      = DB::table('application_request_types')->where('name', 'ماجستير داخلي - تطبيقي')->value('id') ?? 1;

        // Status IDs
        $statusUnderStudy        = DB::table('application_statuses')->where('name', 'تحت التدقيق الأولي')->value('id') ?? 2;
        $statusAwaitingInterview = DB::table('application_statuses')->where('name', 'بانتظار المقابلة')->value('id') ?? 6;
        $statusAwaitingDecision  = DB::table('application_statuses')->where('name', 'بانتظار إصدار القرار')->value('id') ?? 7;

        // =========================================================================
        // معاملة 1: ماجستير داخلي نظري - جامعة الجزيرة الخاصة
        // المرشح: د. سامر وليد الحموي
        // =========================================================================
        $profile1 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'سامر وليد الحموي',
            'father_name' => 'وليد',
            'mother_name' => 'مريم الحسين',
            'national_id' => '01030405061',
            'dob'         => '1993-05-18',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مدرس مساعد / باحث في إدارة المشروعات الهندسية',
            'phone'       => '0113322115',
            'mobile'      => '0944556677',
            'email'       => 'samer.hamwi@jazeera.edu.sy',
            'address'     => 'دمشق - المزة فيلات غربية',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app1 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile1,
            'application_no'       => 'MA-SY-821405',
            'request_type'         => $reqTypeInternalTheoretical,
            'work_university_id'   => $alJazeeraUniId,
            'work_faculty'         => 'كلية الهندسة المدنية والمعمارية',
            'work_department'      => 'قسم هندسة وإدارة التشييد',
            'new_uni_request_no'   => 'JU/ENG/2026-112',
            'new_uni_request_date' => '2026-08-01',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => $statusUnderStudy,
            'user_id'              => $userId,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // الإجازة الجامعية (جامعة دمشق)
        $ed1_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $damascusUniId,
            'faculty'               => 'كلية الهندسة المدنية',
            'general_specialization'=> 'الهندسة المدنية',
            'exact_specialization'  => 'هندسة التشييد والإنشاءات',
            'section_name'          => 'قسم الإنشاءات',
            'registration_date'     => '2011-09-15',
            'graduation_date'       => '2016-07-01',
            'grant_date'            => '2016-08-25',
            'rank'                  => 'جيد جداً (81.3%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // ماجستير سوري داخلي - نظري (جامعة دمشق)
        $ed1_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app1,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaId,
            'university_id'         => $damascusUniId,
            'faculty'               => 'كلية الهندسة المدنية',
            'general_specialization'=> 'الهندسة المدنية',
            'exact_specialization'  => 'إدارة المشاريع الهندسية والتشييد',
            'section_name'          => 'قسم إدارة التشييد',
            'registration_date'     => '2016-10-01',
            'graduation_date'       => '2020-06-30',
            'grant_date'            => '2020-08-10',
            'defense_date'          => '2020-06-15',
            'rank'                  => 'امتياز (88%)',
            'supervisor_name'       => 'أ.د. حامد العلي',
            'thesis_title'          => 'نمذجة معلومات البناء BIM في تحسين إدارة المشاريع الإنشائية واستدامتها',
            'duration_years'        => 3,
            'study_language'        => 'العربية',
            'experience_from_year'  => 2020,
            'experience_to_year'    => 2024,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed1_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الهندسة المدنية - جامعة دمشق', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed1_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة الماجستير في إدارة التشييد - جامعة دمشق', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('application_courses')->insert([
            ['application_id' => $app1, 'faculty' => 'كلية الهندسة المدنية والمعمارية', 'department' => 'قسم هندسة التشييد', 'course_name' => 'إدارة المشاريع الهندسية', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app1, 'faculty' => 'كلية الهندسة المدنية والمعمارية', 'department' => 'قسم هندسة التشييد', 'course_name' => 'اقتصاد هندسي ومواصفات', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);


        // =========================================================================
        // معاملة 2: ماجستير خارجي نظري - جامعة الجزيرة الخاصة
        // المرشحة: د. لين غياث الخاني
        // =========================================================================
        $profile2 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'لين غياث الخاني',
            'father_name' => 'غياث',
            'mother_name' => 'سميرة الأتاسي',
            'national_id' => '01040506072',
            'dob'         => '1994-09-12',
            'gender'      => 'أنثى',
            'is_syrian'   => true,
            'job_title'   => 'صيدلانية / باحثة كيمياء حيوية سريرية',
            'phone'       => '0114433226',
            'mobile'      => '0955667788',
            'email'       => 'leen.khani@jazeera.edu.sy',
            'address'     => 'دمشق - الشعلان - شارع حافظ إبراهيم',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app2 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile2,
            'application_no'       => 'MA-FOR-519842',
            'request_type'         => $reqTypeForeignTheoretical,
            'work_university_id'   => $alJazeeraUniId,
            'work_faculty'         => 'كلية الصيدلة',
            'work_department'      => 'قسم الكيمياء الحيوية والأحياء الدقيقة',
            'new_uni_request_no'   => 'JU/PHARM/2026-088',
            'new_uni_request_date' => '2026-07-25',
            'is_first_time'        => true,
            'study_system'         => 'فصلي منتظم',
            'has_previous_degree'  => true,
            'status'               => $statusAwaitingInterview,
            'interview_date'       => '2026-09-05',
            'interview_time'       => '10:30 صباحاً',
            'interview_notes'      => 'مقابلة علمية واختبار تخصصي في القاعة 104 بمبنى وزارة التعليم العالي والبحث العلمي',
            'user_id'              => $userId,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // الإجازة في الصيدلة (جامعة حلب)
        $ed2_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $aleppoUniId,
            'faculty'               => 'كلية الصيدلة',
            'general_specialization'=> 'العلوم الصيدلية',
            'exact_specialization'  => 'صيدلة عامة',
            'section_name'          => 'قسم الصيدلة',
            'registration_date'     => '2012-09-15',
            'graduation_date'       => '2017-06-30',
            'grant_date'            => '2017-08-15',
            'rank'                  => 'جيد جداً (84.1%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // الماجستير الخارجي النظري (جامعة القاهرة - مصر)
        $ed2_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app2,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $egyptId,
            'university_id'         => $cairoUniId,
            'faculty'               => 'كلية الصيدلة',
            'general_specialization'=> 'العلوم الصيدلية',
            'exact_specialization'  => 'الكيمياء الحيوية السريرية وتصميم الأدوية',
            'section_name'          => 'قسم الكيمياء الحيوية',
            'registration_date'     => '2018-10-01',
            'graduation_date'       => '2021-06-20',
            'grant_date'            => '2021-09-01',
            'defense_date'          => '2021-06-10',
            'rank'                  => 'امتياز مع مرتبة الشرف (91.2%)',
            'supervisor_name'       => 'أ.د. عصام الفقي',
            'thesis_title'          => 'الدلالات البيولوجية الجزيئية ومثبطات الإنزيمات في استهداف وتصميم الأدوية الحديثة',
            'duration_years'        => 3,
            'study_language'        => 'الإنجليزية',
            'experience_from_year'  => 2021,
            'experience_to_year'    => 2025,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // سجل الإقامة بالخارج في مصر (متطلب الماجستير الخارجي النظري)
        DB::table('education_residences')->insert([
            [
                'education_id'  => $ed2_ma,
                'page_number'   => '10',
                'exit_airport'  => 'مطار دمشق الدولي',
                'exit_date'     => '2018-09-20',
                'entry_airport' => 'مطار القاهرة الدولي',
                'entry_date'    => '2018-09-20',
                'stamp_details' => 'تأشيرة إقامة دراسية جامعية - مصر',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'education_id'  => $ed2_ma,
                'page_number'   => '14',
                'exit_airport'  => 'مطار القاهرة الدولي',
                'exit_date'     => '2021-07-10',
                'entry_airport' => 'مطار دمشق الدولي',
                'entry_date'    => '2021-07-10',
                'stamp_details' => 'مغادرة نهائية بعد الحصول على وثيقة التخرج والأطروحة المصدقة',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed2_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الصيدلة - جامعة حلب', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed2_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة ماجستير الكيمياء الحيوية السريرية - جامعة القاهرة', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed2_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_passport_stamps.pdf', 'notes' => 'جواز السفر وأختام الإقامة في جمهورية مصر العربية', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('application_courses')->insert([
            ['application_id' => $app2, 'faculty' => 'كلية الصيدلة', 'department' => 'قسم الكيمياء الحيوية', 'course_name' => 'كيمياء حيوية سريرية 1', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app2, 'faculty' => 'كلية الصيدلة', 'department' => 'قسم الكيمياء الحيوية', 'course_name' => 'علم الأحياء الدقيقة الصيدلي', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);


        // =========================================================================
        // معاملة 3: ماجستير داخلي تطبيقي - جامعة الجزيرة الخاصة
        // المرشح: م. طارق نضال الشيخ
        // =========================================================================
        $profile3 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'طارق نضال الشيخ',
            'father_name' => 'نضال',
            'mother_name' => 'هيام قدسي',
            'national_id' => '01050607083',
            'dob'         => '1995-02-14',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس برمجيات ونظم شبكية وأمن سيبراني',
            'phone'       => '0112211337',
            'mobile'      => '0966778899',
            'email'       => 'tarek.sheikh@jazeera.edu.sy',
            'address'     => 'دمشق - كفرسوسة - تنظيم كفرسوسة الجديد',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app3 = DB::table('applications')->insertGetId([
            'candidate_id'         => $profile3,
            'application_no'       => 'MA-AP-673291',
            'request_type'         => $reqTypeInternalApplied,
            'work_university_id'   => $alJazeeraUniId,
            'work_faculty'         => 'كلية الهندسة المعلوماتية',
            'work_department'      => 'قسم هندسة البرمجيات والذكاء الاصطناعي',
            'new_uni_request_no'   => 'JU/IT/2026-215',
            'new_uni_request_date' => '2026-08-10',
            'is_first_time'        => true,
            'study_system'         => 'ساعات معتمدة',
            'has_previous_degree'  => true,
            'status'               => $statusAwaitingDecision,
            'interview_date'       => '2026-08-20',
            'interview_time'       => '11:00 صباحاً',
            'interview_notes'      => 'اجتاز المرشح المقابلة التخصصية والاختبار العملي بنجاح أمام اللجنة',
            'user_id'              => $userId,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // الإجازة في الهندسة المعلوماتية (جامعة تشرين)
        $ed3_ba = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelBachelorId,
            'country_id'            => $syriaId,
            'university_id'         => $tishreenUniId,
            'faculty'               => 'كلية الهندسة المعلوماتية',
            'general_specialization'=> 'هندسة المعلوماتية',
            'exact_specialization'  => 'هندسة النظم والبرمجيات',
            'section_name'          => 'قسم هندسة البرمجيات',
            'registration_date'     => '2013-09-15',
            'graduation_date'       => '2018-07-01',
            'grant_date'            => '2018-08-20',
            'rank'                  => 'جيد جداً (82.5%)',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // ماجستير تأهيل وتخصص تطبيقي (جامعة دمشق)
        $ed3_ma = DB::table('educations')->insertGetId([
            'application_id'        => $app3,
            'education_level_id'    => $levelMasterId,
            'country_id'            => $syriaId,
            'university_id'         => $damascusUniId,
            'faculty'               => 'كلية الهندسة المعلوماتية',
            'general_specialization'=> 'هندسة المعلوماتية',
            'exact_specialization'  => 'أمن النظم والمعلومات والشبكات (ماجستير تطبيقي)',
            'section_name'          => 'قسم الشبكات ونظم الحاسوب',
            'registration_date'     => '2019-10-01',
            'graduation_date'       => '2022-06-30',
            'grant_date'            => '2022-09-10',
            'rank'                  => 'امتياز (87%)',
            'supervisor_name'       => 'أ.د. غسان مسعود',
            'thesis_title'          => 'تأمين بنية الحوسبة السحابية ضد الهجمات الموزعة DDoS في المؤسسات الجامعية',
            'duration_years'        => 2,
            'study_language'        => 'العربية',
            'experience_from_year'  => 2022,
            'experience_to_year'    => 2025,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('education_attachments')->insert([
            ['education_id' => $ed3_ba, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'مصدقة الإجازة في الهندسة المعلوماتية - جامعة تشرين', 'created_at' => now(), 'updated_at' => now()],
            ['education_id' => $ed3_ma, 'attachment_type_id' => $attachmentTypeId, 'file_path' => 'attachments/sample_certificate.pdf', 'notes' => 'شهادة ماجستير التأهيل والتخصص التطبيقي في أمن النظم - جامعة دمشق', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('application_courses')->insert([
            ['application_id' => $app3, 'faculty' => 'كلية الهندسة المعلوماتية', 'department' => 'قسم هندسة البرمجيات والذكاء الاصطناعي', 'course_name' => 'أمن النظم والمعلومات', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
            ['application_id' => $app3, 'faculty' => 'كلية الهندسة المعلوماتية', 'department' => 'قسم هندسة البرمجيات والذكاء الاصطناعي', 'course_name' => 'شبكات الحاسوب المتقدمة', 'course_status' => 'مطلوب تدريسه', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sample Decisions ready for Candidate 3 (Awaiting decision issuance / has decisions)
        DB::table('application_decisions')->insert([
            'application_id'            => $app3,
            'eligibility_decision_no'   => '115/أ.هـ',
            'eligibility_decision_date' => '2026-08-26',
            'eligibility_file_path'     => 'decisions/sample_eligibility_decision.pdf',
            'decision_no'               => '115/2026',
            'decision_date'             => '2026-08-26',
            'file_path'                 => 'decisions/sample_equivalence_decision.pdf',
            'notes'                     => 'قرار تعادل وأهلية جاهز للمعاينة والطباعة لطلب الماجستير التطبيقي بجامعة الجزيرة الخاصة',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
    }
}

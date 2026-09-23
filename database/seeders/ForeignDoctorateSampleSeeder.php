<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ForeignDoctorateSampleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Prepare Sample PDF Files in Storage if not exist
        Storage::disk('public')->makeDirectory('decisions');
        Storage::disk('public')->makeDirectory('attachments');

        $samplePdfContent = '<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body{font-family: "DejaVu Sans", sans-serif; text-align: center; padding: 40px;}</style></head><body>'
            . '<h2 style="color: #1e3a8a;">وثيقة رسمية مصدقة لطلب تعادل دكتوراه غير سورية (خارجية)</h2>'
            . '<p>وزارة التعليم العالي والبحث العلمي - مديرية التعادل والإنتاج العلمي</p>'
            . '<hr style="border: 1px solid #cbd5e1; margin: 20px 0;">'
            . '<p style="font-size: 15px; line-height: 1.8;">ملف تجريبي مصدق ومرفق بالمعاملة لأغراض التدقيق والفحص الأكاديمي ولجنة الإنتاج العلمي.</p>'
            . '</body></html>';
        $pdfOutput = Pdf::loadHTML($samplePdfContent)->setPaper('A4', 'portrait')->output();

        Storage::disk('public')->put('attachments/sample_phd_certificate.pdf', $pdfOutput);
        Storage::disk('public')->put('attachments/sample_phd_passport_stamps.pdf', $pdfOutput);
        Storage::disk('public')->put('attachments/sample_phd_thesis_summary.pdf', $pdfOutput);

        // 2. Lookup Countries
        $syriaId = DB::table('lookup_countries')->where('name', 'like', '%سوري%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'سوريا', 'created_at' => now(), 'updated_at' => now()]);

        $egyptId = DB::table('lookup_countries')->where('name', 'like', '%مصر%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'مصر', 'created_at' => now(), 'updated_at' => now()]);

        $jordanId = DB::table('lookup_countries')->where('name', 'like', '%أردن%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'الأردن', 'created_at' => now(), 'updated_at' => now()]);

        $lebanonId = DB::table('lookup_countries')->where('name', 'like', '%لبنان%')->value('id')
            ?: DB::table('lookup_countries')->insertGetId(['name' => 'لبنان', 'created_at' => now(), 'updated_at' => now()]);

        // 3. Lookup Universities
        $damascusUniId = DB::table('lookup_universities')->where('name', 'like', '%دمشق%')->value('id')
            ?: DB::table('lookup_universities')->insertGetId(['name' => 'جامعة دمشق', 'country_id' => $syriaId, 'created_at' => now(), 'updated_at' => now()]);

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

        // =========================================================================
        // SAMPLE 1: د. عبد الكريم قاسم الحداد (بانتظار إصدار القرار - لتوليد القرارات الرسمية)
        // =========================================================================
        $oldApp1 = DB::table('applications')->where('application_no', 'DOC-FOR-701982')->first();
        if ($oldApp1) {
            DB::table('applications')->where('id', $oldApp1->id)->delete();
        }
        DB::table('equivalence_profiles')->where('national_id', '01020304991')->delete();

        $profile1 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'عبد الكريم قاسم الحداد',
            'father_name' => 'قاسم',
            'mother_name' => 'فاطمة النجار',
            'national_id' => '01020304991',
            'dob'         => '1987-05-12',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس استشاري / دكتوراه في الهندسة المدنية',
            'phone'       => '0112233441',
            'mobile'      => '0944556677',
            'email'       => 'abdulkarim.haddad@spu.edu.sy',
            'address'     => 'دمشق - المزة - فيلات غربية',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app1 = DB::table('applications')->insertGetId([
            'candidate_id'                   => $profile1,
            'application_no'                 => 'DOC-FOR-701982',
            'request_type'                   => 7, // دكتورة خارجية
            'work_university_id'             => $spuUniId,
            'work_faculty'                   => 'كلية الهندسة المدنية',
            'work_department'                => 'الهندسة الإنشائية',
            'new_uni_request_no'             => 'SPU/ENG/2026-441',
            'new_uni_request_date'           => '2026-05-10',
            'is_first_time'                  => true,
            'study_system'                   => 'نظام الساعات المعتمدة والأطروحة',
            'status'                         => 7, // بانتظار إصدار القرار
            'user_id'                        => 1,
            'committee_track'                => 'scientific_production',
            'scientific_production_approved' => true,
            'scientific_production_date'     => '2026-08-15',
            'scientific_production_decision_no' => '94/إنتاج',
            'scientific_production_notes'    => 'تم اعتماد الإنتاج العلمي والأبحاث المنشورة في مجلات محكمة بنجاح.',
            'interview_date'                 => '2026-09-05',
            'created_at'                     => now()->subMonths(3),
            'updated_at'                     => now(),
        ]);

        // Level 4: High School
        DB::table('educations')->insert([
            'application_id'         => $app1,
            'education_level_id'     => 4,
            'country_id'             => $syriaId,
            'section_name'           => 'الفرع العلمي',
            'general_specialization' => 'ثانوية عامة علمية',
            'grant_date'             => '2005-07-01',
            'notes'                  => 'رقم قرار المعادلة الثانوية: سوري/أصيل | تاريخ: 2005-07-01',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 1: Bachelor
        DB::table('educations')->insert([
            'application_id'         => $app1,
            'education_level_id'     => 1,
            'country_id'             => $syriaId,
            'university_id'          => $damascusUniId,
            'faculty'                => 'كلية الهندسة المدنية',
            'department'             => 'الهندسة المدنية',
            'general_specialization' => 'الهندسة المدنية',
            'exact_specialization'   => 'الهندسة الإنشائية',
            'section_name'           => 'الهندسة الإنشائية',
            'graduation_date'        => '2010-07-15',
            'grant_date'             => '2010-09-20',
            'rank'                   => 'جيد جداً',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 2: Master
        DB::table('educations')->insert([
            'application_id'         => $app1,
            'education_level_id'     => 2,
            'country_id'             => $egyptId,
            'university_id'          => $cairoUniId,
            'faculty'                => 'كلية الهندسة',
            'department'             => 'الهندسة الإنشائية',
            'general_specialization' => 'الهندسة الإنشائية',
            'exact_specialization'   => 'هندسة الخرسانة المسلحة والزلازل',
            'section_name'           => 'الهندسة الإنشائية',
            'registration_date'      => '2011-10-01',
            'graduation_date'        => '2014-06-30',
            'grant_date'             => '2014-11-15',
            'defense_date'           => '2014-09-25',
            'thesis_title'           => 'تحليل الاستجابة اللاخطية للأبراج العالية تحت تأثير الزلازل',
            'rank'                   => 'امتياز',
            'notes'                  => 'معادلة الماجستير برقم 245/م.ع تاريخ 2015-04-10',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 3: PhD (Foreign Doctorate)
        $phdEd1 = DB::table('educations')->insertGetId([
            'application_id'         => $app1,
            'education_level_id'     => 3,
            'country_id'             => $egyptId,
            'university_id'          => $cairoUniId,
            'faculty'                => 'كلية الهندسة',
            'department'             => 'الهندسة الإنشائية',
            'general_specialization' => 'الهندسة الإنشائية',
            'exact_specialization'   => 'هندسة الزلازل والمنشآت الذكية',
            'section_name'           => 'هندسة الزلازل والمنشآت الذكية',
            'grant_date'             => '2020-12-10',
            'registration_date'      => '2016-10-01',
            'defense_date'           => '2020-11-28',
            'graduation_date'        => '2020-12-10',
            'study_language'         => 'اللغة العربية والإنجليزية',
            'supervisor'             => 'أ.د. طارق محمود الشريف',
            'thesis_title'           => 'السلوك الديناميكي للمنشآت الخرسانية المسلحة تحت تأثير الأحمال الزلزالية العالية وتطبيقات العزل القاعدي',
            'rank'                   => 'مرتبة الشرف الأولى',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Residence Movements for PhD 1
        DB::table('education_residences')->insert([
            [
                'education_id'  => $phdEd1,
                'page_number'   => '6',
                'exit_airport'  => 'مطار دمشق الدولي',
                'exit_date'     => '2016-09-25',
                'entry_airport' => 'مطار القاهرة الدولي',
                'entry_date'    => '2016-09-25',
                'stamp_details' => 'إقامة دراسية فعلية بمصر - بدء مرحلة الدكتوراه',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'education_id'  => $phdEd1,
                'page_number'   => '18',
                'exit_airport'  => 'مطار القاهرة الدولي',
                'exit_date'     => '2020-12-15',
                'entry_airport' => 'مطار دمشق الدولي',
                'entry_date'    => '2020-12-15',
                'stamp_details' => 'مغادرة نهائية بعد مناقشة أطروحة الدكتوراه ومنح الدرجة',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        // Attachments
        DB::table('education_attachments')->insert([
            [
                'education_id'        => $phdEd1,
                'attachment_type_id'  => 10,
                'file_path'           => 'attachments/sample_phd_certificate.pdf',
                'notes'               => 'نسخة مصدقة أصولاً عن شهادة الدكتوراه الصادرة من جامعة القاهرة',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'education_id'        => $phdEd1,
                'attachment_type_id'  => 12,
                'file_path'           => 'attachments/sample_phd_thesis_summary.pdf',
                'notes'               => 'ملخص الأطروحة والإنتاج العلمي المعتمد',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'education_id'        => $phdEd1,
                'attachment_type_id'  => 24,
                'file_path'           => 'attachments/sample_phd_passport_stamps.pdf',
                'notes'               => 'بيان حركة القدوم والمغادرة وإقامات جمهورية مصر العربية',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);


        // =========================================================================
        // SAMPLE 2: د. سارة منير الخيمي (بانتظار لجنة إنتاج علمي)
        // =========================================================================
        $oldApp2 = DB::table('applications')->where('application_no', 'DOC-FOR-702315')->first();
        if ($oldApp2) {
            DB::table('applications')->where('id', $oldApp2->id)->delete();
        }
        DB::table('equivalence_profiles')->where('national_id', '01020304992')->delete();

        $profile2 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'سارة منير الخيمي',
            'father_name' => 'منير',
            'mother_name' => 'منى الكردي',
            'national_id' => '01020304992',
            'dob'         => '1990-08-22',
            'gender'      => 'أنثى',
            'is_syrian'   => true,
            'job_title'   => 'أستاذة مساعدة / دكتوراه في هندسة الحواسيب والذكاء الصنعي',
            'phone'       => '0113344552',
            'mobile'      => '0955667788',
            'email'       => 'sara.kheimi@kalamoon.edu.sy',
            'address'     => 'ريف دمشق - دير عطية - سكن هيئة التدريس',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app2 = DB::table('applications')->insertGetId([
            'candidate_id'                => $profile2,
            'application_no'              => 'DOC-FOR-702315',
            'request_type'                => 7, // دكتورة خارجية
            'work_university_id'          => $kalamoonUniId,
            'work_faculty'                => 'كلية تكنولوجيا المعلومات',
            'work_department'             => 'علوم الحاسوب والذكاء الاصطناعي',
            'new_uni_request_no'          => 'UOK/IT/2026-88',
            'new_uni_request_date'        => '2026-06-18',
            'is_first_time'               => true,
            'study_system'                => 'سنوي / أطروحة بحثية',
            'status'                      => 5, // بانتظار لجنة إنتاج علمي
            'user_id'                     => 1,
            'committee_track'             => 'scientific_production',
            'created_at'                  => now()->subMonths(2),
            'updated_at'                  => now(),
        ]);

        // Level 4: High School
        DB::table('educations')->insert([
            'application_id'         => $app2,
            'education_level_id'     => 4,
            'country_id'             => $syriaId,
            'section_name'           => 'علمي',
            'general_specialization' => 'ثانوية عامة علمية',
            'grant_date'             => '2008-07-10',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 1: Bachelor
        DB::table('educations')->insert([
            'application_id'         => $app2,
            'education_level_id'     => 1,
            'country_id'             => $syriaId,
            'university_id'          => $damascusUniId,
            'faculty'                => 'كلية هندسة تكنولوجيا المعلومات',
            'department'             => 'هندسة البرمجيات ونظم المعلومات',
            'general_specialization' => 'هندسة البرمجيات',
            'exact_specialization'   => 'هندسة البرمجيات',
            'section_name'           => 'هندسة البرمجيات',
            'graduation_date'        => '2013-07-20',
            'grant_date'             => '2013-09-15',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 2: Master
        DB::table('educations')->insert([
            'application_id'         => $app2,
            'education_level_id'     => 2,
            'country_id'             => $jordanId,
            'university_id'          => $jordanUniId,
            'faculty'                => 'كلية الملك عبد الله الثاني لتكنولوجيا المعلومات',
            'department'             => 'علم الحاسوب',
            'general_specialization' => 'علم الحاسوب',
            'exact_specialization'   => 'علم الحاسوب ونظم المعلومات',
            'section_name'           => 'علم الحاسوب',
            'registration_date'      => '2014-09-15',
            'graduation_date'        => '2017-06-30',
            'grant_date'             => '2017-06-30',
            'notes'                  => 'قرار المعادلة: 188/م.ع تاريخ 2017-10-15',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 3: PhD
        $phdEd2 = DB::table('educations')->insertGetId([
            'application_id'         => $app2,
            'education_level_id'     => 3,
            'country_id'             => $jordanId,
            'university_id'          => $jordanUniId,
            'faculty'                => 'كلية تكنولوجيا المعلومات',
            'department'             => 'علوم الحاسوب',
            'general_specialization' => 'علوم الحاسوب',
            'exact_specialization'   => 'الذكاء الاصطناعي ومعالجة الصور الطبية',
            'section_name'           => 'الذكاء الاصطناعي ومعالجة الصور الطبية',
            'grant_date'             => '2022-08-25',
            'registration_date'      => '2018-09-15',
            'defense_date'           => '2022-07-20',
            'graduation_date'        => '2022-08-25',
            'study_language'         => 'اللغة الإنجليزية',
            'supervisor'             => 'أ.د. محمد إبراهيم قاسم',
            'thesis_title'           => 'نماذج التعلّم العميق المتقدمة في معالجة وتحليل الصور الطبية الرنينية والكشف المبكر عن الأورام الدقيقة',
            'rank'                   => 'امتياز مع مرتبة الشرف',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        DB::table('education_residences')->insert([
            'education_id'  => $phdEd2,
            'page_number'   => '8',
            'exit_airport'  => 'مطار دمشق الدولي',
            'exit_date'     => '2018-09-10',
            'entry_airport' => 'مطار الملكة علياء الدولي',
            'entry_date'    => '2018-09-10',
            'stamp_details' => 'إقامة دراسية متواصلة في الأردن لأغراض الدكتوراه',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::table('education_attachments')->insert([
            'education_id'       => $phdEd2,
            'attachment_type_id' => 10,
            'file_path'          => 'attachments/sample_phd_certificate.pdf',
            'notes'              => 'شهادة الدكتوراه الأصلية المصدقة من سفارة الجمهورية العربية السورية في عمان',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);


        // =========================================================================
        // SAMPLE 3: د. مأمون حسام الدين النحاس (تحت التدقيق الأولي / لجنة عامة)
        // =========================================================================
        $oldApp3 = DB::table('applications')->where('application_no', 'DOC-FOR-703841')->first();
        if ($oldApp3) {
            DB::table('applications')->where('id', $oldApp3->id)->delete();
        }
        DB::table('equivalence_profiles')->where('national_id', '01020304993')->delete();

        $profile3 = DB::table('equivalence_profiles')->insertGetId([
            'full_name'   => 'مأمون حسام الدين النحاس',
            'father_name' => 'حسام الدين',
            'mother_name' => 'سعاد التغلبي',
            'national_id' => '01020304993',
            'dob'         => '1989-11-04',
            'gender'      => 'ذكر',
            'is_syrian'   => true,
            'job_title'   => 'مهندس اتصالات / دكتوراه في هندسة النظم الإلكترونية',
            'phone'       => '0114455663',
            'mobile'      => '0966778899',
            'email'       => 'mamoun.nahas@spu.edu.sy',
            'address'     => 'دمشق - أبو رمانة - شارع الجلاء',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $app3 = DB::table('applications')->insertGetId([
            'candidate_id'                => $profile3,
            'application_no'              => 'DOC-FOR-703841',
            'request_type'                => 7, // دكتورة خارجية
            'work_university_id'          => $spuUniId,
            'work_faculty'                => 'كلية هندسة الحواسيب والاتصالات',
            'work_department'             => 'هندسة الاتصالات والإلكترونيات',
            'new_uni_request_no'          => 'SPU/COMM/2026-102',
            'new_uni_request_date'        => '2026-07-25',
            'is_first_time'               => true,
            'study_system'                => 'فصلي / ساعات بحثية',
            'status'                      => 2, // تحت التدقيق الأولي
            'user_id'                     => 1,
            'created_at'                  => now()->subDays(20),
            'updated_at'                  => now(),
        ]);

        // Level 1: Bachelor
        DB::table('educations')->insert([
            'application_id'         => $app3,
            'education_level_id'     => 1,
            'country_id'             => $syriaId,
            'university_id'          => $damascusUniId,
            'faculty'                => 'كلية الهندسة الميكانيكية والكهربائية',
            'department'             => 'هندسة الإلكترونيات والاتصالات',
            'general_specialization' => 'هندسة الإلكترونيات والاتصالات',
            'exact_specialization'   => 'هندسة الاتصالات',
            'section_name'           => 'هندسة الاتصالات',
            'graduation_date'        => '2012-07-15',
            'grant_date'             => '2012-09-01',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 2: Master
        DB::table('educations')->insert([
            'application_id'         => $app3,
            'education_level_id'     => 2,
            'country_id'             => $lebanonId,
            'university_id'          => $aubUniId,
            'faculty'                => 'كلية الهندسة والعمارة',
            'department'             => 'الهندسة الكهربائية وهندسة الحاسوب',
            'general_specialization' => 'الهندسة الكهربائية وهندسة الحاسوب',
            'exact_specialization'   => 'هندسة النظم اللاسلكية',
            'section_name'           => 'هندسة النظم اللاسلكية',
            'graduation_date'        => '2016-06-25',
            'grant_date'             => '2016-06-25',
            'notes'                  => 'معادلة الماجستير: 312/م.ع تاريخ 2016-11-20',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // Level 3: PhD
        $phdEd3 = DB::table('educations')->insertGetId([
            'application_id'         => $app3,
            'education_level_id'     => 3,
            'country_id'             => $lebanonId,
            'university_id'          => $aubUniId,
            'faculty'                => 'كلية الهندسة والعمارة',
            'department'             => 'الهندسة الكهربائية وهندسة الحاسوب',
            'general_specialization' => 'هندسة الإلكترونيات والاتصالات',
            'exact_specialization'   => 'نظم الاتصالات اللاسلكية وشبكات الجيل الخامس',
            'section_name'           => 'نظم الاتصالات اللاسلكية وشبكات الجيل الخامس',
            'grant_date'             => '2021-07-15',
            'thesis_title'           => 'تحسين كفاءة الطاقة والإنتاجية في شبكات الاتصالات اللاسلكية الكثيفة من الجيل الخامس 5G وما بعده',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        DB::table('education_residences')->insert([
            'education_id'  => $phdEd3,
            'page_number'   => '5',
            'exit_airport'  => 'جديدة يابوس',
            'exit_date'     => '2017-09-01',
            'entry_airport' => 'المصنع - لبنان',
            'entry_date'    => '2017-09-01',
            'stamp_details' => 'إقامة دراسية في لبنان',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::table('education_attachments')->insert([
            'education_id'       => $phdEd3,
            'attachment_type_id' => 10,
            'file_path'          => 'attachments/sample_phd_certificate.pdf',
            'notes'              => 'شهادة الدكتوراه الصادرة من الجامعة الأمريكية في بيروت',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Application;
use App\Models\EquivalenceProfile;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\ApplicationExperience;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\LookupAttachmentType;
use App\Models\User;

class TestDecisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('🚀 بدء توليد المعاملات الاختبارية الثلاث مع ملفات الـ PDF المرفقة...');

        // 1. إعداد المجلدات في التخزين العام
        $testDocsDir = storage_path('app/public/test_docs');
        $attachmentsDir = storage_path('app/public/attachments');
        if (!is_dir($testDocsDir)) {
            mkdir($testDocsDir, 0777, true);
        }
        if (!is_dir($attachmentsDir)) {
            mkdir($attachmentsDir, 0777, true);
        }

        // 2. ضبط البلدان والجامعات وحساب الجامعة المعتمد
        $syria = LookupCountry::firstOrCreate(['name' => 'سوريا']);
        $lebanon = LookupCountry::firstOrCreate(['name' => 'لبنان']);
        $sudan = LookupCountry::firstOrCreate(['name' => 'السودان']);

        $damascusUni = LookupUniversity::firstOrCreate(['name' => 'جامعة دمشق'], ['country_id' => $syria->id]);
        $aleppoUni   = LookupUniversity::firstOrCreate(['name' => 'جامعة حلب'], ['country_id' => $syria->id]);
        $lebaneseUni = LookupUniversity::firstOrCreate(['name' => 'الجامعة اللبنانية'], ['country_id' => $lebanon->id]);
        $omdurmanUni = LookupUniversity::firstOrCreate(['name' => 'جامعة أم درمان الإسلامية'], ['country_id' => $sudan->id]);
        $dawaUni     = LookupUniversity::firstOrCreate(['name' => 'كلية الدعوة الجامعية'], ['country_id' => $lebanon->id]);

        // الجامعة الخاصة النشطة (جامعة الجزيرة أو أول جامعة خاصة)
        $workUni = LookupUniversity::where('name', 'LIKE', '%الجزيرة%')->first()
            ?? LookupUniversity::where('is_private', 1)->first()
            ?? LookupUniversity::find(8)
            ?? $damascusUni;

        $uniUser = User::where('university_id', $workUni->id)->first()
            ?? User::where('email', 'like', '%uni%')->first()
            ?? User::first();

        $uniUserId = $uniUser?->id ?? 1;

        // تنظيف أي سجلات سابقة للمرشحين التجريبيين لمنع تكرار أو بقاء بيانات ناقصة
        $testNationalIds = ['01020099881', '02040055442', '01030077663'];
        foreach ($testNationalIds as $nid) {
            $existingProf = EquivalenceProfile::where('national_id', $nid)->first();
            if ($existingProf) {
                $oldApps = Application::where('candidate_id', $existingProf->id)->get();
                foreach ($oldApps as $oldApp) {
                    foreach ($oldApp->educations as $oldEdu) {
                        EducationAttachment::where('education_id', $oldEdu->id)->delete();
                        $oldEdu->delete();
                    }
                    ApplicationExperience::where('application_id', $oldApp->id)->delete();
                    $oldApp->delete();
                }
            }
        }

        // =========================================================================
        // المعاملة الأولى: ماجستير تطبيقي (مرشح ذكر - داخلي)
        // =========================================================================
        $this->command?->info('1️⃣ إنشاء معاملة: ماجستير داخلي تطبيقي (طارق محمود العلي)...');

        $profile1 = EquivalenceProfile::updateOrCreate(
            ['national_id' => '01020099881'],
            [
                'full_name'      => 'طارق محمود العلي',
                'father_name'    => 'محمود',
                'mother_name'    => 'سلوى',
                'gender'         => 'ذكر',
                'dob'            => '1996-04-10',
                'job_title'      => 'مهندس',
                'nationality_id' => $syria->id,
                'phone'          => '0933112233',
                'mobile'         => '0933112233',
                'email'          => 'tarek.ali@example.com',
                'is_syrian'      => 1,
            ]
        );

        $appNo1 = 'MA-APP-2026-' . rand(1000, 9999);
        $app1 = Application::create([
            'candidate_id'         => $profile1->id,
            'application_no'       => $appNo1,
            'request_type'         => 1, // ماجستير داخلي - تطبيقي
            'status'               => 2, // تحت التدقيق الأولي
            'work_university_id'   => $workUni->id,
            'user_id'              => $uniUserId,
            'work_faculty'         => 'كلية الهندسة المعلوماتية',
            'work_department'      => 'الذكاء الصنعي وعلم البيانات',
            'new_uni_request_no'   => '410',
            'new_uni_request_date' => '2026-09-15',
            'study_system'         => 'تطبيقي',
            'is_first_time'        => 1,
            'has_previous_degree'  => 0,
        ]);

        // شهادات المعاملة 1
        // 1. الثانوية
        $edu1_sec = Education::create([
            'application_id'     => $app1->id,
            'education_level_id' => 4, // ثانوية عامة
            'country_id'         => $syria->id,
            'section_name'       => 'علمي',
            'graduation_date'    => '2014-01-01',
            'grant_date'         => '2014-01-01',
        ]);
        $this->attachPdf($edu1_sec, 1, 'شهادة الدراسة الثانوية العامة (علمي)', $profile1->full_name, $appNo1);

        // 2. الإجازة
        $edu1_ba = Education::create([
            'application_id'         => $app1->id,
            'education_level_id'     => 1, // إجازة جامعية
            'country_id'             => $syria->id,
            'university_id'          => $damascusUni->id,
            'faculty'                => 'كلية الهندسة المعلوماتية',
            'department'             => 'قسم هندسة البرمجيات',
            'general_specialization' => 'الهندسة المعلوماتية',
            'exact_specialization'   => 'هندسة البرمجيات ونظم المعلومات',
            'graduation_date'        => '2019-08-20',
            'grant_date'             => '2019-08-20',
        ]);
        $this->attachPdf($edu1_ba, 3, 'مصدقة الإجازة الجامعية في الهندسة المعلوماتية', $profile1->full_name, $appNo1);
        $this->attachPdf($edu1_ba, 13, 'صورة البطاقة الشخصية للمرشح', $profile1->full_name, $appNo1);

        // 3. الماجستير
        $edu1_ma = Education::create([
            'application_id'         => $app1->id,
            'education_level_id'     => 2, // ماجستير
            'country_id'             => $syria->id,
            'university_id'          => $damascusUni->id,
            'faculty'                => 'كلية الهندسة المعلوماتية',
            'department'             => 'قسم الذكاء الصنعي',
            'general_specialization' => 'الهندسة المعلوماتية',
            'exact_specialization'   => 'الذكاء الصنعي وعلم البيانات',
            'graduation_date'        => '2024-06-15',
            'grant_date'             => '2024-06-15',
            'thesis_title'           => 'تطوير خوارزميات التعلم العميق لتحليل الصور الطبية',
        ]);
        $this->attachPdf($edu1_ma, 6, 'مصدقة شهادة الماجستير في الذكاء الصنعي وعلم البيانات', $profile1->full_name, $appNo1);
        $this->attachPdf($edu1_ma, 8, 'ملخص رسالة الماجستير باللغة العربية', $profile1->full_name, $appNo1);
        $this->attachPdf($edu1_ma, 15, 'كتاب الجامعة رقم 410 تاريخ 2026-09-15', $profile1->full_name, $appNo1);
        $this->attachPdf($edu1_ma, 16, 'إيصال تسديد رسم تعادل الماجستير', $profile1->full_name, $appNo1);
        $this->attachPdf($edu1_ma, 14, 'السيرة الذاتية المفصلة', $profile1->full_name, $appNo1);

        // الخبرة التدريسية (أقل من سنتين: سنة وشهران = 426 يوماً)
        ApplicationExperience::create([
            'application_id'   => $app1->id,
            'education_id'     => $edu1_ma->id,
            'institution_name' => $workUni->name,
            'role_or_subject'  => 'مدرس عملي - الذكاء الصنعي وتعلم الآلة',
            'start_date'       => '2024-08-01',
            'end_date'         => '2025-10-01',
            'duration_days'    => 426,
            'notes'            => 'تدريس الجوانب العملية والتطبيقية (أقل من سنتين)',
        ]);


        // =========================================================================
        // المعاملة الثانية: ماجستير نظري (مرشحة أنثى - خارجي)
        // =========================================================================
        $this->command?->info('2️⃣ إنشاء معاملة: ماجستير خارجي نظري (فاطمة مصطفى عبطيني)...');

        $profile2 = EquivalenceProfile::updateOrCreate(
            ['national_id' => '02010077662'],
            [
                'full_name'      => 'فاطمة مصطفى عبطيني',
                'father_name'    => 'مصطفى',
                'mother_name'    => 'آمنة',
                'gender'         => 'أنثى',
                'dob'            => '1983-07-22',
                'job_title'      => 'مدرسة',
                'nationality_id' => $syria->id,
                'phone'          => '0944556677',
                'mobile'         => '0944556677',
                'email'          => 'fatima.abtinie@example.com',
                'is_syrian'      => 1,
            ]
        );

        $appNo2 = 'MA-FOR-2026-' . rand(1000, 9999);
        $app2 = Application::create([
            'candidate_id'         => $profile2->id,
            'application_no'       => $appNo2,
            'request_type'         => 6, // ماجستير خارجي - نظري
            'status'               => 2, // تحت التدقيق الأولي
            'work_university_id'   => $workUni->id,
            'user_id'              => $uniUserId,
            'work_faculty'         => 'كلية الهندسة المعلوماتية',
            'work_department'      => 'النمذجة والمحاكاة المعلوماتية',
            'new_uni_request_no'   => '1641',
            'new_uni_request_date' => '2026-10-04',
            'study_system'         => 'نظري - بحثي',
            'is_first_time'        => 1,
            'has_previous_degree'  => 0,
        ]);

        // شهادات المعاملة 2
        // 1. الثانوية
        $edu2_sec = Education::create([
            'application_id'     => $app2->id,
            'education_level_id' => 4, // ثانوية عامة
            'country_id'         => $syria->id,
            'section_name'       => 'علمي',
            'graduation_date'    => '2000-01-01',
            'grant_date'         => '2000-01-01',
        ]);
        $this->attachPdf($edu2_sec, 1, 'شهادة الدراسة الثانوية العامة (علمي)', $profile2->full_name, $appNo2);

        // 2. الإجازة
        $edu2_ba = Education::create([
            'application_id'         => $app2->id,
            'education_level_id'     => 1, // إجازة جامعية
            'country_id'             => $syria->id,
            'university_id'          => $aleppoUni->id,
            'faculty'                => 'كلية الهندسة المعلوماتية',
            'general_specialization' => 'الهندسة المعلوماتية',
            'exact_specialization'   => 'الهندسة المعلوماتية',
            'graduation_date'        => '2005-07-10',
            'grant_date'             => '2005-07-10',
        ]);
        $this->attachPdf($edu2_ba, 3, 'مصدقة الإجازة في الهندسة المعلوماتية - جامعة حلب', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ba, 13, 'صورة الهوية الشخصية للمرشحة', $profile2->full_name, $appNo2);

        // 3. الماجستير (خارجي - لبنان)
        $edu2_ma = Education::create([
            'application_id'         => $app2->id,
            'education_level_id'     => 2, // ماجستير
            'country_id'             => $lebanon->id,
            'university_id'          => $lebaneseUni->id,
            'faculty'                => 'كلية العلوم',
            'general_specialization' => 'المعلوماتية',
            'exact_specialization'   => 'النمذجة والمحاكاة المعلوماتية',
            'graduation_date'        => '2007-11-15',
            'grant_date'             => '2007-11-15',
            'thesis_title'           => 'النمذجة والمحاكاة للأنظمة الموزعة',
        ]);
        $this->attachPdf($edu2_ma, 6, 'شهادة الماجستير البحثي الصادرة من الجامعة اللبنانية', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ma, 9, 'قرار معادلة شهادة الماجستير غير السورية', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ma, 8, 'ملخص رسالة الماجستير باللغة العربية', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ma, 15, 'كتاب الجامعة رقم 1641 تاريخ 2026-10-04', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ma, 16, 'إيصال تسديد رسم تعادل الماجستير الخارجي', $profile2->full_name, $appNo2);
        $this->attachPdf($edu2_ma, 20, 'شهادة خبرة تدريسية مستوفية لشرط السنتين (3 سنوات)', $profile2->full_name, $appNo2);

        // الخبرة التدريسية (مستوفية لشرط السنتين: 3 سنوات = 1096 يوماً)
        ApplicationExperience::create([
            'application_id'   => $app2->id,
            'education_id'     => $edu2_ma->id,
            'institution_name' => $workUni->name,
            'role_or_subject'  => 'تدريس مقررات النمذجة والمحاكاة وأنظمة التشغيل الموزعة',
            'start_date'       => '2022-09-01',
            'end_date'         => '2025-09-01',
            'duration_days'    => 1096,
            'notes'            => 'خبرة تدريسية جامعية مستمرة مستوفية لشرط السنتين',
        ]);


        // =========================================================================
        // المعاملة الثالثة: تعادل دكتوراه (مرشحة أنثى - خارجي)
        // =========================================================================
        $this->command?->info('3️⃣ إنشاء معاملة: تعادل دكتوراه خارجية (نور الهدى عبد الكريم الحداد)...');

        $profile3 = EquivalenceProfile::updateOrCreate(
            ['national_id' => '01010055443'],
            [
                'full_name'      => 'نور الهدى عبد الكريم الحداد',
                'father_name'    => 'عبد الكريم',
                'mother_name'    => 'هدى',
                'gender'         => 'أنثى',
                'dob'            => '1984-03-12',
                'job_title'      => 'دكتورة محاضرة',
                'nationality_id' => $syria->id,
                'phone'          => '0955998877',
                'mobile'         => '0955998877',
                'email'          => 'nour.haddad@example.com',
                'is_syrian'      => 1,
            ]
        );

        $appNo3 = 'PHD-EXT-2026-' . rand(1000, 9999);
        $app3 = Application::create([
            'candidate_id'         => $profile3->id,
            'application_no'       => $appNo3,
            'request_type'         => 7, // دكتورة خارجية
            'status'               => 2, // تحت التدقيق الأولي
            'work_university_id'   => $workUni->id,
            'user_id'              => $uniUserId,
            'work_faculty'         => 'كلية الشريعة والدراسات الإسلامية',
            'work_department'      => 'التفسير وعلوم القرآن',
            'new_uni_request_no'   => '105',
            'new_uni_request_date' => '2026-10-01',
            'is_first_time'        => 1,
            'has_previous_degree'  => 0,
        ]);

        // شهادات المعاملة 3
        // 1. الثانوية
        $edu3_sec = Education::create([
            'application_id'     => $app3->id,
            'education_level_id' => 4, // ثانوية عامة
            'country_id'         => $syria->id,
            'section_name'       => 'أدبي',
            'graduation_date'    => '2002-01-01',
            'grant_date'         => '2002-01-01',
        ]);
        $this->attachPdf($edu3_sec, 1, 'شهادة الدراسة الثانوية العامة (أدبي)', $profile3->full_name, $appNo3);

        // 2. الإجازة
        $edu3_ba = Education::create([
            'application_id'         => $app3->id,
            'education_level_id'     => 1, // إجازة جامعية
            'country_id'             => $syria->id,
            'university_id'          => $damascusUni->id,
            'faculty'                => 'كلية الشريعة',
            'general_specialization' => 'الشريعة',
            'exact_specialization'   => 'الشريعة',
            'graduation_date'        => '2006-08-01',
            'grant_date'             => '2006-08-01',
        ]);
        $this->attachPdf($edu3_ba, 3, 'مصدقة الإجازة في الشريعة - جامعة دمشق', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_ba, 13, 'صورة البطاقة الشخصية للمرشحة', $profile3->full_name, $appNo3);

        // 3. الماجستير (السودان)
        $edu3_ma = Education::create([
            'application_id'         => $app3->id,
            'education_level_id'     => 2, // ماجستير
            'country_id'             => $sudan->id,
            'university_id'          => $omdurmanUni->id,
            'faculty'                => 'كلية الشريعة والقانون',
            'general_specialization' => 'الشريعة والقانون',
            'exact_specialization'   => 'الفقه المقارن',
            'graduation_date'        => '2011-05-20',
            'grant_date'             => '2011-05-20',
            'thesis_title'           => 'دراسات فقهية مقارنة في المعاملات المالية المعاصرة',
        ]);
        $this->attachPdf($edu3_ma, 6, 'شهادة الماجستير - جامعة أم درمان الإسلامية', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_ma, 9, 'قرار معادلة شهادة الماجستير الصادرة من السودان', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_ma, 8, 'ملخص رسالة الماجستير باللغة العربية', $profile3->full_name, $appNo3);

        // 4. الدكتوراه (لبنان)
        $edu3_phd = Education::create([
            'application_id'         => $app3->id,
            'education_level_id'     => 3, // دكتوراه
            'country_id'             => $lebanon->id,
            'university_id'          => $dawaUni->id,
            'faculty'                => 'كلية الدراسات الإسلامية',
            'general_specialization' => 'الدراسات الإسلامية',
            'exact_specialization'   => 'شعبة التفسير وعلوم القرآن',
            'graduation_date'        => '2024-04-10',
            'grant_date'             => '2024-04-10',
            'thesis_title'           => 'مناهج المفسرين في الدراسات القرآنية المعاصرة',
        ]);
        $this->attachPdf($edu3_phd, 10, 'شهادة الدكتوراه في الدراسات الإسلامية - كلية الدعوة الجامعية', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_phd, 12, 'ملخص أطروحة الدكتوراه باللغة العربية', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_phd, 15, 'كتاب الجامعة رقم 105 تاريخ 2026-10-01', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_phd, 17, 'إيصال تسديد رسم تعادل الدكتوراه الخارجية', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_phd, 14, 'السيرة الذاتية للمرشحة', $profile3->full_name, $appNo3);
        $this->attachPdf($edu3_phd, 20, 'شهادة خبرة تدريسية ومحاضرات جامعية', $profile3->full_name, $appNo3);

        // الخبرة التدريسية
        ApplicationExperience::create([
            'application_id'   => $app3->id,
            'education_id'     => $edu3_phd->id,
            'institution_name' => $workUni->name,
            'role_or_subject'  => 'أستاذ محاضر - علوم القرآن والتفسير وأصول الفقه',
            'start_date'       => '2024-05-01',
            'end_date'         => '2026-09-01',
            'duration_days'    => 853,
            'notes'            => 'تدريس مقررات التفسير وعلوم القرآن وأصول الفقه',
        ]);

        $this->command?->info('✅ تم توليد كافة المعاملات والمرفقات بنجاح!');
        $this->command?->info('📌 1. ماجستير تطبيقي: ' . $appNo1 . ' | ID: ' . $app1->id);
        $this->command?->info('📌 2. ماجستير نظري: ' . $appNo2 . ' | ID: ' . $app2->id);
        $this->command?->info('📌 3. دكتوراه خارجية: ' . $appNo3 . ' | ID: ' . $app3->id);
    }

    /**
     * توليد ملف PDF خفيف وأنيق للمرفق وتخزينه وربطه
     */
    private function attachPdf(Education $education, int $attachmentTypeId, string $docName, string $candidateName, string $appNo): EducationAttachment
    {
        $safeDoc = preg_replace('/[^\p{L}\p{N}\-_]/u', '_', $docName);
        $fileName = 'doc_' . $appNo . '_type' . $attachmentTypeId . '_' . substr(md5(uniqid()), 0, 6) . '.pdf';
        
        $relativeFilePath = 'test_docs/' . $fileName;
        $fullPath = storage_path('app/public/' . $relativeFilePath);

        $html = '<!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
                body {
                    font-family: "DejaVu Sans", Tahoma, sans-serif;
                    direction: rtl;
                    text-align: center;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 50px 30px;
                    color: #1e293b;
                }
                .border-box {
                    border: 3px double #1e3a8a;
                    border-radius: 12px;
                    padding: 45px 30px;
                    margin: 20px auto;
                    max-width: 600px;
                    background-color: #f8fafc;
                }
                .header-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #1e3a8a;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #cbd5e1;
                    padding-bottom: 12px;
                    line-height: 1.6;
                }
                .doc-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #0f172a;
                    margin: 20px 0;
                    background: #e2e8f0;
                    padding: 12px;
                    border-radius: 6px;
                }
                .info-row {
                    font-size: 14px;
                    margin: 10px 0;
                    color: #334155;
                    line-height: 1.5;
                }
                .footer-note {
                    margin-top: 35px;
                    font-size: 12px;
                    color: #64748b;
                    border-top: 1px dashed #cbd5e1;
                    padding-top: 12px;
                }
            </style>
        </head>
        <body>
            <div class="border-box">
                <div class="header-title">
                    الجمهورية العربية السورية<br>
                    وزارة التعليم العالي والبحث العلمي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </div>
                <div class="doc-title">وثيقة تجريبية للاختبار: ' . htmlspecialchars($docName) . '</div>
                <div class="info-row"><strong>المرشح:</strong> ' . htmlspecialchars($candidateName) . '</div>
                <div class="info-row"><strong>رقم المعاملة:</strong> ' . htmlspecialchars($appNo) . '</div>
                <div class="info-row"><strong>تاريخ الإدراج:</strong> ' . date('Y-m-d') . '</div>
                <div class="footer-note">وثيقة PDF اختبارية مصدقة إلكترونياً لأغراض الفحص والتدقيق الأكاديمي</div>
            </div>
        </body>
        </html>';

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        file_put_contents($fullPath, $pdf->output());

        return EducationAttachment::create([
            'education_id'       => $education->id,
            'attachment_type_id' => $attachmentTypeId,
            'file_path'          => $relativeFilePath,
            'notes'              => $docName,
        ]);
    }
}

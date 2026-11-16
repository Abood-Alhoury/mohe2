<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Application;
use App\Models\EquivalenceProfile;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\User;

class AppliedMasterDraftsSeeder extends Seeder
{
    /**
     * تفريغ بيانات النظام والأرشيف بالكامل ثم إدراج 3 مسودات ماجستير تطبيقي مكتملة لجامعة الجزيرة الخاصة.
     */
    public function run(): void
    {
        // 1. تصفير جميع الجداول التشغيلية والأرشيف وتنظيف ملفات التخزين القديمة
        $this->command?->info('🧹 جاري تفريغ جميع البيانات السابقة وتصفير الأرشيف...');
        $this->call(WipeSystemDataSeeder::class);

        // 2. التحقق من الثوابت المرجعية وحساب جامعة الجزيرة الخاصة
        $syria = LookupCountry::firstOrCreate(['name' => 'سوريا']);

        $damascusUni = LookupUniversity::firstOrCreate(['name' => 'جامعة دمشق'], ['country_id' => $syria->id]);
        $aleppoUni   = LookupUniversity::firstOrCreate(['name' => 'جامعة حلب'], ['country_id' => $syria->id]);
        $tishreenUni = LookupUniversity::firstOrCreate(['name' => 'جامعة تشرين'], ['country_id' => $syria->id]);
        $baathUni    = LookupUniversity::firstOrCreate(['name' => 'جامعة البعث'], ['country_id' => $syria->id]);

        $alJazeeraUni = LookupUniversity::where('name', 'LIKE', '%الجزيرة%')->first()
            ?? LookupUniversity::find(8)
            ?? LookupUniversity::create(['name' => 'جامعة الجزيرة الخاصة', 'country_id' => $syria->id]);

        $alJazeeraUser = User::where('university_id', $alJazeeraUni->id)->first()
            ?? User::where('email', 'uni_aljazeera@uni.edu.sy')->first()
            ?? User::whereHas('role', fn($q) => $q->where('name', 'university'))->first();

        $uniUserId = $alJazeeraUser?->id ?? 3;

        $hsLevelId = LookupEducationLevel::where('name', 'like', '%ثانوية%')->value('id') ?? 4;
        $baLevelId = LookupEducationLevel::where('name', 'like', '%إجازة%')->value('id') ?? 1;
        $maLevelId = LookupEducationLevel::where('name', 'like', '%ماجستير%')->value('id') ?? 2;

        // 3. تعريف بيانات الطلاب الثلاثة (ماجستير داخلي - تطبيقي في المسودات)
        $students = [
            [
                'profile' => [
                    'full_name'      => 'طارق محمود العلي',
                    'father_name'    => 'محمود',
                    'mother_name'    => 'سلوى الحسن',
                    'national_id'    => '01020099881',
                    'dob'            => '1995-04-10',
                    'job_title'      => 'مهندس برمجيات',
                    'gender'         => 'ذكر',
                    'email'          => 'tarek.ali@aljazeera.edu.sy',
                    'mobile'         => '0933112233',
                    'phone'          => '0112233445',
                    'address'        => 'دمشق - المزة - شارع الجلاء - بناء رقم 14',
                ],
                'application' => [
                    'application_no'       => 'MA-APP-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم الذكاء الصنعي',
                    'new_uni_request_no'   => '101/ج',
                    'new_uni_request_date' => '2026-11-10',
                ],
                'hs' => [
                    'section_name' => 'علمي',
                    'grant_date'   => '2013-01-01',
                ],
                'ba' => [
                    'university_id'        => $damascusUni->id,
                    'faculty'              => 'كلية الهندسة المعلوماتية',
                    'department'           => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'specialization'       => 'هندسة البرمجيات',
                    'rank'                 => 'جيد جداً',
                    'grant_date'           => '2018-08-20',
                ],
                'ma' => [
                    'university_id'          => $damascusUni->id,
                    'faculty'                => 'كلية الهندسة المعلوماتية',
                    'department'             => 'قسم الذكاء الصنعي',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'exact_specialization'   => 'الذكاء الصنعي وعلم البيانات',
                    'supervisor_name'        => 'أ.د. حسان مصطفى الأحمد',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2021-10-01',
                    'defense_date'           => '2024-05-15',
                    'grant_date'             => '2024-06-25',
                    'thesis_title'           => 'تطوير خوارزميات التعلم العميق لتحليل الصور الطبية وتشخيص الأورام',
                ],
                'include_optional_attachments' => true,
            ],
            [
                'profile' => [
                    'full_name'      => 'سارة عبد الرحمن الخطيب',
                    'father_name'    => 'عبد الرحمن',
                    'mother_name'    => 'ندى الصباغ',
                    'national_id'    => '02040088772',
                    'dob'            => '1996-09-22',
                    'job_title'      => 'صيدلانية',
                    'gender'         => 'أنثى',
                    'email'          => 'sara.khatib@aljazeera.edu.sy',
                    'mobile'         => '0944556677',
                    'phone'          => '0212244668',
                    'address'        => 'حلب - الفرقان - شارع الأكاديمية - بناء رقم 7',
                ],
                'application' => [
                    'application_no'       => 'MA-APP-2026-0002',
                    'work_faculty'         => 'كلية الصيدلة',
                    'work_department'      => 'قسم الكيمياء الصيدلانية',
                    'new_uni_request_no'   => '102/ج',
                    'new_uni_request_date' => '2026-11-12',
                ],
                'hs' => [
                    'section_name' => 'علمي',
                    'grant_date'   => '2014-01-01',
                ],
                'ba' => [
                    'university_id'        => $aleppoUni->id,
                    'faculty'              => 'كلية الصيدلة',
                    'department'           => 'قسم العلوم الصيدلانية',
                    'specialization'       => 'الصيدلة والكيمياء الصيدلانية',
                    'rank'                 => 'ممتاز',
                    'grant_date'           => '2019-09-05',
                ],
                'ma' => [
                    'university_id'          => $aleppoUni->id,
                    'faculty'                => 'كلية الصيدلة',
                    'department'             => 'قسم الكيمياء الصيدلانية',
                    'general_specialization' => 'العلوم الصيدلانية',
                    'exact_specialization'   => 'المراقبة الدوائية والتحليل الصيدلاني',
                    'supervisor_name'        => 'أ.د. ريم سليمان الحلبي',
                    'rank'                   => 'جيد جداً',
                    'registration_date'      => '2021-11-10',
                    'defense_date'           => '2024-07-02',
                    'grant_date'             => '2024-08-18',
                    'thesis_title'           => 'طرق تحليلية مطورة لتقدير بعض المركبات الدوائية باستخدام الكروماتوغرافيا السائلة عالية الأداء',
                ],
                'include_optional_attachments' => true,
            ],
            [
                'profile' => [
                    'full_name'      => 'خالد يوسف المنصور',
                    'father_name'    => 'يوسف',
                    'mother_name'    => 'فاطمة الدروبي',
                    'national_id'    => '04050033229',
                    'dob'            => '1994-11-05',
                    'job_title'      => 'مهندس مدني',
                    'gender'         => 'ذكر',
                    'email'          => 'khaled.mansour@aljazeera.edu.sy',
                    'mobile'         => '0955889900',
                    'phone'          => '0413355779',
                    'address'        => 'اللاذقية - المشروع السابع - شارع الثورة - بناء رقم 22',
                ],
                'application' => [
                    'application_no'       => 'MA-APP-2026-0003',
                    'work_faculty'         => 'كلية الهندسة المدنية',
                    'work_department'      => 'قسم الهندسة الإنشائية',
                    'new_uni_request_no'   => '103/ج',
                    'new_uni_request_date' => '2026-11-14',
                ],
                'hs' => [
                    'section_name' => 'علمي',
                    'grant_date'   => '2012-01-01',
                ],
                'ba' => [
                    'university_id'        => $baathUni->id,
                    'faculty'              => 'كلية الهندسة المدنية',
                    'department'           => 'قسم الهندسة المدنية العامة',
                    'specialization'       => null, // حقل اختياري لإتاحة اختبار تركه فارغاً
                    'rank'                 => 'جيد',
                    'grant_date'           => '2017-08-10',
                ],
                'ma' => [
                    'university_id'          => $tishreenUni->id,
                    'faculty'                => 'كلية الهندسة المدنية',
                    'department'             => 'قسم الهندسة الإنشائية',
                    'general_specialization' => 'الهندسة المدنية',
                    'exact_specialization'   => null, // حقل اختياري لإتاحة اختبار استخراج القسم كاختصاص تدريس
                    'supervisor_name'        => 'أ.د. مازن علي غانم',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2020-10-12',
                    'defense_date'           => '2023-09-18',
                    'grant_date'             => '2023-11-05',
                    'thesis_title'           => 'السلوك الزلزالي للإطارات البيتونية المسلحة المزودة بجدران قص مركبة',
                ],
                'include_optional_attachments' => false,
            ],
        ];

        $this->command?->info('📝 جاري إنشاء 3 مسودات ماجستير تطبيقي في حساب جامعة الجزيرة الخاصة مع كامل المرفقات...');

        foreach ($students as $index => $item) {
            $num = $index + 1;
            $pData = $item['profile'];
            $aData = $item['application'];

            // إنشاء ملف المرشح
            $profile = EquivalenceProfile::create([
                'full_name'      => $pData['full_name'],
                'father_name'    => $pData['father_name'],
                'mother_name'    => $pData['mother_name'],
                'national_id'    => $pData['national_id'],
                'dob'            => $pData['dob'],
                'job_title'      => $pData['job_title'],
                'nationality_id' => $syria->id,
                'phone'          => $pData['phone'],
                'mobile'         => $pData['mobile'],
                'email'          => $pData['email'],
                'address'        => $pData['address'],
                'gender'         => $pData['gender'],
                'is_syrian'      => 1,
            ]);

            // إنشاء مسودة طلب الماجستير التطبيقي
            $application = Application::create([
                'candidate_id'         => $profile->id,
                'parent_application_id'=> null,
                'application_no'       => $aData['application_no'],
                'request_type'         => 1, // ماجستير داخلي - تطبيقي
                'status'               => 1, // مسودة
                'work_university_id'   => $alJazeeraUni->id,
                'user_id'              => $uniUserId,
                'work_faculty'         => $aData['work_faculty'],
                'work_department'      => $aData['work_department'],
                'new_uni_request_no'   => $aData['new_uni_request_no'],
                'new_uni_request_date' => $aData['new_uni_request_date'],
                'is_first_time'        => 1,
                'study_system'         => 'فصلي',
                'has_previous_degree'  => 0,
            ]);

            // 1. الشهادة الثانوية
            $edHS = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $hsLevelId,
                'country_id'             => $syria->id,
                'section_name'           => $item['hs']['section_name'],
                'general_specialization' => $item['hs']['section_name'],
                'grant_date'             => $item['hs']['grant_date'],
            ]);

            // 2. الإجازة الجامعية الأولى
            $edBA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $baLevelId,
                'country_id'             => $syria->id,
                'university_id'          => $item['ba']['university_id'],
                'faculty'                => $item['ba']['faculty'],
                'department'             => $item['ba']['department'],
                'section_name'           => $item['ba']['specialization'],
                'general_specialization' => $item['ba']['faculty'],
                'exact_specialization'   => $item['ba']['specialization'],
                'rank'                   => $item['ba']['rank'],
                'grant_date'             => $item['ba']['grant_date'],
            ]);

            // 3. الماجستير السوري
            $edMA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $maLevelId,
                'country_id'             => $syria->id,
                'university_id'          => $item['ma']['university_id'],
                'faculty'                => $item['ma']['faculty'],
                'department'             => $item['ma']['department'],
                'section_name'           => $item['ma']['exact_specialization'],
                'general_specialization' => $item['ma']['general_specialization'],
                'exact_specialization'   => $item['ma']['exact_specialization'],
                'registration_date'      => $item['ma']['registration_date'],
                'defense_date'           => $item['ma']['defense_date'],
                'grant_date'             => $item['ma']['grant_date'],
                'rank'                   => $item['ma']['rank'],
                'supervisor_name'        => $item['ma']['supervisor_name'],
                'thesis_title'           => $item['ma']['thesis_title'],
            ]);

            // توليد المرفقات الـ 11 الإلزامية لمسار الماجستير التطبيقي (PDF أبيض مكتوب عليه بالعربية)
            $this->createWhitePagePdf($application->id, $edMA->id, 13, 'صورة عن الهوية الشخصية / جواز السفر', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edBA->id, 3, 'مصدقة الإجازة الجامعية الأولى', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 6, 'نسخة مصدقة عن شهادة الماجستير', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 7, 'صورة عن قرار مجلس الدراسات العليا بالمناقشة بالماجستير', $profile->full_name, $aData['application_no'], 'ma_defense');
            $this->createWhitePagePdf($application->id, $edMA->id, 7, 'صورة عن قرار مجلس الجامعة بمنح درجة الماجستير', $profile->full_name, $aData['application_no'], 'ma_grant');
            $this->createWhitePagePdf($application->id, $edMA->id, 7, 'صورة عن قرار مجلس الجامعة بالتسجيل بدرجة الماجستير', $profile->full_name, $aData['application_no'], 'ma_reg');
            $this->createWhitePagePdf($application->id, $edMA->id, 16, 'إيصال تسديد رسم تعادل الماجستير (100,000 ل.س)', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 14, 'السيرة الذاتية للمرشح', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 8, 'ملخص رسالة الماجستير باللغة العربية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 15, 'طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة)', $profile->full_name, $aData['application_no']);

            // المرفقات الاختيارية الـ 3 (تضاف للمسودتين الأولى والثانية وتُترك اختيارية في الثالثة)
            if (!empty($item['include_optional_attachments'])) {
                $this->createWhitePagePdf($application->id, $edMA->id, 22, 'إيصال المكتبة الوطنية لاستلام الرسالة / الأطروحة', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edMA->id, 18, 'شهادة إتقان اللغة الإنكليزية', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edMA->id, 19, 'شهادة مهارات الحاسوب (ICDL)', $profile->full_name, $aData['application_no']);
            }

            $this->command?->info("✅ {$num}. مسودة: {$profile->full_name} | رقم الطلب: {$aData['application_no']} | Draft ID: {$application->id}");
        }

        $this->command?->info('🎉 اكتملت عملية التهيئة: الأرشيف والبيانات السابقة مصفّرة تماماً، و3 مسودات ماجستير تطبيقي جاهزة في حساب جامعة الجزيرة الخاصة!');
    }

    /**
     * توليد ملف PDF بصفحة بيضاء نظيفة ومكتوب عليها اسم الوثيقة باللغة العربية المنسقة عبر ArPHP.
     */
    private function createWhitePagePdf(
        int $appId,
        int $educationId,
        int $attachmentTypeId,
        string $docTitle,
        string $candidateName,
        string $appNo,
        string $keySuffix = ''
    ): EducationAttachment {
        $dir = storage_path('app/public/attachments/' . $appId);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $suffix = $keySuffix ? '_' . $keySuffix : '';
        $fileName = 'att_' . $appNo . '_type' . $attachmentTypeId . $suffix . '_' . substr(md5(uniqid('', true)), 0, 6) . '.pdf';
        $relativePath = 'attachments/' . $appId . '/' . $fileName;
        $fullPath = $dir . '/' . $fileName;

        $fontRegular = str_replace('\\', '/', public_path('fonts/segoeui.ttf'));
        $fontBold = str_replace('\\', '/', public_path('fonts/segoeuib.ttf'));

        $html = '<!DOCTYPE html>
<html lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    @page {
        size: A4 portrait;
        margin: 25mm 20mm;
    }
    @font-face {
        font-family: "Segoe UI";
        font-weight: normal;
        src: url("' . $fontRegular . '") format("truetype");
    }
    @font-face {
        font-family: "Segoe UI";
        font-weight: bold;
        src: url("' . $fontBold . '") format("truetype");
    }
    * {
        font-family: "Segoe UI", "DejaVu Sans", sans-serif;
        box-sizing: border-box;
    }
    body {
        background-color: #ffffff;
        color: #111827;
        margin: 0;
        padding: 40px 25px;
        text-align: center;
    }
    .page-frame {
        border: 1px solid #d1d5db;
        padding: 55px 20px;
        margin: 40px auto;
        background-color: #ffffff;
    }
    .doc-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 18px;
    }
    .doc-title {
        font-size: 16.5px;
        font-weight: bold;
        color: #1a2a44;
        margin: 20px 0 35px 0;
        line-height: 1.6;
    }
    .divider {
        width: 180px;
        height: 2px;
        background-color: #c5a059;
        margin: 0 auto 30px auto;
    }
    .meta {
        font-size: 15px;
        color: #374151;
        margin: 10px 0;
        line-height: 1.6;
    }
    .footer-note {
        margin-top: 50px;
        font-size: 12px;
        color: #9ca3af;
    }
</style>
</head>
<body>
    <div class="page-frame">
        <div class="doc-label">وثيقة مرفقة للاختبار - نظام تعادل الشهادات</div>
        <div class="doc-title">' . htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="divider"></div>
        <div class="meta">اسم المرشح: ' . htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="meta">الجامعة الطالبة: جامعة الجزيرة الخاصة</div>
        <div class="meta">رقم المعاملة: ' . htmlspecialchars($appNo, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="footer-note">صفحة تجريبية بيضاء صالحة للمعاينة والدمج الإلكتروني</div>
    </div>
</body>
</html>';

        // تشكيل النصوص العربية عبر ArPHP لتظهر متصلة ومن اليمين إلى اليسار بشكل سليم داخل DomPDF
        try {
            $arabic = new \ArPHP\I18N\Arabic();
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {
            // Fallback silently if shaping fails
        }

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true)
            ->setWarnings(false);
        file_put_contents($fullPath, $pdf->output());

        return EducationAttachment::create([
            'education_id'       => $educationId,
            'attachment_type_id' => $attachmentTypeId,
            'file_path'          => $relativePath,
            'notes'              => $docTitle,
        ]);
    }
}

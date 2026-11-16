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

class FacultyPermissionDraftsSeeder extends Seeder
{
    /**
     * تفريغ جميع بيانات النظام والأرشيف بالكامل ثم إدراج 3 مسودات
     * لمعاملة السماح بالتدريس (أعضاء الهيئة التدريسية بالجامعات الحكومية)
     * داخل حساب جامعة الجزيرة الخاصة مع مرفقات PDF عربية بيضاء جاهزة للاختبار.
     */
    public function run(): void
    {
        // 1. تصفير جميع الجداول التشغيلية والأرشيف وملفات التخزين القديمة بالكامل
        $this->command?->info('🧹 جاري تفريغ جميع البيانات السابقة وتصفير الأرشيف...');
        $this->call(WipeSystemDataSeeder::class);

        // 2. تجهيز الثوابت المرجعية وحساب جامعة الجزيرة الخاصة
        $syria  = LookupCountry::firstOrCreate(['name' => 'سوريا']);
        $russia = LookupCountry::where('name', 'like', '%روسيا%')->first()
            ?? LookupCountry::create(['name' => 'روسيا الاتحادية']);
        $france = LookupCountry::firstOrCreate(['name' => 'فرنسا']);

        $damascusUni = LookupUniversity::firstOrCreate(['name' => 'جامعة دمشق'], ['country_id' => $syria->id]);
        $aleppoUni   = LookupUniversity::firstOrCreate(['name' => 'جامعة حلب'], ['country_id' => $syria->id]);
        $idlibUni    = LookupUniversity::firstOrCreate(['name' => 'جامعة إدلب'], ['country_id' => $syria->id]);
        $furatUni    = LookupUniversity::firstOrCreate(['name' => 'جامعة الفرات'], ['country_id' => $syria->id]);
        $sorbonneUni = LookupUniversity::firstOrCreate(['name' => 'جامعة السوربون بباريس'], ['country_id' => $france->id]);

        $alJazeeraUni = LookupUniversity::where('name', 'LIKE', '%الجزيرة%')->first()
            ?? LookupUniversity::find(8)
            ?? LookupUniversity::create(['name' => 'جامعة الجزيرة الخاصة', 'country_id' => $syria->id]);

        $alJazeeraUser = User::where('university_id', $alJazeeraUni->id)->first()
            ?? User::where('email', 'uni_aljazeera@uni.edu.sy')->first()
            ?? User::whereHas('role', fn ($q) => $q->where('name', 'university'))->first();

        $uniUserId = $alJazeeraUser?->id ?? 3;

        $maLevelId  = LookupEducationLevel::where('name', 'like', '%ماجستير%')->value('id') ?? 2;
        $phdLevelId = LookupEducationLevel::where('name', 'like', '%دكتوراه%')->value('id') ?? 3;

        // 3. تعريف بيانات المرشحين الثلاثة (مسودات السماح بالتدريس)
        $candidates = [
            // الحالة 1: عضو هيئة تدريسية في جامعة دمشق + دكتوراه سورية + ماجستير اختياري + مرفقات إضافية
            [
                'profile' => [
                    'full_name'      => 'مهند خالد الحسن',
                    'father_name'    => 'خالد',
                    'mother_name'    => 'نوال الديري',
                    'national_id'    => '01040088112',
                    'nationality_id' => $syria->id,
                    'dob'            => '1984-04-15',
                    'job_title'      => 'أستاذ مساعد',
                    'gender'         => 'ذكر',
                    'email'          => 'muhanad.hassan@aljazeera.edu.sy',
                    'mobile'         => '0933556677',
                    'phone'          => '0112233445',
                    'address'        => 'دمشق - المزة - شارع الجلاء - بناء رقم 12',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'FAC-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'هندسة البرمجيات والنظم الذكية',
                    'new_uni_request_no'   => '301/ج',
                    'new_uni_request_date' => '2026-11-12',
                ],
                'gov' => [
                    'university_id'    => $damascusUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية الهندسة المعلوماتية',
                    'department'       => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'rank'             => 'أستاذ مساعد',
                ],
                'phd' => [
                    'country_id'       => $syria->id,
                    'university_id'    => $damascusUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية الهندسة المعلوماتية',
                    'department'       => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'specialization'   => 'هندسة البرمجيات والنظم الذكية',
                    'grant_date'       => '2018-09-12',
                ],
                'ma' => [
                    'country_id'       => $syria->id,
                    'university_id'    => $damascusUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية الهندسة المعلوماتية',
                    'department'       => 'قسم هندسة البرمجيات',
                    'grant_date'       => '2014-07-20',
                ],
                'include_optional_attachments' => true,
            ],

            // الحالة 2: عضوة هيئة تدريسية في جامعة إدلب + دكتوراه من روسيا الاتحادية (جامعة أخرى)
            [
                'profile' => [
                    'full_name'      => 'لمى عبد الرحمن الصالح',
                    'father_name'    => 'عبد الرحمن',
                    'mother_name'    => 'سلمى الشوا',
                    'national_id'    => '07020066334',
                    'nationality_id' => $syria->id,
                    'dob'            => '1986-09-22',
                    'job_title'      => 'مدرس',
                    'gender'         => 'أنثى',
                    'email'          => 'lama.saleh@aljazeera.edu.sy',
                    'mobile'         => '0944667788',
                    'phone'          => '0234455667',
                    'address'        => 'إدلب - الحي الشمالي - شارع الجامعة - بناء رقم 7',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'FAC-2026-0002',
                    'work_faculty'         => 'كلية التربية',
                    'work_department'      => 'المناهج وطرائق تدريس الرياضيات',
                    'new_uni_request_no'   => '302/ج',
                    'new_uni_request_date' => '2026-11-14',
                ],
                'gov' => [
                    'university_id'    => $idlibUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية التربية',
                    'department'       => 'قسم المناهج وطرائق التدريس',
                    'rank'             => 'مدرس',
                ],
                'phd' => [
                    'country_id'       => $russia->id,
                    'university_id'    => null,
                    'university_other' => 'جامعة موسكو الحكومية (لومونوسوف)',
                    'faculty'          => 'كلية العلوم التربوية',
                    'department'       => 'قسم طرائق التدريس',
                    'specialization'   => 'طرائق تدريس الرياضيات',
                    'grant_date'       => '2019-06-24',
                ],
                'ma' => [
                    'country_id'       => $syria->id,
                    'university_id'    => $aleppoUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية التربية',
                    'department'       => 'قسم المناهج وطرائق التدريس',
                    'grant_date'       => '2015-08-10',
                ],
                'include_optional_attachments' => false,
            ],

            // الحالة 3: عضو هيئة تدريسية في جامعة الفرات + دكتوراه من فرنسا (بدون ماجستير اختياري)
            [
                'profile' => [
                    'full_name'      => 'طارق زياد العلي',
                    'father_name'    => 'زياد',
                    'mother_name'    => 'فاطمة المحمود',
                    'national_id'    => '09010099445',
                    'nationality_id' => $syria->id,
                    'dob'            => '1979-11-05',
                    'job_title'      => 'أستاذ',
                    'gender'         => 'ذكر',
                    'email'          => 'tariq.ali@aljazeera.edu.sy',
                    'mobile'         => '0955778899',
                    'phone'          => '0513322114',
                    'address'        => 'دير الزور - حي القصور - الشارع العام - بناء رقم 5',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'FAC-2026-0003',
                    'work_faculty'         => 'كلية الهندسة المدنية',
                    'work_department'      => 'الهندسة الإنشائية',
                    'new_uni_request_no'   => '303/ج',
                    'new_uni_request_date' => '2026-11-15',
                ],
                'gov' => [
                    'university_id'    => $furatUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية الهندسة المدنية',
                    'department'       => 'قسم الهندسة الإنشائية',
                    'rank'             => 'أستاذ',
                ],
                'phd' => [
                    'country_id'       => $france->id,
                    'university_id'    => $sorbonneUni->id,
                    'university_other' => null,
                    'faculty'          => 'كلية العلوم والهندسة',
                    'department'       => 'قسم ميكانيك الإنشاءات',
                    'specialization'   => 'الهندسة الإنشائية وتصميم المنشآت المقاومة للزلازل',
                    'grant_date'       => '2013-07-08',
                ],
                'ma' => null,
                'include_optional_attachments' => false,
            ],
        ];

        foreach ($candidates as $idx => $item) {
            $num = $idx + 1;
            $pData = $item['profile'];
            $aData = $item['application'];

            // أ) ملف المرشح
            $profile = EquivalenceProfile::create($pData);

            // ب) المعاملة بحالة مسودة (status = 1) في حساب جامعة الجزيرة الخاصة
            $application = Application::create([
                'candidate_id'         => $profile->id,
                'application_no'       => $aData['application_no'],
                'request_type'         => 'عضو هيئة تدريسية - سماح',
                'work_university_id'   => $alJazeeraUni->id,
                'work_faculty'         => $aData['work_faculty'],
                'work_department'      => $aData['work_department'],
                'new_uni_request_no'   => $aData['new_uni_request_no'],
                'new_uni_request_date' => $aData['new_uni_request_date'],
                'is_first_time'        => true,
                'study_system'         => 'سنوي / فصلي',
                'has_previous_degree'  => true,
                'status'               => 'مسودة',
                'user_id'              => $uniUserId,
            ]);

            // ج) بيانات التعيين في الجامعة الحكومية السورية
            $gov = $item['gov'];
            Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $phdLevelId,
                'country_id'             => $syria->id,
                'university_id'          => $gov['university_id'],
                'university_other'       => $gov['university_other'],
                'faculty'                => $gov['faculty'],
                'department'             => $gov['department'],
                'general_specialization' => $gov['faculty'],
                'exact_specialization'   => $gov['department'],
                'section_name'           => $gov['department'],
                'rank'                   => $gov['rank'],
                'thesis_title'           => 'عضو هيئة تدريسية في جامعة حكومية',
            ]);

            // د) بيانات شهادة الدكتوراه (المؤهل الأساسي التي ترتبط بها المرفقات)
            $phd = $item['phd'];
            $edPhd = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $phdLevelId,
                'country_id'             => $phd['country_id'],
                'university_id'          => $phd['university_id'],
                'university_other'       => $phd['university_other'],
                'faculty'                => $phd['faculty'],
                'department'             => $phd['department'],
                'general_specialization' => $phd['faculty'],
                'exact_specialization'   => $phd['specialization'],
                'section_name'           => $phd['specialization'],
                'grant_date'             => $phd['grant_date'],
                'thesis_title'           => 'شهادة الدكتوراه',
            ]);

            // هـ) بيانات شهادة الماجستير الاختيارية (إن وجدت)
            if (!empty($item['ma'])) {
                $ma = $item['ma'];
                Education::create([
                    'application_id'         => $application->id,
                    'education_level_id'     => $maLevelId,
                    'country_id'             => $ma['country_id'],
                    'university_id'          => $ma['university_id'],
                    'university_other'       => $ma['university_other'],
                    'faculty'                => $ma['faculty'],
                    'department'             => $ma['department'],
                    'general_specialization' => $ma['faculty'],
                    'exact_specialization'   => $ma['department'],
                    'section_name'           => $ma['department'],
                    'grant_date'             => $ma['grant_date'],
                    'thesis_title'           => 'شهادة الماجستير',
                ]);
            }

            // و) المرفقات الخمسة الإلزامية لمعاملة السماح بالتدريس (مربوطة بسجل الدكتوراه كما يعتمدها الـ Wizard)
            $this->createWhitePagePdf($application->id, $edPhd->id, 13, 'صورة عن الهوية الشخصية / إثبات الشخصية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhd->id, 7, 'طلب تقويم / كتاب ترشيح الجامعة الخاصة', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhd->id, 9, 'بيان وضع أو بطاقة ذاتية من الجامعة الحكومية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhd->id, 8, 'نسخة مصدقة أصولاً عن شهادة الدكتوراه', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhd->id, 10, 'إيصال تسديد رسم السماح بالتدريس (125,000 ل.س)', $profile->full_name, $aData['application_no']);

            // ز) مرفقات اختيارية
            if (!empty($item['include_optional_attachments'])) {
                $this->createWhitePagePdf($application->id, $edPhd->id, 23, 'مرفقات ووثائق داعمة أخرى (قرار التعيين / مباشرة العمل)', $profile->full_name, $aData['application_no']);
            }

            $govUniDisplay = $gov['university_id'] ? LookupUniversity::find($gov['university_id'])?->name : $gov['university_other'];
            $this->command?->info("✅ {$num}. مسودة سماح بالتدريس: {$profile->full_name} ({$govUniDisplay}) | رقم الطلب: {$aData['application_no']} | Draft ID: {$application->id}");
        }

        $this->command?->info('🎉 اكتملت عملية التهيئة: الأرشيف والبيانات السابقة مصفّرة تماماً، و3 مسودات سماح بالتدريس جاهزة في حساب جامعة الجزيرة الخاصة!');
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
        string $appNo
    ): EducationAttachment {
        $dir = storage_path('app/public/attachments/' . $appId);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = 'att_' . $appNo . '_type' . $attachmentTypeId . '_' . substr(md5(uniqid('', true)), 0, 6) . '.pdf';
        $relativePath = 'attachments/' . $appId . '/' . $fileName;
        $fullPath = $dir . '/' . $fileName;

        $fontRegular = str_replace('\\', '/', public_path('fonts/segoeui.ttf'));
        $fontBold    = str_replace('\\', '/', public_path('fonts/segoeuib.ttf'));

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
        <div class="doc-label">وثيقة مرفقة للاختبار - معاملة السماح بالتدريس</div>
        <div class="doc-title">' . htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="divider"></div>
        <div class="meta">اسم عضو الهيئة التدريسية: ' . htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="meta">الجامعة الطالبة: جامعة الجزيرة الخاصة</div>
        <div class="meta">رقم المعاملة: ' . htmlspecialchars($appNo, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="footer-note">صفحة تجريبية بيضاء صالحة للمعاينة والدمج الإلكتروني</div>
    </div>
</body>
</html>';

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
            // Ignore shaping errors
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

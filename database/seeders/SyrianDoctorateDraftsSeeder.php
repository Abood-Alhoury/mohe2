<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Application;
use App\Models\EquivalenceProfile;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\LookupAttachmentType;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\User;

class SyrianDoctorateDraftsSeeder extends Seeder
{
    /**
     * تصفير جميع البيانات والأرشيف ثم إدراج 3 مسودات دكتوراه سورية داخلية مكتملة
     * في حساب جامعة الجزيرة الخاصة مع مرفقات PDF عربية بيضاء كاملة لكل حالة.
     */
    public function run(): void
    {
        // 1. تصفير النظام والأرشيف وملفات التخزين بالكامل
        $this->command?->info('🧹 جاري تفريغ جميع البيانات السابقة وتصفير الأرشيف...');
        $this->call(WipeSystemDataSeeder::class);

        // 2. تجهيز الدول والجامعات السورية التسع وأنواع المرفقات
        $syria  = LookupCountry::firstOrCreate(['name' => 'سوريا']);
        $turkey = LookupCountry::firstOrCreate(['name' => 'تركيا']);
        $jordan = LookupCountry::firstOrCreate(['name' => 'الأردن']);
        $egypt  = LookupCountry::firstOrCreate(['name' => 'مصر']);

        $syrianPhdNames = [
            'جامعة دمشق',
            'جامعة حلب',
            'جامعة حمص',
            'جامعة اللاذقية',
            'جامعة طرطوس',
            'جامعة الفرات',
            'الجامعة الافتراضية السورية',
            'جامعة حماة',
            'جامعة إدلب',
        ];
        $phdUnis = [];
        foreach ($syrianPhdNames as $name) {
            $phdUnis[$name] = LookupUniversity::firstOrCreate(
                ['name' => $name],
                ['country_id' => $syria->id]
            );
        }

        $ensureAttType = function (int $preferredId, string $name): int {
            $existing = LookupAttachmentType::where('name', $name)->value('id');
            if ($existing) {
                return (int) $existing;
            }
            if (!LookupAttachmentType::where('id', $preferredId)->exists()) {
                DB::table('lookup_attachment_types')->insert([
                    'id'         => $preferredId,
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return $preferredId;
            }
            return (int) LookupAttachmentType::create(['name' => $name])->id;
        };

        $regDecTypeId   = $ensureAttType(26, 'قرار الجامعة بالتسجيل بدرجة الدكتوراه');
        $defDecTypeId   = $ensureAttType(27, 'قرار الجامعة بالمناقشة للدكتوراه');
        $grantDecTypeId = $ensureAttType(28, 'قرار الجامعة بالحصول على درجة الدكتوراه');

        $alJazeeraUni = LookupUniversity::where('name', 'LIKE', '%الجزيرة%')->first()
            ?? LookupUniversity::find(8)
            ?? LookupUniversity::create(['name' => 'جامعة الجزيرة الخاصة', 'country_id' => $syria->id]);

        $alJazeeraUser = User::where('university_id', $alJazeeraUni->id)->first()
            ?? User::where('email', 'uni_aljazeera@uni.edu.sy')->first()
            ?? User::whereHas('role', fn ($q) => $q->where('name', 'university'))->first();

        $uniUserId = $alJazeeraUser?->id ?? 3;

        $hsLevelId  = LookupEducationLevel::where('name', 'like', '%ثانوية%')->value('id') ?? 4;
        $baLevelId  = LookupEducationLevel::where('name', 'like', '%إجازة%')->value('id') ?? 1;
        $maLevelId  = LookupEducationLevel::where('name', 'like', '%ماجستير%')->value('id') ?? 2;
        $phdLevelId = LookupEducationLevel::where('name', 'like', '%دكتوراه%')->value('id') ?? 3;

        // 3. تعريف بيانات المرشحين الثلاثة (دكتوراه سورية داخلية في المسودات)
        $candidates = [
            // الحالة 1: جميع الشهادات من سوريا (دمشق) + بدون معادلة ماجستير + مرفقات اختيارية
            [
                'profile' => [
                    'full_name'      => 'كنان باسل الأطرش',
                    'father_name'    => 'باسل',
                    'mother_name'    => 'ندى الحموي',
                    'national_id'    => '01050033221',
                    'nationality_id' => $syria->id,
                    'dob'            => '1991-03-14',
                    'job_title'      => 'مدرس جامعي',
                    'gender'         => 'ذكر',
                    'email'          => 'kinan.atrash@aljazeera.edu.sy',
                    'mobile'         => '0933112233',
                    'phone'          => '0114455667',
                    'address'        => 'دمشق - المالكي - شارع عبد المنعم رياض - بناء 14',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-SY-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'new_uni_request_no'   => '401/ج',
                    'new_uni_request_date' => '2026-11-12',
                ],
                'hs' => [
                    'country_id' => $syria->id,
                    'type'       => 'علمي',
                    'grant_date' => '2009',
                ],
                'ba' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $phdUnis['جامعة دمشق']->id,
                    'university_other'       => 'جامعة دمشق',
                    'faculty'                => 'كلية الهندسة المعلوماتية',
                    'department'             => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'grant_date'             => '2014-08-20',
                    'rank'                   => 'ممتاز',
                ],
                'ma' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $phdUnis['جامعة دمشق']->id,
                    'university_other'       => null,
                    'department'             => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'exact_specialization'   => 'هندسة البرمجيات والنظم الذكية',
                    'grant_date'             => '2018-09-10',
                    'notes'                  => null,
                ],
                'phd' => [
                    'university_id'          => $phdUnis['جامعة دمشق']->id,
                    'faculty'                => 'كلية الهندسة المعلوماتية',
                    'department'             => 'قسم هندسة البرمجيات ونظم المعلومات',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'exact_specialization'   => 'هندسة البرمجيات والنظم الذكية',
                    'registration_date'      => '2019-10-01',
                    'defense_date'           => '2023-07-18',
                    'grant_date'             => '2023-09-12',
                    'rank'                   => 'امتياز',
                    'supervisor_name'        => 'أ.د. طلال منصور الحلبي',
                    'thesis_title'           => 'تصميم إطار ذكي لاختبار برمجيات الحوسبة السحابية الموزعة',
                ],
                'include_optional_attachments' => true,
            ],

            // الحالة 2: بكالوريا من تركيا + إجازة من الأردن + ماجستير من الأردن (مع معادلة سابقة) + دكتوراه من جامعة حلب (أنثى)
            [
                'profile' => [
                    'full_name'      => 'سارة محمد نبيل التركماني',
                    'father_name'    => 'محمد نبيل',
                    'mother_name'    => 'هند الجلبي',
                    'national_id'    => '02040088991',
                    'nationality_id' => $syria->id,
                    'dob'            => '1990-06-11',
                    'job_title'      => 'باحثة ومحاضرة جامعية',
                    'gender'         => 'أنثى',
                    'email'          => 'sara.turkmani@aljazeera.edu.sy',
                    'mobile'         => '0944887766',
                    'phone'          => '0213344558',
                    'address'        => 'حلب - الشهباء الجديدة - شارع الجامعة - بناء 6',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-SY-2026-0002',
                    'work_faculty'         => 'كلية العلوم',
                    'work_department'      => 'قسم الكيمياء',
                    'new_uni_request_no'   => '402/ج',
                    'new_uni_request_date' => '2026-11-14',
                ],
                'hs' => [
                    'country_id' => $turkey->id,
                    'type'       => 'علمي',
                    'grant_date' => '2008',
                ],
                'ba' => [
                    'country_id'             => $jordan->id,
                    'university_id'          => LookupUniversity::where('name', 'الجامعة الأردنية')->value('id'),
                    'university_other'       => 'الجامعة الأردنية',
                    'faculty'                => 'كلية العلوم',
                    'department'             => 'قسم الكيمياء',
                    'general_specialization' => 'العلوم الكيميائية',
                    'grant_date'             => '2012-06-25',
                    'rank'                   => 'جيد جداً',
                ],
                'ma' => [
                    'country_id'             => $jordan->id,
                    'university_id'          => LookupUniversity::where('name', 'جامعة اليرموك')->value('id'),
                    'university_other'       => 'جامعة اليرموك',
                    'department'             => 'قسم الكيمياء التطبيقية',
                    'general_specialization' => 'الكيمياء',
                    'exact_specialization'   => 'الكيمياء التحليلية والطيفية',
                    'grant_date'             => '2016-05-14',
                    'notes'                  => 'رقم قرار معادلة شهادة الماجستير: 412/م | تاريخ القرار: 2017-03-19',
                ],
                'phd' => [
                    'university_id'          => $phdUnis['جامعة حلب']->id,
                    'faculty'                => 'كلية العلوم',
                    'department'             => 'قسم الكيمياء',
                    'general_specialization' => 'الكيمياء',
                    'exact_specialization'   => 'الكيمياء التحليلية البيئية',
                    'registration_date'      => '2018-10-15',
                    'defense_date'           => '2022-11-22',
                    'grant_date'             => '2023-02-05',
                    'rank'                   => 'شرف',
                    'supervisor_name'        => 'أ.د. عبد الرزاق قاسم الدندشي',
                    'thesis_title'           => 'تطوير طرق طيفية حديثة لتقدير العناصر الأثرية في المياه السطحية',
                ],
                'include_optional_attachments' => false,
            ],

            // الحالة 3: إجازة من مصر (نص حر) + ماجستير من جامعة حمص (بدون معادلة) + دكتوراه من الجامعة الافتراضية السورية
            [
                'profile' => [
                    'full_name'      => 'حسام الدين عمار القاضي',
                    'father_name'    => 'عمار',
                    'mother_name'    => 'سميرة العبد الله',
                    'national_id'    => '06030077442',
                    'nationality_id' => $syria->id,
                    'dob'            => '1992-01-25',
                    'job_title'      => 'مهندس وباحث',
                    'gender'         => 'ذكر',
                    'email'          => 'husam.qadi@aljazeera.edu.sy',
                    'mobile'         => '0955223344',
                    'phone'          => '0314477889',
                    'address'        => 'حمص - الإنشاءات - شارع البرازيل - بناء 21',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-SY-2026-0003',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية والتقانة',
                    'work_department'      => 'قسم علوم الحاسوب والشبكات',
                    'new_uni_request_no'   => '403/ج',
                    'new_uni_request_date' => '2026-11-16',
                ],
                'hs' => [
                    'country_id' => $syria->id,
                    'type'       => 'علمي',
                    'grant_date' => '2010',
                ],
                'ba' => [
                    'country_id'             => $egypt->id,
                    'university_id'          => null,
                    'university_other'       => 'جامعة عين شمس',
                    'faculty'                => 'كلية الهندسة',
                    'department'             => 'قسم هندسة الإلكترونيات والاتصالات',
                    'general_specialization' => 'الهندسة الكهربائية والإلكترونية',
                    'grant_date'             => '2015-07-14',
                    'rank'                   => 'جيد جداً',
                ],
                'ma' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $phdUnis['جامعة حمص']->id,
                    'university_other'       => null,
                    'department'             => 'قسم هندسة الإلكترونيات والاتصالات',
                    'general_specialization' => 'هندسة الاتصالات والشبكات',
                    'exact_specialization'   => 'أمن الشبكات اللاسلكية',
                    'grant_date'             => '2019-04-28',
                    'notes'                  => null,
                ],
                'phd' => [
                    'university_id'          => $phdUnis['الجامعة الافتراضية السورية']->id,
                    'faculty'                => 'كلية الهندسة المعلوماتية والتقانة',
                    'department'             => 'قسم علوم الحاسوب والشبكات',
                    'general_specialization' => 'المعلوماتية والتقانة',
                    'exact_specialization'   => 'الأمن السيبراني والذكاء الصنعي',
                    'registration_date'      => '2020-03-01',
                    'defense_date'           => '2024-06-10',
                    'grant_date'             => '2024-08-20',
                    'rank'                   => 'امتياز',
                    'supervisor_name'        => 'أ.د. باسل الخطيب',
                    'thesis_title'           => 'تحليل وكشف الهجمات السيبرانية في شبكات إنترنت الأشياء باستخدام التعلم العميق',
                ],
                'include_optional_attachments' => false,
            ],
        ];

        foreach ($candidates as $idx => $item) {
            $num   = $idx + 1;
            $pData = $item['profile'];
            $aData = $item['application'];

            $profile = EquivalenceProfile::create($pData);

            $application = Application::create([
                'candidate_id'         => $profile->id,
                'application_no'       => $aData['application_no'],
                'request_type'         => 'دكتورة داخلي',
                'work_university_id'   => $alJazeeraUni->id,
                'work_faculty'         => $aData['work_faculty'],
                'work_department'      => $aData['work_department'],
                'new_uni_request_no'   => $aData['new_uni_request_no'],
                'new_uni_request_date' => $aData['new_uni_request_date'],
                'is_first_time'        => true,
                'study_system'         => 'فصلي',
                'has_previous_degree'  => false,
                'status'               => 'مسودة',
                'user_id'              => $uniUserId,
            ]);

            // أ) البكالوريا
            $hs = $item['hs'];
            $edHS = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $hsLevelId,
                'country_id'             => $hs['country_id'],
                'section_name'           => $hs['type'],
                'general_specialization' => $hs['type'],
                'grant_date'             => $hs['grant_date'],
                'notes'                  => null,
            ]);

            // ب) الإجازة الجامعية الأولى
            $ba = $item['ba'];
            $edBA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $baLevelId,
                'country_id'             => $ba['country_id'],
                'university_id'          => $ba['university_id'],
                'university_other'       => $ba['university_other'],
                'faculty'                => $ba['faculty'],
                'department'             => $ba['department'],
                'general_specialization' => $ba['general_specialization'],
                'exact_specialization'   => $ba['department'],
                'grant_date'             => $ba['grant_date'],
                'rank'                   => $ba['rank'],
                'notes'                  => null,
            ]);

            // ج) الماجستير
            $ma = $item['ma'];
            $edMA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $maLevelId,
                'country_id'             => $ma['country_id'],
                'university_id'          => $ma['university_id'],
                'university_other'       => $ma['university_other'],
                'department'             => $ma['department'],
                'section_name'           => $ma['exact_specialization'],
                'general_specialization' => $ma['general_specialization'],
                'exact_specialization'   => $ma['exact_specialization'],
                'grant_date'             => $ma['grant_date'],
                'notes'                  => $ma['notes'],
            ]);

            // د) الدكتوراه السورية
            $phd = $item['phd'];
            $edPhD = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $phdLevelId,
                'country_id'             => $syria->id,
                'university_id'          => $phd['university_id'],
                'faculty'                => $phd['faculty'],
                'department'             => $phd['department'],
                'section_name'           => $phd['exact_specialization'],
                'general_specialization' => $phd['general_specialization'],
                'exact_specialization'   => $phd['exact_specialization'],
                'registration_date'      => $phd['registration_date'],
                'defense_date'           => $phd['defense_date'],
                'grant_date'             => $phd['grant_date'],
                'rank'                   => $phd['rank'],
                'supervisor_name'        => $phd['supervisor_name'],
                'thesis_title'           => $phd['thesis_title'],
                'notes'                  => null,
            ]);

            // هـ) المرفقات المعتمدة للدكتوراه السورية
            $this->createWhitePagePdf($application->id, $edPhD->id, 13, '1. صورة عن الهوية الشخصية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edHS->id, 1, '2. صورة عن شهادة البكالوريا', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edBA->id, 3, '3. صورة عن الإجازة الجامعية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edMA->id, 6, '4. صورة عن شهادة الماجستير', $profile->full_name, $aData['application_no']);

            if (!empty($ma['notes'])) {
                $this->createWhitePagePdf($application->id, $edMA->id, 9, '5. صورة عن قرار معادلة الماجستير', $profile->full_name, $aData['application_no']);
            }

            $this->createWhitePagePdf($application->id, $edPhD->id, 10, '6. صورة عن شهادة الدكتوراه السورية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, $regDecTypeId, '7. قرار الجامعة بالتسجيل بدرجة الدكتوراه', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, $defDecTypeId, '8. قرار الجامعة بالمناقشة للدكتوراه', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, $grantDecTypeId, '9. قرار الجامعة بالحصول على الدرجة', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, 15, '10. طلب الجامعة للتعادل (كتاب طلب التقويم)', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, 14, '11. السيرة الذاتية للمرشح', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, 12, '14. ملخص عن أطروحة الدكتوراه باللغة العربية', $profile->full_name, $aData['application_no']);
            $this->createWhitePagePdf($application->id, $edPhD->id, 17, '15. إيصال دفع 125,000 ل.س (رسم تعادل الدكتوراه)', $profile->full_name, $aData['application_no']);

            if (!empty($item['include_optional_attachments'])) {
                $this->createWhitePagePdf($application->id, $edPhD->id, 19, '12. شهادة مهارات الحاسوب (ICDL)', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edPhD->id, 18, '13. شهادة اللغة الإنكليزية', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edPhD->id, 23, '16. مرفقات ووثائق أخرى داعمة', $profile->full_name, $aData['application_no']);
            }

            $phdUniName = LookupUniversity::find($phd['university_id'])?->name ?? 'جامعة سورية';
            $this->command?->info("✅ {$num}. مسودة دكتوراه سورية: {$profile->full_name} ({$phdUniName}) | رقم الطلب: {$aData['application_no']} | Draft ID: {$application->id}");
        }

        $this->command?->info('🎉 اكتملت عملية التهيئة: تم تصفير الأرشيف وإدراج 3 مسودات دكتوراه سورية داخلية كاملة في حساب جامعة الجزيرة الخاصة!');
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
        <div class="doc-label">وثيقة مرفقة للاختبار - معاملة تعادل دكتوراه سورية داخلية</div>
        <div class="doc-title">' . htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') . '</div>
        <div class="divider"></div>
        <div class="meta">اسم المرشح: ' . htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8') . '</div>
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

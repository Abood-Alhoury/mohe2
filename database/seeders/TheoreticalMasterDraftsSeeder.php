<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Application;
use App\Models\ApplicationExperience;
use App\Models\EquivalenceProfile;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\User;

class TheoreticalMasterDraftsSeeder extends Seeder
{
    /**
     * تفريغ بيانات النظام والأرشيف بالكامل ثم إدراج 3 مسودات ماجستير داخلي نظري مكتملة لجامعة الجزيرة الخاصة.
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

        // 3. تعريف بيانات الطلاب الثلاثة (ماجستير داخلي - نظري في المسودات مع خبرة >= 730 يوماً)
        $students = [
            [
                'profile' => [
                    'full_name'   => 'رامي سمير النجار',
                    'father_name' => 'سمير',
                    'mother_name' => 'هناء الكيلاني',
                    'national_id' => '01030055441',
                    'dob'         => '1992-05-12',
                    'job_title'   => 'محاضر جامعي',
                    'gender'      => 'ذكر',
                    'email'       => 'rami.najjar@aljazeera.edu.sy',
                    'mobile'      => '0933445566',
                    'phone'       => '0113344556',
                    'address'     => 'دمشق - أبو رمانة - جادة الجلاء - بناء رقم 9',
                ],
                'application' => [
                    'application_no'       => 'MA-SY-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم نظم المعلومات والشبكات',
                    'new_uni_request_no'   => '201/ج',
                    'new_uni_request_date' => '2026-11-10',
                ],
                'hs' => [
                    'section_name' => 'علمي',
                    'grant_date'   => '2010-01-01',
                ],
                'ba' => [
                    'university_id'  => $damascusUni->id,
                    'faculty'        => 'كلية الهندسة المعلوماتية',
                    'department'     => 'قسم النظم والشبكات الحاسوبية',
                    'specialization' => 'الشبكات الحاسوبية',
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2015-08-18',
                ],
                'ma' => [
                    'university_id'          => $damascusUni->id,
                    'faculty'                => 'كلية الهندسة المعلوماتية',
                    'department'             => 'قسم نظم المعلومات والشبكات',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'exact_specialization'   => 'أمن المعلومات والشبكات',
                    'supervisor_name'        => 'أ.د. باسم محمد العبد الله',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2018-10-01',
                    'defense_date'           => '2021-06-15',
                    'grant_date'             => '2021-07-28',
                    'thesis_title'           => 'تصميم بروتوكولات كشف التسلل الذكية في شبكات الاستشعار اللاسلكية',
                ],
                'experiences' => [
                    [
                        'institution_name' => 'جامعة الجزيرة الخاصة - كلية الهندسة المعلوماتية',
                        'role_or_subject'  => 'مدرس متفرغ - شبكات الحاسوب وأمن المعلومات',
                        'start_date'       => '2023-10-01',
                        'end_date'         => '2026-04-01',
                        'notes'            => 'كتاب تكليف رقم 215 تاريخ 2023-10-01',
                    ],
                ],
                'include_optional_attachments' => true,
            ],
            [
                'profile' => [
                    'full_name'   => 'ريم طارق الحمصي',
                    'father_name' => 'طارق',
                    'mother_name' => 'سعاد البيطار',
                    'national_id' => '03020077662',
                    'dob'         => '1993-08-19',
                    'job_title'   => 'مدرسة جامعية',
                    'gender'      => 'أنثى',
                    'email'       => 'reem.homsi@aljazeera.edu.sy',
                    'mobile'      => '0944778899',
                    'phone'       => '0312255887',
                    'address'     => 'حمص - الوعر - شارع الغرب - بناء رقم 18',
                ],
                'application' => [
                    'application_no'       => 'MA-SY-2026-0002',
                    'work_faculty'         => 'كلية الاقتصاد وإدارة الأعمال',
                    'work_department'      => 'قسم إدارة الأعمال',
                    'new_uni_request_no'   => '202/ج',
                    'new_uni_request_date' => '2026-11-12',
                ],
                'hs' => [
                    'section_name' => 'أدبي',
                    'grant_date'   => '2011-01-01',
                ],
                'ba' => [
                    'university_id'  => $baathUni->id,
                    'faculty'        => 'كلية الاقتصاد',
                    'department'     => 'قسم إدارة الأعمال',
                    'specialization' => 'إدارة الموارد البشرية',
                    'rank'           => 'ممتاز',
                    'grant_date'     => '2016-09-04',
                ],
                'ma' => [
                    'university_id'          => $aleppoUni->id,
                    'faculty'                => 'كلية الاقتصاد',
                    'department'             => 'قسم إدارة الأعمال',
                    'general_specialization' => 'إدارة الأعمال',
                    'exact_specialization'   => 'الإدارة الاستراتيجية والقيادة المؤسسية',
                    'supervisor_name'        => 'أ.د. خلدون فؤاد الشامي',
                    'rank'                   => 'جيد جداً',
                    'registration_date'      => '2019-10-15',
                    'defense_date'           => '2022-07-10',
                    'grant_date'             => '2022-08-22',
                    'thesis_title'           => 'أثر التحول الرقمي والإدارة الاستراتيجية في تحسين الأداء المؤسسي للمنشآت الاقتصادية',
                ],
                'experiences' => [
                    [
                        'institution_name' => 'جامعة البعث - كلية الاقتصاد',
                        'role_or_subject'  => 'محاضرة مكلفة - مبادئ الإدارة والتسويق',
                        'start_date'       => '2023-03-01',
                        'end_date'         => '2024-04-04',
                        'notes'            => 'الفترة الأولى في جامعة البعث',
                    ],
                    [
                        'institution_name' => 'جامعة الجزيرة الخاصة - كلية إدارة الأعمال',
                        'role_or_subject'  => 'تدريس مقررات الإدارة الاستراتيجية وإدارة الموارد البشرية',
                        'start_date'       => '2024-09-01',
                        'end_date'         => '2025-12-05',
                        'notes'            => 'الفترة الثانية في جامعة الجزيرة الخاصة',
                    ],
                ],
                'include_optional_attachments' => true,
            ],
            [
                'profile' => [
                    'full_name'   => 'بشار نزار الصالح',
                    'father_name' => 'نزار',
                    'mother_name' => 'عائدة ديب',
                    'national_id' => '04060099115',
                    'dob'         => '1991-02-28',
                    'job_title'   => 'مهندس مدني ومحاضر',
                    'gender'      => 'ذكر',
                    'email'       => 'bashar.saleh@aljazeera.edu.sy',
                    'mobile'      => '0955119933',
                    'phone'       => '0412299441',
                    'address'     => 'اللاذقية - الزراعة - قرب دوار الجامعة - بناء رقم 5',
                ],
                'application' => [
                    'application_no'       => 'MA-SY-2026-0003',
                    'work_faculty'         => 'كلية الهندسة المدنية',
                    'work_department'      => 'قسم الهندسة المائية والري',
                    'new_uni_request_no'   => '203/ج',
                    'new_uni_request_date' => '2026-11-15',
                ],
                'hs' => [
                    'section_name' => 'علمي',
                    'grant_date'   => '2009-01-01',
                ],
                'ba' => [
                    'university_id'  => $tishreenUni->id,
                    'faculty'        => 'كلية الهندسة المدنية',
                    'department'     => 'قسم الهندسة المدنية',
                    'specialization' => null, // حقل اختياري لإتاحة اختبار تركه فارغاً
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2014-08-25',
                ],
                'ma' => [
                    'university_id'          => $tishreenUni->id,
                    'faculty'                => 'كلية الهندسة المدنية',
                    'department'             => 'قسم الهندسة المائية',
                    'general_specialization' => 'الهندسة المدنية',
                    'exact_specialization'   => null, // حقل اختياري لإتاحة اختبار استخراج القسم في المادة 1
                    'supervisor_name'        => 'أ.د. تمام غسان سليمان',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2018-11-01',
                    'defense_date'           => '2021-10-12',
                    'grant_date'             => '2021-12-01',
                    'thesis_title'           => 'النمذجة الهيدروليكية لشبكات الري المضغوطة وترشيد الاستهلاك المائي',
                ],
                'experiences' => [
                    [
                        'institution_name' => 'جامعة تشرين - كلية الهندسة المدنية',
                        'role_or_subject'  => 'مدرس - الهيدروليك والمنشآت المائية',
                        'start_date'       => '2022-09-01',
                        'end_date'         => '2025-09-01',
                        'notes'            => 'خبرة تدريسية متواصلة لمدة 3 سنوات',
                    ],
                ],
                'include_optional_attachments' => false,
            ],
        ];

        $this->command?->info('📝 جاري إنشاء 3 مسودات ماجستير داخلي نظري في حساب جامعة الجزيرة الخاصة مع كامل البيانات والخبرات والمرفقات...');

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

            // إنشاء مسودة طلب الماجستير الداخلي النظري
            $application = Application::create([
                'candidate_id'          => $profile->id,
                'parent_application_id' => null,
                'application_no'        => $aData['application_no'],
                'request_type'          => 2, // ماجستير داخلي - نظري
                'status'                => 1, // مسودة
                'work_university_id'    => $alJazeeraUni->id,
                'user_id'               => $uniUserId,
                'work_faculty'          => $aData['work_faculty'],
                'work_department'       => $aData['work_department'],
                'new_uni_request_no'    => $aData['new_uni_request_no'],
                'new_uni_request_date'  => $aData['new_uni_request_date'],
                'is_first_time'         => 1,
                'study_system'          => 'فصلي',
                'has_previous_degree'   => 1,
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

            // تجهيز ملخص فترات الخبرة لحقول شهادة الماجستير
            $expStarts = array_column($item['experiences'], 'start_date');
            $expEnds   = array_column($item['experiences'], 'end_date');
            $expInsts  = array_column($item['experiences'], 'institution_name');

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
                'experience_from_year'   => min($expStarts),
                'experience_to_year'     => max($expEnds),
                'notes'                  => 'فترات خبرة تدريسية: ' . implode('، ', $expInsts),
            ]);

            // 4. إدراج فترات الخبرة التدريسية التراكمية (>= 730 يوماً) في application_experiences
            $totalExpDays = 0;
            foreach ($item['experiences'] as $exp) {
                $sDate = Carbon::parse($exp['start_date']);
                $eDate = Carbon::parse($exp['end_date']);
                $days = max(0, (int) $sDate->diffInDays($eDate));
                $totalExpDays += $days;

                ApplicationExperience::create([
                    'application_id'   => $application->id,
                    'education_id'     => $edMA->id,
                    'institution_name' => $exp['institution_name'],
                    'role_or_subject'  => $exp['role_or_subject'],
                    'start_date'       => $exp['start_date'],
                    'end_date'         => $exp['end_date'],
                    'duration_days'    => $days,
                    'notes'            => $exp['notes'],
                ]);
            }

            // 5. توليد المرفقات الـ 14 الإلزامية لمسار الماجستير النظري (11 مشتركة + 3 مرفقات خبرة تدريسية)
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

            // مرفقات الخبرة التدريسية الإلزامية الـ 3 للمسار النظري
            $this->createWhitePagePdf($application->id, $edMA->id, 21, 'صور العقود التدريسية المصدقة لمدة لا تقل عن سنتين', $profile->full_name, $aData['application_no'], 'contracts');
            $this->createWhitePagePdf($application->id, $edMA->id, 21, 'كشوفات الرواتب أو التحويلات البنكية المصدقة', $profile->full_name, $aData['application_no'], 'salary_slips');
            $this->createWhitePagePdf($application->id, $edMA->id, 20, 'وثائق وشهادات الخبرة التدريسية', $profile->full_name, $aData['application_no']);

            // 6. المرفقات الاختيارية الـ 3 (تضاف للمسودتين الأولى والثانية وتُترك اختيارية في الثالثة)
            if (!empty($item['include_optional_attachments'])) {
                $this->createWhitePagePdf($application->id, $edMA->id, 22, 'إيصال المكتبة الوطنية لاستلام الرسالة / الأطروحة', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edMA->id, 18, 'شهادة إتقان اللغة الإنكليزية', $profile->full_name, $aData['application_no']);
                $this->createWhitePagePdf($application->id, $edMA->id, 19, 'شهادة مهارات الحاسوب (ICDL)', $profile->full_name, $aData['application_no']);
            }

            $this->command?->info("✅ {$num}. مسودة ماجستير نظري: {$profile->full_name} | رقم الطلب: {$aData['application_no']} | Draft ID: {$application->id} | إجمالي الخبرة: {$totalExpDays} يوماً");
        }

        $this->command?->info('🎉 اكتملت عملية التهيئة: الأرشيف والبيانات السابقة مصفّرة تماماً، و3 مسودات ماجستير داخلي نظري جاهزة في حساب جامعة الجزيرة الخاصة!');
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquivalenceProfile;
use App\Models\Application;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\EducationResidence;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ForeignDoctorateDraftsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تصفير النظام وحذف كافة الطلبات والمرشحين والمرفقات والقرارات السابقة
        $this->call(WipeSystemDataSeeder::class);

        $syria  = LookupCountry::where('name', 'سوريا')->firstOrFail();
        $turkey = LookupCountry::where('name', 'تركيا')->first() ?? LookupCountry::firstOrCreate(['name' => 'تركيا']);
        $jordan = LookupCountry::where('name', 'الأردن')->first() ?? LookupCountry::firstOrCreate(['name' => 'الأردن']);
        $egypt  = LookupCountry::where('name', 'مصر')->first() ?? LookupCountry::firstOrCreate(['name' => 'مصر']);
        $lebanon= LookupCountry::where('name', 'لبنان')->first() ?? LookupCountry::firstOrCreate(['name' => 'لبنان']);
        $france = LookupCountry::where('name', 'فرنسا')->first() ?? LookupCountry::firstOrCreate(['name' => 'فرنسا']);

        $jazeeraUni = LookupUniversity::where('name', 'like', '%الجزيرة%')->firstOrFail();
        $jazeeraUser = User::where('university_id', $jazeeraUni->id)->first();

        $damascusUni = LookupUniversity::firstOrCreate(['name' => 'جامعة دمشق'], ['country_id' => $syria->id]);
        $aleppoUni   = LookupUniversity::firstOrCreate(['name' => 'جامعة حلب'], ['country_id' => $syria->id]);
        $latakiaUni  = LookupUniversity::where('name', 'like', '%اللاذقية%')->orWhere('name', 'like', '%تشرين%')->first()
            ?? LookupUniversity::firstOrCreate(['name' => 'جامعة اللاذقية'], ['country_id' => $syria->id]);

        $cairoUni    = LookupUniversity::firstOrCreate(['name' => 'جامعة القاهرة'], ['country_id' => $egypt->id]);
        $jordanUni   = LookupUniversity::firstOrCreate(['name' => 'الجامعة الأردنية'], ['country_id' => $jordan->id]);
        $yarmoukUni  = LookupUniversity::firstOrCreate(['name' => 'جامعة اليرموك'], ['country_id' => $jordan->id]);
        $beirutUni   = LookupUniversity::firstOrCreate(['name' => 'جامعة بيروت العربية'], ['country_id' => $lebanon->id]);
        $sorbonneUni = LookupUniversity::firstOrCreate(['name' => 'جامعة السوربون (Paris-Sorbonne)'], ['country_id' => $france->id]);

        $samples = [
            // =========================================================================
            // المسودة 1: ثانوية غير سورية (تركيا + قرار معادلة) + إجازة وماجستير سوريا (دمشق، بدون معادلة ماجستير)
            // + دكتوراه من مصر (جامعة القاهرة - تخصص هندسي غير معفى من الإقامة) + 3 حركات إقامة + كامل المرفقات
            // =========================================================================
            [
                'profile' => [
                    'full_name'      => 'طارق خالد المنصور',
                    'father_name'    => 'خالد',
                    'mother_name'    => 'سلمى الأحمد',
                    'national_id'    => '01030055112',
                    'nationality_id' => $syria->id,
                    'dob'            => '1989-04-15',
                    'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                    'gender'         => 'ذكر',
                    'email'          => 'tarek.mansour@aljazeera.edu.sy',
                    'mobile'         => '0933112233',
                    'phone'          => '0115544332',
                    'address'        => 'دمشق - المزة فيلات شرقية - شارع الجلاء - بناء 14',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-EXT-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم الهندسة الطبية الحيوية',
                    'new_uni_request_no'   => '701',
                    'new_uni_request_date' => '2026-11-10',
                    'study_system'         => 'أطروحة بحثية',
                    'is_residence_exempt'  => false,
                ],
                'hs' => [
                    'country_id' => $turkey->id,
                    'type'       => 'علمي',
                    'grant_date' => '2007',
                    'notes'      => 'رقم قرار معادلة الثانوية: 184/ث | تاريخ القرار: 2007-09-10',
                ],
                'ba' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $damascusUni->id,
                    'university_other'       => null,
                    'faculty'                => 'كلية الهندسة الميكانيكية والكهربائية',
                    'department'             => 'قسم الهندسة الطبية',
                    'general_specialization' => 'الهندسة الطبية',
                    'grant_date'             => '2012-08-20',
                    'rank'                   => 'ممتاز',
                ],
                'ma' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $damascusUni->id,
                    'university_other'       => null,
                    'faculty'                => 'كلية الهندسة الميكانيكية والكهربائية',
                    'department'             => 'قسم الهندسة الطبية',
                    'general_specialization' => 'الهندسة الطبية',
                    'exact_specialization'   => 'معالجة الإشارات الطبية الحيوية',
                    'grant_date'             => '2016-09-12',
                    'rank'                   => 'جيد جداً',
                    'notes'                  => null,
                ],
                'phd' => [
                    'country_id'             => $egypt->id,
                    'university_id'          => $cairoUni->id,
                    'university_other'       => 'جامعة القاهرة',
                    'faculty'                => 'كلية الهندسة',
                    'department'             => 'قسم الهندسة الحيوية الطبية والمنظومات',
                    'general_specialization' => 'الهندسة الطبية',
                    'exact_specialization'   => 'تصوير طبي وتشخيص ذكي',
                    'registration_date'      => '2018-10-01',
                    'defense_date'           => '2023-06-15',
                    'grant_date'             => '2023-07-20',
                    'rank'                   => 'شرف / امتياز',
                    'supervisor_name'        => 'أ.د. هشام عبد الفتاح الشاذلي',
                    'thesis_title'           => 'تطوير خوارزميات تعلم عميق للكشف المبكر عن الأورام عبر التصوير بالرنين المغناطيسي',
                    'study_language'         => 'الإنكليزية',
                ],
                'residences' => [
                    ['entry_date' => '2018-09-25', 'entry_airport' => 'مطار القاهرة الدولي', 'exit_date' => '2019-12-20', 'exit_airport' => 'مطار القاهرة الدولي', 'page_number' => '12'],
                    ['entry_date' => '2020-01-15', 'entry_airport' => 'مطار القاهرة الدولي', 'exit_date' => '2021-10-10', 'exit_airport' => 'مطار القاهرة الدولي', 'page_number' => '16'],
                    ['entry_date' => '2022-02-01', 'entry_airport' => 'مطار القاهرة الدولي', 'exit_date' => '2023-07-25', 'exit_airport' => 'مطار القاهرة الدولي', 'page_number' => '21'],
                ],
                'include_hs_decision' => true,
                'include_ma_decision' => false,
                'include_optional'    => true,
            ],

            // =========================================================================
            // المسودة 2: ثانوية سورية + إجازة غير سورية (الأردن - بدون قرار معادلة كما هو معتمد)
            // + ماجستير غير سوري (اليرموك - مع معادلة ماجستير مفعلة + مرفق قرار المعادلة)
            // + دكتوراه من لبنان (جامعة بيروت العربية - غير معفى من الإقامة) + حركتا إقامة
            // =========================================================================
            [
                'profile' => [
                    'full_name'      => 'ريم عبد الله السعيدي',
                    'father_name'    => 'عبد الله',
                    'mother_name'    => 'وفاء النابلسي',
                    'national_id'    => '02040088912',
                    'nationality_id' => $syria->id,
                    'dob'            => '1991-06-18',
                    'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                    'gender'         => 'أنثى',
                    'email'          => 'reem.saidi@aljazeera.edu.sy',
                    'mobile'         => '0944887766',
                    'phone'          => '0213344558',
                    'address'        => 'حلب - الشهباء الجديدة - شارع الجامعة - بناء 6',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-EXT-2026-0002',
                    'work_faculty'         => 'كلية الصيدلة',
                    'work_department'      => 'قسم الكيمياء الصيدلانية والرقابة الدوائية',
                    'new_uni_request_no'   => '702',
                    'new_uni_request_date' => '2026-11-12',
                    'study_system'         => 'مقررات وأطروحة',
                    'is_residence_exempt'  => false,
                ],
                'hs' => [
                    'country_id' => $syria->id,
                    'type'       => 'علمي',
                    'grant_date' => '2009',
                    'notes'      => null,
                ],
                'ba' => [
                    'country_id'             => $jordan->id,
                    'university_id'          => $jordanUni->id,
                    'university_other'       => 'الجامعة الأردنية',
                    'faculty'                => 'كلية الصيدلة',
                    'department'             => 'قسم العلوم الصيدلانية',
                    'general_specialization' => 'الصيدلة',
                    'grant_date'             => '2014-06-25',
                    'rank'                   => 'جيد جداً',
                ],
                'ma' => [
                    'country_id'             => $jordan->id,
                    'university_id'          => $yarmoukUni->id,
                    'university_other'       => 'جامعة اليرموك',
                    'faculty'                => 'كلية العلوم',
                    'department'             => 'قسم الكيمياء التطبيقية',
                    'general_specialization' => 'الكيمياء الصيدلانية',
                    'exact_specialization'   => 'التحليل الطيفي الدوائي',
                    'grant_date'             => '2017-05-14',
                    'rank'                   => 'ممتاز',
                    'notes'                  => 'رقم قرار معادلة الماجستير: 412/م | تاريخ القرار: 2018-03-19',
                ],
                'phd' => [
                    'country_id'             => $lebanon->id,
                    'university_id'          => $beirutUni->id,
                    'university_other'       => 'جامعة بيروت العربية',
                    'faculty'                => 'كلية الصيدلة',
                    'department'             => 'قسم الكيمياء الصيدلانية',
                    'general_specialization' => 'الصيدلة',
                    'exact_specialization'   => 'تصميم الأدوية الحاسوبي',
                    'registration_date'      => '2019-10-15',
                    'defense_date'           => '2023-11-22',
                    'grant_date'             => '2024-02-05',
                    'rank'                   => 'ممتاز',
                    'supervisor_name'        => 'أ.د. عبد الرزاق قاسم الدندشي',
                    'thesis_title'           => 'النمذجة الجزيئية وتقييم الفعالية الدوائية لمشتقات الكينولين الحديثة',
                    'study_language'         => 'الإنكليزية',
                ],
                'residences' => [
                    ['entry_date' => '2019-10-05', 'entry_airport' => 'معبر المصنع الحدودي', 'exit_date' => '2021-07-15', 'exit_airport' => 'معبر المصنع الحدودي', 'page_number' => '8'],
                    ['entry_date' => '2021-09-10', 'entry_airport' => 'مطار رفيق الحريري الدولي', 'exit_date' => '2024-02-10', 'exit_airport' => 'معبر المصنع الحدودي', 'page_number' => '15'],
                ],
                'include_hs_decision' => false,
                'include_ma_decision' => true,
                'include_optional'    => false,
            ],

            // =========================================================================
            // المسودة 3: ثانوية + إجازة (حلب) + ماجستير (اللاذقية، بدون معادلة)
            // + دكتوراه من فرنسا (السوربون - تخصص نظري معفى من شرط الإقامة is_residence_exempt = true)
            // =========================================================================
            [
                'profile' => [
                    'full_name'      => 'سامر مصطفى الحريري',
                    'father_name'    => 'مصطفى',
                    'mother_name'    => 'سميرة العبد الله',
                    'national_id'    => '06030077449',
                    'nationality_id' => $syria->id,
                    'dob'            => '1987-09-22',
                    'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                    'gender'         => 'ذكر',
                    'email'          => 'samer.hariri@aljazeera.edu.sy',
                    'mobile'         => '0955119988',
                    'phone'          => '0112233447',
                    'address'        => 'اللاذقية - المشروع العاشر - شارع الأندلس - بناء 9',
                    'is_syrian'      => true,
                ],
                'application' => [
                    'application_no'       => 'PHD-EXT-2026-0003',
                    'work_faculty'         => 'كلية الآداب والعلوم الإنسانية',
                    'work_department'      => 'قسم اللغة الفرنسية وآدابها',
                    'new_uni_request_no'   => '703',
                    'new_uni_request_date' => '2026-11-15',
                    'study_system'         => 'أطروحة بحثية',
                    'is_residence_exempt'  => true,
                ],
                'hs' => [
                    'country_id' => $syria->id,
                    'type'       => 'أدبي',
                    'grant_date' => '2005',
                    'notes'      => null,
                ],
                'ba' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $aleppoUni->id,
                    'university_other'       => null,
                    'faculty'                => 'كلية الآداب والعلوم الإنسانية',
                    'department'             => 'قسم اللغة الفرنسية',
                    'general_specialization' => 'الأدب الفرنسي',
                    'grant_date'             => '2009-07-18',
                    'rank'                   => 'جيد جداً',
                ],
                'ma' => [
                    'country_id'             => $syria->id,
                    'university_id'          => $latakiaUni->id,
                    'university_other'       => null,
                    'faculty'                => 'كلية الآداب والعلوم الإنسانية',
                    'department'             => 'قسم الأدب المقارن',
                    'general_specialization' => 'اللسانيات والأدب المقارن',
                    'exact_specialization'   => 'النقد الأدبي الحديث',
                    'grant_date'             => '2014-10-20',
                    'rank'                   => 'ممتاز',
                    'notes'                  => null,
                ],
                'phd' => [
                    'country_id'             => $france->id,
                    'university_id'          => $sorbonneUni->id,
                    'university_other'       => 'جامعة السوربون (Paris-Sorbonne)',
                    'faculty'                => 'كلية الآداب واللغات',
                    'department'             => 'قسم الأدب المقارن',
                    'general_specialization' => 'الأدب الفرنسي والمقارن',
                    'exact_specialization'   => 'دراسات سردية ومعرفية',
                    'registration_date'      => '2018-11-01',
                    'defense_date'           => '2023-12-08',
                    'grant_date'             => '2024-01-18',
                    'rank'                   => 'شرف / امتياز',
                    'supervisor_name'        => 'أ.د. جان لوك مارتان',
                    'thesis_title'           => 'تحولات الخطاب السردي في الرواية المتوسطية المعاصرة — دراسة مقارنة',
                    'study_language'         => 'الفرنسية',
                ],
                'residences'          => [],
                'include_hs_decision' => false,
                'include_ma_decision' => false,
                'include_optional'    => false,
            ],
        ];

        foreach ($samples as $sample) {
            $profile = EquivalenceProfile::create($sample['profile']);

            $app = Application::create([
                'candidate_id'         => $profile->id,
                'application_no'       => $sample['application']['application_no'],
                'request_type'         => 'دكتورة خارجية',
                'work_university_id'   => $jazeeraUni->id,
                'work_faculty'         => $sample['application']['work_faculty'],
                'work_department'      => $sample['application']['work_department'],
                'new_uni_request_no'   => $sample['application']['new_uni_request_no'],
                'new_uni_request_date' => $sample['application']['new_uni_request_date'],
                'study_system'         => $sample['application']['study_system'],
                'is_residence_exempt'  => $sample['application']['is_residence_exempt'],
                'status'               => 'مسودة',
                'user_id'              => $jazeeraUser?->id,
            ]);

            $appNo = $app->application_no;
            $candName = $profile->full_name;

            // 1. الثانوية (Level 4)
            $hsEd = Education::create([
                'application_id'         => $app->id,
                'education_level_id'     => 4,
                'country_id'             => $sample['hs']['country_id'],
                'section_name'           => $sample['hs']['type'],
                'general_specialization' => $sample['hs']['type'],
                'grant_date'             => $sample['hs']['grant_date'] . '-06-30',
                'notes'                  => $sample['hs']['notes'],
            ]);

            // 2. الإجازة الجامعية (Level 1 - بدون قرار معادلة نهائياً)
            $baEd = Education::create([
                'application_id'         => $app->id,
                'education_level_id'     => 1,
                'country_id'             => $sample['ba']['country_id'],
                'university_id'          => $sample['ba']['university_id'],
                'university_other'       => $sample['ba']['university_other'],
                'faculty'                => $sample['ba']['faculty'],
                'department'             => $sample['ba']['department'],
                'general_specialization' => $sample['ba']['general_specialization'],
                'grant_date'             => $sample['ba']['grant_date'],
                'rank'                   => $sample['ba']['rank'],
                'notes'                  => null,
            ]);

            // 3. الماجستير (Level 2 - تاريخ الحصول على الدرجة فقط + قرار معادلة اختياري)
            $maEd = Education::create([
                'application_id'         => $app->id,
                'education_level_id'     => 2,
                'country_id'             => $sample['ma']['country_id'],
                'university_id'          => $sample['ma']['university_id'],
                'university_other'       => $sample['ma']['university_other'],
                'faculty'                => $sample['ma']['faculty'],
                'department'             => $sample['ma']['department'],
                'general_specialization' => $sample['ma']['general_specialization'],
                'exact_specialization'   => $sample['ma']['exact_specialization'],
                'registration_date'      => null,
                'defense_date'           => null,
                'grant_date'             => $sample['ma']['grant_date'],
                'rank'                   => $sample['ma']['rank'],
                'notes'                  => $sample['ma']['notes'],
            ]);

            // 4. الدكتوراه الخارجية (Level 3)
            $phdEd = Education::create([
                'application_id'         => $app->id,
                'education_level_id'     => 3,
                'country_id'             => $sample['phd']['country_id'],
                'university_id'          => $sample['phd']['university_id'],
                'university_other'       => $sample['phd']['university_other'],
                'faculty'                => $sample['phd']['faculty'],
                'department'             => $sample['phd']['department'],
                'general_specialization' => $sample['phd']['general_specialization'],
                'exact_specialization'   => $sample['phd']['exact_specialization'],
                'registration_date'      => $sample['phd']['registration_date'],
                'defense_date'           => $sample['phd']['defense_date'],
                'grant_date'             => $sample['phd']['grant_date'],
                'rank'                   => $sample['phd']['rank'],
                'supervisor'             => $sample['phd']['supervisor_name'],
                'supervisor_name'        => $sample['phd']['supervisor_name'],
                'thesis_title'           => $sample['phd']['thesis_title'],
                'study_language'         => $sample['phd']['study_language'],
            ]);

            // 5. حركات الإقامة والسفر (لغير المعفى من الإقامة)
            if (!$sample['application']['is_residence_exempt'] && !empty($sample['residences'])) {
                foreach ($sample['residences'] as $res) {
                    $diff = Carbon::parse($res['entry_date'])->diff(Carbon::parse($res['exit_date']));
                    EducationResidence::create([
                        'education_id'  => $phdEd->id,
                        'page_number'   => $res['page_number'],
                        'entry_airport' => $res['entry_airport'],
                        'entry_date'    => $res['entry_date'],
                        'exit_airport'  => $res['exit_airport'],
                        'exit_date'     => $res['exit_date'],
                        'stamp_details' => "{$diff->y} سنة و {$diff->m} شهر و {$diff->d} يوم",
                    ]);
                }
            }

            // 6. إنشاء ملفات PDF بيضاء مكتملة بالملاحظات المتوافقة مع المتحكم والواجهة
            // 1- صورة عن الهوية الشخصية / إثبات الشخصية (لاحظ خلو الملاحظة من كلمة "جواز" لمنع التداخل مع 14)
            $this->createBlankPdfAttachment($app->id, $phdEd->id, 13, 'صورة عن الهوية الشخصية / إثبات الشخصية', $candName, $appNo, 'nid');
            // 2- شهادة البكالوريا
            $this->createBlankPdfAttachment($app->id, $hsEd->id, 1, 'نسخة مصدقة عن شهادة البكالوريا', $candName, $appNo, 'hs');
            // 3- قرار معادلة البكالوريا (عندما تكون غير سورية)
            if ($sample['include_hs_decision']) {
                $this->createBlankPdfAttachment($app->id, $hsEd->id, 2, 'قرار معادلة درجة البكالوريا من وزارة التربية', $candName, $appNo, 'hsdec');
            }
            // 4- الإجازة الجامعية مصدقة أصولاً
            $this->createBlankPdfAttachment($app->id, $baEd->id, 3, 'الإجازة الجامعية مصدقة أصولاً', $candName, $appNo, 'ba');
            // 5- شهادة الماجستير مصدقة أصولاً
            $this->createBlankPdfAttachment($app->id, $maEd->id, 6, 'شهادة الماجستير مصدقة أصولاً', $candName, $appNo, 'ma');
            // شرطي: قرار معادلة شهادة الماجستير
            if ($sample['include_ma_decision']) {
                $this->createBlankPdfAttachment($app->id, $maEd->id, 9, 'قرار معادلة شهادة الماجستير', $candName, $appNo, 'madec');
            }
            // 6- شهادة الدكتوراه مصدقة ومترجمة أصولاً
            $this->createBlankPdfAttachment($app->id, $phdEd->id, 10, 'شهادة الدكتوراه مصدقة ومترجمة أصولاً', $candName, $appNo, 'phd');
            // 7- قرارات التسجيل والمناقشة والمنح للدكتوراه
            $this->createBlankPdfAttachment($app->id, $phdEd->id, 11, 'قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه', $candName, $appNo, 'council');
            // 8- السيرة الذاتية
            $this->createBlankPdfAttachment($app->id, $phdEd->id, 14, 'السيرة الذاتية للمرشح', $candName, $appNo, 'cv');
            // 9- إيصال الدفع 125 ألف
            $this->createBlankPdfAttachment($app->id, $phdEd->id, 17, 'إيصال الدفع 125 ألف (رسم تعادل الدكتوراه 125,000 ل.س)', $candName, $appNo, 'fees');

            // 14 و 15: جواز السفر وحركة الهجرة والجوازات (إجباريان عند عدم الإعفاء)
            if (!$sample['application']['is_residence_exempt']) {
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 13, 'صورة جواز السفر مع صفحات الأختام والإقامة', $candName, $appNo, 'passport');
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 24, 'وثيقة حركة الهجرة والجوازات', $candName, $appNo, 'immigration');
            }

            // المرفقات الاختيارية (10، 11، 12، 13، 16) للمسودة الأولى لسهولة الاختبار الكامل
            if ($sample['include_optional']) {
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 22, 'إيصال المكتبة الوطنية', $candName, $appNo, 'lib');
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 5, 'كشف علامات الدكتوراه', $candName, $appNo, 'transcript');
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 19, 'شهادة قيادة الحاسوب (ICDL)', $candName, $appNo, 'icdl');
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 18, 'شهادة اللغة الإنكليزية', $candName, $appNo, 'eng');
                $this->createBlankPdfAttachment($app->id, $phdEd->id, 23, 'مرفقات ووثائق أخرى', $candName, $appNo, 'other');
            }
        }
    }

    private function createBlankPdfAttachment(
        int $appId,
        int $educationId,
        int $attachmentTypeId,
        string $docTitle,
        string $candidateName,
        string $appNo,
        string $keyTag
    ): EducationAttachment {
        $dir = storage_path('app/public/attachments/' . $appId);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = 'att_' . $appNo . '_' . $keyTag . '_' . substr(md5(uniqid('', true)), 0, 6) . '.pdf';
        $relativePath = 'attachments/' . $appId . '/' . $fileName;
        $fullPath = $dir . '/' . $fileName;

        $fontRegular = str_replace('\\', '/', public_path('fonts/segoeui.ttf'));
        $fontBold    = str_replace('\\', '/', public_path('fonts/segoeuib.ttf'));

        $html = '<!DOCTYPE html>
<html lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    @page { size: A4 portrait; margin: 25mm 20mm; }
    @font-face { font-family: "Segoe UI"; font-weight: normal; src: url("' . $fontRegular . '") format("truetype"); }
    @font-face { font-family: "Segoe UI"; font-weight: bold; src: url("' . $fontBold . '") format("truetype"); }
    * { font-family: "Segoe UI", "DejaVu Sans", sans-serif; box-sizing: border-box; }
    body { background-color: #ffffff; color: #111827; margin: 0; padding: 40px 25px; text-align: center; }
    .page-frame { border: 1px solid #d1d5db; padding: 55px 20px; margin: 40px auto; background-color: #ffffff; }
    .doc-label { font-size: 13px; color: #6b7280; margin-bottom: 18px; }
    .doc-title { font-size: 16.5px; font-weight: bold; color: #1a2a44; margin: 20px 0 35px 0; line-height: 1.6; }
    .divider { width: 180px; height: 2px; background-color: #c5a059; margin: 0 auto 30px auto; }
    .meta { font-size: 15px; color: #374151; margin: 10px 0; line-height: 1.6; }
    .footer-note { margin-top: 50px; font-size: 12px; color: #9ca3af; }
</style>
</head>
<body>
    <div class="page-frame">
        <div class="doc-label">وثيقة مرفقة للاختبار - معاملة تعادل دكتوراه خارجية</div>
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

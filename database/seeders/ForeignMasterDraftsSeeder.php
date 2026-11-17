<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationExperience;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\EducationResidence;
use App\Models\EquivalenceProfile;
use App\Models\LookupCountry;
use App\Models\LookupEducationLevel;
use App\Models\LookupUniversity;
use App\Models\User;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ForeignMasterDraftsSeeder extends Seeder
{
    /**
     * إضافة 3 مسودات ماجستير خارجي - تطبيقي و 3 مسودات ماجستير خارجي - نظري
     * في حساب جامعة الجزيرة الخاصة دون مسح البيانات الموجودة مسبقاً.
     */
    public function run(): void
    {
        $syria   = LookupCountry::where('name', 'سوريا')->first() ?? LookupCountry::find(1);
        $france  = LookupCountry::where('name', 'فرنسا')->first() ?? LookupCountry::find(2);
        $egypt   = LookupCountry::where('name', 'مصر')->first() ?? LookupCountry::find(79);
        $lebanon = LookupCountry::where('name', 'لبنان')->first() ?? LookupCountry::find(80);
        $jordan  = LookupCountry::where('name', 'الأردن')->first() ?? LookupCountry::find(81);

        // جامعة الجزيرة الخاصة (حساب الجامعة مقدم الطلب)
        $alJazeeraUni = LookupUniversity::where('name', 'LIKE', '%الجزيرة%')->first()
            ?? LookupUniversity::find(8)
            ?? LookupUniversity::create(['name' => 'جامعة الجزيرة الخاصة', 'country_id' => $syria->id]);

        $alJazeeraUser = User::where('university_id', $alJazeeraUni->id)->first()
            ?? User::where('email', 'uni_aljazeera@uni.edu.sy')->first()
            ?? User::whereHas('role', fn ($q) => $q->where('name', 'university'))->first();

        $uniUserId = $alJazeeraUser?->id ?? 3;

        // الجامعات السورية المرجعية للمؤهلات السابقة
        $damascusUni = LookupUniversity::where('country_id', $syria->id)->where('name', 'LIKE', '%دمشق%')->first()
            ?? LookupUniversity::find(1);
        $aleppoUni = LookupUniversity::where('country_id', $syria->id)->where('name', 'LIKE', '%حلب%')->first()
            ?? LookupUniversity::find(2);
        $tishreenUni = LookupUniversity::where('country_id', $syria->id)->where('name', 'LIKE', '%تشرين%')->first()
            ?? LookupUniversity::find(3);

        // الجامعات الخارجية مطابقة لرمز البلد الفعلي
        $lebaneseUni = LookupUniversity::where('country_id', $lebanon->id)->where('name', 'LIKE', '%اللبنانية%')->first()
            ?? LookupUniversity::firstOrCreate(['name' => 'الجامعة اللبنانية', 'country_id' => $lebanon->id]);
        $azharUni = LookupUniversity::where('country_id', $egypt->id)->first()
            ?? LookupUniversity::firstOrCreate(['name' => 'جامعة القاهرة', 'country_id' => $egypt->id]);
        $yarmoukUni = LookupUniversity::where('country_id', $jordan->id)->where('name', 'LIKE', '%اليرموك%')->first()
            ?? LookupUniversity::firstOrCreate(['name' => 'جامعة اليرموك', 'country_id' => $jordan->id]);
        $sorbonneUni = LookupUniversity::where('country_id', $france->id)->where('name', 'LIKE', '%السوربون%')->first()
            ?? LookupUniversity::firstOrCreate(['name' => 'جامعة السوربون بباريس', 'country_id' => $france->id]);

        // مستويات التعليم
        $hsLevelId = LookupEducationLevel::where('name', 'like', '%ثانوية%')->value('id') ?? 4;
        $baLevelId = LookupEducationLevel::where('name', 'like', '%إجازة%')->value('id') ?? 1;
        $maLevelId = LookupEducationLevel::where('name', 'like', '%ماجستير%')->value('id') ?? 2;

        // تنظيف تكرارات تشغيل سابق لنفس هذا السيدر فقط (idempotent بدون مسح السجلات الأخرى)
        $targetAppNos = [
            'MA-FOR-AP-2026-0001',
            'MA-FOR-AP-2026-0002',
            'MA-FOR-AP-2026-0003',
            'MA-FOR-TH-2026-0001',
            'MA-FOR-TH-2026-0002',
            'MA-FOR-TH-2026-0003',
        ];
        $targetNationalIds = [
            '01081122331',
            '02094455662',
            '03075588993',
            '01062233444',
            '02057788995',
            '04039911226',
        ];

        $this->cleanupPreviousSeededDrafts($targetAppNos, $targetNationalIds);

        $drafts = [
            // =========================================================
            // أولاً: 3 مسودات ماجستير خارجي - تطبيقي (request_type = 5)
            // =========================================================
            [
                'track' => 'applied',
                'request_type' => 5,
                'has_previous_degree' => 0,
                'is_residence_exempt' => false,
                'profile' => [
                    'full_name'   => 'نوار غسان الحموي',
                    'father_name' => 'غسان',
                    'mother_name' => 'رشا المنير',
                    'national_id' => '01081122331',
                    'dob'         => '1996-04-12',
                    'job_title'   => 'مهندس برمجيات ومطور أنظمة',
                    'gender'      => 'ذكر',
                    'email'       => 'nawar.hamwi@aljazeera.edu.sy',
                    'mobile'      => '0938112231',
                    'phone'       => '0115544210',
                    'address'     => 'دمشق - المزة - فيلات غربية - بناء رقم 12',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-AP-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم هندسة البرمجيات والنظم',
                    'new_uni_request_no'   => '301/خ.ت',
                    'new_uni_request_date' => '2026-11-15',
                    'study_system'         => 'سنوي',
                ],
                'hs' => [
                    'country_id'   => $lebanon->id, // بكالوريا غير سورية تتطلب قرار معادلة ثانوية
                    'section_name' => 'علمي',
                    'grant_date'   => '2014-07-10',
                    'decision_no'  => '1420/ث',
                    'decision_date'=> '2015-02-18',
                ],
                'ba' => [
                    'country_id'     => $lebanon->id, // إجازة غير سورية: لا تتطلب قرار معادلة إجازة
                    'university_id'  => $lebaneseUni->id,
                    'faculty'        => 'كلية الهندسة',
                    'department'     => 'قسم الهندسة المعلوماتية',
                    'specialization' => 'هندسة المعلوماتية والاتصالات',
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2019-07-20',
                ],
                'ma' => [
                    'country_id'             => $lebanon->id,
                    'university_id'          => $lebaneseUni->id,
                    'faculty'                => 'كلية الهندسة',
                    'department'             => 'قسم هندسة البرمجيات',
                    'general_specialization' => 'الهندسة المعلوماتية',
                    'exact_specialization'   => 'ذكاء الأعمال وهندسة البيانات',
                    'supervisor_name'        => 'أ.د. شربل أنطوان حداد',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2020-10-01',
                    'defense_date'           => '2022-09-12',
                    'grant_date'             => '2022-11-15',
                    'thesis_title'           => 'تحسين كفاءة نماذج التعلم العميق في معالجة البيانات الضخمة للمؤسسات التعليمية',
                ],
                'residences' => [
                    [
                        'entry_date'    => '2020-10-05',
                        'entry_airport' => 'مطار رفيق الحريري الدولي',
                        'exit_date'     => '2021-07-05',
                        'exit_airport'  => 'معبر جديدة يابوس الحدودي',
                        'page_number'   => '14',
                    ],
                    [
                        'entry_date'    => '2021-09-20',
                        'entry_airport' => 'معبر المصنع الحدودي',
                        'exit_date'     => '2022-09-20',
                        'exit_airport'  => 'مطار رفيق الحريري الدولي',
                        'page_number'   => '22',
                    ],
                ],
                'experiences' => [],
                'include_optional_attachments' => true,
            ],
            [
                'track' => 'applied',
                'request_type' => 5,
                'has_previous_degree' => 0,
                'is_residence_exempt' => false,
                'profile' => [
                    'full_name'   => 'رنيم فؤاد البكور',
                    'father_name' => 'فؤاد',
                    'mother_name' => 'مها الدالاتي',
                    'national_id' => '02094455662',
                    'dob'         => '1995-09-03',
                    'job_title'   => 'باحثة في الكيمياء التحليلية والصيدلانية',
                    'gender'      => 'أنثى',
                    'email'       => 'raneem.bakour@aljazeera.edu.sy',
                    'mobile'      => '0944225562',
                    'phone'       => '0214433219',
                    'address'     => 'حلب - حي السبيل - شارع النيل - بناء رقم 7',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-AP-2026-0002',
                    'work_faculty'         => 'كلية الصيدلة',
                    'work_department'      => 'قسم الكيمياء الصيدلانية والرقابة الدوائية',
                    'new_uni_request_no'   => '302/خ.ت',
                    'new_uni_request_date' => '2026-11-16',
                    'study_system'         => 'فصلي',
                ],
                'hs' => [
                    'country_id'   => $syria->id,
                    'section_name' => 'علمي',
                    'grant_date'   => '2013-07-15',
                    'decision_no'  => null,
                    'decision_date'=> null,
                ],
                'ba' => [
                    'country_id'     => $syria->id,
                    'university_id'  => $aleppoUni->id,
                    'faculty'        => 'كلية العلوم',
                    'department'     => 'قسم الكيمياء',
                    'specialization' => 'الكيمياء التطبيقية',
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2018-08-12',
                ],
                'ma' => [
                    'country_id'             => $egypt->id,
                    'university_id'          => $azharUni->id,
                    'faculty'                => 'كلية العلوم',
                    'department'             => 'قسم الكيمياء التحليلية',
                    'general_specialization' => 'الكيمياء',
                    'exact_specialization'   => 'التحليل الصيدلاني والرقابة الدوائية',
                    'supervisor_name'        => 'أ.د. طارق عبد الحميد شرف',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2019-10-10',
                    'defense_date'           => '2021-12-20',
                    'grant_date'             => '2022-02-14',
                    'thesis_title'           => 'تطوير طرائق طيفية وكروماتوغرافية حديثة لتقدير المركبات الدوائية في المستحضرات المركبة',
                ],
                'residences' => [
                    [
                        'entry_date'    => '2019-10-15',
                        'entry_airport' => 'مطار القاهرة الدولي',
                        'exit_date'     => '2021-01-15',
                        'exit_airport'  => 'مطار القاهرة الدولي',
                        'page_number'   => '9',
                    ],
                    [
                        'entry_date'    => '2021-03-01',
                        'entry_airport' => 'مطار برج العرب الدولي',
                        'exit_date'     => '2021-12-25',
                        'exit_airport'  => 'مطار القاهرة الدولي',
                        'page_number'   => '18',
                    ],
                ],
                'experiences' => [],
                'include_optional_attachments' => false,
            ],
            [
                'track' => 'applied',
                'request_type' => 5,
                'has_previous_degree' => 0,
                'is_residence_exempt' => true, // معفى من شرط الإقامة (دراسة عن بُعد)
                'profile' => [
                    'full_name'   => 'يزن سمير الشهابي',
                    'father_name' => 'سمير',
                    'mother_name' => 'فدوى الأتاسي',
                    'national_id' => '03075588993',
                    'dob'         => '1997-02-18',
                    'job_title'   => 'اختصاصي أنظمة المعلومات الإدارية',
                    'gender'      => 'ذكر',
                    'email'       => 'yazan.shihabi@aljazeera.edu.sy',
                    'mobile'      => '0955778893',
                    'phone'       => '0312233449',
                    'address'     => 'حمص - الإنشاءات - شارع البرازيل - بناء رقم 19',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-AP-2026-0003',
                    'work_faculty'         => 'كلية إدارة الأعمال',
                    'work_department'      => 'قسم نظم المعلومات الإدارية',
                    'new_uni_request_no'   => '303/خ.ت',
                    'new_uni_request_date' => '2026-11-17',
                    'study_system'         => 'فصلي',
                ],
                'hs' => [
                    'country_id'   => $syria->id,
                    'section_name' => 'علمي',
                    'grant_date'   => '2015-07-12',
                    'decision_no'  => null,
                    'decision_date'=> null,
                ],
                'ba' => [
                    'country_id'     => $syria->id,
                    'university_id'  => $damascusUni->id,
                    'faculty'        => 'كلية الاقتصاد',
                    'department'     => 'قسم المعلوماتية الإدارية',
                    'specialization' => 'نظم المعلومات الإدارية',
                    'rank'           => 'جيد',
                    'grant_date'     => '2020-08-20',
                ],
                'ma' => [
                    'country_id'             => $jordan->id,
                    'university_id'          => $yarmoukUni->id,
                    'faculty'                => 'كلية الاقتصاد والعلوم الإدارية',
                    'department'             => 'قسم نظم المعلومات الإدارية',
                    'general_specialization' => 'إدارة الأعمال ونظم المعلومات',
                    'exact_specialization'   => 'تحول الأعمال الرقمي (برنامج أونلاين)',
                    'supervisor_name'        => 'أ.د. محمود سليمان الخصاونة',
                    'rank'                   => 'جيد جداً',
                    'registration_date'      => '2021-10-05',
                    'defense_date'           => '2024-06-14',
                    'grant_date'             => '2024-08-01',
                    'thesis_title'           => 'دور الحوسبة السحابية في تحسين جودة اتخاذ القرار في الجامعات الخاصة',
                ],
                'residences' => [],
                'experiences' => [],
                'include_optional_attachments' => true,
            ],

            // =========================================================
            // ثانياً: 3 مسودات ماجستير خارجي - نظري (request_type = 6)
            // =========================================================
            [
                'track' => 'theoretical',
                'request_type' => 6,
                'has_previous_degree' => 1,
                'is_residence_exempt' => false,
                'profile' => [
                    'full_name'   => 'علاء الدين حسان القطب',
                    'father_name' => 'حسان',
                    'mother_name' => 'هدى النابلسي',
                    'national_id' => '01062233444',
                    'dob'         => '1991-06-22',
                    'job_title'   => 'عضو هيئة تدريسية - قسم الرياضيات التطبيقية',
                    'gender'      => 'ذكر',
                    'email'       => 'alaa.kotob@aljazeera.edu.sy',
                    'mobile'      => '0932665544',
                    'phone'       => '0113322114',
                    'address'     => 'دمشق - أبو رمانة - جادة الجلاء - بناء رقم 21',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-TH-2026-0001',
                    'work_faculty'         => 'كلية الهندسة المعلوماتية',
                    'work_department'      => 'قسم العلوم الأساسية والرياضيات',
                    'new_uni_request_no'   => '401/خ.ن',
                    'new_uni_request_date' => '2026-11-15',
                    'study_system'         => 'سنوي',
                ],
                'hs' => [
                    'country_id'   => $syria->id,
                    'section_name' => 'علمي',
                    'grant_date'   => '2009-07-05',
                    'decision_no'  => null,
                    'decision_date'=> null,
                ],
                'ba' => [
                    'country_id'     => $jordan->id, // إجازة من خارج سوريا بدون قرار معادلة
                    'university_id'  => $yarmoukUni->id,
                    'faculty'        => 'كلية العلوم',
                    'department'     => 'قسم الرياضيات',
                    'specialization' => 'الرياضيات التطبيقية',
                    'rank'           => 'ممتاز',
                    'grant_date'     => '2014-07-15',
                ],
                'ma' => [
                    'country_id'             => $france->id,
                    'university_id'          => $sorbonneUni->id,
                    'faculty'                => 'كلية العلوم والرياضيات',
                    'department'             => 'قسم الرياضيات التطبيقية',
                    'general_specialization' => 'الرياضيات',
                    'exact_specialization'   => 'التحليل العددي والمعادلات التفاضلية',
                    'supervisor_name'        => 'Prof. Jean-Luc Moreau',
                    'rank'                   => 'ممتاز مع مرتبة الشرف',
                    'registration_date'      => '2015-09-15',
                    'defense_date'           => '2018-06-25',
                    'grant_date'             => '2018-09-10',
                    'thesis_title'           => 'الطرائق العددية الحديثة لحل المعادلات التفاضلية الجزئية غير الخطية في النمذجة الفيزيائية',
                ],
                'residences' => [
                    [
                        'entry_date'    => '2015-09-10',
                        'entry_airport' => 'مطار باريس شارل ديغول',
                        'exit_date'     => '2017-07-10',
                        'exit_airport'  => 'مطار باريس شارل ديغول',
                        'page_number'   => '6',
                    ],
                    [
                        'entry_date'    => '2017-09-01',
                        'entry_airport' => 'مطار باريس أورلي',
                        'exit_date'     => '2018-07-01',
                        'exit_airport'  => 'مطار باريس شارل ديغول',
                        'page_number'   => '15',
                    ],
                ],
                'experiences' => [
                    [
                        'institution_name' => 'جامعة دمشق - كلية العلوم',
                        'role_or_subject'  => 'تدريس مقررات التحليل العددي والجبر الخطي',
                        'start_date'       => '2019-10-01',
                        'end_date'         => '2021-10-15',
                        'notes'            => 'الفترة الأولى - سنتان في التدريس الجامعي',
                    ],
                    [
                        'institution_name' => 'جامعة الجزيرة الخاصة - كلية الهندسة',
                        'role_or_subject'  => 'محاضر في الرياضيات الهندسية والاحتمالات',
                        'start_date'       => '2022-02-01',
                        'end_date'         => '2023-03-01',
                        'notes'            => 'الفترة الثانية - جامعة الجزيرة الخاصة',
                    ],
                ],
                'include_optional_attachments' => true,
            ],
            [
                'track' => 'theoretical',
                'request_type' => 6,
                'has_previous_degree' => 1,
                'is_residence_exempt' => false,
                'profile' => [
                    'full_name'   => 'لمى عدنان الصباغ',
                    'father_name' => 'عدنان',
                    'mother_name' => 'سميحة العظم',
                    'national_id' => '02057788995',
                    'dob'         => '1992-11-08',
                    'job_title'   => 'محاضرة متفرغة في علم النفس التربوي',
                    'gender'      => 'أنثى',
                    'email'       => 'lama.sabbagh@aljazeera.edu.sy',
                    'mobile'      => '0991445565',
                    'phone'       => '0412255990',
                    'address'     => 'اللاذقية - حي الرمل الجنوبي - شارع 8 آذار - بناء رقم 3',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-TH-2026-0002',
                    'work_faculty'         => 'كلية العلوم الإنسانية والتربوية',
                    'work_department'      => 'قسم الإرشاد النفسي والتربوي',
                    'new_uni_request_no'   => '402/خ.ن',
                    'new_uni_request_date' => '2026-11-16',
                    'study_system'         => 'فصلي',
                ],
                'hs' => [
                    'country_id'   => $jordan->id, // بكالوريا أردنية غير سورية مع قرار معادلة ثانوية
                    'section_name' => 'أدبي',
                    'grant_date'   => '2010-07-18',
                    'decision_no'  => '874/م.ث',
                    'decision_date'=> '2011-01-25',
                ],
                'ba' => [
                    'country_id'     => $syria->id,
                    'university_id'  => $tishreenUni->id,
                    'faculty'        => 'كلية الآداب والعلوم الإنسانية',
                    'department'     => 'قسم علم النفس',
                    'specialization' => 'علم النفس العام',
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2015-08-18',
                ],
                'ma' => [
                    'country_id'             => $egypt->id,
                    'university_id'          => $azharUni->id,
                    'faculty'                => 'كلية الدراسات الإنسانية',
                    'department'             => 'قسم علم النفس التربوي',
                    'general_specialization' => 'العلوم التربوية والنفسية',
                    'exact_specialization'   => 'القياس والتقويم التربوي',
                    'supervisor_name'        => 'أ.د. هالة مصطفى الجوهري',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2016-10-01',
                    'defense_date'           => '2019-06-10',
                    'grant_date'             => '2019-08-05',
                    'thesis_title'           => 'فاعلية برنامج تدريبي قائم على الذكاء الانفعالي في خفض القلق الأكاديمي لدى طلبة الجامعات',
                ],
                'residences' => [
                    [
                        'entry_date'    => '2016-10-05',
                        'entry_airport' => 'مطار القاهرة الدولي',
                        'exit_date'     => '2018-04-05',
                        'exit_airport'  => 'مطار القاهرة الدولي',
                        'page_number'   => '11',
                    ],
                    [
                        'entry_date'    => '2018-09-15',
                        'entry_airport' => 'مطار القاهرة الدولي',
                        'exit_date'     => '2019-06-15',
                        'exit_airport'  => 'مطار القاهرة الدولي',
                        'page_number'   => '19',
                    ],
                ],
                'experiences' => [
                    [
                        'institution_name' => 'جامعة تشرين - كلية التربية',
                        'role_or_subject'  => 'محاضرة مكلفة - مقررات علم النفس التربوي',
                        'start_date'       => '2020-09-15',
                        'end_date'         => '2021-10-01',
                        'notes'            => 'عقد تدريسي مصدق - السنة الأولى',
                    ],
                    [
                        'institution_name' => 'جامعة الجزيرة الخاصة',
                        'role_or_subject'  => 'تدريس مقررات القياس النفسي وصعوبات التعلم',
                        'start_date'       => '2022-01-10',
                        'end_date'         => '2023-05-10',
                        'notes'            => 'عقد تدريسي وإيصالات رواتب مصدقة',
                    ],
                ],
                'include_optional_attachments' => false,
            ],
            [
                'track' => 'theoretical',
                'request_type' => 6,
                'has_previous_degree' => 1,
                'is_residence_exempt' => true, // معفى من شرط الإقامة (ماجستير اللغة العربية أونلاين)
                'profile' => [
                    'full_name'   => 'مازن رياض حيدر',
                    'father_name' => 'رياض',
                    'mother_name' => 'سعاد الخوري',
                    'national_id' => '04039911226',
                    'dob'         => '1993-08-19',
                    'job_title'   => 'مدرّس وباحث في اللغة العربية والنقد الأدبي',
                    'gender'      => 'ذكر',
                    'email'       => 'mazen.haidar@aljazeera.edu.sy',
                    'mobile'      => '0966889916',
                    'phone'       => '0215566778',
                    'address'     => 'طرطوس - الكورنيش الشرقي - قرب مديرية الثقافة - بناء رقم 9',
                ],
                'application' => [
                    'application_no'       => 'MA-FOR-TH-2026-0003',
                    'work_faculty'         => 'كلية الآداب والعلوم الإنسانية',
                    'work_department'      => 'قسم اللغة العربية وآدابها',
                    'new_uni_request_no'   => '403/خ.ن',
                    'new_uni_request_date' => '2026-11-17',
                    'study_system'         => 'فصلي',
                ],
                'hs' => [
                    'country_id'   => $syria->id,
                    'section_name' => 'أدبي',
                    'grant_date'   => '2012-07-10',
                    'decision_no'  => null,
                    'decision_date'=> null,
                ],
                'ba' => [
                    'country_id'     => $syria->id,
                    'university_id'  => $aleppoUni->id,
                    'faculty'        => 'كلية الآداب والعلوم الإنسانية',
                    'department'     => 'قسم اللغة العربية وآدابها',
                    'specialization' => 'اللغة العربية وآدابها',
                    'rank'           => 'جيد جداً',
                    'grant_date'     => '2016-08-28',
                ],
                'ma' => [
                    'country_id'             => $lebanon->id,
                    'university_id'          => $lebaneseUni->id,
                    'faculty'                => 'كلية الآداب والعلوم الإنسانية',
                    'department'             => 'قسم اللغة العربية وآدابها',
                    'general_specialization' => 'اللغة العربية',
                    'exact_specialization'   => 'الأدب العربي الحديث والنقد الأدبي',
                    'supervisor_name'        => 'أ.د. نزيه شحادة حمود',
                    'rank'                   => 'ممتاز',
                    'registration_date'      => '2018-10-01',
                    'defense_date'           => '2021-05-18',
                    'grant_date'             => '2021-07-12',
                    'thesis_title'           => 'تحولات الخطاب الشعري والنقدي في الأدب العربي المعاصر (دراسة عن بُعد)',
                ],
                'residences' => [], // معفى من شرط الإقامة (لا يتطلب حركات دخول/خروج)
                'experiences' => [
                    [
                        'institution_name' => 'جامعة حلب - كلية الآداب والعلوم الإنسانية',
                        'role_or_subject'  => 'تدريس مقررات الأدب العربي الحديث والنقد',
                        'start_date'       => '2021-10-01',
                        'end_date'         => '2022-11-15',
                        'notes'            => 'عقد عمل وخبرة تدريسية للعام الأول',
                    ],
                    [
                        'institution_name' => 'جامعة الجزيرة الخاصة - كلية الآداب واللغات',
                        'role_or_subject'  => 'محاضر مكلف - النقد الأدبي والبلاغة العربية',
                        'start_date'       => '2023-02-01',
                        'end_date'         => '2024-05-01',
                        'notes'            => 'عقد عمل وإيصالات تحويل بنكية للعام الثاني',
                    ],
                ],
                'include_optional_attachments' => true,
            ],
        ];

        $this->command?->info('📝 جاري إضافة 3 مسودات ماجستير خارجي - تطبيقي و 3 مسودات ماجستير خارجي - نظري تحت حساب جامعة الجزيرة الخاصة...');

        foreach ($drafts as $index => $item) {
            $num   = $index + 1;
            $pData = $item['profile'];
            $aData = $item['application'];
            $isTheoretical = ($item['track'] === 'theoretical');

            // 1. ملف المرشح
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

            // 2. سجل الطلب في المسودة
            $application = Application::create([
                'candidate_id'         => $profile->id,
                'parent_application_id'=> null,
                'application_no'       => $aData['application_no'],
                'request_type'         => $item['request_type'], // 5 = ماجستير خارجي - تطبيقي | 6 = ماجستير خارجي - نظري
                'status'               => 1, // مسودة
                'work_university_id'   => $alJazeeraUni->id,
                'user_id'              => $uniUserId,
                'work_faculty'         => $aData['work_faculty'],
                'work_department'      => $aData['work_department'],
                'new_uni_request_no'   => $aData['new_uni_request_no'],
                'new_uni_request_date' => $aData['new_uni_request_date'],
                'is_first_time'        => 1,
                'study_system'         => $aData['study_system'],
                'has_previous_degree'  => $item['has_previous_degree'],
                'is_residence_exempt'  => $item['is_residence_exempt'],
            ]);

            // 3. الشهادة الثانوية (مع بيانات المعادلة إذا كانت غير سورية)
            $hsNotes = null;
            if ((int) $item['hs']['country_id'] !== (int) $syria->id && !empty($item['hs']['decision_no'])) {
                $hsNotes = 'رقم قرار المعادلة الثانوية: ' . $item['hs']['decision_no'] . ' | تاريخ القرار: ' . $item['hs']['decision_date'];
            }
            $edHS = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $hsLevelId,
                'country_id'             => $item['hs']['country_id'],
                'section_name'           => $item['hs']['section_name'],
                'general_specialization' => $item['hs']['section_name'],
                'grant_date'             => $item['hs']['grant_date'],
                'notes'                  => $hsNotes,
            ]);

            // 4. الإجازة الجامعية الأولى (بدون قرار معادلة حتى لو كانت خارج سوريا)
            $edBA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $baLevelId,
                'country_id'             => $item['ba']['country_id'],
                'university_id'          => $item['ba']['university_id'],
                'faculty'                => $item['ba']['faculty'],
                'department'             => $item['ba']['department'],
                'section_name'           => $item['ba']['specialization'],
                'general_specialization' => $item['ba']['faculty'],
                'exact_specialization'   => $item['ba']['specialization'],
                'rank'                   => $item['ba']['rank'],
                'grant_date'             => $item['ba']['grant_date'],
            ]);

            // 5. شهادة الماجستير الخارجي
            $expStarts = array_column($item['experiences'], 'start_date');
            $expEnds   = array_column($item['experiences'], 'end_date');
            $expInsts  = array_column($item['experiences'], 'institution_name');

            $edMA = Education::create([
                'application_id'         => $application->id,
                'education_level_id'     => $maLevelId,
                'country_id'             => $item['ma']['country_id'],
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
                'experience_from_year'   => !empty($expStarts) ? min($expStarts) : null,
                'experience_to_year'     => !empty($expEnds) ? max($expEnds) : null,
                'notes'                  => $isTheoretical && !empty($expInsts)
                    ? ('فترات خبرة تدريسية: ' . implode('، ', $expInsts))
                    : ($item['is_residence_exempt'] ? 'معفى من شرط الإقامة (دراسة عن بُعد / أونلاين)' : null),
            ]);

            // 6. حركات الإقامة الفعلية (عند عدم الإعفاء من الإقامة)
            if (!$item['is_residence_exempt']) {
                foreach ($item['residences'] as $res) {
                    $staySummary = Application::calculateResidenceSummary([(object) [
                        'entry_date' => $res['entry_date'],
                        'exit_date'  => $res['exit_date'],
                    ]]);
                    $stayLabel = $staySummary['years'] . ' سنة و ' . $staySummary['months'] . ' شهر و ' . $staySummary['days'] . ' يوم';

                    EducationResidence::create([
                        'education_id'  => $edMA->id,
                        'page_number'   => $res['page_number'],
                        'entry_date'    => $res['entry_date'],
                        'entry_airport' => $res['entry_airport'],
                        'exit_date'     => $res['exit_date'],
                        'exit_airport'  => $res['exit_airport'],
                        'stamp_details' => $stayLabel . ' (' . $staySummary['total_days'] . ' يوم)',
                    ]);
                }
            }

            // 7. فترات الخبرة التدريسية التراكمية (للمسار النظري فقط)
            foreach ($item['experiences'] as $exp) {
                $sDate = Carbon::parse($exp['start_date']);
                $eDate = Carbon::parse($exp['end_date']);
                $days  = max(0, (int) $sDate->diffInDays($eDate));

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

            // 8. توليد مرفقات PDF البيضاء المنسقة بملاحظات مطابقة تماماً لقواعد Foreign Masters Wizard
            $this->createWhitePagePdf($application->id, $edMA->id, 13, 'صورة عن الهوية الشخصية', $profile->full_name, $aData['application_no'], 'national_id');
            $this->createWhitePagePdf($application->id, $edHS->id, 1, 'صورة عن شهادة البكالوريا (الثانوية العامة)', $profile->full_name, $aData['application_no'], 'hs_cert');

            if ((int) $item['hs']['country_id'] !== (int) $syria->id) {
                $this->createWhitePagePdf($application->id, $edHS->id, 2, 'قرار معادلة الشهادة الثانوية غير السورية', $profile->full_name, $aData['application_no'], 'hs_decision');
            }

            $this->createWhitePagePdf($application->id, $edBA->id, 3, 'نسخة مصدقة عن الإجازة الجامعية الأولى', $profile->full_name, $aData['application_no'], 'ba_cert');
            $this->createWhitePagePdf($application->id, $edMA->id, 6, 'شهادة الماجستير الخارجي مع الترجمة والتصديق', $profile->full_name, $aData['application_no'], 'ma_cert');
            $this->createWhitePagePdf($application->id, $edMA->id, 7, 'قرارات التسجيل بالدرجة وقرار الحصول على الدرجة', $profile->full_name, $aData['application_no'], 'reg_defense');

            if ($isTheoretical) {
                $this->createWhitePagePdf($application->id, $edMA->id, 17, 'إيصال تسديد رسم تعادل الماجستير النظري الخارجي (125,000 ل.س)', $profile->full_name, $aData['application_no'], 'fees_125k');
            } else {
                $this->createWhitePagePdf($application->id, $edMA->id, 16, 'إيصال تسديد رسم تعادل الماجستير التطبيقي الخارجي (100,000 ل.س)', $profile->full_name, $aData['application_no'], 'fees_100k');
            }

            // جواز السفر وبيان الحركة إلزاميان فقط عند عدم الإعفاء من الإقامة
            if (!$item['is_residence_exempt']) {
                $this->createWhitePagePdf($application->id, $edMA->id, 13, 'صورة جواز السفر مع صفحات الأختام والإقامة', $profile->full_name, $aData['application_no'], 'passport');
                $this->createWhitePagePdf($application->id, $edMA->id, 24, 'بيان حركة من الهجرة والجوازات', $profile->full_name, $aData['application_no'], 'immigration');
            }

            $this->createWhitePagePdf($application->id, $edMA->id, 14, 'السيرة الذاتية للمرشح', $profile->full_name, $aData['application_no'], 'cv');
            $this->createWhitePagePdf($application->id, $edMA->id, 8, 'ملخص عن رسالة الماجستير باللغة العربية', $profile->full_name, $aData['application_no'], 'abstract');

            if ($isTheoretical) {
                $this->createWhitePagePdf($application->id, $edMA->id, 21, 'إيصالات التحويل والرواتب المصدقة', $profile->full_name, $aData['application_no'], 'salary_receipts');
                $this->createWhitePagePdf($application->id, $edMA->id, 21, 'العقود مع الجامعات الخاصة', $profile->full_name, $aData['application_no'], 'contracts');
                $this->createWhitePagePdf($application->id, $edMA->id, 20, 'شهادة الخبرة التدريسية داخل سوريا', $profile->full_name, $aData['application_no'], 'experience_cert');
            }

            if (!empty($item['include_optional_attachments'])) {
                $this->createWhitePagePdf($application->id, $edMA->id, 19, 'شهادة قيادة الحاسوب (ICDL)', $profile->full_name, $aData['application_no'], 'icdl');
                $this->createWhitePagePdf($application->id, $edMA->id, 18, 'شهادة اللغة الإنكليزية', $profile->full_name, $aData['application_no'], 'english');
                $this->createWhitePagePdf($application->id, $edMA->id, 5, 'كشف علامات الماجستير', $profile->full_name, $aData['application_no'], 'transcript');
                $this->createWhitePagePdf($application->id, $edMA->id, 23, 'مرفقات ووثائق داعمة أخرى', $profile->full_name, $aData['application_no'], 'other');
            }

            $trackAr  = $isTheoretical ? 'ماجستير خارجي - نظري' : 'ماجستير خارجي - تطبيقي';
            $exemptAr = $item['is_residence_exempt'] ? 'معفى من الإقامة' : 'يتطلب إقامة';
            $this->command?->info("✅ {$num}. [{$trackAr} | {$exemptAr}] {$profile->full_name} | {$aData['application_no']} | ID: {$application->id}");
        }

        $this->command?->info('🎉 تمت إضافة 6 مسودات ماجستير خارجي (3 تطبيقي + 3 نظري) بنجاح مع الإبقاء على الطلبات السابقة دون أي مساس!');
    }

    /**
     * تنظيف أي مسودات مطابقة تم توليدها مسبقاً من هذا السيدر فقط لتكرار التشغيل بأمان.
     */
    private function cleanupPreviousSeededDrafts(array $appNos, array $nationalIds): void
    {
        $oldAppIds = Application::whereIn('application_no', $appNos)->pluck('id')->all();

        if (!empty($oldAppIds)) {
            $edIds = Education::whereIn('application_id', $oldAppIds)->pluck('id')->all();

            if (!empty($edIds)) {
                EducationResidence::whereIn('education_id', $edIds)->delete();
                EducationAttachment::whereIn('education_id', $edIds)->delete();
            }

            if (Schema::hasTable('application_experiences')) {
                ApplicationExperience::whereIn('application_id', $oldAppIds)->delete();
            }

            Education::whereIn('application_id', $oldAppIds)->delete();
            Application::whereIn('id', $oldAppIds)->delete();

            foreach ($oldAppIds as $oldAppId) {
                $dir = storage_path('app/public/attachments/' . $oldAppId);
                if (File::isDirectory($dir)) {
                    File::deleteDirectory($dir);
                }
            }
        }

        // حذف ملفات المرشحين الخاصة بهذا السيدر فقط إذا لم تعد مرتبطة بأي طلب
        $profiles = EquivalenceProfile::whereIn('national_id', $nationalIds)->get();
        foreach ($profiles as $profile) {
            if (!Application::where('candidate_id', $profile->id)->exists()) {
                $profile->delete();
            }
        }
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
    ): void {
        static $arabic = null;
        if ($arabic === null) {
            $arabic = new Arabic();
        }

        $safeKey   = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $keySuffix !== '' ? $keySuffix : ('type_' . $attachmentTypeId));
        $fileName  = 'seed_' . $safeKey . '.pdf';
        $relPath   = 'attachments/' . $appId . '/' . $fileName;

        $tMinistry  = $arabic->utf8Glyphs('الجمهورية العربية السورية - وزارة التعليم العالي والبحث العلمي');
        $tSystem    = $arabic->utf8Glyphs('وثيقة مرفقة إلكترونياً لأغراض الفحص والتجربة');
        $tDocTitle  = $arabic->utf8Glyphs('اسم الوثيقة: ' . $docTitle);
        $tCandidate = $arabic->utf8Glyphs('المرشح: ' . $candidateName);
        $tAppNo     = $arabic->utf8Glyphs('رقم الطلب: ' . $appNo);

        $html = '<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 28mm 22mm; }
    body {
        font-family: DejaVu Sans, sans-serif;
        background: #ffffff;
        color: #111827;
        margin: 0;
        padding: 0;
    }
    .box {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 36px 28px;
        text-align: center;
    }
    .ministry { font-size: 13px; color: #475569; margin-bottom: 10px; }
    .subtitle { font-size: 11px; color: #64748b; margin-bottom: 28px; }
    .doc-title { font-size: 18px; font-weight: bold; color: #0f172a; margin: 22px 0; padding: 14px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
    .meta { font-size: 13px; color: #1e293b; margin-top: 12px; }
</style>
</head>
<body>
    <div class="box">
        <div class="ministry">' . $tMinistry . '</div>
        <div class="subtitle">' . $tSystem . '</div>
        <div class="doc-title">' . $tDocTitle . '</div>
        <div class="meta">' . $tCandidate . '</div>
        <div class="meta">' . $tAppNo . '</div>
    </div>
</body>
</html>';

        $pdfOutput = Pdf::loadHTML($html)->setPaper('a4', 'portrait')->output();
        Storage::disk('public')->put($relPath, $pdfOutput);

        EducationAttachment::create([
            'education_id'       => $educationId,
            'attachment_type_id' => $attachmentTypeId,
            'file_path'          => $relPath,
            'notes'              => $docTitle,
        ]);
    }
}

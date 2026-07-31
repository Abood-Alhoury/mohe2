<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\LookupCountry;
use App\Models\LookupEducationLevel;
use App\Models\LookupUniversity;
use App\Models\LookupAttachmentType;
use App\Models\EquivalenceProfile;
use App\Models\Application;
use App\Models\ApplicationCourse;
use App\Models\Education;
use App\Models\EducationResidence;
use App\Models\EducationAttachment;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;

class NewdrawSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::create(['name' => 'admin']);
        $uniRole = Role::create(['name' => 'university']);

        // 2. Countries
        $syria = LookupCountry::create(['name' => 'سوريا']);
        $egypt = LookupCountry::create(['name' => 'مصر']);
        $lebanon = LookupCountry::create(['name' => 'لبنان']);
        $algeria = LookupCountry::create(['name' => 'الجزائر']);
        $palestine = LookupCountry::create(['name' => 'فلسطين']);
        $iraq = LookupCountry::create(['name' => 'العراق']);
        $jordan = LookupCountry::create(['name' => 'الأردن']);
        $uae = LookupCountry::create(['name' => 'الإمارات']);
        $saudi = LookupCountry::create(['name' => 'السعودية']);
        $morocco = LookupCountry::create(['name' => 'المغرب']);

        // 3. Education Levels
        $l1 = LookupEducationLevel::create(['name' => 'إجازة جامعية']);
        $l2 = LookupEducationLevel::create(['name' => 'دبلوم دراسات عليا']);
        $l3 = LookupEducationLevel::create(['name' => 'ماجستير']);
        $l4 = LookupEducationLevel::create(['name' => 'دكتوراه']);
        $l5 = LookupEducationLevel::create(['name' => 'معهد متوسط']);
        $l6 = LookupEducationLevel::create(['name' => 'ثانوية عامة']);
        $l7 = LookupEducationLevel::create(['name' => 'إعدادية']);
        $l8 = LookupEducationLevel::create(['name' => 'بكالوريوس فخري']);
        $l9 = LookupEducationLevel::create(['name' => 'ماجستير تأهيل وتخصص']);
        $l10 = LookupEducationLevel::create(['name' => 'اختصاص طبي']);

        // 4. Universities
        $damascus = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة دمشق']);
        $aleppo = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة حلب']);
        $tishreen = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة تشرين']);
        $baath = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة البعث']);
        $hama = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة حماة']);
        $qalamoun = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة القلمون']);
        $antakeya = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة أنطاكية']);
        $eibla = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة إيبلا']);
        $arabInt = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'الجامعة العربية الدولية']);
        $rasheed = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة الرشيد']);

        // Foreign Universities
        $cairoUni = LookupUniversity::create(['country_id' => $egypt->id, 'name' => 'جامعة القاهرة']);
        $aubUni = LookupUniversity::create(['country_id' => $lebanon->id, 'name' => 'الجامعة الأمريكية في بيروت']);
        $birzeit = LookupUniversity::create(['country_id' => $palestine->id, 'name' => 'جامعة بيرزيت']);
        $baghdad = LookupUniversity::create(['country_id' => $iraq->id, 'name' => 'جامعة بغداد']);
        $jordanUni = LookupUniversity::create(['country_id' => $jordan->id, 'name' => 'جامعة الأردن']);
        $dubaiUni = LookupUniversity::create(['country_id' => $uae->id, 'name' => 'جامعة دبي']);
        $ksu = LookupUniversity::create(['country_id' => $saudi->id, 'name' => 'جامعة الملك سعود']);

        // 5. Attachment Types (المرفقات المدمجة المستندة من الطالب/الجامعة)
        $att1 = LookupAttachmentType::create(['name' => 'الهوية الشخصية / جواز السفر']);
        $att2 = LookupAttachmentType::create(['name' => 'مصدقة الإجازة الجامعية الأولى']);
        $att3 = LookupAttachmentType::create(['name' => 'مصدقة الماجستير / الدكتوراه']);
        $att4 = LookupAttachmentType::create(['name' => 'إيصال الدفع المالي الرسمي']);
        $att5 = LookupAttachmentType::create(['name' => 'شهادة المهارات الحاسوبية ICDL']);
        $att6 = LookupAttachmentType::create(['name' => 'شهادة الكفاءة اللغوية (اللغة)']);
        $att7 = LookupAttachmentType::create(['name' => 'كشف العلامات التفصيلي']);
        $att8 = LookupAttachmentType::create(['name' => 'صفحات جواز السفر وأختام الإقامة']);

        // 6. Users
        $adminUser1 = User::create([
            'role_id' => $adminRole->id,
            'name' => 'admin1',
            'email' => 'admin@mohe.gov.sy',
            'password' => Hash::make('123456'),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        $adminUser2 = User::create([
            'role_id' => $adminRole->id,
            'name' => 'sysadmin',
            'email' => 'sysadmin@mohe.gov.sy',
            'password' => Hash::make('system'),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        // University Users
        $uniDamascusUser = User::create([
            'role_id' => $uniRole->id,
            'university_id' => $damascus->id,
            'name' => 'uni_damascus',
            'email' => 'damascus@uni.edu.sy',
            'password' => Hash::make('123456'),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        $uniArabUser = User::create([
            'role_id' => $uniRole->id,
            'university_id' => $arabInt->id,
            'name' => 'uni_arab',
            'email' => 'aiu@uni.edu.sy',
            'password' => Hash::make('123456'),
            'is_active' => true,
            'card_status' => 'yellow_card',
        ]);

        // 7. Site Settings
        SiteSetting::set('site_locked', '0');
        SiteSetting::set('site_notice', 'أهلاً بكم في نظام إدارة وقرارات معادلة الشهادات - وزارة التعليم العالي والبحث العلمي');

        // 8. Equivalence Applications & Profiles
        // Profile 1: أحمد محمود الخالد (Matching screenshot 1 & 2 exact details)
        $p1 = EquivalenceProfile::create([
            'full_name' => 'أحمد محمود الخالد',
            'national_id' => '01010101010',
            'dob' => '1995-01-01',
            'job_title' => 'موظف',
            'nationality_id' => $syria->id,
            'phone' => '0112223344',
            'mobile' => '0933112233',
            'email' => 'ahmad@test.com',
            'address' => 'دمشق - المزة',
            'gender' => 'ذكر',
            'is_syrian' => true,
        ]);

        $app1 = Application::create([
            'candidate_id' => $p1->id,
            'application_no' => '101',
            'request_type' => 'تعادل',
            'work_university_id' => $damascus->id,
            'work_faculty' => 'كلية الهندسة المدنية',
            'work_department' => 'الرياضيات والفيزياء',
            'study_system' => 'ساعات معتمدة',
            'has_previous_degree' => true,
            'status' => 'قيد الدراسة',
            'user_id' => $uniDamascusUser->id,
            'created_at' => '2026-01-10 12:00:00',
        ]);

        // Required Courses for App 1
        ApplicationCourse::create([
            'application_id' => $app1->id,
            'faculty' => 'كلية الهندسة المدنية',
            'department' => 'الرياضيات والفيزياء',
            'course_name' => 'رياضيات 1',
            'course_status' => 'ناجح',
            'status_date' => '2026-01-20',
        ]);

        // Educations for App 1: Secondary, Bachelor, Higher Diploma, Master, PhD
        $edHigh = Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l6->id,
            'country_id' => $syria->id,
            'section_name' => 'علمي',
            'grant_date' => '2013-06-15',
        ]);

        $edBach = Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l1->id,
            'country_id' => $syria->id,
            'university_id' => $damascus->id,
            'general_specialization' => 'هندسة مدنية',
            'exact_specialization' => 'إنشاءات',
            'registration_date' => '2013-09-01',
            'grant_date' => '2018-07-01',
            'rank' => 'جيد',
        ]);

        $edDip = Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l2->id,
            'country_id' => $syria->id,
            'university_id' => $damascus->id,
            'general_specialization' => 'دراسات عليا',
            'exact_specialization' => 'إدارة مشاريع',
            'registration_date' => '2018-10-01',
            'grant_date' => '2020-07-01',
            'rank' => 'جيد',
            'notes' => 'لا توجد',
        ]);

        $edMast = Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l3->id,
            'country_id' => $syria->id,
            'university_id' => $damascus->id,
            'general_specialization' => 'هندسة مدنية',
            'exact_specialization' => 'إدارة مشاريع',
            'registration_date' => '2018-09-01',
            'defense_date' => '2021-06-01',
            'grant_date' => '2021-06-10',
            'rank' => 'امتياز',
            'supervisor_name' => 'د. أحمد',
            'thesis_title' => 'بحث في إدارة الجرائم / الإنشاءات المعدنية',
            'envoy_decision' => 'RES-10',
            'envoy_date' => '2019-09-01',
            'experience_from_year' => 3,
            'experience_to_year' => 2021,
        ]);

        // Residence for Master
        EducationResidence::create([
            'education_id' => $edMast->id,
            'page_number' => '22',
            'exit_airport' => 'مطار دمشق الدولي',
            'exit_date' => '2021-06-12',
            'entry_airport' => 'مطار دمشق الدولي',
            'entry_date' => '2019-09-15',
            'stamp_details' => 'إقامة سنتان في بلد الدراسة',
        ]);

        $edPhD = Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l4->id,
            'country_id' => $egypt->id,
            'university_id' => $cairoUni->id,
            'general_specialization' => 'ميكانيك',
            'exact_specialization' => 'طاقة',
            'registration_date' => '2015-09-01',
            'defense_date' => '2019-05-15',
            'grant_date' => '2019-06-01',
            'rank' => 'جيد جداً',
            'supervisor_name' => 'أ.د. حسام',
            'thesis_title' => 'طاقة الرياح وتقييم الأداء',
        ]);

        EducationResidence::create([
            'education_id' => $edPhD->id,
            'page_number' => '39',
            'exit_airport' => 'مطار دمشق الدولي',
            'exit_date' => '2019-06-10',
            'entry_airport' => 'مطار دمشق الدولي',
            'entry_date' => '2015-08-15',
            'stamp_details' => 'إقامة 4 سنوات',
        ]);

        // Attachments for candidate #1 (الهوية، الشهادة، إيصال الدفع، ICDL، اللغة، الخ)
        EducationAttachment::create([
            'education_id' => $edBach->id,
            'attachment_type_id' => $att1->id,
            'file_path' => 'attachments/sample_national_id.pdf',
            'notes' => 'صورة الهوية الشخصية الموثوقة',
        ]);

        EducationAttachment::create([
            'education_id' => $edBach->id,
            'attachment_type_id' => $att2->id,
            'file_path' => 'attachments/sample_bachelor_degree.pdf',
            'notes' => 'مصدقة الإجازة الجامعية في الهندسة المدنية',
        ]);

        EducationAttachment::create([
            'education_id' => $edBach->id,
            'attachment_type_id' => $att4->id,
            'file_path' => 'attachments/sample_payment_receipt.pdf',
            'notes' => 'إيصال سداد رسوم معادلة الشهادة بمبلغ 50,000 ل.س',
        ]);

        EducationAttachment::create([
            'education_id' => $edBach->id,
            'attachment_type_id' => $att5->id,
            'file_path' => 'attachments/sample_icdl_cert.pdf',
            'notes' => 'شهادة قيادة الحاسوب الدولية ICDL المعتمدة',
        ]);

        EducationAttachment::create([
            'education_id' => $edBach->id,
            'attachment_type_id' => $att6->id,
            'file_path' => 'attachments/sample_language_cert.pdf',
            'notes' => 'شهادة اختبار الكفاءة اللغوية باللغة الإنكليزية',
        ]);

        EducationAttachment::create([
            'education_id' => $edMast->id,
            'attachment_type_id' => $att3->id,
            'file_path' => 'attachments/sample_master_degree.pdf',
            'notes' => 'مصدقة شهادة الماجستير في إدارة المشاريع',
        ]);


        // More Applications matching table in screenshots
        // App 2: فاطمة أحمد - جامعة حلب - إدارة جامعة - مقبول مبدئياً
        $p2 = EquivalenceProfile::create([
            'full_name' => 'فاطمة أحمد المحمود',
            'national_id' => '02020202020',
            'mobile' => '0944112233',
            'email' => 'fatima@aleppo.edu.sy',
            'gender' => 'أنثى',
        ]);
        $app2 = Application::create([
            'candidate_id' => $p2->id,
            'application_no' => '102',
            'request_type' => 'تعادل',
            'work_university_id' => $aleppo->id,
            'work_faculty' => 'كلية الاقتصاد',
            'status' => 'مقبول مبدئياً',
        ]);

        // App 3: طارق العلي - جامعة دمشق - ماجستير - مرفوض
        $p3 = EquivalenceProfile::create(['full_name' => 'طارق العلي', 'national_id' => '03030303030']);
        $app3 = Application::create([
            'candidate_id' => $p3->id,
            'application_no' => '103',
            'request_type' => 'تعادل',
            'work_university_id' => $damascus->id,
            'status' => 'مرفوض',
        ]);

        // App 4: سامر إبراهيم - جامعة حلب - دكتوراه - بانتظار الوثائق
        $p4 = EquivalenceProfile::create(['full_name' => 'سامر إبراهيم', 'national_id' => '04040404040']);
        $app4 = Application::create([
            'candidate_id' => $p4->id,
            'application_no' => '104',
            'request_type' => 'تعادل',
            'work_university_id' => $aleppo->id,
            'status' => 'بانتظار الوثائق',
        ]);

        // App 5: ريم حسن - جامعة تشرين - ماجستير - مكتمل الأوراق
        $p5 = EquivalenceProfile::create(['full_name' => 'ريم حسن', 'national_id' => '05050505050']);
        $app5 = Application::create([
            'candidate_id' => $p5->id,
            'application_no' => '105',
            'request_type' => 'تعادل',
            'work_university_id' => $tishreen->id,
            'status' => 'مكتمل الأوراق',
        ]);

        // App 6: خالد عمر - جامعة البعث - دكتوراه - تحت التدقيق الأمني
        $p6 = EquivalenceProfile::create(['full_name' => 'خالد عمر', 'national_id' => '06060606060']);
        $app6 = Application::create([
            'candidate_id' => $p6->id,
            'application_no' => '106',
            'request_type' => 'تعادل',
            'work_university_id' => $baath->id,
            'status' => 'تحت التدقيق الأمني',
        ]);

        // App 7: نور الزين - جامعة دمشق - إدارة جامعة - تم الصدور
        $p7 = EquivalenceProfile::create(['full_name' => 'نور الزين', 'national_id' => '07070707070']);
        $app7 = Application::create([
            'candidate_id' => $p7->id,
            'application_no' => '107',
            'request_type' => 'تعادل',
            'work_university_id' => $damascus->id,
            'status' => 'تم الصدور',
        ]);

        // App 8: محمد الناصر - جامعة البعث - دكتوراه - معلق (لشاشة اللجنة العامة!)
        $p8 = EquivalenceProfile::create(['full_name' => 'محمد الناصر', 'national_id' => '08080808080']);
        $app8 = Application::create([
            'candidate_id' => $p8->id,
            'application_no' => '108',
            'request_type' => 'تعادل',
            'work_university_id' => $baath->id,
            'work_faculty' => 'كلية العلوم',
            'status' => 'معلق',
        ]);
        Education::create([
            'application_id' => $app8->id,
            'education_level_id' => $l4->id,
            'general_specialization' => 'فيزياء مالية',
            'rank' => 'جيد جداً',
            'thesis_title' => 'دراسة فيزياء الجسيمات الأولية',
        ]);

        // App 9: سارة المصري - جامعة حلب - دكتوراه - مؤجل
        $p9 = EquivalenceProfile::create(['full_name' => 'سارة المصري', 'national_id' => '09090909090']);
        $app9 = Application::create([
            'candidate_id' => $p9->id,
            'application_no' => '109',
            'request_type' => 'تعادل',
            'work_university_id' => $aleppo->id,
            'status' => 'مؤجل',
        ]);

        // App 10: عمر الخطيب - جامعة تشرين - ماجستير - منتهي
        $p10 = EquivalenceProfile::create(['full_name' => 'عمر الخطيب', 'national_id' => '10101010101']);
        $app10 = Application::create([
            'candidate_id' => $p10->id,
            'application_no' => '110',
            'request_type' => 'تعادل',
            'work_university_id' => $tishreen->id,
            'status' => 'منتهي',
        ]);

        // App 11: طارق زياد - جامعة دمشق - إجازة جامعية - قيد الدراسة (تعادل لأول مرة)
        $p11 = EquivalenceProfile::create(['full_name' => 'طارق زياد', 'national_id' => '11111111111']);
        $app11 = Application::create([
            'candidate_id' => $p11->id,
            'application_no' => '1011',
            'request_type' => 'تعادل لأول مرة',
            'work_university_id' => $damascus->id,
            'status' => 'قيد الدراسة',
        ]);
    }
}

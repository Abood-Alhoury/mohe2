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
        $jordan = LookupCountry::create(['name' => 'الأردن']);

        // 3. Education Levels
        $l1 = LookupEducationLevel::create(['name' => 'إجازة جامعية']);
        $l2 = LookupEducationLevel::create(['name' => 'ماجستير']);
        $l3 = LookupEducationLevel::create(['name' => 'دكتوراه']);
        $l4 = LookupEducationLevel::create(['name' => 'ثانوية عامة']);

        // 4. Universities
        $damascus = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة دمشق']);
        $aleppo = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة حلب']);
        $tishreen = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة تشرين']);
        $baath = LookupUniversity::create(['country_id' => $syria->id, 'name' => 'جامعة البعث']);

        // 5. Attachment Types
        LookupAttachmentType::create(['name' => 'نسخة مصدقة عن الشهادة الثانوية']);
        LookupAttachmentType::create(['name' => 'نسخة مصدقة عن الإجازة الجامعية الأولى']);
        LookupAttachmentType::create(['name' => 'نسخة مصدقة عن شهادة الماجستير السورية']);
        LookupAttachmentType::create(['name' => 'وثيقة تواريخ التسجيل والمناقشة والمنح']);
        LookupAttachmentType::create(['name' => 'ملخص باللغة العربية عن الأطروحة']);
        LookupAttachmentType::create(['name' => 'إيصال تسديد رسم تعادل 100,000 ل.س']);

        // 6. Accounts
        $adminUser = User::create([
            'role_id' => $adminRole->id,
            'name' => 'admin1',
            'email' => 'admin@mohe.gov.sy',
            'password' => Hash::make('123456'),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        $uniDamascusUser = User::create([
            'role_id' => $uniRole->id,
            'university_id' => $damascus->id,
            'name' => 'uni_damascus',
            'email' => 'damascus@uni.edu.sy',
            'password' => Hash::make('123456'),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        // 7. Site Settings
        SiteSetting::set('site_locked', '0');
        SiteSetting::set('site_notice', 'أهلاً بكم في نظام إدارة وقرارات معادلة الشهادات - وزارة التعليم العالي والبحث العلمي');

        // ================= APPLICATION 1 =================
        // طالب 1: تعادل لأول مرة - ماجستير سوري - جامعة دمشق (تحت التدقيق الأولي)
        $p1 = EquivalenceProfile::create([
            'full_name' => 'أحمد محمود الخالد',
            'father_name' => 'محمود',
            'mother_name' => 'سميرة المصطفى',
            'national_id' => '01010101010',
            'dob' => '1995-03-15',
            'job_title' => 'مهندس مدني / معيد مرشح',
            'nationality_id' => $syria->id,
            'phone' => '0112223344',
            'mobile' => '0933112233',
            'email' => 'ahmad.khaled@damascus.edu.sy',
            'address' => 'دمشق - المزة - شارع الفيلات الشرقية',
            'gender' => 'ذكر',
        ]);

        $app1 = Application::create([
            'candidate_id' => $p1->id,
            'user_id' => $uniDamascusUser->id,
            'application_no' => '101',
            'request_type' => 'تعادل لأول مرة - ماجستير سوري',
            'work_university_id' => $damascus->id,
            'work_faculty' => 'كلية الهندسة المدنية',
            'work_department' => 'إدارة التشييد والمشاريع',
            'status' => 'تحت التدقيق الأولي',
            'created_at' => now(),
        ]);

        // Education Details App 1
        Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l1->id, // إجازة
            'university_id' => $damascus->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الهندسة المدنية',
            'general_specialization' => 'الهندسة المدنية',
            'department' => 'الإنشاءات',
            'registration_date' => '2013-09-01',
            'graduation_date' => '2018-06-30',
            'rank' => 'جيد جداً',
        ]);

        Education::create([
            'application_id' => $app1->id,
            'education_level_id' => $l2->id, // ماجستير سوري
            'university_id' => $damascus->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الهندسة المدنية',
            'general_specialization' => 'إدارة التشييد والهندسة الإدارية',
            'department' => 'إدارة المشاريع',
            'registration_date' => '2019-10-01',
            'defense_date' => '2022-04-15',
            'graduation_date' => '2022-05-20',
            'rank' => 'امتياز',
            'supervisor' => 'الأستاذ الدكتور محمد سعيد الحكيم',
            'thesis_title' => 'نمذجة وإدارة المخاطر في المشاريع الهندسية باستخدام تقنيات الذكاء الاصطناعي',
        ]);

        // Courses App 1
        ApplicationCourse::create([
            'application_id' => $app1->id,
            'course_name' => 'إدارة المشاريع الهندسية Advanced Project Management',
            'faculty' => 'كلية الهندسة المدنية',
            'department' => 'إدارة التشييد',
        ]);
        ApplicationCourse::create([
            'application_id' => $app1->id,
            'course_name' => 'تحليل وتحكم المخاطر Risk Analysis Control',
            'faculty' => 'كلية الهندسة المدنية',
            'department' => 'إدارة التشييد',
        ]);

        // ================= APPLICATION 2 =================
        // طالب 2: تعادل لأول مرة - ماجستير سوري - جامعة حلب (بانتظار الوثائق)
        $p2 = EquivalenceProfile::create([
            'full_name' => 'د. سارة محمود الحسن',
            'father_name' => 'محمود',
            'mother_name' => 'منى العلي',
            'national_id' => '02020202020',
            'dob' => '1996-07-20',
            'job_title' => 'مهندسة برمجيات / محاضرة',
            'nationality_id' => $syria->id,
            'phone' => '0214445566',
            'mobile' => '0944112233',
            'email' => 'sara.hassan@aleppo.edu.sy',
            'address' => 'حلب - الشهباء الجديدة',
            'gender' => 'أنثى',
        ]);

        $app2 = Application::create([
            'candidate_id' => $p2->id,
            'user_id' => $uniDamascusUser->id,
            'application_no' => '102',
            'request_type' => 'تعادل لأول مرة - ماجستير سوري',
            'work_university_id' => $aleppo->id,
            'work_faculty' => 'كلية الهندسة المعلوماتية',
            'work_department' => 'هندسة البرمجيات ونظم المعلومات',
            'status' => 'بانتظار الوثائق',
            'created_at' => now(),
        ]);

        Education::create([
            'application_id' => $app2->id,
            'education_level_id' => $l1->id,
            'university_id' => $aleppo->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الهندسة المعلوماتية',
            'general_specialization' => 'هندسة البرمجيات',
            'department' => 'علوم الحاسب',
            'registration_date' => '2014-09-01',
            'graduation_date' => '2019-07-10',
            'rank' => 'امتياز',
        ]);

        Education::create([
            'application_id' => $app2->id,
            'education_level_id' => $l2->id,
            'university_id' => $aleppo->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الهندسة المعلوماتية',
            'general_specialization' => 'الذكاء الاصطناعي وتنظيم البيانات',
            'department' => 'هندسة البرمجيات',
            'registration_date' => '2019-11-01',
            'defense_date' => '2022-09-10',
            'graduation_date' => '2022-10-15',
            'rank' => 'امتياز',
            'supervisor' => 'الأستاذ الدكتور فاروق الجابي',
            'thesis_title' => 'تطوير خوارزميات التعلم العميق في معالجة اللغات الطبيعية للغة العربية',
        ]);

        ApplicationCourse::create([
            'application_id' => $app2->id,
            'course_name' => 'هندسة البرمجيات المتقدمة',
            'faculty' => 'كلية الهندسة المعلوماتية',
            'department' => 'قسم البرمجيات',
        ]);

        // ================= APPLICATION 3 =================
        // طالب 3: تعادل لأول مرة - ماجستير سوري - جامعة تشرين (معلق - يظهر في شاشة اللجنة العامة!)
        $p3 = EquivalenceProfile::create([
            'full_name' => 'د. ماهر سليمان الخالد',
            'father_name' => 'سليمان',
            'mother_name' => 'خديجة عثمان',
            'national_id' => '03030303030',
            'dob' => '1993-11-12',
            'job_title' => 'مدرس علوم مالية ومصرفية',
            'nationality_id' => $syria->id,
            'phone' => '0413332211',
            'mobile' => '0955667788',
            'email' => 'maher.khaled@tishreen.edu.sy',
            'address' => 'اللاذقية - مشروع الصليبة',
            'gender' => 'ذكر',
        ]);

        $app3 = Application::create([
            'candidate_id' => $p3->id,
            'user_id' => $uniDamascusUser->id,
            'application_no' => '103',
            'request_type' => 'تعادل لأول مرة - ماجستير سوري',
            'work_university_id' => $tishreen->id,
            'work_faculty' => 'كلية الاقتصاد',
            'work_department' => 'إدارة الأعمال والعلوم المالية',
            'status' => 'معلق',
            'created_at' => now(),
        ]);

        Education::create([
            'application_id' => $app3->id,
            'education_level_id' => $l1->id,
            'university_id' => $tishreen->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الاقتصاد',
            'general_specialization' => 'إدارة الأعمال',
            'department' => 'العلوم المالية والمصرفية',
            'registration_date' => '2012-09-01',
            'graduation_date' => '2016-06-25',
            'rank' => 'جيد جداً',
        ]);

        Education::create([
            'application_id' => $app3->id,
            'education_level_id' => $l2->id,
            'university_id' => $tishreen->id,
            'country_id' => $syria->id,
            'faculty' => 'كلية الاقتصاد',
            'general_specialization' => 'الحوكمة والتحليل المالي للشركات',
            'department' => 'إدارة الأعمال',
            'registration_date' => '2017-10-01',
            'defense_date' => '2020-03-20',
            'graduation_date' => '2020-05-10',
            'rank' => 'امتياز',
            'supervisor' => 'الأستاذ الدكتور سامي عبد الله',
            'thesis_title' => 'أثر تطبيق معايير الحوكمة المؤسسية على الأداء المالي للشركات المساهمة السورية',
        ]);

        ApplicationCourse::create([
            'application_id' => $app3->id,
            'course_name' => 'التحليل المالي المتقدم والتقويم',
            'faculty' => 'كلية الاقتصاد',
            'department' => 'إدارة الأعمال',
        ]);
    }
}

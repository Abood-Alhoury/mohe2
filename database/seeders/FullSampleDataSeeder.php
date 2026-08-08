<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FullSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or Find Aleppo University
        $syCountry = DB::table('lookup_countries')->where('name', 'like', '%سوري%')->value('id');
        if (!$syCountry) {
            $syCountry = DB::table('lookup_countries')->insertGetId(['name' => 'سوريا', 'created_at' => now(), 'updated_at' => now()]);
        }

        $egCountry = DB::table('lookup_countries')->where('name', 'like', '%مصر%')->value('id');
        if (!$egCountry) {
            $egCountry = DB::table('lookup_countries')->insertGetId(['name' => 'مصر', 'created_at' => now(), 'updated_at' => now()]);
        }

        $uniAleppo = DB::table('lookup_universities')->where('name', 'like', '%حلب%')->value('id');
        if (!$uniAleppo) {
            $uniAleppo = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة حلب',
                'country_id' => $syCountry,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $uniCairo = DB::table('lookup_universities')->where('name', 'like', '%القاهرة%')->value('id');
        if (!$uniCairo) {
            $uniCairo = DB::table('lookup_universities')->insertGetId([
                'name' => 'جامعة القاهرة',
                'country_id' => $egCountry,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Delete existing sample candidate if present
        $existingProfile = DB::table('equivalence_profiles')->where('national_id', '02020202099')->first();
        if ($existingProfile) {
            DB::table('applications')->where('candidate_id', $existingProfile->id)->delete();
            DB::table('equivalence_profiles')->where('id', $existingProfile->id)->delete();
        }

        // 2. Candidate Profile
        $candidateId = DB::table('equivalence_profiles')->insertGetId([
            'full_name'    => 'د. سارة محمود الخالد',
            'national_id'  => '02020202099',
            'dob'          => '1996-07-20',
            'gender'       => 'أنثى',
            'is_syrian'    => true,
            'job_title'    => 'مهندسة برمجيات / محاضرة',
            'phone'        => '0214445566',
            'mobile'       => '0944112233',
            'email'        => 'sara.hassan@aleppo.edu.sy',
            'address'      => 'حلب - الشهباء الجديدة',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // 3. Application
        $appId = DB::table('applications')->insertGetId([
            'candidate_id'       => $candidateId,
            'application_no'     => '2026/MOHE-101',
            'request_type'       => 'تعادل أول مرة - ماجستير ودكتوراه',
            'work_university_id' => $uniAleppo,
            'work_faculty'       => 'كلية الهندسة المعلوماتية',
            'work_department'    => 'قسم هندسة البرمجيات ونظم المعلومات',
            'study_system'       => 'انتظام كامل ومطابق لتعليمات مجلس التعليم العالي',
            'has_previous_degree'=> true,
            'status'             => 'قيد الدراسة',
            'user_id'            => 1,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 4. Education Levels
        $levelSecondary = DB::table('lookup_education_levels')->where('name', 'ثانوية عامة')->value('id') ?? 1;
        $levelBachelor  = DB::table('lookup_education_levels')->where('name', 'إجازة جامعية')->value('id') ?? 2;
        $levelMaster    = DB::table('lookup_education_levels')->where('name', 'ماجستير')->value('id') ?? 3;
        $levelPhd       = DB::table('lookup_education_levels')->where('name', 'دكتوراه')->value('id') ?? 4;

        // 4.1 الشهادة الثانوية
        DB::table('educations')->insert([
            'application_id'     => $appId,
            'education_level_id' => $levelSecondary,
            'country_id'         => $syCountry,
            'university_id'      => null,
            'section_name'       => 'علمي (المجموع: 238/240)',
            'grant_date'         => '2014-06-15',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 4.2 الإجازة الجامعية
        DB::table('educations')->insert([
            'application_id'      => $appId,
            'education_level_id'  => $levelBachelor,
            'country_id'          => $syCountry,
            'university_id'       => $uniAleppo,
            'general_specialization' => 'هندسة البرمجيات',
            'exact_specialization'=> 'نظم المعلومات والذكاء الاصطناعي',
            'registration_date'   => '2014-09-01',
            'grant_date'          => '2019-07-01',
            'rank'                => 'امتياز مع مرتبة الشرف (86.4%)',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // 4.3 ماجستير سوري
        $masterId = DB::table('educations')->insertGetId([
            'application_id'      => $appId,
            'education_level_id'  => $levelMaster,
            'country_id'          => $syCountry,
            'university_id'       => $uniAleppo,
            'general_specialization' => 'الذكاء الاصطناعي وتنظيم البيانات',
            'exact_specialization'=> 'معالجة اللغات الطبيعية للغة العربية',
            'registration_date'   => '2019-11-01',
            'grant_date'          => '2022-06-15',
            'rank'                => 'امتياز (91%)',
            'supervisor_name'     => 'أ.د. عبد الرحمن الحكيم',
            'thesis_title'        => 'تطوير خوارزميات التعلم العميق في معالجة اللغات الطبيعية للغة العربية',
            'envoy_decision'      => 'قرار مجلس الكلية رقم 45/ت',
            'envoy_date'          => '2019-10-15',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        DB::table('education_residences')->insert([
            [
                'education_id' => $masterId,
                'entry_date'   => '2020-01-10',
                'entry_airport'=> 'مطار حلب الدولي',
                'exit_date'    => '2022-05-20',
                'exit_airport' => 'مطار حلب الدولي',
                'page_number'  => 18,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        ]);

        // 4.4 دكتوراه غير سورية (مصر)
        $phdId = DB::table('educations')->insertGetId([
            'application_id'      => $appId,
            'education_level_id'  => $levelPhd,
            'country_id'          => $egCountry,
            'university_id'       => $uniCairo,
            'general_specialization' => 'هندسة البرمجيات والأنظمة الذكية',
            'exact_specialization'=> 'الشبكات العصبية العميقة والرؤية الحاسوبية',
            'registration_date'   => '2022-09-01',
            'defense_date'        => '2025-10-15',
            'grant_date'          => '2025-11-30',
            'rank'                => 'مرتبة الشرف الأولى مع التوصية بالطبع',
            'supervisor_name'     => 'أ.د. مصطفى عبد السلام',
            'thesis_title'        => 'نمذجة الشبكات العصبية العميقة للتحليل الدلالي والتنبؤ في الأنظمة الذكية المستقلة',
            'envoy_decision'      => 'قرار الإيفاد الوزاري رقم 102/د',
            'envoy_date'          => '2022-08-20',
            'notes'               => 'تم نشر 3 بحوث علمية في مجلات محكّمة ذات معامل تأثير عالي (Scopus Q1)',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        DB::table('education_residences')->insert([
            [
                'education_id' => $phdId,
                'entry_date'   => '2022-10-01',
                'entry_airport'=> 'مطار القاهرة الدولي',
                'exit_date'    => '2024-06-30',
                'exit_airport' => 'مطار القاهرة الدولي',
                'page_number'  => 32,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'education_id' => $phdId,
                'entry_date'   => '2024-09-01',
                'entry_airport'=> 'مطار القاهرة الدولي',
                'exit_date'    => '2025-11-15',
                'exit_airport' => 'مطار القاهرة الدولي',
                'page_number'  => 44,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        ]);

        // 5. Courses
        DB::table('application_courses')->insert([
            [
                'application_id' => $appId,
                'course_name'    => 'هندسة البرمجيات المتقدمة',
                'department'     => 'قسم هندسة البرمجيات',
                'faculty'        => 'كلية الهندسة المعلوماتية',
                'course_status'  => 'ناجح',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'application_id' => $appId,
                'course_name'    => 'نظم البرمجيات الموزعة والذكية',
                'department'     => 'قسم هندسة البرمجيات',
                'faculty'        => 'كلية الهندسة المعلوماتية',
                'course_status'  => 'ناجح',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        ]);

        $this->command->info('✅ Created Candidate "د. سارة محمود الخالد" with complete details! App ID: ' . $appId);
    }
}

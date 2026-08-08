<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\EquivalenceProfile;
use App\Models\Application;
use App\Models\ApplicationCourse;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\ApplicationMessage;

class ApplicationWizardController extends Controller
{
    public function showOptions()
    {
        // Get unread notifications for header
        $notifications = $this->getUnreadNotifications();
        return view('university.apply.options', compact('notifications'));
    }

    public function showSyrianMastersWizard()
    {
        $countries = LookupCountry::orderBy('name', 'asc')->get();
        
        // Find Syria country model
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        // Universities (group by country if needed, or get all)
        $universities = LookupUniversity::with('country')->orderBy('name', 'asc')->get();
        $educationLevels = LookupEducationLevel::all();

        // Get unread notifications for header
        $notifications = $this->getUnreadNotifications();

        return view('university.apply.syrian_masters', compact(
            'countries', 
            'universities', 
            'educationLevels', 
            'syriaId',
            'notifications'
        ));
    }

    public function submitSyrianMastersWizard(Request $request)
    {
        // 1. Validation of all sections
        $validated = $request->validate([
            // Step 1: Personal Info
            'full_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'nationality_id' => 'required|exists:lookup_countries,id',
            'national_id' => 'required|string|max:50',
            'dob' => 'required|date',
            'job_title' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'gender' => 'required|string|in:ذكر,أنثى',
            'is_syrian' => 'required|boolean',

            // Step 2: High School Info
            'hs_country_id' => 'required|exists:lookup_countries,id',
            'hs_type' => 'required|string|in:علمي,أدبي,تجاري,صناعي',
            'hs_grant_date' => 'required|date',
            'hs_decision_no' => 'nullable|string|max:100',
            'hs_decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Step 3: Bachelor's Degree Info
            'ba_country_id' => 'required|exists:lookup_countries,id',
            'ba_university_id' => 'nullable|exists:lookup_universities,id',
            'ba_university_other' => 'nullable|string|max:255',
            'ba_faculty' => 'required|string|max:255',
            'ba_department' => 'required|string|max:255',
            'ba_registration_date' => 'required|date',
            'ba_grant_date' => 'required|date',
            'ba_rank' => 'required|string|max:100',
            'ba_decision_no' => 'nullable|string|max:100',
            'ba_decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Step 4: Syrian Master's Degree Info
            'ma_university_id' => 'required|exists:lookup_universities,id',
            'ma_faculty' => 'required|string|max:255',
            'ma_department' => 'required|string|max:255',
            'ma_registration_date' => 'required|date',
            'ma_defense_date' => 'required|date',
            'ma_grant_date' => 'required|date',
            'ma_rank' => 'required|string|max:100',
            'ma_supervisor' => 'required|string|max:255',
            'ma_thesis_title' => 'required|string',
            
            // Experience (Optional/Required if more than 2 years exist)
            'has_experience' => 'nullable|boolean',
            'exp_place' => 'nullable|required_if:has_experience,1|string|max:255',
            'exp_from_year' => 'nullable|required_if:has_experience,1|integer|min:1900|max:2099',
            'exp_to_year' => 'nullable|required_if:has_experience,1|integer|min:1900|max:2099',

            // Step 5: University Request & Courses Details
            'req_no' => 'required|string|max:100',
            'req_date' => 'required|date',
            'courses' => 'required|array|min:1',
            'courses.*.name' => 'required|string|max:255',
            'courses.*.faculty' => 'required|string|max:255',
            'courses.*.department' => 'required|string|max:255',

            // Step 6: Final Attachments Upload
            'file_uni_request' => 'required|file|mimes:pdf|max:10240',
            'file_hs_cert' => 'required|file|mimes:pdf|max:10240',
            'file_ba_cert' => 'required|file|mimes:pdf|max:10240',
            'file_ma_cert' => 'required|file|mimes:pdf|max:10240',
            'file_ma_dates' => 'required|file|mimes:pdf|max:10240',
            'file_thesis_summary' => 'required|file|mimes:pdf|max:10240',
            'file_exp_cert' => 'nullable|file|mimes:pdf|max:10240',
            'file_contracts' => 'nullable|file|mimes:pdf|max:10240',
            'file_lang_icdl' => 'required|file|mimes:pdf|max:10240',
            'file_cv' => 'required|file|mimes:pdf|max:10240',
            'file_payment' => 'required|file|mimes:pdf|max:10240',
        ]);

        // 2. Save Equivalence Profile (Candidate)
        $profile = EquivalenceProfile::updateOrCreate(
            ['national_id' => $request->national_id],
            [
                'full_name' => $request->full_name,
                'dob' => $request->dob,
                'job_title' => $request->job_title,
                'nationality_id' => $request->nationality_id,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'gender' => $request->gender,
                'is_syrian' => $request->is_syrian,
            ]
        );

        // 3. Save Application
        $uniId = Auth::user()->university_id;
        $appNo = 'MA-SY-' . rand(100000, 999999); // Generate a unique application number

        $frequency = $request->input('equivalence_frequency', 'تعادل للمرة الأولى');
        $requestType = $frequency . ' - ماجستير سوري';

        $application = Application::create([
            'candidate_id' => $profile->id,
            'application_no' => $appNo,
            'request_type' => $requestType,
            'work_university_id' => $uniId,
            'work_faculty' => $request->ma_faculty,
            'work_department' => $request->ma_department,
            'study_system' => 'فصلي',
            'has_previous_degree' => $request->has_experience ? true : false,
            'status' => 'قيد الدراسة',
            'user_id' => Auth::id(),
        ]);

        // 4. Save Courses
        foreach ($request->courses as $course) {
            ApplicationCourse::create([
                'application_id' => $application->id,
                'faculty' => $course['faculty'],
                'department' => $course['department'],
                'course_name' => $course['name'],
                'course_status' => 'مطلوب تدريسه',
            ]);
        }

        // Get Education Levels from DB
        $lvlHS = LookupEducationLevel::where('name', 'like', '%ثانوية%')->first();
        $lvlBA = LookupEducationLevel::where('name', 'like', '%إجازة%')->first();
        $lvlMA = LookupEducationLevel::where('name', 'like', '%ماجستير%')->first();

        $hsLevelId = $lvlHS ? $lvlHS->id : 6;
        $baLevelId = $lvlBA ? $lvlBA->id : 1;
        $maLevelId = $lvlMA ? $lvlMA->id : 3;

        // 5. Create Educations records
        // A. High School Education
        $edHS = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $hsLevelId,
            'country_id' => $request->hs_country_id,
            'section_name' => $request->hs_type,
            'grant_date' => $request->hs_grant_date,
            'notes' => $request->hs_decision_no ? 'رقم قرار المعادلة الثانوية: ' . $request->hs_decision_no : null,
        ]);

        // B. Bachelor's Education
        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $request->ba_university_id,
            'section_name' => $request->ba_university_other,
            'general_specialization' => $request->ba_faculty,
            'exact_specialization' => $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => $request->ba_decision_no ? 'رقم قرار معادلة الإجازة: ' . $request->ba_decision_no : null,
        ]);

        // C. Master's Education
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaCountryId = $syriaCountry ? $syriaCountry->id : $request->nationality_id;

        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $syriaCountryId,
            'university_id' => $request->ma_university_id,
            'general_specialization' => $request->ma_faculty,
            'exact_specialization' => $request->ma_department,
            'registration_date' => $request->ma_registration_date,
            'defense_date' => $request->ma_defense_date,
            'grant_date' => $request->ma_grant_date,
            'rank' => $request->ma_rank,
            'supervisor_name' => $request->ma_supervisor,
            'thesis_title' => $request->ma_thesis_title,
            'experience_from_year' => $request->exp_from_year,
            'experience_to_year' => $request->exp_to_year,
            'notes' => $request->exp_place ? 'مكان الخبرة التدريسية: ' . $request->exp_place : null,
        ]);

        // 6. Handle File Uploads & Attachments
        // Storage Directory: public/attachments/{app_id}
        $folder = 'attachments/' . $application->id;

        // Helper function for uploading and creating attachment
        $uploadAndAttach = function($fileKey, $educationId, $typeId, $notes = null) use ($request, $folder) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store($folder, 'public');
                EducationAttachment::create([
                    'education_id' => $educationId,
                    'attachment_type_id' => $typeId,
                    'file_path' => $path,
                    'notes' => $notes,
                ]);
            }
        };

        // High School Attachments
        $uploadAndAttach('file_hs_cert', $edHS->id, 1, 'شهادة الدراسة الثانوية');
        if ($request->hasFile('hs_decision_file')) {
            $uploadAndAttach('hs_decision_file', $edHS->id, 1, 'قرار معادلة الشهادة الثانوية');
        }

        // Bachelor's Attachments
        $uploadAndAttach('file_ba_cert', $edBA->id, 2, 'مصدقة الإجازة الجامعية الأولى');
        if ($request->hasFile('ba_decision_file')) {
            $uploadAndAttach('ba_decision_file', $edBA->id, 2, 'قرار معادلة الشهادة الجامعية الأولى');
        }

        // Master's Attachments
        $uploadAndAttach('file_ma_cert', $edMA->id, 3, 'نسخة مصدقة عن شهادة الماجستير');
        $uploadAndAttach('file_ma_dates', $edMA->id, 3, 'وثيقة تواريخ التسجيل والمناقشة والمنح');
        $uploadAndAttach('file_thesis_summary', $edMA->id, 3, 'ملخص رسالة الماجستير باللغة العربية');
        $uploadAndAttach('file_lang_icdl', $edMA->id, 6, 'شهادة اللغة الإنكليزية + شهادة ICDL');
        $uploadAndAttach('file_cv', $edMA->id, 3, 'السيرة الذاتية للمرشح');
        $uploadAndAttach('file_payment', $edMA->id, 4, 'إيصال تسديد رسم تعادل 100,000 ل.س');
        $uploadAndAttach('file_uni_request', $edMA->id, 3, 'كتاب الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date);

        if ($request->hasFile('file_exp_cert')) {
            $uploadAndAttach('file_exp_cert', $edMA->id, 3, 'شهادة خبرة تدريسية لا تقل عن سنتين');
        }
        if ($request->hasFile('file_contracts')) {
            $uploadAndAttach('file_contracts', $edMA->id, 3, 'العقود وإيصالات الرواتب المصدقة');
        }

        return redirect()->route('university.dashboard')
            ->with('success', 'تم تقديم معاملة (' . $requestType . ') بنجاح للطلب رقم: ' . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
    }

    public function lookupCandidate(Request $request)
    {
        $nationalId = trim($request->query('national_id', $request->query('search', '')));
        
        if (empty($nationalId)) {
            return response()->json(['success' => false, 'message' => 'يرجى إدخال الرقم الوطني للمرشح.']);
        }

        $candidate = EquivalenceProfile::where('national_id', $nationalId)
            ->with([
                'nationality',
                'applications.educations.level',
                'applications.educations.country',
                'applications.educations.university'
            ])
            ->first();

        if (!$candidate) {
            $candidate = EquivalenceProfile::where('national_id', 'like', "%{$nationalId}%")
                ->with([
                    'nationality',
                    'applications.educations.level',
                    'applications.educations.country',
                    'applications.educations.university'
                ])
                ->first();
        }

        if (!$candidate) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على أي مرشح مسجل سابقاً بهذا الرقم الوطني (' . $nationalId . '). يمكنك إدخال البيانات يدوياً.']);
        }

        // Extract latest education records across candidate's applications
        $allEducations = $candidate->applications->pluck('educations')->flatten();

        $hsEd = $allEducations->filter(function($e) {
            return $e->level && str_contains($e->level->name, 'ثانوية');
        })->last();

        $baEd = $allEducations->filter(function($e) {
            return $e->level && str_contains($e->level->name, 'إجازة');
        })->last();

        $maEd = $allEducations->filter(function($e) {
            return $e->level && str_contains($e->level->name, 'ماجستير');
        })->last();

        return response()->json([
            'success' => true,
            'candidate' => [
                'full_name' => $candidate->full_name,
                'national_id' => $candidate->national_id,
                'dob' => $candidate->dob,
                'job_title' => $candidate->job_title,
                'gender' => $candidate->gender,
                'email' => $candidate->email,
                'mobile' => $candidate->mobile,
                'phone' => $candidate->phone,
                'address' => $candidate->address,
                'nationality_id' => $candidate->nationality_id,
                'is_syrian' => $candidate->is_syrian,
            ],
            'high_school' => $hsEd ? [
                'country_id' => $hsEd->country_id,
                'type' => $hsEd->section_name,
                'grant_date' => $hsEd->grant_date,
                'decision_no' => preg_replace('/[^0-9]/', '', $hsEd->notes ?? ''),
            ] : null,
            'bachelor' => $baEd ? [
                'country_id' => $baEd->country_id,
                'university_id' => $baEd->university_id,
                'university_other' => $baEd->section_name,
                'faculty' => $baEd->general_specialization,
                'department' => $baEd->exact_specialization,
                'registration_date' => $baEd->registration_date,
                'grant_date' => $baEd->grant_date,
                'rank' => $baEd->rank,
                'decision_no' => preg_replace('/[^0-9]/', '', $baEd->notes ?? ''),
            ] : null,
            'master' => $maEd ? [
                'university_id' => $maEd->university_id,
                'faculty' => $maEd->general_specialization,
                'department' => $maEd->exact_specialization,
                'registration_date' => $maEd->registration_date,
                'defense_date' => $maEd->defense_date,
                'grant_date' => $maEd->grant_date,
                'rank' => $maEd->rank,
                'supervisor' => $maEd->supervisor_name,
                'thesis_title' => $maEd->thesis_title,
            ] : null,
        ]);
    }

    protected function getUnreadNotifications()
    {
        if (!Auth::check()) {
            return collect();
        }
        $uniId = Auth::user()->university_id;
        return ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();
    }
}

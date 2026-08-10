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

    public function showSyrianMastersWizard(Request $request)
    {
        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->where('status', 'مسودة')
                ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'courses'])
                ->first();
        }

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
            'notifications',
            'draft'
        ));
    }

    public function submitSyrianMastersWizard(Request $request)
    {
        $isDraft = $request->input('action') === 'save_draft';

        // 1. Validation of all sections
        if ($isDraft) {
            $rules = [
                'full_name' => 'required|string|max:255',
                'national_id' => 'required|string|max:50',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'nationality_id' => 'nullable|exists:lookup_countries,id',
                'dob' => 'nullable|date',
                'job_title' => 'nullable|string|max:150',
                'phone' => 'nullable|string',
                'mobile' => 'nullable|string',
                'email' => 'nullable|email:filter|max:255',
                'address' => 'nullable|string',
                'gender' => 'nullable|string|in:ذكر,أنثى',
                'is_syrian' => 'nullable|boolean',

                'hs_country_id' => 'nullable|exists:lookup_countries,id',
                'hs_type' => 'nullable|string',
                'hs_grant_date' => 'nullable|date',

                'ba_country_id' => 'nullable|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_faculty' => 'nullable|string',
                'ba_department' => 'nullable|string',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'nullable|date',
                'ba_rank' => 'nullable|string',

                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_faculty' => 'nullable|string',
                'ma_department' => 'nullable|string',
                'ma_registration_date' => 'nullable|date',
                'ma_defense_date' => 'nullable|date',
                'ma_grant_date' => 'nullable|date',
                'ma_rank' => 'nullable|string',
                'ma_supervisor' => 'nullable|string',
                'ma_thesis_title' => 'nullable|string',

                'req_no' => 'nullable',
                'req_date' => 'nullable|date',
                'courses' => 'nullable|array',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:10240',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:10240',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:10240',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:10240',
                'file_ma_dates' => 'nullable|file|mimes:pdf|max:10240',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:10240',
                'file_lang_icdl' => 'nullable|file|mimes:pdf|max:10240',
                'file_cv' => 'nullable|file|mimes:pdf|max:10240',
                'file_payment' => 'nullable|file|mimes:pdf|max:10240',
            ];
            $messages = [];
        } else {
            $rules = [
                // Step 1: Personal Info
                'full_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'nationality_id' => 'required|exists:lookup_countries,id',
                'national_id' => 'required|string|max:50',
                'dob' => 'required|date',
                'job_title' => 'required|string|max:150',
                'phone' => 'nullable|string|regex:/^[0-9]{10}$/',
                'mobile' => 'required|string|regex:/^[0-9]{10}$/',
                'email' => 'required|email:filter|max:255',
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
                'ba_grant_date' => 'required|date|after:ba_registration_date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => 'nullable|string|max:100',
                'ba_decision_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

                // Step 4: Syrian Master's Degree Info
                'ma_university_id' => 'required|exists:lookup_universities,id',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_registration_date' => 'required|date',
                'ma_defense_date' => 'required|date|after:ma_registration_date',
                'ma_grant_date' => 'required|date|after:ma_defense_date|before_or_equal:today',
                'ma_rank' => 'required|string|max:100',
                'ma_supervisor' => 'required|string|max:255',
                'ma_thesis_title' => 'required|string',
                
                // Experience
                'has_experience' => 'nullable|boolean',
                'exp_place' => 'nullable|required_if:has_experience,1|string|max:255',
                'exp_from_year' => 'nullable|required_if:has_experience,1|date',
                'exp_to_year' => 'nullable|required_if:has_experience,1|date|after_or_equal:exp_from_year',

                // Step 5: University Request & Courses Details
                'req_no' => 'required|regex:/^[0-9]+$/',
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
            ];
            $messages = [
                'mobile.regex' => 'رقم الهاتف المحمول يجب أن يتكون من 10 أرقام.',
                'phone.regex' => 'رقم الهاتف الأرضي يجب أن يتكون من 10 أرقام.',
                'email.email' => 'البريد الإلكتروني المدخل غير صحيح.',
                'ba_grant_date.after' => 'تاريخ التخرج من الإجازة يجب أن يكون بعد تاريخ التسجيل بالإجازة.',
                'ba_grant_date.before_or_equal' => 'تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_defense_date.after' => 'تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'ma_grant_date.after' => 'تاريخ منح الدرجة يجب أن يكون بعد تاريخ المناقشة.',
                'ma_grant_date.before_or_equal' => 'تاريخ منح الدرجة يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.',
                'req_no.regex' => 'رقم كتاب طلب التقويم الصادر عن الجامعة يجب أن يتكون من أرقام فقط.',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // 2. Save Equivalence Profile (Candidate)
        $profile = EquivalenceProfile::updateOrCreate(
            ['national_id' => $request->national_id],
            [
                'full_name' => $request->full_name,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
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
        $hasExp = $request->boolean('has_experience') && !empty($request->input('exp_place'));
        $trackName = $hasExp ? 'ماجستير سوري' : 'ماجستير تطبيقي';
        $requestType = $frequency . ' - ' . $trackName;

        $isDraft = $request->input('action') === 'save_draft';
        $appStatus = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';

        $application = null;
        if ($request->filled('draft_id')) {
            $application = Application::where('id', $request->draft_id)
                ->where('work_university_id', $uniId)
                ->first();
        }

        if ($application) {
            $application->update([
                'candidate_id' => $profile->id,
                'request_type' => $requestType,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'has_previous_degree' => $request->has_experience ? true : false,
                'status' => $appStatus,
            ]);
            ApplicationCourse::where('application_id', $application->id)->delete();
        } else {
            $application = Application::create([
                'candidate_id' => $profile->id,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'study_system' => 'فصلي',
                'has_previous_degree' => $request->has_experience ? true : false,
                'status' => $appStatus,
                'user_id' => Auth::id(),
            ]);
        }

        // 4. Save Courses (if provided)
        if ($request->has('courses') && is_array($request->courses)) {
            foreach ($request->courses as $course) {
                if (!empty($course['name'])) {
                    ApplicationCourse::create([
                        'application_id' => $application->id,
                        'faculty' => $course['faculty'] ?? '',
                        'department' => $course['department'] ?? '',
                        'course_name' => $course['name'],
                        'course_status' => 'مطلوب تدريسه',
                    ]);
                }
            }
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

        if ($isDraft) {
            return redirect()->route('university.dashboard')
                ->with('success', 'تم حفظ معاملة (' . $requestType . ') كمسودة بنجاح! للطلب رقم: ' . $appNo . '. يمكنك استكمال رفع المرفقات والوثائق الناقصة في أي وقت من قسم المسودات.');
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

        $fatherName = $candidate->father_name;
        if (empty($fatherName) && !empty($candidate->full_name)) {
            $cleanName = preg_replace('/^(د\.|م\.|أ\.|أ\.د\.|أستاذ|دكتور|مهندس)\s+/u', '', trim($candidate->full_name));
            $parts = preg_split('/\s+/u', $cleanName);
            if (count($parts) >= 3) {
                $fatherName = $parts[1];
            } elseif (count($parts) == 2) {
                $fatherName = $parts[1];
            }
        }

        $motherName = $candidate->mother_name;

        return response()->json([
            'success' => true,
            'candidate' => [
                'full_name' => $candidate->full_name,
                'father_name' => $fatherName,
                'mother_name' => $motherName,
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

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
use App\Models\EducationResidence;
use App\Models\ApplicationMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Str;

class ApplicationWizardController extends Controller
{
    public function showOptions()
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }
        // Get unread notifications for header
        $notifications = $this->getUnreadNotifications();
        return view('university.apply.options', compact('notifications'));
    }

    public function showSyrianMastersWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType', 'courses'])
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
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $uniId = Auth::user()->university_id;
        $existingApp = null;
        if ($request->filled('draft_id')) {
            $existingApp = Application::where('id', $request->draft_id)
                ->where('work_university_id', $uniId)
                ->with(['educations.attachments'])
                ->first();
        }
        $isExisting = ($existingApp !== null);
        $fileRule = $isExisting ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048';

        $isDraft = $request->input('action') === 'save_draft';

        // 1. Validation of all sections
        if ($isDraft) {
            $rules = [
                'full_name' => 'nullable|string|max:255',
                'national_id' => 'nullable|string|max:50',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'nationality_id' => 'nullable',
                'dob' => 'nullable',
                'job_title' => 'nullable|string|max:150',
                'phone' => 'nullable|string',
                'mobile' => 'nullable|string',
                'email' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'gender' => 'nullable|string|in:ذكر,أنثى',
                'is_syrian' => 'nullable',

                'hs_country_id' => 'nullable',
                'hs_type' => 'nullable|string',
                'hs_grant_date' => 'nullable',
                'hs_decision_no' => 'nullable|string|max:100',
                'hs_decision_date' => 'nullable',

                'ba_country_id' => 'nullable',
                'ba_university_id' => 'nullable',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string|max:255',
                'ba_department' => 'nullable|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable',
                'ba_grant_date' => 'nullable',
                'ba_rank' => 'nullable|string',
                'ba_decision_no' => 'nullable|string|max:100',
                'ba_decision_date' => 'nullable',

                'ma_university_id' => 'nullable',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_registration_date' => 'nullable',
                'ma_defense_date' => 'nullable',
                'ma_grant_date' => 'nullable',
                'ma_rank' => 'nullable|string',
                'ma_supervisor' => 'nullable|string',
                'ma_thesis_title' => 'nullable|string',

                'has_experience' => 'nullable',
                'exp_place' => 'nullable|string|max:255',
                'exp_from_year' => 'nullable',
                'exp_to_year' => 'nullable',

                'req_no' => 'nullable',
                'req_date' => 'nullable',
                'is_first_time' => 'nullable',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_dates' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:2048',
                'file_lang_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_exp_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
            $syriaId = $syriaCountry ? $syriaCountry->id : null;

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
                'hs_type' => 'required|string|in:علمي,أدبي,شرعي,صناعي,تجاري',
                'hs_grant_date' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 3: Bachelor's Degree Info
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'nullable|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'required|date',
                'ba_grant_date' => 'required|date|after:ba_registration_date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => ($request->ba_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => ($request->ba_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'ba_decision_file' => ($request->ba_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 4: Syrian Master's Degree Info
                'ma_university_id' => 'required|exists:lookup_universities,id',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
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

                // Step 1: University Request Details
                'req_no' => 'required',
                'req_date' => 'required|date',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // Step 5: Attachments Upload
                'file_uni_request' => $fileRule,
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => $fileRule,
                'file_ba_cert' => $fileRule,
                'file_ma_cert' => $fileRule,
                'file_ma_dates' => $fileRule,
                'file_thesis_summary' => $fileRule,
                'file_exp_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_lang_cert' => $fileRule,
                'file_icdl_cert' => $fileRule,
                'file_cv' => $fileRule,
                'file_payment' => $fileRule,
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [
                'mobile.regex' => 'رقم الهاتف المحمول يجب أن يتكون من 10 أرقام.',
                'phone.regex' => 'رقم الهاتف الأرضي يجب أن يتكون من 10 أرقام.',
                'email.email' => 'البريد الإلكتروني المدخل غير صحيح.',
                'hs_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_date.required' => 'يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الشهادة الثانوية غير السورية (PDF).',
                'ba_decision_no.required' => 'يرجى إدخال رقم قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_date.required' => 'يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الإجازة الجامعية غير السورية (PDF).',
                'ba_grant_date.after' => 'تاريخ التخرج من الإجازة يجب أن يكون بعد تاريخ التسجيل بالإجازة.',
                'ba_grant_date.before_or_equal' => 'تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_defense_date.after' => 'تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'ma_grant_date.after' => 'تاريخ منح الدرجة يجب أن يكون بعد تاريخ المناقشة.',
                'ma_grant_date.before_or_equal' => 'تاريخ منح الدرجة يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.',
                'max' => 'حجم الملف المرفق يتجاوز الحد الأقصى المسموح به (2 ميغابايت).',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // 2. Save Equivalence Profile (Candidate)
        $candNationalId = $request->national_id ?: ($existingApp && $existingApp->candidate ? $existingApp->candidate->national_id : ('TMP-' . time() . '-' . rand(100, 999)));
        $profile = EquivalenceProfile::updateOrCreate(
            ['national_id' => $candNationalId],
            [
                'full_name' => $request->full_name ?: 'مسودة غير مكتملة',
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
                'is_syrian' => $request->is_syrian ?? 1,
            ]
        );

        // 3. Save Application
        $hasExp = $request->boolean('has_experience') && !empty($request->input('exp_place'));
        $trackName = $hasExp ? 'ماجستير سوري' : 'ماجستير تطبيقي';
        $appPrefix = $hasExp ? 'MA-SY-' : 'MA-APP-';
        $appNo = $appPrefix . rand(100000, 999999);
        $requestType = $trackName;

        $wasAwaitingDocs = ($existingApp && $existingApp->status === 'بانتظار الوثائق');

        if ($isDraft) {
            $appStatus = 'مسودة';
        } elseif ($wasAwaitingDocs) {
            $appStatus = 'بانتظار الوثائق';
        } else {
            $appStatus = 'تحت التدقيق الأولي';
        }

        $application = $existingApp;
        $existingAttachments = [];

        if ($application) {
            // Collect existing attachment file paths before updating Educations
            foreach ($application->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->notes) {
                        $existingAttachments[$att->notes] = $att->file_path;
                    }
                    if (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'شخصية')) {
                        $existingAttachments['national_id'] = $att->file_path;
                        $existingAttachments['file_national_id'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
                        $existingAttachments['hs_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الثانوية')) {
                        $existingAttachments['hs_decision'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'الإجازة') && !str_contains($att->notes, 'قرار')) {
                        $existingAttachments['ba_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الجامعية')) {
                        $existingAttachments['ba_decision'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'شهادة الماجستير')) {
                        $existingAttachments['ma_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'تواريخ')) {
                        $existingAttachments['ma_dates'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ملخص')) {
                        $existingAttachments['thesis_summary'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'كتاب الجامعة')) {
                        $existingAttachments['uni_request'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'اللغة')) {
                        $existingAttachments['lang_cert'] = $att->file_path;
                        $existingAttachments['file_lang_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ICDL') || str_contains($att->notes, 'حاسوب')) {
                        $existingAttachments['icdl_cert'] = $att->file_path;
                        $existingAttachments['file_icdl_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'السيرة')) {
                        $existingAttachments['cv'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'إيصال')) {
                        $existingAttachments['payment'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'خبرة')) {
                        $existingAttachments['exp_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'العقود')) {
                        $existingAttachments['contracts'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'أخرى') || str_contains($att->notes, 'اخرى')) {
                        $existingAttachments['other_attachments'] = $att->file_path;
                    }
                }
            }

            $isFirstTime = $request->has('is_first_time') ? 1 : 0;

            $application->update([
                'candidate_id' => $profile->id,
                'request_type' => $requestType,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'has_previous_degree' => $request->has_experience ? true : false,
                'status' => $appStatus,
            ]);
            ApplicationCourse::where('application_id', $application->id)->delete();
            Education::where('application_id', $application->id)->delete();
            $appNo = $application->application_no ?? $appNo;
        } else {
            $isFirstTime = $request->has('is_first_time') ? 1 : 0;
            $application = Application::create([
                'candidate_id' => $profile->id,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'study_system' => 'فصلي',
                'has_previous_degree' => $request->boolean('has_previous_degree'),
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

        $hsLevelId = $lvlHS ? $lvlHS->id : 4;
        $baLevelId = $lvlBA ? $lvlBA->id : 1;
        $maLevelId = $lvlMA ? $lvlMA->id : 2;

        // 5. Create Educations records
        // A. High School Education
        $hsNotes = null;
        if ($request->hs_decision_no) {
            $hsNotes = 'رقم قرار المعادلة الثانوية: ' . $request->hs_decision_no;
            if ($request->hs_decision_date) {
                $hsNotes .= ' | تاريخ القرار: ' . $request->hs_decision_date;
            }
        }

        $hsGrantDate = null;
        if ($request->hs_grant_date) {
            $hsGrantDate = strlen($request->hs_grant_date) == 4 ? ($request->hs_grant_date . '-01-01') : $request->hs_grant_date;
        }

        $edHS = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $hsLevelId,
            'country_id' => $request->hs_country_id,
            'section_name' => $request->hs_type,
            'general_specialization' => $request->hs_type,
            'grant_date' => $hsGrantDate,
            'notes' => $hsNotes,
        ]);

        // B. Bachelor's Education
        $baNotes = null;
        if ($request->ba_decision_no) {
            $baNotes = 'رقم قرار معادلة الإجازة: ' . $request->ba_decision_no;
            if ($request->ba_decision_date) {
                $baNotes .= ' | تاريخ القرار: ' . $request->ba_decision_date;
            }
        }

        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $request->ba_university_id,
            'faculty' => $request->ba_faculty,
            'department' => $request->ba_department,
            'section_name' => $request->ba_specialization ?: ($request->ba_university_other ?? null),
            'general_specialization' => $request->ba_faculty,
            'exact_specialization' => $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => $baNotes,
        ]);

        // C. Master's Education
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaCountryId = $syriaCountry ? $syriaCountry->id : $request->nationality_id;

        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $syriaCountryId,
            'university_id' => $request->ma_university_id,
            'faculty' => $request->ma_faculty,
            'department' => $request->ma_department,
            'section_name' => $request->ma_specialization ?? null,
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
        $folder = 'attachments/' . $application->id;

        $uploadAndAttach = function($fileKey, $educationId, $typeId, $notes, $existingKey = null) use ($request, $folder, $existingAttachments) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store($folder, 'public');
                EducationAttachment::create([
                    'education_id' => $educationId,
                    'attachment_type_id' => $typeId,
                    'file_path' => $path,
                    'notes' => $notes,
                ]);
            } else {
                $oldPath = $existingAttachments[$notes] ?? ($existingKey && isset($existingAttachments[$existingKey]) ? $existingAttachments[$existingKey] : null);
                if ($oldPath) {
                    EducationAttachment::create([
                        'education_id' => $educationId,
                        'attachment_type_id' => $typeId,
                        'file_path' => $oldPath,
                        'notes' => $notes,
                    ]);
                }
            }
        };

        // Personal ID Attachment (if provided)
        if ($request->hasFile('file_national_id') || isset($existingAttachments['national_id'])) {
            $uploadAndAttach('file_national_id', $edMA->id, 11, 'صورة عن الهوية الشخصية', 'national_id');
        }

        // High School Attachments
        $uploadAndAttach('file_hs_cert', $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', 'hs_cert');
        if ($request->hasFile('hs_decision_file') || isset($existingAttachments['hs_decision'])) {
            $uploadAndAttach('hs_decision_file', $edHS->id, 13, 'قرار معادلة الشهادة الثانوية', 'hs_decision');
        }

        // Bachelor's Attachments
        $uploadAndAttach('file_ba_cert', $edBA->id, 2, 'مصدقة الإجازة الجامعية الأولى', 'ba_cert');
        if ($request->hasFile('ba_decision_file') || isset($existingAttachments['ba_decision'])) {
            $uploadAndAttach('ba_decision_file', $edBA->id, 15, 'قرار معادلة الإجازة الجامعية الأولى', 'ba_decision');
        }

        // Master's Attachments
        $uploadAndAttach('file_ma_cert', $edMA->id, 12, 'نسخة مصدقة عن شهادة الماجستير', 'ma_cert');
        $uploadAndAttach('file_ma_dates', $edMA->id, 16, 'وثيقة تواريخ التسجيل والمناقشة والمنح بالماجستير', 'ma_dates');
        $uploadAndAttach('file_thesis_summary', $edMA->id, 17, 'ملخص رسالة الماجستير باللغة العربية', 'thesis_summary');
        $uploadAndAttach('file_lang_cert', $edMA->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'lang_cert');
        $uploadAndAttach('file_icdl_cert', $edMA->id, 19, 'شهادة قيادة الحاسوب الدولية (ICDL)', 'icdl_cert');
        $uploadAndAttach('file_cv', $edMA->id, 20, 'السيرة الذاتية للمرشح', 'cv');
        $uploadAndAttach('file_payment', $edMA->id, 6, 'إيصال تسديد رسم تعادل 100,000 ل.س', 'payment');
        $uploadAndAttach('file_uni_request', $edMA->id, 21, 'كتاب طلب التقويم الصادر عن الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date, 'uni_request');

        if ($request->hasFile('file_exp_cert') || isset($existingAttachments['exp_cert'])) {
            $uploadAndAttach('file_exp_cert', $edMA->id, 22, 'شهادة خبرة تدريسية لا تقل عن سنتين', 'exp_cert');
        }
        if ($request->hasFile('file_contracts') || isset($existingAttachments['contracts'])) {
            $uploadAndAttach('file_contracts', $edMA->id, 23, 'العقود وإيصالات الرواتب المصدقة', 'contracts');
        }
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttach('file_other_attachments', $edMA->id, 24, 'مرفقات أخرى', 'other_attachments');
        }

        if ($isDraft) {
            return redirect()->route('university.dashboard')
                ->with('success', 'تم حفظ معاملة (' . $requestType . ') كمسودة بنجاح! للطلب رقم: ' . $appNo . '. يمكنك استكمال رفع المرفقات والوثائق الناقصة في أي وقت من قسم المسودات.');
        }

        if ($wasAwaitingDocs) {
            $candidateName = $profile ? $profile->full_name : ($application->candidate ? $application->candidate->full_name : '');
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة';

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). يرجى تدقيق ومراجعة المعاملة لتحديث حالتها.",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? (User::where('id', '!=', Auth::id())->value('id') ?? 1);

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => $systemAdminId,
                'message' => "✅ [تأكيد استكمال التعديل]: تم استلام التعديلات والوثائق المستكملة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}) بنجاح من قبل جامعة ({$uniName}). المعاملة الآن قيد المراجعة والتدقيق من قبل وزارة التعليم العالي لتحديث حالتها.",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم إشعار وزارة التعليم العالي لتدقيق التعديلات وتحديث حالة المعاملة.')
                ->with('submitted_app_id', $application->id)
                ->with('submitted_app_no', $appNo);
        }

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات الطلب رقم: ' : 'تم تقديم معاملة (' . $requestType . ') بنجاح للطلب رقم: ') . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
    }

    public function showSyrianDoctorateWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType', 'courses'])
                ->first();
        }

        $countries = LookupCountry::orderBy('name', 'asc')->get();
        
        // Find Syria country model
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        // Universities
        $universities = LookupUniversity::with('country')->orderBy('name', 'asc')->get();
        $educationLevels = LookupEducationLevel::all();

        // Get unread notifications for header
        $notifications = $this->getUnreadNotifications();

        $previousApplications = Application::where('work_university_id', Auth::user()->university_id)
            ->where('status', '!=', 'مسودة')
            ->with('candidate')
            ->orderBy('id', 'desc')
            ->get();

        return view('university.apply.syrian_doctorate', compact(
            'countries', 
            'universities', 
            'educationLevels', 
            'syriaId',
            'notifications',
            'draft',
            'previousApplications'
        ));
    }

    public function submitSyrianDoctorateWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $uniId = Auth::user()->university_id;
        $existingApp = null;
        if ($request->filled('draft_id')) {
            $existingApp = Application::where('id', $request->draft_id)
                ->where('work_university_id', $uniId)
                ->with(['educations.attachments'])
                ->first();
        }
        $isExisting = ($existingApp !== null);
        $fileRule = $isExisting ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048';

        $isDraft = $request->input('action') === 'save_draft';
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

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
                'hs_grant_date' => 'nullable|numeric|digits:4',
                'hs_decision_no' => 'nullable|string|max:100',
                'hs_decision_date' => 'nullable|date',

                'ba_country_id' => 'nullable|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string',
                'ba_department' => 'nullable|string',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'nullable|date',
                'ba_rank' => 'nullable|string',
                'ba_decision_no' => 'nullable|string|max:100',
                'ba_decision_date' => 'nullable|date',

                'has_diploma' => 'nullable|boolean',
                'dip_country_id' => 'nullable|exists:lookup_countries,id',
                'dip_university_id' => 'nullable|exists:lookup_universities,id',
                'dip_faculty' => 'nullable|string|max:255',
                'dip_grant_date' => 'nullable|date',
                'dip_rank' => 'nullable|string|max:100',

                'ma_country_id' => 'nullable|exists:lookup_countries,id',
                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string',
                'ma_department' => 'nullable|string',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_registration_date' => 'nullable|date',
                'ma_defense_date' => 'nullable|date',
                'ma_grant_date' => 'nullable|date',
                'ma_rank' => 'nullable|string',
                'ma_supervisor' => 'nullable|string',
                'ma_thesis_title' => 'nullable|string',
                'ma_decision_no' => 'nullable|string|max:100',
                'ma_decision_date' => 'nullable|date',

                'phd_university_id' => 'nullable|exists:lookup_universities,id',
                'phd_faculty' => 'nullable|string',
                'phd_department' => 'nullable|string',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_registration_date' => 'nullable|date',
                'phd_defense_date' => 'nullable|date',
                'phd_grant_date' => 'nullable|date',
                'phd_rank' => 'nullable|string',
                'phd_supervisor' => 'nullable|string',
                'phd_thesis_title' => 'nullable|string',

                'has_experience' => 'nullable|boolean',
                'exp_place' => 'nullable|string|max:255',
                'exp_from_year' => 'nullable|date',
                'exp_to_year' => 'nullable|date',

                'req_no' => 'nullable',
                'req_date' => 'nullable|date',
                'is_first_time' => 'nullable|boolean',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_diploma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_test' => 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
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

                // Step 1: University Request Details & Repeats
                'req_no' => 'required',
                'req_date' => 'required|date',
                'is_first_time' => 'required|in:0,1',
                'parent_application_id' => 'nullable|required_if:is_first_time,0|exists:applications,id',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // Step 2: High School Info
                'hs_country_id' => 'required|exists:lookup_countries,id',
                'hs_type' => 'required|string|in:علمي,أدبي,شرعي,صناعي,تجاري',
                'hs_grant_date' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 3: Bachelor's Degree Info
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'nullable|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'required|date',
                'ba_grant_date' => 'required|date|after:ba_registration_date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => ($request->ba_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => ($request->ba_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'ba_decision_file' => ($request->ba_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Optional Diploma
                'has_diploma' => 'nullable|boolean',
                'dip_country_id' => 'nullable|required_if:has_diploma,1|exists:lookup_countries,id',
                'dip_university_id' => 'nullable|exists:lookup_universities,id',
                'dip_faculty' => 'nullable|string|max:255',
                'dip_grant_date' => 'nullable|date',
                'dip_rank' => 'nullable|string|max:100',

                // Step 4: Master's Degree Info
                'ma_country_id' => 'required|exists:lookup_countries,id',
                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_registration_date' => 'required|date',
                'ma_defense_date' => 'required|date|after:ma_registration_date',
                'ma_grant_date' => 'required|date|after:ma_defense_date|before_or_equal:today',
                'ma_rank' => 'required|string|max:100',
                'ma_supervisor' => 'required|string|max:255',
                'ma_thesis_title' => 'required|string',
                'ma_decision_no' => 'nullable|string|max:100',
                'ma_decision_date' => 'nullable|date',
                'ma_decision_file' => ($request->filled('ma_decision_no') || $request->filled('ma_decision_date'))
                    ? ((isset($existingAttachments['ma_decision']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048')
                    : 'nullable|file|mimes:pdf|max:2048',

                // Step 5: Syrian Doctorate Degree Info
                'phd_university_id' => 'required|exists:lookup_universities,id',
                'phd_faculty' => 'required|string|max:255',
                'phd_department' => 'nullable|string|max:255',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_registration_date' => 'required|date',
                'phd_defense_date' => 'required|date|after:phd_registration_date',
                'phd_grant_date' => 'required|date|after:phd_defense_date|before_or_equal:today',
                'phd_rank' => 'required|string|max:100',
                'phd_supervisor' => 'required|string|max:255',
                'phd_thesis_title' => 'required|string',

                // Step 6: Attachments Upload (CV + Uni request + Certificates)
                'file_national_id' => $fileRule,
                'file_hs_cert' => $fileRule,
                'file_ba_cert' => $fileRule,
                'file_diploma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => $fileRule,
                'file_phd_cert' => $fileRule,
                'file_thesis_summary' => $fileRule,
                'file_phd_council_decisions' => $fileRule,
                'file_ma_council_decisions' => $fileRule,
                'file_icdl' => $fileRule,
                'file_english_test' => $fileRule,
                'file_payment' => $fileRule,
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
                'file_uni_request' => $fileRule,
                'file_cv' => $fileRule,
            ];
            $messages = [
                'mobile.regex' => 'رقم الهاتف المحمول يجب أن يتكون من 10 أرقام.',
                'phone.regex' => 'رقم الهاتف الأرضي يجب أن يتكون من 10 أرقام.',
                'email.email' => 'البريد الإلكتروني المدخل غير صحيح.',
                'parent_application_id.required_if' => 'يرجى اختيار المعاملة السابقة للمرشح عند اختيار (سبق التقدم بتعادل لشهادة أخرى).',
                'hs_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_date.required' => 'يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الشهادة الثانوية غير السورية (PDF).',
                'ba_decision_no.required' => 'يرجى إدخال رقم قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_date.required' => 'يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الإجازة الجامعية غير السورية (PDF).',
                'ba_grant_date.after' => 'تاريخ التخرج من الإجازة يجب أن يكون بعد تاريخ التسجيل بالإجازة.',
                'ba_grant_date.before_or_equal' => 'تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_defense_date.after' => 'تاريخ المناقشة للماجستير يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'ma_grant_date.after' => 'تاريخ منح الماجستير يجب أن يكون بعد تاريخ المناقشة.',
                'phd_defense_date.after' => 'تاريخ المناقشة للدكتوراه يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'phd_grant_date.after' => 'تاريخ منح الدكتوراه يجب أن يكون بعد تاريخ المناقشة.',
                'phd_grant_date.before_or_equal' => 'تاريخ منح الدكتوراه يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.',
                'file_national_id.required' => 'يرجى رفع صورة عن الهوية الشخصية (PDF).',
                'file_hs_cert.required' => 'يرجى رفع صورة مصدقة عن الشهادة الثانوية العامة (PDF).',
                'file_ba_cert.required' => 'يرجى رفع صورة مصدقة عن شهادة الإجازة الجامعية الأولى (PDF).',
                'file_ma_cert.required' => 'يرجى رفع صورة عن شهادة الماجستير (PDF).',
                'file_phd_cert.required' => 'يرجى رفع صورة مصدقة أصولاً عن شهادة الدكتوراه السورية (PDF).',
                'file_thesis_summary.required' => 'يرجى رفع ملخص أطروحة الدكتوراه باللغة العربية (PDF).',
                'file_phd_council_decisions.required' => 'يرجى رفع قرارات مجلس الجامعة للدكتوراه (وثيقة تواريخ التسجيل والمناقشة والمنح) (PDF).',
                'file_ma_council_decisions.required' => 'يرجى رفع قرارات مجلس الجامعة للماجستير (وثيقة تواريخ التسجيل والمناقشة والمنح) (PDF).',
                'file_icdl.required' => 'يرجى رفع شهادة ICDL (PDF).',
                'file_english_test.required' => 'يرجى رفع شهادة اختبار اللغة الإنكليزية (PDF).',
                'file_payment.required' => 'يرجى رفع إيصال تسديد رسم تعادل الدكتوراه (PDF).',
                'file_uni_request.required' => 'يرجى رفع كتاب طلب التقويم الصادر عن الجامعة (PDF).',
                'file_cv.required' => 'يرجى رفع السيرة الذاتية للمرشح (PDF).',
                'max' => 'حجم الملف المرفق يتجاوز الحد الأقصى المسموح به (2 ميغابايت).',
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
        $appNo = 'PHD-SY-' . rand(100000, 999999);
        $requestType = 'دكتوراه سوري';

        $wasAwaitingDocs = ($existingApp && $existingApp->status === 'بانتظار الوثائق');

        if ($isDraft) {
            $appStatus = 'مسودة';
        } elseif ($wasAwaitingDocs) {
            $appStatus = 'بانتظار الوثائق';
        } else {
            $appStatus = 'تحت التدقيق الأولي';
        }

        $application = $existingApp;
        $existingAttachments = [];

        if ($application) {
            foreach ($application->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->notes) {
                        $existingAttachments[$att->notes] = $att->file_path;
                        if (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'شخصية')) {
                            $existingAttachments['national_id'] = $att->file_path;
                            $existingAttachments['file_national_id'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments['hs_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الثانوية')) {
                            $existingAttachments['hs_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'الإجازة') && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments['ba_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الجامعية')) {
                            $existingAttachments['ba_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'دبلوم')) {
                            $existingAttachments['diploma_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'شهادة ماجستير')) {
                            $existingAttachments['ma_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار معادلة شهادة الماجستير')) {
                            $existingAttachments['ma_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الدكتوراه')) {
                            $existingAttachments['phd_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ملخص')) {
                            $existingAttachments['thesis_summary'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'مكتبة الأسد') || str_contains($att->notes, 'مكتبة الاسد')) {
                            $existingAttachments['assad_library'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرارات مجلس الجامعة للدكتوراه')) {
                            $existingAttachments['phd_council_decisions'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرارات مجلس الجامعة للماجستير')) {
                            $existingAttachments['ma_council_decisions'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ICDL') || str_contains($att->notes, 'حاسوب')) {
                            $existingAttachments['icdl'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'اللغة') || str_contains($att->notes, 'إنكليزية')) {
                            $existingAttachments['english_test'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'كتاب الجامعة')) {
                            $existingAttachments['uni_request'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'السيرة')) {
                            $existingAttachments['cv'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'إيصال') || str_contains($att->notes, 'رسوم التعادل')) {
                            $existingAttachments['payment'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'أخرى') || str_contains($att->notes, 'اخرى')) {
                            $existingAttachments['other_attachments'] = $att->file_path;
                        }
                    }
                }
            }

            $isFirstTime = $request->input('is_first_time', 1);

            $application->update([
                'candidate_id' => $profile->id,
                'parent_application_id' => ($isFirstTime == 0) ? $request->parent_application_id : null,
                'request_type' => $requestType,
                'work_faculty' => $request->phd_faculty,
                'work_department' => $request->phd_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'has_previous_degree' => ($isFirstTime == 0),
                'status' => $appStatus,
            ]);
            ApplicationCourse::where('application_id', $application->id)->delete();
            Education::where('application_id', $application->id)->delete();
            $appNo = $application->application_no ?? $appNo;
        } else {
            $isFirstTime = $request->input('is_first_time', 1);
            $application = Application::create([
                'candidate_id' => $profile->id,
                'parent_application_id' => ($isFirstTime == 0) ? $request->parent_application_id : null,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->phd_faculty,
                'work_department' => $request->phd_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'study_system' => 'فصلي',
                'has_previous_degree' => ($isFirstTime == 0),
                'status' => $appStatus,
                'user_id' => Auth::id(),
            ]);
        }

        // 4. Save Courses
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

        // Get Education Levels
        $lvlHS = LookupEducationLevel::where('name', 'like', '%ثانوية%')->first();
        $lvlBA = LookupEducationLevel::where('name', 'like', '%إجازة%')->first();
        $lvlDIP = LookupEducationLevel::where('name', 'like', '%دبلوم%')->first();
        $lvlMA = LookupEducationLevel::where('name', 'like', '%ماجستير%')->first();
        $lvlPhD = LookupEducationLevel::where('name', 'like', '%دكتوراه%')->first();

        $hsLevelId = $lvlHS ? $lvlHS->id : 4;
        $baLevelId = $lvlBA ? $lvlBA->id : 1;
        $dipLevelId = $lvlDIP ? $lvlDIP->id : 2;
        $maLevelId = $lvlMA ? $lvlMA->id : 2;
        $phdLevelId = $lvlPhD ? $lvlPhD->id : 3;

        // 5. Create Educations records
        // A. High School
        $hsNotes = null;
        if ($request->hs_decision_no) {
            $hsNotes = 'رقم قرار المعادلة الثانوية: ' . $request->hs_decision_no;
            if ($request->hs_decision_date) {
                $hsNotes .= ' | تاريخ القرار: ' . $request->hs_decision_date;
            }
        }

        $edHS = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $hsLevelId,
            'country_id' => $request->hs_country_id,
            'section_name' => $request->hs_type,
            'general_specialization' => $request->hs_type,
            'grant_date' => $request->hs_grant_date,
            'notes' => $hsNotes,
        ]);

        // B. Bachelor's
        $baNotes = null;
        if ($request->ba_decision_no) {
            $baNotes = 'رقم قرار معادلة الإجازة: ' . $request->ba_decision_no;
            if ($request->ba_decision_date) {
                $baNotes .= ' | تاريخ القرار: ' . $request->ba_decision_date;
            }
        }

        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $request->ba_university_id,
            'faculty' => $request->ba_faculty,
            'department' => $request->ba_department,
            'section_name' => $request->ba_specialization ?: ($request->ba_university_other ?? null),
            'general_specialization' => $request->ba_faculty,
            'exact_specialization' => $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => $baNotes,
        ]);

        // C. Postgraduate Diploma (if applicable)
        $edDIP = null;
        if ($request->boolean('has_diploma') || $request->filled('dip_faculty') || $request->filled('dip_university_id') || $request->filled('dip_grant_date')) {
            $edDIP = Education::create([
                'application_id' => $application->id,
                'education_level_id' => $dipLevelId,
                'country_id' => $request->dip_country_id ?? $syriaId,
                'university_id' => $request->dip_university_id,
                'faculty' => $request->dip_faculty,
                'department' => $request->dip_department ?? null,
                'general_specialization' => $request->dip_faculty,
                'exact_specialization' => $request->dip_department ?? null,
                'grant_date' => $request->dip_grant_date,
                'rank' => $request->dip_rank,
                'notes' => 'دبلوم دراسات عليا / تأهيل وتخصص',
            ]);
        }

        // D. Master's
        $maNotes = null;
        if ($request->ma_decision_no) {
            $maNotes = 'رقم قرار معادلة شهادة الماجستير: ' . $request->ma_decision_no;
            if ($request->ma_decision_date) {
                $maNotes .= ' | تاريخ القرار: ' . $request->ma_decision_date;
            }
        }

        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $request->ma_country_id ?? $syriaId,
            'university_id' => $request->ma_university_id,
            'faculty' => $request->ma_faculty,
            'department' => $request->ma_department,
            'section_name' => $request->ma_specialization ?: ($request->ma_university_other ?? null),
            'general_specialization' => $request->ma_faculty,
            'exact_specialization' => $request->ma_department,
            'registration_date' => $request->ma_registration_date,
            'defense_date' => $request->ma_defense_date,
            'grant_date' => $request->ma_grant_date,
            'rank' => $request->ma_rank,
            'supervisor_name' => $request->ma_supervisor,
            'thesis_title' => $request->ma_thesis_title,
            'notes' => $maNotes,
        ]);

        // E. Syrian Doctorate
        $edPhD = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $phdLevelId,
            'country_id' => $syriaId,
            'university_id' => $request->phd_university_id,
            'faculty' => $request->phd_faculty,
            'department' => $request->phd_department,
            'section_name' => $request->phd_specialization ?? null,
            'general_specialization' => $request->phd_faculty,
            'exact_specialization' => $request->phd_department,
            'registration_date' => $request->phd_registration_date,
            'defense_date' => $request->phd_defense_date,
            'grant_date' => $request->phd_grant_date,
            'rank' => $request->phd_rank,
            'supervisor_name' => $request->phd_supervisor,
            'thesis_title' => $request->phd_thesis_title,
            'experience_from_year' => null,
            'experience_to_year' => null,
            'notes' => null,
        ]);

        // 6. Handle File Uploads & Attachments
        $folder = 'attachments/' . $application->id;

        $uploadAndAttachPhd = function($fileKey, $educationId, $typeId, $notes, $existingKey = null) use ($request, $folder, $existingAttachments) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store($folder, 'public');
                EducationAttachment::create([
                    'education_id' => $educationId,
                    'attachment_type_id' => $typeId,
                    'file_path' => $path,
                    'notes' => $notes,
                ]);
            } else {
                $oldPath = $existingAttachments[$notes] ?? ($existingKey && isset($existingAttachments[$existingKey]) ? $existingAttachments[$existingKey] : null);
                if ($oldPath) {
                    EducationAttachment::create([
                        'education_id' => $educationId,
                        'attachment_type_id' => $typeId,
                        'file_path' => $oldPath,
                        'notes' => $notes,
                    ]);
                }
            }
        };

        // Personal ID (Mandatory)
        $uploadAndAttachPhd('file_national_id', $edPhD->id, 11, 'صورة عن الهوية الشخصية', 'national_id');

        // 1. High School
        $uploadAndAttachPhd('file_hs_cert', $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', 'hs_cert');
        if ($request->hasFile('hs_decision_file') || isset($existingAttachments['hs_decision'])) {
            $uploadAndAttachPhd('hs_decision_file', $edHS->id, 13, 'قرار معادلة الشهادة الثانوية', 'hs_decision');
        }

        // 2. Bachelor's
        $uploadAndAttachPhd('file_ba_cert', $edBA->id, 2, 'شهادة الإجازة الجامعية', 'ba_cert');
        if ($request->hasFile('ba_decision_file') || isset($existingAttachments['ba_decision'])) {
            $uploadAndAttachPhd('ba_decision_file', $edBA->id, 2, 'قرار معادلة الشهادة الجامعية الأولى', 'ba_decision');
        }

        // 3. Diploma (if provided)
        if ($edDIP && ($request->hasFile('file_diploma_cert') || isset($existingAttachments['diploma_cert']))) {
            $uploadAndAttachPhd('file_diploma_cert', $edDIP->id, 2, 'شهادة دبلوم إن وجد', 'diploma_cert');
        }

        // 4. Master's
        $uploadAndAttachPhd('file_ma_cert', $edMA->id, 3, 'شهادة ماجستير', 'ma_cert');
        if ($request->hasFile('ma_decision_file') || isset($existingAttachments['ma_decision'])) {
            $uploadAndAttachPhd('ma_decision_file', $edMA->id, 3, 'قرار معادلة شهادة الماجستير', 'ma_decision');
        }
        $uploadAndAttachPhd('file_ma_council_decisions', $edMA->id, 3, 'قرارات مجلس الجامعة للماجستير', 'ma_council_decisions');

        // 5. Syrian Doctorate & Rest of Items
        $uploadAndAttachPhd('file_phd_cert', $edPhD->id, 8, 'نسخة مصدقة أصولاً عن شهادة الدكتوراه', 'phd_cert');
        $uploadAndAttachPhd('file_thesis_summary', $edPhD->id, 5, 'ملخص باللغة العربية عن الأطروحة', 'thesis_summary');
        $uploadAndAttachPhd('file_phd_council_decisions', $edPhD->id, 8, 'قرارات مجلس الجامعة للدكتوراه', 'phd_council_decisions');
        $uploadAndAttachPhd('file_icdl', $edPhD->id, 19, 'شهادة قيادة الحاسوب الدولية (ICDL)', 'icdl');
        $uploadAndAttachPhd('file_english_test', $edPhD->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'english_test');
        $uploadAndAttachPhd('file_payment', $edPhD->id, 10, 'إيصال تسديد رسم تعادل 125,000 ل.س', 'payment');
        
        $uploadAndAttachPhd('file_uni_request', $edPhD->id, 21, 'كتاب طلب التقويم الصادر عن الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date, 'uni_request');
        $uploadAndAttachPhd('file_cv', $edPhD->id, 20, 'السيرة الذاتية للمرشح', 'cv');

        // 13. Other Attachments (Optional)
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttachPhd('file_other_attachments', $edPhD->id, 24, 'مرفقات أخرى', 'other_attachments');
        }

        if ($isDraft) {
            return redirect()->route('university.dashboard')
                ->with('success', 'تم حفظ معاملة (' . $requestType . ') كمسودة بنجاح! للطلب رقم: ' . $appNo . '. يمكنك استكمال رفع المرفقات والوثائق الناقصة في أي وقت من قسم المسودات.');
        }

        if ($wasAwaitingDocs) {
            $candidateName = $profile ? $profile->full_name : ($application->candidate ? $application->candidate->full_name : '');
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة';

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). يرجى تدقيق ومراجعة المعاملة لتحديث حالتها.",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? (User::where('id', '!=', Auth::id())->value('id') ?? 1);

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => $systemAdminId,
                'message' => "✅ [تأكيد استكمال التعديل]: تم استلام التعديلات والوثائق المستكملة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}) بنجاح من قبل جامعة ({$uniName}). المعاملة الآن قيد المراجعة والتدقيق من قبل وزارة التعليم العالي لتحديث حالتها.",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم إشعار وزارة التعليم العالي لتدقيق التعديلات وتحديث حالة المعاملة.')
                ->with('submitted_app_id', $application->id)
                ->with('submitted_app_no', $appNo);
        }

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات الطلب رقم: ' : 'تم تقديم معاملة (' . $requestType . ') بنجاح للطلب رقم: ') . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
    }

    // =========================================================================
    // FOREIGN MASTER'S EQUIVALENCE WIZARD (معاملة الماجستير الخارجي - غير السوري)
    // =========================================================================
    public function showForeignMastersWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $countries = LookupCountry::all();
        $universities = LookupUniversity::all();
        $educationLevels = LookupEducationLevel::all();
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;

        $unreadNotifications = $this->getUnreadNotifications();
        $draft = null;
        if ($request->has('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.attachments.attachmentType', 'educations.residences'])
                ->first();
        }

        return view('university.apply.foreign_masters', compact(
            'countries',
            'universities',
            'educationLevels',
            'syriaId',
            'unreadNotifications',
            'draft'
        ));
    }

    public function submitForeignMastersWizard(Request $request, $id = null)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $uniId = Auth::user()->university_id;
        $existingApp = null;
        $draftId = $request->input('draft_id') ?: $id;
        if ($draftId) {
            $existingApp = Application::where('id', $draftId)
                ->where('work_university_id', $uniId)
                ->with(['educations.attachments', 'educations.residences'])
                ->first();
        }
        $isExisting = ($existingApp !== null);

        $existingFilesMap = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $note = $att->notes ?? '';
                    if ($att->attachment_type_id == 1 || str_contains($note, 'ثانوية')) $existingFilesMap['file_secondary_cert'] = true;
                    if ($att->attachment_type_id == 2 || str_contains($note, 'إجازة')) $existingFilesMap['file_bachelor_cert'] = true;
                    if ($att->attachment_type_id == 3 || str_contains($note, 'قبل المؤهل')) $existingFilesMap['file_prev_qual_cert'] = true;
                    if ($att->attachment_type_id == 4 || str_contains($note, 'شهادة الماجستير')) $existingFilesMap['file_master_cert'] = true;
                    if ($att->attachment_type_id == 5 || str_contains($note, 'كشف علامات')) $existingFilesMap['file_master_transcript'] = true;
                    if ($att->attachment_type_id == 6 || str_contains($note, 'ملخص عن الأطروحة')) $existingFilesMap['file_thesis_abstract'] = true;
                    if ($att->attachment_type_id == 7 || str_contains($note, 'المكتبة الوطنية') || str_contains($note, 'مكتبة الأسد')) $existingFilesMap['file_library_receipt'] = true;
                    if ($att->attachment_type_id == 8 || str_contains($note, 'التسجيل والمناقشة')) $existingFilesMap['file_reg_defense_doc'] = true;
                    if ($att->attachment_type_id == 9 || str_contains($note, 'خبرة')) $existingFilesMap['file_experience_cert'] = true;
                    if ($att->attachment_type_id == 10 || str_contains($note, 'عقود')) $existingFilesMap['file_private_uni_contracts'] = true;
                    if ($att->attachment_type_id == 11 || str_contains($note, 'رواتب')) $existingFilesMap['file_salary_receipts'] = true;
                    if ($att->attachment_type_id == 12 || str_contains($note, 'ICDL')) $existingFilesMap['file_icdl_cert'] = true;
                    if ($att->attachment_type_id == 13 || str_contains($note, 'إنكليزية') || str_contains($note, 'اللغة')) $existingFilesMap['file_english_cert'] = true;
                    if ($att->attachment_type_id == 14 || str_contains($note, 'رسوم') || str_contains($note, 'إيصال تسديد')) $existingFilesMap['file_fees_receipt'] = true;
                    if ($att->attachment_type_id == 15 || str_contains($note, 'جواز السفر') || str_contains($note, 'جواز')) $existingFilesMap['file_passport'] = true;
                }
            }
        }

        $isDraft = $request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1';
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;

        if ($isDraft) {
            $rules = [
                'full_name' => 'nullable|string|max:255',
                'national_id' => 'nullable|string|max:50',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'nationality_id' => 'nullable',
                'dob' => 'nullable',
                'phone' => 'nullable|string|max:50',
                'mobile' => 'nullable|string|max:50',
                'email' => 'nullable|max:255',
                'address' => 'nullable|string',
                'gender' => 'nullable|string',

                'req_no' => 'nullable|string|max:100',
                'req_date' => 'nullable',
                'work_faculty' => 'nullable|string|max:255',
                'work_department' => 'nullable|string|max:255',

                'hs_country_id' => 'nullable',
                'hs_type' => 'nullable|string',
                'hs_grant_date' => 'nullable',
                'hs_decision_no' => 'nullable|string|max:100',

                'ba_country_id' => 'nullable',
                'ba_university_id' => 'nullable',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string|max:255',
                'ba_department' => 'nullable|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable',
                'ba_grant_date' => 'nullable',
                'ba_rank' => 'nullable|string',
                'ba_decision_no' => 'nullable|string|max:100',

                'ma_country_id' => 'nullable',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_study_system' => 'nullable|string',
                'ma_study_language' => 'nullable|string|max:100',
                'ma_duration_years' => 'nullable',
                'ma_registration_date' => 'nullable',
                'ma_defense_date' => 'nullable',
                'ma_grant_date' => 'nullable',
                'ma_rank' => 'nullable|string',
                'ma_thesis_title' => 'nullable|string',
                'ma_supervisor' => 'nullable|string|max:255',
                'is_envoy' => 'nullable',
                'envoy_decision' => 'nullable|string|max:100',
                'envoy_date' => 'nullable',

                'has_syrian_experience' => 'nullable',
                'syrian_exp_years' => 'nullable',
                'syrian_exp_from' => 'nullable',
                'syrian_exp_to' => 'nullable',
                'syrian_exp_universities' => 'nullable|string',

                'residences' => 'nullable|array',

                'file_secondary_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_prev_qual_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_transcript' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_abstract' => 'nullable|file|mimes:pdf|max:2048',
                'file_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_reg_defense_doc' => 'nullable|file|mimes:pdf|max:2048',
                'file_experience_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_private_uni_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_salary_receipts' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_fees_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_passport' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $isExpYes = $request->input('has_syrian_experience') === 'yes';

            $rules = [
                // Step 1: Personal & Uni Request
                'full_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'nationality_id' => 'required|exists:lookup_countries,id',
                'national_id' => 'required|string|max:50',
                'dob' => 'required|date',
                'phone' => 'nullable|string|max:50',
                'mobile' => 'required|string|max:50',
                'email' => 'required|email:filter|max:255',
                'address' => 'required|string',
                'gender' => 'required|string|in:ذكر,أنثى',
                'req_no' => 'required|string|max:100',
                'req_date' => 'required|date',
                'work_faculty' => 'nullable|string|max:255',
                'work_department' => 'nullable|string|max:255',

                // Step 2: High School
                'hs_country_id' => 'required|exists:lookup_countries,id',
                'hs_type' => 'required|string|max:100',
                'hs_grant_date' => 'required|numeric|digits:4',
                'hs_decision_no' => 'nullable|string|max:100',

                // Step 3: Bachelor
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'required_without:ba_university_other|nullable',
                'ba_university_other' => 'required_if:ba_university_id,other|nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_specialization' => 'required|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'required|string|max:50',
                'ba_decision_no' => 'nullable|string|max:100',

                // Step 4: Foreign Master
                'ma_country_id' => 'required|exists:lookup_countries,id',
                'ma_university_other' => 'required|string|max:255',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_specialization' => 'required|string|max:255',
                'ma_study_system' => 'nullable|string|max:100',
                'ma_study_language' => 'nullable|string|max:100',
                'ma_duration_years' => 'nullable|numeric',
                'ma_registration_date' => 'nullable|date',
                'ma_defense_date' => 'nullable|date',
                'ma_grant_date' => 'required|date|before_or_equal:today',
                'ma_rank' => 'required|string|max:50',
                'ma_thesis_title' => 'required|string|max:500',
                'ma_supervisor' => 'nullable|string|max:255',
                'is_envoy' => 'nullable|boolean',
                'envoy_decision' => 'nullable|string|max:100',
                'envoy_date' => 'nullable|date',

                'has_syrian_experience' => 'required|in:yes,no',
                'syrian_exp_years' => 'nullable|numeric',
                'syrian_exp_from' => 'nullable|numeric',
                'syrian_exp_to' => 'nullable|numeric',
                'syrian_exp_universities' => 'nullable|string',

                'residences' => 'nullable|array',

                // Step 6: Attachments
                'file_secondary_cert' => !empty($existingFilesMap['file_secondary_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => !empty($existingFilesMap['file_bachelor_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_prev_qual_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_cert' => !empty($existingFilesMap['file_master_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_master_transcript' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_abstract' => !empty($existingFilesMap['file_thesis_abstract']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_reg_defense_doc' => !empty($existingFilesMap['file_reg_defense_doc']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_experience_cert' => ($isExpYes && empty($existingFilesMap['file_experience_cert'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_private_uni_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_salary_receipts' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert' => !empty($existingFilesMap['file_english_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_fees_receipt' => !empty($existingFilesMap['file_fees_receipt']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_passport' => !empty($existingFilesMap['file_passport']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'full_name.required' => 'يرجى إدخال الاسم والكنية للمرشح.',
                'father_name.required' => 'يرجى إدخال اسم الأب.',
                'mother_name.required' => 'يرجى إدخال اسم الأم.',
                'nationality_id.required' => 'يرجى اختيار جنسية المرشح.',
                'national_id.required' => 'يرجى إدخال الرقم الوطني أو رقم جواز السفر.',
                'dob.required' => 'يرجى إدخال تاريخ الميلاد.',
                'mobile.required' => 'يرجى إدخال رقم الموبايل.',
                'email.required' => 'يرجى إدخال البريد الإلكتروني.',
                'address.required' => 'يرجى إدخال عنوان الإقامة الحالي.',
                'req_no.required' => 'يرجى إدخال رقم كتاب الجامعة الخاصة.',
                'req_date.required' => 'يرجى إدخال تاريخ كتاب الجامعة الخاصة.',
                'hs_country_id.required' => 'يرجى اختيار بلد الحصول على الشهادة الثانوية.',
                'hs_type.required' => 'يرجى اختيار فرع الشهادة الثانوية.',
                'hs_grant_date.required' => 'يرجى إدخال سنة الشهادة الثانوية.',
                'ba_country_id.required' => 'يرجى اختيار بلد الإجازة الجامعية.',
                'ba_faculty.required' => 'يرجى إدخال كلية الإجازة الجامعية.',
                'ba_department.required' => 'يرجى إدخال قسم الإجازة الجامعية.',
                'ba_specialization.required' => 'يرجى إدخال اختصاص الإجازة الجامعية.',
                'ba_grant_date.required' => 'يرجى إدخال تاريخ منح الإجازة الجامعية.',
                'ba_rank.required' => 'يرجى اختيار تقدير الإجازة الجامعية.',
                'ma_country_id.required' => 'يرجى اختيار بلد دراسة الماجستير.',
                'ma_university_other.required' => 'يرجى إدخال اسم الجامعة الخارجية المانحة للماجستير.',
                'ma_faculty.required' => 'يرجى إدخال كلية درجة الماجستير.',
                'ma_department.required' => 'يرجى إدخال القسم / الاختصاص العام لشهادة الماجستير.',
                'ma_specialization.required' => 'يرجى إدخال الاختصاص الدقيق لشهادة الماجستير.',
                'ma_grant_date.required' => 'يرجى إدخال تاريخ منح شهادة الماجستير.',
                'ma_rank.required' => 'يرجى اختيار تقدير شهادة الماجستير.',
                'ma_thesis_title.required' => 'يرجى إدخال عنوان أطروحة الماجستير.',
                'has_syrian_experience.required' => 'يرجى تحديد ما إذا كان المرشح يمتلك خبرة تدريسية سنتين فأكثر داخل الجامعات السورية.',
                'file_secondary_cert.required' => 'يرجى إرفاق نسخة مصدقة عن الشهادة الثانوية.',
                'file_bachelor_cert.required' => 'يرجى إرفاق نسخة مصدقة عن الإجازة الجامعية الأولى.',
                'file_master_cert.required' => 'يرجى إرفاق نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي.',
                'file_thesis_abstract.required' => 'يرجى إرفاق ملخص أطروحة الماجستير باللغة العربية.',
                'file_reg_defense_doc.required' => 'يرجى إرفاق وثيقة تواريخ التسجيل والمباشرة والمناقشة.',
                'file_experience_cert.required' => 'يرجى إرفاق شهادة الخبرة التدريسية المثبتة داخل الجامعات السورية لمسار التدريس النظري.',
                'file_english_cert.required' => 'يرجى إرفاق وثيقة اجتياز اختبار اللغة الأجنبية (الإنكليزية).',
                'file_fees_receipt.required' => 'يرجى إرفاق إيصال تسديد رسم تعادل الماجستير الخارجي.',
                'file_passport.required' => 'يرجى إرفاق صورة جواز السفر وصفحات الأختام والإقامة ببلد الدراسة.',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update
        $fullName = $request->filled('full_name') ? $request->full_name : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة ماجستير خارجي');
        $nationalId = $request->filled('national_id') ? $request->national_id : ($isExisting && $existingApp->candidate ? $existingApp->candidate->national_id : ('TMP-' . time() . '-' . rand(100, 999)));

        $candidate = EquivalenceProfile::updateOrCreate(
            ['national_id' => $nationalId],
            [
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? '',
                'mother_name' => $request->mother_name ?? '',
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'dob' => $request->filled('dob') ? $request->dob : (optional(optional($existingApp)->candidate)->dob ?? null),
                'job_title' => $request->academic_rank ?? 'حاصل على ماجستير خارجي',
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'gender' => $request->gender ?? 'ذكر',
                'is_syrian' => ($request->nationality_id == $syriaId),
            ]
        );

        // 3. Determine Application Type & Number
        $hasExp = $request->input('has_syrian_experience') === 'yes';
        $requestType = $hasExp ? 'ماجستير خارجي - نظري' : 'ماجستير خارجي - تطبيقي';
        $appNo = $isExisting ? $existingApp->application_no : ('MA-FOR-' . rand(100000, 999999));

        $statusToSet = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';
        if ($isExisting && $existingApp->status === 'بانتظار الوثائق' && !$isDraft) {
            $statusToSet = 'بانتظار الوثائق';
        }

        if ($isExisting) {
            $application = $existingApp;
            $application->update([
                'candidate_id' => $candidate->id,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->work_faculty,
                'work_department' => $request->work_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->filled('req_date') ? $request->req_date : optional($existingApp)->new_uni_request_date,
                'status' => $statusToSet,
            ]);
        } else {
            $application = Application::create([
                'candidate_id' => $candidate->id,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->work_faculty,
                'work_department' => $request->work_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->filled('req_date') ? $request->req_date : null,
                'is_first_time' => true,
                'study_system' => $request->ma_study_system ?? 'سنوي / فصلي',
                'has_previous_degree' => true,
                'status' => $statusToSet,
                'user_id' => Auth::id(),
            ]);
        }

        // 4. Store / Update Educations
        // 4.1 High School (Secondary - Level 4)
        $existingHsEd = Education::where('application_id', $application->id)
            ->where('education_level_id', 4)
            ->first();

        $hsGrantDate = $request->filled('hs_grant_date') ? ($request->hs_grant_date . '-06-30') : optional($existingHsEd)->grant_date;
        $hsNotes = $request->filled('hs_decision_no') ? ('رقم قرار المعادلة الثانوية: ' . $request->hs_decision_no) : (optional($existingHsEd)->notes ?? '');

        Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 4,
            ],
            [
                'country_id' => ($request->filled('hs_country_id') && is_numeric($request->hs_country_id)) ? $request->hs_country_id : (optional($existingHsEd)->country_id ?? $syriaId),
                'section_name' => $request->filled('hs_type') ? $request->hs_type : (optional($existingHsEd)->section_name ?? 'علمي'),
                'grant_date' => $hsGrantDate,
                'notes' => $hsNotes,
            ]
        );

        // 4.2 Bachelor Degree (Level 1)
        $existingBaEd = Education::where('application_id', $application->id)
            ->where('education_level_id', 1)
            ->first();

        $baNotes = $request->filled('ba_decision_no') ? ('رقم قرار معادلة الإجازة: ' . $request->ba_decision_no) : (optional($existingBaEd)->notes ?? '');

        Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 1,
            ],
            [
                'country_id' => ($request->filled('ba_country_id') && is_numeric($request->ba_country_id)) ? $request->ba_country_id : (optional($existingBaEd)->country_id ?? $syriaId),
                'university_id' => ($request->filled('ba_university_id') && is_numeric($request->ba_university_id)) ? $request->ba_university_id : optional($existingBaEd)->university_id,
                'faculty' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->faculty ?? ''),
                'department' => $request->filled('ba_department') ? $request->ba_department : (optional($existingBaEd)->department ?? ''),
                'general_specialization' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('ba_department') ? $request->ba_department : (optional($existingBaEd)->exact_specialization ?? ''),
                'section_name' => $request->filled('ba_specialization') ? $request->ba_specialization : (optional($existingBaEd)->section_name ?? ''),
                'registration_date' => $request->filled('ba_registration_date') ? $request->ba_registration_date : optional($existingBaEd)->registration_date,
                'grant_date' => $request->filled('ba_grant_date') ? $request->ba_grant_date : optional($existingBaEd)->grant_date,
                'rank' => $request->filled('ba_rank') ? $request->ba_rank : (optional($existingBaEd)->rank ?? 'جيد'),
                'notes' => $baNotes,
            ]
        );

        // 4.3 Foreign Master Degree (Level 2)
        $existingMaEd = Education::where('application_id', $application->id)
            ->where('education_level_id', 2)
            ->first();

        $expNotes = '';
        if ($request->filled('syrian_exp_universities')) {
            $expNotes = 'جامعات الخبرة: ' . $request->syrian_exp_universities;
        }

        $maEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 2,
            ],
            [
                'country_id' => ($request->filled('ma_country_id') && is_numeric($request->ma_country_id)) ? $request->ma_country_id : (optional($existingMaEd)->country_id ?? null),
                'university_id' => null,
                'university_other' => $request->filled('ma_university_other') ? $request->ma_university_other : (optional($existingMaEd)->university_other ?? ''),
                'faculty' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->faculty ?? ''),
                'department' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->department ?? ''),
                'general_specialization' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('ma_specialization') ? $request->ma_specialization : (optional($existingMaEd)->exact_specialization ?? ''),
                'section_name' => $request->filled('ma_specialization') ? $request->ma_specialization : (optional($existingMaEd)->section_name ?? ''),
                'study_language' => $request->filled('ma_study_language') ? $request->ma_study_language : (optional($existingMaEd)->study_language ?? 'العربية'),
                'duration_years' => $request->filled('ma_duration_years') ? $request->ma_duration_years : (optional($existingMaEd)->duration_years ?? 2),
                'registration_date' => $request->filled('ma_registration_date') ? $request->ma_registration_date : optional($existingMaEd)->registration_date,
                'defense_date' => $request->filled('ma_defense_date') ? $request->ma_defense_date : optional($existingMaEd)->defense_date,
                'grant_date' => $request->filled('ma_grant_date') ? $request->ma_grant_date : optional($existingMaEd)->grant_date,
                'rank' => $request->filled('ma_rank') ? $request->ma_rank : (optional($existingMaEd)->rank ?? 'جيد جداً'),
                'thesis_title' => $request->filled('ma_thesis_title') ? $request->ma_thesis_title : (optional($existingMaEd)->thesis_title ?? 'شهادة الماجستير الخارجي'),
                'supervisor_name' => $request->filled('ma_supervisor') ? $request->ma_supervisor : optional($existingMaEd)->supervisor_name,
                'envoy_decision' => $request->filled('envoy_decision') ? $request->envoy_decision : optional($existingMaEd)->envoy_decision,
                'envoy_date' => $request->filled('envoy_date') ? $request->envoy_date : optional($existingMaEd)->envoy_date,
                'experience_from_year' => $request->filled('syrian_exp_from') ? $request->syrian_exp_from : optional($existingMaEd)->experience_from_year,
                'experience_to_year' => $request->filled('syrian_exp_to') ? $request->syrian_exp_to : optional($existingMaEd)->experience_to_year,
                'notes' => $expNotes ?: optional($existingMaEd)->notes,
            ]
        );

        // 4.4 Store / Update Education Residences (حركات الإقامة والدخول والخروج)
        if ($request->has('residences') && is_array($request->residences)) {
            // Delete old residences and re-create
            EducationResidence::where('education_id', $maEd->id)->delete();

            foreach ($request->residences as $resData) {
                if (!empty($resData['entry_date']) && !empty($resData['exit_date'])) {
                    EducationResidence::create([
                        'education_id' => $maEd->id,
                        'page_number' => $resData['page_number'] ?? null,
                        'entry_airport' => $resData['entry_airport'] ?? null,
                        'entry_date' => $resData['entry_date'],
                        'exit_airport' => $resData['exit_airport'] ?? null,
                        'exit_date' => $resData['exit_date'],
                        'stamp_details' => ($resData['country_name'] ?? '') . ' - ' . ($resData['university_name'] ?? ''),
                    ]);
                }
            }
        }

        // 5. Attachment File Uploads
        $fileInputs = [
            'file_secondary_cert' => ['id' => 1, 'notes' => 'نسخة مصدقة عن الشهادة الثانوية'],
            'file_bachelor_cert' => ['id' => 2, 'notes' => 'نسخة مصدقة عن الإجازة الجامعية الأولى'],
            'file_prev_qual_cert' => ['id' => 3, 'notes' => 'الشهادة قبل المؤهل العلمي الأخير'],
            'file_master_cert' => ['id' => 4, 'notes' => 'نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي'],
            'file_master_transcript' => ['id' => 5, 'notes' => 'كشف علامات الماجستير'],
            'file_thesis_abstract' => ['id' => 6, 'notes' => 'ملخص عن الأطروحة باللغة العربية'],
            'file_library_receipt' => ['id' => 7, 'notes' => 'إيصال إيداع الأطروحة لدى المكتبة الوطنية'],
            'file_reg_defense_doc' => ['id' => 8, 'notes' => 'وثيقة تواريخ التسجيل والمباشرة والمناقشة'],
            'file_experience_cert' => ['id' => 9, 'notes' => 'شهادة الخبرة التدريسية داخل سوريا'],
            'file_private_uni_contracts' => ['id' => 10, 'notes' => 'عقود التدريس مع الجامعة الخاصة'],
            'file_salary_receipts' => ['id' => 11, 'notes' => 'إيصالات الرواتب من الجامعة'],
            'file_icdl_cert' => ['id' => 12, 'notes' => 'شهادة مهارات الحاسوب (ICDL)'],
            'file_english_cert' => ['id' => 13, 'notes' => 'وثيقة اجتياز اختبار اللغة الإنكليزية'],
            'file_fees_receipt' => ['id' => 14, 'notes' => 'إيصال تسديد رسم تعادل الماجستير الخارجي (100,000 ل.س)'],
            'file_passport' => ['id' => 15, 'notes' => 'صورة جواز السفر وصفحات الإقامة والأختام'],
        ];

        foreach ($fileInputs as $inputKey => $meta) {
            $typeId = $meta['id'];
            $note = $meta['notes'];
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $cleanCandidate = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name)));
                $filename = 'MA_FOR_' . $appNo . '_Type' . $typeId . '_' . ($cleanCandidate ?: 'Candidate') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');

                EducationAttachment::updateOrCreate(
                    [
                        'education_id' => $maEd->id,
                        'attachment_type_id' => $typeId,
                    ],
                    [
                        'file_path' => $path,
                        'notes' => $note,
                    ]
                );
            }
        }

        if ($request->hasFile('file_other_attachments')) {
            $file = $request->file('file_other_attachments');
            $filename = 'MA_FOR_' . $appNo . '_Other_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('attachments', $filename, 'public');

            EducationAttachment::create([
                'education_id' => $maEd->id,
                'attachment_type_id' => 16,
                'file_path' => $path,
                'notes' => 'مرفقات ووثائق داعمة أخرى',
            ]);
        }

        if ($isDraft) {
            return redirect()->route('university.dashboard')
                ->with('success', '💾 تم حفظ مسودة معاملة الماجستير الخارجي بنجاح برقم: ' . $appNo . '. يمكنك العودة لتعديلها أو استكمالها في أي وقت.');
        }

        // Notify Admin and University if documents were updated from awaiting documents
        if ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة'])) {
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة الخاصة';
            $candidateName = $candidate->full_name;

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة لمعاملة الماجستير الخارجي رقم (#{$appNo}) للمرشح ({$candidateName}). يرجى تدقيق ومراجعة المعاملة لتحديث حالتها.",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? (User::where('id', '!=', Auth::id())->value('id') ?? 1);

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => $systemAdminId,
                'message' => "✅ [تأكيد استكمال التعديل]: تم استلام التعديلات والوثائق المستكملة لمعاملة الماجستير الخارجي رقم (#{$appNo}) للمرشح ({$candidateName}) بنجاح من قبل جامعة ({$uniName}). المعاملة الآن قيد المراجعة والتدقيق من قبل وزارة التعليم العالي لتحديث حالتها.",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم إشعار وزارة التعليم العالي لتدقيق التعديلات وتحديث حالة المعاملة.')
                ->with('submitted_app_id', $application->id)
                ->with('submitted_app_no', $appNo);
        }

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات معاملة الماجستير الخارجي رقم: ' : 'تم تقديم معاملة الماجستير الخارجي (' . $requestType . ') بنجاح للطلب رقم: ') . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
    }

    public function showFacultyPermissionWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $countries = LookupCountry::all();
        $universities = LookupUniversity::all();
        $govUniversities = LookupUniversity::whereIn('name', [
            'جامعة دمشق',
            'جامعة حلب',
            'جامعة تشرين',
            'جامعة البعث',
            'جامعة الفرات',
            'جامعة حماة',
            'جامعة طرطوس'
        ])->get();

        if ($govUniversities->isEmpty()) {
            $govUniversities = $universities;
        }

        $unreadNotifications = $this->getUnreadNotifications();
        $draft = null;
        if ($request->has('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.attachments.attachmentType'])
                ->first();
        }

        return view('university.apply.faculty_permission', compact(
            'countries',
            'universities',
            'govUniversities',
            'unreadNotifications',
            'draft'
        ));
    }

    public function submitFacultyPermissionWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $uniId = Auth::user()->university_id;
        $existingApp = null;
        if ($request->filled('draft_id')) {
            $existingApp = Application::where('id', $request->draft_id)
                ->with(['educations.attachments'])
                ->first();
        }
        $isExisting = ($existingApp !== null);

        // Check which files already exist in DB
        $existingFilesMap = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->attachment_type_id == 7 || ($att->notes && (str_contains($att->notes, 'طلب تقويم') || str_contains($att->notes, 'كتاب ترشيح') || str_contains($att->notes, 'كتاب الجامعة')))) {
                        $existingFilesMap['file_uni_request'] = true;
                    }
                    if ($att->attachment_type_id == 8 || ($att->notes && (str_contains($att->notes, 'شهادة الدكتوراه') || str_contains($att->notes, 'مصدقة الدكتوراه')))) {
                        $existingFilesMap['file_phd_cert'] = true;
                    }
                    if ($att->attachment_type_id == 9 || ($att->notes && (str_contains($att->notes, 'بيان وضع') || str_contains($att->notes, 'بطاقة ذاتية')))) {
                        $existingFilesMap['file_service_statement'] = true;
                    }
                    if ($att->attachment_type_id == 10 || ($att->notes && (str_contains($att->notes, 'إيصال') || str_contains($att->notes, '125,000') || str_contains($att->notes, 'رسم تعادل')))) {
                        $existingFilesMap['file_payment'] = true;
                    }
                    if ($att->attachment_type_id == 11 || ($att->notes && (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'الهوية الشخصية')))) {
                        $existingFilesMap['file_id_card'] = true;
                    }
                }
            }
        }

        $isDraft = $request->input('action') === 'save_draft';
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        if ($isDraft) {
            $rules = [
                'full_name' => 'nullable|string|max:255',
                'national_id' => 'nullable|string|max:50',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'nationality_id' => 'nullable',
                'dob' => 'nullable',
                'phone' => 'nullable|string|max:50',
                'mobile' => 'nullable|string|max:50',
                'email' => 'nullable|max:255',
                'address' => 'nullable|string',
                'gender' => 'nullable|string',
                
                'gov_university_id' => 'nullable',
                'gov_university_other' => 'nullable|string|max:255',
                'gov_faculty' => 'nullable|string|max:255',
                'gov_department' => 'nullable|string|max:255',
                'academic_rank' => 'nullable|string|max:100',

                'phd_university_id' => 'nullable',
                'phd_university_other' => 'nullable|string|max:255',
                'phd_faculty' => 'nullable|string|max:255',
                'phd_department' => 'nullable|string|max:255',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_grant_date' => 'nullable',

                'has_master' => 'nullable',
                'ma_university_id' => 'nullable',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_grant_date' => 'nullable',

                'req_no' => 'nullable|string|max:100',
                'req_date' => 'nullable',
                'work_faculty' => 'nullable|string|max:255',
                'work_department' => 'nullable|string|max:255',

                'file_id_card' => 'nullable|file|mimes:pdf|max:2048',
                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_service_statement' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $rules = [
                // Step 1: Personal Info & Uni Request Info
                'full_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'nationality_id' => 'required|exists:lookup_countries,id',
                'national_id' => 'required|string|max:50',
                'dob' => 'required|date',
                'phone' => 'nullable|string|max:50',
                'mobile' => 'required|string|max:50',
                'email' => 'required|email:filter|max:255',
                'address' => 'required|string',
                'gender' => 'required|string|in:ذكر,أنثى',

                'req_no' => 'required|string|max:100',
                'req_date' => 'required|date',
                'work_faculty' => 'nullable|string|max:255',
                'work_department' => 'nullable|string|max:255',

                // Step 2: Public University Employment
                'gov_university_id' => 'required_without:gov_university_other|nullable',
                'gov_university_other' => 'required_if:gov_university_id,other|nullable|string|max:255',
                'gov_faculty' => 'required|string|max:255',
                'gov_department' => 'required|string|max:255',
                'academic_rank' => 'required|string|max:100',

                // Step 3: PhD info
                'phd_university_id' => 'required_without:phd_university_other|nullable',
                'phd_university_other' => 'required_if:phd_university_id,other|nullable|string|max:255',
                'phd_faculty' => 'nullable|string|max:255',
                'phd_department' => 'nullable|string|max:255',
                'phd_specialization' => 'required|string|max:255',
                'phd_grant_date' => 'required|date|before_or_equal:today',

                // Optional Master's
                'has_master' => 'nullable|boolean',
                'ma_university_id' => 'nullable',
                'ma_university_other' => 'required_if:ma_university_id,other|nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_grant_date' => 'nullable|date',

                // Step 4: Required Attachments (5 official requirements)
                'file_id_card' => !empty($existingFilesMap['file_id_card']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_uni_request' => !empty($existingFilesMap['file_uni_request']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_service_statement' => !empty($existingFilesMap['file_service_statement']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_phd_cert' => !empty($existingFilesMap['file_phd_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_payment' => !empty($existingFilesMap['file_payment']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'full_name.required' => 'يرجى إدخال الاسم والكنية للمرشح.',
                'father_name.required' => 'يرجى إدخال اسم الأب.',
                'mother_name.required' => 'يرجى إدخال اسم الأم.',
                'national_id.required' => 'يرجى إدخال الرقم الوطني للمرشح.',
                'dob.required' => 'يرجى إدخال تاريخ الميلاد.',
                'mobile.required' => 'يرجى إدخال رقم الموبايل.',
                'email.required' => 'يرجى إدخال البريد الإلكتروني.',
                'address.required' => 'يرجى إدخال عنوان الإقامة الحالي.',
                'req_no.required' => 'يرجى إدخال رقم كتاب الجامعة الخاصة.',
                'req_date.required' => 'يرجى إدخال تاريخ كتاب الجامعة الخاصة.',
                'gov_university_id.required_without' => 'يرجى اختيار الجامعة الحكومية التي ينتمي إليها عضو الهيئة التدريسية.',
                'gov_faculty.required' => 'يرجى إدخال الكلية في الجامعة الحكومية.',
                'gov_department.required' => 'يرجى إدخال القسم في الجامعة الحكومية.',
                'academic_rank.required' => 'يرجى إدخال الرتبة الأكاديمية (مدرس / أستاذ مساعد / أستاذ).',
                'phd_specialization.required' => 'يرجى إدخال الاختصاص الدقيق لشهادة الدكتوراه.',
                'phd_grant_date.required' => 'يرجى إدخال سنة/تاريخ منح درجة الدكتوراه.',
                'file_id_card.required' => 'يرجى إرفاق صورة عن الهوية الشخصية.',
                'file_uni_request.required' => 'يرجى إرفاق طلب التقويم / كتاب ترشيح الجامعة الخاصة.',
                'file_service_statement.required' => 'يرجى إرفاق بيان الوضع أو البطاقة الذاتية من الجامعة الحكومية.',
                'file_phd_cert.required' => 'يرجى إرفاق نسخة مصدقة أصولاً عن شهادة الدكتوراه.',
                'file_payment.required' => 'يرجى إرفاق إيصال تسديد رسم السماح بالتدريس (125,000 ل.س).',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update
        $fullName = $request->filled('full_name') ? $request->full_name : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة طلب سماح بالتدريس');
        $nationalId = $request->filled('national_id') ? $request->national_id : ($isExisting && $existingApp->candidate ? $existingApp->candidate->national_id : ('TMP-' . time() . '-' . rand(100, 999)));

        $candidate = EquivalenceProfile::updateOrCreate(
            ['national_id' => $nationalId],
            [
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? '',
                'mother_name' => $request->mother_name ?? '',
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'dob' => $request->filled('dob') ? $request->dob : (optional(optional($existingApp)->candidate)->dob ?? null),
                'job_title' => $request->academic_rank ?? 'عضو هيئة تدريسية',
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'gender' => $request->gender ?? 'ذكر',
                'is_syrian' => true,
            ]
        );

        // 3. Application Creation / Update
        $requestType = 'عضو هيئة تدريسية';
        $appNo = $isExisting ? $existingApp->application_no : ('FAC-' . rand(100000, 999999));

        $statusToSet = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';
        if ($isExisting && $existingApp->status === 'بانتظار الوثائق' && !$isDraft) {
            $statusToSet = 'بانتظار الوثائق';
        }

        if ($isExisting) {
            $application = $existingApp;
            $application->update([
                'candidate_id' => $candidate->id,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->work_faculty,
                'work_department' => $request->work_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->filled('req_date') ? $request->req_date : optional($existingApp)->new_uni_request_date,
                'status' => $statusToSet,
            ]);
        } else {
            $application = Application::create([
                'candidate_id' => $candidate->id,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->work_faculty,
                'work_department' => $request->work_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->filled('req_date') ? $request->req_date : null,
                'is_first_time' => true,
                'study_system' => 'سنوي / فصلي',
                'has_previous_degree' => true,
                'status' => $statusToSet,
                'user_id' => Auth::id(),
            ]);
        }

        // 4. Store / Update Educations
        // Public Faculty Employment
        $existingGovEd = Education::where('application_id', $application->id)
            ->where('thesis_title', 'عضو هيئة تدريسية في جامعة حكومية')
            ->first();

        $govUniEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'thesis_title' => 'عضو هيئة تدريسية في جامعة حكومية',
            ],
            [
                'education_level_id' => 3,
                'country_id' => $syriaId,
                'university_id' => ($request->filled('gov_university_id') && is_numeric($request->gov_university_id)) ? $request->gov_university_id : optional($existingGovEd)->university_id,
                'university_other' => $request->gov_university_other ?? optional($existingGovEd)->university_other,
                'faculty' => $request->filled('gov_faculty') ? $request->gov_faculty : (optional($existingGovEd)->faculty ?? ''),
                'department' => $request->filled('gov_department') ? $request->gov_department : (optional($existingGovEd)->department ?? ''),
                'general_specialization' => $request->filled('gov_faculty') ? $request->gov_faculty : (optional($existingGovEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('gov_department') ? $request->gov_department : (optional($existingGovEd)->exact_specialization ?? ''),
                'section_name' => $request->filled('gov_department') ? $request->gov_department : (optional($existingGovEd)->section_name ?? ''),
                'rank' => $request->academic_rank ?? optional($existingGovEd)->rank,
            ]
        );

        // PhD Degree
        $existingPhdEd = Education::where('application_id', $application->id)
            ->where('thesis_title', 'شهادة الدكتوراه')
            ->first();

        $phdEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'thesis_title' => 'شهادة الدكتوراه',
            ],
            [
                'education_level_id' => 3,
                'country_id' => $syriaId,
                'university_id' => ($request->filled('phd_university_id') && is_numeric($request->phd_university_id)) ? $request->phd_university_id : optional($existingPhdEd)->university_id,
                'university_other' => $request->phd_university_other ?? optional($existingPhdEd)->university_other,
                'faculty' => $request->filled('phd_faculty') ? $request->phd_faculty : (optional($existingPhdEd)->faculty ?? ''),
                'department' => $request->filled('phd_department') ? $request->phd_department : (optional($existingPhdEd)->department ?? ''),
                'general_specialization' => $request->filled('phd_faculty') ? $request->phd_faculty : (optional($existingPhdEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('phd_specialization') ? $request->phd_specialization : ($request->filled('phd_department') ? $request->phd_department : (optional($existingPhdEd)->exact_specialization ?? '')),
                'section_name' => $request->filled('phd_specialization') ? $request->phd_specialization : (optional($existingPhdEd)->section_name ?? ''),
                'grant_date' => $request->filled('phd_grant_date') ? $request->phd_grant_date : optional($existingPhdEd)->grant_date,
            ]
        );

        // Optional Master's Degree
        $existingMaEd = Education::where('application_id', $application->id)
            ->where('thesis_title', 'شهادة الماجستير')
            ->first();

        if ($request->filled('has_master') && $request->has_master) {
            Education::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'thesis_title' => 'شهادة الماجستير',
                ],
                [
                    'education_level_id' => 2,
                    'country_id' => $syriaId,
                    'university_id' => ($request->filled('ma_university_id') && is_numeric($request->ma_university_id)) ? $request->ma_university_id : optional($existingMaEd)->university_id,
                    'university_other' => $request->ma_university_other ?? optional($existingMaEd)->university_other,
                    'faculty' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->faculty ?? ''),
                    'department' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->department ?? ''),
                    'general_specialization' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->general_specialization ?? ''),
                    'exact_specialization' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->exact_specialization ?? ''),
                    'section_name' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->section_name ?? ''),
                    'grant_date' => $request->filled('ma_grant_date') ? $request->ma_grant_date : optional($existingMaEd)->grant_date,
                ]
            );
        }

        // 5. Attachment File Uploads
        $fileInputs = [
            'file_id_card' => ['id' => 11, 'notes' => 'صورة عن الهوية الشخصية'],
            'file_uni_request' => ['id' => 7, 'notes' => 'طلب تقويم / كتاب ترشيح الجامعة الخاصة'],
            'file_service_statement' => ['id' => 9, 'notes' => 'بيان وضع أو بطاقة ذاتية من الجامعة الحكومية'],
            'file_phd_cert' => ['id' => 8, 'notes' => 'نسخة مصدقة أصولاً عن شهادة الدكتوراه'],
            'file_payment' => ['id' => 10, 'notes' => 'إيصال تسديد رسم السماح بالتدريس (125,000 ل.س)'],
            'file_ma_cert' => ['id' => 12, 'notes' => 'نسخة مصدقة عن شهادة الماجستير'],
        ];

        foreach ($fileInputs as $inputKey => $meta) {
            $typeId = $meta['id'];
            $note = $meta['notes'];
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $cleanCandidate = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name)));
                $filename = 'FAC_' . $appNo . '_Type' . $typeId . '_' . ($cleanCandidate ?: 'Candidate') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');

                EducationAttachment::updateOrCreate(
                    [
                        'education_id' => $phdEd->id,
                        'attachment_type_id' => $typeId,
                    ],
                    [
                        'file_path' => $path,
                        'notes' => $note,
                    ]
                );
            }
        }

        if ($request->hasFile('file_other_attachments')) {
            $file = $request->file('file_other_attachments');
            $filename = 'FAC_' . $appNo . '_Other_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('attachments', $filename, 'public');

            EducationAttachment::create([
                'education_id' => $phdEd->id,
                'attachment_type_id' => 7,
                'file_path' => $path,
                'notes' => 'مرفقات ووثائق داعمة أخرى',
            ]);
        }

        if ($isDraft) {
            return redirect()->route('university.dashboard')
                ->with('success', '💾 تم حفظ المسودة بنجاح برقم: ' . $appNo . '. يمكنك العودة لتعديلها أو استكمالها في أي وقت.');
        }

        // Notify Admin and University if documents were updated from awaiting documents
        if ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة'])) {
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة الخاصة';
            $candidateName = $candidate->full_name;

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة لمعاملة السماح بالتدريس رقم (#{$appNo}) للمرشح ({$candidateName}). يرجى تدقيق ومراجعة المعاملة لتحديث حالتها.",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? (User::where('id', '!=', Auth::id())->value('id') ?? 1);

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => $systemAdminId,
                'message' => "✅ [تأكيد استكمال التعديل]: تم استلام التعديلات والوثائق المستكملة لمعاملة السماح بالتدريس رقم (#{$appNo}) للمرشح ({$candidateName}) بنجاح من قبل جامعة ({$uniName}). المعاملة الآن قيد المراجعة والتدقيق من قبل وزارة التعليم العالي لتحديث حالتها.",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم إشعار وزارة التعليم العالي لتدقيق التعديلات وتحديث حالة المعاملة.')
                ->with('submitted_app_id', $application->id)
                ->with('submitted_app_no', $appNo);
        }

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات معاملة السماح رقم: ' : 'تم تقديم معاملة السماح بالتدريس بنجاح للطلب رقم: ') . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
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

    public function lookupCandidate(Request $request)
    {
        $nationalId = $request->query('national_id');
        if (!$nationalId) {
            return response()->json(['success' => false, 'message' => 'يرجى إدخال الرقم الوطني']);
        }

        $profile = EquivalenceProfile::where('national_id', $nationalId)
            ->with(['applications.educations.level', 'applications.educations.country', 'applications.educations.university'])
            ->first();

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على أي مرشح مسجل سابقاً بهذا الرقم الوطني']);
        }

        $candidateData = [
            'id' => $profile->id,
            'full_name' => $profile->full_name,
            'father_name' => $profile->father_name,
            'mother_name' => $profile->mother_name,
            'national_id' => $profile->national_id,
            'dob' => $profile->dob,
            'job_title' => $profile->job_title,
            'nationality_id' => $profile->nationality_id,
            'phone' => $profile->phone,
            'mobile' => $profile->mobile,
            'email' => $profile->email,
            'address' => $profile->address,
            'gender' => $profile->gender,
            'is_syrian' => $profile->is_syrian,
        ];

        $hsData = null;
        $baData = null;
        $maData = null;
        $phdData = null;

        // Search candidate's past educations
        foreach ($profile->applications as $app) {
            foreach ($app->educations as $ed) {
                $lvl = optional($ed->level)->name ?? '';
                if (str_contains($lvl, 'ثانوية') || $ed->education_level_id == 4) {
                    $grantYr = $ed->grant_date;
                    if ($grantYr && strlen($grantYr) > 4) {
                        $grantYr = substr($grantYr, 0, 4);
                    }
                    $hsData = [
                        'country_id' => $ed->country_id,
                        'type' => $ed->section_name,
                        'grant_date' => $grantYr,
                        'decision_no' => $ed->notes ? preg_replace('/.*رقم قرار المعادلة الثانوية:\s*([^\|]+).*/u', '$1', $ed->notes) : '',
                    ];
                } elseif (str_contains($lvl, 'إجازة') || $ed->education_level_id == 1) {
                    $baData = [
                        'country_id' => $ed->country_id,
                        'university_id' => $ed->university_id,
                        'university_other' => $ed->section_name,
                        'faculty' => $ed->general_specialization,
                        'department' => $ed->exact_specialization,
                        'specialization' => $ed->section_name,
                        'registration_date' => $ed->registration_date,
                        'grant_date' => $ed->grant_date,
                        'rank' => $ed->rank,
                        'decision_no' => $ed->notes ? preg_replace('/.*رقم قرار معادلة الإجازة:\s*([^\|]+).*/u', '$1', $ed->notes) : '',
                    ];
                } elseif (str_contains($lvl, 'ماجستير') || $ed->education_level_id == 2) {
                    $maData = [
                        'country_id' => $ed->country_id,
                        'university_id' => $ed->university_id,
                        'faculty' => $ed->general_specialization,
                        'department' => $ed->exact_specialization,
                        'specialization' => $ed->section_name,
                        'registration_date' => $ed->registration_date,
                        'defense_date' => $ed->defense_date,
                        'grant_date' => $ed->grant_date,
                        'rank' => $ed->rank,
                        'supervisor' => $ed->supervisor_name,
                        'thesis_title' => $ed->thesis_title,
                    ];
                } elseif (str_contains($lvl, 'دكتوراه') || $ed->education_level_id == 3) {
                    $phdData = [
                        'country_id' => $ed->country_id,
                        'university_id' => $ed->university_id,
                        'faculty' => $ed->general_specialization,
                        'department' => $ed->exact_specialization,
                        'specialization' => $ed->section_name,
                        'registration_date' => $ed->registration_date,
                        'defense_date' => $ed->defense_date,
                        'grant_date' => $ed->grant_date,
                        'rank' => $ed->rank,
                        'supervisor' => $ed->supervisor_name,
                        'thesis_title' => $ed->thesis_title,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'candidate' => $candidateData,
            'high_school' => $hsData,
            'bachelor' => $baData,
            'master' => $maData,
            'doctorate' => $phdData,
        ]);
    }
}



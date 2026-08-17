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
use App\Models\SiteSetting;
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

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وثائق]: قامت جامعة ({$uniName}) باستكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}).",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح وإشعار الوزارة.')
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

        return view('university.apply.syrian_doctorate', compact(
            'countries', 
            'universities', 
            'educationLevels', 
            'syriaId',
            'notifications',
            'draft'
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
                'ba_department' => 'required|string|max:255',
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
                'ma_department' => 'required|string|max:255',
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
                'phd_department' => 'required|string|max:255',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_registration_date' => 'required|date',
                'phd_defense_date' => 'required|date|after:phd_registration_date',
                'phd_grant_date' => 'required|date|after:phd_defense_date|before_or_equal:today',
                'phd_rank' => 'required|string|max:100',
                'phd_supervisor' => 'required|string|max:255',
                'phd_thesis_title' => 'required|string',
                
                // Experience
                'has_experience' => 'nullable|boolean',
                'exp_place' => 'nullable|required_if:has_experience,1|string|max:255',
                'exp_from_year' => 'nullable|required_if:has_experience,1|date',
                'exp_to_year' => 'nullable|required_if:has_experience,1|date|after_or_equal:exp_from_year',

                // University Request Details
                'req_no' => 'required|regex:/^[0-9]+$/',
                'req_date' => 'required|date',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // Step 6: Attachments Upload (CV + Uni request + Certificates)
                'file_hs_cert' => $fileRule,
                'file_ba_cert' => $fileRule,
                'file_diploma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => $fileRule,
                'file_phd_cert' => $fileRule,
                'file_thesis_summary' => $fileRule,
                'file_phd_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
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
                'req_no.regex' => 'رقم كتاب طلب التقويم الصادر عن الجامعة يجب أن يتكون من أرقام فقط.',
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

            $isFirstTime = $request->has('is_first_time') ? 1 : 0;

            $application->update([
                'candidate_id' => $profile->id,
                'request_type' => $requestType,
                'work_faculty' => $request->phd_faculty,
                'work_department' => $request->phd_department,
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
                'work_faculty' => $request->phd_faculty,
                'work_department' => $request->phd_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'study_system' => 'فصلي',
                'has_previous_degree' => $request->boolean('has_previous_degree'),
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
            'experience_from_year' => $request->exp_from_year,
            'experience_to_year' => $request->exp_to_year,
            'notes' => $request->exp_place ? 'مكان الخبرة التدريسية: ' . $request->exp_place : null,
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

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وثائق]: قامت جامعة ({$uniName}) باستكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}).",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح وإشعار الوزارة.')
                ->with('submitted_app_id', $application->id)
                ->with('submitted_app_no', $appNo);
        }

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات الطلب رقم: ' : 'تم تقديم معاملة (' . $requestType . ') بنجاح للطلب رقم: ') . $appNo)
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
                'nationality_id' => 'nullable|exists:lookup_countries,id',
                'dob' => 'nullable|date',
                'phone' => 'nullable|string|max:50',
                'mobile' => 'nullable|string|max:50',
                'email' => 'nullable|email:filter|max:255',
                'address' => 'nullable|string',
                'gender' => 'nullable|string|in:ذكر,أنثى',
                
                'gov_university_id' => 'nullable|exists:lookup_universities,id',
                'gov_university_other' => 'nullable|string|max:255',
                'gov_faculty' => 'nullable|string|max:255',
                'gov_department' => 'nullable|string|max:255',
                'academic_rank' => 'nullable|string|max:100',

                'phd_university_id' => 'nullable|exists:lookup_universities,id',
                'phd_university_other' => 'nullable|string|max:255',
                'phd_faculty' => 'nullable|string|max:255',
                'phd_department' => 'nullable|string|max:255',
                'phd_grant_date' => 'nullable|date',

                'has_master' => 'nullable|boolean',
                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_grant_date' => 'nullable|date',

                'req_no' => 'nullable|string|max:100',
                'req_date' => 'nullable|date',
                'work_faculty' => 'nullable|string|max:255',
                'work_department' => 'nullable|string|max:255',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_service_statement' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_id_card' => 'nullable|file|mimes:pdf|max:2048',
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
                'gov_university_id' => 'required_without:gov_university_other|nullable|exists:lookup_universities,id',
                'gov_university_other' => 'required_without:gov_university_id|nullable|string|max:255',
                'gov_faculty' => 'required|string|max:255',
                'gov_department' => 'required|string|max:255',
                'academic_rank' => 'required|string|max:100',

                // Step 3: PhD info
                'phd_university_id' => 'required_without:phd_university_other|nullable|exists:lookup_universities,id',
                'phd_university_other' => 'required_without:phd_university_id|nullable|string|max:255',
                'phd_faculty' => 'required|string|max:255',
                'phd_department' => 'required|string|max:255',
                'phd_grant_date' => 'required|date|before_or_equal:today',

                // Optional Master's
                'has_master' => 'nullable|boolean',
                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_grant_date' => 'nullable|date',

                // Step 4: Required Attachments (Official Page 6 requirements)
                'file_uni_request' => !empty($existingFilesMap['file_uni_request']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_phd_cert' => !empty($existingFilesMap['file_phd_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_service_statement' => !empty($existingFilesMap['file_service_statement']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_payment' => !empty($existingFilesMap['file_payment']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_id_card' => !empty($existingFilesMap['file_id_card']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
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
                'phd_faculty.required' => 'يرجى إدخال كلية درجة الدكتوراه.',
                'phd_department.required' => 'يرجى إدخال اختصاص درجة الدكتوراه.',
                'phd_grant_date.required' => 'يرجى إدخال سنة/تاريخ منح درجة الدكتوراه.',
                'file_uni_request.required' => 'يرجى إرفاق طلب التقويم / كتاب ترشيح الجامعة الخاصة.',
                'file_phd_cert.required' => 'يرجى إرفاق نسخة مصدقة عن شهادة الدكتوراه.',
                'file_service_statement.required' => 'يرجى إرفاق بيان الوضع أو البطاقة الذاتية من الجامعة الحكومية.',
                'file_payment.required' => 'يرجى إرفاق إيصال تسديد رسم التعادل (125,000 ل.س).',
                'file_id_card.required' => 'يرجى إرفاق صورة عن الهوية الشخصية.',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update
        $fullName = $request->full_name ?: ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة جديدة');
        $nationalId = $request->national_id ?: ($isExisting && $existingApp->candidate ? $existingApp->candidate->national_id : ('DRAFT_' . time() . '_' . rand(100, 999)));

        $candidate = EquivalenceProfile::updateOrCreate(
            ['national_id' => $nationalId],
            [
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? '',
                'mother_name' => $request->mother_name ?? '',
                'nationality_id' => $request->nationality_id ?? $syriaId,
                'dob' => $request->dob,
                'job_title' => $request->academic_rank ?? 'عضو هيئة تدريسية',
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'gender' => $request->gender,
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
                'new_uni_request_date' => $request->req_date,
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
                'new_uni_request_date' => $request->req_date,
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
                'university_id' => $request->gov_university_id ?? optional($existingGovEd)->university_id,
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
                'university_id' => $request->phd_university_id ?? optional($existingPhdEd)->university_id,
                'university_other' => $request->phd_university_other ?? optional($existingPhdEd)->university_other,
                'faculty' => $request->filled('phd_faculty') ? $request->phd_faculty : (optional($existingPhdEd)->faculty ?? ''),
                'department' => $request->filled('phd_department') ? $request->phd_department : (optional($existingPhdEd)->department ?? ''),
                'general_specialization' => $request->filled('phd_faculty') ? $request->phd_faculty : (optional($existingPhdEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('phd_department') ? $request->phd_department : (optional($existingPhdEd)->exact_specialization ?? ''),
                'section_name' => $request->filled('phd_department') ? $request->phd_department : (optional($existingPhdEd)->section_name ?? ''),
                'grant_date' => $request->phd_grant_date ?? optional($existingPhdEd)->grant_date,
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
                    'university_id' => $request->ma_university_id ?? optional($existingMaEd)->university_id,
                    'university_other' => $request->ma_university_other ?? optional($existingMaEd)->university_other,
                    'faculty' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->faculty ?? ''),
                    'department' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->department ?? ''),
                    'general_specialization' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->general_specialization ?? ''),
                    'exact_specialization' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->exact_specialization ?? ''),
                    'section_name' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->section_name ?? ''),
                    'grant_date' => $request->ma_grant_date ?? optional($existingMaEd)->grant_date,
                ]
            );
        }

        // 5. Attachment File Uploads
        $fileInputs = [
            'file_uni_request' => ['id' => 7, 'notes' => 'طلب تقويم / كتاب ترشيح الجامعة الخاصة'],
            'file_phd_cert' => ['id' => 8, 'notes' => 'نسخة مصدقة أصولاً عن شهادة الدكتوراه'],
            'file_service_statement' => ['id' => 9, 'notes' => 'بيان وضع أو بطاقة ذاتية من الجامعة الحكومية'],
            'file_payment' => ['id' => 10, 'notes' => 'إيصال تسديد رسم تعادل (125,000 ل.س)'],
            'file_id_card' => ['id' => 11, 'notes' => 'صورة عن الهوية الشخصية'],
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

        // Notify Admin if documents were updated from awaiting documents
        if ($isExisting && $existingApp->status === 'بانتظار الوثائق') {
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة الخاصة';
            $candidateName = $candidate->full_name;

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وثائق]: قامت جامعة ({$uniName}) باستكمال وتعديل الوثائق والبيانات المطلوبة لمعاملة السماح بالتدريس رقم (#{$appNo}) للمرشح ({$candidateName}).",
                'is_read' => false,
            ]);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح وإشعار الوزارة.')
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

    /**
     * Show Wizard for Research Center Scientist (باحث في مراكز البحوث)
     */
    public function showResearchScientistWizard($draftId = null)
    {
        $countries = LookupCountry::all();
        $universities = LookupUniversity::all();
        $attachmentTypes = LookupAttachmentType::all();
        $syriaId = LookupCountry::where('name', 'سوريا')->orWhere('name', 'الجمهورية العربية السورية')->value('id') ?? 1;

        $draft = null;
        if ($draftId) {
            $draft = Application::with(['candidate', 'educations.attachments', 'educations.level', 'educations.university', 'workUniversity'])
                ->where('id', $draftId)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('university.apply.research_scientist', compact(
            'countries',
            'universities',
            'attachmentTypes',
            'syriaId',
            'draft'
        ));
    }

    /**
     * Submit / Save Draft for Research Center Scientist Wizard
     */
    public function submitResearchScientistWizard(Request $request, $draftId = null)
    {
        $isDraft = $request->boolean('is_draft');
        $isExisting = $draftId || $request->filled('draft_id');
        $existingAppId = $draftId ?: $request->input('draft_id');

        $existingApp = null;
        if ($isExisting) {
            $existingApp = Application::with('educations.attachments')->where('id', $existingAppId)->where('user_id', Auth::id())->first();
        }

        $syriaId = LookupCountry::where('name', 'سوريا')->orWhere('name', 'الجمهورية العربية السورية')->value('id') ?? 1;
        $uniId = Auth::user()->work_university_id ?? LookupUniversity::first()->id;

        // Collect existing files map if updating
        $existingFilesMap = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->notes) {
                        $existingFilesMap[$att->notes] = $att->file_path;
                    }
                    if (str_contains($att->notes, 'كتاب') || str_contains($att->notes, 'ترشيح')) {
                        $existingFilesMap['file_uni_request'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'دكتوراه') || str_contains($att->notes, 'الدكتوراه')) {
                        $existingFilesMap['file_phd_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'بيان وضع') || str_contains($att->notes, 'بحوث')) {
                        $existingFilesMap['file_service_statement'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'إيصال') || str_contains($att->notes, 'رسم')) {
                        $existingFilesMap['file_payment'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'شخصية')) {
                        $existingFilesMap['file_id_card'] = $att->file_path;
                    }
                }
            }
        }

        if ($isDraft) {
            $rules = [
                'full_name' => 'nullable|string|max:255',
                'national_id' => 'nullable|string|max:50',
                'req_no' => 'nullable|string|max:100',
                'req_date' => 'nullable|date',
            ];
            $messages = [];
        } else {
            $rules = [
                // Step 1: Personal & Private University info
                'full_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'national_id' => 'required|string|digits:11',
                'dob' => 'required|date|before:today',
                'gender' => 'required|in:ذكر,أنثى',
                'nationality_id' => 'required|exists:lookup_countries,id',
                'phone' => 'nullable|string|max:30',
                'mobile' => 'required|string|max:30',
                'email' => 'required|email|max:255',
                'address' => 'required|string|max:255',
                'req_no' => 'required|string|max:100',
                'req_date' => 'required|date|before_or_equal:today',
                'work_faculty' => 'required|string|max:255',
                'work_department' => 'required|string|max:255',

                // Step 2: Research Center Employment info
                'rc_center_name' => 'required|string|max:255',
                'rc_department' => 'required|string|max:255',
                'rc_rank' => 'required|string|max:100',

                // Step 3: PhD info
                'phd_university_id' => 'required_without:phd_university_other|nullable|exists:lookup_universities,id',
                'phd_university_other' => 'required_without:phd_university_id|nullable|string|max:255',
                'phd_country_id' => 'required|exists:lookup_countries,id',
                'phd_faculty' => 'required|string|max:255',
                'phd_department' => 'required|string|max:255',
                'phd_grant_date' => 'required|date|before_or_equal:today',

                // Step 4: Required Attachments
                'file_uni_request' => !empty($existingFilesMap['file_uni_request']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_phd_cert' => !empty($existingFilesMap['file_phd_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_service_statement' => !empty($existingFilesMap['file_service_statement']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_payment' => !empty($existingFilesMap['file_payment']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_id_card' => !empty($existingFilesMap['file_id_card']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'full_name.required' => 'يرجى إدخال الاسم والكنية للباحث.',
                'father_name.required' => 'يرجى إدخال اسم الأب.',
                'mother_name.required' => 'يرجى إدخال اسم الأم.',
                'national_id.required' => 'يرجى إدخال الرقم الوطني للباحث.',
                'dob.required' => 'يرجى إدخال تاريخ الميلاد.',
                'mobile.required' => 'يرجى إدخال رقم الموبايل.',
                'email.required' => 'يرجى إدخال البريد الإلكتروني.',
                'address.required' => 'يرجى إدخال عنوان الإقامة الحالي.',
                'req_no.required' => 'يرجى إدخال رقم كتاب الجامعة الخاصة.',
                'req_date.required' => 'يرجى إدخال تاريخ كتاب الجامعة الخاصة.',
                'work_faculty.required' => 'يرجى إدخال الكلية المرشح للتدريس فيها.',
                'work_department.required' => 'يرجى إدخال القسم المرشح للتدريس فيه.',
                'rc_center_name.required' => 'يرجى إدخال اسم مركز البحوث (مثلاً: مركز الدراسات والبحوث العلمية).',
                'rc_department.required' => 'يرجى إدخال القسم أو الدائرة في مركز البحوث.',
                'rc_rank.required' => 'يرجى إدخال الصفة الوظيفية (باحث / رئيس باحثين).',
                'phd_faculty.required' => 'يرجى إدخال الكلية / المعهد المانح لدرجة الدكتوراه.',
                'phd_department.required' => 'يرجى إدخال اختصاص درجة الدكتوراه.',
                'phd_grant_date.required' => 'يرجى إدخال سنة/تاريخ منح درجة الدكتوراه.',
                'file_uni_request.required' => 'يرجى إرفاق طلب التقويم / كتاب ترشيح الجامعة الخاصة.',
                'file_phd_cert.required' => 'يرجى إرفاق نسخة مصدقة عن شهادة الدكتوراه.',
                'file_service_statement.required' => 'يرجى إرفاق بيان الوضع الوظيفي من مركز البحوث.',
                'file_payment.required' => 'يرجى إرفاق إيصال تسديد رسم التعادل.',
                'file_id_card.required' => 'يرجى إرفاق صورة عن الهوية الشخصية.',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update
        $fullName = $request->full_name ?: ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة جديدة');
        $nationalId = $request->national_id ?: ($isExisting && $existingApp->candidate ? $existingApp->candidate->national_id : ('DRAFT_' . time() . '_' . rand(100, 999)));

        $candidate = EquivalenceProfile::updateOrCreate(
            ['national_id' => $nationalId],
            [
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? '',
                'mother_name' => $request->mother_name ?? '',
                'nationality_id' => $request->nationality_id ?? $syriaId,
                'dob' => $request->dob,
                'job_title' => $request->rc_rank ?? 'باحث في مركز بحوث',
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'gender' => $request->gender,
                'is_syrian' => true,
            ]
        );

        // 3. Application Creation / Update
        $requestType = 'باحث في مراكز البحوث';
        $appNo = $isExisting ? $existingApp->application_no : ('RES-' . rand(100000, 999999));

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
                'new_uni_request_date' => $request->req_date,
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
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => true,
                'study_system' => 'سنوي / فصلي',
                'has_previous_degree' => true,
                'status' => $statusToSet,
                'user_id' => Auth::id(),
            ]);
        }

        // 4. Store / Update Educations
        // Research Center Employment Record
        $rcCenterName = $request->rc_center_name ?: 'مركز الدراسات والبحوث العلمية';
        $existingRcEd = Education::where('application_id', $application->id)
            ->where('thesis_title', 'باحث في مركز بحوث')
            ->first();

        $rcEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'thesis_title' => 'باحث في مركز بحوث',
            ],
            [
                'country_id' => $syriaId,
                'faculty' => $rcCenterName,
                'department' => $request->rc_department,
                'general_specialization' => $rcCenterName,
                'exact_specialization' => $request->rc_department,
                'rank' => $request->rc_rank ?? 'باحث',
                'education_level_id' => 3,
            ]
        );

        // PhD Record
        $phdLevelId = LookupEducationLevel::where('name', 'like', '%دكتوراه%')->value('id') ?? 3;
        $phdEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => $phdLevelId,
            ],
            [
                'thesis_title' => 'شهادة الدكتوراه (مراكز البحوث)',
                'country_id' => $request->phd_country_id ?? $syriaId,
                'university_id' => $request->phd_university_id,
                'university_other' => $request->phd_university_other,
                'faculty' => $request->phd_faculty,
                'department' => $request->phd_department,
                'general_specialization' => $request->phd_faculty,
                'exact_specialization' => $request->phd_department,
                'section_name' => $request->phd_department,
                'grant_date' => $request->phd_grant_date,
            ]
        );

        // 5. Handle File Uploads
        $uploadAttachments = [
            'file_uni_request' => ['target_ed' => $rcEd, 'type_id' => 7, 'notes' => 'طلب تقويم / كتاب ترشيح الجامعة الخاصة'],
            'file_phd_cert' => ['target_ed' => $phdEd, 'type_id' => 8, 'notes' => 'نسخة مصدقة عن شهادة الدكتوراه'],
            'file_service_statement' => ['target_ed' => $rcEd, 'type_id' => 9, 'notes' => 'بيان الوضع الوظيفي من مركز البحوث'],
            'file_payment' => ['target_ed' => $rcEd, 'type_id' => 10, 'notes' => 'إيصال تسديد رسم التعادل'],
            'file_id_card' => ['target_ed' => $rcEd, 'type_id' => 11, 'notes' => 'صورة عن الهوية الشخصية'],
            'file_other_attachments' => ['target_ed' => $phdEd, 'type_id' => 13, 'notes' => 'مرفقات ووثائق أخرى'],
        ];

        foreach ($uploadAttachments as $fieldName => $meta) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $filePath = $file->store('applications/' . $application->id, 'public');

                Attachment::updateOrCreate(
                    [
                        'education_id' => $meta['target_ed']->id,
                        'attachment_type_id' => $meta['type_id'],
                    ],
                    [
                        'file_path' => $filePath,
                        'file_type' => $file->getClientMimeType() ?: 'application/pdf',
                        'file_size' => $file->getSize(),
                        'notes' => $meta['notes'],
                    ]
                );
            }
        }

        if ($isDraft) {
            return redirect()->route('university.dashboard')->with('success', 'تم حفظ مسودة طلب الباحث في مراكز البحوث بنجاح (رقم المعاملة: ' . $appNo . ')');
        }

        return redirect()->route('university.dashboard')->with('success', 'تم إرسال طلب اعتماد باحث مراكز البحوث بنجاح برقم قيد: ' . $appNo);
    }
}



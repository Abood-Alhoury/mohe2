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
                'hs_decision_no' => 'nullable|string|max:100',
                'hs_decision_date' => 'nullable|date',

                'ba_country_id' => 'nullable|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string',
                'ba_department' => 'nullable|string',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'nullable|date',
                'ba_rank' => 'nullable|string',
                'ba_decision_no' => 'nullable|string|max:100',
                'ba_decision_date' => 'nullable|date',

                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_faculty' => 'nullable|string',
                'ma_department' => 'nullable|string',
                'ma_registration_date' => 'nullable|date',
                'ma_defense_date' => 'nullable|date',
                'ma_grant_date' => 'nullable|date',
                'ma_rank' => 'nullable|string',
                'ma_supervisor' => 'nullable|string',
                'ma_thesis_title' => 'nullable|string',

                'has_experience' => 'nullable|boolean',
                'exp_place' => 'nullable|string|max:255',
                'exp_from_year' => 'nullable|date',
                'exp_to_year' => 'nullable|date',

                'req_no' => 'nullable',
                'req_date' => 'nullable|date',
                'is_first_time' => 'nullable|boolean',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_dates' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:2048',
                'file_lang_icdl' => 'nullable|file|mimes:pdf|max:2048',
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
                'hs_type' => 'required|string|in:علمي,أدبي,تجاري,صناعي',
                'hs_grant_date' => 'required|date',
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 3: Bachelor's Degree Info
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_registration_date' => 'required|date',
                'ba_grant_date' => 'required|date|after:ba_registration_date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => ($request->ba_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => ($request->ba_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'ba_decision_file' => ($request->ba_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

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

                // Step 1: University Request Details
                'req_no' => 'required|regex:/^[0-9]+$/',
                'req_date' => 'required|date',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // Step 6: Final Attachments Upload
                'file_uni_request' => $fileRule,
                'file_hs_cert' => $fileRule,
                'file_ba_cert' => $fileRule,
                'file_ma_cert' => $fileRule,
                'file_ma_dates' => $fileRule,
                'file_thesis_summary' => $fileRule,
                'file_exp_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_lang_icdl' => $fileRule,
                'file_cv' => $fileRule,
                'file_payment' => $fileRule,
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
        $appNo = 'MA-SY-' . rand(100000, 999999);

        $frequency = $request->input('equivalence_frequency', 'تعادل للمرة الأولى');
        $hasExp = $request->boolean('has_experience') && !empty($request->input('exp_place'));
        $trackName = $hasExp ? 'ماجستير سوري' : 'ماجستير تطبيقي';
        $requestType = $frequency . ' - ' . $trackName;

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
                    if (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
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
                        $existingAttachments['lang_icdl'] = $att->file_path;
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

        $edHS = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $hsLevelId,
            'country_id' => $request->hs_country_id,
            'section_name' => $request->hs_type,
            'grant_date' => $request->hs_grant_date,
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
            'section_name' => $request->ba_university_other,
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

        // High School Attachments
        $uploadAndAttach('file_hs_cert', $edHS->id, 1, 'شهادة الدراسة الثانوية', 'hs_cert');
        if ($request->hasFile('hs_decision_file') || isset($existingAttachments['hs_decision'])) {
            $uploadAndAttach('hs_decision_file', $edHS->id, 1, 'قرار معادلة الشهادة الثانوية', 'hs_decision');
        }

        // Bachelor's Attachments
        $uploadAndAttach('file_ba_cert', $edBA->id, 2, 'مصدقة الإجازة الجامعية الأولى', 'ba_cert');
        if ($request->hasFile('ba_decision_file') || isset($existingAttachments['ba_decision'])) {
            $uploadAndAttach('ba_decision_file', $edBA->id, 2, 'قرار معادلة الشهادة الجامعية الأولى', 'ba_decision');
        }

        // Master's Attachments
        $uploadAndAttach('file_ma_cert', $edMA->id, 3, 'نسخة مصدقة عن شهادة الماجستير', 'ma_cert');
        $uploadAndAttach('file_ma_dates', $edMA->id, 3, 'وثيقة تواريخ التسجيل والمناقشة والمنح', 'ma_dates');
        $uploadAndAttach('file_thesis_summary', $edMA->id, 3, 'ملخص رسالة الماجستير باللغة العربية', 'thesis_summary');
        $uploadAndAttach('file_lang_icdl', $edMA->id, 6, 'شهادة اللغة الإنكليزية + شهادة ICDL', 'lang_icdl');
        $uploadAndAttach('file_cv', $edMA->id, 3, 'السيرة الذاتية للمرشح', 'cv');
        $uploadAndAttach('file_payment', $edMA->id, 4, 'إيصال تسديد رسم تعادل 100,000 ل.س', 'payment');
        $uploadAndAttach('file_uni_request', $edMA->id, 3, 'كتاب الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date, 'uni_request');

        if ($request->hasFile('file_exp_cert') || isset($existingAttachments['exp_cert'])) {
            $uploadAndAttach('file_exp_cert', $edMA->id, 3, 'شهادة خبرة تدريسية لا تقل عن سنتين', 'exp_cert');
        }
        if ($request->hasFile('file_contracts') || isset($existingAttachments['contracts'])) {
            $uploadAndAttach('file_contracts', $edMA->id, 3, 'العقود وإيصالات الرواتب المصدقة', 'contracts');
        }
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttach('file_other_attachments', $edMA->id, 3, 'مرفقات أخرى', 'other_attachments');
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
                'hs_grant_date' => 'nullable|date',
                'hs_decision_no' => 'nullable|string|max:100',
                'hs_decision_date' => 'nullable|date',

                'ba_country_id' => 'nullable|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string',
                'ba_department' => 'nullable|string',
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
                'hs_type' => 'required|string|in:علمي,أدبي,تجاري,صناعي',
                'hs_grant_date' => 'required|date',
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 3: Bachelor's Degree Info
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
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

        $frequency = $request->input('equivalence_frequency', 'تعادل للمرة الأولى');
        $requestType = $frequency . ' - دكتوراه سورية';

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
                        if (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
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
                        } elseif (str_contains($att->notes, 'ICDL')) {
                            $existingAttachments['icdl'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'اللغة')) {
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
            'section_name' => $request->ba_university_other,
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
                'general_specialization' => $request->dip_faculty,
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
            'section_name' => $request->ma_university_other,
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

        // 1. High School
        $uploadAndAttachPhd('file_hs_cert', $edHS->id, 1, 'شهادة الدراسة الثانوية', 'hs_cert');
        if ($request->hasFile('hs_decision_file') || isset($existingAttachments['hs_decision'])) {
            $uploadAndAttachPhd('hs_decision_file', $edHS->id, 1, 'قرار معادلة الشهادة الثانوية', 'hs_decision');
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
        $uploadAndAttachPhd('file_phd_cert', $edPhD->id, 4, 'شهادة الدكتوراه', 'phd_cert');
        $uploadAndAttachPhd('file_thesis_summary', $edPhD->id, 4, 'ملخص عن الأطروحة', 'thesis_summary');
        $uploadAndAttachPhd('file_phd_council_decisions', $edPhD->id, 4, 'قرارات مجلس الجامعة للدكتوراه', 'phd_council_decisions');
        $uploadAndAttachPhd('file_icdl', $edPhD->id, 6, 'شهادة ICDL', 'icdl');
        $uploadAndAttachPhd('file_english_test', $edPhD->id, 6, 'شهادة اختبار اللغة الإنكليزية', 'english_test');
        $uploadAndAttachPhd('file_payment', $edPhD->id, 4, 'رسوم التعادل (125,000 ل.س)', 'payment');
        
        $uploadAndAttachPhd('file_uni_request', $edPhD->id, 4, 'كتاب الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date, 'uni_request');
        $uploadAndAttachPhd('file_cv', $edPhD->id, 4, 'السيرة الذاتية للمرشح', 'cv');

        // 13. Other Attachments (Optional)
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttachPhd('file_other_attachments', $edPhD->id, 4, 'مرفقات أخرى', 'other_attachments');
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


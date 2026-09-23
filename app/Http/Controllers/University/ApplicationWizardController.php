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

        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        
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

        // 1. التقاط معرّف الطلب في كافة الحالات
        $appId = $request->input('draft_id') ?? $request->input('application_id') ?? $request->input('app_id') ?? $request->input('id');
        if ($appId) {
            $existingApp = Application::where('id', $appId)
                ->where('work_university_id', $uniId)
                ->with(['educations.level', 'educations.attachments'])
                ->first();
        }

        // البحث الاحتياطي الذكي بالرقم الوطني
        if (!$existingApp && $request->filled('national_id')) {
            $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
            if ($existingProfile) {
                $existingApp = Application::where('candidate_id', $existingProfile->id)
                    ->where('work_university_id', $uniId)
                    ->where(function($q) {
                        $q->whereIn('status', ['مسودة', 'بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 1, 3]);
                    })
                    ->where(function($q) {
                        $q->whereIn('request_type', ['ماجستير داخلي - نظري', 'ماجستير داخلي - تطبيقي', 1, 2]);
                    })
                    ->with(['educations.level', 'educations.attachments'])
                    ->latest('id')
                    ->first();
            }
        }

        $isExisting = ($existingApp !== null);
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        // 2. تجميع المرفقات السابقة (بالأرقام الجديدة وبالنصوص لضمان عدم ضياع أي ملف)
        $existingAttachments = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $notes = $att->notes ?? '';
                    $tId = (int) ($att->attachment_type_id ?? 0);
                    $path = $att->file_path;
                    if (!$path) continue;

                    if ($tId) $existingAttachments[$tId] = $path;
                    if ($notes) $existingAttachments[$notes] = $path;

                    // دعم المسميات والـ IDs الجديدة والقديمة
                    if ($tId === 1 || (str_contains($notes, 'ثانوية') && !str_contains($notes, 'قرار'))) {
                        $existingAttachments[1] = $path;
                        $existingAttachments['hs_cert'] = $path;
                        $existingAttachments['file_hs_cert'] = $path;
                    } elseif ($tId === 2 || $tId === 13 || (str_contains($notes, 'قرار') && str_contains($notes, 'ثانوية'))) {
                        $existingAttachments[2] = $path;
                        $existingAttachments['hs_decision'] = $path;
                        $existingAttachments['hs_decision_file'] = $path;
                        $existingAttachments['file_hs_decision'] = $path;
                    } elseif ($tId === 3 || $tId === 2 || (str_contains($notes, 'الإجازة') && !str_contains($notes, 'قرار'))) {
                        $existingAttachments[3] = $path;
                        $existingAttachments['ba_cert'] = $path;
                        $existingAttachments['file_ba_cert'] = $path;
                    } elseif ($tId === 4 || $tId === 15 || (str_contains($notes, 'قرار') && str_contains($notes, 'الإجازة'))) {
                        $existingAttachments[4] = $path;
                        $existingAttachments['ba_decision'] = $path;
                        $existingAttachments['ba_decision_file'] = $path;
                        $existingAttachments['file_ba_decision'] = $path;
                    } elseif ($tId === 6 || $tId === 12 || (str_contains($notes, 'شهادة') && str_contains($notes, 'ماجستير'))) {
                        $existingAttachments[6] = $path;
                        $existingAttachments['ma_cert'] = $path;
                        $existingAttachments['file_ma_cert'] = $path;
                    } elseif ($tId === 7 || $tId === 16 || str_contains($notes, 'تواريخ') || str_contains($notes, 'مجلس')) {
                        $existingAttachments[7] = $path;
                        $existingAttachments['ma_dates'] = $path;
                        $existingAttachments['file_ma_dates'] = $path;
                    } elseif ($tId === 8 || $tId === 17 || str_contains($notes, 'ملخص')) {
                        $existingAttachments[8] = $path;
                        $existingAttachments['thesis_summary'] = $path;
                        $existingAttachments['file_thesis_summary'] = $path;
                    } elseif ($tId === 13 || $tId === 11 || str_contains($notes, 'هوية') || str_contains($notes, 'شخصية')) {
                        $existingAttachments[13] = $path;
                        $existingAttachments['national_id'] = $path;
                        $existingAttachments['file_national_id'] = $path;
                    } elseif ($tId === 14 || $tId === 20 || str_contains($notes, 'السيرة') || str_contains($notes, 'سيرة')) {
                        $existingAttachments[14] = $path;
                        $existingAttachments['cv'] = $path;
                        $existingAttachments['file_cv'] = $path;
                    } elseif ($tId === 15 || $tId === 21 || str_contains($notes, 'كتاب') || str_contains($notes, 'طلب')) {
                        $existingAttachments[15] = $path;
                        $existingAttachments['uni_request'] = $path;
                        $existingAttachments['file_uni_request'] = $path;
                    } elseif ($tId === 16 || $tId === 6 || str_contains($notes, 'إيصال') || str_contains($notes, 'تسديد') || str_contains($notes, 'رسم')) {
                        $existingAttachments[16] = $path;
                        $existingAttachments['payment'] = $path;
                        $existingAttachments['file_payment'] = $path;
                    } elseif ($tId === 18 || str_contains($notes, 'اللغة')) {
                        $existingAttachments[18] = $path;
                        $existingAttachments['lang_cert'] = $path;
                        $existingAttachments['file_lang_cert'] = $path;
                    } elseif ($tId === 19 || str_contains($notes, 'ICDL') || str_contains($notes, 'حاسوب')) {
                        $existingAttachments[19] = $path;
                        $existingAttachments['icdl_cert'] = $path;
                        $existingAttachments['file_icdl_cert'] = $path;
                    } elseif ($tId === 20 || $tId === 22 || str_contains($notes, 'خبرة')) {
                        $existingAttachments[20] = $path;
                        $existingAttachments['exp_cert'] = $path;
                        $existingAttachments['file_exp_cert'] = $path;
                    } elseif ($tId === 21 || $tId === 23 || str_contains($notes, 'العقود') || str_contains($notes, 'رواتب')) {
                        $existingAttachments[21] = $path;
                        $existingAttachments['contracts'] = $path;
                        $existingAttachments['file_contracts'] = $path;
                    } elseif ($tId === 22 || $tId === 25 || str_contains($notes, 'المكتبة')) {
                        $existingAttachments[22] = $path;
                        $existingAttachments['nat_library_receipt'] = $path;
                        $existingAttachments['file_nat_library_receipt'] = $path;
                    } elseif ($tId === 23 || $tId === 24 || str_contains($notes, 'أخرى') || str_contains($notes, 'اخرى')) {
                        $existingAttachments[23] = $path;
                        $existingAttachments['other_attachments'] = $path;
                        $existingAttachments['file_other_attachments'] = $path;
                    }
                }
            }
        }

        $reqOrNullable = fn($typeId, $key) => (isset($existingAttachments[$typeId]) || isset($existingAttachments[$key]) || isset($existingAttachments['file_' . $key]))
            ? 'nullable|file|mimes:pdf|max:2048'
            : 'required|file|mimes:pdf|max:2048';

        $hasHsDecision = isset($existingAttachments[2]) || isset($existingAttachments['hs_decision']) || isset($existingAttachments['hs_decision_file']);
        $hasBaDecision = isset($existingAttachments[4]) || isset($existingAttachments['ba_decision']) || isset($existingAttachments['ba_decision_file']);
        $hasContracts  = isset($existingAttachments[21]) || isset($existingAttachments['contracts']) || isset($existingAttachments['file_contracts']);

        $isDraft = $request->input('action') === 'save_draft';

        // 3. قواعد التحقق (Validation Rules)
        if ($isDraft) {
            $rules = [
                'full_name' => 'nullable|string|max:255',
                'national_id' => 'nullable|string|regex:/^[0-9]{1,11}$/',
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
                'hs_decision_file' => 'nullable|file|mimes:pdf|max:2048',

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
                'ba_decision_file' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_decision' => 'nullable|file|mimes:pdf|max:2048',
                'ba_university_text' => 'nullable|string|max:255',

                'ma_university_id' => 'nullable',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_general_specialization' => 'nullable|string|max:255',
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
            $rules = [
                'full_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'mother_name' => 'required|string|max:255',
                'nationality_id' => 'required|exists:lookup_countries,id',
                'national_id' => ['required', 'regex:/^[0-9]{11}$/'],
                'dob' => 'required|date',
                'job_title' => 'required|string|max:150',
                'phone' => 'nullable|string|regex:/^[0-9]{10}$/',
                'mobile' => 'required|string|regex:/^[0-9]{10}$/',
                'email' => 'required|email:filter|max:255',
                'address' => 'required|string',
                'gender' => 'required|string|in:ذكر,أنثى',
                'is_syrian' => 'required|boolean',

                'hs_country_id' => 'required|exists:lookup_countries,id',
                'hs_type' => 'required|string|in:أخرى,علمي,أدبي,شرعي,صناعي,تجاري',
                'hs_grant_date' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$hasHsDecision) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_hs_decision' => 'nullable|file|mimes:pdf|max:2048',

                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_university_text' => 'required|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => ($request->ba_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => ($request->ba_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'ba_decision_file' => ($request->ba_country_id != $syriaId && !$hasBaDecision) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_ba_decision' => 'nullable|file|mimes:pdf|max:2048',

                'ma_university_id' => 'required|exists:lookup_universities,id',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_general_specialization' => 'required|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_registration_date' => 'required|date',
                'ma_defense_date' => 'required|date|after:ma_registration_date',
                'ma_grant_date' => 'required|date|after:ma_defense_date|before_or_equal:today',
                'ma_rank' => 'required|string|max:100',
                'ma_supervisor' => 'required|string|max:255',
                'ma_thesis_title' => 'required|string',

                'has_experience' => 'nullable|boolean',
                'exp_place' => 'nullable|required_if:has_experience,1|string|max:255',
                'exp_from_year' => 'nullable|required_if:has_experience,1|date',
                'exp_to_year' => 'nullable|required_if:has_experience,1|date|after_or_equal:exp_from_year',

                'req_no' => 'required',
                'req_date' => 'required|date',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // المرفقات المطلوبة مع الاستفادة من التخزين السابق
                'file_uni_request' => $reqOrNullable(15, 'uni_request'),
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => $reqOrNullable(1, 'hs_cert'),
                'file_ba_cert' => $reqOrNullable(3, 'ba_cert'),
                'file_ma_cert' => $reqOrNullable(6, 'ma_cert'),
                'file_ma_dates' => $reqOrNullable(7, 'ma_dates'),
                'file_thesis_summary' => $reqOrNullable(8, 'thesis_summary'),
                'file_cv' => $reqOrNullable(14, 'cv'),
                'file_payment' => $reqOrNullable(16, 'payment'),
                'file_exp_cert' => ($request->boolean('has_experience') && !isset($existingAttachments[20]) && !isset($existingAttachments['exp_cert'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_contracts' => ($request->boolean('has_experience') && !$hasContracts) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_lang_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_nat_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'national_id.required' => 'الرقم الوطني مطلوب.',
                'national_id.regex' => 'الرقم الوطني يجب أن يتألف من 11 رقماً حصراً.',
                'mobile.regex' => 'رقم الهاتف المحمول يجب أن يتكون من 10 أرقام.',
                'phone.regex' => 'رقم الهاتف الأرضي يجب أن يتكون من 10 أرقام.',
                'email.email' => 'البريد الإلكتروني المدخل غير صحيح.',
                'hs_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_date.required' => 'يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الشهادة الثانوية غير السورية (PDF).',
                'ba_decision_no.required' => 'يرجى إدخال رقم قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_date.required' => 'يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية غير السورية.',
                'ba_decision_file.required' => 'يرجى رفع صورة عن قرار معادلة الإجازة الجامعية غير السورية (PDF).',
                'ba_grant_date.before_or_equal' => 'تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_defense_date.after' => 'تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'ma_grant_date.after' => 'تاريخ منح الدرجة يجب أن يكون بعد تاريخ المناقشة.',
                'ma_grant_date.before_or_equal' => 'تاريخ منح الدرجة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'file_uni_request.required' => 'يرجى رفع ملف كتاب طلب التقويم الصادر عن الجامعة (PDF).',
                'file_hs_cert.required' => 'يرجى رفع ملف شهادة الدراسة الثانوية (PDF).',
                'file_ba_cert.required' => 'يرجى رفع ملف شهادة الإجازة الجامعية الأولى (PDF).',
                'file_ma_cert.required' => 'يرجى رفع ملف شهادة الماجستير السورية (PDF).',
                'file_ma_dates.required' => 'يرجى رفع ملف وثيقة تواريخ وقرارات المجلس بالماجستير (PDF).',
                'file_thesis_summary.required' => 'يرجى رفع ملف ملخص رسالة الماجستير (PDF).',
                'file_cv.required' => 'يرجى رفع ملف السيرة الذاتية للمرشح (PDF).',
                'file_payment.required' => 'يرجى رفع ملف إيصال تسديد رسم تعادل الماجستير 100,000 ل.س (PDF).',
                'file_exp_cert.required' => 'يرجى رفع شهادة الخبرة التدريسية (PDF).',
                'file_contracts.required' => 'يرجى رفع العقود وإيصالات الرواتب (PDF).',
                'max' => 'حجم الملف المرفق يتجاوز الحد الأقصى المسموح به (2 ميغابايت).',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // 4. حفظ وتحديث بيانات المرشح
        if ($existingApp && $existingApp->candidate) {
            $profile = $existingApp->candidate;
            $profile->update([
                'full_name' => $request->filled('full_name') ? $request->full_name : $profile->full_name,
                'father_name' => $request->father_name ?? $profile->father_name,
                'mother_name' => $request->mother_name ?? $profile->mother_name,
                'national_id' => $request->filled('national_id') ? $request->national_id : $profile->national_id,
                'dob' => $request->dob ?: $profile->dob,
                'job_title' => $request->job_title ?? $profile->job_title,
                'nationality_id' => $request->nationality_id ?: $profile->nationality_id,
                'phone' => $request->phone ?? $profile->phone,
                'mobile' => $request->mobile ?? $profile->mobile,
                'email' => $request->email ?? $profile->email,
                'address' => $request->address ?? $profile->address,
                'gender' => $request->gender ?: $profile->gender,
                'is_syrian' => $request->is_syrian ?? 1,
            ]);
        } else {
            $candNationalId = $request->national_id ?: ('TMP-' . time() . '-' . rand(100, 999));
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
        }

        // 5. حفظ وتحديث بيانات الطلب
        $hasExp = $request->boolean('has_experience') && !empty($request->input('exp_place'));
        $trackName = $hasExp ? 'ماجستير داخلي - نظري' : 'ماجستير داخلي - تطبيقي';
        $appPrefix = $hasExp ? 'MA-SY-' : 'MA-APP-';
        $appNo = ($existingApp && $existingApp->application_no) ? $existingApp->application_no : ($appPrefix . rand(100000, 999999));
        $requestType = $trackName;

        $wasAwaitingDocs = ($existingApp && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $appStatus = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';
        $application = $existingApp;

        if ($application) {
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

        // 6. حفظ المقررات
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

        // جلب مستويات التعليم
        $lvlHS = LookupEducationLevel::where('name', 'like', '%ثانوية%')->first();
        $lvlBA = LookupEducationLevel::where('name', 'like', '%إجازة%')->first();
        $lvlMA = LookupEducationLevel::where('name', 'like', '%ماجستير%')->first();

        $hsLevelId = $lvlHS ? $lvlHS->id : 4;
        $baLevelId = $lvlBA ? $lvlBA->id : 1;
        $maLevelId = $lvlMA ? $lvlMA->id : 2;

        // 7. حفظ سجلات التعليم (Educations)
        // أ. الثانوية
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

        // ب. الإجازة الجامعية الأولى
        $baNotes = null;
        if ($request->ba_decision_no) {
            $baNotes = 'رقم قرار معادلة الإجازة: ' . $request->ba_decision_no;
            if ($request->ba_decision_date) {
                $baNotes .= ' | تاريخ القرار: ' . $request->ba_decision_date;
            }
        }

        $baUniId = null;
        $baUniName = trim($request->input('ba_university_text', ''));
        if ($baUniName === '') {
            $baUniName = trim($request->input('ba_university_other', ''));
        }

        $syriaCountryId = $syriaId ?: $request->nationality_id;

        if ($baUniName !== '') {
            $uni = \App\Models\LookupUniversity::where('name', $baUniName)->first()
                ?: \App\Models\LookupUniversity::where('name', 'like', "%{$baUniName}%")->first();
            if (!$uni) {
                $uni = \App\Models\LookupUniversity::create([
                    'name' => $baUniName,
                    'country_id' => $request->ba_country_id ?: $syriaCountryId,
                ]);
            }
            $baUniId = $uni->id;
        } elseif ($request->filled('ba_university_id')) {
            $baUniId = $request->ba_university_id;
        }

        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $baUniId,
            'faculty' => $request->ba_faculty,
            'department' => $request->ba_department,
            'section_name' => $request->ba_specialization ?: null,
            'general_specialization' => $request->ba_faculty,
            'exact_specialization' => $request->ba_specialization ?: $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => $baNotes,
        ]);

        // ج. الماجستير
        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $syriaCountryId,
            'university_id' => $request->ma_university_id,
            'faculty' => $request->ma_faculty,
            'department' => $request->ma_department,
            'section_name' => $request->ma_specialization ?? null,
            'general_specialization' => $request->ma_general_specialization ?: $request->ma_faculty,
            'exact_specialization' => $request->ma_specialization ?: $request->ma_department,
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

        // 8. حفظ وربط المرفقات بالأرقام المعيارية (1 - 23)
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
                $oldPath = $existingAttachments[$typeId]
                    ?? ($existingKey && isset($existingAttachments[$existingKey]) ? $existingAttachments[$existingKey] : null)
                    ?? ($existingAttachments[$notes] ?? null);

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

        // الهوية الشخصية (13)
        if ($request->hasFile('file_national_id') || isset($existingAttachments[13]) || isset($existingAttachments['national_id'])) {
            $uploadAndAttach('file_national_id', $edMA->id, 13, 'صورة عن الهوية الشخصية / جواز السفر', 'national_id');
        }

        // مرفقات الثانوية (شهادة 1 / قرار 2)
        $uploadAndAttach('file_hs_cert', $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', 'hs_cert');
        $hsDecisionInput = $request->hasFile('file_hs_decision') ? 'file_hs_decision' : 'hs_decision_file';
        if ($request->hasFile($hsDecisionInput) || $hasHsDecision) {
            $uploadAndAttach($hsDecisionInput, $edHS->id, 2, 'قرار معادلة الشهادة الثانوية غير السورية', 'hs_decision');
        }

        // مرفقات الإجازة الجامعية الأولى (مصدقة 3 / قرار 4)
        $uploadAndAttach('file_ba_cert', $edBA->id, 3, 'مصدقة الإجازة الجامعية الأولى', 'ba_cert');
        $baDecisionInput = $request->hasFile('file_ba_decision') ? 'file_ba_decision' : 'ba_decision_file';
        if ($request->hasFile($baDecisionInput) || $hasBaDecision) {
            $uploadAndAttach($baDecisionInput, $edBA->id, 4, 'قرار معادلة الإجازة الجامعية الأولى غير السورية', 'ba_decision');
        }

        // مرفقات الماجستير (شهادة 6 / تواريخ وقرارات 7 / ملخص 8)
        $uploadAndAttach('file_ma_cert', $edMA->id, 6, 'نسخة مصدقة عن شهادة الماجستير', 'ma_cert');
        $uploadAndAttach('file_ma_dates', $edMA->id, 7, 'وثيقة تواريخ وقرارات المجلس للماجستير', 'ma_dates');
        $uploadAndAttach('file_thesis_summary', $edMA->id, 8, 'ملخص رسالة الماجستير باللغة العربية', 'thesis_summary');

        // الوثائق العامة والرسوم (السيرة 14 / كتاب الجامعة 15 / رسم الماجستير 16)
        $uploadAndAttach('file_cv', $edMA->id, 14, 'السيرة الذاتية للمرشح', 'cv');
        $uploadAndAttach('file_uni_request', $edMA->id, 15, 'كتاب طلب التقويم الصادر عن الجامعة رقم ' . $request->req_no . ' تاريخ ' . $request->req_date, 'uni_request');
        $uploadAndAttach('file_payment', $edMA->id, 16, 'إيصال تسديد رسم تعادل الماجستير (100,000 ل.س)', 'payment');

        // الوثائق الاختيارية (اللغة 18 / الحاسوب 19 / خبرة 20 / عقود 21 / مكتبة 22 / أخرى 23)
        if ($request->hasFile('file_lang_cert') || isset($existingAttachments[18]) || isset($existingAttachments['lang_cert'])) {
            $uploadAndAttach('file_lang_cert', $edMA->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'lang_cert');
        }
        if ($request->hasFile('file_icdl_cert') || isset($existingAttachments[19]) || isset($existingAttachments['icdl_cert'])) {
            $uploadAndAttach('file_icdl_cert', $edMA->id, 19, 'شهادة مهارات الحاسوب (ICDL)', 'icdl_cert');
        }
        if ($request->hasFile('file_exp_cert') || isset($existingAttachments[20]) || isset($existingAttachments['exp_cert'])) {
            $uploadAndAttach('file_exp_cert', $edMA->id, 20, 'شهادة خبرة تدريسية', 'exp_cert');
        }
        if ($request->hasFile('file_contracts') || $hasContracts) {
            $uploadAndAttach('file_contracts', $edMA->id, 21, 'العقود وإيصالات الرواتب المصدقة', 'contracts');
        }
        if ($request->hasFile('file_nat_library_receipt') || isset($existingAttachments[22]) || isset($existingAttachments['nat_library_receipt'])) {
            $uploadAndAttach('file_nat_library_receipt', $edMA->id, 22, 'إيصال المكتبة الوطنية لاستلام الرسالة / الأطروحة', 'nat_library_receipt');
        }
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments[23]) || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttach('file_other_attachments', $edMA->id, 23, 'مرفقات ووثائق أخرى', 'other_attachments');
        }

        // 9. التوجيه والرسائل
        if ($isDraft) {
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'تم حفظ بيانات طلب المرشح كمسودة تلقائياً بنجاح! يمكنك استكمالها في أي وقت من قسم المسودات المحفوظة.');
            }
            return redirect()->route('university.drafts.index')
                ->with('success', 'تم حفظ معاملة (' . $requestType . ') كمسودة بنجاح! للطلب رقم: ' . $appNo . '. يمكنك استكمال رفع المرفقات والوثائق الناقصة في أي وقت من هنا.');
        }

        if ($wasAwaitingDocs) {
            $candidateName = $profile ? $profile->full_name : ($application->candidate ? $application->candidate->full_name : '');
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة';

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). تم تحويل حالة المعاملة تلقائياً إلى (تحت التدقيق الأولي).",
                'is_read' => false,
            ]);

            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? 1;

            $application->notifyUniversityOfStatusChange('تحت التدقيق الأولي', 'تم استلام التعديلات والوثائق المستكملة بنجاح من الجامعة وتحويل المعاملة للتدقيق الأولي.', $systemAdminId);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم تحويل حالة المعاملة إلى (تحت التدقيق الأولي) وإشعار وزارة التعليم العالي.')
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

        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        
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

        // Smart draft lookup: check if this candidate already has an open draft for Syrian Doctorate in this university
        if (!$existingApp && $request->filled('national_id')) {
            $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
            if ($existingProfile) {
                $existingApp = Application::where('candidate_id', $existingProfile->id)
                    ->where('work_university_id', $uniId)
                    ->where(function($q) {
                        $q->where('status', 'مسودة')->orWhere('status', 1);
                    })
                    ->where(function($q) {
                        $q->where('request_type', 'like', '%دكتور%')
                          ->orWhereIn('request_type', [3, 7]);
                    })
                    ->with(['educations.attachments'])
                    ->latest('id')
                    ->first();
            }
        }

        $isExisting = ($existingApp !== null);
        $isDraft = $request->input('action') === 'save_draft';
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        // تجميع المرفقات السابقة المحفوظة بالأرقام الجديدة وبالنصوص
        $existingAttachments = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->attachment_type_id) {
                        $existingAttachments[$att->attachment_type_id] = $att->file_path;
                    }
                    if ($att->notes) {
                        $existingAttachments[$att->notes] = $att->file_path;
                        if (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'شخصية') || str_contains($att->notes, 'جواز')) {
                            $existingAttachments[13] = $att->file_path;
                            $existingAttachments['national_id'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments[1] = $att->file_path;
                            $existingAttachments['hs_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار') && str_contains($att->notes, 'ثانوية')) {
                            $existingAttachments[2] = $att->file_path;
                            $existingAttachments['hs_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'الإجازة') && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments[3] = $att->file_path;
                            $existingAttachments['ba_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار') && (str_contains($att->notes, 'الإجازة') || str_contains($att->notes, 'الجامعية'))) {
                            $existingAttachments[4] = $att->file_path;
                            $existingAttachments['ba_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'دبلوم')) {
                            $existingAttachments[5] = $att->file_path;
                            $existingAttachments['diploma_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'شهادة ماجستير')) {
                            $existingAttachments[6] = $att->file_path;
                            $existingAttachments['ma_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'تواريخ') || (str_contains($att->notes, 'مجلس') && str_contains($att->notes, 'ماجستير'))) {
                            $existingAttachments[7] = $att->file_path;
                            $existingAttachments['ma_council_decisions'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار') && str_contains($att->notes, 'ماجستير')) {
                            $existingAttachments[9] = $att->file_path;
                            $existingAttachments['ma_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الدكتوراه') || (str_contains($att->notes, 'دكتوراه') && str_contains($att->notes, 'شهادة'))) {
                            $existingAttachments[10] = $att->file_path;
                            $existingAttachments['phd_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'مجلس الجامعة للدكتوراه') || (str_contains($att->notes, 'مجلس') && str_contains($att->notes, 'دكتوراه'))) {
                            $existingAttachments[11] = $att->file_path;
                            $existingAttachments['phd_council_decisions'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ملخص') && (str_contains($att->notes, 'دكتوراه') || str_contains($att->notes, 'الأطروحة'))) {
                            $existingAttachments[12] = $att->file_path;
                            $existingAttachments['thesis_summary'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'السيرة') || str_contains($att->notes, 'سيرة')) {
                            $existingAttachments[14] = $att->file_path;
                            $existingAttachments['cv'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'طلب') || str_contains($att->notes, 'كتاب الجامعة')) {
                            $existingAttachments[15] = $att->file_path;
                            $existingAttachments['uni_request'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'إيصال') || str_contains($att->notes, 'رسوم') || str_contains($att->notes, '125')) {
                            $existingAttachments[17] = $att->file_path;
                            $existingAttachments['payment'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'اللغة') || str_contains($att->notes, 'إنكليزية')) {
                            $existingAttachments[18] = $att->file_path;
                            $existingAttachments['english_test'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ICDL') || str_contains($att->notes, 'حاسوب')) {
                            $existingAttachments[19] = $att->file_path;
                            $existingAttachments['icdl'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'المكتبة')) {
                            $existingAttachments[22] = $att->file_path;
                            $existingAttachments['nat_library_receipt'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'أخرى') || str_contains($att->notes, 'اخرى')) {
                            $existingAttachments[23] = $att->file_path;
                            $existingAttachments['other_attachments'] = $att->file_path;
                        }
                    }
                }
            }
        }

        // دالة مساعدة لتحديد إلزامية المرفق: معفى إذا كان مرفوعاً مسبقاً
        $isUploaded = function($typeId, $alias = null) use ($existingAttachments) {
            return isset($existingAttachments[$typeId]) || ($alias && isset($existingAttachments[$alias]));
        };

        // قاعدة الفحص العامة
        $fileRuleFor = function($typeId, $alias = null) use ($isExisting, $isUploaded) {
            if ($isExisting || $isUploaded($typeId, $alias)) {
                return 'nullable|file|mimes:pdf|max:2048';
            }
            return 'required|file|mimes:pdf|max:2048';
        };

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
                'ba_university_text' => 'nullable|string|max:255',
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
                'dip_university_text' => 'nullable|string|max:255',
                'dip_faculty' => 'nullable|string|max:255',
                'dip_specialization' => 'nullable|string|max:255',
                'dip_grant_date' => 'nullable|date',
                'dip_rank' => 'nullable|string|max:100',

                'ma_country_id' => 'nullable|exists:lookup_countries,id',
                'ma_university_id' => 'nullable|exists:lookup_universities,id',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string',
                'ma_department' => 'nullable|string',
                'ma_general_specialization' => 'nullable|string|max:255',
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
                'phd_general_specialization' => 'nullable|string|max:255',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_registration_date' => 'nullable|date',
                'phd_defense_date' => 'nullable|date',
                'phd_grant_date' => 'nullable|date',
                'phd_rank' => 'nullable|string',
                'phd_supervisor' => 'nullable|string',
                'phd_thesis_title' => 'nullable|string',

                'req_no' => 'nullable',
                'req_date' => 'nullable|date',
                'is_first_time' => 'nullable|boolean',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:2048',
                'hs_decision_file' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'ba_decision_file' => 'nullable|file|mimes:pdf|max:2048',
                'file_diploma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'ma_decision_file' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_council_decisions' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_test' => 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_nat_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
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

                // Step 1: University Request Details
                'req_no' => 'required',
                'req_date' => 'required|date',
                'is_first_time' => 'required|in:0,1',
                'parent_application_id' => 'nullable|required_if:is_first_time,0|exists:applications,id',

                // Step 2: High School Info
                'hs_country_id' => 'required|exists:lookup_countries,id',
                'hs_type' => 'required|string|in:علمي,أدبي,شرعي,صناعي,تجاري',
                'hs_grant_date' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
                'hs_decision_no' => ($request->hs_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => ($request->hs_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'hs_decision_file' => ($request->hs_country_id != $syriaId && !$isUploaded(2, 'hs_decision')) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 3: Bachelor's Degree Info
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|exists:lookup_universities,id',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_university_text' => 'required|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'required|string|max:100',
                'ba_decision_no' => ($request->ba_country_id != $syriaId) ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => ($request->ba_country_id != $syriaId) ? 'required|date' : 'nullable|date',
                'ba_decision_file' => ($request->ba_country_id != $syriaId && !$isUploaded(4, 'ba_decision')) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Optional Diploma
                'has_diploma' => 'nullable|boolean',
                'dip_country_id' => 'nullable|required_if:has_diploma,1|exists:lookup_countries,id',
                'dip_university_id' => 'nullable|exists:lookup_universities,id',
                'dip_university_text' => 'nullable|string|max:255',
                'dip_faculty' => 'nullable|string|max:255',
                'dip_specialization' => 'nullable|string|max:255',
                'dip_grant_date' => 'nullable|date',
                'dip_rank' => 'nullable|string|max:100',

                // Step 4: Master's Degree Info
                'ma_country_id' => 'required|exists:lookup_countries,id',
                'ma_university_id' => ($request->ma_country_id == $syriaId) ? 'required|exists:lookup_universities,id' : 'nullable',
                'ma_university_other' => ($request->ma_country_id != $syriaId) ? 'required|string|max:255' : 'nullable|string|max:255',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_general_specialization' => 'required|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_registration_date' => 'required|date',
                'ma_defense_date' => 'required|date|after:ma_registration_date',
                'ma_grant_date' => 'required|date|after:ma_defense_date|before_or_equal:today',
                'ma_rank' => 'required|string|max:100',
                'ma_supervisor' => 'required|string|max:255',
                'ma_thesis_title' => 'required|string',
                'ma_decision_no' => 'nullable|string|max:100',
                'ma_decision_date' => 'nullable|date',
                'ma_decision_file' => 'nullable|file|mimes:pdf|max:2048', // اختياري دائماً في الدكتوراه السورية

                // Step 5: Syrian Doctorate Degree Info
                'phd_university_id' => 'required|exists:lookup_universities,id',
                'phd_faculty' => 'required|string|max:255',
                'phd_department' => 'required|string|max:255',
                'phd_general_specialization' => 'required|string|max:255',
                'phd_specialization' => 'nullable|string|max:255',
                'phd_registration_date' => 'required|date',
                'phd_defense_date' => 'required|date|after:phd_registration_date',
                'phd_grant_date' => 'required|date|after:phd_defense_date|before_or_equal:today',
                'phd_rank' => 'required|string|max:100',
                'phd_supervisor' => 'required|string|max:255',
                'phd_thesis_title' => 'required|string',

                // Step 6: Attachments Upload (معفاة تلقائياً إذا كانت مرفوعة مسبقاً)
                'file_national_id' => $fileRuleFor(13, 'national_id'),
                'file_hs_cert' => $fileRuleFor(1, 'hs_cert'),
                'file_ba_cert' => $fileRuleFor(3, 'ba_cert'),
                'file_diploma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => $fileRuleFor(6, 'ma_cert'),
                'file_ma_council_decisions' => $fileRuleFor(7, 'ma_council_decisions'),
                'file_phd_cert' => $fileRuleFor(10, 'phd_cert'),
                'file_phd_council_decisions' => $fileRuleFor(11, 'phd_council_decisions'),
                'file_thesis_summary' => $fileRuleFor(12, 'thesis_summary'),
                'file_cv' => $fileRuleFor(14, 'cv'),
                'file_uni_request' => $fileRuleFor(15, 'uni_request'),
                'file_payment' => $fileRuleFor(17, 'payment'),
                'file_english_test' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl' => 'nullable|file|mimes:pdf|max:2048',
                'file_nat_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
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
                'file_ma_council_decisions.required' => 'يرجى رفع وثيقة قرارات وتواريخ مجلس الجامعة للماجستير (PDF).',
                'file_phd_cert.required' => 'يرجى رفع صورة مصدقة أصولاً عن شهادة الدكتوراه السورية (PDF).',
                'file_phd_council_decisions.required' => 'يرجى رفع قرارات مجلس الجامعة للدكتوراه (وثيقة تواريخ التسجيل والمناقشة والمنح) (PDF).',
                'file_thesis_summary.required' => 'يرجى رفع ملخص أطروحة الدكتوراه باللغة العربية (PDF).',
                'file_cv.required' => 'يرجى رفع السيرة الذاتية للمرشح (PDF).',
                'file_uni_request.required' => 'يرجى رفع كتاب طلب التقويم الصادر عن الجامعة (PDF).',
                'file_payment.required' => 'يرجى رفع إيصال تسديد رسم تعادل الدكتوراه 125,000 ل.س (PDF).',
                'max' => 'حجم الملف المرفق يتجاوز الحد الأقصى المسموح به (2 ميغابايت).',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // 2. Save Equivalence Profile (Candidate)
        if ($existingApp && $existingApp->candidate) {
            $profile = $existingApp->candidate;
            $profile->update([
                'full_name' => $request->filled('full_name') ? $request->full_name : $profile->full_name,
                'father_name' => $request->father_name ?? $profile->father_name,
                'mother_name' => $request->mother_name ?? $profile->mother_name,
                'national_id' => $request->filled('national_id') ? $request->national_id : $profile->national_id,
                'dob' => $request->dob ?: $profile->dob,
                'job_title' => $request->job_title ?? $profile->job_title,
                'nationality_id' => $request->nationality_id ?: $profile->nationality_id,
                'phone' => $request->phone ?? $profile->phone,
                'mobile' => $request->mobile ?? $profile->mobile,
                'email' => $request->email ?? $profile->email,
                'address' => $request->address ?? $profile->address,
                'gender' => $request->gender ?: $profile->gender,
                'is_syrian' => $request->is_syrian,
            ]);
        } else {
            $candNationalId = $request->national_id ?: ('TMP-' . time() . '-' . rand(100, 999));
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
                    'is_syrian' => $request->is_syrian,
                ]
            );
        }

        // 3. Save Application
        $appNo = ($existingApp && $existingApp->application_no) ? $existingApp->application_no : ('PHD-SY-' . rand(100000, 999999));
        $requestType = 'دكتورة داخلي';
        $wasAwaitingDocs = ($existingApp && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $appStatus = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';
        $isFirstTime = $request->input('is_first_time', 1);

        $application = $existingApp;

        if ($application) {
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

        // 4. Create Educations records
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

        $baUniId = null;
        $baUniName = trim($request->input('ba_university_text', ''));
        if ($baUniName === '') {
            $baUniName = trim($request->input('ba_university_other', ''));
        }

        if ($baUniName !== '') {
            $uni = \App\Models\LookupUniversity::where('name', $baUniName)->first()
                ?: \App\Models\LookupUniversity::where('name', 'like', "%{$baUniName}%")->first();
            if (!$uni) {
                $uni = \App\Models\LookupUniversity::create([
                    'name' => $baUniName,
                    'country_id' => $request->ba_country_id ?: $syriaId,
                ]);
            }
            $baUniId = $uni->id;
        } elseif ($request->filled('ba_university_id')) {
            $baUniId = $request->ba_university_id;
        }

        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $baUniId,
            'faculty' => $request->ba_faculty,
            'department' => $request->ba_department,
            'section_name' => $request->ba_specialization ?: null,
            'general_specialization' => $request->ba_faculty,
            'exact_specialization' => $request->ba_specialization ?: $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => $baNotes,
        ]);

        // C. Postgraduate Diploma (if applicable)
        $edDIP = null;
        if ($request->boolean('has_diploma') || $request->filled('dip_faculty') || $request->filled('dip_university_id') || $request->filled('dip_university_text') || $request->filled('dip_grant_date')) {
            $dipUniId = $request->dip_university_id;
            $dipUniName = trim($request->input('dip_university_text', ''));
            if (!$dipUniId && $dipUniName !== '') {
                $uni = \App\Models\LookupUniversity::where('name', $dipUniName)->first()
                    ?: \App\Models\LookupUniversity::create([
                        'name' => $dipUniName,
                        'country_id' => $request->dip_country_id ?? $syriaId,
                    ]);
                $dipUniId = $uni->id;
            }

            $edDIP = Education::create([
                'application_id' => $application->id,
                'education_level_id' => $dipLevelId,
                'country_id' => $request->dip_country_id ?? $syriaId,
                'university_id' => $dipUniId,
                'faculty' => $request->dip_faculty,
                'department' => $request->dip_specialization ?? null,
                'section_name' => $request->dip_specialization ?? null,
                'general_specialization' => $request->dip_faculty,
                'exact_specialization' => $request->dip_specialization ?? null,
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

        $maUniId = null;
        if ($request->ma_country_id == $syriaId) {
            $maUniId = $request->ma_university_id;
        } else {
            $maUniName = trim($request->input('ma_university_other', ''));
            if ($maUniName !== '') {
                $createdMaUni = \App\Models\LookupUniversity::firstOrCreate([
                    'name' => $maUniName,
                ], [
                    'country_id' => $request->ma_country_id ?? $syriaId,
                ]);
                $maUniId = $createdMaUni->id;
            }
        }

        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $request->ma_country_id ?? $syriaId,
            'university_id' => $maUniId,
            'university_other' => $request->ma_university_other,
            'faculty' => $request->ma_faculty,
            'department' => $request->ma_department,
            'section_name' => $request->ma_specialization ?: null,
            'general_specialization' => $request->ma_general_specialization ?: $request->ma_faculty,
            'exact_specialization' => $request->ma_specialization ?: $request->ma_department,
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
            'general_specialization' => $request->phd_general_specialization ?: $request->phd_faculty,
            'exact_specialization' => $request->phd_specialization ?: $request->phd_department,
            'registration_date' => $request->phd_registration_date,
            'defense_date' => $request->phd_defense_date,
            'grant_date' => $request->phd_grant_date,
            'rank' => $request->phd_rank,
            'supervisor_name' => $request->phd_supervisor,
            'thesis_title' => $request->phd_thesis_title,
            'notes' => null,
        ]);

        // 5. Handle File Uploads & Attachments (الأرقام المعيارية الجديدة 1 - 23)
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
                // البحث بالأولوية: بالـ ID الجديد أولاً، ثم بالاسم الرمزي، ثم بالنص
                $oldPath = $existingAttachments[$typeId]
                    ?? ($existingKey && isset($existingAttachments[$existingKey]) ? $existingAttachments[$existingKey] : null)
                    ?? ($existingAttachments[$notes] ?? null);

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

        // 1. الثانوية العامة وقرارها
        $uploadAndAttachPhd('file_hs_cert', $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', 'hs_cert');
        if ($request->hasFile('hs_decision_file') || isset($existingAttachments[2]) || isset($existingAttachments['hs_decision'])) {
            $uploadAndAttachPhd('hs_decision_file', $edHS->id, 2, 'قرار معادلة الشهادة الثانوية غير السورية', 'hs_decision');
        }

        // 2. الإجازة الجامعية الأولى وقرارها
        $uploadAndAttachPhd('file_ba_cert', $edBA->id, 3, 'مصدقة الإجازة الجامعية الأولى', 'ba_cert');
        if ($request->hasFile('ba_decision_file') || isset($existingAttachments[4]) || isset($existingAttachments['ba_decision'])) {
            $uploadAndAttachPhd('ba_decision_file', $edBA->id, 4, 'قرار معادلة الإجازة الجامعية الأولى غير السورية', 'ba_decision');
        }

        // 3. الدبلوم إن وجد
        if ($edDIP && ($request->hasFile('file_diploma_cert') || isset($existingAttachments[5]) || isset($existingAttachments['diploma_cert']))) {
            $uploadAndAttachPhd('file_diploma_cert', $edDIP->id, 5, 'شهادة دبلوم دراسات عليا / تأهيل وتخصص', 'diploma_cert');
        }

        // 4. الماجستير (الشهادة 6 / قرارات المجلس 7 / قرار التعادل غير السوري 9)
        $uploadAndAttachPhd('file_ma_cert', $edMA->id, 6, 'نسخة مصدقة عن شهادة الماجستير', 'ma_cert');
        $uploadAndAttachPhd('file_ma_council_decisions', $edMA->id, 7, 'وثيقة تواريخ وقرارات المجلس للماجستير', 'ma_council_decisions');
        if ($request->hasFile('ma_decision_file') || isset($existingAttachments[9]) || isset($existingAttachments['ma_decision'])) {
            $uploadAndAttachPhd('ma_decision_file', $edMA->id, 9, 'قرار معادلة شهادة الماجستير غير السورية', 'ma_decision');
        }

        // 5. الدكتوراه السورية (الشهادة 10 / قرارات المجلس 11 / ملخص الأطروحة 12)
        $uploadAndAttachPhd('file_phd_cert', $edPhD->id, 10, 'نسخة مصدقة أصولاً عن شهادة الدكتوراه السورية', 'phd_cert');
        $uploadAndAttachPhd('file_phd_council_decisions', $edPhD->id, 11, 'وثيقة تواريخ وقرارات المجلس للدكتوراه', 'phd_council_decisions');
        $uploadAndAttachPhd('file_thesis_summary', $edPhD->id, 12, 'ملخص أطروحة الدكتوراه باللغة العربية', 'thesis_summary');

        // 6. الوثائق العامة والشخصية والرسوم
        $uploadAndAttachPhd('file_national_id', $edPhD->id, 13, 'صورة عن الهوية الشخصية / جواز السفر', 'national_id');
        $uploadAndAttachPhd('file_cv', $edPhD->id, 14, 'السيرة الذاتية للمرشح', 'cv');
        $uploadAndAttachPhd('file_uni_request', $edPhD->id, 15, 'كتاب طلب التقويم الصادر عن الجامعة', 'uni_request');
        $uploadAndAttachPhd('file_payment', $edPhD->id, 17, 'إيصال تسديد رسم تعادل الدكتوراه (125,000 ل.س)', 'payment');

        // 7. الوثائق الاختيارية والإضافية
        if ($request->hasFile('file_english_test') || isset($existingAttachments[18]) || isset($existingAttachments['english_test'])) {
            $uploadAndAttachPhd('file_english_test', $edPhD->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'english_test');
        }
        if ($request->hasFile('file_icdl') || isset($existingAttachments[19]) || isset($existingAttachments['icdl'])) {
            $uploadAndAttachPhd('file_icdl', $edPhD->id, 19, 'شهادة مهارات الحاسوب (ICDL)', 'icdl');
        }
        if ($request->hasFile('file_nat_library_receipt') || isset($existingAttachments[22]) || isset($existingAttachments['nat_library_receipt'])) {
            $uploadAndAttachPhd('file_nat_library_receipt', $edPhD->id, 22, 'إيصال المكتبة الوطنية لاستلام الرسالة / الأطروحة', 'nat_library_receipt');
        }
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments[23]) || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttachPhd('file_other_attachments', $edPhD->id, 23, 'مرفقات ووثائق أخرى', 'other_attachments');
        }

        if ($isDraft) {
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'تم حفظ بيانات طلب المرشح كمسودة تلقائياً بنجاح! يمكنك استكمالها في أي وقت من قسم المسودات المحفوظة.');
            }
            return redirect()->route('university.drafts.index')
                ->with('success', 'تم حفظ معاملة (' . $requestType . ') كمسودة بنجاح! للطلب رقم: ' . $appNo . '. يمكنك استكمال رفع المرفقات والوثائق الناقصة في أي وقت من هنا.');
        }

        if ($wasAwaitingDocs) {
            $candidateName = $profile ? $profile->full_name : ($application->candidate ? $application->candidate->full_name : '');
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة';

            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). تم تحويل حالة المعاملة تلقائياً إلى (تحت التدقيق الأولي).",
                'is_read' => false,
            ]);

            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? 1;

            $application->notifyUniversityOfStatusChange('تحت التدقيق الأولي', 'تم استلام التعديلات والوثائق المستكملة بنجاح من الجامعة وتحويل المعاملة للتدقيق الأولي.', $systemAdminId);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم تحويل حالة المعاملة إلى (تحت التدقيق الأولي) وإشعار وزارة التعليم العالي.')
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

        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
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

        // Smart draft lookup: check if this candidate already has an open draft for Foreign Masters in this university
        if (!$existingApp && $request->filled('national_id')) {
            $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
            if ($existingProfile) {
                $existingApp = Application::where('candidate_id', $existingProfile->id)
                    ->where('work_university_id', $uniId)
                    ->where(function($q) {
                        $q->where('status', 'مسودة')->orWhere('status', 1);
                    })
                    ->where(function($q) {
                        $q->where('request_type', 'like', '%خارجي%')
                          ->orWhereIn('request_type', [5, 6]);
                    })
                    ->with(['educations.attachments', 'educations.residences'])
                    ->latest('id')
                    ->first();
            }
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
                    if ($att->attachment_type_id == 16 || str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة الثانوية')) $existingFilesMap['file_hs_decision'] = true;
                    if ($att->attachment_type_id == 17 || str_contains($note, 'معادلة الإجازة') || str_contains($note, 'معادلة البكالوريوس')) $existingFilesMap['file_ba_decision'] = true;
                    // تم ربط وثيقة حركة الهجرة والجوازات بالرقم 24
                    if ($att->attachment_type_id == 24 || str_contains($note, 'الهجرة والجوازات') || str_contains($note, 'حركة الهجرة')) $existingFilesMap['file_immigration_movement'] = true;
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
                'hs_country_other' => 'nullable|string|max:255',
                'hs_type' => 'nullable|string',
                'hs_grant_date' => 'nullable',
                'hs_decision_no' => 'nullable|string|max:100',
                'hs_decision_date' => 'nullable',

                'ba_country_id' => 'nullable',
                'ba_country_other' => 'nullable|string|max:255',
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

                'ma_country_id' => 'nullable',
                'ma_country_other' => 'nullable|string|max:255',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_department' => 'nullable|string|max:255',
                'ma_general_specialization' => 'nullable|string|max:255',
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

                'has_syrian_experience' => 'nullable',
                'syrian_exp_years' => 'nullable',
                'syrian_exp_from' => 'nullable',
                'syrian_exp_to' => 'nullable',
                'syrian_exp_universities' => 'nullable|string',

                'residences' => 'nullable|array',

                'file_secondary_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_decision' => 'nullable|file|mimes:pdf|max:2048',
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
                'file_immigration_movement' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $isExpYes = $request->input('has_syrian_experience') === 'yes';
            $isHsForeign = ($request->input('hs_country_id') && $request->input('hs_country_id') != $syriaId);
            $isBaForeign = ($request->input('ba_country_id') && $request->input('ba_country_id') != $syriaId);

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
                'hs_country_other' => 'nullable|string|max:255',
                'hs_type' => 'required|string|max:100',
                'hs_grant_date' => 'required|numeric|digits:4',
                'hs_decision_no' => $isHsForeign ? 'required|string|max:100' : 'nullable|string|max:100',
                'hs_decision_date' => $isHsForeign ? 'required|date' : 'nullable|date',

                // Step 3: Bachelor
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_country_other' => 'nullable|string|max:255',
                'ba_university_id' => 'nullable',
                'ba_university_other' => 'required|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'required|string|max:50',
                'ba_decision_no' => $isBaForeign ? 'required|string|max:100' : 'nullable|string|max:100',
                'ba_decision_date' => $isBaForeign ? 'required|date' : 'nullable|date',

                // Step 4: Foreign Master
                'ma_country_id' => [
                    'required',
                    'exists:lookup_countries,id',
                    function ($attribute, $value, $fail) use ($syriaId) {
                        if ($value == $syriaId) {
                            $fail('بلد الحصول على درجة الماجستير الخارجي يجب أن يكون بلداً غير سوري.');
                        }
                    }
                ],
                'ma_country_other' => 'nullable|string|max:255',
                'ma_university_other' => 'required|string|max:255',
                'ma_faculty' => 'required|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_general_specialization' => 'required|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_study_system' => 'nullable|string|max:100',
                'ma_study_language' => 'nullable|string|max:100',
                'ma_duration_years' => 'nullable|numeric',
                'ma_registration_date' => 'required|date',
                'ma_defense_date' => 'required|date',
                'ma_grant_date' => 'required|date|before_or_equal:today',
                'ma_rank' => 'required|string|max:50',
                'ma_thesis_title' => 'required|string|max:500',
                'ma_supervisor' => 'required|string|max:255',

                'has_syrian_experience' => 'required|in:yes,no',
                'syrian_exp_years' => 'nullable|numeric',
                'syrian_exp_from' => 'nullable|numeric',
                'syrian_exp_to' => 'nullable|numeric',
                'syrian_exp_universities' => 'nullable|string',

                'residences' => 'nullable|array',

                // Step 6: Attachments
                'file_secondary_cert' => (!empty($existingFilesMap['file_secondary_cert']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_hs_decision' => ($isHsForeign && empty($existingFilesMap['file_hs_decision']) && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => (!empty($existingFilesMap['file_bachelor_cert']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_ba_decision' => ($isBaForeign && empty($existingFilesMap['file_ba_decision']) && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_prev_qual_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_cert' => (!empty($existingFilesMap['file_master_cert']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_master_transcript' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_abstract' => (!empty($existingFilesMap['file_thesis_abstract']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_reg_defense_doc' => (!empty($existingFilesMap['file_reg_defense_doc']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_experience_cert' => ($isExpYes && empty($existingFilesMap['file_experience_cert']) && !$isExisting) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_private_uni_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_salary_receipts' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_fees_receipt' => (!empty($existingFilesMap['file_fees_receipt']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_passport' => (!empty($existingFilesMap['file_passport']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                // وثيقة حركة الهجرة والجوازات (اختياري)
                'file_immigration_movement' => 'nullable|file|mimes:pdf|max:2048',
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
                'hs_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية.',
                'hs_decision_date.required' => 'يرجى إدخال تاريخ صدور قرار معادلة الشهادة الثانوية غير السورية.',
                'ba_country_id.required' => 'يرجى اختيار بلد الإجازة الجامعية.',
                'ba_university_other.required' => 'يرجى إدخال اسم الجامعة المانحة للإجازة الجامعية.',
                'ba_faculty.required' => 'يرجى إدخال كلية الإجازة الجامعية.',
                'ba_grant_date.required' => 'يرجى إدخال تاريخ منح الإجازة الجامعية.',
                'ba_rank.required' => 'يرجى اختيار تقدير الإجازة الجامعية.',
                'ba_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الإجازة الجامعية غير السورية.',
                'ba_decision_date.required' => 'يرجى إدخال تاريخ صدور قرار معادلة الإجازة الجامعية غير السورية.',
                'ma_country_id.required' => 'يرجى اختيار بلد دراسة الماجستير.',
                'ma_university_other.required' => 'يرجى إدخال اسم الجامعة الخارجية المانحة للماجستير.',
                'ma_faculty.required' => 'يرجى إدخال كلية درجة الماجستير.',
                'ma_grant_date.required' => 'يرجى إدخال تاريخ منح شهادة الماجستير.',
                'ma_rank.required' => 'يرجى اختيار تقدير شهادة الماجستير.',
                'ma_thesis_title.required' => 'يرجى إدخال عنوان أطروحة الماجستير.',
                'has_syrian_experience.required' => 'يرجى تحديد ما إذا كان المرشح يمتلك خبرة تدريسية سنتين فأكثر داخل الجامعات السورية.',
                'file_secondary_cert.required' => 'يرجى إرفاق نسخة مصدقة عن الشهادة الثانوية.',
                'file_hs_decision.required' => 'يرجى إرفاق قرار معادلة الشهادة الثانوية غير السورية.',
                'file_bachelor_cert.required' => 'يرجى إرفاق نسخة مصدقة عن الإجازة الجامعية الأولى.',
                'file_ba_decision.required' => 'يرجى إرفاق قرار معادلة الإجازة الجامعية الأولى غير السورية.',
                'file_master_cert.required' => 'يرجى إرفاق نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي.',
                'file_thesis_abstract.required' => 'يرجى إرفاق ملخص أطروحة الماجستير باللغة العربية.',
                'file_reg_defense_doc.required' => 'يرجى إرفاق وثيقة تواريخ التسجيل والمباشرة والمناقشة.',
                'file_experience_cert.required' => 'يرجى إرفاق شهادة الخبرة التدريسية المثبتة داخل الجامعات السورية لمسار التدريس النظري.',
                'file_english_cert.required' => 'يرجى إرفاق وثيقة اجتياز اختبار اللغة الأجنبية (الإنكليزية).',
                'file_icdl_cert.required' => 'يرجى إرفاق شهادة مهارات الحاسوب (ICDL).',
                'file_fees_receipt.required' => 'يرجى إرفاق إيصال تسديد رسم تعادل الماجستير الخارجي.',
                'file_passport.required' => 'يرجى إرفاق صورة جواز السفر وصفحات الأختام والإقامة ببلد الدراسة.',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update
        $fullName = $request->filled('full_name') ? $request->full_name : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة ماجستير خارجي');
        $nationalId = $request->filled('national_id') ? $request->national_id : ($isExisting && $existingApp->candidate ? $existingApp->candidate->national_id : ('TMP-' . time() . '-' . rand(100, 999)));

        if ($isExisting && $existingApp->candidate) {
            $candidate = $existingApp->candidate;
            $candidate->update([
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? $candidate->father_name,
                'mother_name' => $request->mother_name ?? $candidate->mother_name,
                'national_id' => $nationalId,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob' => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title' => 'مرشح تعادل ماجستير خارجي',
                'phone' => $request->phone ?? $candidate->phone,
                'mobile' => $request->mobile ?? $candidate->mobile,
                'email' => $request->email ?? $candidate->email,
                'address' => $request->address ?? $candidate->address,
                'gender' => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian' => ($request->nationality_id == $syriaId),
            ]);
        } else {
            $candidate = EquivalenceProfile::updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'full_name' => $fullName,
                    'father_name' => $request->father_name ?? '',
                    'mother_name' => $request->mother_name ?? '',
                    'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                    'dob' => $request->filled('dob') ? $request->dob : null,
                    'job_title' => 'مرشح تعادل ماجستير خارجي',
                    'phone' => $request->phone,
                    'mobile' => $request->mobile ?? '',
                    'email' => $request->email ?? '',
                    'address' => $request->address ?? '',
                    'gender' => $request->gender ?? 'ذكر',
                    'is_syrian' => ($request->nationality_id == $syriaId),
                ]
            );
        }

        // 3. Determine Application Type & Number
        $hasExp = $request->input('has_syrian_experience') === 'yes';
        $requestType = $hasExp ? 'ماجستير خارجي - نظري' : 'ماجستير خارجي - تطبيقي';
        $appNo = $isExisting ? $existingApp->application_no : ('MA-FOR-' . rand(100000, 999999));

        $wasAwaitingDocs = ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $statusToSet = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';

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
                'study_system' => $request->ma_study_system ?? 'سنوي / فصلي',
                'has_previous_degree' => $hasExp,
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
        $hsDecNotes = [];
        if ($request->filled('hs_decision_no')) {
            $hsDecNotes[] = 'رقم قرار المعادلة الثانوية: ' . $request->hs_decision_no;
        }
        if ($request->filled('hs_decision_date')) {
            $hsDecNotes[] = 'تاريخ القرار: ' . $request->hs_decision_date;
        }
        $hsNotes = !empty($hsDecNotes) ? implode(' | ', $hsDecNotes) : (optional($existingHsEd)->notes ?? '');

        $hsCountryId = ($request->filled('hs_country_id') && is_numeric($request->hs_country_id)) ? $request->hs_country_id : (optional($existingHsEd)->country_id ?? $syriaId);

        $hsEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 4,
            ],
            [
                'country_id' => $hsCountryId,
                'section_name' => $request->filled('hs_type') ? $request->hs_type : (optional($existingHsEd)->section_name ?? 'علمي'),
                'grant_date' => $hsGrantDate,
                'notes' => $hsNotes,
            ]
        );

        // 4.2 Bachelor Degree (Level 1)
        $existingBaEd = Education::where('application_id', $application->id)
            ->where('education_level_id', 1)
            ->first();

        $baCountryId = ($request->filled('ba_country_id') && is_numeric($request->ba_country_id)) ? $request->ba_country_id : (optional($existingBaEd)->country_id ?? $syriaId);

        $baUniId = null;
        if (($request->input('ba_university_id') === 'other' || !$request->filled('ba_university_id')) && $request->filled('ba_university_other')) {
            $createdBaUni = \App\Models\LookupUniversity::firstOrCreate([
                'name' => trim($request->ba_university_other),
            ], [
                'country_id' => $baCountryId,
            ]);
            $baUniId = $createdBaUni->id;
        } elseif ($request->filled('ba_university_id') && is_numeric($request->ba_university_id)) {
            $baUniId = $request->ba_university_id;
        } else {
            $baUniId = optional($existingBaEd)->university_id;
        }

        $baDecNotes = [];
        if ($request->filled('ba_decision_no')) {
            $baDecNotes[] = 'رقم قرار معادلة الإجازة: ' . $request->ba_decision_no;
        }
        if ($request->filled('ba_decision_date')) {
            $baDecNotes[] = 'تاريخ القرار: ' . $request->ba_decision_date;
        }
        $baNotes = !empty($baDecNotes) ? implode(' | ', $baDecNotes) : (optional($existingBaEd)->notes ?? '');

        $baEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 1,
            ],
            [
                'country_id' => $baCountryId,
                'university_id' => $baUniId,
                'faculty' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->faculty ?? ''),
                'department' => $request->filled('ba_department') ? $request->ba_department : (optional($existingBaEd)->department ?? ''),
                'general_specialization' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('ba_department') ? $request->ba_department : (optional($existingBaEd)->exact_specialization ?? ''),
                'section_name' => $request->filled('ba_specialization') ? $request->ba_specialization : (optional($existingBaEd)->section_name ?? null),
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

        $maCountryId = ($request->filled('ma_country_id') && is_numeric($request->ma_country_id)) ? $request->ma_country_id : optional($existingMaEd)->country_id;

        $maUniId = null;
        if ($request->filled('ma_university_other')) {
            $createdMaUni = \App\Models\LookupUniversity::firstOrCreate([
                'name' => trim($request->ma_university_other),
            ], [
                'country_id' => $maCountryId,
            ]);
            $maUniId = $createdMaUni->id;
        } elseif ($request->filled('ma_university_id') && is_numeric($request->ma_university_id)) {
            $maUniId = $request->ma_university_id;
        } else {
            $maUniId = optional($existingMaEd)->university_id;
        }

        $maEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 2,
            ],
            [
                'country_id' => $maCountryId,
                'university_id' => $maUniId,
                'university_other' => $request->filled('ma_university_other') ? $request->ma_university_other : (optional($existingMaEd)->university_other ?? ''),
                'faculty' => $request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->faculty ?? ''),
                'department' => $request->filled('ma_department') ? $request->ma_department : (optional($existingMaEd)->department ?? ''),
                'general_specialization' => $request->filled('ma_general_specialization') ? $request->ma_general_specialization : ($request->filled('ma_faculty') ? $request->ma_faculty : (optional($existingMaEd)->general_specialization ?? '')),
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
                'experience_from_year' => $request->filled('syrian_exp_from') ? $request->syrian_exp_from : optional($existingMaEd)->experience_from_year,
                'experience_to_year' => $request->filled('syrian_exp_to') ? $request->syrian_exp_to : optional($existingMaEd)->experience_to_year,
                'notes' => $expNotes ?: optional($existingMaEd)->notes,
            ]
        );

        // 4.4 Store / Update Education Residences (حركات الإقامة والدخول والخروج)
        if ($request->has('residences') && is_array($request->residences)) {
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
                        'stamp_details' => $resData['stamp_details'] ?? null,
                    ]);
                }
            }
        }

        // 5. Attachment File Uploads (تم استبدال الإيفاد بوثيقة حركة الهجرة والجوازات رقم 24)
        $fileInputs = [
            'file_secondary_cert' => ['id' => 1, 'notes' => 'نسخة مصدقة عن الشهادة الثانوية', 'ed_id' => $hsEd->id],
            'file_hs_decision' => ['id' => 16, 'notes' => 'قرار معادلة الشهادة الثانوية غير السورية', 'ed_id' => $hsEd->id],
            'file_bachelor_cert' => ['id' => 2, 'notes' => 'نسخة مصدقة عن الإجازة الجامعية الأولى', 'ed_id' => $baEd->id],
            'file_ba_decision' => ['id' => 17, 'notes' => 'قرار معادلة الإجازة الجامعية الأولى غير السورية', 'ed_id' => $baEd->id],
            'file_prev_qual_cert' => ['id' => 3, 'notes' => 'الشهادة قبل المؤهل العلمي الأخير', 'ed_id' => $maEd->id],
            'file_master_cert' => ['id' => 4, 'notes' => 'نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي', 'ed_id' => $maEd->id],
            'file_master_transcript' => ['id' => 5, 'notes' => 'كشف علامات الماجستير', 'ed_id' => $maEd->id],
            'file_thesis_abstract' => ['id' => 6, 'notes' => 'ملخص عن الأطروحة باللغة العربية', 'ed_id' => $maEd->id],
            'file_library_receipt' => ['id' => 7, 'notes' => 'إيصال إيداع الأطروحة لدى المكتبة الوطنية', 'ed_id' => $maEd->id],
            'file_reg_defense_doc' => ['id' => 8, 'notes' => 'وثيقة تواريخ التسجيل والمباشرة والمناقشة', 'ed_id' => $maEd->id],
            'file_experience_cert' => ['id' => 9, 'notes' => 'شهادة الخبرة التدريسية داخل سوريا', 'ed_id' => $maEd->id],
            'file_private_uni_contracts' => ['id' => 10, 'notes' => 'عقود التدريس مع الجامعة الخاصة', 'ed_id' => $maEd->id],
            'file_salary_receipts' => ['id' => 11, 'notes' => 'إيصالات الرواتب من الجامعة', 'ed_id' => $maEd->id],
            'file_icdl_cert' => ['id' => 12, 'notes' => 'شهادة مهارات الحاسوب (ICDL)', 'ed_id' => $maEd->id],
            'file_english_cert' => ['id' => 13, 'notes' => 'وثيقة اجتياز اختبار اللغة الإنكليزية', 'ed_id' => $maEd->id],
            'file_fees_receipt' => ['id' => 14, 'notes' => 'إيصال تسديد رسم تعادل الماجستير الخارجي (100,000 ل.س)', 'ed_id' => $maEd->id],
            'file_passport' => ['id' => 15, 'notes' => 'صورة جواز السفر وصفحات الإقامة والأختام', 'ed_id' => $maEd->id],
            // هنا المرفق الجديد رقم 24
            'file_immigration_movement' => ['id' => 24, 'notes' => 'وثيقة حركة الهجرة والجوازات', 'ed_id' => $maEd->id],
        ];

        foreach ($fileInputs as $inputKey => $meta) {
            $typeId = $meta['id'];
            $note = $meta['notes'];
            $targetEdId = $meta['ed_id'] ?? $maEd->id;
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $cleanCandidate = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name)));
                $filename = 'MA_FOR_' . $appNo . '_Type' . $typeId . '_' . ($cleanCandidate ?: 'Candidate') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');

                EducationAttachment::updateOrCreate(
                    [
                        'education_id' => $targetEdId,
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
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'تم حفظ بيانات طلب المرشح كمسودة تلقائياً بنجاح! يمكنك استكمالها في أي وقت من قسم المسودات المحفوظة.');
            }
            return redirect()->route('university.drafts.index')
                ->with('success', '💾 تم حفظ مسودة معاملة الماجستير الخارجي بنجاح برقم: ' . $appNo . '. يمكنك العودة لتعديلها أو استكمالها في أي وقت من هنا.');
        }

        // Notify Admin and University if documents were updated from awaiting documents
        if ($wasAwaitingDocs) {
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة الخاصة';
            $candidateName = $candidate->full_name;

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة لمعاملة الماجستير الخارجي رقم (#{$appNo}) للمرشح ({$candidateName}). تم تحويل حالة المعاملة تلقائياً إلى (تحت التدقيق الأولي).",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? 1;

            $application->notifyUniversityOfStatusChange('تحت التدقيق الأولي', 'تم استلام التعديلات والوثائق المستكملة بنجاح من الجامعة وتحويل المعاملة للتدقيق الأولي.', $systemAdminId);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم تحويل حالة المعاملة إلى (تحت التدقيق الأولي) وإشعار وزارة التعليم العالي.')
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

        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
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
                ->where('work_university_id', $uniId)
                ->with(['educations.attachments'])
                ->first();
        }

        // Smart draft lookup: check if this candidate already has an open draft for Faculty Permission in this university
        if (!$existingApp && $request->filled('national_id')) {
            $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
            if ($existingProfile) {
                $existingApp = Application::where('candidate_id', $existingProfile->id)
                    ->where('work_university_id', $uniId)
                    ->where(function($q) {
                        $q->where('status', 'مسودة')->orWhere('status', 1);
                    })
                    ->where(function($q) {
                        $q->where('request_type', 'like', '%سماح%')
                          ->orWhere('request_type', 'like', '%تدريس%')
                          ->orWhere('request_type', 4);
                    })
                    ->with(['educations.attachments'])
                    ->latest('id')
                    ->first();
            }
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
                'file_id_card' => (!empty($existingFilesMap['file_id_card']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_uni_request' => (!empty($existingFilesMap['file_uni_request']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_service_statement' => (!empty($existingFilesMap['file_service_statement']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_phd_cert' => (!empty($existingFilesMap['file_phd_cert']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_payment' => (!empty($existingFilesMap['file_payment']) || $isExisting) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
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

        if ($isExisting && $existingApp->candidate) {
            $candidate = $existingApp->candidate;
            $candidate->update([
                'full_name' => $fullName,
                'father_name' => $request->father_name ?? $candidate->father_name,
                'mother_name' => $request->mother_name ?? $candidate->mother_name,
                'national_id' => $nationalId,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob' => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title' => $request->academic_rank ?? ($candidate->job_title ?? 'عضو هيئة تدريسية'),
                'phone' => $request->phone ?? $candidate->phone,
                'mobile' => $request->mobile ?? $candidate->mobile,
                'email' => $request->email ?? $candidate->email,
                'address' => $request->address ?? $candidate->address,
                'gender' => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian' => true,
            ]);
        } else {
            $candidate = EquivalenceProfile::updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'full_name' => $fullName,
                    'father_name' => $request->father_name ?? '',
                    'mother_name' => $request->mother_name ?? '',
                    'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                    'dob' => $request->filled('dob') ? $request->dob : null,
                    'job_title' => $request->academic_rank ?? 'عضو هيئة تدريسية',
                    'phone' => $request->phone,
                    'mobile' => $request->mobile ?? '',
                    'email' => $request->email ?? '',
                    'address' => $request->address ?? '',
                    'gender' => $request->gender ?? 'ذكر',
                    'is_syrian' => true,
                ]
            );
        }

        // 3. Application Creation / Update
        $requestType = 'عضو هيئة تدريسية - سماح';
        $appNo = $isExisting ? $existingApp->application_no : ('FAC-' . rand(100000, 999999));

        $wasAwaitingDocs = ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $statusToSet = $isDraft ? 'مسودة' : 'تحت التدقيق الأولي';

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
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'تم حفظ بيانات طلب المرشح كمسودة تلقائياً بنجاح! يمكنك استكمالها في أي وقت من قسم المسودات المحفوظة.');
            }
            return redirect()->route('university.drafts.index')
                ->with('success', '💾 تم حفظ المسودة بنجاح برقم: ' . $appNo . '. يمكنك العودة لتعديلها أو استكمالها في أي وقت من هنا.');
        }

        // Notify Admin and University if documents were updated from awaiting documents
        if ($wasAwaitingDocs) {
            $uniName = Auth::user()->university ? Auth::user()->university->name : 'الجامعة الخاصة';
            $candidateName = $candidate->full_name;

            // 1. Notification to Admin
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => Auth::id(),
                'message' => "📑 [استكمال وتعديل وثائق]: قامت جامعة ({$uniName}) بالانتهاء من تعديل واستكمال الوثائق والبيانات المطلوبة لمعاملة السماح بالتدريس رقم (#{$appNo}) للمرشح ({$candidateName}). تم تحويل حالة المعاملة تلقائياً إلى (تحت التدقيق الأولي).",
                'is_read' => false,
            ]);

            // 2. Notification to University
            $systemAdminId = User::whereHas('role', function($q) {
                $q->where('name', 'admin');
            })->where('id', '!=', Auth::id())->value('id') ?? 1;

            $application->notifyUniversityOfStatusChange('تحت التدقيق الأولي', 'تم استلام التعديلات والوثائق المستكملة بنجاح من الجامعة وتحويل المعاملة للتدقيق الأولي.', $systemAdminId);

            return redirect()->route('university.dashboard')
                ->with('success', 'تم استكمال وتعديل الوثائق والبيانات المطلوبة للطلب رقم: ' . $appNo . ' بنجاح! تم تحويل حالة المعاملة إلى (تحت التدقيق الأولي) وإشعار وزارة التعليم العالي.')
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

        $userUniId = Auth::user() ? Auth::user()->university_id : null;
        $activeDraft = null;
        if ($userUniId) {
            $activeDraft = $profile->applications
                ->where('work_university_id', $userUniId)
                ->filter(function($app) {
                    return $app->status === 'مسودة' || $app->status == 1;
                })
                ->sortByDesc('id')
                ->first();
        }

        return response()->json([
            'success' => true,
            'candidate' => $candidateData,
            'high_school' => $hsData,
            'bachelor' => $baData,
            'master' => $maData,
            'doctorate' => $phdData,
            'draft_id' => $activeDraft ? $activeDraft->id : null,
            'draft_app_no' => $activeDraft ? $activeDraft->application_no : null,
            'draft_type' => $activeDraft ? $activeDraft->request_type : null,
        ]);
    }
}



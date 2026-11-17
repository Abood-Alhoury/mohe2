<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;
use App\Models\LookupAttachmentType;
use App\Models\EquivalenceProfile;
use Illuminate\Validation\Rule;
use App\Models\Application;
use App\Models\ApplicationCourse;
use App\Models\Education;
use App\Models\EducationAttachment;
use App\Models\EducationResidence;
use App\Models\ApplicationMessage;
use App\Models\ApplicationExperience;
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

    private function prepareSyrianMastersWizardData(Request $request, string $trackType, ?Application $adminDraft = null): array
    {
        $isAdminEdit = ($adminDraft !== null);
        $draft = $adminDraft;
        if (!$isAdminEdit && $request->filled('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType', 'courses', 'experiences'])
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
        $notifications = $isAdminEdit ? collect() : $this->getUnreadNotifications();

        $prevUniId = $isAdminEdit ? $draft->work_university_id : Auth::user()->university_id;
        $previousApplications = Application::where('work_university_id', $prevUniId)
            ->where('status', '!=', 'مسودة')
            ->with('candidate')
            ->orderBy('id', 'desc')
            ->get();

        $adminSubmitRoute = $isAdminEdit ? route('admin.applications.wizard_update', $draft->id) : null;
        $adminWizardTrack = $isAdminEdit ? ('syrian_masters_' . $trackType) : null;

        return compact(
            'countries',
            'universities',
            'educationLevels',
            'syriaId',
            'notifications',
            'draft',
            'previousApplications',
            'trackType',
            'isAdminEdit',
            'adminSubmitRoute',
            'adminWizardTrack'
        );
    }

    public function showSyrianMastersAppliedWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.syrian_masters_applied', $this->prepareSyrianMastersWizardData($request, 'applied'));
    }

    public function showSyrianMastersTheoreticalWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.syrian_masters_theoretical', $this->prepareSyrianMastersWizardData($request, 'theoretical'));
    }

    public function showSyrianMastersWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $trackType = in_array($request->query('track'), ['applied', 'theoretical'], true)
            ? $request->query('track')
            : null;

        $data = $this->prepareSyrianMastersWizardData($request, $trackType ?? 'applied');

        if ($trackType === null && $data['draft']) {
            $draftType = (string) ($data['draft']->request_type ?? '');
            $draftExpDays = (int) ($data['draft']->experiences ? $data['draft']->experiences->sum('duration_days') : 0);
            $data['trackType'] = (str_contains($draftType, 'نظري') || (!str_contains($draftType, 'تطبيقي') && $draftExpDays >= 730))
                ? 'theoretical'
                : 'applied';
        }

        return view('university.apply.syrian_masters', $data);
    }

    public function submitSyrianMastersAppliedWizard(Request $request)
    {
        return $this->submitSyrianMastersWizard($request, 'applied');
    }

    public function submitSyrianMastersTheoreticalWizard(Request $request)
    {
        return $this->submitSyrianMastersWizard($request, 'theoretical');
    }

    public function submitSyrianMastersWizard(Request $request, ?string $forcedTrack = null)
    {
        $isAdminEdit = (bool) $request->attributes->get('admin_wizard_edit', false);
        if (!$isAdminEdit && \App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $trackType = $forcedTrack;
        if (!in_array($trackType, ['applied', 'theoretical'], true)) {
            $reqTrack = $request->input('track_type');
            if (in_array($reqTrack, ['applied', 'theoretical'], true)) {
                $trackType = $reqTrack;
            } else {
                $trackType = ($request->boolean('has_experience') || $request->input('has_experience') == '1') ? 'theoretical' : 'applied';
            }
        }
        $isTheoreticalTrack = ($trackType === 'theoretical');
        $targetRequestTypes = $isTheoreticalTrack
            ? ['ماجستير داخلي - نظري', 2]
            : ['ماجستير داخلي - تطبيقي', 1];

        $existingApp = null;
        if ($isAdminEdit) {
            $adminAppId = (int) $request->attributes->get('admin_target_app_id');
            $existingApp = Application::with(['candidate', 'educations.level', 'educations.attachments', 'experiences', 'courses'])
                ->findOrFail($adminAppId);
            $uniId = $request->filled('work_university_id') ? (int) $request->input('work_university_id') : $existingApp->work_university_id;
        } else {
            $uniId = Auth::user()->university_id;

            // 1. التقاط معرّف الطلب في كافة الحالات
            $appId = $request->input('draft_id') ?? $request->input('application_id') ?? $request->input('app_id') ?? $request->input('id');
            if ($appId) {
                $existingApp = Application::where('id', $appId)
                    ->where('work_university_id', $uniId)
                    ->with(['educations.level', 'educations.attachments'])
                    ->first();
            }

            // البحث الاحتياطي الذكي بالرقم الوطني (معزول حسب المسار)
            if (!$existingApp && $request->filled('national_id')) {
                $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
                if ($existingProfile) {
                    $existingApp = Application::where('candidate_id', $existingProfile->id)
                        ->where('work_university_id', $uniId)
                        ->where(function($q) {
                            $q->whereIn('status', ['مسودة', 'بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 1, 3]);
                        })
                        ->where(function($q) use ($targetRequestTypes) {
                            $q->whereIn('request_type', $targetRequestTypes);
                        })
                        ->with(['educations.level', 'educations.attachments'])
                        ->latest('id')
                        ->first();
                }
            }
        }

        $isExisting = ($existingApp !== null);
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        // 2. تجميع المرفقات السابقة بدون تعارض للأنواع المشتركة (7 و 21)
        $existingAttachments = [];
        $sharedTypeIds = [7, 21];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $notes = trim($att->notes ?? '');
                    $tId = (int) ($att->attachment_type_id ?? 0);
                    $path = $att->file_path;
                    if (!$path) continue;

                    if ($tId && !in_array($tId, $sharedTypeIds, true)) {
                        $existingAttachments[$tId] = $path;
                    }
                    if ($notes !== '') {
                        $existingAttachments[$notes] = $path;
                    }

                    // مطابقة الملاحظات المحددة أولاً لمنع التداخل
                    if (str_contains($notes, 'هوية') || str_contains($notes, 'شخصية') || str_contains($notes, 'جواز') || $tId === 11) {
                        $existingAttachments[13] = $path;
                        $existingAttachments['national_id'] = $path;
                        $existingAttachments['file_national_id'] = $path;
                    } elseif ((str_contains($notes, 'قرار') && str_contains($notes, 'ثانوية')) || $tId === 2) {
                        $existingAttachments[2] = $path;
                        $existingAttachments['hs_decision'] = $path;
                        $existingAttachments['hs_decision_file'] = $path;
                        $existingAttachments['file_hs_decision'] = $path;
                    } elseif ((str_contains($notes, 'ثانوية') && !str_contains($notes, 'قرار')) || $tId === 1) {
                        $existingAttachments[1] = $path;
                        $existingAttachments['hs_cert'] = $path;
                        $existingAttachments['file_hs_cert'] = $path;
                    } elseif (str_contains($notes, 'قرار') && str_contains($notes, 'الإجازة') || $tId === 4) {
                        $existingAttachments[4] = $path;
                        $existingAttachments['ba_decision'] = $path;
                        $existingAttachments['ba_decision_file'] = $path;
                        $existingAttachments['file_ba_decision'] = $path;
                    } elseif ((str_contains($notes, 'الإجازة') && !str_contains($notes, 'قرار')) || $tId === 3) {
                        $existingAttachments[3] = $path;
                        $existingAttachments['ba_cert'] = $path;
                        $existingAttachments['file_ba_cert'] = $path;
                    } elseif (str_contains($notes, 'المناقشة')) {
                        $existingAttachments['ma_defense_decision'] = $path;
                        $existingAttachments['file_ma_defense_decision'] = $path;
                    } elseif (str_contains($notes, 'منح') && str_contains($notes, 'ماجستير')) {
                        $existingAttachments['ma_grant_decision'] = $path;
                        $existingAttachments['file_ma_grant_decision'] = $path;
                    } elseif (str_contains($notes, 'بالتسجيل') || (str_contains($notes, 'التسجيل') && str_contains($notes, 'ماجستير'))) {
                        $existingAttachments['ma_reg_decision'] = $path;
                        $existingAttachments['file_ma_reg_decision'] = $path;
                    } elseif (str_contains($notes, 'تواريخ') || str_contains($notes, 'مجلس') || $tId === 7 || ($tId === 16 && str_contains($notes, 'ماجستير') && !str_contains($notes, 'إيصال') && !str_contains($notes, 'تسديد') && !str_contains($notes, 'رسم'))) {
                        $existingAttachments['ma_dates'] = $path;
                        $existingAttachments['file_ma_dates'] = $path;
                    } elseif ((str_contains($notes, 'شهادة') && str_contains($notes, 'ماجستير')) || $tId === 6 || $tId === 12) {
                        $existingAttachments[6] = $path;
                        $existingAttachments['ma_cert'] = $path;
                        $existingAttachments['file_ma_cert'] = $path;
                    } elseif (str_contains($notes, 'ملخص') || $tId === 8 || $tId === 17) {
                        $existingAttachments[8] = $path;
                        $existingAttachments['thesis_summary'] = $path;
                        $existingAttachments['file_thesis_summary'] = $path;
                    } elseif (str_contains($notes, 'المكتبة') || $tId === 22 || $tId === 25) {
                        $existingAttachments[22] = $path;
                        $existingAttachments['nat_library_receipt'] = $path;
                        $existingAttachments['file_nat_library_receipt'] = $path;
                    } elseif (str_contains($notes, 'السيرة') || str_contains($notes, 'سيرة') || $tId === 14) {
                        $existingAttachments[14] = $path;
                        $existingAttachments['cv'] = $path;
                        $existingAttachments['file_cv'] = $path;
                    } elseif (str_contains($notes, 'كتاب') || str_contains($notes, 'طلب') || $tId === 15) {
                        $existingAttachments[15] = $path;
                        $existingAttachments['uni_request'] = $path;
                        $existingAttachments['file_uni_request'] = $path;
                    } elseif (str_contains($notes, 'إيصال') || str_contains($notes, 'تسديد') || str_contains($notes, 'رسم') || $tId === 16) {
                        $existingAttachments[16] = $path;
                        $existingAttachments['payment'] = $path;
                        $existingAttachments['file_payment'] = $path;
                    } elseif (str_contains($notes, 'اللغة') || $tId === 18) {
                        $existingAttachments[18] = $path;
                        $existingAttachments['lang_cert'] = $path;
                        $existingAttachments['file_lang_cert'] = $path;
                    } elseif (str_contains($notes, 'ICDL') || str_contains($notes, 'حاسوب') || $tId === 19) {
                        $existingAttachments[19] = $path;
                        $existingAttachments['icdl_cert'] = $path;
                        $existingAttachments['file_icdl_cert'] = $path;
                    } elseif (str_contains($notes, 'خبرة') || $tId === 20) {
                        $existingAttachments[20] = $path;
                        $existingAttachments['exp_cert'] = $path;
                        $existingAttachments['file_exp_cert'] = $path;
                    } elseif ((str_contains($notes, 'كشوفات الرواتب') || str_contains($notes, 'التحويلات البنكية') || (str_contains($notes, 'رواتب') && !str_contains($notes, 'العقود')))) {
                        $existingAttachments['salary_slips'] = $path;
                        $existingAttachments['file_salary_slips'] = $path;
                    } elseif (str_contains($notes, 'أخرى') || str_contains($notes, 'اخرى') || $tId === 23 || $tId === 24) {
                        $existingAttachments[23] = $path;
                        $existingAttachments['other_attachments'] = $path;
                        $existingAttachments['file_other_attachments'] = $path;
                    } elseif (str_contains($notes, 'العقود') || str_contains($notes, 'عقود') || $tId === 21) {
                        $existingAttachments['contracts'] = $path;
                        $existingAttachments['file_contracts'] = $path;
                    } elseif ($tId === 13) {
                        $existingAttachments[13] = $path;
                        $existingAttachments['national_id'] = $path;
                        $existingAttachments['file_national_id'] = $path;
                    }
                }
            }
        }

        $reqOrNullable = fn($typeId, $key, $max = 2048) => (
            isset($existingAttachments[$key]) ||
            isset($existingAttachments['file_' . $key]) ||
            ($typeId && !in_array($typeId, $sharedTypeIds, true) && isset($existingAttachments[$typeId]))
        )
            ? "nullable|file|mimes:pdf|max:{$max}"
            : "required|file|mimes:pdf|max:{$max}";

        $hasHsDecision = isset($existingAttachments[2]) || isset($existingAttachments['hs_decision']) || isset($existingAttachments['hs_decision_file']);
        $hasBaDecision = isset($existingAttachments[4]) || isset($existingAttachments['ba_decision']) || isset($existingAttachments['ba_decision_file']);
        $hasContracts  = isset($existingAttachments['contracts']) || isset($existingAttachments['file_contracts']);
        $hasSalarySlips = isset($existingAttachments['salary_slips']) || isset($existingAttachments['file_salary_slips']);
        $hasExpCert     = isset($existingAttachments[20]) || isset($existingAttachments['exp_cert']) || isset($existingAttachments['file_exp_cert']);

        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && (
            $existingApp->status_id == 3 || 
            in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة', 3])
        ));

        if (!$isAdminEdit && $wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ هذا الطلب كمسودة لأن المعاملة قيد المعالجة (بانتظار استكمال الوثائق). يرجى استكمال البيانات والمرفقات ثم النقر على زر الإرسال.');
        }

        $isDraft = !$isAdminEdit && !$wasAwaitingDocs && ($request->input('action') === 'save_draft');

        // تطبيع وتوحيد مصفوفة الخبرات التدريسية للتوافق بين institution و institution_name (في المسار النظري فقط)
        $calcExpDays = 0;
        if ($isTheoreticalTrack) {
            if ($request->has('experiences') && is_array($request->input('experiences'))) {
                $normExp = [];
                foreach ($request->input('experiences') as $idx => $exp) {
                    if (is_array($exp)) {
                        $inst = trim($exp['institution_name'] ?? $exp['institution'] ?? '');
                        if ($inst !== '') {
                            $exp['institution_name'] = $inst;
                            $exp['institution'] = $inst;
                        }
                        $normExp[$idx] = $exp;
                    }
                }
                $request->merge(['experiences' => $normExp]);
            }

            // حساب إجمالي أيام فترات الخبرة التدريسية للتحقق من شرط السنتين (730 يوماً)
            if ($request->has('experiences') && is_array($request->input('experiences'))) {
                foreach ($request->input('experiences') as $exp) {
                    $inst = !empty($exp['institution_name']) ? $exp['institution_name'] : (!empty($exp['institution']) ? $exp['institution'] : null);
                    if ($inst && !empty($exp['start_date']) && !empty($exp['end_date'])) {
                        try {
                            $sDate = \Carbon\Carbon::parse($exp['start_date']);
                            $eDate = \Carbon\Carbon::parse($exp['end_date']);
                            if ($eDate->gte($sDate)) {
                                $calcExpDays += (int) $sDate->diffInDays($eDate);
                            }
                        } catch (\Exception $e) {}
                    }
                }
            }
            if ($calcExpDays == 0 && $request->filled('exp_from_year') && $request->filled('exp_to_year')) {
                try {
                    $sDate = \Carbon\Carbon::parse($request->exp_from_year);
                    $eDate = \Carbon\Carbon::parse($request->exp_to_year);
                    if ($eDate->gte($sDate)) {
                        $calcExpDays += (int) $sDate->diffInDays($eDate);
                    }
                } catch (\Exception $e) {}
            }
        }

        // 3. قواعد التحقق (Validation Rules)
        if ($isDraft || $isAdminEdit) {
            $rules = [
                'track_type' => 'nullable|string|in:applied,theoretical',
                'full_name' => $isAdminEdit ? 'required|string|max:255' : 'nullable|string|max:255',
                'national_id' => $isAdminEdit ? 'nullable|string|max:50' : 'nullable|string|regex:/^[0-9]{1,11}$/',
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
                'file_hs_decision' => 'nullable|file|mimes:pdf|max:2048',

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
                'experiences' => 'nullable|array',
                'experiences.*.institution_name' => 'nullable|string|max:255',
                'experiences.*.institution' => 'nullable|string|max:255',
                'experiences.*.role_or_subject' => 'nullable|string|max:255',
                'experiences.*.start_date' => 'nullable',
                'experiences.*.end_date' => 'nullable',
                'experiences.*.duration_days' => 'nullable',
                'experiences.*.notes' => 'nullable|string',

                'req_no' => 'nullable',
                'req_date' => 'nullable',
                'is_first_time' => 'nullable',
                'parent_application_id' => 'nullable|integer',

                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_defense_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_grant_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_reg_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_dates' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:5120',
                'file_lang_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_nat_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_payment' => 'nullable|file|mimes:pdf|max:2048',
                'file_exp_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_salary_slips' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $rules = [
                'track_type' => 'nullable|string|in:applied,theoretical',
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

                'has_experience' => 'nullable',
                'exp_place' => 'nullable|string|max:255',
                'exp_from_year' => 'nullable|date',
                'exp_to_year' => 'nullable|date|after_or_equal:exp_from_year',

                'experiences' => 'nullable|array',
                'experiences.*.institution_name' => 'nullable|string|max:255',
                'experiences.*.institution' => 'nullable|string|max:255',
                'experiences.*.role_or_subject' => 'nullable|string|max:255',
                'experiences.*.start_date' => 'nullable|date',
                'experiences.*.end_date' => 'nullable|date|after_or_equal:experiences.*.start_date',
                'experiences.*.duration_days' => 'nullable',
                'experiences.*.notes' => 'nullable|string',

                'req_no' => 'required',
                'req_date' => 'required|date',
                'parent_application_id' => 'nullable|integer',
                'courses' => 'nullable|array',
                'courses.*.name' => 'nullable|string|max:255',
                'courses.*.faculty' => 'nullable|string|max:255',
                'courses.*.department' => 'nullable|string|max:255',

                // الـ 13 مرفقاً المشتركة (10 إلزامية + 3 اختيارية) + حقول التوافق القديمة
                'file_national_id' => $reqOrNullable(13, 'national_id'),
                'file_hs_cert' => $reqOrNullable(1, 'hs_cert'),
                'file_ba_cert' => $reqOrNullable(3, 'ba_cert'),
                'file_ma_cert' => $reqOrNullable(6, 'ma_cert'),
                'file_ma_defense_decision' => $reqOrNullable(7, 'ma_defense_decision'),
                'file_ma_grant_decision' => $reqOrNullable(7, 'ma_grant_decision'),
                'file_ma_reg_decision' => $reqOrNullable(7, 'ma_reg_decision'),
                'file_payment' => $reqOrNullable(16, 'payment'),
                'file_cv' => $reqOrNullable(14, 'cv'),
                'file_thesis_summary' => $reqOrNullable(8, 'thesis_summary', 5120),
                'file_nat_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_lang_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                // مرفقات المسار النظري الإلزامية (3 مرفقات إضافية)
                'file_contracts' => ($isTheoreticalTrack && !$hasContracts) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_salary_slips' => ($isTheoreticalTrack && !$hasSalarySlips) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_exp_cert' => ($isTheoreticalTrack && !$hasExpCert) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                // حقول إلزامية + اختيارية
                'file_uni_request' => $reqOrNullable(15, 'uni_request'),
                'file_ma_dates' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            if ($isTheoreticalTrack) {
                $hasExperiences = is_array($request->input('experiences')) && count($request->input('experiences')) > 0;
                $hasLegacyExp = $request->filled('exp_place') && $request->filled('exp_from_year') && $request->filled('exp_to_year');

                if ($hasExperiences) {
                    $rules['experiences'] = 'required|array|min:1';
                    $rules['experiences.*.institution_name'] = 'required|string|max:255';
                    $rules['experiences.*.start_date'] = 'required|date';
                    $rules['experiences.*.end_date'] = 'required|date|after_or_equal:experiences.*.start_date';
                } elseif (!$hasLegacyExp) {
                    $rules['experiences'] = 'required|array|min:1';
                }
            }

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
                'experiences.required' => 'في مسار الماجستير النظري، يجب إدخال فترة خبرة تدريسية واحدة على الأقل (بمجموع لا يقل عن سنتين).',
                'experiences.min' => 'في مسار الماجستير النظري، يجب إدخال فترة خبرة تدريسية واحدة على الأقل (بمجموع لا يقل عن سنتين).',
                'experiences.*.institution_name.required' => 'يرجى إدخال اسم الجامعة أو الجهة لكل فترة خبرة تدريسية.',
                'experiences.*.institution.required' => 'يرجى إدخال اسم الجامعة أو الجهة لكل فترة خبرة تدريسية.',
                'experiences.*.start_date.required' => 'يرجى إدخال تاريخ بداية فترة الخبرة التدريسية.',
                'experiences.*.start_date.date' => 'تاريخ بداية فترة الخبرة التدريسية غير صحيح.',
                'experiences.*.end_date.required' => 'يرجى إدخال تاريخ نهاية فترة الخبرة التدريسية.',
                'experiences.*.end_date.date' => 'تاريخ نهاية فترة الخبرة التدريسية غير صحيح.',
                'experiences.*.end_date.after_or_equal' => 'تاريخ نهاية الخبرة التدريسية يجب أن يكون مساوياً أو لاحقاً لتاريخ البداية.',
                'file_national_id.required' => 'يرجى إرفاق صورة الهوية الشخصية أو جواز السفر كمتطلب إلزامي.',
                'file_hs_cert.required' => 'يرجى رفع ملف نسخة مصدقة عن الشهادة الثانوية (PDF).',
                'file_ba_cert.required' => 'يرجى رفع ملف مصدقة الإجازة الجامعية الأولى (PDF).',
                'file_ma_cert.required' => 'يرجى رفع ملف نسخة مصدقة عن شهادة الماجستير (PDF).',
                'file_ma_defense_decision.required' => 'يرجى رفع صورة عن قرار مجلس الدراسات العليا بالمناقشة بالماجستير (PDF).',
                'file_ma_grant_decision.required' => 'يرجى رفع صورة عن قرار مجلس الجامعة بمنح درجة الماجستير (PDF).',
                'file_ma_reg_decision.required' => 'يرجى رفع صورة عن قرار مجلس الجامعة بالتسجيل بدرجة الماجستير (PDF).',
                'file_thesis_summary.required' => 'يرجى رفع ملف ملخص رسالة الماجستير (PDF).',
                'file_thesis_summary.max' => 'حجم ملف ملخص رسالة الماجستير يجب ألا يتجاوز 5 ميغابايت.',
                'file_cv.required' => 'يرجى رفع ملف السيرة الذاتية للمرشح (PDF).',
                'file_payment.required' => 'يرجى رفع ملف إيصال تسديد رسم تعادل الماجستير (PDF).',
                'file_uni_request.required' => 'يرجى رفع طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة) بصيغة PDF.',
                'file_exp_cert.required' => 'يرجى رفع وثائق وشهادات الخبرة التدريسية (PDF).',
                'file_contracts.required' => 'يرجى رفع صور العقود التدريسية المصدقة لمدة لا تقل عن سنتين (PDF).',
                'file_salary_slips.required' => 'يرجى رفع كشوفات الرواتب أو التحويلات البنكية المصدقة (PDF).',
                'max' => 'حجم الملف المرفق يجب ألا يتجاوز 2 ميغابايت.',
            ];
        }

        $validated = $request->validate($rules, $messages);

        if (!$isDraft && !$isAdminEdit && $isTheoreticalTrack && $calcExpDays < 730) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['experiences' => 'في مسار الماجستير النظري، يجب أن يكون مجموع مدة الخبرة التدريسية التراكمي سنتين على الأقل (730 يوماً فأكثر). المجموع الحالي: ' . $calcExpDays . ' يوماً.']);
        }

        // 4. حفظ وتحديث بيانات المرشح (حل جذري لمنع خطأ تعارض الرقم الوطني UNIQUE constraint failed)
        $fullName = $request->filled('full_name') ? trim($request->full_name) : ($existingApp && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة ماجستير سوري');
        $cleanNatId = $request->filled('national_id') ? trim($request->national_id) : null;

        $matchedProfile = null;
        if ($cleanNatId) {
            $matchedProfile = EquivalenceProfile::where('national_id', $cleanNatId)->first();
        }

        if ($matchedProfile) {
            // أ. الرقم الوطني مسجل مسبقاً لدى مرشح في النظام -> اعتماد البروفايل الأصلي وتحديث بياناته
            $profile = $matchedProfile;
            $profile->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $profile->father_name,
                'mother_name'    => $request->mother_name ?? $profile->mother_name,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($profile->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $profile->dob,
                'job_title'      => $request->job_title ?? ($profile->job_title ?? 'مدرس / باحث'),
                'phone'          => $request->phone ?? $profile->phone,
                'mobile'         => $request->mobile ?? $profile->mobile,
                'email'          => $request->filled('email') ? trim($request->email) : ($profile->email ?? ($isAdminEdit ? '' : (Auth::user()->email ?? ''))),
                'address'        => $request->address ?? $profile->address,
                'gender'         => $request->gender ?: ($profile->gender ?? 'ذكر'),
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : ($profile->is_syrian ?? 1),
            ]);

            // إذا كانت المسودة مرتبطة بمرشح آخر مختلف، نقوم بإعادة ربط المسودة بالمرشح الصحيح وحذف القديم إن كان مؤقتاً
            if ($existingApp && $existingApp->candidate_id && $existingApp->candidate_id != $profile->id) {
                $oldCandidateId = $existingApp->candidate_id;
                $existingApp->update(['candidate_id' => $profile->id]);

                $oldCandidate = EquivalenceProfile::find($oldCandidateId);
                if ($oldCandidate && str_starts_with($oldCandidate->national_id ?? '', 'TMP-')) {
                    $oldCandidate->delete();
                }
            }
        } elseif ($existingApp && $existingApp->candidate) {
            // ب. تحديث البروفايل الحالي للمسودة لعدم وجود تعارض في الرقم الوطني
            $profile = $existingApp->candidate;
            $profile->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $profile->father_name,
                'mother_name'    => $request->mother_name ?? $profile->mother_name,
                'national_id'    => $cleanNatId ?: $profile->national_id,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($profile->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $profile->dob,
                'job_title'      => $request->job_title ?? ($profile->job_title ?? 'مدرس / باحث'),
                'phone'          => $request->phone ?? $profile->phone,
                'mobile'         => $request->mobile ?? $profile->mobile,
                'email'          => $request->filled('email') ? trim($request->email) : ($profile->email ?? ($isAdminEdit ? '' : (Auth::user()->email ?? ''))),
                'address'        => $request->address ?? $profile->address,
                'gender'         => $request->gender ?: ($profile->gender ?? 'ذكر'),
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : ($profile->is_syrian ?? 1),
            ]);
        } else {
            // ج. إنشاء بروفايل جديد تماماً
            $candNationalId = $cleanNatId ?: ('TMP-' . time() . '-' . rand(100, 999));
            $profile = EquivalenceProfile::create([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name,
                'mother_name'    => $request->mother_name,
                'national_id'    => $candNationalId,
                'dob'            => $request->dob,
                'job_title'      => $request->job_title ?? 'مدرس / باحث',
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'phone'          => $request->phone,
                'mobile'         => $request->mobile ?? '',
                'email'          => $request->filled('email') ? trim($request->email) : (Auth::user()->email ?? ''),
                'address'        => $request->address ?? '',
                'gender'         => $request->gender ?: 'ذكر',
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : 1,
            ]);
        }

        // 5. حساب تراكمي لفترات الخبرة التدريسية وتحديد المسار المعتمد (تطبيقي / نظري)
        $totalExpDays = 0;
        $validExperiences = [];

        if ($isTheoreticalTrack) {
            $experiencesInput = $request->input('experiences', []);
            if (is_array($experiencesInput)) {
                foreach ($experiencesInput as $exp) {
                    $instName = !empty($exp['institution_name']) ? $exp['institution_name'] : (!empty($exp['institution']) ? $exp['institution'] : null);
                    if (!empty($instName) && !empty($exp['start_date']) && !empty($exp['end_date'])) {
                        $startDate = \Carbon\Carbon::parse($exp['start_date']);
                        $endDate = \Carbon\Carbon::parse($exp['end_date']);
                        $durationDays = $endDate->gte($startDate) ? (int) $startDate->diffInDays($endDate) : 0;
                        $totalExpDays += $durationDays;
                        $validExperiences[] = [
                            'institution_name' => trim($instName),
                            'role_or_subject'  => !empty($exp['role_or_subject']) ? trim($exp['role_or_subject']) : null,
                            'start_date'       => $exp['start_date'],
                            'end_date'         => $exp['end_date'],
                            'duration_days'    => $durationDays,
                            'notes'            => !empty($exp['notes']) ? trim($exp['notes']) : null,
                        ];
                    }
                }
            }

            // دعم احتياطي للبيانات المدخلة بالحقول القديمة إن لم توجد مصفوفة الخبرات
            if (empty($validExperiences) && $request->filled('exp_from_year') && $request->filled('exp_to_year') && $request->filled('exp_place')) {
                $startDate = \Carbon\Carbon::parse($request->exp_from_year);
                $endDate = \Carbon\Carbon::parse($request->exp_to_year);
                $durationDays = $endDate->gte($startDate) ? (int) $startDate->diffInDays($endDate) : 0;
                $totalExpDays += $durationDays;
                $validExperiences[] = [
                    'institution_name' => trim($request->exp_place),
                    'role_or_subject'  => 'تدريس جامعي',
                    'start_date'       => $request->exp_from_year,
                    'end_date'         => $request->exp_to_year,
                    'duration_days'    => $durationDays,
                    'notes'            => null,
                ];
            }
        }

        $trackName = $isTheoreticalTrack ? 'ماجستير داخلي - نظري' : 'ماجستير داخلي - تطبيقي';
        $appPrefix = $isTheoreticalTrack ? 'MA-SY-' : 'MA-APP-';
        $appNo = ($existingApp && $existingApp->application_no) ? $existingApp->application_no : ($appPrefix . rand(100000, 999999));
        $requestType = $trackName;

        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $appStatus = $isAdminEdit
            ? ($existingApp->getRawOriginal('status') ?: $existingApp->status)
            : ($isDraft ? 'مسودة' : 'تحت التدقيق الأولي');
        $application = $existingApp;
        $isFirstTime = ((int) $request->input('is_first_time', 1) === 1) ? 1 : 0;
        $parentAppId = ($isFirstTime === 0 && $request->filled('parent_application_id')) ? $request->input('parent_application_id') : null;

        if ($application) {
            $application->update([
                'candidate_id' => $profile->id,
                'parent_application_id' => $parentAppId,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'has_previous_degree' => $isTheoreticalTrack,
                'status' => $appStatus,
            ]);
            if ($request->has('courses')) {
                ApplicationCourse::where('application_id', $application->id)->delete();
            }
            Education::where('application_id', $application->id)->delete();
            $appNo = $application->application_no ?? $appNo;
        } else {
            $application = Application::create([
                'candidate_id' => $profile->id,
                'parent_application_id' => $parentAppId,
                'application_no' => $appNo,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->ma_faculty,
                'work_department' => $request->ma_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'study_system' => 'فصلي',
                'has_previous_degree' => $isTheoreticalTrack,
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
            'exact_specialization' => $request->ba_specialization ?: null,
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
            'section_name' => $request->ma_specialization ?: null,
            'general_specialization' => $request->ma_general_specialization ?: $request->ma_faculty,
            'exact_specialization' => $request->ma_specialization ?: null,
            'registration_date' => $request->ma_registration_date,
            'defense_date' => $request->ma_defense_date,
            'grant_date' => $request->ma_grant_date,
            'rank' => $request->ma_rank,
            'supervisor_name' => $request->ma_supervisor,
            'thesis_title' => $request->ma_thesis_title,
            'experience_from_year' => ($isTheoreticalTrack && !empty($validExperiences)) ? collect($validExperiences)->min('start_date') : null,
            'experience_to_year' => ($isTheoreticalTrack && !empty($validExperiences)) ? collect($validExperiences)->max('end_date') : null,
            'notes' => ($isTheoreticalTrack && !empty($validExperiences)) ? ('فترات خبرة تدريسية: ' . collect($validExperiences)->pluck('institution_name')->implode('، ')) : null,
        ]);

        // حفظ فترات الخبرة التدريسية (في المسار النظري فقط، ومسح أي سجلات قديمة في التطبيقي)
        ApplicationExperience::where('application_id', $application->id)->delete();
        if ($isTheoreticalTrack) {
            foreach ($validExperiences as $vExp) {
                ApplicationExperience::create([
                    'application_id'   => $application->id,
                    'education_id'     => $edMA->id,
                    'institution_name' => $vExp['institution_name'],
                    'role_or_subject'  => $vExp['role_or_subject'],
                    'start_date'       => $vExp['start_date'],
                    'end_date'         => $vExp['end_date'],
                    'duration_days'    => $vExp['duration_days'],
                    'notes'            => $vExp['notes'],
                ]);
            }
        }

        // 8. حفظ وربط المرفقات بالأرقام المعيارية (1 - 23) بدون تعارض للأنواع المشتركة
        $folder = 'attachments/' . $application->id;

        $uploadAndAttach = function($fileKey, $educationId, $typeId, $notes, $existingKey = null) use ($request, $folder, $existingAttachments, $sharedTypeIds) {
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
                $oldPath = ($existingAttachments[$notes] ?? null)
                    ?? ($existingKey && isset($existingAttachments[$existingKey]) ? $existingAttachments[$existingKey] : null)
                    ?? (!in_array((int)$typeId, $sharedTypeIds, true) ? ($existingAttachments[$typeId] ?? null) : null);

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

        // 1. الهوية الشخصية / جواز السفر (13)
        if ($request->hasFile('file_national_id') || isset($existingAttachments[13]) || isset($existingAttachments['national_id'])) {
            $uploadAndAttach('file_national_id', $edMA->id, 13, 'صورة عن الهوية الشخصية / جواز السفر', 'national_id');
        }

        // 2. الشهادة الثانوية (1) وقرار معادلتها إن كانت غير سورية (2)
        $uploadAndAttach('file_hs_cert', $edHS->id, 1, 'نسخة مصدقة عن الشهادة الثانوية', 'hs_cert');
        $hsDecisionInput = $request->hasFile('file_hs_decision') ? 'file_hs_decision' : 'hs_decision_file';
        if ($request->hasFile($hsDecisionInput) || $hasHsDecision) {
            $uploadAndAttach($hsDecisionInput, $edHS->id, 2, 'قرار معادلة الشهادة الثانوية غير السورية', 'hs_decision');
        }

        // 3. مصدقة الإجازة الجامعية الأولى (3) وقرار معادلتها إن كانت غير سورية (4)
        $uploadAndAttach('file_ba_cert', $edBA->id, 3, 'مصدقة الإجازة الجامعية الأولى', 'ba_cert');
        $baDecisionInput = $request->hasFile('file_ba_decision') ? 'file_ba_decision' : 'ba_decision_file';
        if ($request->hasFile($baDecisionInput) || $hasBaDecision) {
            $uploadAndAttach($baDecisionInput, $edBA->id, 4, 'قرار معادلة الإجازة الجامعية الأولى غير السورية', 'ba_decision');
        }

        // 4. شهادة الماجستير (6) وقرارات المجالس الثلاثة (7)
        $uploadAndAttach('file_ma_cert', $edMA->id, 6, 'نسخة مصدقة عن شهادة الماجستير', 'ma_cert');
        $uploadAndAttach('file_ma_defense_decision', $edMA->id, 7, 'صورة عن قرار مجلس الدراسات العليا بالمناقشة بالماجستير', 'ma_defense_decision');
        $uploadAndAttach('file_ma_grant_decision', $edMA->id, 7, 'صورة عن قرار مجلس الجامعة بمنح درجة الماجستير', 'ma_grant_decision');
        $uploadAndAttach('file_ma_reg_decision', $edMA->id, 7, 'صورة عن قرار مجلس الجامعة بالتسجيل بدرجة الماجستير', 'ma_reg_decision');
        if ($request->hasFile('file_ma_dates') || isset($existingAttachments['ma_dates'])) {
            $uploadAndAttach('file_ma_dates', $edMA->id, 7, 'وثيقة تواريخ وقرارات المجلس للماجستير', 'ma_dates');
        }

        // 8-10. إيصال الرسم (16) + السيرة الذاتية (14) + ملخص الرسالة (8)
        $uploadAndAttach('file_payment', $edMA->id, 16, 'إيصال تسديد رسم تعادل الماجستير (100,000 ل.س)', 'payment');
        $uploadAndAttach('file_cv', $edMA->id, 14, 'السيرة الذاتية للمرشح', 'cv');
        $uploadAndAttach('file_thesis_summary', $edMA->id, 8, 'ملخص رسالة الماجستير باللغة العربية', 'thesis_summary');

        // 11-13. الوثائق الاختيارية المشتركة (المكتبة الوطنية 22 / اللغة 18 / الحاسوب 19)
        if ($request->hasFile('file_nat_library_receipt') || isset($existingAttachments[22]) || isset($existingAttachments['nat_library_receipt'])) {
            $uploadAndAttach('file_nat_library_receipt', $edMA->id, 22, 'إيصال المكتبة الوطنية لاستلام الرسالة / الأطروحة', 'nat_library_receipt');
        }
        if ($request->hasFile('file_lang_cert') || isset($existingAttachments[18]) || isset($existingAttachments['lang_cert'])) {
            $uploadAndAttach('file_lang_cert', $edMA->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'lang_cert');
        }
        if ($request->hasFile('file_icdl_cert') || isset($existingAttachments[19]) || isset($existingAttachments['icdl_cert'])) {
            $uploadAndAttach('file_icdl_cert', $edMA->id, 19, 'شهادة مهارات الحاسوب (ICDL)', 'icdl_cert');
        }

        // 14-16. مرفقات الخبرة التدريسية (في المسار النظري فقط)
        if ($isTheoreticalTrack) {
            if ($request->hasFile('file_contracts') || $hasContracts) {
                $uploadAndAttach('file_contracts', $edMA->id, 21, 'صور العقود التدريسية المصدقة لمدة لا تقل عن سنتين', 'contracts');
            }
            if ($request->hasFile('file_salary_slips') || $hasSalarySlips) {
                $uploadAndAttach('file_salary_slips', $edMA->id, 21, 'كشوفات الرواتب أو التحويلات البنكية المصدقة', 'salary_slips');
            }
            if ($request->hasFile('file_exp_cert') || $hasExpCert) {
                $uploadAndAttach('file_exp_cert', $edMA->id, 20, 'وثائق وشهادات الخبرة التدريسية', 'exp_cert');
            }
        }

        // طلب الجامعة للتعادل (15) ومرفقات أخرى (23)
        $uploadAndAttach('file_uni_request', $edMA->id, 15, 'طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة)', 'uni_request');
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments[23]) || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttach('file_other_attachments', $edMA->id, 23, 'مرفقات ووثائق أخرى', 'other_attachments');
        }

        // 9. التوجيه والرسائل
        if ($isAdminEdit) {
            return $this->redirectAfterAdminWizardUpdate($application);
        }

        if ($isDraft) {
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
    /**
     * تصنيف المؤسسات الجامعية السورية إلى ثلاث مجموعات رسمية (حكومية 9، خاصة 36، معاهد عليا 3).
     */
    private function getSyrianDoctorateUniversityGroups(?int $syriaId = null): array
    {
        $syriaId = $syriaId ?: (LookupCountry::where('name', 'سوريا')->value('id') ?? 1);

        $resolveNames = function (array $names) use ($syriaId) {
            return collect($names)->map(fn (string $name) => LookupUniversity::firstOrCreate(
                ['name' => $name],
                ['country_id' => $syriaId]
            ));
        };

        $publicNames = [
            'جامعة دمشق',
            'جامعة حلب',
            'جامعة اللاذقية',
            'جامعة حمص',
            'الجامعة الافتراضية السورية',
            'جامعة الفرات',
            'جامعة حماة',
            'جامعة طرطوس',
            'جامعة إدلب',
        ];

        $privateNames = [
            'الأكاديمية العربية للعلوم والتكنولوجيا والنقل البحري',
            'جامعة القلمون الخاصة',
            'الجامعة العربية الدولية',
            'الجامعة السورية الخاصة',
            'الجامعة الدولية الخاصة للعلوم والتكنولوجيا',
            'جامعة الوادي الدولية',
            'جامعة الرشيد الدولية',
            'جامعة اليرموك الخاصة',
            'الجامعة الوطنية الخاصة',
            'جامعة قرطبة الخاصة',
            'جامعة الاتحاد الخاصة',
            'جامعة الشهباء الخاصة',
            'جامعة الجزيرة الخاصة',
            'الجامعة العربية الخاصة للعلوم والتكنولوجيا',
            'جامعة الأندلس للعلوم الطبية',
            'جامعة الحواش الخاصة',
            'جامعة إيبلا الخاصة',
            'جامعة الشام الخاصة',
            'جامعة بلاد الشام للعلوم الشرعية',
            'جامعة قاسيون الخاصة',
            'جامعة الشمال الخاصة',
            'جامعة ماري الخاصة',
            'جامعة الشام',
            'جامعة المنارة',
            'جامعة أنطاكية السورية الخاصة',
            'الجامعة الدولية للعلوم والنهضة',
            'جامعة آرام للعلوم',
            'السورية للعلوم والتكنولوجيا',
            'جامعة باشاك شهير',
            'جامعة الزهراء',
            'جامعة المعالي',
            'جامعة الزيتونة',
            'جامعة الرواد للعلوم والثقافة',
            'جامعة الأمانوس',
            'جامعة المعارف الخاصة',
            'الجامعة الإسلامية',
        ];

        $instituteNames = [
            'المعهد العالي لإدارة الأعمال',
            'المعهد الوطني للإدارة العامة',
            'المعهد العالي للدراسات والبحوث السكانية',
        ];

        return [
            'public'     => $resolveNames($publicNames),
            'private'    => $resolveNames($privateNames),
            'institutes' => $resolveNames($instituteNames),
        ];
    }

    /**
     * قائمة الجامعات الحكومية السورية التسع المخولة بمنح درجة الدكتوراه حصراً.
     */
    private function getSyrianDoctoratePhdUniversities(?int $syriaId = null)
    {
        return $this->getSyrianDoctorateUniversityGroups($syriaId)['public'];
    }

    /**
     * أرقام أنواع المرفقات الثلاثة لقرارات جامعة الدكتوراه السورية.
     */
    private function getSyrianDoctorateDecisionAttachmentTypeIds(): array
    {
        $ensureType = function (int $preferredId, string $name): int {
            $existing = LookupAttachmentType::where('name', $name)->value('id');
            if ($existing) {
                return (int) $existing;
            }
            if (!LookupAttachmentType::where('id', $preferredId)->exists()) {
                \Illuminate\Support\Facades\DB::table('lookup_attachment_types')->insert([
                    'id'         => $preferredId,
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return $preferredId;
            }
            return (int) LookupAttachmentType::create(['name' => $name])->id;
        };

        return [
            'registration' => $ensureType(26, 'قرار الجامعة بالتسجيل بدرجة الدكتوراه'),
            'defense'      => $ensureType(27, 'قرار الجامعة بالمناقشة للدكتوراه'),
            'grant'        => $ensureType(28, 'قرار الجامعة بالحصول على درجة الدكتوراه'),
        ];
    }

    private function prepareSyrianDoctorateWizardData(Request $request, ?Application $adminDraft = null): array
    {
        $isAdminEdit = ($adminDraft !== null);
        $draft = $adminDraft;
        if (!$isAdminEdit && $request->filled('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.level', 'educations.country', 'educations.university', 'educations.attachments.attachmentType', 'courses'])
                ->first();
        }

        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        $syrianUniGroups = $this->getSyrianDoctorateUniversityGroups($syriaId);
        $syrianPublicUnis = $syrianUniGroups['public'];
        $syrianPrivateUnis = $syrianUniGroups['private'];
        $syrianInstitutes = $syrianUniGroups['institutes'];
        $phdUniversities = $syrianPublicUnis;
        $phdDecisionTypeIds = $this->getSyrianDoctorateDecisionAttachmentTypeIds();
        $universities = LookupUniversity::with('country')->orderBy('name', 'asc')->get();
        $educationLevels = LookupEducationLevel::all();
        $notifications = $isAdminEdit ? collect() : $this->getUnreadNotifications();

        $prevUniId = $isAdminEdit ? $draft->work_university_id : Auth::user()->university_id;
        $previousApplications = Application::where('work_university_id', $prevUniId)
            ->where('status', '!=', 'مسودة')
            ->with('candidate')
            ->orderBy('id', 'desc')
            ->get();

        $adminSubmitRoute = $isAdminEdit ? route('admin.applications.wizard_update', $draft->id) : null;
        $adminWizardTrack = $isAdminEdit ? 'syrian_doctorate' : null;

        return compact(
            'countries',
            'universities',
            'syrianPublicUnis',
            'syrianPrivateUnis',
            'syrianInstitutes',
            'phdUniversities',
            'phdDecisionTypeIds',
            'educationLevels',
            'syriaId',
            'notifications',
            'draft',
            'previousApplications',
            'isAdminEdit',
            'adminSubmitRoute',
            'adminWizardTrack'
        );
    }

    public function showSyrianDoctorateWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.syrian_doctorate', $this->prepareSyrianDoctorateWizardData($request));
    }

    public function submitSyrianDoctorateWizard(Request $request)
    {
        $isAdminEdit = (bool) $request->attributes->get('admin_wizard_edit', false);
        if (!$isAdminEdit && \App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $existingApp = null;
        if ($isAdminEdit) {
            $adminAppId = (int) $request->attributes->get('admin_target_app_id');
            $existingApp = Application::with(['candidate', 'educations.attachments', 'courses'])
                ->findOrFail($adminAppId);
            $uniId = $request->filled('work_university_id') ? (int) $request->input('work_university_id') : $existingApp->work_university_id;
        } else {
            $uniId = Auth::user()->university_id;
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
        }

        $isExisting = ($existingApp !== null);
        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && (
            $existingApp->status_id == 3 || 
            in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة', 3])
        ));

        if (!$isAdminEdit && $wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ هذا الطلب كمسودة لأن المعاملة قيد المعالجة (بانتظار استكمال الوثائق). يرجى استكمال البيانات والمرفقات ثم النقر على زر الإرسال.');
        }

        $isDraft = !$isAdminEdit && !$wasAwaitingDocs && ($request->input('action') === 'save_draft');
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;
        $phdUniversities = $this->getSyrianDoctoratePhdUniversities($syriaId);
        $allowedPhdUniIds = $phdUniversities->pluck('id')->all();
        $phdDecTypeIds = $this->getSyrianDoctorateDecisionAttachmentTypeIds();
        $regDecTypeId = $phdDecTypeIds['registration'];
        $defDecTypeId = $phdDecTypeIds['defense'];
        $grantDecTypeId = $phdDecTypeIds['grant'];

        // تجميع المرفقات السابقة المحفوظة بالأرقام الجديدة وبالنصوص
        $existingAttachments = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $tId = (int) ($att->attachment_type_id ?? 0);
                    if ($tId) {
                        $existingAttachments[$tId] = $att->file_path;
                    }
                    if ($tId === $regDecTypeId) {
                        $existingAttachments['phd_registration_decision'] = $att->file_path;
                    } elseif ($tId === $defDecTypeId) {
                        $existingAttachments['phd_defense_decision'] = $att->file_path;
                    } elseif ($tId === $grantDecTypeId) {
                        $existingAttachments['phd_grant_decision'] = $att->file_path;
                    }
                    if ($att->notes) {
                        $existingAttachments[$att->notes] = $att->file_path;
                        if (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'شخصية') || str_contains($att->notes, 'جواز')) {
                            $existingAttachments[13] = $att->file_path;
                            $existingAttachments['national_id'] = $att->file_path;
                        } elseif ((str_contains($att->notes, 'ثانوية') || str_contains($att->notes, 'بكالوريا')) && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments[1] = $att->file_path;
                            $existingAttachments['hs_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'الإجازة') && !str_contains($att->notes, 'قرار')) {
                            $existingAttachments[3] = $att->file_path;
                            $existingAttachments['ba_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'شهادة ماجستير')) {
                            $existingAttachments[6] = $att->file_path;
                            $existingAttachments['ma_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'قرار') && str_contains($att->notes, 'ماجستير')) {
                            $existingAttachments[9] = $att->file_path;
                            $existingAttachments['ma_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'شهادة الدكتوراه') || (str_contains($att->notes, 'دكتوراه') && str_contains($att->notes, 'شهادة'))) {
                            $existingAttachments[10] = $att->file_path;
                            $existingAttachments['phd_cert'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'بالتسجيل')) {
                            $existingAttachments[$regDecTypeId] = $att->file_path;
                            $existingAttachments['phd_registration_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'بالمناقشة')) {
                            $existingAttachments[$defDecTypeId] = $att->file_path;
                            $existingAttachments['phd_defense_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'بالحصول على الدرجة')) {
                            $existingAttachments[$grantDecTypeId] = $att->file_path;
                            $existingAttachments['phd_grant_decision'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ملخص') && (str_contains($att->notes, 'دكتوراه') || str_contains($att->notes, 'الأطروحة'))) {
                            $existingAttachments[12] = $att->file_path;
                            $existingAttachments['thesis_summary'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'السيرة') || str_contains($att->notes, 'سيرة')) {
                            $existingAttachments[14] = $att->file_path;
                            $existingAttachments['cv'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'طلب') || str_contains($att->notes, 'كتاب الجامعة')) {
                            $existingAttachments[15] = $att->file_path;
                            $existingAttachments['uni_request'] = $att->file_path;
                        } elseif (str_contains($att->notes, '125,000') || str_contains($att->notes, '125000') || str_contains($att->notes, 'رسم تعادل') || (str_contains($att->notes, 'إيصال') && str_contains($att->notes, 'دفع'))) {
                            $existingAttachments[17] = $att->file_path;
                            $existingAttachments['fees_receipt'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'اللغة') || str_contains($att->notes, 'إنكليزية')) {
                            $existingAttachments[18] = $att->file_path;
                            $existingAttachments['english_test'] = $att->file_path;
                        } elseif (str_contains($att->notes, 'ICDL') || str_contains($att->notes, 'حاسوب')) {
                            $existingAttachments[19] = $att->file_path;
                            $existingAttachments['icdl'] = $att->file_path;
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
        $fileRuleFor = function($typeId, $alias = null, $max = 2048) use ($isUploaded) {
            if ($isUploaded($typeId, $alias)) {
                return "nullable|file|mimes:pdf|max:{$max}";
            }
            return "required|file|mimes:pdf|max:{$max}";
        };

        $hasMaEquivalence = $request->boolean('has_ma_equivalence');

        // 1. Validation of all sections
        if ($isDraft || $isAdminEdit) {
            $rules = [
                'full_name' => 'required|string|max:255',
                'national_id' => $isAdminEdit ? 'nullable|string|max:50' : 'required|string|max:50',
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
                'hs_type' => 'nullable|string|max:255',
                'hs_type_other' => 'nullable|string|max:255',
                'hs_grant_date' => 'nullable|numeric|digits:4',

                'ba_country_id' => 'nullable|exists:lookup_countries,id',
                'ba_university_id' => 'nullable|string|max:50',
                'ba_university_other' => 'nullable|string|max:255',
                'ba_university_text' => 'nullable|string|max:255',
                'ba_faculty' => 'nullable|string',
                'ba_department' => 'nullable|string',
                'ba_general_specialization' => 'nullable|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_grant_date' => 'nullable|date',
                'ba_rank' => 'nullable|string',

                'ma_country_id' => 'nullable|exists:lookup_countries,id',
                'ma_university_id' => 'nullable|string|max:50',
                'ma_university_other' => 'nullable|string|max:255',
                'ma_faculty' => 'nullable|string',
                'ma_department' => 'nullable|string',
                'ma_general_specialization' => 'nullable|string|max:255',
                'ma_specialization' => 'nullable|string|max:255',
                'ma_grant_date' => 'nullable|date',
                'ma_rank' => 'nullable|string',
                'has_ma_equivalence' => 'nullable|boolean',
                'ma_decision_no' => 'nullable|string|max:100',
                'ma_decision_date' => 'nullable|date',

                'phd_university_id' => ['nullable', Rule::in($allowedPhdUniIds)],
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
                'file_ba_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ma_cert' => 'nullable|file|mimes:pdf|max:2048',
                'ma_decision_file' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_registration_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_defense_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_grant_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => 'nullable|file|mimes:pdf|max:5120',
                'file_fees_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_test' => 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $baIsSyria = ($request->ba_country_id == $syriaId);
            $baIsOther = !$baIsSyria || ($request->input('ba_university_id') === 'other');
            $maIsSyria = ($request->ma_country_id == $syriaId);
            $maIsOther = !$maIsSyria || ($request->input('ma_university_id') === 'other');

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

                // Step 2: High School Info (الدولة، نوع الشهادة + أخرى، عام البكالوريا فقط)
                'hs_country_id' => 'required|exists:lookup_countries,id',
                'hs_type' => 'required|string|in:علمي,أدبي,شرعي,صناعي,تجاري,other',
                'hs_type_other' => ($request->input('hs_type') === 'other') ? 'required|string|max:255' : 'nullable|string|max:255',
                'hs_grant_date' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),

                // Step 3: Bachelor's Degree Info (الدولة، الجامعة مقسّمة داخل سوريا / نصي خارجها، الكلية، القسم، الاختصاص العام)
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_university_id' => $baIsOther ? 'nullable' : 'required|exists:lookup_universities,id',
                'ba_university_text' => $baIsOther ? 'required|string|max:255' : 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_general_specialization' => 'required|string|max:255',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'nullable|string|max:100',

                // Step 4: Master's Degree Info (الدولة، الجامعة، القسم، الاختصاص العام، الاختصاص الدقيق، تاريخ المنح + checkbox معادلة)
                'ma_country_id' => 'required|exists:lookup_countries,id',
                'ma_university_id' => $maIsOther ? 'nullable' : 'required|exists:lookup_universities,id',
                'ma_university_other' => $maIsOther ? 'required|string|max:255' : 'nullable|string|max:255',
                'ma_department' => 'required|string|max:255',
                'ma_general_specialization' => 'required|string|max:255',
                'ma_specialization' => 'required|string|max:255',
                'ma_grant_date' => 'required|date|before_or_equal:today',
                'ma_faculty' => 'nullable|string|max:255',
                'ma_rank' => 'nullable|string|max:100',
                'has_ma_equivalence' => 'nullable|boolean',
                'ma_decision_no' => $hasMaEquivalence ? 'required|string|max:100' : 'nullable|string|max:100',
                'ma_decision_date' => $hasMaEquivalence ? 'required|date|before_or_equal:today' : 'nullable|date',
                'ma_decision_file' => ($hasMaEquivalence && !$isUploaded(9, 'ma_decision')) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',

                // Step 5: Syrian Doctorate Degree Info (الجامعات الـ 9 الحكومية حصراً + التواريخ الـ 3)
                'phd_university_id' => ['required', Rule::in($allowedPhdUniIds)],
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

                // Step 6: 16 Attachments (معفاة تلقائياً إذا كانت مرفوعة مسبقاً)
                'file_national_id' => $fileRuleFor(13, 'national_id'),
                'file_hs_cert' => $fileRuleFor(1, 'hs_cert'),
                'file_ba_cert' => $fileRuleFor(3, 'ba_cert'),
                'file_ma_cert' => $fileRuleFor(6, 'ma_cert'),
                'file_phd_cert' => $fileRuleFor(10, 'phd_cert'),
                'file_phd_registration_decision' => $fileRuleFor($regDecTypeId, 'phd_registration_decision'),
                'file_phd_defense_decision' => $fileRuleFor($defDecTypeId, 'phd_defense_decision'),
                'file_phd_grant_decision' => $fileRuleFor($grantDecTypeId, 'phd_grant_decision'),
                'file_uni_request' => $fileRuleFor(15, 'uni_request'),
                'file_cv' => $fileRuleFor(14, 'cv'),
                'file_icdl' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_test' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_summary' => $fileRuleFor(12, 'thesis_summary', 5120),
                'file_fees_receipt' => $fileRuleFor(17, 'fees_receipt'),
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'mobile.regex' => 'رقم الهاتف المحمول يجب أن يتكون من 10 أرقام.',
                'phone.regex' => 'رقم الهاتف الأرضي يجب أن يتكون من 10 أرقام.',
                'email.email' => 'البريد الإلكتروني المدخل غير صحيح.',
                'parent_application_id.required_if' => 'يرجى اختيار المعاملة السابقة للمرشح عند اختيار (سبق التقدم بتعادل لشهادة أخرى).',
                'hs_type_other.required' => 'يرجى تحديد نوع شهادة البكالوريا عند اختيار (أخرى).',
                'ba_university_id.required' => 'يرجى اختيار الجامعة المانحة للإجازة الجامعية.',
                'ba_university_text.required' => 'يرجى إدخال اسم الجامعة المانحة للإجازة الجامعية.',
                'ba_general_specialization.required' => 'يرجى إدخال الاختصاص العام للإجازة الجامعية.',
                'ba_grant_date.before_or_equal' => 'تاريخ الحصول على الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_university_other.required' => 'يرجى إدخال اسم الجامعة المانحة للماجستير.',
                'ma_specialization.required' => 'يرجى إدخال الاختصاص الدقيق لشهادة الماجستير.',
                'ma_grant_date.before_or_equal' => 'تاريخ الحصول على الماجستير يجب أن يكون قبل أو يساوي اليوم الحالي.',
                'ma_decision_no.required' => 'يرجى إدخال رقم قرار معادلة الماجستير.',
                'ma_decision_date.required' => 'يرجى إدخال تاريخ قرار معادلة الماجستير.',
                'ma_decision_file.required' => 'يرجى إرفاق صورة عن قرار معادلة الماجستير (PDF).',
                'phd_university_id.in' => 'جامعة الدكتوراه السورية يجب أن تكون إحدى الجامعات الحكومية السورية التسع المعتمدة فقط.',
                'phd_defense_date.after' => 'تاريخ المناقشة للدكتوراه يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'phd_grant_date.after' => 'تاريخ منح الدكتوراه يجب أن يكون بعد تاريخ المناقشة.',
                'phd_grant_date.before_or_equal' => 'تاريخ منح الدكتوراه يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.',
                'file_national_id.required' => 'يرجى إرفاق صورة عن الهوية الشخصية (PDF).',
                'file_hs_cert.required' => 'يرجى إرفاق صورة عن شهادة البكالوريا (PDF).',
                'file_ba_cert.required' => 'يرجى إرفاق صورة عن الإجازة الجامعية (PDF).',
                'file_ma_cert.required' => 'يرجى إرفاق صورة عن شهادة الماجستير (PDF).',
                'file_phd_cert.required' => 'يرجى إرفاق صورة عن شهادة الدكتوراه (PDF).',
                'file_phd_registration_decision.required' => 'يرجى إرفاق قرار الجامعة بالتسجيل بدرجة الدكتوراه (PDF).',
                'file_phd_defense_decision.required' => 'يرجى إرفاق قرار الجامعة بالمناقشة (PDF).',
                'file_phd_grant_decision.required' => 'يرجى إرفاق قرار الجامعة بالحصول على الدرجة (PDF).',
                'file_uni_request.required' => 'يرجى إرفاق طلب الجامعة للتعادل (PDF).',
                'file_cv.required' => 'يرجى إرفاق السيرة الذاتية (PDF).',
                'file_thesis_summary.required' => 'يرجى إرفاق ملخص عن أطروحة الدكتوراه (PDF).',
                'file_fees_receipt.required' => 'يرجى إرفاق إيصال تسديد رسم تعادل الدكتوراه 125,000 ل.س (PDF).',
                'file_thesis_summary.max' => 'حجم ملف ملخص أطروحة الدكتوراه يجب ألا يتجاوز 5 ميغابايت.',
                'max' => 'حجم الملف المرفق يجب ألا يتجاوز 2 ميغابايت.',
            ];
        }

        $validated = $request->validate($rules, $messages);

        // 2. Save Equivalence Profile (Candidate) (حل جذري لمنع خطأ تعارض الرقم الوطني UNIQUE constraint failed)
        $fullName = $request->filled('full_name') ? trim($request->full_name) : ($existingApp && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة دكتوراه سورية');
        $cleanNatId = $request->filled('national_id') ? trim($request->national_id) : null;

        $matchedProfile = null;
        if ($cleanNatId) {
            $matchedProfile = EquivalenceProfile::where('national_id', $cleanNatId)->first();
        }

        if ($matchedProfile) {
            // أ. الرقم الوطني مسجل مسبقاً لدى مرشح في النظام -> اعتماد البروفايل الأصلي وتحديث بياناته
            $profile = $matchedProfile;
            $profile->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $profile->father_name,
                'mother_name'    => $request->mother_name ?? $profile->mother_name,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($profile->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $profile->dob,
                'job_title'      => $request->job_title ?? ($profile->job_title ?? 'مدرس / باحث'),
                'phone'          => $request->phone ?? $profile->phone,
                'mobile'         => $request->mobile ?? $profile->mobile,
                'email'          => $request->filled('email') ? trim($request->email) : ($profile->email ?? ($isAdminEdit ? '' : (Auth::user()->email ?? ''))),
                'address'        => $request->address ?? $profile->address,
                'gender'         => $request->gender ?: ($profile->gender ?? 'ذكر'),
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : ($profile->is_syrian ?? 1),
            ]);

            // إذا كانت المسودة مرتبطة بمرشح آخر مختلف، نقوم بإعادة ربط المسودة بالمرشح الصحيح وحذف القديم إن كان مؤقتاً
            if ($existingApp && $existingApp->candidate_id && $existingApp->candidate_id != $profile->id) {
                $oldCandidateId = $existingApp->candidate_id;
                $existingApp->update(['candidate_id' => $profile->id]);

                $oldCandidate = EquivalenceProfile::find($oldCandidateId);
                if ($oldCandidate && str_starts_with($oldCandidate->national_id ?? '', 'TMP-')) {
                    $oldCandidate->delete();
                }
            }
        } elseif ($existingApp && $existingApp->candidate) {
            // ب. تحديث البروفايل الحالي للمسودة لعدم وجود تعارض في الرقم الوطني
            $profile = $existingApp->candidate;
            $profile->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $profile->father_name,
                'mother_name'    => $request->mother_name ?? $profile->mother_name,
                'national_id'    => $cleanNatId ?: $profile->national_id,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($profile->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $profile->dob,
                'job_title'      => $request->job_title ?? ($profile->job_title ?? 'مدرس / باحث'),
                'phone'          => $request->phone ?? $profile->phone,
                'mobile'         => $request->mobile ?? $profile->mobile,
                'email'          => $request->filled('email') ? trim($request->email) : ($profile->email ?? ($isAdminEdit ? '' : (Auth::user()->email ?? ''))),
                'address'        => $request->address ?? $profile->address,
                'gender'         => $request->gender ?: ($profile->gender ?? 'ذكر'),
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : ($profile->is_syrian ?? 1),
            ]);
        } else {
            // ج. إنشاء بروفايل جديد تماماً
            $candNationalId = $cleanNatId ?: ('TMP-' . time() . '-' . rand(100, 999));
            $profile = EquivalenceProfile::create([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name,
                'mother_name'    => $request->mother_name,
                'national_id'    => $candNationalId,
                'dob'            => $request->dob,
                'job_title'      => $request->job_title ?? 'مدرس / باحث',
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'phone'          => $request->phone,
                'mobile'         => $request->mobile ?? '',
                'email'          => $request->filled('email') ? trim($request->email) : (Auth::user()->email ?? ''),
                'address'        => $request->address ?? '',
                'gender'         => $request->gender ?: 'ذكر',
                'is_syrian'      => $request->has('is_syrian') ? (int)$request->is_syrian : 1,
            ]);
        }

        // 3. Save Application
        $appNo = ($existingApp && $existingApp->application_no) ? $existingApp->application_no : ('PHD-SY-' . rand(100000, 999999));
        $requestType = 'دكتورة داخلي';
        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $appStatus = $isAdminEdit
            ? ($existingApp->getRawOriginal('status') ?: $existingApp->status)
            : ($isDraft ? 'مسودة' : 'تحت التدقيق الأولي');
        $isFirstTime = $request->input('is_first_time', 1);

        $application = $existingApp;

        if ($application) {
            $application->update([
                'candidate_id' => $profile->id,
                'parent_application_id' => ($isFirstTime == 0) ? $request->parent_application_id : null,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $request->phd_faculty,
                'work_department' => $request->phd_department,
                'new_uni_request_no' => $request->req_no,
                'new_uni_request_date' => $request->req_date,
                'is_first_time' => $isFirstTime,
                'has_previous_degree' => ($isFirstTime == 0),
                'status' => $appStatus,
            ]);
            if ($request->has('courses')) {
                ApplicationCourse::where('application_id', $application->id)->delete();
            }
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
        // A. High School (الدولة، نوع الشهادة أو أخرى، عام البكالوريا فقط)
        $hsTypeToSave = ($request->input('hs_type') === 'other')
            ? trim((string) $request->input('hs_type_other', ''))
            : $request->input('hs_type');

        $edHS = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $hsLevelId,
            'country_id' => $request->hs_country_id,
            'section_name' => $hsTypeToSave,
            'general_specialization' => $hsTypeToSave,
            'grant_date' => $request->hs_grant_date,
            'notes' => null,
        ]);

        // B. Bachelor's (الدولة، الجامعة مقسّمة داخل سوريا / نصي خارجها، الكلية، القسم، الاختصاص العام)
        $baUniId = null;
        $baUniOther = null;
        if ($request->ba_country_id == $syriaId && $request->input('ba_university_id') !== 'other' && is_numeric($request->ba_university_id)) {
            $baUniId = (int) $request->ba_university_id;
        } else {
            $baUniName = trim($request->input('ba_university_text', '')) ?: trim($request->input('ba_university_other', ''));
            if ($baUniName !== '') {
                $baUniId = LookupUniversity::where('name', $baUniName)->value('id');
                $baUniOther = $baUniName;
            }
        }

        $edBA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $baLevelId,
            'country_id' => $request->ba_country_id,
            'university_id' => $baUniId,
            'university_other' => $baUniOther,
            'faculty' => $request->ba_faculty,
            'department' => $request->ba_department,
            'section_name' => $request->ba_specialization ?: null,
            'general_specialization' => $request->ba_general_specialization ?: $request->ba_faculty,
            'exact_specialization' => $request->ba_department,
            'registration_date' => $request->ba_registration_date,
            'grant_date' => $request->ba_grant_date,
            'rank' => $request->ba_rank,
            'notes' => null,
        ]);

        // C. Master's (الدولة، الجامعة، القسم، الاختصاص العام، الاختصاص الدقيق، تاريخ الحصول على الدرجة + قرار المعادلة إن وجد)
        $maNotes = null;
        if ($hasMaEquivalence || (!$request->has('has_ma_equivalence') && $request->filled('ma_decision_no'))) {
            if ($request->filled('ma_decision_no')) {
                $maNotes = 'رقم قرار معادلة شهادة الماجستير: ' . trim($request->ma_decision_no);
                if ($request->filled('ma_decision_date')) {
                    $maNotes .= ' | تاريخ القرار: ' . $request->ma_decision_date;
                }
            }
        }

        $maUniId = null;
        $maUniOther = null;
        if ($request->ma_country_id == $syriaId && $request->input('ma_university_id') !== 'other' && is_numeric($request->ma_university_id)) {
            $maUniId = (int) $request->ma_university_id;
        } else {
            $maUniName = trim($request->input('ma_university_other', ''));
            if ($maUniName !== '') {
                $maUniId = LookupUniversity::where('name', $maUniName)->value('id');
                $maUniOther = $maUniName;
            }
        }

        $edMA = Education::create([
            'application_id' => $application->id,
            'education_level_id' => $maLevelId,
            'country_id' => $request->ma_country_id ?? $syriaId,
            'university_id' => $maUniId,
            'university_other' => $maUniOther,
            'faculty' => $request->ma_faculty ?: null,
            'department' => $request->ma_department,
            'section_name' => $request->ma_specialization ?: null,
            'general_specialization' => $request->ma_general_specialization ?: ($request->ma_faculty ?: ''),
            'exact_specialization' => $request->ma_specialization ?: $request->ma_department,
            'registration_date' => $request->ma_registration_date,
            'defense_date' => $request->ma_defense_date,
            'grant_date' => $request->ma_grant_date,
            'rank' => $request->ma_rank,
            'supervisor_name' => $request->ma_supervisor,
            'thesis_title' => $request->ma_thesis_title,
            'notes' => $maNotes,
        ]);

        // D. Syrian Doctorate (إحدى الجامعات الحكومية السورية التسع حصراً)
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

        // 5. Handle File Uploads & Attachments (المرفقات الـ 16 المحددة للدكتوراه السورية)
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

        // 1. صورة عن الهوية الشخصية (إجباري)
        $uploadAndAttachPhd('file_national_id', $edPhD->id, 13, 'صورة عن الهوية الشخصية / جواز السفر', 'national_id');
        // 2. صورة عن شهادة البكالوريا (إجباري)
        $uploadAndAttachPhd('file_hs_cert', $edHS->id, 1, 'صورة عن شهادة البكالوريا', 'hs_cert');
        // 3. صورة عن الإجازة الجامعية (إجباري)
        $uploadAndAttachPhd('file_ba_cert', $edBA->id, 3, 'صورة عن الإجازة الجامعية الأولى', 'ba_cert');
        // 4. صورة عن شهادة الماجستير (إجباري)
        $uploadAndAttachPhd('file_ma_cert', $edMA->id, 6, 'صورة عن شهادة الماجستير', 'ma_cert');
        // 5. صورة عن قرار معادلة الماجستير (إجباري إذا تم اختيار المعادلة المسبقة)
        if ($hasMaEquivalence || $request->hasFile('ma_decision_file')) {
            $uploadAndAttachPhd('ma_decision_file', $edMA->id, 9, 'صورة عن قرار معادلة الماجستير', 'ma_decision');
        }
        // 6. صورة عن شهادة الدكتوراه (إجباري)
        $uploadAndAttachPhd('file_phd_cert', $edPhD->id, 10, 'صورة عن شهادة الدكتوراه السورية المصدقة أصولاً', 'phd_cert');
        // 7. قرار الجامعة بالتسجيل بدرجة الدكتوراه (إجباري)
        $uploadAndAttachPhd('file_phd_registration_decision', $edPhD->id, $regDecTypeId, 'قرار الجامعة بالتسجيل بدرجة الدكتوراه', 'phd_registration_decision');
        // 8. قرار الجامعة بالمناقشة (إجباري)
        $uploadAndAttachPhd('file_phd_defense_decision', $edPhD->id, $defDecTypeId, 'قرار الجامعة بالمناقشة للدكتوراه', 'phd_defense_decision');
        // 9. قرار الجامعة بالحصول على الدرجة (إجباري)
        $uploadAndAttachPhd('file_phd_grant_decision', $edPhD->id, $grantDecTypeId, 'قرار الجامعة بالحصول على درجة الدكتوراه', 'phd_grant_decision');
        // 10. طلب الجامعة للتعادل (إجباري)
        $uploadAndAttachPhd('file_uni_request', $edPhD->id, 15, 'طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة)', 'uni_request');
        // 11. السيرة الذاتية (إجباري)
        $uploadAndAttachPhd('file_cv', $edPhD->id, 14, 'السيرة الذاتية للمرشح', 'cv');
        // 12. شهادة ICDL (اختياري)
        if ($request->hasFile('file_icdl') || isset($existingAttachments[19]) || isset($existingAttachments['icdl'])) {
            $uploadAndAttachPhd('file_icdl', $edPhD->id, 19, 'شهادة مهارات الحاسوب (ICDL)', 'icdl');
        }
        // 13. شهادة اللغة الإنكليزية (اختياري)
        if ($request->hasFile('file_english_test') || isset($existingAttachments[18]) || isset($existingAttachments['english_test'])) {
            $uploadAndAttachPhd('file_english_test', $edPhD->id, 18, 'شهادة إتقان اللغة الإنكليزية', 'english_test');
        }
        // 14. ملخص عن أطروحة الدكتوراه (إجباري)
        $uploadAndAttachPhd('file_thesis_summary', $edPhD->id, 12, 'ملخص أطروحة الدكتوراه باللغة العربية', 'thesis_summary');
        // 15. إيصال دفع 125,000 ل.س (رسم تعادل الدكتوراه) (إجباري)
        $uploadAndAttachPhd('file_fees_receipt', $edPhD->id, 17, 'إيصال تسديد رسم تعادل الدكتوراه (125,000 ل.س)', 'fees_receipt');
        // 16. مرفقات أخرى (اختياري)
        if ($request->hasFile('file_other_attachments') || isset($existingAttachments[23]) || isset($existingAttachments['other_attachments'])) {
            $uploadAndAttachPhd('file_other_attachments', $edPhD->id, 23, 'مرفقات ووثائق أخرى', 'other_attachments');
        }

        if ($isAdminEdit) {
            return $this->redirectAfterAdminWizardUpdate($application);
        }

        if ($isDraft) {
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
    private function prepareForeignMastersWizardData(Request $request, string $trackType = 'applied', ?Application $adminDraft = null): array
    {
        $trackType = in_array($trackType, ['applied', 'theoretical'], true) ? $trackType : 'applied';
        $isAdminEdit = ($adminDraft !== null);
        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        $universities = LookupUniversity::all();
        $educationLevels = LookupEducationLevel::all();
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;

        $syrianUniGroups = $this->getSyrianDoctorateUniversityGroups($syriaId);
        $syrianPublicUnis = $syrianUniGroups['public'];
        $syrianPrivateUnis = $syrianUniGroups['private'];
        $syrianInstitutes = $syrianUniGroups['institutes'];

        $unreadNotifications = $isAdminEdit ? collect() : $this->getUnreadNotifications();
        $draft = $adminDraft;
        if (!$isAdminEdit && $request->has('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.attachments.attachmentType', 'educations.residences', 'experiences'])
                ->first();
        }

        $adminSubmitRoute = $isAdminEdit ? route('admin.applications.wizard_update', $draft->id) : null;
        $adminWizardTrack = $isAdminEdit ? ('foreign_masters_' . $trackType) : null;

        return compact(
            'countries',
            'universities',
            'syrianPublicUnis',
            'syrianPrivateUnis',
            'syrianInstitutes',
            'educationLevels',
            'syriaId',
            'unreadNotifications',
            'draft',
            'trackType',
            'isAdminEdit',
            'adminSubmitRoute',
            'adminWizardTrack'
        );
    }

    public function showForeignMastersAppliedWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.foreign_masters_applied', $this->prepareForeignMastersWizardData($request, 'applied'));
    }

    public function showForeignMastersTheoreticalWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.foreign_masters_theoretical', $this->prepareForeignMastersWizardData($request, 'theoretical'));
    }

    public function showForeignMastersWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $trackType = in_array($request->query('track'), ['applied', 'theoretical'], true)
            ? $request->query('track')
            : null;

        $data = $this->prepareForeignMastersWizardData($request, $trackType ?? 'applied');

        if ($trackType === null && $data['draft']) {
            $draftType = (string) ($data['draft']->request_type ?? '');
            $draftExpDays = (int) ($data['draft']->experiences ? $data['draft']->experiences->sum('duration_days') : 0);
            $data['trackType'] = (str_contains($draftType, 'نظري') || (!str_contains($draftType, 'تطبيقي') && $draftExpDays >= 730))
                ? 'theoretical'
                : 'applied';
        }

        return view('university.apply.foreign_masters', $data);
    }

    public function submitForeignMastersAppliedWizard(Request $request)
    {
        return $this->submitForeignMastersWizard($request, null, 'applied');
    }

    public function submitForeignMastersTheoreticalWizard(Request $request)
    {
        return $this->submitForeignMastersWizard($request, null, 'theoretical');
    }

    public function submitForeignMastersWizard(Request $request, $id = null, ?string $trackOverride = null)
    {
        $isAdminEdit = (bool) $request->attributes->get('admin_wizard_edit', false);
        if (!$isAdminEdit && \App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $existingApp = null;
        if ($isAdminEdit) {
            $adminAppId = (int) $request->attributes->get('admin_target_app_id');
            $existingApp = Application::with(['candidate', 'educations.attachments', 'educations.residences', 'experiences'])
                ->findOrFail($adminAppId);
            $uniId = $request->filled('work_university_id') ? (int) $request->input('work_university_id') : $existingApp->work_university_id;
        } else {
            $uniId = Auth::user()->university_id;
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
        }

        $isExisting = ($existingApp !== null);

        $resolvedTrack = in_array($trackOverride, ['applied', 'theoretical'], true)
            ? $trackOverride
            : (in_array($request->input('wizard_track'), ['applied', 'theoretical'], true)
                ? $request->input('wizard_track')
                : null);

        if ($resolvedTrack === null) {
            if ($request->input('study_mode') === 'theoretical' || $request->input('has_syrian_experience') === 'yes') {
                $resolvedTrack = 'theoretical';
            } elseif ($existingApp && str_contains((string) $existingApp->request_type, 'نظري')) {
                $resolvedTrack = 'theoretical';
            } else {
                $resolvedTrack = 'applied';
            }
        }
        $isTheoreticalTrack = ($resolvedTrack === 'theoretical');
        $isResidenceExempt = $request->boolean('is_residence_exempt');

        $existingFilesMap = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $note = (string) ($att->notes ?? '');
                    $tid  = (int) ($att->attachment_type_id ?? 0);

                    if (str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة الثانوية') || $tid === 2 || ($tid === 16 && str_contains($note, 'ثانوية'))) {
                        $existingFilesMap['file_hs_decision'] = true;
                    } elseif ($tid === 1 || (str_contains($note, 'ثانوية') && !str_contains($note, 'معادلة'))) {
                        $existingFilesMap['file_secondary_cert'] = true;
                    }
                    if (str_contains($note, 'معادلة الإجازة') || str_contains($note, 'معادلة البكالوريوس')) {
                        $existingFilesMap['file_ba_decision'] = true;
                    } elseif (str_contains($note, 'إجازة') || str_contains($note, 'بكالوريوس') || $tid === 3 || $tid === 2) {
                        $existingFilesMap['file_bachelor_cert'] = true;
                    }
                    if ($tid === 6 || ($tid === 4 && !str_contains($note, 'معادلة')) || (str_contains($note, 'شهادة الماجستير') && !str_contains($note, 'ملخص'))) {
                        $existingFilesMap['file_master_cert'] = true;
                    }
                    if (str_contains($note, 'التسجيل') || str_contains($note, 'الحصول على الدرجة') || str_contains($note, 'المناقشة') || $tid === 7 || ($tid === 8 && str_contains($note, 'تسجيل'))) {
                        $existingFilesMap['file_reg_defense_doc'] = true;
                    }
                    if (str_contains($note, 'ملخص') || ($tid === 8 && !str_contains($note, 'تسجيل')) || ($tid === 6 && str_contains($note, 'ملخص'))) {
                        $existingFilesMap['file_thesis_abstract'] = true;
                    }
                    if (str_contains($note, 'السيرة الذاتية') || ($tid === 14 && !str_contains($note, 'رسم') && !str_contains($note, 'رسوم') && !str_contains($note, 'إيصال'))) {
                        $existingFilesMap['file_cv'] = true;
                    }
                    if (str_contains($note, 'رسوم') || str_contains($note, 'رسم') || str_contains($note, 'إيصال تسديد') || $tid === 16 || $tid === 17 || ($tid === 14 && str_contains($note, 'إيصال'))) {
                        $existingFilesMap['file_fees_receipt'] = true;
                    }
                    if (str_contains($note, 'جواز السفر') || (str_contains($note, 'جواز') && !str_contains($note, 'الهجرة'))) {
                        $existingFilesMap['file_passport'] = true;
                    } elseif (str_contains($note, 'الهوية') || str_contains($note, 'هوية') || str_contains($note, 'إثبات الشخصية') || ($tid === 13 && !str_contains($note, 'إنكليزية') && !str_contains($note, 'اللغة'))) {
                        $existingFilesMap['file_national_id'] = true;
                    }
                    if ($tid === 24 || str_contains($note, 'الهجرة والجوازات') || str_contains($note, 'حركة الهجرة') || str_contains($note, 'بيان حركة')) {
                        $existingFilesMap['file_immigration_movement'] = true;
                    }
                    if ($tid === 19 || str_contains($note, 'ICDL') || str_contains($note, 'الحاسوب')) {
                        $existingFilesMap['file_icdl_cert'] = true;
                    }
                    if ($tid === 18 || str_contains($note, 'الإنكليزية') || str_contains($note, 'إنكليزية') || str_contains($note, 'الأجنبية')) {
                        $existingFilesMap['file_english_cert'] = true;
                    }
                    if (str_contains($note, 'كشف علامات') || ($tid === 5 && !str_contains($note, 'ملخص'))) {
                        $existingFilesMap['file_master_transcript'] = true;
                    }
                    if ($tid === 20 || $tid === 9 || str_contains($note, 'خبرة')) {
                        $existingFilesMap['file_experience_cert'] = true;
                    }
                    if (str_contains($note, 'عقود') || str_contains($note, 'العقود') || $tid === 10) {
                        $existingFilesMap['file_private_uni_contracts'] = true;
                    }
                    if (str_contains($note, 'رواتب') || str_contains($note, 'تحويل') || str_contains($note, 'إيصالات التحويل') || $tid === 11) {
                        $existingFilesMap['file_salary_receipts'] = true;
                    }
                    if (str_contains($note, 'طلب الجامعة') || str_contains($note, 'كتاب طلب التقويم') || str_contains($note, 'طلب التقويم')) {
                        $existingFilesMap['file_uni_request'] = true;
                    }
                    if ($tid === 23 || str_contains($note, 'أخرى') || str_contains($note, 'اخرى')) {
                        $existingFilesMap['file_other_attachments'] = true;
                    }
                }
            }
        }

        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && (
            $existingApp->status_id == 3 || 
            in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة', 3])
        ));

        if (!$isAdminEdit && $wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ هذا الطلب كمسودة لأن المعاملة قيد المعالجة (بانتظار استكمال الوثائق). يرجى استكمال البيانات والمرفقات ثم النقر على زر الإرسال.');
        }

        $isDraft = !$isAdminEdit && !$wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1');
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;

        if ($isDraft || $isAdminEdit) {
            $rules = [
                'full_name' => $isAdminEdit ? 'required|string|max:255' : 'nullable|string|max:255',
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

                'is_residence_exempt' => 'nullable|boolean',
                'residences' => 'nullable|array',

                'file_national_id' => 'nullable|file|mimes:pdf|max:2048',
                'file_secondary_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_hs_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_ba_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_prev_qual_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_transcript' => 'nullable|file|mimes:pdf|max:2048',
                'file_thesis_abstract' => 'nullable|file|mimes:pdf|max:5120',
                'file_cv' => 'nullable|file|mimes:pdf|max:2048',
                'file_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_reg_defense_doc' => 'nullable|file|mimes:pdf|max:2048',
                'file_experience_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_private_uni_contracts' => 'nullable|file|mimes:pdf|max:2048',
                'file_salary_receipts' => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_fees_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_passport' => 'nullable|file|mimes:pdf|max:2048',
                'file_immigration_movement' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
            ];
            $messages = [];
        } else {
            $isHsForeign = ($request->input('hs_country_id') && $request->input('hs_country_id') != $syriaId);
            $isBaSyrian  = ($request->input('ba_country_id') == $syriaId);
            $isBaOther   = !$isBaSyrian || $request->input('ba_university_id') === 'other';

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

                // Step 3: Bachelor (بدون اشتراط قرار معادلة حتى لو كانت غير سورية)
                'ba_country_id' => 'required|exists:lookup_countries,id',
                'ba_country_other' => 'nullable|string|max:255',
                'ba_university_id' => $isBaSyrian
                    ? [
                        'required',
                        function ($attribute, $value, $fail) {
                            if ($value !== 'other' && (!is_numeric($value) || !\App\Models\LookupUniversity::where('id', (int) $value)->exists())) {
                                $fail('يرجى اختيار الجامعة السورية المانحة للإجازة الجامعية.');
                            }
                        },
                    ]
                    : 'nullable',
                'ba_university_other' => $isBaOther ? 'required|string|max:255' : 'nullable|string|max:255',
                'ba_faculty' => 'required|string|max:255',
                'ba_department' => 'required|string|max:255',
                'ba_specialization' => 'nullable|string|max:255',
                'ba_registration_date' => 'nullable|date',
                'ba_grant_date' => 'required|date|before_or_equal:today',
                'ba_rank' => 'required|string|max:50',
                'ba_decision_no' => 'nullable|string|max:100',
                'ba_decision_date' => 'nullable|date',

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

                'is_residence_exempt' => 'nullable|boolean',
                'has_syrian_experience' => 'nullable|in:yes,no',
                'syrian_exp_years' => 'nullable|numeric',
                'syrian_exp_from' => 'nullable|numeric',
                'syrian_exp_to' => 'nullable|numeric',
                'syrian_exp_universities' => 'nullable|string',

                // Step 5: Residency (Required only when NOT exempt)
                'residences' => $isResidenceExempt ? 'nullable|array' : 'required|array|min:1',
                'residences.*.entry_date' => $isResidenceExempt ? 'nullable|date' : 'required|date',
                'residences.*.exit_date' => $isResidenceExempt ? 'nullable|date' : 'required|date|after_or_equal:residences.*.entry_date',

                // Step 6: Attachments
                'file_national_id' => !empty($existingFilesMap['file_national_id']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_secondary_cert' => !empty($existingFilesMap['file_secondary_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_hs_decision' => ($isHsForeign && empty($existingFilesMap['file_hs_decision'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert' => !empty($existingFilesMap['file_bachelor_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_ba_decision' => 'nullable|file|mimes:pdf|max:2048',
                'file_prev_qual_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_cert' => !empty($existingFilesMap['file_master_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_reg_defense_doc' => !empty($existingFilesMap['file_reg_defense_doc']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_fees_receipt' => !empty($existingFilesMap['file_fees_receipt']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_passport' => (!$isResidenceExempt && empty($existingFilesMap['file_passport'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_immigration_movement' => (!$isResidenceExempt && empty($existingFilesMap['file_immigration_movement'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_cv' => !empty($existingFilesMap['file_cv']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_thesis_abstract' => !empty($existingFilesMap['file_thesis_abstract']) ? 'nullable|file|mimes:pdf|max:5120' : 'required|file|mimes:pdf|max:5120',
                'file_icdl_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert' => 'nullable|file|mimes:pdf|max:2048',
                'file_master_transcript' => 'nullable|file|mimes:pdf|max:2048',
                'file_library_receipt' => 'nullable|file|mimes:pdf|max:2048',
                'file_uni_request' => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments' => 'nullable|file|mimes:pdf|max:2048',
                // مرفقات المسار النظري الإلزامية (إيصالات التحويل + العقود + شهادة الخبرة)
                'file_salary_receipts' => ($isTheoreticalTrack && empty($existingFilesMap['file_salary_receipts'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_private_uni_contracts' => ($isTheoreticalTrack && empty($existingFilesMap['file_private_uni_contracts'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_experience_cert' => ($isTheoreticalTrack && empty($existingFilesMap['file_experience_cert'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
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
                'ba_university_id.required' => 'يرجى اختيار الجامعة السورية المانحة للإجازة الجامعية.',
                'ba_university_other.required' => 'يرجى إدخال اسم الجامعة المانحة للإجازة الجامعية.',
                'ba_faculty.required' => 'يرجى إدخال كلية الإجازة الجامعية.',
                'ba_grant_date.required' => 'يرجى إدخال تاريخ منح الإجازة الجامعية.',
                'ba_rank.required' => 'يرجى اختيار تقدير الإجازة الجامعية.',
                'ma_country_id.required' => 'يرجى اختيار بلد دراسة الماجستير.',
                'ma_university_other.required' => 'يرجى إدخال اسم الجامعة الخارجية المانحة للماجستير.',
                'ma_faculty.required' => 'يرجى إدخال كلية درجة الماجستير.',
                'ma_grant_date.required' => 'يرجى إدخال تاريخ منح شهادة الماجستير.',
                'ma_rank.required' => 'يرجى اختيار تقدير شهادة الماجستير.',
                'ma_thesis_title.required' => 'يرجى إدخال عنوان أطروحة الماجستير.',
                'residences.required' => 'يرجى إضافة حركة دخول وخروج واحدة على الأقل في خطوة الإقامة (أو تفعيل خيار الإعفاء من شرط الإقامة إذا كان التخصص نظرياً معفى).',
                'residences.min' => 'يرجى إضافة حركة دخول وخروج واحدة على الأقل في خطوة الإقامة.',
                'file_national_id.required' => 'يرجى إرفاق صورة الهوية الشخصية كمتطلب إلزامي.',
                'file_secondary_cert.required' => 'يرجى إرفاق صورة شهادة البكالوريا كمتطلب إلزامي.',
                'file_hs_decision.required' => 'يرجى إرفاق قرار معادلة الشهادة الثانوية غير السورية كمتطلب إلزامي.',
                'file_bachelor_cert.required' => 'يرجى إرفاق شهادة الإجازة الجامعية كمتطلب إلزامي.',
                'file_master_cert.required' => 'يرجى إرفاق شهادة الماجستير مع الترجمة والتصديق كمتطلب إلزامي.',
                'file_reg_defense_doc.required' => 'يرجى إرفاق قرارات التسجيل بالدرجة وقرار الحصول على الدرجة كمتطلب إلزامي.',
                'file_fees_receipt.required' => 'يرجى إرفاق إيصال تسديد رسم التعادل كمتطلب إلزامي.',
                'file_passport.required' => 'يرجى إرفاق جواز السفر مع صفحات الأختام (إلزامي لغير المعفى من شرط الإقامة).',
                'file_immigration_movement.required' => 'يرجى إرفاق بيان حركة من الهجرة والجوازات (إلزامي لغير المعفى من شرط الإقامة).',
                'file_cv.required' => 'يرجى إرفاق السيرة الذاتية للمرشح كمتطلب إلزامي.',
                'file_thesis_abstract.required' => 'يرجى إرفاق ملخص عن رسالة الماجستير باللغة العربية.',
                'file_thesis_abstract.max' => 'حجم ملف ملخص رسالة الماجستير يجب ألا يتجاوز 5 ميغابايت.',
                'file_salary_receipts.required' => 'يرجى إرفاق إيصالات التحويل / الرواتب لمسار الماجستير النظري الخارجي.',
                'file_private_uni_contracts.required' => 'يرجى إرفاق العقود مع الجامعات لمسار الماجستير النظري الخارجي.',
                'file_experience_cert.required' => 'يرجى إرفاق شهادة الخبرة التدريسية لمسار الماجستير النظري الخارجي.',
                'max' => 'حجم الملف المرفق يجب ألا يتجاوز 2 ميغابايت.',
            ];
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update (حل جذري لمنع خطأ تعارض الرقم الوطني UNIQUE constraint failed)
        $fullName = $request->filled('full_name') ? trim($request->full_name) : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة ماجستير خارجي');
        $cleanNatId = $request->filled('national_id') ? trim($request->national_id) : null;

        $matchedProfile = null;
        if ($cleanNatId) {
            $matchedProfile = EquivalenceProfile::where('national_id', $cleanNatId)->first();
        }

        if ($matchedProfile) {
            $candidate = $matchedProfile;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => 'مرشح تعادل ماجستير خارجي',
                'phone'          => $request->phone ?? $candidate->phone,
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $request->email ?? $candidate->email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);

            if ($existingApp && $existingApp->candidate_id && $existingApp->candidate_id != $candidate->id) {
                $oldCandidateId = $existingApp->candidate_id;
                $existingApp->update(['candidate_id' => $candidate->id]);

                $oldCandidate = EquivalenceProfile::find($oldCandidateId);
                if ($oldCandidate && str_starts_with($oldCandidate->national_id ?? '', 'TMP-')) {
                    $oldCandidate->delete();
                }
            }
        } elseif ($isExisting && $existingApp->candidate) {
            $candidate = $existingApp->candidate;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'national_id'    => $cleanNatId ?: $candidate->national_id,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => 'مرشح تعادل ماجستير خارجي',
                'phone'          => $request->phone ?? $candidate->phone,
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $request->email ?? $candidate->email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);
        } else {
            $natIdToSave = $cleanNatId ?: ('TMP-' . time() . '-' . rand(100, 999));
            $candidate = EquivalenceProfile::create([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? '',
                'mother_name'    => $request->mother_name ?? '',
                'national_id'    => $natIdToSave,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'dob'            => $request->filled('dob') ? $request->dob : null,
                'job_title'      => 'مرشح تعادل ماجستير خارجي',
                'phone'          => $request->phone,
                'mobile'         => $request->mobile ?? '',
                'email'          => $request->email ?? '',
                'address'        => $request->address ?? '',
                'gender'         => $request->gender ?? 'ذكر',
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);
        }

        // 3. Determine Application Type & Number
        $experiencesInput = $request->input('experiences', []);
        $totalExpDays = 0;
        $validExperiences = [];

        if ($isTheoreticalTrack) {
            if (is_array($experiencesInput)) {
                foreach ($experiencesInput as $exp) {
                    if (!empty($exp['institution_name']) && !empty($exp['start_date']) && !empty($exp['end_date'])) {
                        $startDate = \Carbon\Carbon::parse($exp['start_date']);
                        $endDate = \Carbon\Carbon::parse($exp['end_date']);
                        $durationDays = $endDate->gte($startDate) ? (int) $startDate->diffInDays($endDate) : 0;
                        $totalExpDays += $durationDays;
                        $validExperiences[] = [
                            'institution_name' => trim($exp['institution_name']),
                            'role_or_subject'  => !empty($exp['role_or_subject']) ? trim($exp['role_or_subject']) : null,
                            'start_date'       => $exp['start_date'],
                            'end_date'         => $exp['end_date'],
                            'duration_days'    => $durationDays,
                            'notes'            => !empty($exp['notes']) ? trim($exp['notes']) : null,
                        ];
                    }
                }
            }

            if (empty($validExperiences) && $request->input('has_syrian_experience') === 'yes') {
                if ($request->filled('syrian_exp_from') && $request->filled('syrian_exp_to')) {
                    $startDate = \Carbon\Carbon::parse($request->syrian_exp_from . '-01-01');
                    $endDate = \Carbon\Carbon::parse($request->syrian_exp_to . '-12-31');
                    $durationDays = $endDate->gte($startDate) ? (int) $startDate->diffInDays($endDate) : 0;
                    $totalExpDays += $durationDays;
                    $validExperiences[] = [
                        'institution_name' => $request->syrian_exp_universities ?: 'جامعة سورية',
                        'role_or_subject'  => 'تدريس جامعي',
                        'start_date'       => $startDate->format('Y-m-d'),
                        'end_date'         => $endDate->format('Y-m-d'),
                        'duration_days'    => $durationDays,
                        'notes'            => null,
                    ];
                } elseif ($request->filled('syrian_exp_years') && (int)$request->syrian_exp_years >= 2) {
                    $totalExpDays = (int)$request->syrian_exp_years * 365;
                }
            }

            if (!$isDraft && !$isAdminEdit && $totalExpDays < 730) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "عذراً، مسار الماجستير الخارجي النظري يتطلب خبرة تدريسية تراكمية لا تقل عن سنتين (730 يوماً فأكثر) داخل سوريا. مجموع الخبرة المدخلة حالياً: {$totalExpDays} يوماً.");
            }
        }

        $hasExp = $isTheoreticalTrack;
        $requestType = $hasExp ? 'ماجستير خارجي - نظري' : 'ماجستير خارجي - تطبيقي';
        $appNo = $isExisting ? $existingApp->application_no : (($hasExp ? 'MA-FOR-TH-' : 'MA-FOR-AP-') . rand(100000, 999999));

        $wasAwaitingDocs = !$isAdminEdit && ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $statusToSet = $isAdminEdit
            ? ($existingApp->getRawOriginal('status') ?: $existingApp->status)
            : ($isDraft ? 'مسودة' : 'تحت التدقيق الأولي');

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
                'is_residence_exempt' => $isResidenceExempt,
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
                'has_previous_degree' => $hasExp,
                'is_residence_exempt' => $isResidenceExempt,
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

        $baUniOtherToSave = ($baCountryId != $syriaId || $request->input('ba_university_id') === 'other')
            ? ($request->filled('ba_university_other') ? trim($request->ba_university_other) : optional($existingBaEd)->university_other)
            : null;

        $baEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'education_level_id' => 1,
            ],
            [
                'country_id' => $baCountryId,
                'university_id' => $baUniId,
                'university_other' => $baUniOtherToSave,
                'faculty' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->faculty ?? ''),
                'department' => $request->filled('ba_department') ? $request->ba_department : (optional($existingBaEd)->department ?? ''),
                'general_specialization' => $request->filled('ba_faculty') ? $request->ba_faculty : (optional($existingBaEd)->general_specialization ?? ''),
                'exact_specialization' => $request->filled('ba_specialization') ? $request->ba_specialization : (optional($existingBaEd)->exact_specialization ?? null),
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
                'experience_from_year' => !empty($validExperiences) ? collect($validExperiences)->min('start_date') : ($request->filled('syrian_exp_from') ? $request->syrian_exp_from : optional($existingMaEd)->experience_from_year),
                'experience_to_year' => !empty($validExperiences) ? collect($validExperiences)->max('end_date') : ($request->filled('syrian_exp_to') ? $request->syrian_exp_to : optional($existingMaEd)->experience_to_year),
                'notes' => !empty($validExperiences) ? ('فترات خبرة تدريسية: ' . collect($validExperiences)->pluck('institution_name')->implode('، ')) : ($expNotes ?: optional($existingMaEd)->notes),
            ]
        );

        // 4.3.1 Store Application Experiences (فترات الخبرة التدريسية)
        if ($isTheoreticalTrack && ($request->has('experiences') || $request->boolean('experiences_submitted_flag'))) {
            ApplicationExperience::where('application_id', $application->id)->delete();
            foreach ($validExperiences as $vExp) {
                ApplicationExperience::create([
                    'application_id'   => $application->id,
                    'education_id'     => $maEd->id,
                    'institution_name' => $vExp['institution_name'],
                    'role_or_subject'  => $vExp['role_or_subject'],
                    'start_date'       => $vExp['start_date'],
                    'end_date'         => $vExp['end_date'],
                    'duration_days'    => $vExp['duration_days'],
                    'notes'            => $vExp['notes'],
                ]);
            }
        } elseif (!$isTheoreticalTrack && ($request->boolean('experiences_submitted_flag') || $request->has('experiences'))) {
            ApplicationExperience::where('application_id', $application->id)->delete();
        }

        // 4.4 Store / Update Education Residences (حركات الإقامة والدخول والخروج)
        if ($request->boolean('residences_submitted_flag') || ($request->has('residences') && is_array($request->residences))) {
            EducationResidence::where('education_id', $maEd->id)->delete();

            foreach ($request->input('residences', []) as $resData) {
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

        // 5. Attachment File Uploads (متطابق مع جدول lookup_attachment_types)
        $feeNote = $isTheoreticalTrack
            ? 'إيصال تسديد رسم تعادل الماجستير النظري الخارجي (125,000 ل.س)'
            : 'إيصال تسديد رسم تعادل الماجستير التطبيقي الخارجي (100,000 ل.س)';
        $feeTypeId = $isTheoreticalTrack ? 17 : 16;

        $fileInputs = [
            'file_national_id'           => ['id' => 13, 'notes' => 'صورة عن الهوية الشخصية', 'ed_id' => $maEd->id],
            'file_secondary_cert'        => ['id' => 1,  'notes' => 'صورة عن شهادة البكالوريا (الثانوية العامة)', 'ed_id' => $hsEd->id],
            'file_hs_decision'           => ['id' => 2,  'notes' => 'قرار معادلة الشهادة الثانوية غير السورية', 'ed_id' => $hsEd->id],
            'file_bachelor_cert'         => ['id' => 3,  'notes' => 'نسخة مصدقة عن الإجازة الجامعية الأولى', 'ed_id' => $baEd->id],
            'file_ba_decision'           => ['id' => 4,  'notes' => 'قرار معادلة الإجازة الجامعية الأولى غير السورية', 'ed_id' => $baEd->id],
            'file_prev_qual_cert'        => ['id' => 5,  'notes' => 'الشهادة قبل المؤهل العلمي الأخير', 'ed_id' => $maEd->id],
            'file_master_cert'           => ['id' => 6,  'notes' => 'شهادة الماجستير الخارجي مع الترجمة والتصديق', 'ed_id' => $maEd->id],
            'file_reg_defense_doc'       => ['id' => 7,  'notes' => 'قرارات التسجيل بالدرجة وقرار الحصول على الدرجة', 'ed_id' => $maEd->id],
            'file_fees_receipt'          => ['id' => $feeTypeId, 'notes' => $feeNote, 'ed_id' => $maEd->id],
            'file_passport'              => ['id' => 13, 'notes' => 'صورة جواز السفر مع صفحات الأختام والإقامة', 'ed_id' => $maEd->id],
            'file_immigration_movement'  => ['id' => 24, 'notes' => 'بيان حركة من الهجرة والجوازات', 'ed_id' => $maEd->id],
            'file_cv'                    => ['id' => 14, 'notes' => 'السيرة الذاتية للمرشح', 'ed_id' => $maEd->id],
            'file_thesis_abstract'       => ['id' => 8,  'notes' => 'ملخص عن رسالة الماجستير باللغة العربية', 'ed_id' => $maEd->id],
            'file_icdl_cert'             => ['id' => 19, 'notes' => 'شهادة قيادة الحاسوب (ICDL)', 'ed_id' => $maEd->id],
            'file_english_cert'          => ['id' => 18, 'notes' => 'شهادة اللغة الإنكليزية', 'ed_id' => $maEd->id],
            'file_master_transcript'     => ['id' => 5,  'notes' => 'كشف علامات الماجستير', 'ed_id' => $maEd->id],
            'file_experience_cert'       => ['id' => 20, 'notes' => 'شهادة الخبرة التدريسية داخل سوريا', 'ed_id' => $maEd->id],
            'file_private_uni_contracts' => ['id' => 21, 'notes' => 'العقود مع الجامعات الخاصة', 'ed_id' => $maEd->id],
            'file_salary_receipts'       => ['id' => 21, 'notes' => 'إيصالات التحويل والرواتب المصدقة', 'ed_id' => $maEd->id],
            'file_library_receipt'       => ['id' => 22, 'notes' => 'إيصال إيداع الأطروحة لدى المكتبة الوطنية', 'ed_id' => $maEd->id],
            'file_uni_request'           => ['id' => 15, 'notes' => 'طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة)', 'ed_id' => $maEd->id],
        ];

        foreach ($fileInputs as $inputKey => $meta) {
            $typeId = $meta['id'];
            $note = $meta['notes'];
            $targetEdId = $meta['ed_id'] ?? $maEd->id;
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $cleanCandidate = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name)));
                $keySuffix = '_' . $inputKey;
                $filename = 'MA_FOR_' . $appNo . '_Type' . $typeId . $keySuffix . '_' . ($cleanCandidate ?: 'Candidate') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');

                $matchCriteria = [
                    'education_id' => $targetEdId,
                    'attachment_type_id' => $typeId,
                ];
                if (in_array($typeId, [5, 13, 15, 21, 23], true)) {
                    $matchCriteria['notes'] = $note;
                }

                EducationAttachment::updateOrCreate(
                    $matchCriteria,
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
                'attachment_type_id' => 23,
                'file_path' => $path,
                'notes' => 'مرفقات ووثائق داعمة أخرى',
            ]);
        }

        if ($isAdminEdit) {
            return $this->redirectAfterAdminWizardUpdate($application);
        }

        if ($isDraft) {
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

    // =========================================================================
    // FOREIGN DOCTORATE EQUIVALENCE WIZARD (معاملة الدكتوراه غير السورية - الخارجية)
    // =========================================================================
    private function prepareForeignDoctorateWizardData(Request $request, ?Application $adminDraft = null): array
    {
        $isAdminEdit = ($adminDraft !== null);
        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        $universities = LookupUniversity::all();
        $educationLevels = LookupEducationLevel::all();
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;
        $syrianUniGroups = $this->getSyrianDoctorateUniversityGroups($syriaId);
        $syrianPublicUnis = $syrianUniGroups['public'];
        $syrianPrivateUnis = $syrianUniGroups['private'];
        $syrianInstitutes = $syrianUniGroups['institutes'];

        $unreadNotifications = $isAdminEdit ? collect() : $this->getUnreadNotifications();
        $draft = $adminDraft;
        if (!$isAdminEdit && $request->has('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.attachments.attachmentType', 'educations.residences'])
                ->first();
        }

        $adminSubmitRoute = $isAdminEdit ? route('admin.applications.wizard_update', $draft->id) : null;
        $adminWizardTrack = $isAdminEdit ? 'foreign_doctorate' : null;

        return compact(
            'countries',
            'universities',
            'educationLevels',
            'syriaId',
            'syrianPublicUnis',
            'syrianPrivateUnis',
            'syrianInstitutes',
            'unreadNotifications',
            'draft',
            'isAdminEdit',
            'adminSubmitRoute',
            'adminWizardTrack'
        );
    }

    public function showForeignDoctorateWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.foreign_doctorate', $this->prepareForeignDoctorateWizardData($request));
    }

    /**
     * حفظ مسودة أو تقديم معاملة تعادل الدكتوراه غير السورية (الخارجية)
     */
    public function submitForeignDoctorateWizard(Request $request, $id = null)
    {
        $isAdminEdit = (bool) $request->attributes->get('admin_wizard_edit', false);
        // 1. التحقق من حالة فتح النظام
        if (!$isAdminEdit && \App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $existingApp = null;
        if ($isAdminEdit) {
            $adminAppId = (int) $request->attributes->get('admin_target_app_id');
            $existingApp = Application::with(['candidate', 'educations.attachments', 'educations.residences'])
                ->findOrFail($adminAppId);
            $uniId = $request->filled('work_university_id') ? (int) $request->input('work_university_id') : $existingApp->work_university_id;
        } else {
            $uniId = Auth::user()->university_id;
            $draftId = $request->input('draft_id') ?: $id;
            
            if ($draftId) {
                $existingApp = Application::where('id', $draftId)
                    ->where('work_university_id', $uniId)
                    ->with(['educations.attachments', 'educations.residences'])
                    ->first();
            }

            // البحث الذكي عن مسودة مفتوحة للمرشح
            if (!$existingApp && $request->filled('national_id')) {
                $existingProfile = EquivalenceProfile::where('national_id', trim($request->national_id))->first();
                if ($existingProfile) {
                    $existingApp = Application::where('candidate_id', $existingProfile->id)
                        ->where('work_university_id', $uniId)
                        ->where(function($q) {
                            $q->where('status', 'مسودة')->orWhere('status', 1);
                        })
                        ->where(function($q) {
                            $q->where('request_type', 'like', '%دكتور%خارج%')
                              ->orWhere('request_type', 'دكتورة خارجية')
                              ->orWhere('request_type', 7);
                        })
                        ->with(['educations.attachments', 'educations.residences'])
                        ->latest('id')
                        ->first();
                }
            }
        }

        $isExisting = ($existingApp !== null);
        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && (
            $existingApp->status_id == 3 || 
            in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة', 3])
        ));

        if (!$isAdminEdit && $wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ هذا الطلب كمسودة لأن المعاملة قيد المعالجة (بانتظار استكمال الوثائق). يرجى استكمال البيانات والمرفقات ثم النقر على زر الإرسال.');
        }

        $isDraft = !$isAdminEdit && !$wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1');
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;
        $currentYear = date('Y');

        // خريطة المرفقات المرفوعة مسبقاً لمنع المطالبة بها مجدداً في المسودات
        $existingFilesMap = [];
        if ($existingApp && $existingApp->educations) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    $typeId = (int)$att->attachment_type_id;
                    $note = $att->notes ?? '';

                    if ($typeId === 1 || (str_contains($note, 'ثانوية') && !str_contains($note, 'معادلة'))) $existingFilesMap['file_secondary_cert'] = true;
                    if ($typeId === 2 || str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة درجة البكالوريا')) $existingFilesMap['file_hs_decision'] = true;
                    if ($typeId === 3 || (str_contains($note, 'الإجازة') && !str_contains($note, 'معادلة'))) $existingFilesMap['file_bachelor_cert'] = true;
                    if ($typeId === 6 || (str_contains($note, 'شهادة الماجستير') && !str_contains($note, 'معادلة') && !str_contains($note, 'ملخص'))) $existingFilesMap['file_master_cert'] = true;
                    if ($typeId === 9 || str_contains($note, 'معادلة شهادة الماجستير')) $existingFilesMap['file_ma_decision'] = true;
                    if ($typeId === 10 || (str_contains($note, 'شهادة الدكتوراه') && !str_contains($note, 'ملخص') && !str_contains($note, 'كاملة'))) $existingFilesMap['file_phd_cert'] = true;
                    if ($typeId === 11 || str_contains($note, 'مجلس') || str_contains($note, 'تواريخ التسجيل والمناقشة')) $existingFilesMap['file_phd_council_decisions'] = true;
                    if ($typeId === 14 || str_contains($note, 'السيرة')) $existingFilesMap['file_cv'] = true;
                    if ($typeId === 17 || str_contains($note, '125,000') || str_contains($note, 'رسم تعادل الدكتوراه')) $existingFilesMap['file_fees_receipt'] = true;
                    if ($typeId === 22 || str_contains($note, 'المكتبة الوطنية')) $existingFilesMap['file_library_receipt'] = true;
                    if ($typeId === 5 && (str_contains($note, 'الدكتوراه') || str_contains($note, 'كشف علامات الدكتوراه'))) $existingFilesMap['file_phd_transcript'] = true;
                    if ($typeId === 19 || str_contains($note, 'ICDL')) $existingFilesMap['file_icdl_cert'] = true;
                    if ($typeId === 18 || str_contains($note, 'الإنكليزية') || str_contains($note, 'إنكليزية')) $existingFilesMap['file_english_cert'] = true;
                    if ($typeId === 13) {
                        if (str_contains($note, 'صفحات الأختام') || str_contains($note, 'جواز السفر مع')) {
                            $existingFilesMap['file_passport'] = true;
                        } else {
                            $existingFilesMap['file_national_id'] = true;
                        }
                    } elseif (str_contains($note, 'هوية') || str_contains($note, 'إثبات الشخصية')) {
                        $existingFilesMap['file_national_id'] = true;
                    } elseif (str_contains($note, 'صفحات الأختام')) {
                        $existingFilesMap['file_passport'] = true;
                    }
                    if ($typeId === 24 || str_contains($note, 'حركة الهجرة والجوازات')) $existingFilesMap['file_immigration_movement'] = true;
                    if ($typeId === 23 || str_contains($note, 'مرفقات ووثائق أخرى')) $existingFilesMap['file_other_attachments'] = true;
                }
            }
        }

        $isResidenceExempt = $request->boolean('is_residence_exempt');
        if (!$request->has('is_residence_exempt') && $request->has('requires_residence')) {
            $isResidenceExempt = !$request->boolean('requires_residence');
        }
        $hasMaEquivalence = $request->boolean('has_ma_equivalence');

        // =========================================================================
        // 2. قواعد التحقق (Validation Rules)
        // =========================================================================
        if ($isDraft || $isAdminEdit) {
            $rules = [
                'full_name'           => $isAdminEdit ? 'required|string|max:255' : 'nullable|string|max:255',
                'national_id'         => 'nullable|string|max:50',
                'ba_university_id'    => 'nullable|string|max:50',
                'ba_university_other' => 'nullable|string|max:255',
                'ma_university_id'    => 'nullable|string|max:50',
                'ma_university_other' => 'nullable|string|max:255',
                'has_ma_equivalence'  => 'nullable|boolean',
                'is_residence_exempt' => 'nullable|boolean',
                'residences'          => 'nullable|array',
            ];
            $allFiles = [
                'file_national_id', 'file_secondary_cert', 'file_hs_decision',
                'file_bachelor_cert', 'file_master_cert', 'file_ma_decision',
                'file_phd_cert', 'file_phd_council_decisions', 'file_cv',
                'file_fees_receipt', 'file_library_receipt', 'file_phd_transcript',
                'file_icdl_cert', 'file_english_cert', 'file_passport',
                'file_immigration_movement', 'file_other_attachments',
            ];
            foreach ($allFiles as $fKey) {
                $rules[$fKey] = 'nullable|file|mimes:pdf|max:2048';
            }
            $messages = [];
        } else {
            $isHsForeign = ($request->filled('hs_country_id') && $request->hs_country_id != $syriaId);
            $baIsSyria = ($request->ba_country_id == $syriaId);
            $baIsOther = !$baIsSyria || ($request->input('ba_university_id') === 'other');
            $maIsSyria = ($request->ma_country_id == $syriaId);
            $maIsOther = !$maIsSyria || ($request->input('ma_university_id') === 'other');
            $requiresResidence = !$isResidenceExempt;
            $hsYearDate = $request->filled('hs_grant_date') ? ($request->hs_grant_date . '-01-01') : '1950-01-01';

            $rules = [
                // أ. البيانات الشخصية والجامعة
                'full_name'        => 'required|string|max:255',
                'father_name'      => 'required|string|max:255',
                'mother_name'      => 'required|string|max:255',
                'nationality_id'   => 'required|exists:lookup_countries,id',
                'national_id'      => ['required', 'regex:/^[0-9]{11}$/'],
                'dob'              => 'required|date|before_or_equal:today',
                'gender'           => 'required|in:ذكر,أنثى',
                'mobile'           => 'required|string|max:50',
                'email'            => 'required|email:filter|max:255',
                'address'          => 'required|string',
                'req_no'           => 'required|numeric|min:1',
                'req_date'         => 'required|date|before_or_equal:today',
                'work_faculty'     => 'required|string|max:255',
                'work_department'  => 'required|string|max:255',

                // ب. الثانوية العامة
                'hs_country_id'    => 'required|exists:lookup_countries,id',
                'hs_type'          => 'required|string|max:100',
                'hs_grant_date'    => 'required|numeric|digits:4|max:' . $currentYear,
                'hs_decision_no'   => $isHsForeign ? 'required|string|max:100' : 'nullable',
                'hs_decision_date' => $isHsForeign ? 'required|date|after_or_equal:' . $hsYearDate . '|before_or_equal:today' : 'nullable',

                // ج. الإجازة الجامعية الأولى (بدون قرار معادلة نهائياً + قائمة الجامعات داخل سوريا)
                'ba_country_id'             => 'required|exists:lookup_countries,id',
                'ba_university_id'          => $baIsOther ? 'nullable' : 'required|exists:lookup_universities,id',
                'ba_university_other'       => $baIsOther ? 'required|string|max:255' : 'nullable|string|max:255',
                'ba_faculty'                => 'required|string|max:255',
                'ba_department'             => 'required|string|max:255',
                'ba_general_specialization' => 'required|string|max:255',
                'ba_rank'                   => 'required|in:ممتاز,جيد جداً,جيد,مقبول',
                'ba_grant_date'             => 'required|date|after:' . $hsYearDate . '|before_or_equal:today',

                // د. درجة الماجستير (تاريخ الحصول على الدرجة فقط + checkbox معادلة اختياري + قائمة الجامعات داخل سوريا)
                'ma_country_id'             => 'required|exists:lookup_countries,id',
                'ma_university_id'          => $maIsOther ? 'nullable' : 'required|exists:lookup_universities,id',
                'ma_university_other'       => $maIsOther ? 'required|string|max:255' : 'nullable|string|max:255',
                'ma_faculty'                => 'required|string|max:255',
                'ma_department'             => 'required|string|max:255',
                'ma_general_specialization' => 'required|string|max:255',
                'ma_specialization'         => 'nullable|string|max:255',
                'ma_rank'                   => 'required|in:ممتاز,جيد جداً,جيد,مقبول',
                'ma_grant_date'             => 'required|date|after:ba_grant_date|before_or_equal:today',
                'has_ma_equivalence'        => 'nullable|boolean',
                'ma_decision_no'            => $hasMaEquivalence ? 'required|string|max:100' : 'nullable|string|max:100',
                'ma_decision_date'          => $hasMaEquivalence ? 'required|date|after_or_equal:ma_grant_date|before_or_equal:today' : 'nullable|date',

                // هـ. الدكتوراه الخارجية (التسجيل + المناقشة + المنح إلزامية)
                'phd_country_id'            => [
                    'required',
                    'exists:lookup_countries,id',
                    function ($attribute, $value, $fail) use ($syriaId) {
                        if ($value == $syriaId) {
                            $fail('بلد الحصول على درجة الدكتوراه الخارجية يجب أن يكون بلداً غير سوري.');
                        }
                    }
                ],
                'phd_university_other'      => 'required|string|max:255',
                'phd_faculty'               => 'required|string|max:255',
                'phd_department'            => 'required|string|max:255',
                'phd_general_specialization'=> 'nullable|string|max:255',
                'phd_specialization'        => 'nullable|string|max:255',
                'phd_rank'                  => 'required|in:شرف / امتياز,ممتاز,جيد جداً,جيد',
                'phd_thesis_title'          => 'required|string|max:500',
                'phd_supervisor'            => 'required|string|max:255',
                'phd_study_system'          => 'required|string|max:100',
                'phd_study_language'        => 'required|string|max:100',
                'phd_registration_date'     => 'required|date|after:ma_grant_date|before_or_equal:today',
                'phd_defense_date'          => 'required|date|after:phd_registration_date|before_or_equal:today',
                'phd_grant_date'            => 'required|date|after_or_equal:phd_defense_date|before_or_equal:today',
                'is_residence_exempt'       => 'nullable|boolean',

                // و. حركات الإقامة والسفر (مطلوبة فقط عند عدم الإعفاء من الإقامة)
                'residences'                 => $requiresResidence ? 'required|array|min:1' : 'nullable|array',
                'residences.*.entry_date'    => $requiresResidence ? 'required|date' : 'nullable|date',
                'residences.*.exit_date'     => $requiresResidence ? 'required|date|after_or_equal:residences.*.entry_date' : 'nullable|date',
                'residences.*.entry_airport' => $requiresResidence ? 'required|string|max:255' : 'nullable|string|max:255',
                'residences.*.exit_airport'  => $requiresResidence ? 'required|string|max:255' : 'nullable|string|max:255',
                'residences.*.page_number'   => $requiresResidence ? 'required|string|max:50' : 'nullable|string|max:50',

                // ز. المرفقات الثبوتية (الـ 16 بنداً المعتمدة للدكتوراه الخارجية)
                'file_national_id'           => !empty($existingFilesMap['file_national_id']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_secondary_cert'        => !empty($existingFilesMap['file_secondary_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_hs_decision'           => ($isHsForeign && empty($existingFilesMap['file_hs_decision'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_bachelor_cert'         => !empty($existingFilesMap['file_bachelor_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_master_cert'           => !empty($existingFilesMap['file_master_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_ma_decision'           => ($hasMaEquivalence && empty($existingFilesMap['file_ma_decision'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_phd_cert'              => !empty($existingFilesMap['file_phd_cert']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_phd_council_decisions' => !empty($existingFilesMap['file_phd_council_decisions']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_cv'                    => !empty($existingFilesMap['file_cv']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
                'file_fees_receipt'          => !empty($existingFilesMap['file_fees_receipt']) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',

                // اختيارية
                'file_library_receipt'       => 'nullable|file|mimes:pdf|max:2048',
                'file_phd_transcript'        => 'nullable|file|mimes:pdf|max:2048',
                'file_icdl_cert'             => 'nullable|file|mimes:pdf|max:2048',
                'file_english_cert'          => 'nullable|file|mimes:pdf|max:2048',
                'file_other_attachments'     => 'nullable|file|mimes:pdf|max:2048',

                // مشروطة بعدم الإعفاء من شرط الإقامة
                'file_passport'              => ($requiresResidence && empty($existingFilesMap['file_passport'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
                'file_immigration_movement'  => ($requiresResidence && empty($existingFilesMap['file_immigration_movement'])) ? 'required|file|mimes:pdf|max:2048' : 'nullable|file|mimes:pdf|max:2048',
            ];

            $messages = [
                'full_name.required'              => 'يرجى إدخال الاسم والكنية للمرشح.',
                'national_id.required'            => 'يرجى إدخال الرقم الوطني أو رقم جواز السفر.',
                'national_id.regex'               => 'يجب أن يتكون الرقم الوطني من 11 خانة رقمية حصراً دون أي أحرف أو مسافات.',
                'req_no.required'                 => 'يرجى إدخال رقم كتاب ترشيح الجامعة الخاصة.',
                'req_no.numeric'                  => 'رقم كتاب ترشيح الجامعة يجب أن يكون رقماً فقط.',
                'req_date.before_or_equal'        => 'تاريخ كتاب الجامعة لا يمكن أن يكون تاريخاً مستقبلياً.',

                'hs_grant_date.required'          => 'يرجى إدخال سنة الحصول على الشهادة الثانوية.',
                'hs_grant_date.max'               => 'سنة الحصول على الثانوية لا يمكن أن تتجاوز العام الحالي.',
                'ba_university_id.required'       => 'يرجى اختيار الجامعة المانحة للإجازة الجامعية.',
                'ba_university_other.required'    => 'يرجى إدخال اسم الجامعة المانحة للإجازة الجامعية.',
                'ba_grant_date.after'             => 'تاريخ منح الإجازة الجامعية يجب أن يكون لاحقاً لسنة الحصول على الثانوية العامة.',
                'ba_grant_date.before_or_equal'   => 'تاريخ منح الإجازة الجامعية لا يمكن أن يكون في المستقبل.',
                'ma_university_id.required'       => 'يرجى اختيار الجامعة المانحة لدرجة الماجستير.',
                'ma_university_other.required'    => 'يرجى إدخال اسم الجامعة المانحة لدرجة الماجستير.',
                'ma_grant_date.after'             => 'تاريخ منح درجة الماجستير يجب أن يكون لاحقاً لتاريخ الحصول على الإجازة الجامعية الأولى.',
                'ma_grant_date.before_or_equal'   => 'تاريخ منح درجة الماجستير لا يمكن أن يكون في المستقبل.',

                'phd_registration_date.after'     => 'تاريخ التسجيل بالدكتوراه يجب أن يكون لاحقاً لتاريخ الحصول على درجة الماجستير.',
                'phd_defense_date.after'          => 'تاريخ مناقشة الأطروحة يجب أن يكون بعد تاريخ التسجيل بالدرجة.',
                'phd_grant_date.after_or_equal'   => 'تاريخ منح الدكتوراه يجب أن يكون في نفس يوم المناقشة أو بعده، ولا يمكن أن يسبق المناقشة.',
                'phd_grant_date.before_or_equal'  => 'تاريخ منح الدكتوراه لا يمكن أن يكون تاريخاً مستقبلياً.',

                'hs_decision_date.after_or_equal' => 'تاريخ قرار معادلة الثانوية يجب أن يكون بعد سنة الحصول على الشهادة الثانوية.',
                'ma_decision_no.required'         => 'يرجى إدخال رقم قرار معادلة شهادة الماجستير.',
                'ma_decision_date.required'       => 'يرجى إدخال تاريخ قرار معادلة شهادة الماجستير.',
                'ma_decision_date.after_or_equal' => 'تاريخ قرار معادلة الماجستير يجب أن يكون بعد تاريخ منح شهادة الماجستير.',
                'residences.required'             => 'يرجى إضافة حركة إقامة واحدة على الأقل، أو تفعيل خيار الإعفاء من شرط الإقامة للتخصص النظري.',
                'residences.*.exit_date.after_or_equal' => 'تاريخ المغادرة لحركة الإقامة يجب أن يكون مساوياً لتاريخ الدخول أو بعده.',

                'file_national_id.required'           => 'يرجى إرفاق صورة عن الهوية الشخصية أو جواز السفر كمتطلب إلزامي.',
                'file_secondary_cert.required'        => 'يرجى إرفاق صورة عن شهادة البكالوريا.',
                'file_hs_decision.required'           => 'يرجى إرفاق قرار معادلة درجة البكالوريا من وزارة التربية.',
                'file_bachelor_cert.required'         => 'يرجى إرفاق الإجازة الجامعية مصدقة أصولاً.',
                'file_master_cert.required'           => 'يرجى إرفاق شهادة الماجستير مصدقة أصولاً.',
                'file_ma_decision.required'           => 'يرجى إرفاق قرار معادلة شهادة الماجستير.',
                'file_phd_cert.required'              => 'يرجى إرفاق شهادة الدكتوراه مصدقة ومترجمة أصولاً.',
                'file_phd_council_decisions.required' => 'يرجى إرفاق وثيقة قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه.',
                'file_cv.required'                    => 'يرجى إرفاق السيرة الذاتية للمرشح.',
                'file_fees_receipt.required'          => 'يرجى إرفاق إيصال الدفع 125 ألف ل.س.',
                'file_passport.required'              => 'يرجى إرفاق جواز السفر مع صفحات الأختام.',
                'file_immigration_movement.required'  => 'يرجى إرفاق وثيقة حركة الهجرة والجوازات.',
                'max'                                 => 'حجم الملف المرفق يجب ألا يتجاوز 2 ميغابايت.',
            ];
        }

        $request->validate($rules, $messages);

        // =========================================================================
        // 3. معالجة وحفظ الملف الشخصي للمرشح (حل جذري لـ UNIQUE constraint failed)
        // =========================================================================
        $fullName = $request->filled('full_name') ? trim($request->full_name) : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة دكتوراه غير سورية');
        $email = $request->filled('email')
            ? trim($request->email)
            : ($isExisting && $existingApp->candidate ? ($existingApp->candidate->email ?? '') : ($isAdminEdit ? '' : (Auth::user()->email ?? '')));
        $cleanNatId = $request->filled('national_id') ? trim($request->national_id) : null;

        // البحث عما إذا كان الرقم الوطني مسجلاً مسبقاً لدى أي مرشح آخر
        $matchedProfile = null;
        if ($cleanNatId) {
            $matchedProfile = EquivalenceProfile::where('national_id', $cleanNatId)->first();
        }

        if ($matchedProfile) {
            // أ. الرقم الوطني مسجل مسبقاً -> اعتماد البروفايل الأصلي وتحديث بياناته
            $candidate = $matchedProfile;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);

            // إذا كانت المسودة مرتبطة ببروفايل مؤقت مختلف، نربطها بالأصلي ونحذف المؤقت
            if ($existingApp && $existingApp->candidate_id && $existingApp->candidate_id != $candidate->id) {
                $oldCandidateId = $existingApp->candidate_id;
                $existingApp->update(['candidate_id' => $candidate->id]);

                $oldCandidate = EquivalenceProfile::find($oldCandidateId);
                if ($oldCandidate && str_starts_with($oldCandidate->national_id ?? '', 'TMP-')) {
                    $oldCandidate->delete();
                }
            }
        } elseif ($isExisting && $existingApp->candidate) {
            // ب. تحديث البروفايل الحالي للمسودة لعدم وجود تعارض في الرقم الوطني
            $candidate = $existingApp->candidate;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'national_id'    => $cleanNatId ?: $candidate->national_id,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? $syriaId),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);
        } else {
            // ج. إنشاء بروفايل جديد كلياً
            $natIdToSave = $cleanNatId ?: ('TMP-' . time() . '-' . rand(100, 999));
            $candidate = EquivalenceProfile::create([
                'national_id'    => $natIdToSave,
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? '',
                'mother_name'    => $request->mother_name ?? '',
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : $syriaId,
                'dob'            => $request->filled('dob') ? $request->dob : null,
                'job_title'      => 'مرشح تعادل دكتوراه خارجية',
                'mobile'         => $request->mobile ?? '',
                'email'          => $email,
                'address'        => $request->address ?? '',
                'gender'         => $request->gender ?? 'ذكر',
                'is_syrian'      => ($request->nationality_id == $syriaId),
            ]);
        }

        // =========================================================================
        // 4. حفظ أو تحديث الطلب (Application)
        // =========================================================================
        $appNo = $existingApp ? $existingApp->application_no : ('PHD-EXT-' . date('Y') . '-' . rand(1000, 9999));
        $requestType = 'دكتورة خارجية';
        $status = $isAdminEdit
            ? ($existingApp->getRawOriginal('status') ?: $existingApp->status)
            : ($isDraft ? 'مسودة' : 'تحت التدقيق الأولي');

        $appData = [
            'candidate_id'         => $candidate->id,
            'work_university_id'   => $uniId,
            'work_faculty'         => $request->work_faculty,
            'work_department'      => $request->work_department,
            'new_uni_request_no'   => $request->req_no,
            'new_uni_request_date' => $request->req_date,
            'study_system'         => $request->phd_study_system ?? 'أطروحة بحثية',
            'is_residence_exempt'  => $isResidenceExempt,
            'status'               => $status,
            'request_type'         => $requestType,
            'user_id'              => $existingApp ? $existingApp->user_id : Auth::id(),
        ];

        if ($existingApp) {
            $existingApp->update($appData);
            $application = $existingApp;
        } else {
            $appData['application_no'] = $appNo;
            $application = Application::create($appData);
        }

        // =========================================================================
        // 5. حفظ المراحل التعليمية الأربعة (Educations)
        // =========================================================================

        // 1. الثانوية العامة (Level 4)
        $hsCountryId = ($request->filled('hs_country_id') && is_numeric($request->hs_country_id)) ? (int) $request->hs_country_id : $syriaId;
        $isHsForeignSave = ($hsCountryId != $syriaId);
        $hsDecNotes = [];
        if ($isHsForeignSave) {
            if ($request->filled('hs_decision_no')) $hsDecNotes[] = 'رقم قرار معادلة الثانوية: ' . $request->hs_decision_no;
            if ($request->filled('hs_decision_date')) $hsDecNotes[] = 'تاريخ القرار: ' . $request->hs_decision_date;
        }

        $highSchoolEd = Education::updateOrCreate(
            ['application_id' => $application->id, 'education_level_id' => 4],
            [
                'country_id'                => $hsCountryId,
                'section_name'              => $request->hs_type ?? 'علمي',
                'specialization'            => $request->hs_type ?? 'علمي',
                'graduation_year'           => $request->hs_grant_date,
                'grant_date'                => $request->filled('hs_grant_date') ? ($request->hs_grant_date . '-06-30') : null,
                'equivalence_decision_no'   => $isHsForeignSave ? $request->hs_decision_no : null,
                'equivalence_decision_date' => $isHsForeignSave ? $request->hs_decision_date : null,
                'notes'                     => !empty($hsDecNotes) ? implode(' | ', $hsDecNotes) : null,
            ]
        );

        // 2. الإجازة الجامعية الأولى (Level 1) — بدون قرار معادلة نهائياً + اختيار جامعة سورية من القائمة المنسدلة
        $baCountryId = ($request->filled('ba_country_id') && is_numeric($request->ba_country_id)) ? (int) $request->ba_country_id : $syriaId;
        $baUniId = null;
        $baUniOther = null;
        if ($baCountryId == $syriaId && $request->input('ba_university_id') !== 'other' && is_numeric($request->ba_university_id)) {
            $baUniId = (int) $request->ba_university_id;
        } elseif ($request->filled('ba_university_other')) {
            $baUniName = trim($request->ba_university_other);
            $createdBaUni = \App\Models\LookupUniversity::firstOrCreate([
                'name' => $baUniName,
            ], [
                'country_id' => $baCountryId,
            ]);
            $baUniId = $createdBaUni->id;
            $baUniOther = $baUniName;
        }

        $bachelorEd = Education::updateOrCreate(
            ['application_id' => $application->id, 'education_level_id' => 1],
            [
                'country_id'                => $baCountryId,
                'university_id'             => $baUniId,
                'university_other'          => $baUniOther,
                'university_name_manual'    => $baUniOther,
                'faculty'                   => $request->ba_faculty,
                'department'                => $request->ba_department,
                'general_specialization'    => $request->ba_general_specialization,
                'exact_specialization'      => null,
                'specialization'            => null,
                'grant_date'                => $request->ba_grant_date,
                'graduation_year'           => $request->ba_grant_date ? date('Y', strtotime($request->ba_grant_date)) : null,
                'rank'                      => $request->ba_rank ?? 'جيد جداً',
                'equivalence_decision_no'   => null,
                'equivalence_decision_date' => null,
                'notes'                     => null,
            ]
        );

        // 3. درجة الماجستير (Level 2) — تاريخ الحصول على الدرجة فقط + checkbox معادلة اختياري + اختيار جامعة سورية من القائمة
        $maCountryId = ($request->filled('ma_country_id') && is_numeric($request->ma_country_id)) ? (int) $request->ma_country_id : $syriaId;
        $maUniId = null;
        $maUniOther = null;
        if ($maCountryId == $syriaId && $request->input('ma_university_id') !== 'other' && is_numeric($request->ma_university_id)) {
            $maUniId = (int) $request->ma_university_id;
        } elseif ($request->filled('ma_university_other')) {
            $maUniName = trim($request->ma_university_other);
            $createdMaUni = \App\Models\LookupUniversity::firstOrCreate([
                'name' => $maUniName,
            ], [
                'country_id' => $maCountryId,
            ]);
            $maUniId = $createdMaUni->id;
            $maUniOther = $maUniName;
        }

        $maDecNotes = [];
        if ($hasMaEquivalence) {
            if ($request->filled('ma_decision_no')) $maDecNotes[] = 'رقم قرار معادلة الماجستير: ' . trim($request->ma_decision_no);
            if ($request->filled('ma_decision_date')) $maDecNotes[] = 'تاريخ القرار: ' . $request->ma_decision_date;
        }

        $masterEd = Education::updateOrCreate(
            ['application_id' => $application->id, 'education_level_id' => 2],
            [
                'country_id'                => $maCountryId,
                'university_id'             => $maUniId,
                'university_other'          => $maUniOther,
                'university_name_manual'    => $maUniOther,
                'faculty'                   => $request->ma_faculty,
                'department'                => $request->ma_department,
                'general_specialization'    => $request->ma_general_specialization,
                'exact_specialization'      => $request->ma_specialization,
                'specialization'            => $request->ma_specialization,
                'registration_date'         => null,
                'defense_date'              => null,
                'grant_date'                => $request->ma_grant_date,
                'graduation_year'           => $request->ma_grant_date ? date('Y', strtotime($request->ma_grant_date)) : null,
                'rank'                      => $request->ma_rank ?? 'جيد جداً',
                'equivalence_decision_no'   => $hasMaEquivalence ? $request->ma_decision_no : null,
                'equivalence_decision_date' => $hasMaEquivalence ? $request->ma_decision_date : null,
                'notes'                     => !empty($maDecNotes) ? implode(' | ', $maDecNotes) : null,
            ]
        );

        // 4. الدكتوراه الخارجية (Level 3)
        $phdCountryId = ($request->filled('phd_country_id') && is_numeric($request->phd_country_id)) ? $request->phd_country_id : null;
        $phdUniId = null;
        if ($request->filled('phd_university_other')) {
            $createdPhdUni = \App\Models\LookupUniversity::firstOrCreate([
                'name' => trim($request->phd_university_other),
            ], [
                'country_id' => $phdCountryId,
            ]);
            $phdUniId = $createdPhdUni->id;
        }

        $phdEd = Education::updateOrCreate(
            ['application_id' => $application->id, 'education_level_id' => 3],
            [
                'country_id'                => $phdCountryId,
                'university_id'             => $phdUniId,
                'university_other'          => $request->phd_university_other,
                'university_name_manual'    => $request->phd_university_other,
                'faculty'                   => $request->phd_faculty,
                'department'                => $request->phd_department,
                'general_specialization'    => $request->phd_general_specialization ?: $request->phd_department,
                'exact_specialization'      => $request->phd_specialization,
                'specialization'            => $request->phd_specialization,
                'rank'                      => $request->phd_rank ?? 'شرف / امتياز',
                'thesis_title'              => $request->phd_thesis_title,
                'supervisor'                => $request->phd_supervisor,
                'supervisor_name'           => $request->phd_supervisor,
                'study_system'              => $request->phd_study_system,
                'study_language'            => $request->phd_study_language,
                'registration_date'         => $request->phd_registration_date,
                'defense_date'              => $request->phd_defense_date,
                'grant_date'                => $request->phd_grant_date,
                'graduation_year'           => $request->phd_grant_date ? date('Y', strtotime($request->phd_grant_date)) : null,
            ]
        );

        // =========================================================================
        // 6. حركات الإقامة والسفر (Residences)
        // =========================================================================
        if ($request->boolean('residences_submitted_flag') || ($request->has('residences') && is_array($request->residences)) || !$isDraft) {
            EducationResidence::where('education_id', $phdEd->id)->delete();
            if (!$isResidenceExempt && $request->has('residences') && is_array($request->residences)) {
                foreach ($request->residences as $resData) {
                    if (!empty($resData['entry_date']) && !empty($resData['exit_date'])) {
                        $stampDetails = $resData['stamp_details'] ?? null;
                        if (empty($stampDetails)) {
                            $diff = \Carbon\Carbon::parse($resData['entry_date'])->diff(\Carbon\Carbon::parse($resData['exit_date']));
                            $stampDetails = "{$diff->y} سنة و {$diff->m} شهر و {$diff->d} يوم";
                        }
                        EducationResidence::create([
                            'education_id'  => $phdEd->id,
                            'page_number'   => $resData['page_number'] ?? null,
                            'entry_airport' => $resData['entry_airport'] ?? null,
                            'entry_date'    => $resData['entry_date'],
                            'exit_airport'  => $resData['exit_airport'] ?? null,
                            'exit_date'     => $resData['exit_date'],
                            'country_id'    => $phdCountryId,
                            'stamp_details' => $stampDetails,
                        ]);
                    }
                }
            }
        }

        // =========================================================================
        // 7. رفع وحفظ المرفقات وفق جدول lookup_attachment_types (16 بنداً + قرار معادلة الماجستير الشرطي)
        // =========================================================================
        $fileInputs = [
            'file_national_id'           => ['id' => 13, 'notes' => 'صورة عن الهوية الشخصية / إثبات الشخصية', 'ed_id' => $phdEd->id],
            'file_secondary_cert'        => ['id' => 1,  'notes' => 'نسخة مصدقة عن شهادة البكالوريا', 'ed_id' => $highSchoolEd->id],
            'file_hs_decision'           => ['id' => 2,  'notes' => 'قرار معادلة درجة البكالوريا من وزارة التربية', 'ed_id' => $highSchoolEd->id],
            'file_bachelor_cert'         => ['id' => 3,  'notes' => 'الإجازة الجامعية مصدقة أصولاً', 'ed_id' => $bachelorEd->id],
            'file_master_cert'           => ['id' => 6,  'notes' => 'شهادة الماجستير مصدقة أصولاً', 'ed_id' => $masterEd->id],
            'file_ma_decision'           => ['id' => 9,  'notes' => 'قرار معادلة شهادة الماجستير', 'ed_id' => $masterEd->id],
            'file_phd_cert'              => ['id' => 10, 'notes' => 'شهادة الدكتوراه مصدقة ومترجمة أصولاً', 'ed_id' => $phdEd->id],
            'file_phd_council_decisions' => ['id' => 11, 'notes' => 'قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه', 'ed_id' => $phdEd->id],
            'file_cv'                    => ['id' => 14, 'notes' => 'السيرة الذاتية للمرشح', 'ed_id' => $phdEd->id],
            'file_fees_receipt'          => ['id' => 17, 'notes' => 'إيصال الدفع 125 ألف (رسم تعادل الدكتوراه 125,000 ل.س)', 'ed_id' => $phdEd->id],
            'file_library_receipt'       => ['id' => 22, 'notes' => 'إيصال المكتبة الوطنية', 'ed_id' => $phdEd->id],
            'file_phd_transcript'        => ['id' => 5,  'notes' => 'كشف علامات الدكتوراه', 'ed_id' => $phdEd->id],
            'file_icdl_cert'             => ['id' => 19, 'notes' => 'شهادة قيادة الحاسوب (ICDL)', 'ed_id' => $phdEd->id],
            'file_english_cert'          => ['id' => 18, 'notes' => 'شهادة اللغة الإنكليزية', 'ed_id' => $phdEd->id],
            'file_passport'              => ['id' => 13, 'notes' => 'صورة جواز السفر مع صفحات الأختام والإقامة', 'ed_id' => $phdEd->id],
            'file_immigration_movement'  => ['id' => 24, 'notes' => 'وثيقة حركة الهجرة والجوازات', 'ed_id' => $phdEd->id],
            'file_other_attachments'     => ['id' => 23, 'notes' => 'مرفقات ووثائق أخرى', 'ed_id' => $phdEd->id],
        ];

        foreach ($fileInputs as $inputKey => $meta) {
            $typeId = $meta['id'];
            $note = $meta['notes'];
            $targetEdId = $meta['ed_id'] ?? $phdEd->id;

            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $cleanCandidate = trim(preg_replace('/\s+/', '_', preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name)));
                $keySuffix = in_array($typeId, [5, 13], true) ? ('_' . $inputKey) : '';
                $filename = 'PHD_EXT_' . $appNo . '_Type' . $typeId . $keySuffix . '_' . ($cleanCandidate ?: 'Candidate') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');

                $matchCriteria = [
                    'education_id'       => $targetEdId,
                    'attachment_type_id' => $typeId,
                ];
                if (in_array($typeId, [5, 13], true)) {
                    $matchCriteria['notes'] = $note;
                }

                EducationAttachment::updateOrCreate(
                    $matchCriteria,
                    [
                        'file_path' => $path,
                        'notes'     => $note,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                    ]
                );
            }
        }

        // =========================================================================
        // 8. الرد وإعادة التوجيه
        // =========================================================================
        if ($isAdminEdit) {
            return $this->redirectAfterAdminWizardUpdate($application);
        }

        if ($isDraft) {
            return redirect()->route('university.drafts.index')
                ->with('success', '💾 تم حفظ مسودة معاملة الدكتوراه غير السورية بنجاح برقم: ' . $appNo . '. يمكنك العودة لتعديلها واستكمالها في أي وقت.');
        }

        $application->notifyUniversityOfStatusChange('تحت التدقيق الأولي');

        return redirect()->route('university.dashboard')
            ->with('success', ($isExisting ? 'تم إعادة تعديل وحفظ بيانات ومرفقات معاملة الدكتوراه الخارجية رقم: ' : 'تم تقديم معاملة تعادل الدكتوراه غير السورية بنجاح للطلب رقم: ') . $appNo)
            ->with('submitted_app_id', $application->id)
            ->with('submitted_app_no', $appNo);
    }

    private function prepareFacultyPermissionWizardData(Request $request, ?Application $adminDraft = null): array
    {
        $isAdminEdit = ($adminDraft !== null);
        $countries = LookupCountry::orderByRaw("CASE WHEN name = 'سوريا' THEN 0 ELSE 1 END, name ASC")->get();
        $universities = LookupUniversity::all();
        $govUniversities = LookupUniversity::whereIn('name', [
            'جامعة دمشق',
            'جامعة حلب',
            'جامعة تشرين',
            'جامعة البعث',
            'جامعة الفرات',
            'جامعة حماة',
            'جامعة طرطوس',
            'جامعة إدلب',
        ])->get();

        if ($govUniversities->isEmpty()) {
            $govUniversities = $universities;
        }

        $unreadNotifications = $isAdminEdit ? collect() : $this->getUnreadNotifications();
        $draft = $adminDraft;
        $phdEdu = null;
        $govEdu = null;
        if (!$isAdminEdit && $request->has('draft_id')) {
            $draft = Application::where('id', $request->draft_id)
                ->where('work_university_id', Auth::user()->university_id)
                ->with(['candidate', 'educations.country', 'educations.university', 'educations.level', 'educations.attachments.attachmentType'])
                ->first();
        }

        if ($draft) {
            $phdEdu = $draft->educations->first(function($e) {
                return $e->thesis_title === 'شهادة الدكتوراه' || ($e->education_level_id == 3 && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية'));
            }) ?? $draft->educations->where('education_level_id', 3)->first() ?? $draft->educations->first();

            $govEdu = $draft->educations->first(function($e) {
                return str_contains($e->thesis_title ?? '', 'هيئة تدريسية') || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
            });
        }

        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : 1;
        $adminSubmitRoute = $isAdminEdit ? route('admin.applications.wizard_update', $draft->id) : null;
        $adminWizardTrack = $isAdminEdit ? 'faculty_permission' : null;

        return compact(
            'countries',
            'universities',
            'govUniversities',
            'syriaId',
            'unreadNotifications',
            'draft',
            'phdEdu',
            'govEdu',
            'isAdminEdit',
            'adminSubmitRoute',
            'adminWizardTrack'
        );
    }

    public function showFacultyPermissionWizard(Request $request)
    {
        if (\App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        return view('university.apply.faculty_permission', $this->prepareFacultyPermissionWizardData($request));
    }

    public function submitFacultyPermissionWizard(Request $request)
    {
        $isAdminEdit = (bool) $request->attributes->get('admin_wizard_edit', false);
        if (!$isAdminEdit && \App\Models\SiteSetting::get('site_locked', '0') === '1') {
            $notice = \App\Models\SiteSetting::get('site_notice', 'تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار من مجلس التعليم العالي.');
            return redirect()->route('university.dashboard')->with('error', '🔒 عذراً! ' . $notice . ' (يمكنك تصفح البيانات والمعاملات والمراسلة فقط).');
        }

        $existingApp = null;
        if ($isAdminEdit) {
            $adminAppId = (int) $request->attributes->get('admin_target_app_id');
            $existingApp = Application::with(['educations.attachments', 'educations.level', 'educations.university', 'educations.country', 'candidate'])
                ->findOrFail($adminAppId);
            $uniId = $request->filled('work_university_id') ? (int) $request->input('work_university_id') : $existingApp->work_university_id;
        } else {
            $uniId = Auth::user()->university_id;
            if ($request->filled('draft_id')) {
                $existingApp = Application::where('id', $request->draft_id)
                    ->where('work_university_id', $uniId)
                    ->with(['educations.attachments', 'educations.level', 'educations.university', 'educations.country', 'candidate'])
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
                        ->with(['educations.attachments', 'educations.level', 'educations.university', 'educations.country', 'candidate'])
                        ->latest('id')
                        ->first();
                }
            }
        }

        $isExisting = ($existingApp !== null);

        // Fallback لاسترجاع بيانات الجامعات والشهادات المحفوظة مسبقاً إذا لم يتم إرسالها بالطلب
        $existingPhdEdu = null;
        $existingGovEd = null;
        if ($existingApp) {
            $existingPhdEdu = $existingApp->educations->first(function($e) {
                return $e->thesis_title === 'شهادة الدكتوراه' || ($e->education_level_id == 3 && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية'));
            }) ?? $existingApp->educations->where('education_level_id', 3)->first() ?? $existingApp->educations->first();

            $existingGovEd = $existingApp->educations->first(function($e) {
                return str_contains($e->thesis_title ?? '', 'هيئة تدريسية') || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
            });
        }

        if ($existingPhdEdu && !$request->filled('phd_university_id') && !$request->filled('phd_university_other')) {
            $request->merge([
                'phd_university_id'    => $existingPhdEdu->university_id ?: ($existingPhdEdu->university_other ? 'other' : null),
                'phd_university_other' => $existingPhdEdu->university_other,
            ]);
        }

        if ($existingGovEd && !$request->filled('gov_university_id') && !$request->filled('gov_university_other')) {
            $request->merge([
                'gov_university_id'    => $existingGovEd->university_id ?: ($existingGovEd->university_other ? 'other' : null),
                'gov_university_other' => $existingGovEd->university_other,
            ]);
        }

        // Check which files already exist in DB
        $existingFilesMap = [];
        if ($existingApp) {
            foreach ($existingApp->educations as $ed) {
                foreach ($ed->attachments as $att) {
                    if ($att->attachment_type_id == 23 || ($att->notes && (str_contains($att->notes, 'أخرى') || str_contains($att->notes, 'داعمة')))) {
                        $existingFilesMap['file_other_attachments'] = true;
                    } elseif ($att->attachment_type_id == 7 || ($att->notes && (str_contains($att->notes, 'طلب تقويم') || str_contains($att->notes, 'كتاب ترشيح') || str_contains($att->notes, 'كتاب الجامعة')))) {
                        $existingFilesMap['file_uni_request'] = true;
                    } elseif ($att->attachment_type_id == 8 || ($att->notes && (str_contains($att->notes, 'شهادة الدكتوراه') || str_contains($att->notes, 'مصدقة الدكتوراه')))) {
                        $existingFilesMap['file_phd_cert'] = true;
                    } elseif ($att->attachment_type_id == 9 || ($att->notes && (str_contains($att->notes, 'بيان وضع') || str_contains($att->notes, 'بطاقة ذاتية')))) {
                        $existingFilesMap['file_service_statement'] = true;
                    } elseif ($att->attachment_type_id == 10 || ($att->notes && (str_contains($att->notes, 'إيصال') || str_contains($att->notes, '125,000') || str_contains($att->notes, 'رسم تعادل')))) {
                        $existingFilesMap['file_payment'] = true;
                    } elseif ($att->attachment_type_id == 11 || $att->attachment_type_id == 13 || ($att->notes && (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'الهوية الشخصية') || str_contains($att->notes, 'إثبات الشخصية')))) {
                        $existingFilesMap['file_id_card'] = true;
                        $existingFilesMap['file_national_id'] = true;
                    } elseif ($att->attachment_type_id == 12 || ($att->notes && (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'مصدقة الماجستير')))) {
                        $existingFilesMap['file_ma_cert'] = true;
                    }
                }
            }
        }

        $wasAwaitingDocs = !$isAdminEdit && ($existingApp && (
            $existingApp->status_id == 3 || 
            in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة', 3])
        ));

        if (!$isAdminEdit && $wasAwaitingDocs && ($request->input('action') === 'save_draft' || $request->boolean('is_draft') || $request->input('is_draft') == '1')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ هذا الطلب كمسودة لأن المعاملة قيد المعالجة (بانتظار استكمال الوثائق). يرجى استكمال البيانات والمرفقات ثم النقر على زر الإرسال.');
        }

        $isDraft = !$isAdminEdit && !$wasAwaitingDocs && ($request->input('action') === 'save_draft');
        $syriaCountry = LookupCountry::where('name', 'سوريا')->first();
        $syriaId = $syriaCountry ? $syriaCountry->id : null;

        if ($isDraft || $isAdminEdit) {
            $rules = [
                'full_name' => $isAdminEdit ? 'required|string|max:255' : 'nullable|string|max:255',
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
                'file_id_card' => (!empty($existingFilesMap['file_id_card']) || !empty($existingFilesMap['file_national_id'])) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',
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
                'gov_university_id.required_without' => 'يرجى اختيار الجامعة الحكومية التي ينتمي إليها عضو الهيئة التدريسية في الخطوة الثانية.',
                'gov_university_id.required' => 'يرجى اختيار الجامعة الحكومية التي ينتمي إليها عضو الهيئة التدريسية في الخطوة الثانية.',
                'gov_university_other.required_if' => 'يرجى كتابة اسم الجامعة الحكومية في الخطوة الثانية عند اختيار (أخرى).',
                'gov_faculty.required' => 'يرجى إدخال الكلية في الجامعة الحكومية.',
                'gov_department.required' => 'يرجى إدخال القسم في الجامعة الحكومية.',
                'academic_rank.required' => 'يرجى إدخال الرتبة الأكاديمية (مدرس / أستاذ مساعد / أستاذ).',
                'phd_university_id.required' => 'يرجى اختيار الجامعة المانحة لشهادة الدكتوراه في الخطوة الثالثة (الشهادات العلمية).',
                'phd_university_id.exists' => 'الجامعة المانحة لشهادة الدكتوراه المختارة غير موجودة بالقائمة.',
                'phd_university_other.required' => 'يرجى كتابة اسم الجامعة المانحة لشهادة الدكتوراه في الخطوة الثالثة عند اختيار (جامعة أخرى).',
                'phd_specialization.required' => 'يرجى إدخال الاختصاص الدقيق لشهادة الدكتوراه.',
                'phd_grant_date.required' => 'يرجى إدخال سنة/تاريخ منح درجة الدكتوراه.',
                'phd_grant_date.before_or_equal' => 'تاريخ منح درجة الدكتوراه يجب أن يكون تاريخاً سابقاً أو مساوياً لليوم.',
                'file_id_card.required' => 'يرجى إرفاق صورة الهوية الشخصية أو إثبات الشخصية للمرشح كمتطلب إلزامي.',
                'file_uni_request.required' => 'يرجى إرفاق طلب التقويم / كتاب ترشيح الجامعة الخاصة.',
                'file_service_statement.required' => 'يرجى إرفاق بيان الوضع أو البطاقة الذاتية من الجامعة الحكومية.',
                'file_phd_cert.required' => 'يرجى إرفاق نسخة مصدقة أصولاً عن شهادة الدكتوراه.',
                'file_payment.required' => 'يرجى إرفاق إيصال تسديد رسم السماح بالتدريس (125,000 ل.س).',
                'max' => 'حجم الملف المرفق يجب ألا يتجاوز 2 ميغابايت.',
            ];
        }

        // Fallback مباشر قبل الـ Validation لاسترجاع بيانات الجامعات المحفوظة مسبقاً إذا لم يرسلها المستخدم في الفورم
        if ($existingApp) {
            $existingPhd = $existingApp->educations->first(function($e) {
                return $e->thesis_title === 'شهادة الدكتوراه' || ($e->education_level_id == 3 && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية'));
            }) ?? $existingApp->educations->where('education_level_id', 3)->first() ?? $existingApp->educations->first();

            if ($existingPhd && !$request->filled('phd_university_id') && !$request->filled('phd_university_other')) {
                $request->merge([
                    'phd_university_id'    => $existingPhd->university_id ?: ($existingPhd->university_other ? 'other' : null),
                    'phd_university_other' => $existingPhd->university_other,
                ]);
            } elseif ($existingPhd && $request->input('phd_university_id') === 'other' && !$request->filled('phd_university_other') && $existingPhd->university_other) {
                $request->merge([
                    'phd_university_other' => $existingPhd->university_other,
                ]);
            }

            if ($existingGovEd && !$request->filled('gov_university_id') && !$request->filled('gov_university_other')) {
                $request->merge([
                    'gov_university_id'    => $existingGovEd->university_id ?: ($existingGovEd->university_other ? 'other' : null),
                    'gov_university_other' => $existingGovEd->university_other,
                ]);
            } elseif ($existingGovEd && $request->input('gov_university_id') === 'other' && !$request->filled('gov_university_other') && $existingGovEd->university_other) {
                $request->merge([
                    'gov_university_other' => $existingGovEd->university_other,
                ]);
            }
        }

        // ضبط قواعد التحقق الدقيقة لجامعة الدكتوراه والجامعة الحكومية
        if (!$isDraft && !$isAdminEdit) {
            $rules['phd_university_id'] = 'required';
            if ($request->input('phd_university_id') === 'other') {
                $rules['phd_university_other'] = 'required|string|max:255';
            } else {
                $rules['phd_university_id'] = 'required|exists:lookup_universities,id';
                $rules['phd_university_other'] = 'nullable';
            }

            $rules['gov_university_id'] = 'required';
            if ($request->input('gov_university_id') === 'other') {
                $rules['gov_university_other'] = 'required|string|max:255';
            } else {
                $rules['gov_university_id'] = 'required|exists:lookup_universities,id';
                $rules['gov_university_other'] = 'nullable';
            }
        }

        $request->validate($rules, $messages);

        // 2. Profile Creation or Update (حل جذري لمنع خطأ تعارض الرقم الوطني UNIQUE constraint failed)
        $fullName = $request->filled('full_name') ? trim($request->full_name) : ($isExisting && $existingApp->candidate ? $existingApp->candidate->full_name : 'مسودة طلب سماح بالتدريس');
        $cleanNatId = $request->filled('national_id') ? trim($request->national_id) : null;

        $matchedProfile = null;
        if ($cleanNatId) {
            $matchedProfile = EquivalenceProfile::where('national_id', $cleanNatId)->first();
        }

        if ($matchedProfile) {
            $candidate = $matchedProfile;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => $request->academic_rank ?? ($candidate->job_title ?? 'عضو هيئة تدريسية'),
                'phone'          => $request->phone ?? $candidate->phone,
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $request->email ?? $candidate->email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => true,
            ]);

            if ($existingApp && $existingApp->candidate_id && $existingApp->candidate_id != $candidate->id) {
                $oldCandidateId = $existingApp->candidate_id;
                $existingApp->update(['candidate_id' => $candidate->id]);

                $oldCandidate = EquivalenceProfile::find($oldCandidateId);
                if ($oldCandidate && str_starts_with($oldCandidate->national_id ?? '', 'TMP-')) {
                    $oldCandidate->delete();
                }
            }
        } elseif ($isExisting && $existingApp->candidate) {
            $candidate = $existingApp->candidate;
            $candidate->update([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? $candidate->father_name,
                'mother_name'    => $request->mother_name ?? $candidate->mother_name,
                'national_id'    => $cleanNatId ?: $candidate->national_id,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($candidate->nationality_id ?? ($syriaId ?? 1)),
                'dob'            => $request->filled('dob') ? $request->dob : $candidate->dob,
                'job_title'      => $request->academic_rank ?? ($candidate->job_title ?? 'عضو هيئة تدريسية'),
                'phone'          => $request->phone ?? $candidate->phone,
                'mobile'         => $request->mobile ?? $candidate->mobile,
                'email'          => $request->email ?? $candidate->email,
                'address'        => $request->address ?? $candidate->address,
                'gender'         => $request->gender ?? ($candidate->gender ?? 'ذكر'),
                'is_syrian'      => true,
            ]);
        } else {
            $natIdToSave = $cleanNatId ?: ('TMP-' . time() . '-' . rand(100, 999));
            $candidate = EquivalenceProfile::create([
                'full_name'      => $fullName,
                'father_name'    => $request->father_name ?? '',
                'mother_name'    => $request->mother_name ?? '',
                'national_id'    => $natIdToSave,
                'nationality_id' => ($request->filled('nationality_id') && is_numeric($request->nationality_id)) ? $request->nationality_id : ($syriaId ?? 1),
                'dob'            => $request->filled('dob') ? $request->dob : null,
                'job_title'      => $request->academic_rank ?? 'عضو هيئة تدريسية',
                'phone'          => $request->phone,
                'mobile'         => $request->mobile ?? '',
                'email'          => $request->email ?? '',
                'address'        => $request->address ?? '',
                'gender'         => $request->gender ?? 'ذكر',
                'is_syrian'      => true,
            ]);
        }

        // 3. Application Creation / Update
        $requestType = 'عضو هيئة تدريسية - سماح';
        $appNo = $isExisting ? $existingApp->application_no : ('FAC-' . rand(100000, 999999));

        $wasAwaitingDocs = !$isAdminEdit && ($isExisting && in_array($existingApp->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار الوثائق الناقصة', 3]));
        $statusToSet = $isAdminEdit
            ? ($existingApp->getRawOriginal('status') ?: $existingApp->status)
            : ($isDraft ? 'مسودة' : 'تحت التدقيق الأولي');

        $workFacultyToSave = $request->filled('work_faculty')
            ? $request->work_faculty
            : ($request->filled('gov_faculty') ? $request->gov_faculty : ($request->phd_faculty ?? optional($existingApp)->work_faculty));
        $workDeptToSave = $request->filled('work_department')
            ? $request->work_department
            : ($request->filled('phd_specialization') ? $request->phd_specialization : ($request->gov_department ?? optional($existingApp)->work_department));

        if ($isExisting) {
            $application = $existingApp;
            $application->update([
                'candidate_id' => $candidate->id,
                'request_type' => $requestType,
                'work_university_id' => $uniId,
                'work_faculty' => $workFacultyToSave,
                'work_department' => $workDeptToSave,
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
                'work_faculty' => $workFacultyToSave,
                'work_department' => $workDeptToSave,
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

        $govUniIdToSave = null;
        $govUniOtherToSave = null;
        if ($request->input('gov_university_id') === 'other') {
            $govUniIdToSave = null;
            $govUniOtherToSave = $request->filled('gov_university_other') ? trim($request->input('gov_university_other')) : optional($existingGovEd)->university_other;
        } elseif ($request->filled('gov_university_id') && is_numeric($request->gov_university_id)) {
            $govUniIdToSave = $request->gov_university_id;
            $govUniOtherToSave = null;
        } else {
            $govUniIdToSave = optional($existingGovEd)->university_id;
            $govUniOtherToSave = optional($existingGovEd)->university_other;
        }

        $govUniEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'thesis_title' => 'عضو هيئة تدريسية في جامعة حكومية',
            ],
            [
                'education_level_id' => 3,
                'country_id' => $syriaId,
                'university_id' => $govUniIdToSave,
                'university_other' => $govUniOtherToSave,
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

        $phdUniIdToSave = null;
        $phdUniOtherToSave = null;
        if ($request->input('phd_university_id') === 'other') {
            $phdUniIdToSave = null;
            $phdUniOtherToSave = $request->filled('phd_university_other') ? trim($request->input('phd_university_other')) : optional($existingPhdEd)->university_other;
        } elseif ($request->filled('phd_university_id') && is_numeric($request->phd_university_id)) {
            $phdUniIdToSave = $request->phd_university_id;
            $phdUniOtherToSave = null;
        } else {
            $phdUniIdToSave = optional($existingPhdEd)->university_id;
            $phdUniOtherToSave = optional($existingPhdEd)->university_other;
        }

        $resolveCountryId = function (?int $explicitCountryId, $universityId, ?int $fallbackCountryId) use ($syriaId): int {
            if ($explicitCountryId && LookupCountry::where('id', $explicitCountryId)->exists()) {
                return $explicitCountryId;
            }
            if ($universityId && is_numeric($universityId)) {
                $uniCountryId = LookupUniversity::where('id', $universityId)->value('country_id');
                if ($uniCountryId && LookupCountry::where('id', $uniCountryId)->exists()) {
                    return (int) $uniCountryId;
                }
            }
            return $fallbackCountryId ?: $syriaId;
        };

        $phdCountryIdToSave = $resolveCountryId(
            $request->filled('phd_country_id') && is_numeric($request->phd_country_id) ? (int) $request->phd_country_id : null,
            $phdUniIdToSave,
            optional($existingPhdEd)->country_id
        );

        $phdEd = Education::updateOrCreate(
            [
                'application_id' => $application->id,
                'thesis_title' => 'شهادة الدكتوراه',
            ],
            [
                'education_level_id' => 3,
                'country_id' => $phdCountryIdToSave,
                'university_id' => $phdUniIdToSave,
                'university_other' => $phdUniOtherToSave,
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
            $maUniIdToSave = null;
            $maUniOtherToSave = null;
            if ($request->input('ma_university_id') === 'other') {
                $maUniIdToSave = null;
                $maUniOtherToSave = $request->filled('ma_university_other') ? trim($request->input('ma_university_other')) : optional($existingMaEd)->university_other;
            } elseif ($request->filled('ma_university_id') && is_numeric($request->ma_university_id)) {
                $maUniIdToSave = $request->ma_university_id;
                $maUniOtherToSave = null;
            } else {
                $maUniIdToSave = optional($existingMaEd)->university_id;
                $maUniOtherToSave = optional($existingMaEd)->university_other;
            }

            $maCountryIdToSave = $resolveCountryId(
                $request->filled('ma_country_id') && is_numeric($request->ma_country_id) ? (int) $request->ma_country_id : null,
                $maUniIdToSave,
                optional($existingMaEd)->country_id
            );

            Education::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'thesis_title' => 'شهادة الماجستير',
                ],
                [
                    'education_level_id' => 2,
                    'country_id' => $maCountryIdToSave,
                    'university_id' => $maUniIdToSave,
                    'university_other' => $maUniOtherToSave,
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
            'file_id_card'           => ['id' => 13, 'notes' => 'صورة عن الهوية الشخصية / إثبات الشخصية'],
            'file_uni_request'       => ['id' => 7,  'notes' => 'طلب تقويم / كتاب ترشيح الجامعة الخاصة'],
            'file_service_statement' => ['id' => 9,  'notes' => 'بيان وضع أو بطاقة ذاتية من الجامعة الحكومية'],
            'file_phd_cert'          => ['id' => 8,  'notes' => 'نسخة مصدقة أصولاً عن شهادة الدكتوراه'],
            'file_payment'           => ['id' => 10, 'notes' => 'إيصال تسديد رسم السماح بالتدريس (125,000 ل.س)'],
            'file_ma_cert'           => ['id' => 12, 'notes' => 'نسخة مصدقة عن شهادة الماجستير'],
            'file_other_attachments' => ['id' => 23, 'notes' => 'مرفقات ووثائق داعمة أخرى'],
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
                        'education_id'       => $phdEd->id,
                        'attachment_type_id' => $typeId,
                    ],
                    [
                        'file_path' => $path,
                        'notes'     => $note,
                    ]
                );
            }
        }

        // Safeguard: Ensure all attachments of this application belong to the PhD record ($phdEd->id)
        $appOtherEduIds = Education::where('application_id', $application->id)
            ->where('id', '!=', $phdEd->id)
            ->pluck('id');
        if ($appOtherEduIds->isNotEmpty()) {
            EducationAttachment::whereIn('education_id', $appOtherEduIds)->update([
                'education_id' => $phdEd->id
            ]);
        }

        if ($isAdminEdit) {
            return $this->redirectAfterAdminWizardUpdate($application);
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

    // =========================================================================
    // ADMIN MULTI-STEP EDIT WIZARD BRIDGE (SAME WIZARD UI, STATUS UNCHANGED)
    // =========================================================================
    public function resolveWizardTrackForApplication(Application $application): string
    {
        $reqType = (string) ($application->request_type ?? '');
        if (str_contains($reqType, 'سماح') || str_contains($reqType, 'هيئة تدريسية')) {
            return 'faculty_permission';
        }

        $isDoctorate = str_contains($reqType, 'دكتوراه')
            || str_contains($reqType, 'دكتورة')
            || $application->educations()->whereHas('level', function ($q) {
                $q->where('name', 'like', '%دكتور%');
            })->exists();
        $isForeign = str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري');

        if ($isDoctorate && $isForeign) {
            return 'foreign_doctorate';
        }
        if ($isForeign) {
            if (str_contains($reqType, 'نظري') || (!str_contains($reqType, 'تطبيقي') && $application->total_experience_days >= 730)) {
                return 'foreign_masters_theoretical';
            }
            return 'foreign_masters_applied';
        }
        if ($isDoctorate) {
            return 'syrian_doctorate';
        }
        if (str_contains($reqType, 'تطبيقي')) {
            return 'syrian_masters_applied';
        }
        if (str_contains($reqType, 'نظري')) {
            return 'syrian_masters_theoretical';
        }

        return ($application->total_experience_days >= 730)
            ? 'syrian_masters_theoretical'
            : 'syrian_masters_applied';
    }

    public function renderAdminEditWizard(int $id, Request $request)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType',
        ])->findOrFail($id);

        $track = $this->resolveWizardTrackForApplication($application);

        return match ($track) {
            'faculty_permission'          => view('university.apply.faculty_permission', $this->prepareFacultyPermissionWizardData($request, $application)),
            'foreign_doctorate'           => view('university.apply.foreign_doctorate', $this->prepareForeignDoctorateWizardData($request, $application)),
            'foreign_masters_theoretical' => view('university.apply.foreign_masters_theoretical', $this->prepareForeignMastersWizardData($request, 'theoretical', $application)),
            'foreign_masters_applied'     => view('university.apply.foreign_masters_applied', $this->prepareForeignMastersWizardData($request, 'applied', $application)),
            'foreign_masters'             => view('university.apply.foreign_masters_applied', $this->prepareForeignMastersWizardData($request, 'applied', $application)),
            'syrian_doctorate'            => view('university.apply.syrian_doctorate', $this->prepareSyrianDoctorateWizardData($request, $application)),
            'syrian_masters_theoretical'  => view('university.apply.syrian_masters_theoretical', $this->prepareSyrianMastersWizardData($request, 'theoretical', $application)),
            default                       => view('university.apply.syrian_masters_applied', $this->prepareSyrianMastersWizardData($request, 'applied', $application)),
        };
    }

    public function submitAdminEditWizard(int $id, Request $request)
    {
        $application = Application::findOrFail($id);
        $track = $request->input('admin_wizard_track') ?: $this->resolveWizardTrackForApplication($application);

        // Server-only flags (cannot be spoofed from HTTP request payload)
        $request->attributes->set('admin_wizard_edit', true);
        $request->attributes->set('admin_target_app_id', $application->id);

        return match ($track) {
            'faculty_permission'          => $this->submitFacultyPermissionWizard($request),
            'foreign_doctorate'           => $this->submitForeignDoctorateWizard($request, $application->id),
            'foreign_masters_theoretical' => $this->submitForeignMastersWizard($request, $application->id, 'theoretical'),
            'foreign_masters_applied'     => $this->submitForeignMastersWizard($request, $application->id, 'applied'),
            'foreign_masters'             => $this->submitForeignMastersWizard($request, $application->id),
            'syrian_doctorate'            => $this->submitSyrianDoctorateWizard($request),
            'syrian_masters_theoretical'  => $this->submitSyrianMastersWizard($request, 'theoretical'),
            'syrian_masters_applied'      => $this->submitSyrianMastersWizard($request, 'applied'),
            default                       => $this->submitSyrianMastersWizard($request),
        };
    }

    protected function redirectAfterAdminWizardUpdate(Application $application)
    {
        $appNo = $application->application_no ?? $application->id;
        return redirect()
            ->route('admin.applications.edit', $application->id)
            ->with('action_banner', [
                'variant'     => 'success',
                'title'       => 'تم حفظ تعديلات الطلب رقم (#' . $appNo . ') بنجاح',
                'message'     => 'تم تحديث البيانات والمرفقات مع الحفاظ على الحالة الحالية للمعاملة (' . $application->status . ') دون تغيير.',
                'issue_url'   => route('admin.applications.index'),
                'issue_label' => 'العودة إلى سجلات الطلبات',
            ]);
    }
}



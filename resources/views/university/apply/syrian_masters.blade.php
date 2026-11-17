@extends(!empty($isAdminEdit) ? 'layouts.admin' : 'layouts.university')

@php
    $trackType = (isset($trackType) && in_array($trackType, ['applied', 'theoretical'], true)) ? $trackType : 'applied';
    $isTheoreticalTrack = ($trackType === 'theoretical');
    $trackLabel = $isTheoreticalTrack ? 'ماجستير سوري (مسار نظري)' : 'ماجستير سوري (مسار تطبيقي)';
    $submitRoute = !empty($isAdminEdit)
        ? $adminSubmitRoute
        : ($isTheoreticalTrack
            ? route('university.apply.syrian_masters_theoretical.submit')
            : route('university.apply.syrian_masters_applied.submit'));
@endphp

@section('title', 'تقديم طلب تعادل ' . $trackLabel)

@section('content')

@php
    $candidate = $draft ? $draft->candidate : null;
    $hsEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'ثانوية')) || $e->education_level_id == 4; }) : null;
    $baEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'إجازة')) || $e->education_level_id == 1; }) : null;
    $maEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'ماجستير')) || $e->education_level_id == 2; }) : null;

    $existingFiles = [];
    if ($draft) {
        $sharedTypeIds = [7, 21];
        $typeToKeyMap = [
            1  => 'file_hs_cert',
            2  => 'hs_decision_file',
            3  => 'file_ba_cert',
            4  => 'ba_decision_file',
            6  => 'file_ma_cert',
            8  => 'file_thesis_summary',
            13 => 'file_national_id',
            14 => 'file_cv',
            15 => 'file_uni_request',
            16 => 'file_payment',
            18 => 'file_lang_cert',
            19 => 'file_icdl_cert',
            20 => 'file_exp_cert',
            22 => 'file_nat_library_receipt',
            23 => 'file_other_attachments',
        ];

        foreach ($draft->educations as $ed) {
            foreach ($ed->attachments as $att) {
                $tId = (int) ($att->attachment_type_id ?? 0);
                $notes = trim($att->notes ?? '');
                $path = $att->file_path;
                if (!$path) continue;

                if ($tId && !in_array($tId, $sharedTypeIds, true)) {
                    $existingFiles[$tId] = $path;
                }

                // مطابقة الملاحظات المحددة أولاً لمنع التداخل في الأنواع المشتركة (7 و 21) و (13)
                if (str_contains($notes, 'هوية') || str_contains($notes, 'شخصية') || str_contains($notes, 'جواز') || $tId === 11) {
                    $existingFiles['file_national_id'] = $path;
                    $existingFiles['national_id'] = $path;
                } elseif ((str_contains($notes, 'قرار') && str_contains($notes, 'ثانوية')) || $tId === 2) {
                    $existingFiles['hs_decision_file'] = $path;
                    $existingFiles['file_hs_decision'] = $path;
                } elseif ((str_contains($notes, 'ثانوية') && !str_contains($notes, 'قرار')) || $tId === 1) {
                    $existingFiles['file_hs_cert'] = $path;
                } elseif ((str_contains($notes, 'قرار') && (str_contains($notes, 'الإجازة') || str_contains($notes, 'الجامعية'))) || $tId === 4) {
                    $existingFiles['ba_decision_file'] = $path;
                    $existingFiles['file_ba_decision'] = $path;
                } elseif ((str_contains($notes, 'الإجازة') && !str_contains($notes, 'قرار')) || $tId === 3) {
                    $existingFiles['file_ba_cert'] = $path;
                } elseif (str_contains($notes, 'المناقشة')) {
                    $existingFiles['file_ma_defense_decision'] = $path;
                } elseif (str_contains($notes, 'منح') && str_contains($notes, 'ماجستير')) {
                    $existingFiles['file_ma_grant_decision'] = $path;
                } elseif (str_contains($notes, 'بالتسجيل') || (str_contains($notes, 'التسجيل') && str_contains($notes, 'ماجستير'))) {
                    $existingFiles['file_ma_reg_decision'] = $path;
                } elseif (str_contains($notes, 'تواريخ') || str_contains($notes, 'مجلس') || $tId === 7) {
                    $existingFiles['file_ma_dates'] = $path;
                } elseif ((str_contains($notes, 'شهادة') && str_contains($notes, 'ماجستير')) || $tId === 6 || $tId === 12) {
                    $existingFiles['file_ma_cert'] = $path;
                } elseif (str_contains($notes, 'ملخص') || $tId === 8 || $tId === 17) {
                    $existingFiles['file_thesis_summary'] = $path;
                } elseif (str_contains($notes, 'المكتبة') || $tId === 22 || $tId === 25) {
                    $existingFiles['file_nat_library_receipt'] = $path;
                } elseif (str_contains($notes, 'السيرة') || str_contains($notes, 'سيرة') || $tId === 14) {
                    $existingFiles['file_cv'] = $path;
                } elseif (str_contains($notes, 'كتاب') || str_contains($notes, 'طلب') || $tId === 15) {
                    $existingFiles['file_uni_request'] = $path;
                } elseif (str_contains($notes, 'إيصال') || str_contains($notes, 'تسديد') || str_contains($notes, 'رسم') || $tId === 16) {
                    $existingFiles['file_payment'] = $path;
                } elseif (str_contains($notes, 'اللغة') || $tId === 18) {
                    $existingFiles['file_lang_cert'] = $path;
                } elseif (str_contains($notes, 'ICDL') || str_contains($notes, 'حاسوب') || $tId === 19) {
                    $existingFiles['file_icdl_cert'] = $path;
                } elseif (str_contains($notes, 'خبرة') || $tId === 20) {
                    $existingFiles['file_exp_cert'] = $path;
                } elseif (str_contains($notes, 'كشوفات الرواتب') || str_contains($notes, 'التحويلات البنكية') || (str_contains($notes, 'رواتب') && !str_contains($notes, 'العقود'))) {
                    $existingFiles['file_salary_slips'] = $path;
                } elseif (str_contains($notes, 'أخرى') || str_contains($notes, 'اخرى') || $tId === 23 || $tId === 24) {
                    $existingFiles['file_other_attachments'] = $path;
                } elseif (str_contains($notes, 'العقود') || str_contains($notes, 'عقود') || $tId === 21) {
                    $existingFiles['file_contracts'] = $path;
                } elseif (isset($typeToKeyMap[$tId])) {
                    $existingFiles[$typeToKeyMap[$tId]] = $path;
                }
            }
        }
    }

    $isAwaitingDocs = empty($isAdminEdit) && $draft && (
        $draft->status_id == 3 || 
        in_array($draft->status, ['بانتظار الوثائق', 'بانتظار استكمال الوثائق', 'بانتظار تصحيح الوثائق', 'بانتظار الوثائق الناقصة'])
    );
    $canSaveDraft = empty($isAdminEdit) && (!$draft || ($draft->status_id == 1 || $draft->status === 'مسودة'));
@endphp

@if(!empty($isAdminEdit))
    @include('university.apply.partials._admin_edit_banner', ['wizardTitle' => $trackLabel])
@else
<!-- BREADCRUMBS & PAGE HEADER -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" style="color: var(--primary-container); text-decoration: none;">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" style="color: var(--primary-container); text-decoration: none;">خيارات التعادل</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">{{ $trackLabel }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid {{ $isTheoreticalTrack ? 'fa-chalkboard-user' : 'fa-flask-vial' }} me-2" style="color: var(--heritage-gold);"></i> معاملة تعادل {{ $trackLabel }}
                </h3>
                <p class="body-md text-muted mb-0">
                    @if($isTheoreticalTrack)
                        لتعادل درجة الماجستير الصادرة عن الجامعات السورية لتدريس المقررات النظرية (يتطلب خبرة تدريسية تراكمية لا تقل عن سنتين ومقابلة وأهلية تدريس).
                    @else
                        لتعادل درجة الماجستير الصادرة عن الجامعات السورية لتدريس الجوانب التطبيقية والعملية (بدون شرط خبرة السنتين وبدون قرار أهلية).
                    @endif
                </p>
            </div>
            @if($draft)
                <div>
                    @if($isAwaitingDocs)
                        <span class="badge bg-warning text-dark px-3 py-2 border border-warning fs-7 fw-bold">
                            <i class="fa-solid fa-file-circle-exclamation me-1"></i> بانتظار استكمال الوثائق: #{{ $draft->application_no }}
                        </span>
                    @else
                        <span class="badge px-3 py-2 border fs-7 fw-bold" style="background-color: #FAF6EE; color: #8A651E; border-color: #D9C394 !important;">
                            <i class="fa-solid fa-file-pen me-1"></i> تعديل مسودة طلب: #{{ $draft->application_no }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<style>
    .mohe-official-panel {
        background: #ffffff;
        border: 1px solid #d5dce6;
        border-top: 3px solid var(--imperial-navy);
        border-radius: 10px;
        box-shadow: 0 3px 14px rgba(15, 29, 51, 0.05);
        overflow: hidden;
    }
    .mohe-panel-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 20px;
    }
    .mohe-emblem-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: var(--imperial-navy);
        color: var(--heritage-gold-light);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(26, 42, 68, 0.18);
    }
    .mohe-subform-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
    }
    .mohe-ledger-wrap {
        border: 1px solid #d5dce6;
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
    }
    .mohe-ledger-table thead th {
        background-color: var(--imperial-navy) !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 0.80rem;
        padding: 11px 10px;
        border-color: #243b5e !important;
        border-bottom: 2px solid var(--heritage-gold) !important;
        vertical-align: middle;
        white-space: nowrap;
    }
    .mohe-ledger-table tbody td {
        padding: 10px 8px;
        vertical-align: middle;
        border-color: #e8edf4 !important;
        font-size: 0.84rem;
    }
    .mohe-ledger-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .mohe-duration-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f3f6fb;
        color: var(--imperial-navy);
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 10px;
        font-weight: 600;
        font-size: 0.80rem;
    }
    .mohe-stat-tile {
        background: #ffffff;
        border: 1px solid #cfd8e3;
        border-top: 2px solid var(--imperial-navy);
        border-radius: 8px;
        padding: 12px 10px;
        text-align: center;
        box-shadow: 0 2px 6px rgba(15, 29, 51, 0.04);
    }
    .mohe-stat-tile.is-gold {
        background: #fcfaf4;
        border-color: #dec99c;
        border-top: 2px solid var(--heritage-gold-dark);
    }
    .mohe-stat-value {
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--imperial-navy);
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
    }
    .mohe-stat-tile.is-gold .mohe-stat-value {
        color: var(--heritage-gold-dark);
    }
    .mohe-stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-top: 3px;
    }
    .mohe-btn-navy {
        background-color: var(--imperial-navy);
        border: 1px solid var(--imperial-navy);
        color: #ffffff;
        transition: all 0.2s ease;
    }
    .mohe-btn-navy:hover {
        background-color: var(--imperial-navy-dark);
        border-color: var(--imperial-navy-dark);
        color: var(--heritage-gold-light);
    }
    .mohe-add-icon-btn {
        width: 44px;
        height: 44px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .mohe-action-icon-btn {
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 6px !important;
        line-height: 1 !important;
    }
</style>

<!-- WIZARD MAIN CONTAINER -->
<div class="card border-0 shadow-sm" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
    <div class="card-body p-4 p-md-5">

        @if($isAwaitingDocs)
            <div class="alert alert-warning border-warning shadow-sm d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background-color: #fff8e1; border-right: 4px solid #f59e0b !important;">
                <i class="fa-solid fa-file-circle-exclamation fs-3 text-warning"></i>
                <div>
                    <div class="fw-bold fs-6 text-dark">معاملة معادة لاستكمال وتصحيح الوثائق (طلب رقم: #{{ $draft->application_no }})</div>
                    <div class="small text-muted">حالة المعاملة الحالية هي "بانتظار الوثائق". يرجى استكمال المرفقات والبيانات المطلوبة ثم الضغط على زر الإرسال. لا يمكن حفظ هذا الطلب كمسودة لأنه مسجل ومقدم رسمياً.</div>
                </div>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger mb-4 shadow-sm border-0 rounded" style="border-right: 4px solid #ba1a1a !important;">
                <h6 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> يرجى تصحيح الملاحظات التالية لإرسال الطلب:</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li class="fs-7">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Multi-Step Progress Indicators (6 STEPS TOTAL) -->
        <div class="wizard-steps" id="wizard-steps-container">
            <div class="wizard-progress" id="wizard-progress-bar" style="width: 0%;"></div>
            
            <div class="wizard-step active" data-step="1">
                <div class="wizard-icon">1</div>
                <span class="wizard-label d-none d-md-inline">الشخصية والجامعة</span>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="wizard-icon">2</div>
                <span class="wizard-label d-none d-md-inline">الثانوية</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-icon">3</div>
                <span class="wizard-label d-none d-md-inline">الإجازة</span>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="wizard-icon">4</div>
                <span class="wizard-label d-none d-md-inline">الماجستير</span>
            </div>
            <div class="wizard-step" data-step="5">
                <div class="wizard-icon">5</div>
                <span class="wizard-label d-none d-md-inline">المرفقات</span>
            </div>
            <div class="wizard-step" data-step="6">
                <div class="wizard-icon">6</div>
                <span class="wizard-label d-none d-md-inline">المراجعة</span>
            </div>
        </div>

        <!-- Form Tag -->
        <form action="{{ $submitRoute }}" method="POST" enctype="multipart/form-data" id="wizard-form" novalidate>
            @csrf
            <input type="hidden" name="draft_id" id="input-draft-id" value="{{ optional($draft)->id }}">
            <input type="hidden" name="track_type" id="input-trackType" value="{{ $trackType }}">

            <!-- ================= STEP 1: PERSONAL INFO & UNIVERSITY REQUEST ================= -->
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب طلب التقييم الصادر عن الجامعة
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم المرشح </label><span class="text-danger"> *</span>
                        <input type="text" name="full_name" id="input-fullName" class="form-control academic-input" placeholder="الاسم والنسبة" value="{{ old('full_name', optional(optional($draft)->candidate)->full_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأب</label><span class="text-danger"> *</span>
                        <input type="text" name="father_name" id="input-fatherName" class="form-control academic-input" placeholder="اسم الأب" value="{{ old('father_name', optional(optional($draft)->candidate)->father_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأم ونسبتها </label><span class="text-danger"> *</span>
                        <input type="text" name="mother_name" id="input-motherName" class="form-control academic-input" placeholder="اسم ونسبة الأم" value="{{ old('mother_name', optional(optional($draft)->candidate)->mother_name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنسية </label><span class="text-danger"> *</span>
                        <select name="nationality_id" id="input-nationality" class="form-select academic-input" onchange="updateSyrianStatus(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('nationality_id', optional(optional($draft)->candidate)->nationality_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_syrian" id="input-isSyrian" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الرقم الوطني للمرشح </label><span class="text-danger"> *</span>
                        @php
                            $draftNatId = optional(optional($draft)->candidate)->national_id;
                            if ($draftNatId && str_starts_with($draftNatId, 'TMP-')) {
                                $draftNatId = '';
                            }
                        @endphp
                        <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="الرقم الوطني (11 رقماً)" maxlength="11" minlength="11" pattern="\d{11}" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length === 11) { this.setCustomValidity(''); } else { this.setCustomValidity('الرقم الوطني يجب أن يتألف من 11 رقماً حصراً'); }" value="{{ old('national_id', $draftNatId) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الميلاد </label><span class="text-danger"> *</span>
                        <input type="date" name="dob" id="input-dob" class="form-control academic-input" value="{{ old('dob', optional(optional($draft)->candidate)->dob) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الوظيفة الحالية للمرشح </label><span class="text-danger"> *</span>
                        <input type="text" name="job_title" id="input-jobTitle" class="form-control academic-input" placeholder="مثال: مهندس، موظف، معيد" value="{{ old('job_title', optional(optional($draft)->candidate)->job_title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنس </label><span class="text-danger"> *</span>
                        <select name="gender" id="input-gender" class="form-select academic-input" required>
                            <option value="ذكر" {{ old('gender', optional(optional($draft)->candidate)->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                            <option value="أنثى" {{ old('gender', optional(optional($draft)->candidate)->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">البريد الإلكتروني</label><span class="text-danger"> *</span>
                        <input type="email" name="email" id="input-email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', optional(optional($draft)->candidate)->email ?: (empty($isAdminEdit) ? (Auth::user()?->university?->email ?? Auth::user()?->email) : '')) }}" oninput="this.setCustomValidity('')" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف المحمول</label><span class="text-danger"> *</span>
                        <input type="text" name="mobile" id="input-mobile" class="form-control academic-input" placeholder="09xxxxxxxx" maxlength="10" pattern="[0-9]{10}" value="{{ old('mobile', optional(optional($draft)->candidate)->mobile) }}" oninput="this.setCustomValidity(''); this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف الأرضي</label>
                        <input type="text" name="phone" id="input-phone" class="form-control academic-input" placeholder="011xxxxxxx" maxlength="10" pattern="[0-9]{10}" value="{{ old('phone', optional(optional($draft)->candidate)->phone) }}" oninput="this.setCustomValidity(''); this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">عنوان الإقامة الحالي بالتفصيل </label><span class="text-danger"> *</span>
                        <textarea name="address" id="input-address" class="form-control academic-input" rows="2" placeholder="المحافظة - المدينة - الشارع - البناء" required>{{ old('address', optional(optional($draft)->candidate)->address) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">رقم كتاب طلب التقييم الصادر عن الجامعة </label><span class="text-danger"> *</span>
                        <input type="text" name="req_no" id="input-reqNo" class="form-control academic-input" placeholder="مثال: 123" value="{{ old('req_no', optional($draft)->new_uni_request_no) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ كتاب طلب التقييم </label><span class="text-danger"> *</span>
                        <input type="date" name="req_date" id="input-reqDate" class="form-control academic-input" value="{{ old('req_date', optional($draft)->new_uni_request_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تكرار طلب التعادل </label><span class="text-danger"> *</span>
                        <select name="is_first_time" id="input-isFirstTime" class="form-select academic-input" onchange="togglePreviousDegree(this)" required>
                            <option value="1" {{ old('is_first_time', optional($draft)->parent_application_id ? '0' : '1') == '1' ? 'selected' : '' }}>تعادل للمرة الأولى</option>
                            <option value="0" {{ old('is_first_time', optional($draft)->parent_application_id ? '0' : '1') == '0' ? 'selected' : '' }}>سبق التقدم بتعادل لشهادة أخرى</option>
                        </select>
                    </div>

                    <!-- Hidden by default: previous degree info if not first time -->
                    <div class="col-12 mt-3" id="previous-degree-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--surface-container-low); border-right: 4px solid var(--primary-container) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--primary-container);"><i class="fa-solid fa-clock-rotate-left me-1"></i> بيانات شهادة التعادل السابقة</h6>
                            <p class="label-sm text-muted mb-3">يرجى تحديد المعاملة السابقة لربط الطلب الجديد بالملف المحفوظ:</p>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label label-md fw-medium text-dark">اختر المعاملة السابقة للمرشح </label><span class="text-danger"> *</span>
                                    <select name="parent_application_id" id="input-parentAppId" class="form-select academic-input">
                                        <option value="">-- اختر من المعاملات السابقة المسجلة --</option>
                                        @if(isset($previousApplications))
                                            @foreach($previousApplications as $prevApp)
                                                <option value="{{ $prevApp->id }}" {{ old('parent_application_id', optional($draft)->parent_application_id) == $prevApp->id ? 'selected' : '' }}>
                                                    معاملة رقم: {{ $prevApp->application_no }} ({{ optional($prevApp->candidate)->full_name }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 2: HIGH SCHOOL ================= -->
            <div class="form-section" id="step-2" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات الشهادة الثانوية (البكالوريا)
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">دولة الحصول على الشهادة الثانوية </label><span class="text-danger"> *</span>
                        <select name="hs_country_id" id="input-hsCountry" class="form-select academic-input" onchange="toggleHsCountrySection(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('hs_country_id', optional($hsEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">نوع البكالوريا </label><span class="text-danger"> *</span>
                        <select name="hs_type" id="input-hsType" class="form-select academic-input" required>
                            @php $oldHsType = old('hs_type', optional($hsEd)->section_name); @endphp
                            <option value="علمي" {{ $oldHsType == 'علمي' ? 'selected' : '' }}>علمي</option>
                            <option value="أدبي" {{ $oldHsType == 'أدبي' ? 'selected' : '' }}>أدبي</option>
                            <option value="شرعي" {{ $oldHsType == 'شرعي' ? 'selected' : '' }}>شرعي</option>
                            <option value="تجاري" {{ $oldHsType == 'تجاري' ? 'selected' : '' }}>تجاري</option>
                            <option value="صناعي" {{ $oldHsType == 'صناعي' ? 'selected' : '' }}>صناعي</option>
                            <option value="أخرى" {{ $oldHsType == 'أخرى' || $oldHsType == 'أخر' ? 'selected' : '' }}>أخرى</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الحصول على الشهادة (العام فقط) </label><span class="text-danger"> *</span>
                        <input type="number" name="hs_grant_date" id="input-hsDate" class="form-control academic-input" min="1950" max="{{ date('Y') }}" placeholder="مثال: 2015" value="{{ old('hs_grant_date', optional($hsEd)->grant_date ? (strlen($hsEd->grant_date) > 4 ? substr($hsEd->grant_date, 0, 4) : $hsEd->grant_date) : '') }}" required>
                    </div>

                    <!-- Conditional high school equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="hs-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الشهادة الثانوية غير السورية</h6>
                            <p class="label-sm text-muted mb-3">بما أن الشهادة الثانوية غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم وتاريخ قرار المعادلة الصادر عن وزارة التربية السورية، ورفع صورة القرار في خطوة المرفقات النهائية (إجباري).</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار معادلة الشهادة الثانوية </label><span class="text-danger"> *</span>
                                    <input type="text" name="hs_decision_no" id="input-hsDecisionNo" class="form-control academic-input" placeholder="أدخل رقم القرار الرسمي" value="{{ old('hs_decision_no', $hsEd && $hsEd->notes ? preg_replace('/.*رقم قرار المعادلة الثانوية:\s*([^\|]+).*/u', '$1', $hsEd->notes) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ قرار معادلة الشهادة الثانوية </label><span class="text-danger"> *</span>
                                    <input type="date" name="hs_decision_date" id="input-hsDecisionDate" class="form-control academic-input" value="{{ old('hs_decision_date', $hsEd && $hsEd->notes && str_contains($hsEd->notes, 'تاريخ القرار:') ? preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $hsEd->notes) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ================= STEP 3: UNIVERSITY DEGREE ================= -->
            <div class="form-section" id="step-3" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: بيانات الإجازة الجامعية الأولى (البكالوريوس)
                </h5>
                
                <div class="row g-3">
                    <!-- 1. الدولة المانحة للإجازة (مطلوب) -->
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">1. الدولة المانحة للإجازة </label><span class="text-danger">*</span>
                        <select name="ba_country_id" id="input-baCountry" class="form-select academic-input" onchange="toggleBaCountrySection(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ba_country_id', optional($baEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. الجامعة المانحة (مطلوب) -->
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">2. الجامعة المانحة</label><span class="text-danger"> *</span>
                        <input type="text" name="ba_university_text" id="input-baUniText" class="form-control academic-input" placeholder="مثال: جامعة دمشق / جامعة القاهرة" value="{{ old('ba_university_text', optional($baEd)->university_name ?: (optional(optional($baEd)->university)->name ?? (optional($baEd)->section_name ?? ''))) }}" required>
                    </div>

                    <!-- 3. الكلية (مطلوب) -->
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">3. الكلية </label><span class="text-danger">*</span>
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية / كلية العلوم" value="{{ old('ba_faculty', optional($baEd)->faculty ?: optional($baEd)->general_specialization) }}" required>
                    </div>

                    <!-- 4. القسم (مطلوب) -->
                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">4. القسم </label><span class="text-danger">*</span>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: قسم هندسة البرمجيات / قسم الكيمياء" value="{{ old('ba_department', optional($baEd)->department ?: optional($baEd)->exact_specialization) }}" required>
                    </div>

                    <!-- 5. اختصاص (اختياري) -->
                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">5. اختصاص <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="text" name="ba_specialization" id="input-baSpec" class="form-control academic-input" placeholder="مثال: ذكاء اصطناعي / كيمياء تطبيقية" value="{{ old('ba_specialization', optional($baEd)->section_name ?: optional($baEd)->exact_specialization) }}">
                    </div>

                    <!-- 6. التقدير -->
                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">6. التقدير </label><span class="text-danger">*</span>
                        @php $oldBaRank = old('ba_rank', optional($baEd)->rank); @endphp
                        <select name="ba_rank" id="input-baRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ ($oldBaRank == 'ممتاز' || $oldBaRank == 'امتياز') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ $oldBaRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldBaRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ $oldBaRank == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <!-- 7. تاريخ الحصول على الدرجة (مطلوب) -->
                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">7. تاريخ الحصول على الدرجة </label><span class="text-danger">*</span>
                        <input type="date" name="ba_grant_date" id="input-baGrantDate" class="form-control academic-input" value="{{ old('ba_grant_date', optional($baEd)->grant_date) }}" oninput="this.setCustomValidity('')" required>
                    </div>

                    <!-- Conditional bachelor's equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="ba-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الإجازة الجامعية الأولى غير السورية</h6>
                            <p class="label-sm text-muted mb-3">بما أن الإجازة الجامعية الأولى غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم وتاريخ قرار المعادلة الصادر عن وزارة التعليم العالي والبحث العلمي السورية، ورفع صورة القرار في خطوة المرفقات النهائية (إجباري).</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار تعادل الإجازة الجامعية </label><span class="text-danger"> *</span>
                                    <input type="text" name="ba_decision_no" id="input-baDecisionNo" class="form-control academic-input" placeholder="أدخل رقم قرار التعادل الرسمي" value="{{ old('ba_decision_no', $baEd && $baEd->notes ? preg_replace('/.*رقم قرار معادلة الإجازة:\s*([^\|]+).*/u', '$1', $baEd->notes) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ قرار تعادل الإجازة الجامعية </label><span class="text-danger"> *</span>
                                    <input type="date" name="ba_decision_date" id="input-baDecisionDate" class="form-control academic-input" value="{{ old('ba_decision_date', $baEd && $baEd->notes && str_contains($baEd->notes, 'تاريخ القرار:') ? preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $baEd->notes) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 4: SYRIAN MASTER'S INFO ================= -->
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: بيانات درجة الماجستير السورية والخبرة
                </h5>
                
                <div class="row g-3">
                    <!-- Row 1: 1. الجامعة المانحة للماجستير * | 2. الكلية * | 3. القسم * -->
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">1. الجامعة المانحة للماجستير </label><span class="text-danger"> *</span>
                        <select name="ma_university_id" id="input-maUniId" class="form-select academic-input" required>
                            <option value="">-- اختر الجامعة السورية --</option>
                            @foreach($universities as $uni)
                                @if($uni->country && $uni->country->name === 'سوريا')
                                    <option value="{{ $uni->id }}" {{ old('ma_university_id', optional($maEd)->university_id) == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">2. الكلية </label><span class="text-danger"> *</span>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المدنية" value="{{ old('ma_faculty', optional($maEd)->faculty ?: optional($maEd)->general_specialization) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">3. القسم </label><span class="text-danger"> *</span>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="مثال: قسم الهندسة الإنشائية" value="{{ old('ma_department', optional($maEd)->department ?: optional($maEd)->exact_specialization) }}" required>
                    </div>

                    <!-- Row 2: 4. الاختصاص العام * | 5. الاختصاص الدقيق (اختياري) | 6. اسم الأستاذ المشرف * | 7. التقدير * -->
                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">4. الاختصاص العام </label><span class="text-danger"> *</span>
                        <input type="text" name="ma_general_specialization" id="input-maGenSpec" class="form-control academic-input" placeholder="مثال: الهندسة الإنشائية والزلازل" value="{{ old('ma_general_specialization', optional($maEd)->general_specialization) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">5. الاختصاص الدقيق <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="text" name="ma_specialization" id="input-maSpec" class="form-control academic-input" placeholder="مثال: تصميم المنشآت البيتونية المقاومة للزلازل" value="{{ old('ma_specialization', optional($maEd)->exact_specialization ?: optional($maEd)->section_name) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">6. اسم الأستاذ المشرف </label><span class="text-danger"> *</span>
                        <input type="text" name="ma_supervisor" id="input-maSupervisor" class="form-control academic-input" placeholder="الاسم الكامل للمشرف مع اللقب العلمي" value="{{ old('ma_supervisor', optional($maEd)->supervisor_name ?: optional($maEd)->supervisor) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-md fw-medium text-dark">7. التقدير </label><span class="text-danger"> *</span>
                        @php $oldMaRank = old('ma_rank', optional($maEd)->rank); @endphp
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ ($oldMaRank == 'ممتاز' || $oldMaRank == 'امتياز') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ $oldMaRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldMaRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                        </select>
                    </div>

                    <!-- Row 3: 8. تاريخ التسجيل بالدرجة * | 9. تاريخ المناقشة * | 10. تاريخ منح الدرجة * -->
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">8. تاريخ التسجيل بالدرجة </label><span class="text-danger"> *</span>
                        <input type="date" name="ma_registration_date" id="input-maRegDate" class="form-control academic-input" value="{{ old('ma_registration_date', optional($maEd)->registration_date) }}" oninput="this.setCustomValidity(''); const d = document.getElementById('input-maDefDate'); if(d) d.setCustomValidity(''); const g = document.getElementById('input-maGrantDate'); if(g) g.setCustomValidity('');" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">9. تاريخ المناقشة </label><span class="text-danger"> *</span>
                        <input type="date" name="ma_defense_date" id="input-maDefDate" class="form-control academic-input" value="{{ old('ma_defense_date', optional($maEd)->defense_date) }}" oninput="this.setCustomValidity(''); const g = document.getElementById('input-maGrantDate'); if(g) g.setCustomValidity('');" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">10. تاريخ منح الدرجة </label><span class="text-danger"> *</span>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" value="{{ old('ma_grant_date', optional($maEd)->grant_date) }}" onchange="checkMasterGrantDateForExperience()" oninput="this.setCustomValidity(''); checkMasterGrantDateForExperience();" required>
                    </div>

                    <!-- Row 4: 11. عنوان رسالة الماجستير (الأطروحة) بالتفصيل * -->
                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">11. عنوان رسالة الماجستير (الأطروحة) بالتفصيل </label><span class="text-danger"> *</span>
                        <textarea name="ma_thesis_title" id="input-maThesisTitle" class="form-control academic-input" rows="2" placeholder="أدخل عنوان رسالة الماجستير كاملاً كما هو مذكور في مصدقة التخرج أو الأطروحة" required>{{ old('ma_thesis_title', optional($maEd)->thesis_title) }}</textarea>
                    </div>

                    @php
                        $oldExperiences = old('experiences');
                        if (is_array($oldExperiences) && count($oldExperiences) > 0) {
                            $draftExperiences = collect($oldExperiences)->map(function($exp, $key) {
                                return (object)[
                                    'id' => $key + 1,
                                    'institution_name' => $exp['institution_name'] ?? $exp['institution'] ?? '',
                                    'role_or_subject' => $exp['role_or_subject'] ?? null,
                                    'start_date' => $exp['start_date'] ?? null,
                                    'end_date' => $exp['end_date'] ?? null,
                                    'notes' => $exp['notes'] ?? null,
                                ];
                            });
                        } else {
                            $draftExperiences = ($draft && $draft->experiences) ? $draft->experiences : collect();
                            if ($draftExperiences->isEmpty() && optional($maEd)->experience_from_year && optional($maEd)->experience_to_year) {
                                $s = \Carbon\Carbon::parse($maEd->experience_from_year);
                                $e = \Carbon\Carbon::parse($maEd->experience_to_year);
                                $draftExperiences = collect([(object)[
                                    'id' => 1,
                                    'institution_name' => optional($maEd)->notes ? preg_replace('/.*مكان الخبرة التدريسية:\s*/u', '', $maEd->notes) : 'جامعة سورية',
                                    'role_or_subject' => 'تدريس جامعي',
                                    'start_date' => $s->format('Y-m-d'),
                                    'end_date' => $e->format('Y-m-d'),
                                    'duration_days' => max(0, $s->diffInDays($e)),
                                    'notes' => null,
                                ]]);
                            }
                        }
                    @endphp

                    @if($isTheoreticalTrack)
                        <!-- Multi-Period Teaching Experience Section (Mandatory for Theoretical Track) -->
                        <div class="col-12 mt-4" id="experience-section-container">
                            <input type="hidden" id="input-hasExperience" name="has_experience" value="1">
                            <div class="mohe-official-panel">
                                <div class="mohe-panel-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="mohe-emblem-box">
                                            <i class="fa-solid fa-chalkboard-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                                                سجل فترات الخبرة التدريسية الأكاديمية <span class="text-danger">*</span>
                                            </h6>
                                            <p class="text-muted fs-8 mb-0 mt-0.5">
                                                يجب إدخال فترات الخبرة التدريسية لدى جامعة أو عدة جامعات بحيث لا يقل المجموع التراكمي عن <strong>سنتين كاملتين (730 يوماً فأكثر)</strong> لإتمام تقديم مسار <strong>(ماجستير نظري)</strong> وإصدار قرار الأهلية والمقابلة.
                                            </p>
                                        </div>
                                    </div>
                                    <span class="badge px-3 py-1.5 fs-8 fw-semibold" style="background-color: #fbf8f1; color: var(--heritage-gold-dark); border: 1px solid #dec99c;">
                                        <i class="fa-solid fa-shield-halved me-1"></i> الحد الأدنى: 730 يوماً
                                    </span>
                                </div>

                                <div class="p-3 p-md-4">
                                    <div id="experience-entry-wrapper">
                                        <!-- Form Inputs Card -->
                                        <div class="mohe-subform-box mb-3">
                                            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                                                <i class="fa-solid fa-plus-circle fs-7" style="color: var(--heritage-gold-dark);"></i>
                                                <span class="fw-bold fs-8" style="color: var(--imperial-navy);">إدراج فترة خبرة تدريسية جديدة</span>
                                            </div>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label label-sm fw-bold text-dark">الجهة / الجامعة والكلية <span class="text-danger">*</span></label>
                                                    <input type="text" id="exp_institution" class="form-control academic-input bg-white" placeholder="مثال: جامعة دمشق - كلية العلوم">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label label-sm fw-bold text-dark">الصفة التدريسية / المقرر <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                                                    <input type="text" id="exp_role" class="form-control academic-input bg-white" placeholder="مثال: تدريس مقرر البرمجة">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label label-sm fw-bold text-dark">من تاريخ <span class="text-danger">*</span></label>
                                                    <input type="date" id="exp_start_date" class="form-control academic-input bg-white" max="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label label-sm fw-bold text-dark">إلى تاريخ <span class="text-danger">*</span></label>
                                                    <input type="date" id="exp_end_date" class="form-control academic-input bg-white" max="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-1 d-flex justify-content-md-end">
                                                    <button type="button" class="btn mohe-btn-navy mohe-add-icon-btn shadow-xs" onclick="addExperiencePeriod()" title="إضافة فترة الخبرة إلى الجدول" aria-label="إضافة فترة الخبرة">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <input type="text" id="exp_notes" class="form-control academic-input form-control-sm bg-white" placeholder="ملاحظات إضافية أو رقم كتاب التكليف بالتدريس (اختياري)">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Periods Table -->
                                        <div class="mohe-ledger-wrap mb-3">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle text-center mb-0 mohe-ledger-table" id="experiences_table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 5%;">#</th>
                                                            <th style="width: 26%;"><i class="fa-solid fa-building-columns me-1 text-warning opacity-75"></i> الجهة / الجامعة والكلية</th>
                                                            <th style="width: 19%;"><i class="fa-solid fa-chalkboard me-1 text-warning opacity-75"></i> الصفة / المقرر</th>
                                                            <th style="width: 12%;"><i class="fa-regular fa-calendar-check me-1 text-warning opacity-75"></i> من تاريخ</th>
                                                            <th style="width: 12%;"><i class="fa-regular fa-calendar-xmark me-1 text-warning opacity-75"></i> إلى تاريخ</th>
                                                            <th style="width: 18%;"><i class="fa-solid fa-clock-rotate-left me-1 text-warning opacity-75"></i> المدة المحسوبة</th>
                                                            <th style="width: 8%;">إجراء</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="experiences_tbody">
                                                        @forelse($draftExperiences as $idx => $exp)
                                                            @php
                                                                $sDateStr = !empty($exp->start_date) ? $exp->start_date : null;
                                                                $eDateStr = !empty($exp->end_date) ? $exp->end_date : null;
                                                                $calcY = 0; $calcM = 0; $calcD = 0; $tDays = 0;
                                                                if ($sDateStr && $eDateStr) {
                                                                    try {
                                                                        $sDate = \Carbon\Carbon::parse($sDateStr);
                                                                        $eDate = \Carbon\Carbon::parse($eDateStr);
                                                                        if ($eDate->gte($sDate)) {
                                                                            $diff = $sDate->diff($eDate);
                                                                            $calcY = $diff->y; $calcM = $diff->m; $calcD = $diff->d;
                                                                            $tDays = $sDate->diffInDays($eDate);
                                                                        }
                                                                    } catch (\Exception $ex) {}
                                                                }
                                                            @endphp
                                                            <tr data-row-id="{{ $idx + 1 }}">
                                                                <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                                                <td class="text-start pe-3"><strong style="color: var(--imperial-navy);">{{ $exp->institution_name }}</strong></td>
                                                                <td class="text-secondary">{{ $exp->role_or_subject ?: '---' }}</td>
                                                                <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $sDateStr ? \Carbon\Carbon::parse($sDateStr)->format('Y-m-d') : '---' }}</span></td>
                                                                <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $eDateStr ? \Carbon\Carbon::parse($eDateStr)->format('Y-m-d') : '---' }}</span></td>
                                                                <td>
                                                                    <span class="mohe-duration-chip">
                                                                        <i class="fa-regular fa-clock"></i>
                                                                        {{ $calcY }} سنة و {{ $calcM }} شهر و {{ $calcD }} يوم
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-outline-danger mohe-action-icon-btn" onclick="deleteExperiencePeriod(this)" title="حذف الفترة"><i class="fa-solid fa-trash-can"></i></button>
                                                                    <input type="hidden" name="experiences[{{ $idx }}][institution_name]" value="{{ $exp->institution_name }}">
                                                                    <input type="hidden" name="experiences[{{ $idx }}][role_or_subject]" value="{{ $exp->role_or_subject }}">
                                                                    <input type="hidden" name="experiences[{{ $idx }}][start_date]" value="{{ $sDateStr ? \Carbon\Carbon::parse($sDateStr)->format('Y-m-d') : '' }}">
                                                                    <input type="hidden" name="experiences[{{ $idx }}][end_date]" value="{{ $eDateStr ? \Carbon\Carbon::parse($eDateStr)->format('Y-m-d') : '' }}">
                                                                    <input type="hidden" name="experiences[{{ $idx }}][duration_days]" value="{{ $tDays }}">
                                                                    <input type="hidden" name="experiences[{{ $idx }}][notes]" value="{{ $exp->notes }}">
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr id="no_experiences_row">
                                                                <td colspan="7" class="text-muted py-4">لم يتم تسجيل أي فترات خبرة تدريسية بعد. يرجى إضافة فترة واحدة أو أكثر بحيث يبلغ المجموع التراكمي 730 يوماً (سنتين) على الأقل.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Live Executive Summary Duration Scorecard (بدون مربعات خضراء) -->
                                        <div class="p-3 p-md-4 rounded-3 border bg-white" id="experience_kpi_card" style="border-color: #d5dce6 !important;">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-hourglass-half fs-6" style="color: var(--imperial-navy);" id="exp_kpi_icon"></i>
                                                    <span class="fw-bold fs-7" style="color: var(--imperial-navy);">خلاصة مدة الخبرات التدريسية التراكمية:</span>
                                                </div>
                                                <span class="fs-8 fw-semibold" style="color: var(--heritage-gold-dark);">
                                                    <i class="fa-solid fa-calculator me-1"></i> احتساب تراكمي لفترات التدريس الجامعي
                                                </span>
                                            </div>
                                            <div class="row g-3" style="max-width: 760px; margin: 0 auto;">
                                                <div class="col-6 col-sm-3">
                                                    <div class="mohe-stat-tile">
                                                        <div class="mohe-stat-value" id="exp_total_years">0</div>
                                                        <div class="mohe-stat-label">سنة</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <div class="mohe-stat-tile">
                                                        <div class="mohe-stat-value" id="exp_total_months">0</div>
                                                        <div class="mohe-stat-label">شهر</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <div class="mohe-stat-tile">
                                                        <div class="mohe-stat-value" id="exp_total_days">0</div>
                                                        <div class="mohe-stat-label">يوم</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-sm-3">
                                                    <div class="mohe-stat-tile is-gold">
                                                        <div class="mohe-stat-value" id="exp_total_all_days">0</div>
                                                        <div class="mohe-stat-label">إجمالي الأيام</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ================= STEP 5: ATTACHMENTS ================= -->
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: رفع المرفقات والمستندات الثبوتية المطلوبة ({{ $isTheoreticalTrack ? '16 مرفقاً' : '13 مرفقاً' }} — PDF فقط)
                </h5>
                
                <div class="alert border-0 shadow-sm mb-4" style="background-color: var(--surface-container-low); border-right: 4px solid var(--primary-container) !important; color: var(--primary-container); border-radius: 4px;">
                    <i class="fa-solid fa-info-circle me-1" style="color: var(--heritage-gold);"></i> يرجى التأكد من رفع ملفات PDF واضحة ومصدقة أصولاً، <strong>بحجم لا يتجاوز 2 ميغابايت للوثائق الثبوتية، وحتى 5 ميغابايت لملخص الرسالة</strong> لعدم تعليق المعاملة.
                </div>

                <div class="row g-4">
                    <!-- 1. National ID / Passport (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">1. صورة عن الهوية الشخصية / جواز السفر <span class="text-danger">*</span></label>
                        <input type="file" name="file_national_id" id="input-fileNationalId" class="form-control academic-input" accept=".pdf">
                        @if(!empty($existingFiles['file_national_id']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_national_id']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 2. High School Cert (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">2. نسخة مصدقة عن الشهادة الثانوية <span class="text-danger">*</span></label>
                        <input type="file" name="file_hs_cert" id="input-fileHsCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_hs_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_hs_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- HS Equivalence Decision File (Conditional if HS not Syrian) -->
                    <div class="col-md-6" id="hs-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الثانوية غير السورية (وزارة التربية) <span class="text-danger">*</span></label>
                        <input type="file" name="hs_decision_file" id="input-hsDecisionFile" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['hs_decision_file']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['hs_decision_file']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 3. Bachelor Cert (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">3. مصدقة الإجازة الجامعية الأولى <span class="text-danger">*</span></label>
                        <input type="file" name="file_ba_cert" id="input-fileBaCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ba_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ba_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Bachelor Equivalence Decision File (Conditional if BA not Syrian) -->
                    <div class="col-md-6" id="ba-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الجامعية الأولى غير السورية <span class="text-danger">*</span></label>
                        <input type="file" name="ba_decision_file" id="input-baDecisionFile" class="form-control academic-input" accept=".pdf">

                        @php
                            $baDecisionFilePath = $existingFiles['ba_decision_file']
                                ?? $existingFiles['ba_decision']
                                ?? $existingFiles['file_ba_decision']
                                ?? null;

                            if (!$baDecisionFilePath && isset($draft) && $draft) {
                                foreach ($draft->educations as $ed) {
                                    foreach ($ed->attachments as $att) {
                                        if (($att->attachment_type_id == 4) || (str_contains($att->notes ?? '', 'قرار') && (str_contains($att->notes ?? '', 'الإجازة') || str_contains($att->notes ?? '', 'الجامعية')))) {
                                            $baDecisionFilePath = $att->file_path;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        @endphp

                        @if($baDecisionFilePath)
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً
                                </span>
                                <a href="{{ asset('storage/' . $baDecisionFilePath) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 4. Master Cert (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">4. نسخة مصدقة عن شهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="file" name="file_ma_cert" id="input-fileMaCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 5. Master Defense Decision (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">5. صورة عن قرار مجلس الدراسات العليا بالمناقشة بالماجستير <span class="text-danger">*</span></label>
                        <input type="file" name="file_ma_defense_decision" id="input-fileMaDefenseDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_defense_decision']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_defense_decision']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 6. Master Grant Decision (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">6. صورة عن قرار مجلس الجامعة بمنح درجة الماجستير <span class="text-danger">*</span></label>
                        <input type="file" name="file_ma_grant_decision" id="input-fileMaGrantDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_grant_decision']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_grant_decision']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 7. Master Registration Decision (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">7. صورة عن قرار مجلس الجامعة بالتسجيل بدرجة الماجستير <span class="text-danger">*</span></label>
                        <input type="file" name="file_ma_reg_decision" id="input-fileMaRegDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_reg_decision']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_reg_decision']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 8. Payment Receipt (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">8. إيصال تسديد رسم تعادل الماجستير (100,000 ل.س) <span class="text-danger">*</span></label>
                        <input type="file" name="file_payment" id="input-filePayment" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_payment']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_payment']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 9. CV (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">9. السيرة الذاتية للمرشح <span class="text-danger">*</span></label>
                        <input type="file" name="file_cv" id="input-fileCv" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_cv']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_cv']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 10. Arabic Thesis Summary (Mandatory, max 5MB) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">10. ملخص رسالة الماجستير باللغة العربية إلكترونياً <span class="badge bg-light text-primary border ms-1 fs-9">حتى 5MB</span> <span class="text-danger">*</span></label>
                        <input type="file" name="file_thesis_summary" id="input-fileThesisSummary" data-max-mb="5" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_thesis_summary']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_thesis_summary']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 11. University Equivalence Request (Mandatory) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">11. طلب الجامعة للتعادل (كتاب طلب التقويم الصادر عن الجامعة) <span class="text-danger">*</span></label>
                        <input type="file" name="file_uni_request" id="input-fileUniRequest" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_uni_request']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_uni_request']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 12. National Library Receipt (Optional) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">12. إيصال المكتبة الوطنية لاستلام الرسالة <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_nat_library_receipt" id="input-fileNatLibraryReceipt" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_nat_library_receipt']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_nat_library_receipt']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 13. Language Certificate (Optional) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">13. شهادة إتقان اللغة الإنكليزية المعتمدة <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_lang_cert" id="input-fileLangCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_lang_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_lang_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 14. ICDL Certificate (Optional) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">14. شهادة مهارات الحاسوب (ICDL) المعتمدة <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_icdl_cert" id="input-fileIcdlCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_icdl_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_icdl_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($isTheoreticalTrack)
                        <div class="col-12 mt-2">
                            <hr class="my-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-briefcase me-1" style="color: var(--heritage-gold);"></i> مرفقات الخبرة التدريسية الإلزامية للمسار النظري (سنتين فأكثر):</h6>
                        </div>

                        <!-- 15. Certified Teaching Contracts (Mandatory in Theoretical) -->
                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">15. صور العقود التدريسية المصدقة لمدة لا تقل عن سنتين <span class="text-danger">*</span></label>
                            <input type="file" name="file_contracts" id="input-fileContracts" class="form-control academic-input" accept=".pdf">
                            @if(isset($existingFiles['file_contracts']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFiles['file_contracts']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- 16. Certified Salary Slips / Bank Transfers (Mandatory in Theoretical) -->
                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">16. كشوفات الرواتب أو التحويلات البنكية المصدقة <span class="text-danger">*</span></label>
                            <input type="file" name="file_salary_slips" id="input-fileSalarySlips" class="form-control academic-input" accept=".pdf">
                            @if(isset($existingFiles['file_salary_slips']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFiles['file_salary_slips']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- 17. Teaching Experience Certificates (Mandatory in Theoretical) -->
                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">17. وثائق وشهادات الخبرة التدريسية <span class="text-danger">*</span></label>
                            <input type="file" name="file_exp_cert" id="input-fileExpCert" class="form-control academic-input" accept=".pdf">
                            @if(isset($existingFiles['file_exp_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFiles['file_exp_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Other Attachments (Optional) -->
                    <div class="col-12 mt-2">
                        <label class="form-label label-md fw-medium text-dark">مرفقات أخرى <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_other_attachments" id="input-fileOtherAttachments" class="form-control academic-input" accept=".pdf">
                        <span class="fs-8 text-muted d-block mt-1">يمكنك رفع أي وثائق أو مستندات داعمة إضافية بصيغة (PDF - حتى 2 ميغابايت).</span>
                        @if(isset($existingFiles['file_other_attachments']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_other_attachments']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ================= STEP 6: REVIEW & SUBMIT ================= -->
            <div class="form-section" id="step-6" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-print fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: مراجعة البيانات المدخلة وتأكيد الإرسال
                </h5>
                
                <p class="label-md text-muted mb-4">يرجى مراجعة كافة البيانات المدخلة قبل النقر على زر إنهاء الإرسال. يمكنك التعديل والرجوع لأي خطوة سابقة.</p>

                <!-- Mozhakkara Consolidated Review Report -->
                <div class="card p-4 shadow-sm border-0" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
                    <div class="text-center pb-3 mb-4 border-bottom" style="border-bottom-color: var(--outline-variant) !important;">
                        <h4 class="fw-bold mb-1" style="color: var(--primary-container);">تقرير طلب تقويم وتعادل الشهادات العلمية للمرشح</h4>
                        <div class="text-muted fw-bold label-sm">مجلس التعليم العالي - الجمهورية العربية السورية</div>
                    </div>
                    
                    <div class="row g-4 text-dark text-start" dir="rtl" style="text-align: right;">
                        <!-- Group 1: Personal Details -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-user me-1" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية للمرشح:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الاسم والكنية:</strong> <span id="preview-fullName"></span></div>
                                <div class="col-md-6"><strong>اسم الأب:</strong> <span id="preview-fatherName"></span></div>
                                <div class="col-md-6"><strong>اسم ونسبة الأم:</strong> <span id="preview-motherName"></span></div>
                                <div class="col-md-6"><strong>الرقم الوطني/جواز السفر:</strong> <span id="preview-nationalId"></span></div>
                                <div class="col-md-6"><strong>تاريخ الميلاد:</strong> <span id="preview-dob"></span></div>
                                <div class="col-md-6"><strong>الوظيفة الحالية:</strong> <span id="preview-jobTitle"></span></div>
                                <div class="col-md-6"><strong>الجنس:</strong> <span id="preview-gender"></span></div>
                                <div class="col-md-6"><strong>البريد الإلكتروني:</strong> <span id="preview-email"></span></div>
                                <div class="col-md-6"><strong>الجوال:</strong> <span id="preview-mobile"></span></div>
                                <div class="col-md-6"><strong>العنوان بالتفصيل:</strong> <span id="preview-address"></span></div>
                            </div>
                        </div>

                        <!-- Group 2: High School -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 2. بيانات الشهادة الثانوية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(2)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-hsCountry"></span></div>
                                <div class="col-md-6"><strong>نوع البكالوريا:</strong> <span id="preview-hsType"></span></div>
                                <div class="col-md-6"><strong>تاريخ الحصول عليها:</strong> <span id="preview-hsDate"></span></div>
                                <div class="col-md-6" id="preview-hsDecisionContainer"><strong>رقم قرار المعادلة السوري:</strong> <span id="preview-hsDecisionNo"></span></div>
                            </div>
                        </div>

                        <!-- Group 3: Bachelor's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> 3. بيانات الإجازة الجامعية الأولى (البكالوريوس):</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(3)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>1. الدولة المانحة:</strong> <span id="preview-baCountry"></span></div>
                                <div class="col-md-6"><strong>2. الجامعة المانحة:</strong> <span id="preview-baUni"></span></div>
                                <div class="col-md-6"><strong>3. الكلية:</strong> <span id="preview-baFaculty"></span></div>
                                <div class="col-md-6"><strong>4. القسم:</strong> <span id="preview-baDept"></span></div>
                                <div class="col-md-6"><strong>5. اختصاص:</strong> <span id="preview-baSpec"></span></div>
                                <div class="col-md-6"><strong>6. التقدير:</strong> <span id="preview-baRank"></span></div>
                                <div class="col-md-6"><strong>7. تاريخ الحصول على الدرجة:</strong> <span id="preview-baGrantDate"></span></div>
                                <div class="col-md-6" id="preview-baDecisionContainer"><strong>رقم وتاريخ قرار المعادلة:</strong> <span id="preview-baDecisionNo"></span></div>
                            </div>
                        </div>

                        <!-- Group 4: Master's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير والخبرة التدريسية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>1. الجامعة المانحة:</strong> <span id="preview-maUni"></span></div>
                                <div class="col-md-6"><strong>2. الكلية:</strong> <span id="preview-maFaculty"></span></div>
                                <div class="col-md-6"><strong>3. القسم:</strong> <span id="preview-maDept"></span></div>
                                <div class="col-md-6"><strong>4. الاختصاص العام:</strong> <span id="preview-maGenSpec"></span></div>
                                <div class="col-md-6"><strong>5. الاختصاص الدقيق:</strong> <span id="preview-maSpec"></span></div>
                                <div class="col-md-6"><strong>6. اسم الأستاذ المشرف:</strong> <span id="preview-maSupervisor"></span></div>
                                <div class="col-md-6"><strong>7. التقدير:</strong> <span id="preview-maRank"></span></div>
                                <div class="col-md-6"><strong>8. تاريخ التسجيل بالدرجة:</strong> <span id="preview-maRegDate"></span></div>
                                <div class="col-md-6"><strong>9. تاريخ المناقشة:</strong> <span id="preview-maDefDate"></span></div>
                                <div class="col-md-6"><strong>10. تاريخ منح الدرجة:</strong> <span id="preview-maGrantDate"></span></div>
                                <div class="col-12"><strong>11. عنوان رسالة الماجستير (الأطروحة):</strong> <span id="preview-maThesisTitle"></span></div>
                                
                                <!-- Experience Section in Report (Theoretical Track Only) -->
                                <div class="col-12 mt-3" id="preview-experience-container" style="display: {{ $isTheoreticalTrack ? 'block' : 'none' }};">
                                    <div class="card p-3 border rounded-2" style="background-color: var(--surface-container-low);">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <strong class="text-dark"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> بيانات الخبرة التدريسية والمسار الأكاديمي المعتمد:</strong>
                                            <span id="preview-trackBadge" class="badge bg-secondary">لم يُحدد</span>
                                        </div>
                                        <div class="mb-1"><strong>إجمالي مدة الخبرة:</strong> <span id="preview-totalExpTime" class="text-dark fw-bold">لا توجد خبرات مسجلة</span></div>
                                        <div id="preview-experiences-list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Group 5: University Evaluation Request -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 5. كتاب طلب التقويم الصادر عن الجامعة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo"></span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate"></span></div>
                            </div>
                        </div>

                        <!-- Group 6: Uploaded Documents -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-folder-open me-1" style="color: var(--heritage-gold);"></i> 6. الوثائق والمستندات المرفقة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(5)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل المرفقات</button>
                            </div>
                            <p class="text-muted fs-8 mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> تم إرفاق الوثائق المطلوبة بصيغة PDF. يمكنك النقر على زر التعديل للعودة لخطوة المرفقات.</p>
                        </div>
                    </div>
                </div>

                <!-- Final check warning confirmation -->
                <div class="form-check form-switch mt-4 p-3 border rounded d-flex align-items-center gap-3" style="background-color: var(--surface-container-low); border-color: var(--outline-variant) !important;">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required style="width: 2.2em; height: 1.2em;">
                    <label class="form-check-label fw-bold text-dark mb-0 label-md" for="chkConfirm">
                        نصادق نحن في إدارة الجامعة على صحة كافة البيانات والوثائق المرفقة أعلاه، ونتحمل المسؤولية القانونية كاملة عن أي معلومات مغلوطة.
                    </label>
                </div>
            </div>

            <!-- ================= BUTTONS NAVIGATION ================= -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 pt-3 border-top" style="border-top-color: var(--outline-variant) !important;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-navy px-4 py-2" id="btn-prev" onclick="changeStep(-1)" style="display: none;">
                        <i class="fa-solid fa-arrow-right me-1"></i> السابق
                    </button>
                    <div id="spacer-prev"></div> <!-- Spacer if step 1 -->

                    @if($canSaveDraft)
                    <button type="submit" formnovalidate name="action" value="save_draft" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة ومتابعة لاحقاً
                    </button>
                    @endif
                    @if(!empty($isAdminEdit))
                        @include('university.apply.partials._admin_edit_footer_btn')
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary px-3 py-2 fw-bold" id="btn-quick-review" onclick="quickReturnToReview()" style="display: none;" title="العودة مباشرة لخطوة المراجعة والتدقيق النهائي">
                        <i class="fa-solid fa-clipboard-check me-1"></i> العودة للمراجعة والإرسال
                    </button>

                    <button type="button" class="btn btn-primary px-4 py-2" id="btn-next" onclick="changeStep(1)">
                        التالي <i class="fa-solid fa-arrow-left ms-1"></i>
                    </button>

                    <button type="submit" formnovalidate name="action" value="submit_final" class="btn btn-gold-cta px-5 py-2" id="btn-submit" style="display: none;">
                        @if(!empty($isAdminEdit))
                            حفظ كافة التعديلات (دون تغيير الحالة) <i class="fa-solid fa-check-double ms-1"></i>
                        @elseif($isAwaitingDocs)
                            إعادة إرسال الطلب بعد استكمال الوثائق <i class="fa-solid fa-paper-plane ms-1"></i>
                        @else
                            إنهاء وإرسال الطلب للوزارة <i class="fa-solid fa-paper-plane ms-1"></i>
                        @endif
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const IS_ADMIN_EDIT = @json(!empty($isAdminEdit));
    let currentStep = 1;
    const totalSteps = 6;
    const syriaCountryId = "{{ $syriaId }}";
    const trackType = "{{ $trackType }}";
    let hasVisitedReview = false;

    function togglePreviousDegree(select) {
        const section = document.getElementById('previous-degree-section');
        const parentSelect = document.getElementById('input-parentAppId');
        if (!select || !section) return;
        if (select.value === '0') {
            section.style.display = 'block';
            if (parentSelect) parentSelect.required = true;
        } else {
            section.style.display = 'none';
            if (parentSelect) {
                parentSelect.required = false;
                parentSelect.value = '';
            }
        }
    }

    function quickReturnToReview() {
        if (typeof validateAllStepsUpTo === 'function') {
            if (validateAllStepsUpTo(totalSteps, true)) {
                goToStep(totalSteps);
            }
        } else {
            goToStep(totalSteps);
        }
    }

    // ==========================================
    // MULTI-PERIOD TEACHING EXPERIENCE LOGIC
    // ==========================================
    let experienceCounter = {{ $draftExperiences->count() }};

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function getDetailedStay(entryDateStr, exitDateStr) {
        const inD = new Date(entryDateStr);
        const outD = new Date(exitDateStr);
        if (isNaN(inD.getTime()) || isNaN(outD.getTime()) || outD < inD) {
            return { years: 0, months: 0, days: 0, totalDays: 0 };
        }
        const diffTime = Math.abs(outD - inD);
        const totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        let y = outD.getFullYear() - inD.getFullYear();
        let m = outD.getMonth() - inD.getMonth();
        let d = outD.getDate() - inD.getDate();

        if (d < 0) {
            m -= 1;
            const prevMonthLastDay = new Date(outD.getFullYear(), outD.getMonth(), 0).getDate();
            d += prevMonthLastDay;
        }
        if (m < 0) {
            y -= 1;
            m += 12;
        }

        return { years: y, months: m, days: d, totalDays: totalDays };
    }

    function toggleExperienceBlock(show) {
        const wrapper = document.getElementById('experience-entry-wrapper');
        if (wrapper) wrapper.style.display = show ? 'block' : 'none';
        if (!show) {
            const instEl = document.getElementById('exp_institution');
            const roleEl = document.getElementById('exp_role');
            const startEl = document.getElementById('exp_start_date');
            const endEl = document.getElementById('exp_end_date');
            const notesEl = document.getElementById('exp_notes');
            if (instEl) instEl.value = '';
            if (roleEl) roleEl.value = '';
            if (startEl) startEl.value = '';
            if (endEl) endEl.value = '';
            if (notesEl) notesEl.value = '';
        }
        recalcTotalExperience();
    }

    function toggleExperienceSection(chk) {
        toggleExperienceBlock(chk ? chk.checked : false);
    }

    function addExperiencePeriod() {
        const instEl = document.getElementById('exp_institution');
        const roleEl = document.getElementById('exp_role');
        const startEl = document.getElementById('exp_start_date');
        const endEl = document.getElementById('exp_end_date');
        const notesEl = document.getElementById('exp_notes');

        const institution = instEl ? instEl.value.trim() : '';
        const role = roleEl ? roleEl.value.trim() : '';
        const startDate = startEl ? startEl.value : '';
        const endDate = endEl ? endEl.value : '';
        const notes = notesEl ? notesEl.value.trim() : '';

        if (!institution) {
            alert('يرجى إدخال اسم الجهة أو الجامعة والكلية لفترة الخبرة التدريسية.');
            if (instEl) instEl.focus();
            return;
        }
        if (!startDate || !endDate) {
            alert('يرجى تحديد تاريخ البدء وتاريخ الانتهاء لفترة الخبرة.');
            return;
        }

        const inD = new Date(startDate);
        const outD = new Date(endDate);
        if (outD < inD) {
            alert('تاريخ انتهاء فترة الخبرة يجب أن يكون مساوياً أو بعد تاريخ البدء.');
            return;
        }

        const stay = getDetailedStay(startDate, endDate);
        experienceCounter++;

        const tbody = document.getElementById('experiences_tbody');
        const noRow = document.getElementById('no_experiences_row');
        if (noRow) noRow.remove();

        const tr = document.createElement('tr');
        tr.setAttribute('data-days', stay.totalDays);
        tr.innerHTML = `
            <td class="fw-bold text-muted">${tbody.children.length + 1}</td>
            <td class="text-start pe-3"><strong style="color: var(--imperial-navy);">${escapeHtml(institution)}</strong></td>
            <td class="text-secondary">${escapeHtml(role) || '---'}</td>
            <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">${startDate}</span></td>
            <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">${endDate}</span></td>
            <td>
                <span class="mohe-duration-chip">
                    <i class="fa-regular fa-clock"></i>
                    ${stay.years} سنة و ${stay.months} شهر و ${stay.days} يوم
                </span>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger mohe-action-icon-btn" onclick="deleteExperiencePeriod(this)" title="حذف الفترة"><i class="fa-solid fa-trash-can"></i></button>
                <input type="hidden" name="experiences[${experienceCounter}][institution_name]" value="${escapeHtml(institution)}">
                <input type="hidden" name="experiences[${experienceCounter}][role_or_subject]" value="${escapeHtml(role)}">
                <input type="hidden" name="experiences[${experienceCounter}][start_date]" value="${startDate}">
                <input type="hidden" name="experiences[${experienceCounter}][end_date]" value="${endDate}">
                <input type="hidden" name="experiences[${experienceCounter}][duration_days]" value="${stay.totalDays}">
                <input type="hidden" name="experiences[${experienceCounter}][notes]" value="${escapeHtml(notes)}">
            </td>
        `;
        tbody.appendChild(tr);

        if (instEl) instEl.value = '';
        if (roleEl) roleEl.value = '';
        if (startEl) startEl.value = '';
        if (endEl) endEl.value = '';
        if (notesEl) notesEl.value = '';

        recalcTotalExperience();
    }

    function deleteExperiencePeriod(btn) {
        const row = btn.closest('tr');
        if (row) row.remove();
        const tbody = document.getElementById('experiences_tbody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr id="no_experiences_row"><td colspan="7" class="text-muted py-4">لم يتم تسجيل أي فترات خبرة تدريسية بعد. يمكنك إدخال الفترات من النموذج أعلاه والضغط على (إضافة) لإدراجها بالجدول.</td></tr>';
        } else {
            Array.from(tbody.children).forEach((r, idx) => {
                if (r.children[0]) r.children[0].textContent = idx + 1;
            });
        }
        recalcTotalExperience();
    }

    function recalcTotalExperience() {
        if (trackType !== 'theoretical') {
            return { totalDays: 0, isTheoretical: false, years: 0, months: 0, days: 0 };
        }

        const tbody = document.getElementById('experiences_tbody');
        let totalDays = 0;

        if (tbody) {
            tbody.querySelectorAll('tr').forEach(tr => {
                const startInput = tr.querySelector('input[name*="[start_date]"]');
                const endInput = tr.querySelector('input[name*="[end_date]"]');
                if (startInput && endInput && startInput.value && endInput.value) {
                    const stay = getDetailedStay(startInput.value, endInput.value);
                    totalDays += stay.totalDays;
                }
            });
        }

        const years = Math.floor(totalDays / 365);
        const months = Math.floor((totalDays % 365) / 30);
        const days = (totalDays % 365) % 30;

        const yEl = document.getElementById('exp_total_years');
        const mEl = document.getElementById('exp_total_months');
        const dEl = document.getElementById('exp_total_days');
        const allEl = document.getElementById('exp_total_all_days');
        if (yEl) yEl.textContent = years;
        if (mEl) mEl.textContent = months;
        if (dEl) dEl.textContent = days;
        if (allEl) allEl.textContent = totalDays;

        const isTheoretical = (totalDays >= 730);

        return { totalDays, isTheoretical, years, months, days };
    }

    // Toggle High School Decision inputs
    function toggleHsCountrySection(select, isInitial = false) {
        const section = document.getElementById('hs-equivalence-section');
        const inputNo = document.getElementById('input-hsDecisionNo');
        const inputDate = document.getElementById('input-hsDecisionDate');
        const fileContainer = document.getElementById('hs-decision-file-container');
        const fileInput = fileContainer ? fileContainer.querySelector('input') : null;
        const hasExistingHsFile = {{ isset($existingFiles['hs_decision_file']) ? 'true' : 'false' }};

        if (select && select.value != syriaCountryId) {
            if (section) section.style.display = 'block';
            if (inputNo) inputNo.required = true;
            if (inputDate) inputDate.required = true;
            if (fileContainer) fileContainer.style.display = 'block';
            if (fileInput) fileInput.required = !hasExistingHsFile;
        } else {
            if (section) section.style.display = 'none';
            if (inputNo) { inputNo.required = false; if (!isInitial) inputNo.value = ''; }
            if (inputDate) { inputDate.required = false; if (!isInitial) inputDate.value = ''; }
            if (fileContainer) fileContainer.style.display = 'none';
            if (fileInput) { fileInput.required = false; }
        }
    }

    // Toggle Bachelor's Decision inputs
    function toggleBaCountrySection(select, isInitial = false) {
        const selectContainer = document.getElementById('ba-uni-select-container');
        const textContainer = document.getElementById('ba-uni-text-container');
        const section = document.getElementById('ba-equivalence-section');
        const inputNo = document.getElementById('input-baDecisionNo');
        const inputDate = document.getElementById('input-baDecisionDate');
        const fileContainer = document.getElementById('ba-decision-file-container');
        const fileInput = fileContainer ? fileContainer.querySelector('input') : null;
        const hasExistingBaFile = {{ isset($existingFiles['ba_decision_file']) ? 'true' : 'false' }};

        if (select && select.value == syriaCountryId) {
            if (selectContainer) {
                selectContainer.style.display = 'block';
                const s = selectContainer.querySelector('select');
                if (s) s.required = true;
            }
            if (textContainer) {
                textContainer.style.display = 'none';
                const i = textContainer.querySelector('input');
                if (i) { i.required = false; if (!isInitial) i.value = ''; }
            }
            if (section) section.style.display = 'none';
            if (inputNo) { inputNo.required = false; if (!isInitial) inputNo.value = ''; }
            if (inputDate) { inputDate.required = false; if (!isInitial) inputDate.value = ''; }
            if (fileContainer) fileContainer.style.display = 'none';
            if (fileInput) { fileInput.required = false; }
        } else if (select) {
            if (selectContainer) {
                selectContainer.style.display = 'none';
                const s = selectContainer.querySelector('select');
                if (s) { s.required = false; if (!isInitial) s.value = ''; }
            }
            if (textContainer) {
                textContainer.style.display = 'block';
                const i = textContainer.querySelector('input');
                if (i) i.required = true;
            }
            if (section) section.style.display = 'block';
            if (inputNo) inputNo.required = true;
            if (inputDate) inputDate.required = true;
            if (fileContainer) fileContainer.style.display = 'block';
            if (fileInput) fileInput.required = !hasExistingBaFile;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hsSelect = document.getElementById('input-hsCountry');
        if (hsSelect) toggleHsCountrySection(hsSelect, true);

        const baSelect = document.getElementById('input-baCountry');
        if (baSelect) toggleBaCountrySection(baSelect, true);

        const isFirstTimeSelect = document.getElementById('input-isFirstTime');
        if (isFirstTimeSelect) togglePreviousDegree(isFirstTimeSelect);

        recalcTotalExperience();
    });

    function updateSyrianStatus(select) {
        const inputIsSyrian = document.getElementById('input-isSyrian');
        if (select.value == syriaCountryId) {
            inputIsSyrian.value = '1';
        } else {
            inputIsSyrian.value = '0';
        }
    }

    // Modular step validation
    function validateStep(step, showNotice = true) {
        if (IS_ADMIN_EDIT) return true;
        const currentSection = document.getElementById(`step-${step}`);
        if (!currentSection) return true;

        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        for (const input of inputs) {
            if (!input.checkValidity()) {
                if (showNotice) {
                    input.reportValidity();
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    input.focus();
                }
                return false;
            }
        }

        const todayStr = new Date().toISOString().split('T')[0];

        if (step === 1) {
            const nationalIdInput = document.getElementById('input-nationalId');
            if (nationalIdInput) {
                const nationalIdVal = nationalIdInput.value.trim();
                if (!/^[0-9]{11}$/.test(nationalIdVal)) {
                    if (showNotice) {
                        nationalIdInput.setCustomValidity('الرقم الوطني يجب أن يتألف من 11 رقماً حصراً (أرقام فقط دون فراغات).');
                        nationalIdInput.reportValidity();
                        nationalIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        nationalIdInput.focus();
                    }
                    return false;
                } else {
                    nationalIdInput.setCustomValidity('');
                }
            }

            const mobileInput = document.getElementById('input-mobile');
            if (mobileInput) {
                const mobileVal = mobileInput.value.trim();
                if (!/^[0-9]{10}$/.test(mobileVal)) {
                    if (showNotice) {
                        mobileInput.setCustomValidity('رقم الهاتف المحمول يجب أن يكون مكوناً من 10 أرقام (مثال: 0991168727).');
                        mobileInput.reportValidity();
                        mobileInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        mobileInput.focus();
                    }
                    return false;
                } else {
                    mobileInput.setCustomValidity('');
                }
            }

            const phoneInput = document.getElementById('input-phone');
            if (phoneInput && phoneInput.value.trim() !== '') {
                const phoneVal = phoneInput.value.trim();
                if (!/^[0-9]{10}$/.test(phoneVal)) {
                    if (showNotice) {
                        phoneInput.setCustomValidity('رقم الهاتف الأرضي يجب أن يكون مكوناً من 10 أرقام (مثال: 0113414768).');
                        phoneInput.reportValidity();
                        phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        phoneInput.focus();
                    }
                    return false;
                } else {
                    phoneInput.setCustomValidity('');
                }
            }

            const emailInput = document.getElementById('input-email');
            if (emailInput) {
                const emailVal = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailVal)) {
                    if (showNotice) {
                        emailInput.setCustomValidity('يرجى إدخال عنوان بريد إلكتروني صحيح (مثال: example@domain.com).');
                        emailInput.reportValidity();
                        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        emailInput.focus();
                    }
                    return false;
                } else {
                    emailInput.setCustomValidity('');
                }
            }
        } else if (step === 2) {
            const hsCountry = document.getElementById('input-hsCountry');
            if (hsCountry && hsCountry.value != syriaCountryId) {
                const decNo = document.getElementById('input-hsDecisionNo');
                const decDate = document.getElementById('input-hsDecisionDate');
                if (decNo && !decNo.value.trim()) {
                    if (showNotice) {
                        decNo.setCustomValidity('يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية للمتابعة.');
                        decNo.reportValidity();
                        decNo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decNo.focus();
                    }
                    return false;
                } else if (decNo) {
                    decNo.setCustomValidity('');
                }

                if (decDate && !decDate.value) {
                    if (showNotice) {
                        decDate.setCustomValidity('يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية للمتابعة.');
                        decDate.reportValidity();
                        decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decDate.focus();
                    }
                    return false;
                } else if (decDate) {
                    decDate.setCustomValidity('');
                }
            }
        } else if (step === 3) {
            const baCountry = document.getElementById('input-baCountry');
            if (baCountry && baCountry.value != syriaCountryId) {
                const decNo = document.getElementById('input-baDecisionNo');
                const decDate = document.getElementById('input-baDecisionDate');
                if (decNo && !decNo.value.trim()) {
                    if (showNotice) {
                        decNo.setCustomValidity('يرجى إدخال رقم قرار تعادل الإجازة الجامعية الأولى غير السورية للمتابعة.');
                        decNo.reportValidity();
                        decNo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decNo.focus();
                    }
                    return false;
                } else if (decNo) {
                    decNo.setCustomValidity('');
                }

                if (decDate && !decDate.value) {
                    if (showNotice) {
                        decDate.setCustomValidity('يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية الأولى غير السورية للمتابعة.');
                        decDate.reportValidity();
                        decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decDate.focus();
                    }
                    return false;
                } else if (decDate) {
                    decDate.setCustomValidity('');
                }
            }

            const baGrantInput = document.getElementById('input-baGrantDate');
            if (baGrantInput && baGrantInput.value) {
                if (baGrantInput.value > todayStr) {
                    if (showNotice) {
                        baGrantInput.setCustomValidity('تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.');
                        baGrantInput.reportValidity();
                        baGrantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        baGrantInput.focus();
                    }
                    return false;
                } else {
                    baGrantInput.setCustomValidity('');
                }
            }
        } else if (step === 4) {
            const maRegInput = document.getElementById('input-maRegDate');
            const maDefInput = document.getElementById('input-maDefDate');
            const maGrantInput = document.getElementById('input-maGrantDate');

            if (maRegInput && maDefInput && maRegInput.value && maDefInput.value) {
                if (maDefInput.value <= maRegInput.value) {
                    if (showNotice) {
                        maDefInput.setCustomValidity('تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل بالدرجة.');
                        maDefInput.reportValidity();
                        maDefInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        maDefInput.focus();
                    }
                    return false;
                } else {
                    maDefInput.setCustomValidity('');
                }
            }

            if (maDefInput && maGrantInput && maDefInput.value && maGrantInput.value) {
                if (maGrantInput.value <= maDefInput.value) {
                    if (showNotice) {
                        maGrantInput.setCustomValidity('تاريخ منح الدرجة (الحصول على الشهادة) يجب أن يكون بعد تاريخ المناقشة.');
                        maGrantInput.reportValidity();
                        maGrantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        maGrantInput.focus();
                    }
                    return false;
                } else if (maGrantInput.value > todayStr) {
                    if (showNotice) {
                        maGrantInput.setCustomValidity('تاريخ منح الدرجة (الحصول على الشهادة) يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.');
                        maGrantInput.reportValidity();
                        maGrantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        maGrantInput.focus();
                    }
                    return false;
                } else {
                    maGrantInput.setCustomValidity('');
                }
            }

            if (trackType === 'theoretical') {
                const tbody = document.getElementById('experiences_tbody');
                let hasRows = tbody && tbody.querySelectorAll('tr:not(#no_experiences_row)').length > 0;
                const instEl = document.getElementById('exp_institution');
                const startEl = document.getElementById('exp_start_date');
                const endEl = document.getElementById('exp_end_date');

                // If user entered details in inputs but forgot to click Add (+)
                if (!hasRows && instEl && instEl.value.trim() && startEl && startEl.value && endEl && endEl.value) {
                    addExperiencePeriod();
                    hasRows = tbody && tbody.querySelectorAll('tr:not(#no_experiences_row)').length > 0;
                }

                const expStats = recalcTotalExperience();
                if (!hasRows) {
                    if (showNotice) {
                        alert('في مسار الماجستير النظري، يجب إدخال فترة خبرة تدريسية واحدة على الأقل (بمجموع لا يقل عن سنتين / 730 يوماً) ثم الضغط على (إضافة).');
                        if (instEl) {
                            instEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            instEl.focus();
                        }
                    }
                    return false;
                }
                if (expStats.totalDays < 730) {
                    if (showNotice) {
                        alert(`مجموع مدة الخبرة التدريسية المسجلة حالياً هو (${expStats.totalDays}) يوماً، بينما يشترط المسار النظري خبرة تدريسية لا تقل عن سنتين (730 يوماً فأكثر). يرجى إضافة فترات خبرة إضافية أو التقديم عبر مسار الماجستير التطبيقي.`);
                        const expContainer = document.getElementById('experience-section-container');
                        if (expContainer) expContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            }
        } else if (step === 5) {
            const requiredAttachments = [
                { id: 'input-fileNationalId', name: 'صورة عن الهوية الشخصية / جواز السفر' },
                { id: 'input-fileHsCert', name: 'نسخة مصدقة عن الشهادة الثانوية' },
                { id: 'input-fileBaCert', name: 'مصدقة الإجازة الجامعية الأولى' },
                { id: 'input-fileMaCert', name: 'نسخة مصدقة عن شهادة الماجستير' },
                { id: 'input-fileMaDefenseDecision', name: 'صورة عن قرار مجلس الدراسات العليا بالمناقشة بالماجستير' },
                { id: 'input-fileMaGrantDecision', name: 'صورة عن قرار مجلس الجامعة بمنح درجة الماجستير' },
                { id: 'input-fileMaRegDecision', name: 'صورة عن قرار مجلس الجامعة بالتسجيل بدرجة الماجستير' },
                { id: 'input-filePayment', name: 'إيصال تسديد رسم تعادل الماجستير' },
                { id: 'input-fileCv', name: 'السيرة الذاتية للمرشح' },
                { id: 'input-fileThesisSummary', name: 'ملخص رسالة الماجستير باللغة العربية' },
                { id: 'input-fileUniRequest', name: 'طلب الجامعة للتعادل (كتاب طلب التقويم)' }
            ];

            const hsCountry = document.getElementById('input-hsCountry');
            const hsDecisionNo = document.getElementById('input-hsDecisionNo');
            const hsDecisionDate = document.getElementById('input-hsDecisionDate');
            const hsFileContainer = document.getElementById('hs-decision-file-container');
            const isHsAlreadyUploaded = hsFileContainer && hsFileContainer.querySelector('.badge.bg-success-subtle') !== null;
            if (!isHsAlreadyUploaded && ((hsCountry && hsCountry.value != syriaCountryId) || (hsDecisionNo && hsDecisionNo.value.trim() !== '') || (hsDecisionDate && hsDecisionDate.value !== ''))) {
                requiredAttachments.push({ id: 'input-hsDecisionFile', name: 'قرار معادلة الشهادة الثانوية غير السورية' });
            }

            const baCountry = document.getElementById('input-baCountry');
            const baDecisionNo = document.getElementById('input-baDecisionNo');
            const baDecisionDate = document.getElementById('input-baDecisionDate');
            const baFileContainer = document.getElementById('ba-decision-file-container');
            const isBaAlreadyUploaded = baFileContainer && (
                baFileContainer.querySelector('.badge.bg-success-subtle') !== null ||
                baFileContainer.querySelector('a') !== null
            );

            if (!isBaAlreadyUploaded && ((baCountry && baCountry.value != syriaCountryId) || (baDecisionNo && baDecisionNo.value.trim() !== '') || (baDecisionDate && baDecisionDate.value !== ''))) {
                requiredAttachments.push({ id: 'input-baDecisionFile', name: 'قرار معادلة الإجازة الجامعية غير السورية' });
            }

            if (trackType === 'theoretical') {
                requiredAttachments.push({ id: 'input-fileContracts', name: 'صور العقود التدريسية المصدقة لمدة لا تقل عن سنتين' });
                requiredAttachments.push({ id: 'input-fileSalarySlips', name: 'كشوفات الرواتب أو التحويلات البنكية المصدقة' });
                requiredAttachments.push({ id: 'input-fileExpCert', name: 'وثائق وشهادات الخبرة التدريسية' });
            }

            for (const att of requiredAttachments) {
                const inputEl = document.getElementById(att.id);
                if (inputEl) {
                    const hasFile = inputEl.files && inputEl.files.length > 0;
                    const parentContainer = inputEl.closest('.col-md-6, .col-12');
                    const isAlreadyUploaded = parentContainer && !!parentContainer.querySelector('.badge.bg-success-subtle');

                    if (!hasFile && !isAlreadyUploaded) {
                        if (showNotice) {
                            inputEl.setCustomValidity(`يرجى رفع ملف (${att.name}) بصيغة PDF للمتابعة.`);
                            inputEl.reportValidity();
                            inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            inputEl.focus();
                        }
                        return false;
                    } else {
                        inputEl.setCustomValidity('');
                    }
                }
            }
        }

        return true;
    }

    function validateAllStepsUpTo(targetStep, showNotice = true) {
        for (let s = 1; s < targetStep; s++) {
            if (!validateStep(s, false)) {
                goToStep(s);
                if (showNotice) {
                    setTimeout(() => {
                        validateStep(s, true);
                    }, 150);
                }
                return false;
            }
        }
        return true;
    }

    // Step navigation
    function changeStep(direction) {
        if (direction === 1) {
            if (currentStep >= totalSteps) return;
            if (!validateStep(currentStep, true)) return;
        } else if (direction === -1) {
            if (currentStep <= 1) return;
        }
        goToStep(currentStep + direction);
    }

    function updateWizardProgress() {
        // Update Progress Bar
        const barWidth = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('wizard-progress-bar').style.width = `${barWidth}%`;

        // Update Circle indicators
        const steps = document.querySelectorAll('#wizard-steps-container .wizard-step');
        steps.forEach((stepDiv, index) => {
            const stepNum = index + 1;
            stepDiv.classList.remove('active', 'completed');
            if (stepNum === currentStep) {
                stepDiv.classList.add('active');
            } else if (stepNum < currentStep) {
                stepDiv.classList.add('completed');
            }
        });

        // Update Buttons
        const btnPrev = document.getElementById('btn-prev');
        const spacerPrev = document.getElementById('spacer-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');
        const btnQuickReview = document.getElementById('btn-quick-review');

        if (currentStep === 1) {
            btnPrev.style.display = 'none';
            spacerPrev.style.display = 'block';
        } else {
            btnPrev.style.display = 'block';
            spacerPrev.style.display = 'none';
        }

        if (currentStep === totalSteps) {
            hasVisitedReview = true;
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-block';
            if (btnQuickReview) btnQuickReview.style.display = 'none';
        } else {
            btnNext.style.display = 'inline-block';
            btnSubmit.style.display = 'none';
            if (btnQuickReview) {
                btnQuickReview.style.display = hasVisitedReview ? 'inline-block' : 'none';
            }
        }
    }

    function goToStep(step) {
        if (step >= 1 && step <= totalSteps) {
            // Hide all steps
            for (let i = 1; i <= totalSteps; i++) {
                const sec = document.getElementById(`step-${i}`);
                if (sec) {
                    sec.style.display = 'none';
                    sec.classList.remove('active');
                }
            }

            currentStep = step;

            // Show target step
            const targetSec = document.getElementById(`step-${currentStep}`);
            if (targetSec) {
                targetSec.style.display = 'block';
                targetSec.classList.add('active');
            }

            // Update indicators and buttons FIRST!
            updateWizardProgress();

            // If entering final review step, update preview safely
            if (currentStep === totalSteps) {
                try {
                    updateReportPreview();
                } catch (err) {
                    console.error('Error updating report preview:', err);
                }
            }

            // Scroll smooth to wizard top
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }
    }
    window.goToStep = goToStep;

    function formatDateDisplay(val) {
        if (!val || val === '-') return '-';
        const match = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return `${match[3]}/${match[2]}/${match[1]}`;
        }
        return val;
    }

    function updateReportPreview() {
        const getVal = id => {
            const el = document.getElementById(id);
            if (!el) return '-';
            if (el.tagName === 'SELECT') {
                return (el.options && el.selectedIndex >= 0 && el.options[el.selectedIndex])
                    ? el.options[el.selectedIndex].text
                    : '-';
            }
            if (el.type === 'checkbox') return el.checked;
            return el.value && el.value.trim() ? el.value.trim() : '-';
        };

        const setTxt = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = (val !== null && val !== undefined && val !== '') ? val : '-';
        };

        // Personal details
        setTxt('preview-fullName', getVal('input-fullName'));
        setTxt('preview-fatherName', getVal('input-fatherName'));
        setTxt('preview-motherName', getVal('input-motherName'));
        setTxt('preview-nationalId', getVal('input-nationalId'));
        setTxt('preview-dob', formatDateDisplay(getVal('input-dob')));
        setTxt('preview-jobTitle', getVal('input-jobTitle'));
        setTxt('preview-gender', getVal('input-gender'));
        setTxt('preview-email', getVal('input-email'));
        setTxt('preview-mobile', getVal('input-mobile'));
        setTxt('preview-address', getVal('input-address'));

        // HS details
        const hsCountrySelect = document.getElementById('input-hsCountry');
        setTxt('preview-hsCountry', getVal('input-hsCountry'));
        setTxt('preview-hsType', getVal('input-hsType'));
        setTxt('preview-hsDate', formatDateDisplay(getVal('input-hsDate')));
        
        const hsDecContainer = document.getElementById('preview-hsDecisionContainer');
        if (hsCountrySelect && hsCountrySelect.value && hsCountrySelect.value != syriaCountryId) {
            if (hsDecContainer) hsDecContainer.style.display = 'block';
            setTxt('preview-hsDecisionNo', getVal('input-hsDecisionNo'));
        } else {
            if (hsDecContainer) hsDecContainer.style.display = 'none';
        }

        // BA details
        const baCountrySelect = document.getElementById('input-baCountry');
        setTxt('preview-baCountry', getVal('input-baCountry'));
        
        const baDecContainer = document.getElementById('preview-baDecisionContainer');
        const baUniTxt = getVal('input-baUniText');
        const baUniDisplay = (baUniTxt && baUniTxt !== '-') ? baUniTxt : (getVal('input-baUniId') !== '-' ? getVal('input-baUniId') : getVal('input-baUniOther'));
        setTxt('preview-baUni', baUniDisplay);

        if (baCountrySelect && baCountrySelect.value == syriaCountryId) {
            if (baDecContainer) baDecContainer.style.display = 'none';
        } else {
            if (baDecContainer) baDecContainer.style.display = 'block';
            setTxt('preview-baDecisionNo', getVal('input-baDecisionNo'));
        }
        setTxt('preview-baFaculty', getVal('input-baFaculty'));
        setTxt('preview-baDept', getVal('input-baDept'));
        setTxt('preview-baSpec', getVal('input-baSpec'));
        setTxt('preview-baRank', getVal('input-baRank'));
        setTxt('preview-baGrantDate', formatDateDisplay(getVal('input-baGrantDate')));

        // MA details
        setTxt('preview-maUni', getVal('input-maUniId'));
        setTxt('preview-maFaculty', getVal('input-maFaculty'));
        setTxt('preview-maDept', getVal('input-maDept'));
        setTxt('preview-maGenSpec', getVal('input-maGenSpec'));
        setTxt('preview-maSpec', getVal('input-maSpec'));
        setTxt('preview-maSupervisor', getVal('input-maSupervisor'));
        setTxt('preview-maRank', getVal('input-maRank'));
        setTxt('preview-maRegDate', formatDateDisplay(getVal('input-maRegDate')));
        setTxt('preview-maDefDate', formatDateDisplay(getVal('input-maDefDate')));
        setTxt('preview-maGrantDate', formatDateDisplay(getVal('input-maGrantDate')));
        setTxt('preview-maThesisTitle', getVal('input-maThesisTitle'));

        // Experience (Only in Theoretical Track)
        const expContainer = document.getElementById('preview-experience-container');
        if (trackType === 'theoretical') {
            if (expContainer) expContainer.style.display = 'block';
            const stats = recalcTotalExperience();
            const badgeEl = document.getElementById('preview-trackBadge');
            const totalEl = document.getElementById('preview-totalExpTime');
            const listEl = document.getElementById('preview-experiences-list');

            if (badgeEl) {
                if (stats.isTheoretical) {
                    badgeEl.className = 'badge bg-success-subtle text-success border border-success px-3 py-1';
                    badgeEl.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ماجستير نظري (مستوفي شرط السنتين ≥ 730 يوماً)';
                } else {
                    badgeEl.className = 'badge bg-warning-subtle text-dark border border-warning px-3 py-1';
                    badgeEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> غير مستوفٍ شرط السنتين بعد';
                }
            }
            if (totalEl) {
                totalEl.textContent = stats.totalDays > 0
                    ? `${stats.years} سنة و ${stats.months} شهر و ${stats.days} يوم (إجمالي ${stats.totalDays} يوماً)`
                    : 'لا توجد فترات خبرة تدريسية مسجلة';
            }
            if (listEl) {
                const tbody = document.getElementById('experiences_tbody');
                const rows = tbody ? tbody.querySelectorAll('tr:not(#no_experiences_row)') : [];
                if (rows.length > 0) {
                    let itemsHtml = '<ul class="mb-0 mt-2 ps-3 fs-8 text-dark">';
                    rows.forEach(r => {
                        const inst = r.children[1] ? r.children[1].textContent.trim() : '';
                        const role = r.children[2] ? r.children[2].textContent.trim() : '';
                        const fromD = r.children[3] ? r.children[3].textContent.trim() : '';
                        const toD = r.children[4] ? r.children[4].textContent.trim() : '';
                        const dur = r.children[5] ? r.children[5].textContent.trim() : '';
                        itemsHtml += `<li><strong>${inst}</strong> ${role !== '---' ? '(' + role + ')' : ''}: من ${fromD} إلى ${toD} [${dur}]</li>`;
                    });
                    itemsHtml += '</ul>';
                    listEl.innerHTML = itemsHtml;
                } else {
                    listEl.innerHTML = '';
                }
            }
        } else {
            if (expContainer) expContainer.style.display = 'none';
        }

        // Request
        setTxt('preview-reqNo', getVal('input-reqNo'));
        setTxt('preview-reqDate', formatDateDisplay(getVal('input-reqDate')));
    }

    function toggleCandidateLookupBox(show) {
        const box = document.getElementById('candidate_lookup_box');
        if (box) {
            if (show) {
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        }
    }

    function performCandidateLookup() {
        const input = document.getElementById('candidate_search_input');
        const area = document.getElementById('lookup_results_area');
        if (!input || !area) return;

        const query = input.value.trim();
        if (query.length < 2) {
            area.innerHTML = '<div class="alert alert-warning py-1.5 px-3 fs-8 mt-2">يرجى كتابة الرقم الوطني للمرشح للاستعلام.</div>';
            return;
        }

        area.innerHTML = '<div class="text-center py-2 text-muted fs-8"><i class="fa-solid fa-spinner fa-spin me-1"></i> جاري الاستعلام بالرقم الوطني في قاعدة البيانات...</div>';

        fetch(`{{ route('university.candidate.lookup') }}?national_id=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.candidate) {
                    area.innerHTML = `<div class="alert alert-info py-1.5 px-3 fs-8 mt-2">${data.message || 'لم يتم العثور على أي مرشح مسجل سابقاً بهذا الرقم الوطني.'}</div>`;
                    return;
                }

                const c = data.candidate;
                const hs = data.high_school;
                const ba = data.bachelor;
                const ma = data.master;

                if (data.draft_id) {
                    const draftInput = document.querySelector('input[name="draft_id"]');
                    if (draftInput && !draftInput.value) {
                        draftInput.value = data.draft_id;
                    }
                }

                // Auto-fill Step 1 (Personal Info)
                if (c.full_name) document.getElementById('input-fullName').value = c.full_name;
                if (c.father_name) document.getElementById('input-fatherName').value = c.father_name;
                if (c.mother_name) document.getElementById('input-motherName').value = c.mother_name;
                if (c.national_id) document.getElementById('input-nationalId').value = c.national_id;
                if (c.dob) document.getElementById('input-dob').value = c.dob;
                if (c.job_title) document.getElementById('input-jobTitle').value = c.job_title;
                if (c.gender) document.getElementById('input-gender').value = c.gender;
                if (c.email) document.getElementById('input-email').value = c.email;
                if (c.mobile) document.getElementById('input-mobile').value = c.mobile;
                if (c.phone) document.getElementById('input-phone').value = c.phone;
                if (c.address) document.getElementById('input-address').value = c.address;
                if (c.nationality_id) {
                    document.getElementById('input-nationality').value = c.nationality_id;
                    updateSyrianStatus(document.getElementById('input-nationality'));
                }

                // Auto-fill Step 2 (High School)
                if (hs) {
                    if (hs.country_id) {
                        const hsCountry = document.getElementById('input-hsCountry');
                        if (hsCountry) {
                            hsCountry.value = hs.country_id;
                            toggleHsCountrySection(hsCountry);
                        }
                    }
                    if (hs.type) document.getElementById('input-hsType').value = hs.type;
                    if (hs.grant_date) document.getElementById('input-hsDate').value = hs.grant_date;
                    if (hs.decision_no) document.getElementById('input-hsDecisionNo').value = hs.decision_no;
                }

                // Auto-fill Step 3 (Bachelor's Degree)
                if (ba) {
                    if (ba.country_id) {
                        const baCountry = document.getElementById('input-baCountry');
                        if (baCountry) {
                            baCountry.value = ba.country_id;
                            toggleBaCountrySection(baCountry);
                        }
                    }
                    if (ba.university_id) document.getElementById('input-baUniId').value = ba.university_id;
                    if (ba.university_other) document.getElementById('input-baUniOther').value = ba.university_other;
                    if (ba.faculty) document.getElementById('input-baFaculty').value = ba.faculty;
                    if (ba.department) document.getElementById('input-baDept').value = ba.department;
                    if (ba.registration_date) document.getElementById('input-baRegDate').value = ba.registration_date;
                    if (ba.grant_date) document.getElementById('input-baGrantDate').value = ba.grant_date;
                    if (ba.rank) document.getElementById('input-baRank').value = ba.rank;
                    if (ba.decision_no) document.getElementById('input-baDecisionNo').value = ba.decision_no;
                }

                // Auto-fill Step 4 (Master's Degree)
                if (ma) {
                    if (ma.university_id) document.getElementById('input-maUniId').value = ma.university_id;
                    if (ma.faculty) document.getElementById('input-maFaculty').value = ma.faculty;
                    if (ma.department) document.getElementById('input-maDept').value = ma.department;
                    if (ma.registration_date) document.getElementById('input-maRegDate').value = ma.registration_date;
                    if (ma.defense_date) document.getElementById('input-maDefDate').value = ma.defense_date;
                    if (ma.grant_date) document.getElementById('input-maGrantDate').value = ma.grant_date;
                    if (ma.rank) document.getElementById('input-maRank').value = ma.rank;
                    if (ma.supervisor) document.getElementById('input-maSupervisor').value = ma.supervisor;
                    if (ma.thesis_title) document.getElementById('input-maThesisTitle').value = ma.thesis_title;
                    checkMasterGrantDateForExperience();
                }

                let draftNotice = '';
                if (data.draft_id) {
                    draftNotice = `<br><span class="badge bg-warning text-dark mt-1 fs-8"><i class="fa-solid fa-floppy-disk me-1"></i> توجد مسودة سابقة محفوظة لهذا المرشح (#Draft-${data.draft_id}). سيتم تحديث بياناتها تلقائياً دون تكرار.</span>`;
                }

                area.innerHTML = `
                    <div class="alert alert-success py-2 px-3 fs-8 mt-2 shadow-sm border-0" style="background-color: #E6F4EA; color: #137333;">
                        <i class="fa-solid fa-circle-check fs-6 me-1.5" style="color: #137333;"></i>
                        <strong>تم الاستعلام والتعبئة بنجاح بالرقم الوطني (${c.national_id}):</strong><br>
                        تم جلب وتعبئة البيانات الشخصية والمؤهلات العلمية السابقة للمرشح (<strong>${c.full_name}</strong>) تلقائياً عبر جميع الخطوات! يمكنك الضغط على "التالي" لمتابعة الخطوات ومراجعة أو إضافة مرفقات جديدة.
                        ${draftNotice}
                    </div>
                `;
            })
            .catch(err => {
                area.innerHTML = '<div class="alert alert-danger py-1.5 px-3 fs-8 mt-2">حدث خطأ أثناء إجراء عملية الاستعلام. يرجى التأكد من الرقم الوطني وإعادة المحاولة.</div>';
            });
    }

    function checkMasterGrantDateForExperience() {
        recalcTotalExperience();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hsCountry = document.getElementById('input-hsCountry');
        if (hsCountry) toggleHsCountrySection(hsCountry);

        const baCountry = document.getElementById('input-baCountry');
        if (baCountry) toggleBaCountrySection(baCountry);

        recalcTotalExperience();

        @php
            $initialStep = 1;
            if ($errors->any()) {
                $firstErrorKey = $errors->keys()[0] ?? '';
                if (str_starts_with($firstErrorKey, 'hs_')) {
                    $initialStep = 2;
                } elseif (str_starts_with($firstErrorKey, 'ba_')) {
                    $initialStep = 3;
                } elseif (str_starts_with($firstErrorKey, 'ma_') || str_starts_with($firstErrorKey, 'exp_') || $firstErrorKey === 'has_experience' || str_starts_with($firstErrorKey, 'experiences')) {
                    $initialStep = 4;
                } elseif (str_starts_with($firstErrorKey, 'file_') || str_ends_with($firstErrorKey, '_file')) {
                    $initialStep = 5;
                }
            } elseif (request()->filled('step')) {
                $initialStep = (int)request('step');
            }
        @endphp

        const initialStep = {{ $initialStep }};
        goToStep(initialStep);

        // Make stepper circles clickable
        document.querySelectorAll('#wizard-steps-container .wizard-step').forEach(el => {
            el.style.cursor = 'pointer';
            el.addEventListener('click', function() {
                const targetStep = parseInt(this.getAttribute('data-step'));
                if (targetStep) {
                    if (targetStep > currentStep) {
                        if (validateAllStepsUpTo(targetStep, true)) {
                            goToStep(targetStep);
                        }
                    } else if (targetStep < currentStep) {
                        goToStep(targetStep);
                    }
                }
            });
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                this.setCustomValidity('');
            });
        });

        const form = document.getElementById('wizard-form');
        let isSubmitting = false;

        if (form) {
            form.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (IS_ADMIN_EDIT || (submitter && (submitter.value === 'save_draft' || submitter.value === 'admin_save'))) {
                    form.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('required'));
                    isSubmitting = true;
                    return true;
                }

                // Final submit validation: verify all steps 1 through 5
                if (!validateAllStepsUpTo(totalSteps, true)) {
                    e.preventDefault();
                    isSubmitting = false;
                    return false;
                }

                const chkConfirm = document.getElementById('chkConfirm');
                if (chkConfirm && !chkConfirm.checked) {
                    e.preventDefault();
                    isSubmitting = false;
                    goToStep(6);
                    chkConfirm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    chkConfirm.focus();
                    chkConfirm.setCustomValidity('يرجى المصادقة على الإقرار بصحة البيانات للمتابعة.');
                    chkConfirm.reportValidity();
                    return false;
                } else if (chkConfirm) {
                    chkConfirm.setCustomValidity('');
                }

                isSubmitting = true;
                // Remove required from all inputs to ensure smooth and guaranteed submission
                form.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('required'));
                return true;
            });
        }
    });
</script>
@endpush

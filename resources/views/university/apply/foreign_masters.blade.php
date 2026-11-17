@extends(!empty($isAdminEdit) ? 'layouts.admin' : 'layouts.university')

@php
    $trackType = (isset($trackType) && in_array($trackType, ['applied', 'theoretical'], true)) ? $trackType : 'applied';
    $isTheoreticalTrack = ($trackType === 'theoretical');
    $trackLabel = $isTheoreticalTrack ? 'ماجستير خارجي (مسار نظري)' : 'ماجستير خارجي (مسار تطبيقي)';
    $submitRoute = !empty($isAdminEdit)
        ? $adminSubmitRoute
        : ($isTheoreticalTrack
            ? route('university.apply.foreign_masters_theoretical.submit')
            : route('university.apply.foreign_masters_applied.submit'));
    $isResExempt = (bool) old('is_residence_exempt', optional($draft)->is_residence_exempt ?? false);
@endphp

@section('title', 'معاملة تعادل ' . $trackLabel)

@section('content')

@php
    $draftCandidate = optional(optional($draft)->candidate);
    $draftNatId = $draftCandidate->national_id ?? '';
    if ($draftNatId && str_starts_with($draftNatId, 'TMP-')) {
        $draftNatId = '';
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
<!-- PAGE TITLE & BREADCRUMB -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}">نوع المعاملة</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">{{ $trackLabel }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid {{ $isTheoreticalTrack ? 'fa-globe' : 'fa-earth-americas' }} me-2" style="color: var(--heritage-gold);"></i> معاملة تعادل {{ $trackLabel }}
                </h3>
                <p class="body-md text-muted mb-0">
                    @if($isTheoreticalTrack)
                        لتعادل درجة الماجستير الصادرة عن الجامعات غير السورية لتدريس المقررات النظرية (يتطلب خبرة تدريسية سنتين فأكثر مثبتة بعقود وإيصالات وشهادة خبرة + إثبات الإقامة أو الإعفاء).
                    @else
                        لتعادل درجة الماجستير الصادرة عن الجامعات غير السورية لتدريس الجوانب التطبيقية (بدون شرط خبرة السنتين، مع حركات الإقامة أو الإعفاء للتخصصات النظرية).
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canSaveDraft)
                <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" onclick="triggerSaveDraft()" title="حفظ البيانات المدخلة كمسودة للعودة إليها لاحقاً برقم الطلب">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ كمسودة ومتابعة لاحقاً</span>
                </button>
                @endif
                @if($draft)
                    @if($isAwaitingDocs)
                        <span class="badge bg-warning text-dark px-3 py-2 border border-warning fs-7 fw-bold">
                            <i class="fa-solid fa-file-circle-exclamation me-1"></i> بانتظار استكمال الوثائق: #{{ $draft->application_no }}
                        </span>
                    @else
                        <span class="badge px-3 py-2 border fs-7 fw-bold" style="background-color: #FAF6EE; color: #8A651E; border-color: #D9C394 !important;">
                            <i class="fa-solid fa-file-pen me-1"></i> تعديل مسودة طلب: #{{ $draft->application_no }}
                        </span>
                    @endif
                @endif
            </div>
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
    .mohe-exempt-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-right: 4px solid var(--heritage-gold);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(15, 29, 51, 0.04);
    }
    .mohe-exempt-switch-box {
        background: #f8fafc;
        border: 1px solid #dce3ec;
        border-radius: 8px;
        padding: 10px 16px;
        min-width: 265px;
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
    .mohe-context-bar {
        background: linear-gradient(135deg, #f8fafc 0%, #f3f6fb 100%);
        border: 1px solid #d5dce6;
        border-right: 4px solid var(--imperial-navy);
        border-radius: 8px;
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

        <!-- WIZARD PROGRESS STEPPERS -->
        <div class="wizard-steps-container mb-5">
            <div class="wizard-steps">
                <div class="wizard-step active" data-step="1" id="step-node-1">
                    <div class="wizard-icon"><i class="fa-solid fa-user"></i></div>
                    <div class="wizard-label">1. المعلومات الشخصية</div>
                </div>
                <div class="wizard-step" data-step="2" id="step-node-2">
                    <div class="wizard-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="wizard-label">2. الشهادة الثانوية</div>
                </div>
                <div class="wizard-step" data-step="3" id="step-node-3">
                    <div class="wizard-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="wizard-label">3. الإجازة الجامعية</div>
                </div>
                <div class="wizard-step" data-step="4" id="step-node-4">
                    <div class="wizard-icon"><i class="fa-solid fa-award"></i></div>
                    <div class="wizard-label">{{ $isTheoreticalTrack ? '4. الماجستير والخبرة' : '4. الماجستير الخارجي' }}</div>
                </div>
                <div class="wizard-step" data-step="5" id="step-node-5">
                    <div class="wizard-icon"><i class="fa-solid fa-passport"></i></div>
                    <div class="wizard-label">5. حركات الإقامة والسفر</div>
                </div>
                <div class="wizard-step" data-step="6" id="step-node-6">
                    <div class="wizard-icon"><i class="fa-solid fa-paperclip"></i></div>
                    <div class="wizard-label">6. المرفقات المطلوبة</div>
                </div>
                <div class="wizard-step" data-step="7" id="step-node-7">
                    <div class="wizard-icon"><i class="fa-solid fa-print"></i></div>
                    <div class="wizard-label">7. المراجعة والإرسال</div>
                </div>
            </div>
        </div>

        <!-- Form Tag -->
        <form action="{{ $submitRoute }}" method="POST" enctype="multipart/form-data" id="wizard-form" novalidate>
            @csrf
            <input type="hidden" name="wizard_track" value="{{ $trackType }}">
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">
            <input type="hidden" name="action" id="form-action-input" value="submit_final">
            <input type="hidden" name="is_draft" id="form-is-draft-input" value="0">

            {{-- =========================================================================
                 STEP 1: PERSONAL DETAILS & UNIVERSITY EVALUATION REQUEST
            ========================================================================= --}}
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب طلب التقييم الصادر عن الجامعة
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الاسم والكنية <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control academic-input" placeholder="الاسم الثلاثي مع الكنية" value="{{ old('full_name', $draftCandidate->full_name ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">اسم الأب <span class="text-danger">*</span></label>
                        <input type="text" name="father_name" class="form-control academic-input" placeholder="اسم الوالد" value="{{ old('father_name', $draftCandidate->father_name ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">اسم الأم ونسبتها <span class="text-danger">*</span></label>
                        <input type="text" name="mother_name" class="form-control academic-input" placeholder="اسم الوالدة ونسبتها" value="{{ old('mother_name', $draftCandidate->mother_name ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الرقم الوطني / رقم جواز السفر <span class="text-danger">*</span></label>
                        <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="الرقم الوطني المكون من 11 خانة" value="{{ old('national_id', $draftNatId) }}" maxlength="20" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الجنسية <span class="text-danger">*</span></label>
                        <select name="nationality_id" class="form-select academic-input" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('nationality_id', $draftCandidate->nationality_id ?? 1) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ الميلاد <span class="text-danger">*</span></label>
                        <input type="date" name="dob" class="form-control academic-input" value="{{ old('dob', $draftCandidate->dob ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الجنس <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select academic-input" required>
                            <option value="ذكر" {{ old('gender', $draftCandidate->gender ?? '') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                            <option value="أنثى" {{ old('gender', $draftCandidate->gender ?? '') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">رقم الهاتف الأرضي</label>
                        <input type="text" name="phone" class="form-control academic-input" placeholder="مثال: 011-2345678" value="{{ old('phone', $draftCandidate->phone ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">رقم الموبايل الشخصي <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control academic-input" placeholder="مثال: 0912345678" value="{{ old('mobile', $draftCandidate->mobile ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', $draftCandidate->email ?: (empty($isAdminEdit) ? (Auth::user()?->university?->email ?? Auth::user()?->email) : '')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">عنوان الإقامة الحالي في سوريا <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control academic-input" placeholder="المدينة - المنطقة - الشارع" value="{{ old('address', $draftCandidate->address ?? '') }}" required>
                    </div>
                </div>

                <h6 class="fw-bold mt-4 mb-3 border-bottom pb-2" style="color: var(--primary-container);">
                    <i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> بيانات كتاب ترشيح الجامعة الخاصة
                </h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">رقم كتاب الجامعة الخاصة <span class="text-danger">*</span></label>
                        <input type="text" name="req_no" class="form-control academic-input" placeholder="مثال: 124" value="{{ old('req_no', optional($draft)->new_uni_request_no ?? '') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">تاريخ كتاب الجامعة الخاصة <span class="text-danger">*</span></label>
                        <input type="date" name="req_date" class="form-control academic-input" value="{{ old('req_date', optional($draft)->new_uni_request_date ?? '') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">الكلية المرشح للتدريس فيها بالجامعة الخاصة</label>
                        <input type="text" name="work_faculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعمارية" value="{{ old('work_faculty', optional($draft)->work_faculty ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">القسم المرشح للتدريس فيه بالجامعة الخاصة</label>
                        <input type="text" name="work_department" class="form-control academic-input" placeholder="مثال: قسم التصميم المعماري" value="{{ old('work_department', optional($draft)->work_department ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 2: HIGH SCHOOL DETAILS
            ========================================================================= --}}
            <div class="form-section" id="step-2" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات الشهادة الثانوية (البكالوريا)
                </h5>

                @php
                    $draftHsEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 4)->first() : null;
                    $hsCountryId = $draftHsEd ? $draftHsEd->country_id : ($syriaId ?? 1);
                    $hsType = $draftHsEd ? $draftHsEd->section_name : '';
                    $hsYear = $draftHsEd ? substr((string) $draftHsEd->grant_date, 0, 4) : '';
                    $hsDecNo = '';
                    $hsDecDate = '';
                    if ($draftHsEd && $draftHsEd->notes) {
                        if (preg_match('/رقم قرار المعادلة الثانوية:\s*([^\|]+)/u', $draftHsEd->notes, $hm)) {
                            $hsDecNo = trim($hm[1]);
                        }
                        if (preg_match('/تاريخ القرار:\s*([^\|]+)/u', $draftHsEd->notes, $dm)) {
                            $hsDecDate = trim($dm[1]);
                        }
                    }
                    $isHsForeign = old('hs_country_id', $hsCountryId) != ($syriaId ?? 1);
                @endphp

                <div class="row g-3" id="hs_fields_row">
                    <div class="col-md-4" id="hs_country_col">
                        <label class="form-label label-sm fw-bold">بلد الحصول على الشهادة الثانوية <span class="text-danger">*</span></label>
                        <select name="hs_country_id" id="hs_country_id" class="form-select academic-input" onchange="toggleHsDecision(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('hs_country_id', $hsCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="hs_type_col">
                        <label class="form-label label-sm fw-bold">فرع الشهادة الثانوية <span class="text-danger">*</span></label>
                        <select name="hs_type" class="form-select academic-input" required>
                            <option value="علمي" {{ old('hs_type', $hsType) == 'علمي' ? 'selected' : '' }}>علمي</option>
                            <option value="أدبي" {{ old('hs_type', $hsType) == 'أدبي' ? 'selected' : '' }}>أدبي</option>
                            <option value="شرعي" {{ old('hs_type', $hsType) == 'شرعي' ? 'selected' : '' }}>شرعي</option>
                            <option value="صناعي" {{ old('hs_type', $hsType) == 'صناعي' ? 'selected' : '' }}>صناعي</option>
                            <option value="تجاري" {{ old('hs_type', $hsType) == 'تجاري' ? 'selected' : '' }}>تجاري</option>
                            <option value="نسوي" {{ old('hs_type', $hsType) == 'نسوي' ? 'selected' : '' }}>نسوي</option>
                            <option value="أخرى" {{ old('hs_type', $hsType) == 'أخرى' ? 'selected' : '' }}>أخرى (شهادة ثانوية خارجية)</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="hs_year_col">
                        <label class="form-label label-sm fw-bold">سنة الحصول على الشهادة الثانوية <span class="text-danger">*</span></label>
                        <input type="number" name="hs_grant_date" class="form-control academic-input" placeholder="مثال: 2012" min="1950" max="{{ date('Y') }}" value="{{ old('hs_grant_date', $hsYear) }}" required>
                    </div>

                    <div class="col-12" id="hs_decision_box" style="display: {{ $isHsForeign ? 'block' : 'none' }};">
                        <div class="row g-3 p-3 bg-light rounded border border-warning">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">رقم قرار معادلة الشهادة الثانوية غير السورية <span class="text-danger" id="hs_dec_req_badge">*</span></label>
                                <input type="text" name="hs_decision_no" id="hs_decision_no" class="form-control academic-input" placeholder="أدخل رقم قرار المعادلة الصادر عن وزارة التربية" value="{{ old('hs_decision_no', $hsDecNo) }}" {{ $isHsForeign ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">تاريخ صدور قرار معادلة الشهادة الثانوية <span class="text-danger" id="hs_date_req_badge">*</span></label>
                                <input type="date" name="hs_decision_date" id="hs_decision_date" class="form-control academic-input" value="{{ old('hs_decision_date', $hsDecDate) }}" {{ $isHsForeign ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 3: BACHELOR'S DEGREE DETAILS
            ========================================================================= --}}
            <div class="form-section" id="step-3" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: بيانات الإجازة الجامعية الأولى (البكالوريوس)
                </h5>

                @php
                    $draftBaEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 1)->first() : null;
                    $baCountryId = old('ba_country_id', $draftBaEd ? $draftBaEd->country_id : ($syriaId ?? 1));
                    $savedBaUniId = old('ba_university_id', $draftBaEd ? $draftBaEd->university_id : '');
                    $baUniOther = $draftBaEd ? ($draftBaEd->university_other ?: optional($draftBaEd->university)->name) : '';
                    $baUniValue = old('ba_university_other', optional($draftBaEd)->university ? $draftBaEd->university->name : $baUniOther);
                    $isBaSyria = ($baCountryId == ($syriaId ?? 1));
                    $isBaOtherSelected = ($savedBaUniId === 'other') || ($isBaSyria && empty($savedBaUniId) && !empty($baUniValue));
                    if ($isBaOtherSelected) {
                        $savedBaUniId = 'other';
                    }
                    $baFaculty = $draftBaEd ? ($draftBaEd->general_specialization ?: $draftBaEd->faculty) : '';
                    $baDept = $draftBaEd ? ($draftBaEd->exact_specialization ?: $draftBaEd->department) : '';
                    $baSpec = $draftBaEd ? $draftBaEd->section_name : '';
                    $baRegDate = $draftBaEd ? $draftBaEd->registration_date : '';
                    $baGrantDate = $draftBaEd ? $draftBaEd->grant_date : '';
                    $baRank = $draftBaEd ? $draftBaEd->rank : '';
                @endphp

                <div class="row g-3" id="ba_first_row">
                    <div class="col-md-4" id="ba_country_col">
                        <label class="form-label label-sm fw-bold">1. بلد الإجازة الجامعية الأولى (الدولة المانحة) <span class="text-danger">*</span></label>
                        <select name="ba_country_id" id="ba_country_id" class="form-select academic-input" onchange="toggleBaCountry(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ $baCountryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="ba_uni_select_col" style="display: {{ $isBaSyria ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. الجامعة المانحة للإجازة (سوريا) <span class="text-danger">*</span></label>
                        <select name="ba_university_id" id="ba_university_id" class="form-select academic-input" onchange="toggleBaUniOther(this.value)" {{ $isBaSyria ? 'required' : '' }}>
                            @include('university.apply.partials._syrian_university_optgroups', [
                                'selectedId' => $savedBaUniId,
                                'isOther' => $isBaOtherSelected,
                                'publicUnis' => $syrianPublicUnis,
                                'privateUnis' => $syrianPrivateUnis,
                                'institutes' => $syrianInstitutes,
                                'fallbackUni' => optional($draftBaEd)->university,
                            ])
                        </select>
                    </div>

                    <div class="col-md-4" id="ba_uni_other_col" style="display: {{ (!$isBaSyria || $isBaOtherSelected) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. {{ $isBaSyria ? 'اسم الجامعة (يدوياً)' : 'الجامعة المانحة للإجازة الجامعية' }} <span class="text-danger">*</span></label>
                        <input type="text" name="ba_university_other" id="ba_university_other" class="form-control academic-input" placeholder="أدخل اسم الجامعة المانحة للإجازة (مثال: جامعة القاهرة)" value="{{ $baUniValue }}" {{ (!$isBaSyria || $isBaOtherSelected) ? 'required' : '' }}>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">3. الكلية المانحة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: كلية العلوم" value="{{ old('ba_faculty', $baFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">4. القسم لدرجة الإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: قسم الكيمياء" value="{{ old('ba_department', $baDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">5. اختصاص <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ba_specialization" id="input-baSpec" class="form-control academic-input" placeholder="مثال: كيمياء تطبيقية" value="{{ old('ba_specialization', $baSpec) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">6. التقدير <span class="text-danger">*</span></label>
                        <select name="ba_rank" id="input-baRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ (old('ba_rank', $baRank) == 'ممتاز' || old('ba_rank', $baRank) == 'امتياز') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ old('ba_rank', $baRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ba_rank', $baRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ old('ba_rank', $baRank) == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">7. تاريخ الحصول على الدرجة (تاريخ المنح) <span class="text-danger">*</span></label>
                        <input type="date" name="ba_grant_date" id="input-baGrantDate" class="form-control academic-input" value="{{ old('ba_grant_date', $baGrantDate) }}" required>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 4: FOREIGN MASTER'S DEGREE & SYRIAN TEACHING EXPERIENCE
            ========================================================================= --}}
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-award fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: بيانات درجة الماجستير الخارجي والخبرة التدريسية
                </h5>

                @php
                    $draftMaEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 2)->first() : null;
                    $maCountryId = $draftMaEd ? $draftMaEd->country_id : null;
                    $maUniId = $draftMaEd ? $draftMaEd->university_id : '';
                    $maUniOther = $draftMaEd ? ($draftMaEd->university_other ?: optional($draftMaEd->university)->name) : '';
                    $maFaculty = $draftMaEd ? $draftMaEd->faculty : '';
                    $maDept = $draftMaEd ? $draftMaEd->department : '';
                    $maSpec = $draftMaEd ? ($draftMaEd->exact_specialization ?: $draftMaEd->section_name) : '';
                    $maStudySystem = $draftMaEd ? $draftMaEd->study_system : 'فصلي / سنوي';
                    $maLang = $draftMaEd ? $draftMaEd->study_language : 'العربية';
                    $maDuration = $draftMaEd ? $draftMaEd->duration_years : '2';
                    $maRegDate = $draftMaEd ? $draftMaEd->registration_date : '';
                    $maDefDate = $draftMaEd ? $draftMaEd->defense_date : '';
                    $maGrantDate = $draftMaEd ? $draftMaEd->grant_date : '';
                    $maRank = $draftMaEd ? $draftMaEd->rank : 'جيد جداً';
                    $maThesis = $draftMaEd ? $draftMaEd->thesis_title : '';
                    $maSupervisor = $draftMaEd ? $draftMaEd->supervisor_name : '';

                    // Experience details (Determines applied vs theoretical)
                    $draftExperiences = ($draft && $draft->experiences) ? $draft->experiences : collect();
                    if ($draftExperiences->isEmpty() && $draftMaEd && $draftMaEd->experience_from_year && $draftMaEd->experience_to_year) {
                        $sYear = is_numeric($draftMaEd->experience_from_year) && strlen($draftMaEd->experience_from_year) == 4 ? ($draftMaEd->experience_from_year . '-01-01') : $draftMaEd->experience_from_year;
                        $eYear = is_numeric($draftMaEd->experience_to_year) && strlen($draftMaEd->experience_to_year) == 4 ? ($draftMaEd->experience_to_year . '-12-31') : $draftMaEd->experience_to_year;
                        $sDate = \Carbon\Carbon::parse($sYear);
                        $eDate = \Carbon\Carbon::parse($eYear);
                        $draftExperiences = collect([(object)[
                            'id' => 1,
                            'institution_name' => ($draftMaEd->notes && preg_match('/جامعات الخبرة:\s*([^\|]+)/u', $draftMaEd->notes, $m)) ? trim($m[1]) : 'جامعة سورية',
                            'role_or_subject' => 'تدريس جامعي',
                            'start_date' => $sDate->format('Y-m-d'),
                            'end_date' => $eDate->format('Y-m-d'),
                            'duration_days' => max(0, $sDate->diffInDays($eDate)),
                            'notes' => null,
                        ]]);
                    }
                    $hasInitialExp = $draftExperiences->isNotEmpty() || old('has_syrian_experience') == 'yes';
                    $hasExp = $hasInitialExp ? 'yes' : 'no';
                    $expYears = 2;
                    $expFrom = '';
                    $expTo = '';
                    $expUnis = '';
                @endphp

                <div class="row g-3" id="ma_first_row">
                    <!-- 1. بلد الدراسة والجامعة المانحة -->
                    <div class="col-md-6" id="ma_country_col">
                        <label class="form-label label-sm fw-bold">بلد الحصول على درجة الماجستير (بلد الدراسة) <span class="text-danger">*</span></label>
                        <select name="ma_country_id" id="ma_country_id" class="form-select academic-input" onchange="toggleMaCountry(this.value)" required>
                            <option value="">-- اختر بلد دراسة الماجستير (خارجي) --</option>
                            @foreach($countries as $c)
                                @if($c->id != ($syriaId ?? 1) && $c->name !== 'سوريا')
                                    <option value="{{ $c->id }}" {{ old('ma_country_id', $maCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="ma_university_col">
                        <label class="form-label label-sm fw-bold">1. الجامعة الخارجية المانحة للماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_university_other" id="ma_university_other" class="form-control academic-input" placeholder="اسم الجامعة الخارجية (مثال: جامعة بيروت العربية / جامعة القاهرة)" value="{{ old('ma_university_other', $maUniOther) }}" required>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <!-- 2. الكلية -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">2. الكلية المانحة لدرجة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية / كلية الآداب" value="{{ old('ma_faculty', $maFaculty) }}" required>
                    </div>

                    <!-- 3. القسم -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">3. القسم لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="مثال: قسم هندسة البرمجيات" value="{{ old('ma_department', $maDept) }}" required>
                    </div>

                    <!-- 4. الاختصاص العام -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">4. الاختصاص العام لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_general_specialization" id="input-maGenSpec" class="form-control academic-input" placeholder="مثال: علوم الحاسب ونظم المعلومات" value="{{ old('ma_general_specialization', optional($draftMaEd)->general_specialization ?: $maFaculty) }}" required>
                    </div>

                    <!-- 5. الاختصاص الدقيق -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">5. الاختصاص الدقيق لشهادة الماجستير <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ma_specialization" id="input-maSpec" class="form-control academic-input" placeholder="مثال: النمذجة والمحاكاة الحاسوبية / أمن البيانات" value="{{ old('ma_specialization', $maSpec) }}">
                    </div>

                    <!-- 6. اسم الأستاذ المشرف -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">6. اسم الأستاذ المشرف على رسالة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_supervisor" id="input-maSupervisor" class="form-control academic-input" placeholder="مثال: أ.د. فلان الفلاني" value="{{ old('ma_supervisor', $maSupervisor) }}" required>
                    </div>

                    <!-- 7. التقدير (ممتاز - جيد جداً - جيد) -->
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">7. التقدير / المرتبة <span class="text-danger">*</span></label>
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ (old('ma_rank', $maRank) == 'ممتاز' || old('ma_rank', $maRank) == 'امتياز' || old('ma_rank', $maRank) == 'شرف') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ old('ma_rank', $maRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ma_rank', $maRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                        </select>
                    </div>

                    <!-- 8. تاريخ التسجيل -->
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">8. تاريخ التسجيل بالدرجة <span class="text-danger">*</span></label>
                        <input type="date" name="ma_registration_date" id="input-maRegDate" class="form-control academic-input" value="{{ old('ma_registration_date', $maRegDate) }}" required>
                    </div>

                    <!-- 9. تاريخ المناقشة -->
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">9. تاريخ المناقشة <span class="text-danger">*</span></label>
                        <input type="date" name="ma_defense_date" id="input-maDefDate" class="form-control academic-input" value="{{ old('ma_defense_date', $maDefDate) }}" required>
                    </div>

                    <!-- 10. تاريخ منح الدرجة -->
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">10. تاريخ منح الدرجة <span class="text-danger">*</span></label>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" value="{{ old('ma_grant_date', $maGrantDate) }}" required>
                    </div>

                    <!-- 11. عنوان رسالة الماجستير (الأطروحة) بالتفصيل -->
                    <div class="col-12">
                        <label class="form-label label-sm fw-bold">11. عنوان رسالة الماجستير (الأطروحة) بالتفصيل <span class="text-danger">*</span></label>
                        <textarea name="ma_thesis_title" id="input-maThesisTitle" class="form-control academic-input" rows="2" placeholder="أدخل عنوان رسالة الماجستير كاملاً كما هو مذكور في الشهادة الخارجية أو الأطروحة" required>{{ old('ma_thesis_title', $maThesis) }}</textarea>
                    </div>

                    <!-- نظام الدراسة ولغة الدراسة ومدة الدراسة -->
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">نظام الدراسة بالماجستير</label>
                        <select name="ma_study_system" class="form-select academic-input">
                            <option value="سنوي" {{ old('ma_study_system', $maStudySystem) == 'سنوي' ? 'selected' : '' }}>سنوي</option>
                            <option value="فصلي" {{ old('ma_study_system', $maStudySystem) == 'فصلي' ? 'selected' : '' }}>فصلي</option>
                            <option value="ساعات معتمدة" {{ old('ma_study_system', $maStudySystem) == 'ساعات معتمدة' ? 'selected' : '' }}>ساعات معتمدة (Credit Hours)</option>
                            <option value="بحث وأطروحة فقط" {{ old('ma_study_system', $maStudySystem) == 'بحث وأطروحة فقط' ? 'selected' : '' }}>بحث وأطروحة فقط</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">لغة الدراسة</label>
                        <input type="text" name="ma_study_language" class="form-control academic-input" placeholder="مثال: العربية / الإنكليزية / الفرنسية" value="{{ old('ma_study_language', $maLang) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">المدة المقررة للدراسة (سنوات)</label>
                        <input type="number" name="ma_duration_years" class="form-control academic-input" min="1" max="10" value="{{ old('ma_duration_years', $maDuration) }}">
                    </div>
                </div>

                {{-- =========================================================================
                     RESIDENCE EXEMPTION TOGGLE (هل الماجستير معفى من شرط الإقامة؟)
                ========================================================================= --}}
                {{-- خيار الإعفاء من شرط الإقامة (تصميم رسمي أنيق) --}}
                <div class="mohe-exempt-card mt-4 p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3" style="max-width: 700px;">
                            <div class="mohe-emblem-box mt-0.5">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                    <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                                        هل شهادة الماجستير مأخوذة في تخصص نظري ومعفاة من شرط الإقامة؟
                                    </h6>
                                    <span class="badge rounded-pill px-2.5 py-1 fs-9 fw-semibold" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                                        دراسة عن بُعد / أونلاين
                                    </span>
                                </div>
                                <p class="text-muted fs-8 mb-2 lh-base">
                                    يُفعّل هذا الخيار حصراً للتخصصات النظرية المعفاة من الإقامة الفعلية (مثل ماجستير اللغة العربية أو اللغة الفرنسية أو التخصصات النظرية عن بُعد).
                                </p>
                                <div class="d-flex flex-wrap gap-2 fs-9">
                                    <span class="px-2.5 py-1 rounded" style="background: #f0f9f4; color: #14532d; border: 1px solid #bbf7d0;">
                                        <i class="fa-solid fa-check me-1"></i> عند التفعيل: حركات الإقامة وجواز السفر وبيان الحركة اختيارية
                                    </span>
                                    <span class="px-2.5 py-1 rounded" style="background: #f8fafc; color: #475569; border: 1px solid #dce3ec;">
                                        <i class="fa-solid fa-building-columns me-1"></i> التخصصات التطبيقية والطبية: ملزمة بالإقامة الفعلية
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mohe-exempt-switch-box d-flex flex-column align-items-start gap-2">
                            <span class="fs-9 fw-semibold text-uppercase" style="color: #64748b; letter-spacing: 0.3px;">الوضع المعتمد لشرط الإقامة</span>
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0" type="checkbox" id="input-isResidenceExempt" name="is_residence_exempt" value="1" {{ $isResExempt ? 'checked' : '' }} onchange="syncResidenceExempt(this.checked)" style="width: 2.75em; height: 1.4em; cursor: pointer;">
                                <label class="form-check-label fw-bold fs-8 mb-0 {{ $isResExempt ? 'text-success' : 'text-dark' }}" for="input-isResidenceExempt" id="lbl-residenceExemptStep4" style="cursor: pointer;">
                                    {{ $isResExempt ? 'معفى من شرط الإقامة (دراسة نظرية / أونلاين)' : 'يتطلب حضوراً وإقامة فعلية' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!$isTheoreticalTrack)
                    {{-- مسار الماجستير التطبيقي الخارجي: لا يتطلب خبرة تدريسية لمدة سنتين --}}
                    <input type="hidden" name="has_syrian_experience" id="has_syrian_experience" value="no">
                    <div class="mohe-context-bar mt-4 mb-0 p-3 d-flex align-items-center gap-3">
                        <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-briefcase fs-7"></i>
                        </div>
                        <div class="fs-7 text-dark">
                            <strong style="color: var(--imperial-navy);">مسار الماجستير الخارجي (التطبيقي):</strong> مخصص للمرشحين الذين لا يملكون خبرة تدريسية لمدة سنتين فأكثر في سوريا، ويُعتمد لتدريس الجوانب التطبيقية (رسم التعادل: <strong>100,000 ل.س</strong>).
                        </div>
                    </div>
                @else
                {{-- =========================================================================
                     EXPERIENCE SECTION (الماجستير النظري الخارجي: خبرة تدريسية سنتين فأكثر إجبارية)
                ========================================================================= --}}
                <input type="hidden" name="experiences_submitted_flag" value="1">
                <input type="hidden" name="has_syrian_experience" id="has_syrian_experience" value="yes">
                <div class="mohe-official-panel mt-4">
                    <div class="mohe-panel-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="mohe-emblem-box">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                                    سجل الخبرة التدريسية داخل الجامعات السورية
                                </h6>
                                <p class="text-muted fs-8 mb-0 mt-0.5">
                                    يشترط مسار <strong>(ماجستير خارجي - نظري)</strong> خبرة تدريسية تراكمية لا تقل عن <strong>سنتين (730 يوماً فأكثر)</strong> مثبتة بعقود وإيصالات تحويل وشهادة خبرة (رسم التعادل: <strong>125,000 ل.س</strong>).
                                </p>
                            </div>
                        </div>
                        <span class="badge px-3 py-1.5 fs-8 fw-semibold" style="background-color: #fbf8f1; color: var(--heritage-gold-dark); border: 1px solid #dec99c;">
                            <i class="fa-solid fa-award me-1"></i> الحد الأدنى: 730 يوماً
                        </span>
                    </div>

                    <div class="p-3 p-md-4">
                        <div id="experience_details_container" style="display: block;">
                            <!-- Add Period Form -->
                            <div class="mohe-subform-box mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                                    <i class="fa-solid fa-plus-circle fs-7" style="color: var(--heritage-gold-dark);"></i>
                                    <span class="fw-bold fs-8" style="color: var(--imperial-navy);">إدراج فترة خبرة تدريسية جديدة</span>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label label-sm fw-bold text-dark">الجهة / الجامعة والكلية في سوريا <span class="text-danger">*</span></label>
                                        <input type="text" id="exp_institution" class="form-control academic-input bg-white" placeholder="مثال: جامعة دمشق - كلية الهندسة">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label label-sm fw-bold text-dark">الصفة التدريسية / المقرر <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                                        <input type="text" id="exp_role" class="form-control academic-input bg-white" placeholder="مثال: تدريس مقررات نظرية">
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
                                                <th style="width: 26%;"><i class="fa-solid fa-building-columns me-1 text-warning opacity-75"></i> الجامعة / الكلية في سوريا</th>
                                                <th style="width: 19%;"><i class="fa-solid fa-chalkboard me-1 text-warning opacity-75"></i> الصفة التدريسية / المقرر</th>
                                                <th style="width: 12%;"><i class="fa-regular fa-calendar-check me-1 text-warning opacity-75"></i> من تاريخ</th>
                                                <th style="width: 12%;"><i class="fa-regular fa-calendar-xmark me-1 text-warning opacity-75"></i> إلى تاريخ</th>
                                                <th style="width: 18%;"><i class="fa-solid fa-clock-rotate-left me-1 text-warning opacity-75"></i> المدة المحسوبة</th>
                                                <th style="width: 8%;">إجراء</th>
                                            </tr>
                                        </thead>
                                        <tbody id="experiences_tbody">
                                            @forelse($draftExperiences as $idx => $exp)
                                                @php
                                                    $sDate = \Carbon\Carbon::parse($exp->start_date);
                                                    $eDate = \Carbon\Carbon::parse($exp->end_date);
                                                    if ($eDate->gte($sDate)) {
                                                        $diff = $sDate->diff($eDate);
                                                        $calcY = $diff->y; $calcM = $diff->m; $calcD = $diff->d;
                                                        $tDays = $sDate->diffInDays($eDate);
                                                    } else {
                                                        $calcY = 0; $calcM = 0; $calcD = 0; $tDays = 0;
                                                    }
                                                @endphp
                                                <tr data-row-id="{{ $idx + 1 }}">
                                                    <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                                    <td class="text-start pe-3"><strong style="color: var(--imperial-navy);">{{ $exp->institution_name }}</strong></td>
                                                    <td class="text-secondary">{{ $exp->role_or_subject ?: '---' }}</td>
                                                    <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ \Carbon\Carbon::parse($exp->start_date)->format('Y-m-d') }}</span></td>
                                                    <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ \Carbon\Carbon::parse($exp->end_date)->format('Y-m-d') }}</span></td>
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
                                                        <input type="hidden" name="experiences[{{ $idx }}][start_date]" value="{{ \Carbon\Carbon::parse($exp->start_date)->format('Y-m-d') }}">
                                                        <input type="hidden" name="experiences[{{ $idx }}][end_date]" value="{{ \Carbon\Carbon::parse($exp->end_date)->format('Y-m-d') }}">
                                                        <input type="hidden" name="experiences[{{ $idx }}][duration_days]" value="{{ $tDays }}">
                                                        <input type="hidden" name="experiences[{{ $idx }}][notes]" value="{{ $exp->notes }}">
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="no_experiences_row">
                                                    <td colspan="7" class="text-muted py-4">لم يتم تسجيل أي فترات خبرة تدريسية داخل سوريا بعد. يرجى إدخال الفترات أعلاه (بما لا يقل عن 730 يوماً) والضغط على زر (+).</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Executive Summary Duration Scorecard (بدون مربعات خضراء) -->
                            <div class="p-3 p-md-4 rounded-3 border bg-white" id="experience_kpi_card" style="border-color: #d5dce6 !important;">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-hourglass-half fs-6" style="color: var(--imperial-navy);" id="exp_kpi_icon"></i>
                                        <span class="fw-bold fs-7" style="color: var(--imperial-navy);">خلاصة مدة الخبرات التدريسية التراكمية داخل سوريا:</span>
                                    </div>
                                    <span class="fs-8 fw-semibold" style="color: var(--heritage-gold-dark);">
                                        <i class="fa-solid fa-calculator me-1"></i> احتساب تقويمي تراكمي لفترات التدريس
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
                @endif
            </div>

            {{-- =========================================================================
                 STEP 5: RESIDENCY DURATION & TRAVEL ENTRY/EXIT MOVEMENTS (حركات الإقامة)
            ========================================================================= --}}
            <div class="form-section" id="step-5" style="display: none;">
                <input type="hidden" name="residences_submitted_flag" value="1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-passport fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: إقامة المرشح — حركات الدخول والخروج من بلد الدراسة
                </h5>

                <!-- شريط الربط التلقائي لبلد الدراسة والجامعة + حالة الإعفاء -->
                <div class="mohe-context-bar p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-earth-americas fs-7"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-9 fw-semibold">الدولة (بلد الدراسة)</div>
                                <div id="step5-bound-country" class="fw-bold fs-7" style="color: var(--imperial-navy);">---</div>
                            </div>
                        </div>
                        <div class="vr opacity-25 d-none d-sm-block" style="height: 28px;"></div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-building-columns fs-7"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-9 fw-semibold">الجامعة المانحة</div>
                                <div id="step5-bound-university" class="fw-bold fs-7" style="color: var(--imperial-navy);">---</div>
                            </div>
                        </div>
                    </div>
                    <div class="mohe-exempt-switch-box py-2 px-3" style="min-width: auto;">
                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                            <input class="form-check-input m-0" type="checkbox" id="step5-residence-exempt-switch" {{ $isResExempt ? 'checked' : '' }} onchange="syncResidenceExempt(this.checked)" style="cursor: pointer;">
                            <label class="form-check-label fw-bold fs-8 text-dark mb-0" for="step5-residence-exempt-switch" style="cursor: pointer;">
                                الماجستير معفى من شرط الإقامة (تخصص نظري / أونلاين)
                            </label>
                        </div>
                    </div>
                </div>

                <div id="residency-exempt-banner" class="py-3 px-4 fs-8 mb-4 rounded-3 border" style="display: {{ $isResExempt ? 'block' : 'none' }}; background-color: #fbf8f1; border-color: #dec99c !important; border-right: 4px solid var(--heritage-gold-dark) !important; color: var(--imperial-navy);">
                    <i class="fa-solid fa-circle-check me-1.5" style="color: var(--heritage-gold-dark);"></i>
                    <strong>التخصص معفى من شرط الإقامة:</strong> لا يُشترط إدخال حركات الدخول والخروج من بلد الدراسة ويمكنك الانتقال مباشرة إلى خطوة المرفقات، كما يصبح مرفقا جواز السفر وبيان حركة الهجرة اختياريين. (يمكنك إضافة حركات اختيارياً إن رغبت في توثيقها).
                </div>

                <div id="residency-required-banner" class="py-3 px-4 fs-8 mb-4 rounded-3 border" style="display: {{ $isResExempt ? 'none' : 'block' }}; background-color: #f8fafc; border-color: #cbd5e1 !important; border-right: 4px solid var(--heritage-gold) !important; color: var(--imperial-navy);">
                    <i class="fa-solid fa-circle-info me-1.5" style="color: var(--heritage-gold-dark);"></i>
                    <strong>إقامة المرشح:</strong> الرجاء إدخال حركات الدخول والخروج من بلد الدراسة من واقع أختام جواز السفر (يمكن إضافة عدة مدد متفرقة في حال غادر المرشح في إجازات ثم عاد)، وسيتم احتساب إجمالي مدة الإقامة بدقة بالسنوات والأشهر والأيام.
                </div>

                <!-- MOVEMENT INPUT PANEL -->
                <div class="mohe-official-panel mb-4">
                    <div class="mohe-panel-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plane-arrival" style="color: var(--heritage-gold-dark);"></i>
                            <h6 class="fw-bold mb-0 fs-7" style="color: var(--imperial-navy);">تسجيل حركة دخول وخروج من بلد الدراسة</h6>
                        </div>
                        <span id="mov_edit_mode_badge" class="badge bg-warning-subtle text-dark border border-warning px-2.5 py-1" style="display: none;">
                            <i class="fa-solid fa-pen-to-square me-1"></i> وضع تعديل الحركة المحددة
                        </span>
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-regular fa-calendar-check me-1 text-muted"></i> تاريخ الدخول لبلد الدراسة <span class="text-danger res-req-star">*</span></label>
                                <input type="date" id="mov_entry_date" class="form-control academic-input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-solid fa-plane-arrival me-1 text-muted"></i> مطار / منفذ الدخول <span class="text-danger res-req-star">*</span></label>
                                <input type="text" id="mov_entry_airport" class="form-control academic-input" placeholder="مثال: مطار رفيق الحريري الدولي">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-regular fa-calendar-xmark me-1 text-muted"></i> تاريخ الخروج من بلد الدراسة <span class="text-danger res-req-star">*</span></label>
                                <input type="date" id="mov_exit_date" class="form-control academic-input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-solid fa-plane-departure me-1 text-muted"></i> مطار / بلد الخروج <span class="text-danger res-req-star">*</span></label>
                                <input type="text" id="mov_exit_airport" class="form-control academic-input" placeholder="مثال: مطار رفيق الحريري / معبر المصنع">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-solid fa-book-open me-1 text-muted"></i> رقم صفحة الختم بالجواز <span class="text-danger res-req-star">*</span></label>
                                <input type="text" id="mov_page_number" class="form-control academic-input" placeholder="مثال: 14 أو 14-15">
                            </div>
                            <div class="col-md-8 d-flex align-items-end justify-content-start gap-2">
                                <button type="button" id="btn-add-residency" class="btn mohe-btn-navy mohe-add-icon-btn shadow-xs" onclick="addResidencyMovement()" title="إضافة حركة الإقامة إلى الجدول" aria-label="إضافة حركة إقامة">
                                    <i class="fa-solid fa-plus" id="btn-add-residency-icon"></i>
                                </button>
                                <button type="button" id="btn-cancel-edit-residency" class="btn btn-outline-secondary mohe-add-icon-btn" onclick="cancelEditResidencyMovement()" style="display: none;" title="إلغاء التعديل" aria-label="إلغاء التعديل">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MOVEMENTS TABLE -->
                <div class="mohe-ledger-wrap mb-4 shadow-xs">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0 mohe-ledger-table" id="movements_table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 13%;"><i class="fa-regular fa-calendar-check me-1 text-warning opacity-75"></i> تاريخ الدخول</th>
                                    <th style="width: 15%;"><i class="fa-solid fa-plane-arrival me-1 text-warning opacity-75"></i> مطار الدخول</th>
                                    <th style="width: 13%;"><i class="fa-regular fa-calendar-xmark me-1 text-warning opacity-75"></i> تاريخ الخروج</th>
                                    <th style="width: 15%;"><i class="fa-solid fa-plane-departure me-1 text-warning opacity-75"></i> مطار / بلد الخروج</th>
                                    <th style="width: 9%;"><i class="fa-solid fa-file-lines me-1 text-warning opacity-75"></i> الصفحة</th>
                                    <th style="width: 19%;"><i class="fa-solid fa-clock-rotate-left me-1 text-warning opacity-75"></i> مدة الإقامة</th>
                                    <th style="width: 11%;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="movements_tbody">
                                @php
                                    $draftResidences = optional($draftMaEd)->residences ? $draftMaEd->residences->sortBy('entry_date')->values() : collect();
                                @endphp
                                @forelse($draftResidences as $idx => $res)
                                    @php
                                        $inDate = \Carbon\Carbon::parse($res->entry_date);
                                        $outDate = \Carbon\Carbon::parse($res->exit_date);
                                        if ($inDate && $outDate && $outDate->gte($inDate)) {
                                            $diff = $inDate->diff($outDate);
                                            $calcY = $diff->y;
                                            $calcM = $diff->m;
                                            $calcD = $diff->d;
                                            $calcDays = (int) $inDate->diffInDays($outDate);
                                        } else {
                                            $calcY = 0; $calcM = 0; $calcD = 0; $calcDays = 0;
                                        }
                                    @endphp
                                    <tr data-row-id="{{ $idx + 1 }}" data-entry="{{ $res->entry_date }}" data-exit="{{ $res->exit_date }}">
                                        <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                        <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $res->entry_date }}</span></td>
                                        <td class="text-dark fw-medium">{{ $res->entry_airport ?: '---' }}</td>
                                        <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $res->exit_date }}</span></td>
                                        <td class="text-dark fw-medium">{{ $res->exit_airport ?: '---' }}</td>
                                        <td><span class="badge px-2 py-1" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">ص {{ $res->page_number ?: '---' }}</span></td>
                                        <td>
                                            <span class="mohe-duration-chip">{{ $calcY }} سنة و {{ $calcM }} شهر و {{ $calcD }} يوم</span>
                                            <div class="text-muted mt-1" style="font-size: 0.72rem;">({{ $calcDays }} يوماً)</div>
                                        </td>
                                        <td>
                                            <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                                                <button type="button" class="btn btn-outline-primary mohe-action-icon-btn" onclick="editResidencyMovement(this)" title="تعديل الحركة">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger mohe-action-icon-btn" onclick="deleteResidencyMovement(this)" title="حذف الحركة">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="residences[{{ $idx }}][entry_date]" value="{{ $res->entry_date }}">
                                            <input type="hidden" name="residences[{{ $idx }}][entry_airport]" value="{{ $res->entry_airport }}">
                                            <input type="hidden" name="residences[{{ $idx }}][exit_date]" value="{{ $res->exit_date }}">
                                            <input type="hidden" name="residences[{{ $idx }}][exit_airport]" value="{{ $res->exit_airport }}">
                                            <input type="hidden" name="residences[{{ $idx }}][page_number]" value="{{ $res->page_number }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no_movements_row">
                                        <td colspan="8" class="text-muted py-4">لم يتم تسجيل أي حركة دخول أو خروج بعد. يرجى ملء الحقول أعلاه والضغط على زر (+).</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TOTAL STAY DURATION SUMMARY SCORECARD (تصميم مؤسسي رسمي لاحتساب الإقامة) -->
                <div class="mohe-official-panel">
                    <div class="mohe-panel-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-clock-rotate-left fs-7"></i>
                            </div>
                            <h6 class="fw-bold mb-0 fs-7" style="color: var(--imperial-navy);">مدة الإقامة الإجمالية المحسوبة ببلد الدراسة</h6>
                        </div>
                        <span class="fs-9 fw-semibold" style="color: var(--heritage-gold-dark);">
                            <i class="fa-solid fa-calculator me-1"></i> احتساب تقويمي تراكمي مطابق لأختام جواز السفر
                        </span>
                    </div>
                    <div class="p-3 p-md-4 bg-white">
                        <div class="row g-3" style="max-width: 760px; margin: 0 auto;">
                            <div class="col-6 col-sm-3">
                                <div class="mohe-stat-tile py-3">
                                    <div class="mohe-stat-value" id="total_stay_years">0</div>
                                    <div class="mohe-stat-label">سنة</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="mohe-stat-tile py-3">
                                    <div class="mohe-stat-value" id="total_stay_months">0</div>
                                    <div class="mohe-stat-label">شهر</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="mohe-stat-tile py-3">
                                    <div class="mohe-stat-value" id="total_stay_days">0</div>
                                    <div class="mohe-stat-label">يوم</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="mohe-stat-tile is-gold py-3">
                                    <div class="mohe-stat-value" id="total_stay_all_days">0</div>
                                    <div class="mohe-stat-label">إجمالي الأيام</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-muted fs-8 mt-3">
                            يتم احتساب المجموع التراكمي بدقة لجميع مدد الإقامة المتفرقة المسجلة بالجدول أعلاه تلقائياً.
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 6: ATTACHMENTS (14 EXACT OFFICIAL ATTACHMENTS + 3 EXPERIENCE FOR THEORETICAL)
            ========================================================================= --}}
            <div class="form-section" id="step-6" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: المرفقات والمستندات الثبوتية المطلوبة ({{ $trackLabel }}) - PDF فقط (حتى 5MB لملخص الرسالة و2MB لباقي الملفات)
                </h5>

                @php
                    $existingFilesMap = [];
                    if ($draft) {
                        foreach ($draft->educations as $ed) {
                            foreach ($ed->attachments as $att) {
                                $note = (string) ($att->notes ?? '');
                                $tid  = (int) ($att->attachment_type_id ?? 0);

                                if (str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة الثانوية') || $tid === 2 || ($tid === 16 && str_contains($note, 'ثانوية'))) {
                                    $existingFilesMap['file_hs_decision'] = $att;
                                } elseif ($tid === 1 || (str_contains($note, 'ثانوية') && !str_contains($note, 'معادلة'))) {
                                    $existingFilesMap['file_secondary_cert'] = $att;
                                }
                                if (str_contains($note, 'معادلة الإجازة') || str_contains($note, 'معادلة البكالوريوس')) {
                                    $existingFilesMap['file_ba_decision'] = $att;
                                } elseif (str_contains($note, 'إجازة') || str_contains($note, 'بكالوريوس') || $tid === 3 || $tid === 2) {
                                    $existingFilesMap['file_bachelor_cert'] = $att;
                                }
                                if ($tid === 6 || ($tid === 4 && !str_contains($note, 'معادلة')) || (str_contains($note, 'شهادة الماجستير') && !str_contains($note, 'ملخص'))) {
                                    $existingFilesMap['file_master_cert'] = $att;
                                }
                                if (str_contains($note, 'التسجيل') || str_contains($note, 'الحصول على الدرجة') || str_contains($note, 'المناقشة') || $tid === 7 || ($tid === 8 && str_contains($note, 'تسجيل'))) {
                                    $existingFilesMap['file_reg_defense_doc'] = $att;
                                }
                                if (str_contains($note, 'ملخص') || ($tid === 8 && !str_contains($note, 'تسجيل')) || ($tid === 6 && str_contains($note, 'ملخص'))) {
                                    $existingFilesMap['file_thesis_abstract'] = $att;
                                }
                                if (str_contains($note, 'السيرة الذاتية') || ($tid === 14 && !str_contains($note, 'رسم') && !str_contains($note, 'رسوم') && !str_contains($note, 'إيصال'))) {
                                    $existingFilesMap['file_cv'] = $att;
                                }
                                if (str_contains($note, 'رسوم') || str_contains($note, 'رسم') || str_contains($note, 'إيصال تسديد') || $tid === 16 || $tid === 17 || ($tid === 14 && str_contains($note, 'إيصال'))) {
                                    $existingFilesMap['file_fees_receipt'] = $att;
                                }
                                if (str_contains($note, 'جواز السفر') || (str_contains($note, 'جواز') && !str_contains($note, 'الهجرة'))) {
                                    $existingFilesMap['file_passport'] = $att;
                                } elseif (str_contains($note, 'الهوية') || str_contains($note, 'هوية') || str_contains($note, 'إثبات الشخصية') || ($tid === 13 && !str_contains($note, 'إنكليزية') && !str_contains($note, 'اللغة'))) {
                                    $existingFilesMap['file_national_id'] = $att;
                                }
                                if ($tid === 24 || str_contains($note, 'الهجرة والجوازات') || str_contains($note, 'حركة الهجرة') || str_contains($note, 'بيان حركة')) {
                                    $existingFilesMap['file_immigration_movement'] = $att;
                                }
                                if ($tid === 19 || str_contains($note, 'ICDL') || str_contains($note, 'الحاسوب')) {
                                    $existingFilesMap['file_icdl_cert'] = $att;
                                }
                                if ($tid === 18 || str_contains($note, 'الإنكليزية') || str_contains($note, 'إنكليزية') || str_contains($note, 'الأجنبية')) {
                                    $existingFilesMap['file_english_cert'] = $att;
                                }
                                if (str_contains($note, 'كشف علامات') || ($tid === 5 && !str_contains($note, 'ملخص'))) {
                                    $existingFilesMap['file_master_transcript'] = $att;
                                }
                                if ($tid === 20 || $tid === 9 || str_contains($note, 'خبرة')) {
                                    $existingFilesMap['file_experience_cert'] = $att;
                                }
                                if (str_contains($note, 'عقود') || str_contains($note, 'العقود') || $tid === 10) {
                                    $existingFilesMap['file_private_uni_contracts'] = $att;
                                }
                                if (str_contains($note, 'رواتب') || str_contains($note, 'تحويل') || str_contains($note, 'إيصالات التحويل') || $tid === 11) {
                                    $existingFilesMap['file_salary_receipts'] = $att;
                                }
                                if ($tid === 23 || str_contains($note, 'أخرى') || str_contains($note, 'اخرى')) {
                                    $existingFilesMap['file_other_attachments'] = $att;
                                }
                            }
                        }
                    }
                @endphp

                <div class="row g-3">
                    <!-- 1. صورة عن الهوية الشخصية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">1. صورة عن الهوية الشخصية <span class="text-danger">*</span></label>
                            <input type="file" name="file_national_id" id="input_file_national_id" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_national_id']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_national_id']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_national_id']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. صورة عن شهادة البكالوريا -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">2. صورة عن شهادة البكالوريا (الثانوية العامة) <span class="text-danger">*</span></label>
                            <input type="file" name="file_secondary_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_secondary_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_secondary_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_secondary_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- قرار معادلة الشهادة الثانوية غير السورية -->
                    <div class="col-md-6" id="hs_decision_att_box" style="display: {{ $isHsForeign ? 'block' : 'none' }};">
                        <div class="border rounded p-3 bg-light h-100 border-warning">
                            <label class="form-label label-sm fw-bold">قرار معادلة الشهادة الثانوية غير السورية <span class="text-danger" id="hs_att_req_badge">*</span></label>
                            <input type="file" name="file_hs_decision" id="file_hs_decision" class="form-control academic-input" accept=".pdf" {{ $isHsForeign && empty($existingFilesMap['file_hs_decision']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_hs_decision']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_hs_decision']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. الإجازة الجامعية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">3. شهادة الإجازة الجامعية الأولى <span class="text-danger">*</span></label>
                            <input type="file" name="file_bachelor_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_bachelor_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_bachelor_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_bachelor_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. شهادة الماجستير مع الترجمة والتصديق -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">4. شهادة الماجستير مع الترجمة والتصديق (غير السورية) <span class="text-danger">*</span></label>
                            <input type="file" name="file_master_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_master_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_master_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_master_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. قرارات التسجيل بالدرجة وقرار الحصول على الدرجة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">5. قرارات التسجيل بالدرجة وقرار الحصول على الدرجة <span class="text-danger">*</span></label>
                            <input type="file" name="file_reg_defense_doc" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_reg_defense_doc']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_reg_defense_doc']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_reg_defense_doc']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. إيصال دفع (100 ألف للتطبيقي / 125 ألف للنظري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">6. إيصال دفع {{ $isTheoreticalTrack ? '125 ألف ل.س' : '100 ألف ل.س' }} <span class="text-danger">*</span></label>
                            <input type="file" name="file_fees_receipt" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_fees_receipt']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_fees_receipt']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_fees_receipt']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 7. جواز السفر مع صفحات الأختام -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100 {{ $isResExempt ? '' : 'border-info' }}" id="card_passport_att">
                            <label class="form-label label-sm fw-bold">
                                7. جواز السفر مع صفحات الأختام
                                <span class="badge bg-secondary-subtle text-muted ms-1 fs-9 res-att-opt-badge" style="display: {{ $isResExempt ? 'inline' : 'none' }};">(اختياري - معفى من الإقامة)</span>
                                <span class="text-danger res-req-star" style="display: {{ $isResExempt ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="file" name="file_passport" id="input_file_passport" class="form-control academic-input" accept=".pdf" {{ (!$isResExempt && empty($existingFilesMap['file_passport'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_passport']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_passport']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. بيان حركة من الهجرة والجوازات -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100 {{ $isResExempt ? '' : 'border-info' }}" id="card_immigration_att">
                            <label class="form-label label-sm fw-bold">
                                8. بيان حركة من الهجرة والجوازات
                                <span class="badge bg-secondary-subtle text-muted ms-1 fs-9 res-att-opt-badge" style="display: {{ $isResExempt ? 'inline' : 'none' }};">(اختياري - معفى من الإقامة)</span>
                                <span class="text-danger res-req-star" style="display: {{ $isResExempt ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="file" name="file_immigration_movement" id="input_file_immigration" class="form-control academic-input" accept=".pdf" {{ (!$isResExempt && empty($existingFilesMap['file_immigration_movement'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_immigration_movement']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_immigration_movement']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 9. السيرة الذاتية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">9. السيرة الذاتية للمرشح <span class="text-danger">*</span></label>
                            <input type="file" name="file_cv" id="input_file_cv" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_cv']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_cv']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_cv']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 10. ملخص عن رسالة الماجستير (حتى 5MB) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">10. ملخص عن رسالة الماجستير <span class="badge bg-light text-primary border ms-1 fs-9">حتى 5MB</span> <span class="text-danger">*</span></label>
                            <input type="file" name="file_thesis_abstract" data-max-mb="5" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_thesis_abstract']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_thesis_abstract']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_thesis_abstract']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 11. شهادة ICDL (اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">11. شهادة ICDL <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_icdl_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_icdl_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_icdl_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 12. شهادة اللغة الإنكليزية (اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">12. شهادة اللغة الإنكليزية <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_english_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_english_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_english_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 13. كشف علامات الماجستير إن وجد (اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">13. كشف علامات الماجستير إن وجد <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_master_transcript" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_master_transcript']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_master_transcript']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 14. مرفقات أخرى (اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">14. مرفقات أخرى <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_other_attachments" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_other_attachments']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_other_attachments']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($isTheoreticalTrack)
                        <div class="col-12 mt-3">
                            <div class="border-top pt-3">
                                <h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-briefcase me-1" style="color: var(--heritage-gold);"></i> مرفقات الخبرة التدريسية المطلوبة للمسار النظري (سنتين فأكثر):</h6>
                            </div>
                        </div>

                        <!-- إيصالات التحويل (للنظري) -->
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100 border-warning">
                                <label class="form-label label-sm fw-bold">إيصالات التحويل / الرواتب <span class="text-danger">*</span></label>
                                <input type="file" name="file_salary_receipts" id="file_salary_receipts" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_salary_receipts']) ? 'required' : '' }}>
                                @if(!empty($existingFilesMap['file_salary_receipts']))
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                        <a href="{{ asset('storage/' . $existingFilesMap['file_salary_receipts']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                            <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- العقود مع الجامعات (للنظري) -->
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100 border-warning">
                                <label class="form-label label-sm fw-bold">العقود مع الجامعات <span class="text-danger">*</span></label>
                                <input type="file" name="file_private_uni_contracts" id="file_private_uni_contracts" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_private_uni_contracts']) ? 'required' : '' }}>
                                @if(!empty($existingFilesMap['file_private_uni_contracts']))
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                        <a href="{{ asset('storage/' . $existingFilesMap['file_private_uni_contracts']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                            <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- شهادة الخبرة (للنظري) -->
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100 border-warning">
                                <label class="form-label label-sm fw-bold">شهادة الخبرة <span class="text-danger">*</span></label>
                                <input type="file" name="file_experience_cert" id="file_experience_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_experience_cert']) ? 'required' : '' }}>
                                @if(!empty($existingFilesMap['file_experience_cert']))
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                        <a href="{{ asset('storage/' . $existingFilesMap['file_experience_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                            <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- =========================================================================
                 STEP 7: REVIEW & CONFIRMATION
            ========================================================================= --}}
            <div class="form-section" id="step-7" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-print fs-5" style="color: var(--heritage-gold);"></i> الخطوة 7: مراجعة البيانات المدخلة وتأكيد الإرسال
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
                                <div class="col-md-6"><strong>الاسم والكنية:</strong> <span id="preview-fullName">---</span></div>
                                <div class="col-md-6"><strong>اسم الأب:</strong> <span id="preview-fatherName">---</span></div>
                                <div class="col-md-6"><strong>اسم ونسبة الأم:</strong> <span id="preview-motherName">---</span></div>
                                <div class="col-md-6"><strong>الرقم الوطني/جواز السفر:</strong> <span id="preview-nationalId">---</span></div>
                                <div class="col-md-6"><strong>تاريخ الميلاد:</strong> <span id="preview-dob">---</span></div>
                                <div class="col-md-6"><strong>الجنسية:</strong> <span id="preview-nationality">---</span></div>
                                <div class="col-md-6"><strong>الجنس:</strong> <span id="preview-gender">---</span></div>
                                <div class="col-md-6"><strong>البريد الإلكتروني:</strong> <span id="preview-email">---</span></div>
                                <div class="col-md-6"><strong>الجوال:</strong> <span id="preview-mobile">---</span></div>
                                <div class="col-md-6"><strong>العنوان بالتفصيل:</strong> <span id="preview-address">---</span></div>
                            </div>
                        </div>

                        <!-- Group 2: High School -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 2. بيانات الشهادة الثانوية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(2)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-hsCountry">---</span></div>
                                <div class="col-md-6"><strong>نوع البكالوريا:</strong> <span id="preview-hsType">---</span></div>
                                <div class="col-md-6"><strong>تاريخ الحصول عليها:</strong> <span id="preview-hsDate">---</span></div>
                                <div class="col-md-6" id="preview-hsDecisionContainer"><strong>رقم قرار المعادلة السوري:</strong> <span id="preview-hsDecisionNo">---</span></div>
                            </div>
                        </div>

                        <!-- Group 3: Bachelor's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> 3. بيانات الإجازة الجامعية الأولى (البكالوريوس):</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(3)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>1. الدولة المانحة:</strong> <span id="preview-baCountry">---</span></div>
                                <div class="col-md-6"><strong>2. الجامعة المانحة:</strong> <span id="preview-baUni">---</span></div>
                                <div class="col-md-6"><strong>3. الكلية:</strong> <span id="preview-baFaculty">---</span></div>
                                <div class="col-md-6"><strong>4. القسم:</strong> <span id="preview-baDept">---</span></div>
                                <div class="col-md-6"><strong>5. اختصاص:</strong> <span id="preview-baSpec">---</span></div>
                                <div class="col-md-6"><strong>6. التقدير:</strong> <span id="preview-baRank">---</span></div>
                                <div class="col-md-6"><strong>7. تاريخ الحصول على الدرجة:</strong> <span id="preview-baGrantDate">---</span></div>
                            </div>
                        </div>

                        <!-- Group 4: Foreign Master's & Track -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-award me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير الخارجي والمسار:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>1. بلد الدراسة (الدولة المانحة):</strong> <span id="preview-maCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة الخارجية المانحة:</strong> <span id="preview-maUni">---</span></div>
                                <div class="col-md-6"><strong>2. الكلية المانحة:</strong> <span id="preview-maFaculty">---</span></div>
                                <div class="col-md-6"><strong>3. القسم:</strong> <span id="preview-maDept">---</span></div>
                                <div class="col-md-6"><strong>4. الاختصاص العام:</strong> <span id="preview-maGenSpec">---</span></div>
                                <div class="col-md-6"><strong>5. الاختصاص الدقيق:</strong> <span id="preview-maSpec">---</span></div>
                                <div class="col-md-6"><strong>6. اسم الأستاذ المشرف:</strong> <span id="preview-maSupervisor">---</span></div>
                                <div class="col-md-6"><strong>7. التقدير:</strong> <span id="preview-maRank">---</span></div>
                                <div class="col-md-6"><strong>8. تاريخ التسجيل بالدرجة:</strong> <span id="preview-maRegDate">---</span></div>
                                <div class="col-md-6"><strong>9. تاريخ المناقشة:</strong> <span id="preview-maDefDate">---</span></div>
                                <div class="col-md-6"><strong>10. تاريخ منح الدرجة:</strong> <span id="preview-maGrantDate">---</span></div>
                                <div class="col-12"><strong>11. عنوان رسالة الماجستير (الأطروحة):</strong> <span id="preview-maThesisTitle">---</span></div>
                                <div class="col-md-6 mt-2"><strong>مسار الطلب:</strong> <span id="preview-maTrack" class="badge bg-warning-subtle text-warning-emphasis border">{{ $trackLabel }}</span></div>
                                <div class="col-md-6 mt-2"><strong>شرط الإقامة:</strong> <span id="preview-resExemptStatus" class="badge bg-info-subtle text-dark border">---</span></div>
                                @if($isTheoreticalTrack)
                                <div class="col-12 mt-3" id="preview-expContainer">
                                    <div class="card p-3 border rounded-2" style="background-color: var(--surface-container-low);">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <strong class="text-dark"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> بيانات الخبرة التدريسية داخل سوريا (مطلوب سنتين فأكثر):</strong>
                                            <span id="preview-expSummaryBadge" class="badge bg-secondary">---</span>
                                        </div>
                                        <div class="mb-1"><strong>إجمالي مدة الخبرة:</strong> <span id="preview-totalExpTime" class="text-dark fw-bold">لا توجد فترات خبرة مسجلة</span></div>
                                        <div id="preview-experiences-list"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Group 5: Residency Movements -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-passport me-1" style="color: var(--heritage-gold);"></i> 5. ملخص مدة الإقامة ببلد الدراسة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(5)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل حركات الإقامة</button>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <strong>عدد حركات الإقامة المسجلة:</strong> <span id="preview-resCount" class="badge bg-secondary">0</span> حركات
                                </div>
                                <div class="col-md-6">
                                    <strong>إجمالي مدة الإقامة المحسوبة:</strong> <span id="preview-totalStay" class="badge bg-primary fs-7">0 سنة و 0 شهر و 0 يوم</span>
                                </div>
                            </div>
                        </div>

                        <!-- Group 6: University Evaluation Request -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 6. كتاب طلب التقويم الصادر عن الجامعة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo">---</span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate">---</span></div>
                            </div>
                        </div>

                        <!-- Group 7: Uploaded Documents Summary -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-folder-open me-1" style="color: var(--heritage-gold);"></i> 7. الوثائق والمستندات المرفقة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(6)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل المرفقات</button>
                            </div>
                            <p class="text-muted fs-8 mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> تم إرفاق الوثائق والمستندات المطلوبة وجواز السفر بصيغة PDF. يمكنك النقر على زر التعديل للعودة لخطوة المرفقات.</p>
                        </div>
                    </div>
                </div>

                <!-- Final check warning confirmation -->
                <div class="form-check form-switch mt-4 p-3 border rounded d-flex align-items-center gap-3" style="background-color: var(--surface-container-low); border-color: var(--outline-variant) !important;">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required style="width: 2.2em; height: 1.2em; cursor: pointer;">
                    <label class="form-check-label fw-bold text-dark mb-0 label-md" for="chkConfirm" style="cursor: pointer;">
                        نصادق نحن في إدارة الجامعة على صحة كافة البيانات والوثائق المرفقة أعلاه، وأن جميع البيانات والمرفقات المدرجة صحيحة ومطابقة للوثائق الرسمية المعتمدة والأختام المرفقة بجواز السفر، ونتحمل كامل المسؤولية القانونية حيال ذلك.
                    </label>
                </div>
            </div>

            <!-- ================= BUTTONS NAVIGATION ================= -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 pt-3 border-top" style="border-top-color: var(--outline-variant) !important;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-navy px-4 py-2" id="btn-prev" onclick="changeStep(-1)" style="display: none;">
                        <i class="fa-solid fa-arrow-right me-1"></i> السابق
                    </button>
                    <div id="spacer-prev"></div>

                    @if($canSaveDraft)
                    <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" onclick="triggerSaveDraft()" title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
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

<script>
    const IS_ADMIN_EDIT = @json(!empty($isAdminEdit));
    const IS_THEORETICAL_TRACK = @json($isTheoreticalTrack);
    let currentStep = 1;
    const totalSteps = 7;
    let hasVisitedReview = {{ optional($draft)->id ? 'true' : 'false' }};

    function syncBoundMasterContext() {
        const cEl = document.getElementById('ma_country_id');
        const uEl = document.getElementById('ma_university_other');
        const outC = document.getElementById('step5-bound-country');
        const outU = document.getElementById('step5-bound-university');
        if (outC && cEl) {
            outC.textContent = (cEl.value && cEl.options[cEl.selectedIndex]) ? cEl.options[cEl.selectedIndex].text.trim() : '---';
        }
        if (outU && uEl) {
            outU.textContent = uEl.value.trim() || '---';
        }
    }

    function isResidenceExemptChecked() {
        const chk = document.getElementById('input-isResidenceExempt');
        return !!(chk && chk.checked);
    }

    function syncResidenceExempt(isExempt) {
        const exempt = !!isExempt;
        const step4Chk = document.getElementById('input-isResidenceExempt');
        const step5Chk = document.getElementById('step5-residence-exempt-switch');
        if (step4Chk) step4Chk.checked = exempt;
        if (step5Chk) step5Chk.checked = exempt;

        const lbl4 = document.getElementById('lbl-residenceExemptStep4');
        if (lbl4) {
            lbl4.textContent = exempt ? 'معفى من شرط الإقامة (دراسة نظرية / أونلاين)' : 'يتطلب حضوراً وإقامة فعلية';
            lbl4.className = 'form-check-label fw-bold fs-8 mb-0 ' + (exempt ? 'text-success' : 'text-dark');
        }

        const exemptBanner = document.getElementById('residency-exempt-banner');
        const reqBanner = document.getElementById('residency-required-banner');
        if (exemptBanner) exemptBanner.style.display = exempt ? 'block' : 'none';
        if (reqBanner) reqBanner.style.display = exempt ? 'none' : 'block';

        document.querySelectorAll('.res-req-star').forEach(el => {
            el.style.display = exempt ? 'none' : 'inline';
        });
        document.querySelectorAll('.res-att-req-badge').forEach(el => {
            el.style.display = exempt ? 'none' : 'inline';
        });
        document.querySelectorAll('.res-att-opt-badge').forEach(el => {
            el.style.display = exempt ? 'inline' : 'none';
        });

        ['input_file_passport', 'input_file_immigration'].forEach(fid => {
            const inp = document.getElementById(fid);
            if (!inp) return;
            const hasSaved = !!inp.closest('.border.rounded')?.querySelector('.badge.bg-success-subtle');
            if (exempt || hasSaved) {
                inp.removeAttribute('required');
                inp.classList.remove('is-invalid');
            } else {
                inp.setAttribute('required', 'required');
            }
        });

        const cardPass = document.getElementById('card_passport_att');
        const cardImm = document.getElementById('card_immigration_att');
        [cardPass, cardImm].forEach(c => {
            if (c) {
                if (exempt) c.classList.remove('border-info');
                else c.classList.add('border-info');
            }
        });

        recalculateTotalStay();
    }

    function showStep(step) {
        currentStep = step;

        for (let i = 1; i <= totalSteps; i++) {
            const section = document.getElementById('step-' + i);
            if (section) {
                if (i === step) {
                    section.style.display = 'block';
                    section.classList.add('active');
                } else {
                    section.style.display = 'none';
                    section.classList.remove('active');
                }
            }

            const stepNode = document.getElementById('step-node-' + i);
            if (stepNode) {
                if (i < step) {
                    stepNode.className = 'wizard-step completed';
                } else if (i === step) {
                    stepNode.className = 'wizard-step active';
                } else {
                    stepNode.className = 'wizard-step';
                }
            }
        }

        const btnPrev = document.getElementById('btn-prev');
        const spacerPrev = document.getElementById('spacer-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');
        const btnQuickReview = document.getElementById('btn-quick-review');

        if (step === 1) {
            btnPrev.style.display = 'none';
            spacerPrev.style.display = 'block';
        } else {
            btnPrev.style.display = 'inline-block';
            spacerPrev.style.display = 'none';
        }

        if (step === 5) {
            syncBoundMasterContext();
        }

        if (step === totalSteps) {
            hasVisitedReview = true;
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-block';
            if (btnQuickReview) btnQuickReview.style.display = 'none';
            try {
                populateReview();
            } catch (err) {
                console.error('Error populating review:', err);
            }
        } else {
            btnNext.style.display = 'inline-block';
            btnSubmit.style.display = 'none';
            if (btnQuickReview) {
                btnQuickReview.style.display = hasVisitedReview ? 'inline-block' : 'none';
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function quickReturnToReview() {
        if (validateCurrentStep(currentStep)) {
            showStep(totalSteps);
        }
    }

    function changeStep(delta) {
        const nextStep = currentStep + delta;
        if (delta > 0 && !validateCurrentStep(currentStep)) {
            return;
        }
        if (nextStep >= 1 && nextStep <= totalSteps) {
            showStep(nextStep);
        }
    }

    function goToStep(targetStep) {
        if (targetStep < currentStep) {
            showStep(targetStep);
        } else {
            for (let s = currentStep; s < targetStep; s++) {
                if (!validateCurrentStep(s)) {
                    showStep(s);
                    return;
                }
            }
            showStep(targetStep);
        }
    }
    window.goToStep = goToStep;
    window.showStep = showStep;

    function validateCurrentStep(step) {
        if (IS_ADMIN_EDIT) return true;
        const section = document.getElementById('step-' + step);
        if (!section) return true;

        const requiredInputs = section.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            if (input.offsetParent === null) {
                return;
            }

            if (input.type === 'file') {
                const parentDiv = input.closest('.border.rounded') || input.parentElement;
                if (parentDiv && parentDiv.querySelector('.badge.bg-success-subtle')) {
                    return;
                }
                if (!input.files || input.files.length === 0) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            } else {
                if (!input.value || input.value.trim() === '') {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });

        if (step === 4 && IS_THEORETICAL_TRACK) {
            const tbody = document.getElementById('experiences_tbody');
            const hasRows = tbody && tbody.querySelectorAll('tr:not(#no_experiences_row)').length > 0;
            const instEl = document.getElementById('exp_institution');
            const startEl = document.getElementById('exp_start_date');
            const endEl = document.getElementById('exp_end_date');

            if (!hasRows && instEl && instEl.value.trim() && startEl && startEl.value && endEl && endEl.value) {
                addExperiencePeriod();
            }

            const stats = recalcTotalExperience();
            if (!stats || stats.totalDays < 730) {
                alert(`مسار الماجستير الخارجي النظري يتطلب خبرة تدريسية لا تقل عن سنتين (730 يوماً فأكثر) داخل سوريا. مجموع الخبرة المسجلة حالياً: ${stats ? stats.totalDays : 0} يوماً.`);
                if (instEl) {
                    instEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        }

        if (step === 5 && !isResidenceExemptChecked()) {
            const tbody = document.getElementById('movements_tbody');
            let hasRows = tbody && tbody.querySelectorAll('tr:not(#no_movements_row)').length > 0;

            if (!hasRows) {
                const eDate = document.getElementById('mov_entry_date')?.value;
                const eAir = document.getElementById('mov_entry_airport')?.value.trim();
                const xDate = document.getElementById('mov_exit_date')?.value;
                const xAir = document.getElementById('mov_exit_airport')?.value.trim();
                const pg = document.getElementById('mov_page_number')?.value.trim();
                if (eDate && eAir && xDate && xAir && pg) {
                    addResidencyMovement();
                    hasRows = tbody && tbody.querySelectorAll('tr:not(#no_movements_row)').length > 0;
                }
            }

            if (!hasRows) {
                alert('يرجى إدخال حركة دخول وخروج واحدة على الأقل إلى بلد الدراسة والضغط على (+ إضافة)، أو تفعيل خيار (معفى من شرط الإقامة) إذا كان التخصص نظرياً معفى.');
                document.getElementById('mov_entry_date')?.focus();
                return false;
            }
        }

        return isValid;
    }

    const syriaCountryId = '{{ $syriaId ?? 1 }}';

    function toggleHsDecision(countryId) {
        const box = document.getElementById('hs_decision_box');
        const noInput = document.getElementById('hs_decision_no');
        const dateInput = document.getElementById('hs_decision_date');
        const attBox = document.getElementById('hs_decision_att_box');
        const attInput = document.getElementById('file_hs_decision');

        if (countryId != syriaCountryId) {
            if (box) box.style.display = 'block';
            if (noInput) noInput.setAttribute('required', 'required');
            if (dateInput) dateInput.setAttribute('required', 'required');
            if (attBox) attBox.style.display = 'block';
            if (attInput && !attInput.closest('.border.rounded')?.querySelector('.badge.bg-success-subtle')) {
                attInput.setAttribute('required', 'required');
            }
        } else {
            if (box) box.style.display = 'none';
            if (noInput) { noInput.removeAttribute('required'); noInput.classList.remove('is-invalid'); }
            if (dateInput) { dateInput.removeAttribute('required'); dateInput.classList.remove('is-invalid'); }
            if (attBox) attBox.style.display = 'none';
            if (attInput) { attInput.removeAttribute('required'); attInput.classList.remove('is-invalid'); }
        }
    }

    function syncBaUniversityFields() {
        const countryEl = document.getElementById('ba_country_id');
        const countryCol = document.getElementById('ba_country_col');
        const selectCol = document.getElementById('ba_uni_select_col');
        const otherCol = document.getElementById('ba_uni_other_col');
        const selectEl = document.getElementById('ba_university_id');
        const otherEl = document.getElementById('ba_university_other');

        const isSyria = countryEl ? (countryEl.value == syriaCountryId) : false;
        const isOther = !isSyria || (selectEl && selectEl.value === 'other');

        if (selectCol) selectCol.style.display = isSyria ? 'block' : 'none';
        if (otherCol) otherCol.style.display = isOther ? 'block' : 'none';

        if (selectEl) {
            if (isSyria) selectEl.setAttribute('required', 'required');
            else { selectEl.removeAttribute('required'); selectEl.classList.remove('is-invalid'); }
        }
        if (otherEl) {
            if (isOther) otherEl.setAttribute('required', 'required');
            else { otherEl.removeAttribute('required'); otherEl.classList.remove('is-invalid'); }
        }

        const colCls = (isSyria && isOther) ? 'col-md-4' : 'col-md-6';
        [countryCol, selectCol, otherCol].forEach(c => {
            if (c) {
                c.classList.remove('col-md-4', 'col-md-6');
                c.classList.add(colCls);
            }
        });
    }

    function toggleBaCountry(countryId) {
        const otherEl = document.getElementById('ba_university_other');
        const selectEl = document.getElementById('ba_university_id');
        if (countryId != syriaCountryId && selectEl && selectEl.value && otherEl && !otherEl.value.trim()) {
            // Keep foreign text clean when switching out of Syria
            otherEl.value = '';
        }
        syncBaUniversityFields();
    }

    function toggleBaUniOther(val) {
        syncBaUniversityFields();
    }
    function updateBaRowCols() {
        syncBaUniversityFields();
    }
    function toggleMaCountry(val) {
        syncBoundMasterContext();
    }

    // ==========================================
    // MULTI-PERIOD TEACHING EXPERIENCE LOGIC (خبرات التدريس بسوريا)
    // ==========================================
    let expCounter = {{ $draftExperiences->count() }};

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function toggleExperienceBlock(show) {
        const container = document.getElementById('experience_details_container');
        if (container) container.style.display = show ? 'block' : 'none';
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

    function toggleExperienceFields(val) {
        toggleExperienceBlock(val === 'yes');
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
            alert('يرجى إدخال اسم الجامعة أو الكلية في سوريا لفترة الخبرة.');
            if (instEl) instEl.focus();
            return;
        }
        if (!startDate || !endDate) {
            alert('يرجى تحديد تاريخ البدء وتاريخ الانتهاء لفترة الخبرة التدريسية.');
            return;
        }

        const inD = new Date(startDate);
        const outD = new Date(endDate);
        if (outD < inD) {
            alert('تاريخ نهاية فترة الخبرة يجب أن يكون مساوياً أو بعد تاريخ البدء.');
            return;
        }

        const stay = getDetailedStay(startDate, endDate);
        expCounter++;

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
                <input type="hidden" name="experiences[${expCounter}][institution_name]" value="${escapeHtml(institution)}">
                <input type="hidden" name="experiences[${expCounter}][role_or_subject]" value="${escapeHtml(role)}">
                <input type="hidden" name="experiences[${expCounter}][start_date]" value="${startDate}">
                <input type="hidden" name="experiences[${expCounter}][end_date]" value="${endDate}">
                <input type="hidden" name="experiences[${expCounter}][duration_days]" value="${stay.totalDays}">
                <input type="hidden" name="experiences[${expCounter}][notes]" value="${escapeHtml(notes)}">
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
            tbody.innerHTML = '<tr id="no_experiences_row"><td colspan="7" class="text-muted py-4">لم يتم تسجيل أي فترات خبرة تدريسية داخل سوريا بعد. يمكنك إدخال الفترات من النموذج أعلاه والضغط على زر (+).</td></tr>';
        } else {
            Array.from(tbody.children).forEach((r, idx) => {
                if (r.children[0]) r.children[0].textContent = idx + 1;
            });
        }
        recalcTotalExperience();
    }

    function recalcTotalExperience() {
        if (!IS_THEORETICAL_TRACK) {
            const hasSyrianExpHidden = document.getElementById('has_syrian_experience');
            if (hasSyrianExpHidden) hasSyrianExpHidden.value = 'no';
            return { totalDays: 0, isTheoretical: false, years: 0, months: 0, days: 0 };
        }

        const tbody = document.getElementById('experiences_tbody');
        let totalDays = 0;
        let sumY = 0, sumM = 0, sumD = 0;

        if (tbody) {
            tbody.querySelectorAll('tr:not(#no_experiences_row)').forEach(tr => {
                const startInput = tr.querySelector('input[name*="[start_date]"]');
                const endInput = tr.querySelector('input[name*="[end_date]"]');
                if (startInput && endInput && startInput.value && endInput.value) {
                    const stay = getDetailedStay(startInput.value, endInput.value);
                    totalDays += stay.totalDays;
                    sumY += stay.years;
                    sumM += stay.months;
                    sumD += stay.days;
                }
            });
        }

        if (sumD >= 30) {
            sumM += Math.floor(sumD / 30);
            sumD = sumD % 30;
        }
        if (sumM >= 12) {
            sumY += Math.floor(sumM / 12);
            sumM = sumM % 12;
        }

        const yEl = document.getElementById('exp_total_years');
        const mEl = document.getElementById('exp_total_months');
        const dEl = document.getElementById('exp_total_days');
        const allEl = document.getElementById('exp_total_all_days');
        if (yEl) yEl.textContent = sumY;
        if (mEl) mEl.textContent = sumM;
        if (dEl) dEl.textContent = sumD;
        if (allEl) allEl.textContent = totalDays;

        const isTheoretical = (totalDays >= 730);
        const hasSyrianExpHidden = document.getElementById('has_syrian_experience');
        if (hasSyrianExpHidden) {
            hasSyrianExpHidden.value = 'yes';
        }

        return { totalDays, isTheoretical, years: sumY, months: sumM, days: sumD };
    }

    // ==========================================
    // RESIDENCY MOVEMENTS LOGIC (حركات الإقامة واحتسابها التقويمي الدقيق)
    // ==========================================
    let movementCounter = {{ $draftResidences->count() ?? 0 }};
    let editingResidencyRow = null;

    function parseYmdDate(dateStr) {
        if (!dateStr) return null;
        const m = String(dateStr).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return null;
        const y = Number(m[1]), mo = Number(m[2]), d = Number(m[3]);
        const dt = new Date(y, mo - 1, d);
        if (dt.getFullYear() !== y || dt.getMonth() !== (mo - 1) || dt.getDate() !== d) return null;
        return { date: dt, utc: Date.UTC(y, mo - 1, d) };
    }

    function getDetailedStay(entryDateStr, exitDateStr) {
        const pIn = parseYmdDate(entryDateStr);
        const pOut = parseYmdDate(exitDateStr);
        if (!pIn || !pOut || pOut.utc < pIn.utc) {
            return { years: 0, months: 0, days: 0, totalDays: 0 };
        }
        const totalDays = Math.round((pOut.utc - pIn.utc) / (1000 * 60 * 60 * 24));
        const inD = pIn.date;
        const outD = pOut.date;

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

    function sortResidencyRows() {
        const tbody = document.getElementById('movements_tbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr:not(#no_movements_row)'));
        rows.sort((a, b) => {
            const da = a.querySelector('input[name*="[entry_date]"]')?.value || '';
            const db = b.querySelector('input[name*="[entry_date]"]')?.value || '';
            return da.localeCompare(db);
        });
        rows.forEach((r, idx) => {
            if (r.children[0]) r.children[0].textContent = idx + 1;
            tbody.appendChild(r);
        });
    }

    function editResidencyMovement(btn) {
        const row = btn.closest('tr');
        if (!row) return;
        editingResidencyRow = row;

        document.getElementById('mov_entry_date').value = row.querySelector('input[name*="[entry_date]"]')?.value || '';
        document.getElementById('mov_entry_airport').value = row.querySelector('input[name*="[entry_airport]"]')?.value || '';
        document.getElementById('mov_exit_date').value = row.querySelector('input[name*="[exit_date]"]')?.value || '';
        document.getElementById('mov_exit_airport').value = row.querySelector('input[name*="[exit_airport]"]')?.value || '';
        document.getElementById('mov_page_number').value = row.querySelector('input[name*="[page_number]"]')?.value || '';

        const iconEl = document.getElementById('btn-add-residency-icon');
        if (iconEl) iconEl.className = 'fa-solid fa-check';
        const btnAdd = document.getElementById('btn-add-residency');
        if (btnAdd) btnAdd.title = 'حفظ تعديل الحركة';
        const cancelBtn = document.getElementById('btn-cancel-edit-residency');
        if (cancelBtn) cancelBtn.style.display = 'inline-flex';
        const editBadge = document.getElementById('mov_edit_mode_badge');
        if (editBadge) editBadge.style.display = 'inline-block';
        document.getElementById('mov_entry_date')?.focus();
    }

    function cancelEditResidencyMovement() {
        editingResidencyRow = null;
        document.getElementById('mov_entry_date').value = '';
        document.getElementById('mov_entry_airport').value = '';
        document.getElementById('mov_exit_date').value = '';
        document.getElementById('mov_exit_airport').value = '';
        document.getElementById('mov_page_number').value = '';

        const iconEl = document.getElementById('btn-add-residency-icon');
        if (iconEl) iconEl.className = 'fa-solid fa-plus';
        const btnAdd = document.getElementById('btn-add-residency');
        if (btnAdd) btnAdd.title = 'إضافة حركة الإقامة إلى الجدول';
        const cancelBtn = document.getElementById('btn-cancel-edit-residency');
        if (cancelBtn) cancelBtn.style.display = 'none';
        const editBadge = document.getElementById('mov_edit_mode_badge');
        if (editBadge) editBadge.style.display = 'none';
    }

    function addResidencyMovement() {
        const entryDate = document.getElementById('mov_entry_date')?.value;
        const entryAirport = document.getElementById('mov_entry_airport')?.value.trim() || '';
        const exitDate = document.getElementById('mov_exit_date')?.value;
        const exitAirport = document.getElementById('mov_exit_airport')?.value.trim() || '';
        const pageNumber = document.getElementById('mov_page_number')?.value.trim() || '';

        if (!entryDate || !exitDate) {
            alert('يرجى إدخال تاريخ الدخول وتاريخ الخروج لبلد الدراسة.');
            return;
        }
        if (!entryAirport || !exitAirport) {
            alert('يرجى إدخال مطار/منفذ الدخول ومطار/بلد الخروج.');
            return;
        }
        if (!pageNumber) {
            alert('يرجى إدخال رقم صفحة الختم في جواز السفر.');
            return;
        }

        const pIn = parseYmdDate(entryDate);
        const pOut = parseYmdDate(exitDate);
        if (!pIn || !pOut || pOut.utc < pIn.utc) {
            alert('تاريخ الخروج يجب أن يكون مساوياً أو بعد تاريخ الدخول.');
            return;
        }

        const tbody = document.getElementById('movements_tbody');
        let hasOverlap = false;
        if (tbody) {
            tbody.querySelectorAll('tr:not(#no_movements_row)').forEach(tr => {
                if (editingResidencyRow && tr === editingResidencyRow) return;
                const rowIn = parseYmdDate(tr.querySelector('input[name*="[entry_date]"]')?.value);
                const rowOut = parseYmdDate(tr.querySelector('input[name*="[exit_date]"]')?.value);
                if (rowIn && rowOut && pIn.utc < rowOut.utc && pOut.utc > rowIn.utc) {
                    hasOverlap = true;
                }
            });
        }
        if (hasOverlap) {
            alert('تتداخل تواريخ هذه الحركة مع حركة دخول وخروج مسجلة مسبقاً في الجدول. يرجى التحقق من التواريخ لمنع احتساب المدة مرتين.');
            return;
        }

        const stay = getDetailedStay(entryDate, exitDate);
        const noRow = document.getElementById('no_movements_row');
        if (noRow) noRow.remove();

        let targetRow = editingResidencyRow;
        let idxKey;
        if (targetRow) {
            const existingInput = targetRow.querySelector('input[name*="[entry_date]"]');
            const mIdx = existingInput ? existingInput.name.match(/residences\[(\d+)\]/) : null;
            idxKey = mIdx ? Number(mIdx[1]) : (++movementCounter);
        } else {
            movementCounter++;
            idxKey = movementCounter;
            targetRow = document.createElement('tr');
            tbody.appendChild(targetRow);
        }

        targetRow.setAttribute('data-days', stay.totalDays);
        targetRow.setAttribute('data-entry', entryDate);
        targetRow.setAttribute('data-exit', exitDate);
        targetRow.innerHTML = `
            <td class="fw-bold text-muted">#</td>
            <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">${escapeHtml(entryDate)}</span></td>
            <td class="text-dark fw-medium">${escapeHtml(entryAirport) || '---'}</td>
            <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">${escapeHtml(exitDate)}</span></td>
            <td class="text-dark fw-medium">${escapeHtml(exitAirport) || '---'}</td>
            <td><span class="badge px-2 py-1" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">ص ${escapeHtml(pageNumber) || '---'}</span></td>
            <td>
                <span class="mohe-duration-chip">${stay.years} سنة و ${stay.months} شهر و ${stay.days} يوم</span>
                <div class="text-muted mt-1" style="font-size: 0.72rem;">(${stay.totalDays} يوماً)</div>
            </td>
            <td>
                <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                    <button type="button" class="btn btn-outline-primary mohe-action-icon-btn" onclick="editResidencyMovement(this)" title="تعديل الحركة">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger mohe-action-icon-btn" onclick="deleteResidencyMovement(this)" title="حذف الحركة">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <input type="hidden" name="residences[${idxKey}][entry_date]" value="${escapeHtml(entryDate)}">
                <input type="hidden" name="residences[${idxKey}][entry_airport]" value="${escapeHtml(entryAirport)}">
                <input type="hidden" name="residences[${idxKey}][exit_date]" value="${escapeHtml(exitDate)}">
                <input type="hidden" name="residences[${idxKey}][exit_airport]" value="${escapeHtml(exitAirport)}">
                <input type="hidden" name="residences[${idxKey}][page_number]" value="${escapeHtml(pageNumber)}">
            </td>
        `;

        sortResidencyRows();
        cancelEditResidencyMovement();
        recalculateTotalStay();
    }

    function deleteResidencyMovement(btn) {
        const row = btn.closest('tr');
        if (editingResidencyRow && row === editingResidencyRow) {
            cancelEditResidencyMovement();
        }
        row.remove();
        const tbody = document.getElementById('movements_tbody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr id="no_movements_row"><td colspan="8" class="text-muted py-4">لم يتم تسجيل أي حركة دخول أو خروج بعد. يرجى ملء الحقول أعلاه والضغط على زر (+).</td></tr>';
        } else {
            sortResidencyRows();
        }
        recalculateTotalStay();
    }

    function recalculateTotalStay() {
        const tbody = document.getElementById('movements_tbody');
        let totalDays = 0;
        let sumY = 0, sumM = 0, sumD = 0;

        if (tbody) {
            tbody.querySelectorAll('tr:not(#no_movements_row)').forEach(tr => {
                const inDateInput = tr.querySelector('input[name*="[entry_date]"]');
                const outDateInput = tr.querySelector('input[name*="[exit_date]"]');
                if (inDateInput && outDateInput && inDateInput.value && outDateInput.value) {
                    const stay = getDetailedStay(inDateInput.value, outDateInput.value);
                    totalDays += stay.totalDays;
                    sumY += stay.years;
                    sumM += stay.months;
                    sumD += stay.days;
                }
            });
        }

        if (sumD >= 30) {
            sumM += Math.floor(sumD / 30);
            sumD = sumD % 30;
        }
        if (sumM >= 12) {
            sumY += Math.floor(sumM / 12);
            sumM = sumM % 12;
        }

        const yEl = document.getElementById('total_stay_years');
        const mEl = document.getElementById('total_stay_months');
        const dEl = document.getElementById('total_stay_days');
        const allEl = document.getElementById('total_stay_all_days');
        if (yEl) yEl.textContent = sumY;
        if (mEl) mEl.textContent = sumM;
        if (dEl) dEl.textContent = sumD;
        if (allEl) allEl.textContent = totalDays;

        const previewStay = document.getElementById('preview-totalStay');
        if (previewStay) {
            if (isResidenceExemptChecked() && totalDays === 0) {
                previewStay.textContent = 'معفى من شرط الإقامة (تخصص نظري / أونلاين)';
                previewStay.className = 'badge bg-info text-dark fs-7';
            } else {
                previewStay.textContent = `${sumY} سنة و ${sumM} شهر و ${sumD} يوم (${totalDays} يوماً)`;
                previewStay.className = 'badge bg-primary fs-7';
            }
        }
        const previewResCount = document.getElementById('preview-resCount');
        if (previewResCount && tbody) {
            const validRows = tbody.querySelectorAll('tr:not(#no_movements_row)').length;
            previewResCount.textContent = validRows;
        }
    }

    function formatDateDisplay(val) {
        if (!val || val === '-') return '---';
        const match = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return `${match[3]}/${match[2]}/${match[1]}`;
        }
        return val;
    }

    function populateReview() {
        const form = document.getElementById('wizard-form');
        if (!form) return;

        // Group 1: Personal Details
        document.getElementById('preview-fullName').textContent = form.full_name.value || '---';
        document.getElementById('preview-fatherName').textContent = form.father_name.value || '---';
        document.getElementById('preview-motherName').textContent = form.mother_name.value || '---';
        document.getElementById('preview-nationalId').textContent = form.national_id.value || '---';
        document.getElementById('preview-dob').textContent = formatDateDisplay(form.dob ? form.dob.value : '');
        document.getElementById('preview-gender').textContent = form.gender.value || '---';
        document.getElementById('preview-mobile').textContent = form.mobile.value || '---';
        document.getElementById('preview-email').textContent = form.email.value || '---';
        document.getElementById('preview-address').textContent = form.address.value || '---';

        const natSelect = form.nationality_id;
        document.getElementById('preview-nationality').textContent = natSelect.options[natSelect.selectedIndex]?.text || '---';

        // Group 2: High School
        const hsCountrySelect = form.hs_country_id;
        let hsCountryText = hsCountrySelect.options[hsCountrySelect.selectedIndex]?.text || '---';
        document.getElementById('preview-hsCountry').textContent = hsCountryText;
        document.getElementById('preview-hsType').textContent = form.hs_type.value || '---';
        document.getElementById('preview-hsDate').textContent = form.hs_grant_date.value || '---';

        const isHsForeign = hsCountrySelect.value != {{ $syriaId ?? 1 }};
        const hsDecBox = document.getElementById('preview-hsDecisionContainer');
        if (hsDecBox) {
            if (isHsForeign && form.hs_decision_no && form.hs_decision_no.value.trim()) {
                hsDecBox.style.display = 'block';
                document.getElementById('preview-hsDecisionNo').textContent = form.hs_decision_no.value + (form.hs_decision_date && form.hs_decision_date.value ? ' (تاريخ: ' + formatDateDisplay(form.hs_decision_date.value) + ')' : '');
            } else {
                hsDecBox.style.display = 'none';
            }
        }

        // Group 3: Bachelor's Degree
        const baCountrySelect = form.ba_country_id;
        let baCountryText = baCountrySelect ? (baCountrySelect.options[baCountrySelect.selectedIndex]?.text || '---') : '---';
        if (document.getElementById('preview-baCountry')) document.getElementById('preview-baCountry').textContent = baCountryText;
        let baUniDisplay = '---';
        const baUniSelect = document.getElementById('ba_university_id');
        if (baCountrySelect && baCountrySelect.value == syriaCountryId && baUniSelect && baUniSelect.value && baUniSelect.value !== 'other') {
            baUniDisplay = baUniSelect.options[baUniSelect.selectedIndex]?.text || '---';
        } else if (form.ba_university_other) {
            baUniDisplay = form.ba_university_other.value || '---';
        }
        if (document.getElementById('preview-baUni')) document.getElementById('preview-baUni').textContent = baUniDisplay;
        if (document.getElementById('preview-baFaculty')) document.getElementById('preview-baFaculty').textContent = form.ba_faculty ? (form.ba_faculty.value || '---') : '---';
        if (document.getElementById('preview-baDept')) document.getElementById('preview-baDept').textContent = form.ba_department ? (form.ba_department.value || '---') : '---';
        if (document.getElementById('preview-baSpec')) document.getElementById('preview-baSpec').textContent = form.ba_specialization ? (form.ba_specialization.value || '---') : '---';
        if (document.getElementById('preview-baRank')) document.getElementById('preview-baRank').textContent = form.ba_rank ? (form.ba_rank.value || '---') : '---';
        if (document.getElementById('preview-baGrantDate')) document.getElementById('preview-baGrantDate').textContent = formatDateDisplay(form.ba_grant_date ? form.ba_grant_date.value : '');

        // Group 4: Master & Track
        const maCountrySelect = form.ma_country_id;
        let maCountryText = maCountrySelect ? (maCountrySelect.options[maCountrySelect.selectedIndex]?.text || '---') : '---';
        if (document.getElementById('preview-maCountry')) document.getElementById('preview-maCountry').textContent = maCountryText;
        if (document.getElementById('preview-maUni')) document.getElementById('preview-maUni').textContent = form.ma_university_other ? (form.ma_university_other.value || '---') : '---';
        if (document.getElementById('preview-maFaculty')) document.getElementById('preview-maFaculty').textContent = form.ma_faculty ? (form.ma_faculty.value || '---') : '---';
        if (document.getElementById('preview-maDept')) document.getElementById('preview-maDept').textContent = form.ma_department ? (form.ma_department.value || '---') : '---';
        if (document.getElementById('preview-maGenSpec')) document.getElementById('preview-maGenSpec').textContent = form.ma_general_specialization ? (form.ma_general_specialization.value || '---') : '---';
        if (document.getElementById('preview-maSpec')) document.getElementById('preview-maSpec').textContent = form.ma_specialization ? (form.ma_specialization.value || '---') : '---';
        if (document.getElementById('preview-maSupervisor')) document.getElementById('preview-maSupervisor').textContent = form.ma_supervisor ? (form.ma_supervisor.value || '---') : '---';
        if (document.getElementById('preview-maRank')) document.getElementById('preview-maRank').textContent = form.ma_rank ? (form.ma_rank.value || '---') : '---';
        if (document.getElementById('preview-maRegDate')) document.getElementById('preview-maRegDate').textContent = formatDateDisplay(form.ma_registration_date ? form.ma_registration_date.value : '');
        if (document.getElementById('preview-maDefDate')) document.getElementById('preview-maDefDate').textContent = formatDateDisplay(form.ma_defense_date ? form.ma_defense_date.value : '');
        if (document.getElementById('preview-maGrantDate')) document.getElementById('preview-maGrantDate').textContent = formatDateDisplay(form.ma_grant_date ? form.ma_grant_date.value : '');
        if (document.getElementById('preview-maThesisTitle')) document.getElementById('preview-maThesisTitle').textContent = form.ma_thesis_title ? (form.ma_thesis_title.value || '---') : '---';

        const resExemptBadge = document.getElementById('preview-resExemptStatus');
        if (resExemptBadge) {
            if (isResidenceExemptChecked()) {
                resExemptBadge.className = 'badge bg-info text-dark border';
                resExemptBadge.textContent = 'معفى من شرط الإقامة (تخصص نظري / أونلاين)';
            } else {
                resExemptBadge.className = 'badge bg-primary-subtle text-primary border border-primary';
                resExemptBadge.textContent = 'يتطلب حضوراً وإقامة فعلية في بلد الدراسة';
            }
        }

        const trackBadge = document.getElementById('preview-maTrack');
        if (trackBadge) {
            if (IS_THEORETICAL_TRACK) {
                trackBadge.className = 'badge bg-success-subtle text-success border border-success';
                trackBadge.textContent = 'ماجستير خارجي - نظري (خبرة تدريسية سنتين فأكثر - رسم 125,000 ل.س)';
            } else {
                trackBadge.className = 'badge bg-primary-subtle text-primary border border-primary';
                trackBadge.textContent = 'ماجستير خارجي - تطبيقي (بدون شرط خبرة السنتين - رسم 100,000 ل.س)';
            }
        }

        if (IS_THEORETICAL_TRACK) {
            const stats = recalcTotalExperience();
            const hasExp = stats.isTheoretical;
            const expSummaryBadge = document.getElementById('preview-expSummaryBadge');
            const totalExpTime = document.getElementById('preview-totalExpTime');
            const listEl = document.getElementById('preview-experiences-list');

            if (expSummaryBadge) {
                expSummaryBadge.className = hasExp ? 'badge bg-success-subtle text-success border border-success' : 'badge bg-danger-subtle text-danger border border-danger';
                expSummaryBadge.textContent = hasExp ? 'مستوفي شرط السنتين (≥ 730 يوماً)' : 'أقل من سنتين (غير مستوفي)';
            }
            if (totalExpTime) {
                totalExpTime.textContent = stats.totalDays > 0 ? `${stats.years} سنة و ${stats.months} شهر و ${stats.days} يوم (إجمالي: ${stats.totalDays} يوماً)` : 'لا توجد فترات خبرة مسجلة';
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
                        itemsHtml += `<li><strong>${escapeHtml(inst)}</strong> ${role !== '---' ? '(' + escapeHtml(role) + ')' : ''}: من ${escapeHtml(fromD)} إلى ${escapeHtml(toD)} [${escapeHtml(dur)}]</li>`;
                    });
                    itemsHtml += '</ul>';
                    listEl.innerHTML = itemsHtml;
                } else {
                    listEl.innerHTML = '';
                }
            }
        }

        // Group 6: University Request
        document.getElementById('preview-reqNo').textContent = form.req_no.value || '---';
        document.getElementById('preview-reqDate').textContent = formatDateDisplay(form.req_date.value);

        recalculateTotalStay();
    }

    function triggerSaveDraft() {
        const form = document.getElementById('wizard-form');
        if (!form) return;

        // 1. Remove all required constraints and clear validity
        form.querySelectorAll('input, select, textarea').forEach(function(el) {
            el.removeAttribute('required');
            el.setCustomValidity('');
        });

        // 2. Set action and is_draft hidden fields
        const actionInput = document.getElementById('form-action-input');
        if (actionInput) actionInput.value = 'save_draft';

        const isDraftInput = document.getElementById('form-is-draft-input');
        if (isDraftInput) isDraftInput.value = '1';

        // 3. Submit form directly
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);
        syncBaUniversityFields();
        syncBoundMasterContext();
        syncResidenceExempt(isResidenceExemptChecked());
        recalculateTotalStay();
        recalcTotalExperience();

        document.getElementById('ma_university_other')?.addEventListener('input', syncBoundMasterContext);

        // Remove required attribute from any file inputs with existing uploaded badges
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            const parentDiv = input.closest('.border.rounded') || input.closest('.col-md-6') || input.parentElement;
            if (parentDiv && parentDiv.querySelector('.badge.bg-success-subtle')) {
                input.removeAttribute('required');
            }
        });

        const stepEls = document.querySelectorAll('.wizard-step');
        stepEls.forEach(el => {
            el.addEventListener('click', function() {
                const targetStep = parseInt(this.getAttribute('data-step'));
                if (targetStep < currentStep) {
                    goToStep(targetStep);
                } else if (targetStep > currentStep) {
                    if (validateCurrentStep(currentStep)) {
                        goToStep(targetStep);
                    }
                }
            });
        });

        const form = document.getElementById('wizard-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const actionInput = document.getElementById('form-action-input');
                const isDraftInput = document.getElementById('form-is-draft-input');
                const submitter = e.submitter;

                if (IS_ADMIN_EDIT ||
                    (actionInput && actionInput.value === 'save_draft') || 
                    (isDraftInput && isDraftInput.value === '1') || 
                    (submitter && (submitter.value === 'save_draft' || submitter.value === 'admin_save'))) {
                    form.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('required'));
                    return true;
                }

                // Check final step confirmation
                const chkConfirm = document.getElementById('chkConfirm');
                if (chkConfirm && !chkConfirm.checked) {
                    e.preventDefault();
                    goToStep(7);
                    chkConfirm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    chkConfirm.focus();
                    chkConfirm.setCustomValidity('يرجى المصادقة على الإقرار بصحة البيانات للمتابعة.');
                    chkConfirm.reportValidity();
                    return false;
                } else if (chkConfirm) {
                    chkConfirm.setCustomValidity('');
                }

                // Remove required from all inputs to ensure smooth and guaranteed submission
                form.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('required'));
                return true;
            });
        }
    });
</script>

@endsection
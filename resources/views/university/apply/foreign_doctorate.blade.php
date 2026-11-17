@extends(!empty($isAdminEdit) ? 'layouts.admin' : 'layouts.university')

@section('title', 'معاملة الدكتوراه غير السورية (الخارجية)')

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

    $todayDate = date('Y-m-d');
    $currentYear = date('Y');

    // 1. بيانات الثانوية
    $draftHsEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 4)->first() : null;
    $hsCountryId = $draftHsEd ? $draftHsEd->country_id : ($syriaId ?? 1);
    $hsType = $draftHsEd ? ($draftHsEd->section_name ?: $draftHsEd->specialization) : 'علمي';
    $hsYear = $draftHsEd ? ($draftHsEd->graduation_year ?: substr($draftHsEd->grant_date, 0, 4)) : '';
    $hsDecNo = $draftHsEd && $draftHsEd->notes && str_contains($draftHsEd->notes, 'رقم قرار') ? trim(preg_replace('/.*رقم قرار.*?:\s*([^\|]+).*/u', '$1', $draftHsEd->notes)) : ($draftHsEd?->equivalence_decision_no ?? '');
    $hsDecDate = $draftHsEd && $draftHsEd->notes && str_contains($draftHsEd->notes, 'تاريخ القرار:') ? trim(preg_replace('/.*تاريخ القرار:\s*([^\|]+).*/u', '$1', $draftHsEd->notes)) : ($draftHsEd?->equivalence_decision_date ? date('Y-m-d', strtotime($draftHsEd->equivalence_decision_date)) : '');
    $isHsForeign = old('hs_country_id', $hsCountryId) != ($syriaId ?? 1);

    // 2. بيانات الإجازة الجامعية الأولى (بدون قرار معادلة + اختيار جامعة سورية من القائمة)
    $draftBaEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 1)->first() : null;
    $baCountryId = old('ba_country_id', $draftBaEd ? $draftBaEd->country_id : ($syriaId ?? 1));
    $baUniId = old('ba_university_id', $draftBaEd ? $draftBaEd->university_id : null);
    $baUniOther = old('ba_university_other', $draftBaEd ? ($draftBaEd->university_other ?: (optional($draftBaEd->university)->name ?: $draftBaEd->university_name_manual)) : '');
    $isBaOtherSelected = ($baUniId === 'other') || ($baCountryId == ($syriaId ?? 1) && empty($baUniId) && !empty($baUniOther));
    if ($isBaOtherSelected) {
        $baUniId = 'other';
    }
    $baFaculty = $draftBaEd ? $draftBaEd->faculty : '';
    $baDept = $draftBaEd ? $draftBaEd->department : '';
    $baGenSpec = $draftBaEd ? ($draftBaEd->general_specialization ?: $draftBaEd->specialization) : '';
    $baRank = $draftBaEd ? ($draftBaEd->rank ?: 'جيد جداً') : 'جيد جداً';
    $baGrantDate = $draftBaEd && $draftBaEd->grant_date ? substr($draftBaEd->grant_date, 0, 10) : '';

    // 3. بيانات الماجستير (تاريخ الحصول على الدرجة فقط + checkbox معادلة اختياري + جامعة سورية من القائمة)
    $draftMaEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 2)->first() : null;
    $maCountryId = old('ma_country_id', $draftMaEd ? $draftMaEd->country_id : ($syriaId ?? 1));
    $maUniId = old('ma_university_id', $draftMaEd ? $draftMaEd->university_id : null);
    $maUniOther = old('ma_university_other', $draftMaEd ? ($draftMaEd->university_other ?: (optional($draftMaEd->university)->name ?: $draftMaEd->university_name_manual)) : '');
    $isMaOtherSelected = ($maUniId === 'other') || ($maCountryId == ($syriaId ?? 1) && empty($maUniId) && !empty($maUniOther));
    if ($isMaOtherSelected) {
        $maUniId = 'other';
    }
    $maFaculty = $draftMaEd ? $draftMaEd->faculty : '';
    $maDept = $draftMaEd ? $draftMaEd->department : '';
    $maGenSpec = $draftMaEd ? $draftMaEd->general_specialization : '';
    $maSpec = $draftMaEd ? ($draftMaEd->exact_specialization ?: $draftMaEd->specialization) : '';
    $maRank = $draftMaEd ? ($draftMaEd->rank ?: 'جيد جداً') : 'جيد جداً';
    $maGrantDate = $draftMaEd && $draftMaEd->grant_date ? substr($draftMaEd->grant_date, 0, 10) : '';
    $maDecNo = $draftMaEd && $draftMaEd->notes && str_contains($draftMaEd->notes, 'رقم قرار') ? trim(preg_replace('/.*رقم قرار.*?:\s*([^\|]+).*/u', '$1', $draftMaEd->notes)) : ($draftMaEd?->equivalence_decision_no ?? '');
    $maDecDate = $draftMaEd && $draftMaEd->notes && str_contains($draftMaEd->notes, 'تاريخ القرار:') ? trim(preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $draftMaEd->notes)) : ($draftMaEd?->equivalence_decision_date ? date('Y-m-d', strtotime($draftMaEd->equivalence_decision_date)) : '');
    $hasMaEquivalence = (bool) old('has_ma_equivalence', !empty($maDecNo) || !empty($maDecDate) || ($draftMaEd && $draftMaEd->attachments->where('attachment_type_id', 9)->isNotEmpty()));

    // 4. بيانات الدكتوراه الخارجية + الإعفاء من شرط الإقامة
    $draftPhdEd = optional($draft)->educations ? $draft->educations->where('education_level_id', 3)->first() : null;
    $phdCountryId = $draftPhdEd ? $draftPhdEd->country_id : null;
    $phdUniOther = $draftPhdEd ? ($draftPhdEd->university_other ?: (optional($draftPhdEd->university)->name ?: $draftPhdEd->university_name_manual)) : '';
    $phdFaculty = $draftPhdEd ? $draftPhdEd->faculty : '';
    $phdDept = $draftPhdEd ? $draftPhdEd->department : '';
    $phdGenSpec = $draftPhdEd ? ($draftPhdEd->general_specialization ?: $draftPhdEd->department) : '';
    $phdSpec = $draftPhdEd ? ($draftPhdEd->exact_specialization ?: $draftPhdEd->specialization) : '';
    $phdRank = $draftPhdEd ? ($draftPhdEd->rank ?: 'شرف / امتياز') : 'شرف / امتياز';
    $phdThesis = $draftPhdEd ? $draftPhdEd->thesis_title : '';
    $phdSupervisor = $draftPhdEd ? ($draftPhdEd->supervisor_name ?: $draftPhdEd->supervisor) : '';
    $phdStudySystem = $draftPhdEd ? $draftPhdEd->study_system : 'أطروحة بحثية';
    $phdLang = $draftPhdEd ? $draftPhdEd->study_language : 'العربية';
    $phdRegDate = $draftPhdEd && $draftPhdEd->registration_date ? substr($draftPhdEd->registration_date, 0, 10) : '';
    $phdDefDate = $draftPhdEd && $draftPhdEd->defense_date ? substr($draftPhdEd->defense_date, 0, 10) : '';
    $phdGrantDate = $draftPhdEd && $draftPhdEd->grant_date ? substr($draftPhdEd->grant_date, 0, 10) : '';
    $isResExempt = (bool) old('is_residence_exempt', optional($draft)->is_residence_exempt ?? false);

    // مصفوفة استرجاع المرفقات المحفوظة بالمسودة بدقة تامة (16 بنداً + قرار معادلة الماجستير الشرطي)
    $existingFilesMap = [];
    if ($draft && $draft->educations) {
        foreach ($draft->educations as $ed) {
            foreach ($ed->attachments as $att) {
                $typeId = (int)$att->attachment_type_id;
                $note = $att->notes ?? '';

                if ($typeId === 1 || (str_contains($note, 'ثانوية') && !str_contains($note, 'معادلة'))) $existingFilesMap['file_secondary_cert'] = $att;
                if ($typeId === 2 || str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة درجة البكالوريا')) $existingFilesMap['file_hs_decision'] = $att;
                if ($typeId === 3 || (str_contains($note, 'الإجازة') && !str_contains($note, 'معادلة'))) $existingFilesMap['file_bachelor_cert'] = $att;
                if ($typeId === 6 || (str_contains($note, 'شهادة الماجستير') && !str_contains($note, 'معادلة') && !str_contains($note, 'ملخص'))) $existingFilesMap['file_master_cert'] = $att;
                if ($typeId === 9 || str_contains($note, 'معادلة شهادة الماجستير') || str_contains($note, 'معادلة الماجستير')) $existingFilesMap['file_ma_decision'] = $att;
                if ($typeId === 10 || (str_contains($note, 'شهادة الدكتوراه') && !str_contains($note, 'ملخص') && !str_contains($note, 'كاملة'))) $existingFilesMap['file_phd_cert'] = $att;
                if ($typeId === 11 || str_contains($note, 'مجلس') || str_contains($note, 'تواريخ التسجيل والمناقشة')) $existingFilesMap['file_phd_council_decisions'] = $att;
                if ($typeId === 14 || str_contains($note, 'السيرة')) $existingFilesMap['file_cv'] = $att;
                if ($typeId === 17 || str_contains($note, '125,000') || str_contains($note, '125 ألف') || str_contains($note, 'رسم تعادل الدكتوراه')) $existingFilesMap['file_fees_receipt'] = $att;
                if ($typeId === 22 || str_contains($note, 'المكتبة الوطنية')) $existingFilesMap['file_library_receipt'] = $att;
                if ($typeId === 5 && (str_contains($note, 'الدكتوراه') || str_contains($note, 'كشف علامات الدكتوراه'))) $existingFilesMap['file_phd_transcript'] = $att;
                if ($typeId === 19 || str_contains($note, 'ICDL')) $existingFilesMap['file_icdl_cert'] = $att;
                if ($typeId === 18 || str_contains($note, 'الإنكليزية') || str_contains($note, 'إنكليزية')) $existingFilesMap['file_english_cert'] = $att;
                if ($typeId === 13) {
                    if (str_contains($note, 'صفحات الأختام') || str_contains($note, 'جواز السفر مع')) {
                        $existingFilesMap['file_passport'] = $att;
                    } else {
                        $existingFilesMap['file_national_id'] = $att;
                    }
                } elseif (str_contains($note, 'هوية') || str_contains($note, 'إثبات الشخصية')) {
                    $existingFilesMap['file_national_id'] = $att;
                } elseif (str_contains($note, 'صفحات الأختام')) {
                    $existingFilesMap['file_passport'] = $att;
                }
                if ($typeId === 24 || str_contains($note, 'حركة الهجرة والجوازات') || str_contains($note, 'الهجرة والجوازات')) $existingFilesMap['file_immigration_movement'] = $att;
                if ($typeId === 23 || str_contains($note, 'مرفقات ووثائق أخرى') || str_contains($note, 'مرفقات أخرى')) $existingFilesMap['file_other_attachments'] = $att;
            }
        }
    }
@endphp

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

@if(!empty($isAdminEdit))
    @include('university.apply.partials._admin_edit_banner', ['wizardTitle' => 'الدكتوراه غير السورية (الخارجية)'])
@else
<!-- PAGE TITLE & BREADCRUMB -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}">نوع المعاملة</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">معاملة الدكتوراه غير السورية</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid fa-award me-2" style="color: var(--heritage-gold);"></i> معاملة تعادل الدكتوراه غير السورية (الخارجية)
                </h3>
                <p class="body-md text-muted mb-0">لتعادل وتقييم درجة الدكتوراه الممنوحة من جامعات عربية أو أجنبية (فحص الإقامة، تقييم الإنتاج العلمي، ومقابلة الأهلية).</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canSaveDraft)
                <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" onclick="triggerSaveDraft()" formnovalidate title="حفظ البيانات المدخلة كمسودة للعودة إليها لاحقاً">
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

        <!-- WIZARD PROGRESS STEPPERS (8 STEPS) -->
        <div class="wizard-steps-container mb-5">
            <div class="wizard-steps">
                <div class="wizard-step active" data-step="1" id="step-node-1">
                    <div class="wizard-icon"><i class="fa-solid fa-user"></i></div>
                    <div class="wizard-label">1. البيانات الشخصية</div>
                </div>
                <div class="wizard-step" data-step="2" id="step-node-2">
                    <div class="wizard-icon"><i class="fa-solid fa-school"></i></div>
                    <div class="wizard-label">2. الثانوية العامة</div>
                </div>
                <div class="wizard-step" data-step="3" id="step-node-3">
                    <div class="wizard-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="wizard-label">3. الإجازة الجامعية</div>
                </div>
                <div class="wizard-step" data-step="4" id="step-node-4">
                    <div class="wizard-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="wizard-label">4. درجة الماجستير</div>
                </div>
                <div class="wizard-step" data-step="5" id="step-node-5">
                    <div class="wizard-icon"><i class="fa-solid fa-award"></i></div>
                    <div class="wizard-label">5. الدكتوراه الخارجية</div>
                </div>
                <div class="wizard-step" data-step="6" id="step-node-6">
                    <div class="wizard-icon"><i class="fa-solid fa-passport"></i></div>
                    <div class="wizard-label">6. حركات الإقامة والسفر</div>
                </div>
                <div class="wizard-step" data-step="7" id="step-node-7">
                    <div class="wizard-icon"><i class="fa-solid fa-paperclip"></i></div>
                    <div class="wizard-label">7. المرفقات والوثائق</div>
                </div>
                <div class="wizard-step" data-step="8" id="step-node-8">
                    <div class="wizard-icon"><i class="fa-solid fa-print"></i></div>
                    <div class="wizard-label">8. المراجعة والإرسال</div>
                </div>
            </div>
        </div>

        <!-- Form Tag -->
        <form action="{{ !empty($isAdminEdit) ? $adminSubmitRoute : route('university.apply.foreign_doctorate.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form" novalidate>
            @csrf
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">
            <input type="hidden" name="action" id="form-action-input" value="submit_final">
            <input type="hidden" name="is_draft" id="form-is-draft-input" value="0">

            {{-- =========================================================================
                 STEP 1: PERSONAL DETAILS & UNIVERSITY EVALUATION REQUEST
            ========================================================================= --}}
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب الجامعة الخاصة
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
                        <input type="text" name="national_id" id="input-nationalId" 
                               class="form-control academic-input" 
                               placeholder="الرقم الوطني المكون من 11 خانة رقمية" 
                               value="{{ old('national_id', $draftNatId) }}" 
                               maxlength="11" 
                               pattern="\d{11}" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                               required>
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
                        <input type="date" name="dob" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('dob', $draftCandidate->dob ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الجنس <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select academic-input" required>
                            <option value="ذكر" {{ old('gender', $draftCandidate->gender ?? '') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                            <option value="أنثى" {{ old('gender', $draftCandidate->gender ?? '') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">رقم الهاتف المحمول <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control academic-input" placeholder="09xxxxxxxx" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="{{ old('mobile', $draftCandidate->mobile ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control academic-input" placeholder="example@domain.com" value="{{ old('email', $draftCandidate->email ?? auth()->user()->email) }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label label-sm fw-bold">مكان الإقامة / العنوان الحالي في سوريا <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control academic-input" placeholder="المحافظة، المدينة، المنطقة، الشارع" value="{{ old('address', $draftCandidate->address ?? '') }}" required>
                    </div>

                    <!-- بيانات كتاب الترشيح -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-prestigious border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-file-lines me-1" style="color: var(--heritage-gold);"></i> بيانات كتاب الترشيح الصادر عن الجامعة الخاصة
                        </h6>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">رقم كتاب الجامعة <span class="text-danger">*</span></label>
                        <input type="text" name="req_no" class="form-control academic-input" 
                               placeholder="أرقام فقط (مثال: 105)" 
                               value="{{ old('req_no', optional($draft)->new_uni_request_no ?? '') }}" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">تاريخ كتاب الجامعة <span class="text-danger">*</span></label>
                        <input type="date" name="req_date" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('req_date', optional($draft)->new_uni_request_date ? date('Y-m-d', strtotime(optional($draft)->new_uni_request_date)) : '') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">الكلية المرشح للتدريس فيها بالجامعة الخاصة <span class="text-danger">*</span></label>
                        <input type="text" name="work_faculty" class="form-control academic-input" placeholder="مثال: كلية الدراسات الإسلامية" value="{{ old('work_faculty', optional($draft)->work_faculty ?? '') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">القسم / الشعبة المطلوب للتدريس فيها <span class="text-danger">*</span></label>
                        <input type="text" name="work_department" class="form-control academic-input" placeholder="مثال: شعبة التفسير وعلوم القرآن" value="{{ old('work_department', optional($draft)->work_department ?? '') }}" required>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 2: HIGH SCHOOL (الشهادة الثانوية)
            ========================================================================= --}}
            <div class="form-section" id="step-2" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-school fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات الشهادة الثانوية العامة
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">بلد الحصول على الثانوية <span class="text-danger">*</span></label>
                        <select name="hs_country_id" id="hs_country_id" class="form-select academic-input" onchange="toggleHsDecisionFields(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('hs_country_id', $hsCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">نوع الشهادة الثانوية <span class="text-danger">*</span></label>
                        <select name="hs_type" class="form-select academic-input" required>
                            <option value="علمي" {{ old('hs_type', $hsType) == 'علمي' ? 'selected' : '' }}>علمي</option>
                            <option value="أدبي" {{ old('hs_type', $hsType) == 'أدبي' ? 'selected' : '' }}>أدبي</option>
                            <option value="شرعية" {{ old('hs_type', $hsType) == 'شرعية' ? 'selected' : '' }}>شرعية</option>
                            <option value="صناعي" {{ old('hs_type', $hsType) == 'صناعي' ? 'selected' : '' }}>صناعي</option>
                            <option value="تجاري" {{ old('hs_type', $hsType) == 'تجاري' ? 'selected' : '' }}>تجاري</option>
                            <option value="أخرى" {{ old('hs_type', $hsType) == 'أخرى' ? 'selected' : '' }}>أخرى (شهادة غير سورية)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">سنة الحصول على الثانوية <span class="text-danger">*</span></label>
                        <input type="number" name="hs_grant_date" id="input-hsYear" class="form-control academic-input" placeholder="مثال: 2002" min="1950" max="{{ $currentYear }}" value="{{ old('hs_grant_date', $hsYear) }}" required>
                    </div>

                    <div class="col-12" id="hs_decision_box" style="display: {{ $isHsForeign ? 'block' : 'none' }};">
                        <div class="row g-3 p-3 bg-light rounded border border-warning">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">رقم قرار معادلة الشهادة الثانوية غير السورية <span class="text-danger" id="hs_dec_req_badge">*</span></label>
                                <input type="text" name="hs_decision_no" id="hs_decision_no" class="form-control academic-input" placeholder="أدخل رقم قرار المعادلة الصادر عن وزارة التربية" value="{{ old('hs_decision_no', $hsDecNo) }}" {{ $isHsForeign ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">تاريخ صدور قرار معادلة الشهادة الثانوية <span class="text-danger" id="hs_date_req_badge">*</span></label>
                                <input type="date" name="hs_decision_date" id="hs_decision_date" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('hs_decision_date', $hsDecDate) }}" {{ $isHsForeign ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 3: BACHELOR DEGREE (الإجازة الجامعية الأولى)
            ========================================================================= --}}
            <div class="form-section" id="step-3" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: درجة الإجازة الجامعية الأولى (البكالوريوس)
                </h5>

                {{-- الصف الأول: البلد + الجامعة (قائمة سورية عند سوريا / نصي عند غير سوريا أو أخرى) --}}
                <div class="row g-3">
                    <div class="col-md-6" id="ba_country_col">
                        <label class="form-label label-sm fw-bold">1. بلد الحصول على الإجازة <span class="text-danger">*</span></label>
                        <select name="ba_country_id" id="ba_country_id" class="form-select academic-input" onchange="syncBaUniversityFields()" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ $baCountryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="ba_uni_select_col" style="display: {{ $baCountryId == ($syriaId ?? 1) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. الجامعة المانحة للإجازة (سوريا) <span class="text-danger">*</span></label>
                        <select name="ba_university_id" id="ba_university_id" class="form-select academic-input" onchange="syncBaUniversityFields()">
                            @include('university.apply.partials._syrian_university_optgroups', [
                                'selectedId' => $baUniId,
                                'isOther' => $isBaOtherSelected,
                                'publicUnis' => $syrianPublicUnis,
                                'privateUnis' => $syrianPrivateUnis,
                                'institutes' => $syrianInstitutes,
                                'fallbackUni' => optional($draftBaEd)->university,
                            ])
                        </select>
                    </div>

                    <div class="col-md-6" id="ba_uni_other_col" style="display: {{ ($baCountryId != ($syriaId ?? 1) || $isBaOtherSelected) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. اسم الجامعة المانحة للإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_university_other" id="ba_university_other" class="form-control academic-input" placeholder="مثال: جامعة القاهرة / الجامعة الأردنية" value="{{ $baUniOther }}">
                    </div>
                </div>

                {{-- الصف الثاني: الكلية - القسم - التخصص العام --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">3. الكلية المانحة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية" value="{{ old('ba_faculty', $baFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">4. القسم لدرجة الإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: قسم هندسة البرمجيات" value="{{ old('ba_department', $baDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">5. التخصص العام لدرجة الإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_general_specialization" id="input-baGenSpec" class="form-control academic-input" placeholder="مثال: الهندسة المعلوماتية" value="{{ old('ba_general_specialization', $baGenSpec) }}" required>
                    </div>
                </div>

                {{-- الصف الثالث: التقدير - تاريخ المنح --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">6. التقدير / المعدل العام <span class="text-danger">*</span></label>
                        <select name="ba_rank" id="input-baRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ (old('ba_rank', $baRank) == 'ممتاز' || old('ba_rank', $baRank) == 'امتياز') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ old('ba_rank', $baRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ba_rank', $baRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ old('ba_rank', $baRank) == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">7. تاريخ الحصول على الإجازة الجامعية <span class="text-danger">*</span></label>
                        <input type="date" name="ba_grant_date" id="input-baGrantDate" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('ba_grant_date', $baGrantDate) }}" required>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 4: MASTER DEGREE (درجة الماجستير — داخل أو خارج سوريا + تاريخ المنح فقط + معادلة اختيارية)
            ========================================================================= --}}
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: درجة الماجستير السابقة
                </h5>

                {{-- الصف الأول: البلد + الجامعة (قائمة سورية عند سوريا / نصي عند غير سوريا أو أخرى) --}}
                <div class="row g-3">
                    <div class="col-md-6" id="ma_country_col">
                        <label class="form-label label-sm fw-bold">1. بلد الحصول على الماجستير <span class="text-danger">*</span></label>
                        <select name="ma_country_id" id="ma_country_id" class="form-select academic-input" onchange="syncMaUniversityFields()" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ $maCountryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="ma_uni_select_col" style="display: {{ $maCountryId == ($syriaId ?? 1) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. الجامعة المانحة للماجستير (سوريا) <span class="text-danger">*</span></label>
                        <select name="ma_university_id" id="ma_university_id" class="form-select academic-input" onchange="syncMaUniversityFields()">
                            @include('university.apply.partials._syrian_university_optgroups', [
                                'selectedId' => $maUniId,
                                'isOther' => $isMaOtherSelected,
                                'publicUnis' => $syrianPublicUnis,
                                'privateUnis' => $syrianPrivateUnis,
                                'institutes' => $syrianInstitutes,
                                'fallbackUni' => optional($draftMaEd)->university,
                            ])
                        </select>
                    </div>

                    <div class="col-md-6" id="ma_uni_other_col" style="display: {{ ($maCountryId != ($syriaId ?? 1) || $isMaOtherSelected) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold">2. اسم الجامعة المانحة للماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_university_other" id="ma_university_other" class="form-control academic-input" placeholder="مثال: جامعة القاهرة / الجامعة الأردنية" value="{{ $maUniOther }}">
                    </div>
                </div>

                {{-- الصف الثاني: الكلية - القسم - التخصص العام --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">3. الكلية المانحة لدرجة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية" value="{{ old('ma_faculty', $maFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">4. القسم لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="مثال: قسم هندسة البرمجيات" value="{{ old('ma_department', $maDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">5. التخصص العام لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_general_specialization" id="input-maGenSpec" class="form-control academic-input" placeholder="مثال: الهندسة المعلوماتية" value="{{ old('ma_general_specialization', $maGenSpec ?: $maFaculty) }}" required>
                    </div>
                </div>

                {{-- الصف الثالث: التخصص الدقيق - التقدير - تاريخ الحصول على الدرجة فقط --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">6. التخصص الدقيق لدرجة الماجستير <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ma_specialization" id="input-maSpec" class="form-control academic-input" placeholder="مثال: الذكاء الصنعي وأمن المعلومات" value="{{ old('ma_specialization', $maSpec) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">7. التقدير <span class="text-danger">*</span></label>
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="ممتاز" {{ (old('ma_rank', $maRank) == 'ممتاز' || old('ma_rank', $maRank) == 'امتياز') ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ old('ma_rank', $maRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ma_rank', $maRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ old('ma_rank', $maRank) == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">8. تاريخ الحصول على الدرجة <span class="text-danger">*</span></label>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('ma_grant_date', $maGrantDate) }}" required>
                    </div>
                </div>

                {{-- Checkbox: هل تمت معادلة درجة الماجستير من قبل؟ --}}
                <div class="mohe-exempt-card p-3 p-md-4 mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="mohe-emblem-box">
                                <i class="fa-solid fa-stamp"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">هل تمت معادلة درجة الماجستير من قبل؟</h6>
                                <p class="text-muted fs-8 mb-0 mt-1">عند تفعيل هذا الخيار، يُطلب إدخال رقم وتاريخ قرار معادلة الماجستير، ويصبح مرفق <strong>قرار معادلة شهادة الماجستير</strong> مطلوباً في خطوة المرفقات.</p>
                            </div>
                        </div>
                        <div class="mohe-exempt-switch-box py-2 px-3" style="min-width: auto;">
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0" type="checkbox" id="check-hasMaEquivalence" name="has_ma_equivalence" value="1" {{ $hasMaEquivalence ? 'checked' : '' }} onchange="toggleMaEquivalenceSection(this.checked)" style="width: 2.75em; height: 1.4em; cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark fs-8 mb-0" for="check-hasMaEquivalence" style="cursor: pointer;">
                                    تمت معادلة الماجستير مسبقاً
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="ma-equivalence-section" class="mt-3 pt-3 border-top" style="display: {{ $hasMaEquivalence ? 'block' : 'none' }}; border-color: #e2e8f0 !important;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold">رقم قرار معادلة شهادة الماجستير <span class="text-danger">*</span></label>
                                <input type="text" name="ma_decision_no" id="ma_decision_no" class="form-control academic-input" placeholder="رقم قرار التعادل الصادر عن مجلس التعليم العالي" value="{{ old('ma_decision_no', $maDecNo) }}" {{ $hasMaEquivalence ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold">تاريخ قرار معادلة شهادة الماجستير <span class="text-danger">*</span></label>
                                <input type="date" name="ma_decision_date" id="ma_decision_date" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('ma_decision_date', $maDecDate) }}" {{ $hasMaEquivalence ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 5: FOREIGN DOCTORATE (درجة الدكتوراه الخارجية)
            ========================================================================= --}}
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-award fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: درجة الدكتوراه غير السورية (الخارجية) المراد معادلتها
                </h5>

                {{-- الصف الأول: 4 أعمدة (البلد - الجامعة - الكلية - التقدير) --}}
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">1. بلد الحصول على الدكتوراه <span class="text-danger">*</span></label>
                        <select name="phd_country_id" id="phd_country_id" class="form-select academic-input" onchange="syncBoundDoctorateContext()" required>
                            <option value="">-- اختر بلد دراسة الدكتوراه --</option>
                            @foreach($countries as $c)
                                @if($c->id != ($syriaId ?? 1) && $c->name !== 'سوريا')
                                    <option value="{{ $c->id }}" {{ old('phd_country_id', $phdCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">2. الجامعة المانحة للدكتوراه <span class="text-danger">*</span></label>
                        <input type="text" name="phd_university_other" id="input-phdUni" class="form-control academic-input" placeholder="مثال: جامعة القاهرة / كلية الدعوة الجامعية" value="{{ old('phd_university_other', $phdUniOther) }}" oninput="syncBoundDoctorateContext()" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">3. الكلية المانحة للدكتوراه <span class="text-danger">*</span></label>
                        <input type="text" name="phd_faculty" id="input-phdFaculty" class="form-control academic-input" placeholder="مثال: كلية الدراسات العليا" value="{{ old('phd_faculty', $phdFaculty) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">4. التقدير <span class="text-danger">*</span></label>
                        <select name="phd_rank" id="input-phdRank" class="form-select academic-input" required>
                            <option value="شرف / امتياز" {{ old('phd_rank', $phdRank) == 'شرف / امتياز' ? 'selected' : '' }}>شرف / امتياز</option>
                            <option value="ممتاز" {{ old('phd_rank', $phdRank) == 'ممتاز' ? 'selected' : '' }}>ممتاز</option>
                            <option value="جيد جداً" {{ old('phd_rank', $phdRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('phd_rank', $phdRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                        </select>
                    </div>
                </div>

                {{-- الصف الثاني: 3 أعمدة (القسم - الاختصاص العام - الاختصاص الدقيق اختياري) --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">5. القسم لدرجة الدكتوراه <span class="text-danger">*</span></label>
                        <input type="text" name="phd_department" id="input-phdDept" class="form-control academic-input" placeholder="مثال: قسم الهندسة الطبية" value="{{ old('phd_department', $phdDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">6. الاختصاص العام <span class="text-danger">*</span></label>
                        <input type="text" name="phd_general_specialization" id="input-phdGenSpec" class="form-control academic-input" placeholder="مثال: الهندسة المعلوماتية" value="{{ old('phd_general_specialization', $phdGenSpec ?: $phdDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">7. الاختصاص الدقيق <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="phd_specialization" id="input-phdSpec" class="form-control academic-input" placeholder="مثال: معالجة الإشارات الحيوية" value="{{ old('phd_specialization', $phdSpec) }}">
                    </div>
                </div>

                {{-- الصف الثالث: عنوان الأطروحة - اسم المشرف - نظام الدراسة --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">8. عنوان أطروحة الدكتوراه بالتفصيل <span class="text-danger">*</span></label>
                        <textarea name="phd_thesis_title" id="input-phdThesisTitle" rows="1" class="form-control academic-input" placeholder="أدخل العنوان الكامل كما هو في شهادة الدكتوراه والأطروحة" required>{{ old('phd_thesis_title', $phdThesis) }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">9. اسم الأستاذ المشرف على الأطروحة <span class="text-danger">*</span></label>
                        <input type="text" name="phd_supervisor" id="input-phdSupervisor" class="form-control academic-input" placeholder="مثال: أ.د. فلان الفلاني" value="{{ old('phd_supervisor', $phdSupervisor) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">10. نظام الدراسة في الدكتوراه <span class="text-danger">*</span></label>
                        <select name="phd_study_system" id="input-phdStudySystem" class="form-select academic-input" required>
                            <option value="أطروحة بحثية" {{ old('phd_study_system', $phdStudySystem) == 'أطروحة بحثية' ? 'selected' : '' }}>أطروحة بحثية (Thesis Only)</option>
                            <option value="مقررات وأطروحة" {{ old('phd_study_system', $phdStudySystem) == 'مقررات وأطروحة' ? 'selected' : '' }}>مقررات دراسية وأطروحة (Courses + Thesis)</option>
                            <option value="ساعات معتمدة" {{ old('phd_study_system', $phdStudySystem) == 'ساعات معتمدة' ? 'selected' : '' }}>ساعات معتمدة (Credit Hours)</option>
                        </select>
                    </div>
                </div>

                {{-- الصف الرابع: تاريخ التسجيل - تاريخ المناقشة - تاريخ المنح - لغة الدراسة --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">11. تاريخ التسجيل بالدرجة <span class="text-danger">*</span></label>
                        <input type="date" name="phd_registration_date" id="input-phdRegDate" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('phd_registration_date', $phdRegDate) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">12. تاريخ مناقشة الأطروحة <span class="text-danger">*</span></label>
                        <input type="date" name="phd_defense_date" id="input-phdDefDate" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('phd_defense_date', $phdDefDate) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">13. تاريخ الحصول على الدرجة <span class="text-danger">*</span></label>
                        <input type="date" name="phd_grant_date" id="input-phdGrantDate" class="form-control academic-input" max="{{ $todayDate }}" value="{{ old('phd_grant_date', $phdGrantDate) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">14. لغة الدراسة والبحث <span class="text-danger">*</span></label>
                        <select name="phd_study_language" id="input-phdLang" class="form-select academic-input" required>
                            <option value="العربية" {{ old('phd_study_language', $phdLang) == 'العربية' ? 'selected' : '' }}>العربية</option>
                            <option value="الإنكليزية" {{ old('phd_study_language', $phdLang) == 'الإنكليزية' ? 'selected' : '' }}>الإنكليزية</option>
                            <option value="الفرنسية" {{ old('phd_study_language', $phdLang) == 'الفرنسية' ? 'selected' : '' }}>الفرنسية</option>
                            <option value="الروسية" {{ old('phd_study_language', $phdLang) == 'الروسية' ? 'selected' : '' }}>الروسية</option>
                            <option value="الألمانية" {{ old('phd_study_language', $phdLang) == 'الألمانية' ? 'selected' : '' }}>الألمانية</option>
                            <option value="الإسبانية" {{ old('phd_study_language', $phdLang) == 'الإسبانية' ? 'selected' : '' }}>الإسبانية</option>
                            <option value="أخرى" {{ old('phd_study_language', $phdLang) == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                        </select>
                    </div>
                </div>

                {{-- بطاقة شرط الإقامة والإعفاء (متزامنة مع الخطوة السادسة) --}}
                <div class="mohe-exempt-card p-3 p-md-4 mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3" style="max-width: 700px;">
                            <div class="mohe-emblem-box mt-0.5">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                    <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                                        هل شهادة الدكتوراه مأخوذة في تخصص نظري ومعفاة من شرط الإقامة؟
                                    </h6>
                                    <span class="badge rounded-pill px-2.5 py-1 fs-9 fw-semibold" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                                        تخصص نظري / إشراف مشترك
                                    </span>
                                </div>
                                <p class="text-muted fs-8 mb-2 lh-base">
                                    يُفعّل هذا الخيار حصراً للتخصصات النظرية المعفاة من الإقامة الفعلية (مثل اللغة العربية أو الفلسفة أو الحقوق أو التخصصات النظرية عن بُعد).
                                </p>
                                <div class="d-flex flex-wrap gap-2 fs-9">
                                    <span class="px-2.5 py-1 rounded" style="background: #f0f9f4; color: #14532d; border: 1px solid #bbf7d0;">
                                        <i class="fa-solid fa-check me-1"></i> عند التفعيل: حركات الإقامة وجواز السفر وبيان الحركة اختيارية
                                    </span>
                                    <span class="px-2.5 py-1 rounded" style="background: #f8fafc; color: #475569; border: 1px solid #dce3ec;">
                                        <i class="fa-solid fa-building-columns me-1"></i> التخصصات التطبيقية والهندسية والطبية: ملزمة بالإقامة الفعلية
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mohe-exempt-switch-box d-flex flex-column align-items-start gap-2">
                            <span class="fs-9 fw-semibold text-uppercase" style="color: #64748b; letter-spacing: 0.3px;">الوضع المعتمد لشرط الإقامة</span>
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0" type="checkbox" id="input-isResidenceExempt" name="is_residence_exempt" value="1" {{ $isResExempt ? 'checked' : '' }} onchange="syncResidenceExempt(this.checked)" style="width: 2.75em; height: 1.4em; cursor: pointer;">
                                <label class="form-check-label fw-bold fs-8 mb-0 {{ $isResExempt ? 'text-success' : 'text-dark' }}" for="input-isResidenceExempt" id="lbl-residenceExemptStep5" style="cursor: pointer;">
                                    {{ $isResExempt ? 'معفى من شرط الإقامة (تخصص نظري)' : 'يتطلب حضوراً وإقامة فعلية' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 6: RESIDENCE & TRAVEL MOVEMENTS (الواجهة المؤسسية لحركات الإقامة وحساب المدة)
            ========================================================================= --}}
            <div class="form-section" id="step-6" style="display: none;">
                <input type="hidden" name="residences_submitted_flag" value="1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-passport fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: إقامة المرشح — حركات الدخول والخروج من بلد دراسة الدكتوراه
                </h5>

                <!-- شريط الربط التلقائي لبلد الدراسة والجامعة + حالة الإعفاء -->
                <div class="mohe-context-bar p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-earth-americas fs-7"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-9 fw-semibold">الدولة (بلد دراسة الدكتوراه)</div>
                                <div id="step6-bound-country" class="fw-bold fs-7" style="color: var(--imperial-navy);">---</div>
                            </div>
                        </div>
                        <div class="vr opacity-25 d-none d-sm-block" style="height: 28px;"></div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="mohe-emblem-box" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-building-columns fs-7"></i>
                            </div>
                            <div>
                                <div class="text-muted fs-9 fw-semibold">الجامعة المانحة</div>
                                <div id="step6-bound-university" class="fw-bold fs-7" style="color: var(--imperial-navy);">---</div>
                            </div>
                        </div>
                    </div>
                    <div class="mohe-exempt-switch-box py-2 px-3" style="min-width: auto;">
                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                            <input class="form-check-input m-0" type="checkbox" id="step6-residence-exempt-switch" {{ $isResExempt ? 'checked' : '' }} onchange="syncResidenceExempt(this.checked)" style="cursor: pointer;">
                            <label class="form-check-label fw-bold fs-8 text-dark mb-0" for="step6-residence-exempt-switch" style="cursor: pointer;">
                                الدكتوراه معفاة من شرط الإقامة (تخصص نظري)
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
                                <input type="date" id="mov_entry_date" class="form-control academic-input" max="{{ $todayDate }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-solid fa-plane-arrival me-1 text-muted"></i> مطار / منفذ الدخول <span class="text-danger res-req-star">*</span></label>
                                <input type="text" id="mov_entry_airport" class="form-control academic-input" placeholder="مثال: مطار بيروت الدولي">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-regular fa-calendar-xmark me-1 text-muted"></i> تاريخ الخروج من بلد الدراسة <span class="text-danger res-req-star">*</span></label>
                                <input type="date" id="mov_exit_date" class="form-control academic-input" max="{{ $todayDate }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label label-sm fw-bold text-dark"><i class="fa-solid fa-plane-departure me-1 text-muted"></i> مطار / بلد الخروج <span class="text-danger res-req-star">*</span></label>
                                <input type="text" id="mov_exit_airport" class="form-control academic-input" placeholder="مثال: معبر المصنع / مطار القاهرة">
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
                                    $draftResidences = optional($draftPhdEd)->residences ? $draftPhdEd->residences->sortBy('entry_date')->values() : collect();
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

                <!-- TOTAL STAY DURATION SUMMARY SCORECARD -->
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
                 STEP 7: REQUIRED ATTACHMENTS (المرفقات والوثائق الـ 16 المعتمدة)
            ========================================================================= --}}
            <div class="form-section" id="step-7" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 7: المرفقات والمستندات الثبوتية المطلوبة (PDF فقط - حد أقصى 2 ميغابايت لكل ملف)
                </h5>

                <div class="row g-3">
                    <!-- 1. صورة عن الهوية الشخصية أو جواز السفر (ID 13) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">1. صورة عن الهوية الشخصية أو جواز السفر <span class="text-danger">*</span></label>
                            <input type="file" name="file_national_id" id="input_file_national_id" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_national_id']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_national_id']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_national_id']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. شهادة البكالوريا (ID 1) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">2. شهادة البكالوريا <span class="text-danger">*</span></label>
                            <input type="file" name="file_secondary_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_secondary_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_secondary_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_secondary_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. قرار معادلة درجة البكالوريا من وزارة التربية (ID 2 - شرطي إذا البكالوريا غير سورية) -->
                    <div class="col-md-6" id="hs_decision_att_box" style="display: {{ $isHsForeign ? 'block' : 'none' }};">
                        <div class="border rounded p-3 bg-light h-100 border-warning">
                            <label class="form-label label-sm fw-bold">3. قرار معادلة درجة البكالوريا من وزارة التربية <span class="text-danger">*</span></label>
                            <input type="file" name="file_hs_decision" id="file_hs_decision" class="form-control academic-input" accept=".pdf" {{ ($isHsForeign && empty($existingFilesMap['file_hs_decision'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_hs_decision']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_hs_decision']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. الإجازة الجامعية مصدقة أصولاً (ID 3) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">4. الإجازة الجامعية مصدقة أصولاً <span class="text-danger">*</span></label>
                            <input type="file" name="file_bachelor_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_bachelor_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_bachelor_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_bachelor_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. شهادة الماجستير مصدقة أصولاً (ID 6) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">5. شهادة الماجستير مصدقة أصولاً <span class="text-danger">*</span></label>
                            <input type="file" name="file_master_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_master_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_master_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_master_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- شرطي: قرار معادلة شهادة الماجستير (ID 9 - يظهر عند تفعيل checkbox المعادلة) -->
                    <div class="col-md-6" id="ma_decision_att_box" style="display: {{ $hasMaEquivalence ? 'block' : 'none' }};">
                        <div class="border rounded p-3 bg-light h-100 border-warning">
                            <label class="form-label label-sm fw-bold">قرار معادلة شهادة الماجستير <span class="text-danger">*</span></label>
                            <input type="file" name="file_ma_decision" id="file_ma_decision" class="form-control academic-input" accept=".pdf" {{ ($hasMaEquivalence && empty($existingFilesMap['file_ma_decision'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_ma_decision']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_ma_decision']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. شهادة الدكتوراه مصدقة ومترجمة أصولاً (ID 10) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">6. شهادة الدكتوراه مصدقة ومترجمة أصولاً <span class="text-danger">*</span></label>
                            <input type="file" name="file_phd_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_phd_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_phd_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_phd_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 7. قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه (ID 11) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">7. قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه <span class="text-danger">*</span></label>
                            <input type="file" name="file_phd_council_decisions" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_phd_council_decisions']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_phd_council_decisions']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_phd_council_decisions']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. السيرة الذاتية (ID 14) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">8. السيرة الذاتية <span class="text-danger">*</span></label>
                            <input type="file" name="file_cv" id="input_file_cv" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_cv']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_cv']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_cv']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 9. إيصال الدفع 125 ألف (ID 17) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">9. إيصال الدفع 125 ألف <span class="text-danger">*</span></label>
                            <input type="file" name="file_fees_receipt" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_fees_receipt']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_fees_receipt']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_fees_receipt']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 10. إيصال المكتبة الوطنية (ID 22 - اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">10. إيصال المكتبة الوطنية <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_library_receipt" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_library_receipt']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_library_receipt']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 11. كشف علامات الدكتوراه (ID 5 - اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">11. كشف علامات الدكتوراه <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_phd_transcript" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_phd_transcript']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_phd_transcript']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 12. شهادة ICDL (ID 19 - اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">12. شهادة ICDL <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_icdl_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_icdl_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_icdl_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 13. شهادة اللغة الإنكليزية (ID 18 - اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">13. شهادة اللغة الإنكليزية <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_english_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_english_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_english_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 14. جواز السفر مع صفحات الأختام (ID 13 - شرطي حسب الإعفاء من الإقامة) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100 {{ $isResExempt ? '' : 'border-info' }}" id="card_passport_att">
                            <label class="form-label label-sm fw-bold">
                                14. جواز السفر مع صفحات الأختام
                                <span class="badge bg-secondary-subtle text-muted ms-1 fs-9 res-att-opt-badge" style="display: {{ $isResExempt ? 'inline' : 'none' }};">(اختياري - معفى من الإقامة)</span>
                                <span class="text-danger res-req-star" style="display: {{ $isResExempt ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="file" name="file_passport" id="input_file_passport" class="form-control academic-input" accept=".pdf" {{ (!$isResExempt && empty($existingFilesMap['file_passport'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_passport']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_passport']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 15. حركة الهجرة والجوازات (ID 24 - شرطي حسب الإعفاء من الإقامة) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100 {{ $isResExempt ? '' : 'border-info' }}" id="card_immigration_att">
                            <label class="form-label label-sm fw-bold">
                                15. حركة الهجرة والجوازات
                                <span class="badge bg-secondary-subtle text-muted ms-1 fs-9 res-att-opt-badge" style="display: {{ $isResExempt ? 'inline' : 'none' }};">(اختياري - معفى من الإقامة)</span>
                                <span class="text-danger res-req-star" style="display: {{ $isResExempt ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="file" name="file_immigration_movement" id="input_file_immigration" class="form-control academic-input" accept=".pdf" {{ (!$isResExempt && empty($existingFilesMap['file_immigration_movement'])) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_immigration_movement']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_immigration_movement']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 16. مرفقات أخرى (ID 23 - اختياري) -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">16. مرفقات أخرى <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_other_attachments" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_other_attachments']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_other_attachments']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 8: REVIEW & SUBMISSION (المراجعة الشاملة ومذكرة العرض)
            ========================================================================= --}}
            <div class="form-section" id="step-8" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-print fs-5" style="color: var(--heritage-gold);"></i> الخطوة 8: مراجعة البيانات المدخلة وتأكيد الإرسال
                </h5>

                <p class="label-md text-muted mb-4">يرجى مراجعة كافة البيانات المدخلة قبل النقر على زر إنهاء الإرسال. يمكنك التعديل والرجوع لأي خطوة سابقة.</p>

                <div class="card p-4 shadow-sm border-0" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
                    <div class="text-center pb-3 mb-4 border-bottom" style="border-bottom-color: var(--outline-variant) !important;">
                        <h4 class="fw-bold mb-1" style="color: var(--primary-container);">تقرير طلب تقويم وتعادل الشهادات العلمية للمرشح</h4>
                        <div class="text-muted fw-bold label-sm">مجلس التعليم العالي - لجنة التأهيل ومعادلة الدرجات العلمية (معاملة دكتوراه غير سورية)</div>
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

                        <!-- Group 2: University Request -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 2. بيانات كتاب ترشيح الجامعة الخاصة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo">---</span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate">---</span></div>
                                <div class="col-md-6"><strong>الكلية المرشح لها:</strong> <span id="preview-workFaculty">---</span></div>
                                <div class="col-md-6"><strong>القسم المطلوب للتدريس فيه:</strong> <span id="preview-workDept">---</span></div>
                            </div>
                        </div>

                        <!-- Group 3: High School -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-school me-1" style="color: var(--heritage-gold);"></i> 3. بيانات الشهادة الثانوية العامة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(2)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-hsCountry">---</span></div>
                                <div class="col-md-6"><strong>نوع الشهادة:</strong> <span id="preview-hsType">---</span></div>
                                <div class="col-md-6"><strong>سنة الحصول عليها:</strong> <span id="preview-hsDate">---</span></div>
                                <div class="col-md-6" id="preview-hsDecisionContainer"><strong>قرار المعادلة السوري:</strong> <span id="preview-hsDecisionNo">---</span></div>
                            </div>
                        </div>

                        <!-- Group 4: Bachelor's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. بيانات الإجازة الجامعية الأولى (البكالوريوس):</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(3)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-baCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-baUni">---</span></div>
                                <div class="col-md-4"><strong>الكلية:</strong> <span id="preview-baFaculty">---</span></div>
                                <div class="col-md-4"><strong>القسم:</strong> <span id="preview-baDept">---</span></div>
                                <div class="col-md-4"><strong>التخصص العام:</strong> <span id="preview-baGenSpec">---</span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-baRank">---</span></div>
                                <div class="col-md-6"><strong>تاريخ المنح:</strong> <span id="preview-baGrantDate">---</span></div>
                            </div>
                        </div>

                        <!-- Group 5: Master's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> 5. بيانات درجة الماجستير السابقة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-maCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-maUni">---</span></div>
                                <div class="col-md-4"><strong>الكلية:</strong> <span id="preview-maFaculty">---</span></div>
                                <div class="col-md-4"><strong>القسم:</strong> <span id="preview-maDept">---</span></div>
                                <div class="col-md-4"><strong>التخصص العام:</strong> <span id="preview-maGenSpec">---</span></div>
                                <div class="col-md-6"><strong>التخصص الدقيق:</strong> <span id="preview-maSpec">---</span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-maRank">---</span></div>
                                <div class="col-md-6"><strong>تاريخ الحصول على الدرجة:</strong> <span id="preview-maGrantDate">---</span></div>
                                <div class="col-md-12" id="preview-maDecisionContainer" style="display: none;"><strong>قرار معادلة شهادة الماجستير:</strong> <span id="preview-maDecisionNo">---</span></div>
                            </div>
                        </div>

                        <!-- Group 6: Foreign Doctorate -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-award me-1" style="color: var(--heritage-gold);"></i> 6. درجة الدكتوراه الخارجية المراد معادلتها:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(5)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>بلد الحصول عليها:</strong> <span id="preview-phdCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة الخارجية المانحة:</strong> <span id="preview-phdUni">---</span></div>
                                <div class="col-md-4"><strong>الكلية المانحة:</strong> <span id="preview-phdFaculty">---</span></div>
                                <div class="col-md-4"><strong>القسم:</strong> <span id="preview-phdDept">---</span></div>
                                <div class="col-md-4"><strong>الاختصاص العام:</strong> <span id="preview-phdGenSpec">---</span></div>
                                <div class="col-md-4"><strong>الاختصاص الدقيق:</strong> <span id="preview-phdSpec">---</span></div>
                                <div class="col-md-4"><strong>التقدير:</strong> <span id="preview-phdRank">---</span></div>
                                <div class="col-md-4"><strong>الأستاذ المشرف:</strong> <span id="preview-phdSupervisor">---</span></div>
                                <div class="col-md-6"><strong>نظام ولغة الدراسة:</strong> <span id="preview-phdSystemLang">---</span></div>
                                <div class="col-md-4"><strong>تاريخ التسجيل:</strong> <span id="preview-phdRegDate">---</span></div>
                                <div class="col-md-4"><strong>تاريخ المناقشة:</strong> <span id="preview-phdDefDate">---</span></div>
                                <div class="col-md-4"><strong>تاريخ المنح:</strong> <span id="preview-phdGrantDate">---</span></div>
                                <div class="col-12 mt-2"><strong>عنوان أطروحة الدكتوراه:</strong> <span id="preview-phdThesisTitle" class="fw-bold" style="color: var(--imperial-navy);">---</span></div>
                            </div>
                        </div>

                        <!-- Group 7: Residency Movements -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-passport me-1" style="color: var(--heritage-gold);"></i> 7. ملخص مدة الإقامة ببلد دراسة الدكتوراه:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(6)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل حركات الإقامة</button>
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

                        <!-- Group 8: Documents Summary -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-folder-open me-1" style="color: var(--heritage-gold);"></i> 8. الوثائق والمستندات المرفقة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(7)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل المرفقات</button>
                            </div>
                            <p class="text-muted fs-8 mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> تم إرفاق كافة الوثائق الرسمية المعتمدة، الهوية الشخصية/جواز السفر، الشهادات الدراسية المصدقة، قرارات التسجيل والمناقشة والمنح للدكتوراه، السيرة الذاتية، وإيصال الدفع (125,000 ل.س) بصيغة PDF.</p>
                        </div>
                    </div>
                </div>

                <!-- إقرار المصادقة القانونية بصحة البيانات -->
                <div class="form-check form-switch mt-4 p-3 border rounded d-flex align-items-center gap-3" style="background-color: var(--surface-container-low); border-color: var(--outline-variant) !important;">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required style="width: 2.2em; height: 1.2em; cursor: pointer;">
                    <label class="form-check-label fw-bold text-dark mb-0 label-md" for="chkConfirm" style="cursor: pointer;">
                        نصادق نحن في إدارة الجامعة على صحة كافة البيانات والوثائق المرفقة أعلاه، وأن جميع البيانات والمرفقات والأطروحة المودعة صحيحة ومطابقة للوثائق الرسمية المعتمدة وأختام جواز السفر، ونتحمل كامل المسؤولية القانونية حيال ذلك.
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
                    <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" onclick="triggerSaveDraft()" formnovalidate title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
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
    let currentStep = 1;
    const totalSteps = 8;
    let hasVisitedReview = {{ optional($draft)->id ? 'true' : 'false' }};
    const syriaCountryId = '{{ $syriaId ?? 1 }}';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function syncBoundDoctorateContext() {
        const cEl = document.getElementById('phd_country_id');
        const uEl = document.getElementById('input-phdUni');
        const outC = document.getElementById('step6-bound-country');
        const outU = document.getElementById('step6-bound-university');
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
        const step5Chk = document.getElementById('input-isResidenceExempt');
        const step6Chk = document.getElementById('step6-residence-exempt-switch');
        if (step5Chk) step5Chk.checked = exempt;
        if (step6Chk) step6Chk.checked = exempt;

        const lbl5 = document.getElementById('lbl-residenceExemptStep5');
        if (lbl5) {
            lbl5.textContent = exempt ? 'معفى من شرط الإقامة (تخصص نظري)' : 'يتطلب حضوراً وإقامة فعلية';
            lbl5.className = 'form-check-label fw-bold fs-8 mb-0 ' + (exempt ? 'text-success' : 'text-dark');
        }

        const exemptBanner = document.getElementById('residency-exempt-banner');
        const reqBanner = document.getElementById('residency-required-banner');
        if (exemptBanner) exemptBanner.style.display = exempt ? 'block' : 'none';
        if (reqBanner) reqBanner.style.display = exempt ? 'none' : 'block';

        document.querySelectorAll('.res-req-star').forEach(el => {
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

        [document.getElementById('card_passport_att'), document.getElementById('card_immigration_att')].forEach(c => {
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

        if (step === 6) {
            syncBoundDoctorateContext();
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

        if (!isValid) {
            const firstInvalid = section.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                if (firstInvalid.reportValidity) {
                    firstInvalid.reportValidity();
                }
            }
            return false;
        }

        // التحقق من حركات الإقامة في الخطوة السادسة عند عدم الإعفاء
        if (step === 6 && !isResidenceExemptChecked()) {
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
                alert('يرجى إدخال حركة دخول وخروج واحدة على الأقل إلى بلد دراسة الدكتوراه والضغط على (+)، أو تفعيل خيار (معفى من شرط الإقامة) إذا كان التخصص نظرياً.');
                document.getElementById('mov_entry_date')?.focus();
                return false;
            }
        }

        return true;
    }

    function toggleHsDecisionFields(countryId) {
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

    function syncDegreeUniversityFields(prefix) {
        const countryEl = document.getElementById(prefix + '_country_id');
        const countryCol = document.getElementById(prefix + '_country_col');
        const selectCol = document.getElementById(prefix + '_uni_select_col');
        const otherCol = document.getElementById(prefix + '_uni_other_col');
        const selectEl = document.getElementById(prefix + '_university_id');
        const otherEl = document.getElementById(prefix + '_university_other');

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

    function syncBaUniversityFields() {
        syncDegreeUniversityFields('ba');
    }

    function syncMaUniversityFields() {
        syncDegreeUniversityFields('ma');
    }

    function toggleMaEquivalenceSection(isChecked) {
        const checked = !!isChecked;
        const sec = document.getElementById('ma-equivalence-section');
        const noInput = document.getElementById('ma_decision_no');
        const dateInput = document.getElementById('ma_decision_date');
        const attBox = document.getElementById('ma_decision_att_box');
        const attInput = document.getElementById('file_ma_decision');

        if (sec) sec.style.display = checked ? 'block' : 'none';
        if (attBox) attBox.style.display = checked ? 'block' : 'none';

        if (checked) {
            if (noInput) noInput.setAttribute('required', 'required');
            if (dateInput) dateInput.setAttribute('required', 'required');
            if (attInput && !attInput.closest('.border.rounded')?.querySelector('.badge.bg-success-subtle')) {
                attInput.setAttribute('required', 'required');
            }
        } else {
            if (noInput) { noInput.removeAttribute('required'); noInput.classList.remove('is-invalid'); }
            if (dateInput) { dateInput.removeAttribute('required'); dateInput.classList.remove('is-invalid'); }
            if (attInput) { attInput.removeAttribute('required'); attInput.classList.remove('is-invalid'); }
        }
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
                previewStay.textContent = 'معفى من شرط الإقامة (تخصص نظري)';
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

    function resolvePreviewUniName(prefix, form) {
        const cEl = document.getElementById(prefix + '_country_id');
        const sEl = document.getElementById(prefix + '_university_id');
        const oEl = document.getElementById(prefix + '_university_other');
        if (cEl && cEl.value == syriaCountryId && sEl && sEl.value && sEl.value !== 'other') {
            return sEl.options[sEl.selectedIndex]?.text?.trim() || '---';
        }
        return oEl && oEl.value.trim() ? oEl.value.trim() : '---';
    }

    function populateReview() {
        const form = document.getElementById('wizard-form');
        if (!form) return;

        // 1. البيانات الشخصية
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

        // 2. كتاب ترشيح الجامعة
        document.getElementById('preview-reqNo').textContent = form.req_no.value || '---';
        document.getElementById('preview-reqDate').textContent = formatDateDisplay(form.req_date.value);
        document.getElementById('preview-workFaculty').textContent = form.work_faculty.value || '---';
        document.getElementById('preview-workDept').textContent = form.work_department.value || '---';

        // 3. الشهادة الثانوية
        const hsCountrySelect = form.hs_country_id;
        document.getElementById('preview-hsCountry').textContent = hsCountrySelect.options[hsCountrySelect.selectedIndex]?.text || '---';
        document.getElementById('preview-hsType').textContent = form.hs_type.value || '---';
        document.getElementById('preview-hsDate').textContent = form.hs_grant_date.value || '---';

        const isHsForeign = hsCountrySelect.value != syriaCountryId;
        const hsDecBox = document.getElementById('preview-hsDecisionContainer');
        if (hsDecBox) {
            if (isHsForeign && form.hs_decision_no && form.hs_decision_no.value.trim()) {
                hsDecBox.style.display = 'block';
                document.getElementById('preview-hsDecisionNo').textContent = form.hs_decision_no.value + (form.hs_decision_date && form.hs_decision_date.value ? ' (تاريخ: ' + formatDateDisplay(form.hs_decision_date.value) + ')' : '');
            } else {
                hsDecBox.style.display = 'none';
            }
        }

        // 4. الإجازة الجامعية الأولى (بدون قرار معادلة)
        const baCountrySelect = form.ba_country_id;
        document.getElementById('preview-baCountry').textContent = baCountrySelect ? (baCountrySelect.options[baCountrySelect.selectedIndex]?.text || '---') : '---';
        document.getElementById('preview-baUni').textContent = resolvePreviewUniName('ba', form);
        document.getElementById('preview-baFaculty').textContent = form.ba_faculty.value || '---';
        document.getElementById('preview-baDept').textContent = form.ba_department.value || '---';
        document.getElementById('preview-baGenSpec').textContent = form.ba_general_specialization.value || '---';
        document.getElementById('preview-baRank').textContent = form.ba_rank.value || '---';
        document.getElementById('preview-baGrantDate').textContent = formatDateDisplay(form.ba_grant_date ? form.ba_grant_date.value : '');

        // 5. الماجستير
        const maCountrySelect = form.ma_country_id;
        document.getElementById('preview-maCountry').textContent = maCountrySelect ? (maCountrySelect.options[maCountrySelect.selectedIndex]?.text || '---') : '---';
        document.getElementById('preview-maUni').textContent = resolvePreviewUniName('ma', form);
        document.getElementById('preview-maFaculty').textContent = form.ma_faculty.value || '---';
        document.getElementById('preview-maDept').textContent = form.ma_department.value || '---';
        document.getElementById('preview-maGenSpec').textContent = form.ma_general_specialization.value || '---';
        document.getElementById('preview-maSpec').textContent = form.ma_specialization ? (form.ma_specialization.value || '---') : '---';
        document.getElementById('preview-maRank').textContent = form.ma_rank.value || '---';
        document.getElementById('preview-maGrantDate').textContent = formatDateDisplay(form.ma_grant_date ? form.ma_grant_date.value : '');

        const hasMaEq = document.getElementById('check-hasMaEquivalence')?.checked;
        const maDecBox = document.getElementById('preview-maDecisionContainer');
        if (maDecBox) {
            if (hasMaEq && form.ma_decision_no && form.ma_decision_no.value.trim()) {
                maDecBox.style.display = 'block';
                document.getElementById('preview-maDecisionNo').textContent = form.ma_decision_no.value + (form.ma_decision_date && form.ma_decision_date.value ? ' (تاريخ: ' + formatDateDisplay(form.ma_decision_date.value) + ')' : '');
            } else {
                maDecBox.style.display = 'none';
            }
        }

        // 6. الدكتوراه الخارجية
        const phdCountrySelect = form.phd_country_id;
        document.getElementById('preview-phdCountry').textContent = phdCountrySelect ? (phdCountrySelect.options[phdCountrySelect.selectedIndex]?.text || '---') : '---';
        document.getElementById('preview-phdUni').textContent = form.phd_university_other.value || '---';
        document.getElementById('preview-phdFaculty').textContent = form.phd_faculty.value || '---';
        document.getElementById('preview-phdDept').textContent = form.phd_department.value || '---';
        document.getElementById('preview-phdGenSpec').textContent = form.phd_general_specialization.value || '---';
        document.getElementById('preview-phdSpec').textContent = form.phd_specialization ? (form.phd_specialization.value || '---') : '---';
        document.getElementById('preview-phdRank').textContent = form.phd_rank.value || '---';
        document.getElementById('preview-phdSupervisor').textContent = form.phd_supervisor.value || '---';
        document.getElementById('preview-phdSystemLang').textContent = (form.phd_study_system.value || 'أطروحة') + ' (لغة: ' + (form.phd_study_language.value || 'العربية') + ')';
        document.getElementById('preview-phdRegDate').textContent = formatDateDisplay(form.phd_registration_date ? form.phd_registration_date.value : '');
        document.getElementById('preview-phdDefDate').textContent = formatDateDisplay(form.phd_defense_date ? form.phd_defense_date.value : '');
        document.getElementById('preview-phdGrantDate').textContent = formatDateDisplay(form.phd_grant_date ? form.phd_grant_date.value : '');
        document.getElementById('preview-phdThesisTitle').textContent = form.phd_thesis_title.value || '---';

        recalculateTotalStay();
    }

    function triggerSaveDraft() {
        const form = document.getElementById('wizard-form');
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach(function(el) {
            el.removeAttribute('required');
            el.setCustomValidity('');
            el.classList.remove('is-invalid');
        });

        const actionInput = document.getElementById('form-action-input');
        if (actionInput) actionInput.value = 'save_draft';

        const isDraftInput = document.getElementById('form-is-draft-input');
        if (isDraftInput) isDraftInput.value = '1';

        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);
        syncBaUniversityFields();
        syncMaUniversityFields();
        toggleMaEquivalenceSection(document.getElementById('check-hasMaEquivalence')?.checked);
        syncBoundDoctorateContext();
        syncResidenceExempt(isResidenceExemptChecked());
        recalculateTotalStay();

        // 1. الربط الزمني المنطقي لتواريخ المراحل العلمية
        const hsYearInput = document.getElementById('input-hsYear');
        const baGrantDateInput = document.getElementById('input-baGrantDate');
        const maGrantDateInput = document.getElementById('input-maGrantDate');
        const maDecDateInput = document.getElementById('ma_decision_date');
        const regDateInput = document.getElementById('input-phdRegDate');
        const defDateInput = document.getElementById('input-phdDefDate');
        const grantDateInput = document.getElementById('input-phdGrantDate');

        if (hsYearInput && baGrantDateInput) {
            hsYearInput.addEventListener('change', function() {
                if (this.value) {
                    baGrantDateInput.setAttribute('min', this.value + '-06-30');
                }
            });
        }

        if (baGrantDateInput && maGrantDateInput) {
            baGrantDateInput.addEventListener('change', function() {
                if (this.value) {
                    maGrantDateInput.setAttribute('min', this.value);
                    if (maGrantDateInput.value && maGrantDateInput.value < this.value) {
                        maGrantDateInput.value = '';
                    }
                }
            });
        }

        if (maGrantDateInput && regDateInput) {
            maGrantDateInput.addEventListener('change', function() {
                if (this.value) {
                    regDateInput.setAttribute('min', this.value);
                    if (regDateInput.value && regDateInput.value < this.value) {
                        regDateInput.value = '';
                    }
                    if (maDecDateInput) {
                        maDecDateInput.setAttribute('min', this.value);
                    }
                }
            });
        }

        if (regDateInput && defDateInput) {
            regDateInput.addEventListener('change', function() {
                if (this.value) {
                    defDateInput.setAttribute('min', this.value);
                    if (defDateInput.value && defDateInput.value < this.value) {
                        defDateInput.value = '';
                    }
                }
            });
        }

        if (defDateInput && grantDateInput) {
            defDateInput.addEventListener('change', function() {
                if (this.value) {
                    grantDateInput.setAttribute('min', this.value);
                    if (grantDateInput.value && grantDateInput.value < this.value) {
                        grantDateInput.value = '';
                    }
                }
            });
        }

        const movEntry = document.getElementById('mov_entry_date');
        const movExit = document.getElementById('mov_exit_date');
        if (movEntry && movExit) {
            movEntry.addEventListener('change', function() {
                if (this.value) {
                    movExit.setAttribute('min', this.value);
                }
            });
        }

        // إزالة الإلزامية عن المرفقات المرفوعة مسبقاً
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            const parentDiv = input.closest('.border.rounded') || input.parentElement;
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

                const chkConfirm = document.getElementById('chkConfirm');
                if (chkConfirm && !chkConfirm.checked) {
                    e.preventDefault();
                    goToStep(8);
                    chkConfirm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    chkConfirm.focus();
                    chkConfirm.setCustomValidity('يرجى المصادقة على الإقرار بصحة البيانات للمتابعة.');
                    chkConfirm.reportValidity();
                    return false;
                } else if (chkConfirm) {
                    chkConfirm.setCustomValidity('');
                }

                form.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('required'));
                return true;
            });
        }
    });
</script>

<style>
.wizard-steps-container {
    padding: 10px 0;
}
.wizard-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    gap: 8px;
    flex-wrap: wrap;
}
.wizard-step {
    flex: 1;
    min-width: 95px;
    text-align: center;
    position: relative;
    cursor: pointer;
}
.wizard-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 6px;
    font-size: 0.95rem;
    border: 2px solid #cbd5e1;
    transition: all 0.3s ease;
}
.wizard-step.active .wizard-icon {
    background: var(--heritage-gold);
    color: #ffffff;
    border-color: var(--heritage-gold);
    box-shadow: 0 0 10px rgba(197, 160, 89, 0.4);
}
.wizard-step.completed .wizard-icon {
    background: #059669;
    color: #ffffff;
    border-color: #059669;
}
.wizard-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
}
.wizard-step.active .wizard-label {
    color: var(--primary-container);
}
.wizard-step.completed .wizard-label {
    color: #059669;
}
.academic-input {
    border-color: #cbd5e1;
    font-size: 0.9rem;
}
.academic-input:focus {
    border-color: var(--heritage-gold);
    box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.15);
}
</style>

@endsection
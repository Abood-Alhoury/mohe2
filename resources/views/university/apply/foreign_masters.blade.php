@extends('layouts.university')

@section('title', 'معاملة الماجستير الخارجي (غير السوري)')

@section('content')

@php
    $draftCandidate = optional(optional($draft)->candidate);
    $draftNatId = $draftCandidate->national_id ?? '';
    if ($draftNatId && str_starts_with($draftNatId, 'TMP-')) {
        $draftNatId = '';
    }
@endphp

<!-- PAGE TITLE & BREADCRUMB -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}">نوع المعاملة</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">معاملة الماجستير الخارجي</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid fa-earth-americas me-2" style="color: var(--heritage-gold);"></i> معاملة الماجستير الخارجي (غير السوري)
                </h3>
                <p class="body-md text-muted mb-0">لتعادل وتقييم درجة الماجستير الصادرة عن الجامعات غير السورية (مسار تطبيقي / مسار نظري مع إثبات الإقامة وحركات الدخول والخروج).</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" onclick="triggerSaveDraft()" title="حفظ البيانات المدخلة كمسودة للعودة إليها لاحقاً برقم الطلب">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ كمسودة ومتابعة لاحقاً</span>
                </button>
                @if($draft)
                    <span class="badge px-3 py-2 border fs-7 fw-bold" style="background-color: #FAF6EE; color: #8A651E; border-color: #D9C394 !important;">
                        <i class="fa-solid fa-file-pen me-1"></i> تعديل مسودة طلب: #{{ $draft->application_no }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- WIZARD MAIN CONTAINER -->
<div class="card border-0 shadow-sm" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
    <div class="card-body p-4 p-md-5">
        
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
                    <div class="wizard-label">4. الماجستير الخارجي والخبرة</div>
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
        <form action="{{ route('university.apply.foreign_masters.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form" novalidate>
            @csrf
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
                        <input type="email" name="email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', $draftCandidate->email ?? auth()->user()->email) }}" required>
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
                    $hsYear = $draftHsEd ? substr($draftHsEd->grant_date, 0, 4) : '';
                    $hsDecNo = $draftHsEd && $draftHsEd->notes ? preg_replace('/.*رقم قرار المعادلة الثانوية:\s*([^\|]+).*/u', '$1', $draftHsEd->notes) : '';
                    $hsDecDate = $draftHsEd && $draftHsEd->notes && str_contains($draftHsEd->notes, 'تاريخ القرار:') ? trim(preg_replace('/.*تاريخ القرار:\s*([^\|]+).*/u', '$1', $draftHsEd->notes)) : '';
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
                    $baCountryId = $draftBaEd ? $draftBaEd->country_id : ($syriaId ?? 1);
                    $baUniId = $draftBaEd ? $draftBaEd->university_id : '';
                    $baUniOther = $draftBaEd ? $draftBaEd->section_name : '';
                    $baFaculty = $draftBaEd ? $draftBaEd->general_specialization : '';
                    $baDept = $draftBaEd ? $draftBaEd->exact_specialization : '';
                    $baSpec = $draftBaEd ? $draftBaEd->section_name : '';
                    $baRegDate = $draftBaEd ? $draftBaEd->registration_date : '';
                    $baGrantDate = $draftBaEd ? $draftBaEd->grant_date : '';
                    $baRank = $draftBaEd ? $draftBaEd->rank : '';
                    $baDecNo = $draftBaEd && $draftBaEd->notes ? preg_replace('/.*رقم قرار معادلة الإجازة:\s*([^\|]+).*/u', '$1', $draftBaEd->notes) : '';
                    $baDecDate = $draftBaEd && $draftBaEd->notes && str_contains($draftBaEd->notes, 'تاريخ القرار:') ? trim(preg_replace('/.*تاريخ القرار:\s*([^\|]+).*/u', '$1', $draftBaEd->notes)) : '';
                    $isBaForeign = old('ba_country_id', $baCountryId) != ($syriaId ?? 1);
                    $baUniValue = old('ba_university_other', optional($draftBaEd)->university ? $draftBaEd->university->name : $baUniOther);
                @endphp

                <div class="row g-3" id="ba_first_row">
                    <div class="col-md-6" id="ba_country_col">
                        <label class="form-label label-sm fw-bold">بلد الإجازة الجامعية الأولى <span class="text-danger">*</span></label>
                        <select name="ba_country_id" id="ba_country_id" class="form-select academic-input" onchange="toggleBaCountry(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ba_country_id', $baCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="ba_uni_col">
                        <label class="form-label label-sm fw-bold">الجامعة المانحة للإجازة الجامعية <span class="text-danger">*</span></label>
                        <input type="text" name="ba_university_other" id="ba_university_other" class="form-control academic-input" placeholder="أدخل اسم الجامعة المانحة للإجازة (مثال: جامعة دمشق / جامعة القاهرة)" value="{{ $baUniValue }}" required>
                    </div>
                </div>

                <div class="row g-3 mt-1">

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الكلية المانحة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_faculty" class="form-control academic-input" placeholder="مثال: كلية العلوم" value="{{ old('ba_faculty', $baFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">القسم لدرجة الإجازة <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ba_department" class="form-control academic-input" placeholder="مثال: قسم الكيمياء" value="{{ old('ba_department', $baDept) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الاختصاص لدرجة الإجازة <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ba_specialization" class="form-control academic-input" placeholder="مثال: كيمياء تطبيقية" value="{{ old('ba_specialization', $baSpec) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ التسجيل والمباشرة بالإجازة</label>
                        <input type="date" name="ba_registration_date" class="form-control academic-input" value="{{ old('ba_registration_date', $baRegDate) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ منح الإجازة الجامعية <span class="text-danger">*</span></label>
                        <input type="date" name="ba_grant_date" class="form-control academic-input" value="{{ old('ba_grant_date', $baGrantDate) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">التقدير / المعدل العام بالإجازة <span class="text-danger">*</span></label>
                        <select name="ba_rank" class="form-select academic-input" required>
                            <option value="شرف" {{ old('ba_rank', $baRank) == 'شرف' ? 'selected' : '' }}>مرتبة شرف</option>
                            <option value="امتياز" {{ old('ba_rank', $baRank) == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ old('ba_rank', $baRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ba_rank', $baRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ old('ba_rank', $baRank) == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-12" id="ba_decision_box" style="display: {{ $isBaForeign ? 'block' : 'none' }};">
                        <div class="row g-3 p-3 bg-light rounded border border-warning">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">رقم قرار معادلة الإجازة الجامعية غير السورية <span class="text-danger" id="ba_dec_req_badge">*</span></label>
                                <input type="text" name="ba_decision_no" id="ba_decision_no" class="form-control academic-input" placeholder="رقم قرار المعادلة الصادر عن لجنة تعادل الشهادات" value="{{ old('ba_decision_no', $baDecNo) }}" {{ $isBaForeign ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">تاريخ صدور قرار معادلة الإجازة الجامعية <span class="text-danger" id="ba_date_req_badge">*</span></label>
                                <input type="date" name="ba_decision_date" id="ba_decision_date" class="form-control academic-input" value="{{ old('ba_decision_date', $baDecDate) }}" {{ $isBaForeign ? 'required' : '' }}>
                            </div>
                        </div>
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
                    $isEnvoy = $draftMaEd && $draftMaEd->envoy_decision ? 1 : 0;
                    $envoyDec = $draftMaEd ? $draftMaEd->envoy_decision : '';
                    $envoyDate = $draftMaEd ? $draftMaEd->envoy_date : '';

                    // Experience details (Determines applied vs theoretical)
                    $hasExp = (optional($draft)->request_type && str_contains(optional($draft)->request_type, 'نظري')) || ($draftMaEd && $draftMaEd->experience_from_year) ? 'yes' : 'no';
                    $expYears = $draftMaEd ? ($draftMaEd->experience_to_year ? ($draftMaEd->experience_to_year - $draftMaEd->experience_from_year) : 2) : 2;
                    $expFrom = $draftMaEd ? $draftMaEd->experience_from_year : '';
                    $expTo = $draftMaEd ? $draftMaEd->experience_to_year : '';
                    $expUnis = $draftMaEd && $draftMaEd->notes ? preg_replace('/.*جامعات الخبرة:\s*([^\|]+).*/u', '$1', $draftMaEd->notes) : '';
                @endphp

                <div class="row g-3" id="ma_first_row">
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
                        <label class="form-label label-sm fw-bold">الجامعة الخارجية المانحة للماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_university_other" id="ma_university_other" class="form-control academic-input" placeholder="اسم الجامعة الخارجية (مثال: جامعة بيروت العربية / جامعة القاهرة)" value="{{ old('ma_university_other', $maUniOther) }}" required>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الكلية المانحة لدرجة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_faculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية / كلية الآداب" value="{{ old('ma_faculty', $maFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">القسم / الاختصاص العام لشهادة الماجستير <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ma_department" class="form-control academic-input" placeholder="مثال: هندسة البرمجيات ونظم المعلومات" value="{{ old('ma_department', $maDept) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الاختصاص الدقيق لشهادة الماجستير <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                        <input type="text" name="ma_specialization" class="form-control academic-input" placeholder="مثال: النمذجة والمحاكاة الحاسوبية / أمن البيانات" value="{{ old('ma_specialization', $maSpec) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">نظام الدراسة بالماجستير</label>
                        <select name="ma_study_system" class="form-select academic-input">
                            <option value="سنوي" {{ old('ma_study_system', $maStudySystem) == 'سنوي' ? 'selected' : '' }}>سنوي</option>
                            <option value="فصلي" {{ old('ma_study_system', $maStudySystem) == 'فصلي' ? 'selected' : '' }}>فصلي</option>
                            <option value="ساعات معتمدة" {{ old('ma_study_system', $maStudySystem) == 'ساعات معتمدة' ? 'selected' : '' }}>ساعات معتمدة (Credit Hours)</option>
                            <option value="بحث وأطروحة فقط" {{ old('ma_study_system', $maStudySystem) == 'بحث وأطروحة فقط' ? 'selected' : '' }}>بحث وأطروحة فقط</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">لغة الدراسة</label>
                        <input type="text" name="ma_study_language" class="form-control academic-input" placeholder="مثال: العربية / الإنكليزية / الفرنسية" value="{{ old('ma_study_language', $maLang) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">المدة المقررة للدراسة (سنوات)</label>
                        <input type="number" name="ma_duration_years" class="form-control academic-input" min="1" max="10" value="{{ old('ma_duration_years', $maDuration) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label label-sm fw-bold">التقدير / المعدل العام بالماجستير <span class="text-danger">*</span></label>
                        <select name="ma_rank" class="form-select academic-input" required>
                            <option value="شرف" {{ old('ma_rank', $maRank) == 'شرف' ? 'selected' : '' }}>مرتبة شرف</option>
                            <option value="امتياز" {{ old('ma_rank', $maRank) == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ old('ma_rank', $maRank) == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ old('ma_rank', $maRank) == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ old('ma_rank', $maRank) == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ التسجيل والمباشرة بالماجستير</label>
                        <input type="date" name="ma_registration_date" class="form-control academic-input" value="{{ old('ma_registration_date', $maRegDate) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ مناقشة أطروحة الماجستير</label>
                        <input type="date" name="ma_defense_date" class="form-control academic-input" value="{{ old('ma_defense_date', $maDefDate) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">تاريخ منح / صدور شهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="date" name="ma_grant_date" class="form-control academic-input" value="{{ old('ma_grant_date', $maGrantDate) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">عنوان أطروحة / رسالة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_thesis_title" class="form-control academic-input" placeholder="عنوان الرسالة كما ورد في الشهادة الخارجية" value="{{ old('ma_thesis_title', $maThesis) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">اسم الأستاذ المشرف على رسالة الماجستير</label>
                        <input type="text" name="ma_supervisor" class="form-control academic-input" placeholder="مثال: أ.د. فلان الفلاني" value="{{ old('ma_supervisor', $maSupervisor) }}">
                    </div>

                    <div class="col-md-12">
                        <div class="form-check p-3 bg-light rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_envoy" id="is_envoy" value="1" {{ old('is_envoy', $isEnvoy) ? 'checked' : '' }} onchange="toggleEnvoyBox(this.checked)">
                            <label class="form-check-label fw-bold text-dark" for="is_envoy">
                                <i class="fa-solid fa-plane-departure me-1 "></i> هل المرشح موفد رسمياً للدراسة في الخارج بقرار من وزارة التعليم العالي أو إحدى الجهات العامة؟
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12" id="envoy_details_box" style="display: {{ old('is_envoy', $isEnvoy) ? 'block' : 'none' }};">
                        <div class="row g-3 p-3 bg-light rounded border border-warning">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">رقم قرار الإيفاد <span class="text-danger" id="envoy_dec_req_badge">*</span></label>
                                <input type="text" name="envoy_decision" id="envoy_decision" class="form-control academic-input" placeholder="505" value="{{ old('envoy_decision', $envoyDec) }}" {{ old('is_envoy', $isEnvoy) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold ">تاريخ قرار الإيفاد <span class="text-danger" id="envoy_date_req_badge">*</span></label>
                                <input type="date" name="envoy_date" id="envoy_date" class="form-control academic-input" value="{{ old('envoy_date', $envoyDate) }}" {{ old('is_envoy', $isEnvoy) ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================================
                     EXPERIENCE DETERMINATION SECTION (تطبيقي أم نظري)
                ========================================================================= --}}
                <div class="card mt-4 border-2" style="border-color: #D9C394 !important; background-color: #FCFAF6;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: #8A651E;">
                            <i class="fa-solid fa-chalkboard-user fs-5"></i> الخبرة التدريسية داخل الجامعات السورية (تحديد مسار الطلب: تطبيقي أم نظري)
                        </h6>
                        <!-- <p class="fs-7 text-muted mb-3">
                            <i class="fa-solid fa-circle-info me-1 text-warning"></i>
                            <strong>تعليمات وزارة التعليم العالي:</strong> الحاصل على ماجستير خارجي والقادم حديثاً دون خبرة تدريسية مثبتة داخل الجامعات السورية لمدة سنتين، يمنح <strong>قرار تدريس الجوانب التطبيقية (عضو هيئة فنية)</strong> بدون مقابلة ليكتسب خبرة سنتين داخل البلد. أما من لديه خبرة تدريسية مثبتة داخل سوريا لمدة سنتين فأكثر فيتقدم لمسار <strong>تدريس المقررات النظرية</strong> (يتطلب مقابلة وأهلية).
                        </p> -->

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark fs-7">هل يمتلك المرشح خبرة تدريسية مثبتة داخل الجامعات السورية لمدة سنتين فأكثر؟ <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_syrian_experience" id="exp_no" value="no" {{ old('has_syrian_experience', $hasExp) == 'no' ? 'checked' : '' }} onchange="toggleExperienceFields('no')">
                                    <label class="form-check-label fw-bold text-dark" for="exp_no">
                                        لا
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_syrian_experience" id="exp_yes" value="yes" {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'checked' : '' }} onchange="toggleExperienceFields('yes')">
                                    <label class="form-check-label fw-bold text-dark" for="exp_yes">نعم                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="experience_details_container" style="display: {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'block' : 'none' }};" class="mt-3 p-3 bg-white rounded border">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label label-sm fw-bold">عدد سنوات الخبرة المثبتة داخل سوريا</label>
                                    <input type="number" name="syrian_exp_years" class="form-control academic-input" min="2" max="40" value="{{ old('syrian_exp_years', $expYears) }}" placeholder="مثال: 2">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-sm fw-bold">من عام</label>
                                    <input type="number" name="syrian_exp_from" class="form-control academic-input" min="1980" max="{{ date('Y') }}" value="{{ old('syrian_exp_from', $expFrom) }}" placeholder="مثال: 2022">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-sm fw-bold">إلى عام</label>
                                    <input type="number" name="syrian_exp_to" class="form-control academic-input" min="1980" max="{{ date('Y') }}" value="{{ old('syrian_exp_to', $expTo) }}" placeholder="مثال: 2024">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-sm fw-bold">الجامعات والكليات التي درّس فيها</label>
                                    <input type="text" name="syrian_exp_universities" class="form-control academic-input" value="{{ old('syrian_exp_universities', $expUnis) }}" placeholder="مثال: جامعة دمشق - كلية الهندسة">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 5: RESIDENCY DURATION & TRAVEL ENTRY/EXIT MOVEMENTS (حركات الإقامة)
            ========================================================================= --}}
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-passport fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: بيانات الإقامة وحركات الدخول والخروج من بلد الدراسة
                </h5>

                <div class="alert alert-info py-2 px-3 fs-7 mb-4 border-0 rounded shadow-xs" style="background-color: #E8F0FE; color: #1967D2;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>إقامة المرشح:</strong> الرجاء إدخال حركات الدخول والخروج من بلد الدراسة من واقع أختام جواز السفر لحساب إجمالي مدة الإقامة الفعلية للمرشح خارج القطر أثناء دراسة الماجستير.
                </div>

                <!-- MOVEMENT INPUT ROW -->
                <div class="card p-4 mb-4 border rounded-3 shadow-xs" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--primary-container);">
                        <i class="fa-solid fa-passport" style="color: var(--heritage-gold);"></i> تسجيل حركة قدوم ومغادرة (دخول وخروج) جديدة
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label label-sm fw-bold">تاريخ الدخول لبلد الدراسة <span class="text-danger">*</span></label>
                            <input type="date" id="mov_entry_date" class="form-control academic-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label label-sm fw-bold">منفذ / مطار الدخول <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="text" id="mov_entry_airport" class="form-control academic-input" placeholder="مثال: مطار القاهرة / مطار دمشق الدولي">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label label-sm fw-bold">تاريخ الخروج من بلد الدراسة <span class="text-danger">*</span></label>
                            <input type="date" id="mov_exit_date" class="form-control academic-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label label-sm fw-bold">منفذ / مطار الخروج <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="text" id="mov_exit_airport" class="form-control academic-input" placeholder="مثال: مطار رفيق الحريري / معبر جديدة يابوس">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label label-sm fw-bold">رقم صفحة الجواز <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="text" id="mov_page_number" class="form-control academic-input" placeholder="مثال: ص 14 أو 14-15">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary px-4 fw-bold w-100 shadow-sm" onclick="addResidencyMovement()" style="height: 38px;">
                                <i class="fa-solid fa-plus-circle me-1"></i> إضافة حركة الإقامة إلى الجدول
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MOVEMENTS TABLE -->
                <div class="table-responsive mb-4 shadow-xs rounded border">
                    <table class="table table-bordered table-hover align-middle text-center mb-0" id="movements_table" style="font-size: 0.85rem;">
                        <thead style="background-color: #1e3a5f; color: #ffffff;" class="align-middle text-center">
                            <tr>
                                <th style="width: 5%; color: #ffffff;">#</th>
                                <th style="width: 14%; color: #ffffff;"><i class="fa-regular fa-calendar-check me-1"></i> تاريخ الدخول</th>
                                <th style="width: 16%; color: #ffffff;"><i class="fa-solid fa-plane-arrival me-1"></i> منفذ / مطار الدخول</th>
                                <th style="width: 14%; color: #ffffff;"><i class="fa-regular fa-calendar-xmark me-1"></i> تاريخ الخروج</th>
                                <th style="width: 16%; color: #ffffff;"><i class="fa-solid fa-plane-departure me-1"></i> منفذ / مطار الخروج</th>
                                <th style="width: 12%; color: #ffffff;"><i class="fa-solid fa-file-lines me-1"></i> رقم صفحة الجواز</th>
                                <th style="width: 15%; color: #ffffff;"><i class="fa-solid fa-clock-rotate-left me-1"></i> مدة الإقامة المحسوبة</th>
                                <th style="width: 8%; color: #ffffff;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="movements_tbody">
                            @php
                                $draftResidences = optional($draftMaEd)->residences ?? collect();
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
                                    } else {
                                        $calcY = 0; $calcM = 0; $calcD = 0;
                                    }
                                @endphp
                                <tr data-row-id="{{ $idx + 1 }}">
                                    <td>{{ $idx + 1 }}</td>
                                    <td><strong class="text-dark">{{ $res->entry_date }}</strong></td>
                                    <td>{{ $res->entry_airport ?: '---' }}</td>
                                    <td><strong class="text-dark">{{ $res->exit_date }}</strong></td>
                                    <td>{{ $res->exit_airport ?: '---' }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border">{{ $res->page_number ?: '---' }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1">{{ $calcY }} سنة و {{ $calcM }} شهر و {{ $calcD }} يوم</span></td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteResidencyMovement(this)" title="حذف الحركة">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <input type="hidden" name="residences[{{ $idx }}][entry_date]" value="{{ $res->entry_date }}">
                                        <input type="hidden" name="residences[{{ $idx }}][entry_airport]" value="{{ $res->entry_airport }}">
                                        <input type="hidden" name="residences[{{ $idx }}][exit_date]" value="{{ $res->exit_date }}">
                                        <input type="hidden" name="residences[{{ $idx }}][exit_airport]" value="{{ $res->exit_airport }}">
                                        <input type="hidden" name="residences[{{ $idx }}][page_number]" value="{{ $res->page_number }}">
                                    </td>
                                </tr>
                            @empty
                                <tr id="no_movements_row">
                                    <td colspan="8" class="text-muted py-4">لم يتم تسجيل أي حركة دخول أو خروج بعد. يرجى ملء الحقول أعلاه والضغط على (إضافة حركة الإقامة إلى الجدول).</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL STAY DURATION BANNER (مجموع مدة الإقامة) -->
                <div class="card p-3 text-center rounded-3 shadow-sm border" style="background-color: #f0fdf4; border-color: #86efac !important;">
                    <h6 class="fw-bold mb-2" style="color: #166534 !important;"><i class="fa-solid fa-clock-rotate-left me-1"></i> إجمالي مدة الإقامة المحسوبة خارج القطر :</h6>
                    <div class="d-flex justify-content-center align-items-center gap-3 fs-5 fw-bold text-dark">
                        <div class="d-flex align-items-center gap-1"><span id="total_stay_years" class="badge bg-success fs-6 px-3 py-2">0</span> سنة</div>
                        <div class="d-flex align-items-center gap-1"><span id="total_stay_months" class="badge bg-success fs-6 px-3 py-2">0</span> شهر</div>
                        <div class="d-flex align-items-center gap-1"><span id="total_stay_days" class="badge bg-success fs-6 px-3 py-2">0</span> يوم</div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 6: ATTACHMENTS (16 EXACT OFFICIAL ATTACHMENTS)
            ========================================================================= --}}
            <div class="form-section" id="step-6" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: المرفقات والمستندات الثبوتية المطلوبة (PDF فقط - حد أقصى 2 ميغابايت)
                </h5>

                @php
                    $existingFilesMap = [];
                    if ($draft) {
                        foreach ($draft->educations as $ed) {
                            foreach ($ed->attachments as $att) {
                                $note = $att->notes ?? '';
                                if ($att->attachment_type_id == 1 || str_contains($note, 'ثانوية')) $existingFilesMap['file_secondary_cert'] = $att;
                                if ($att->attachment_type_id == 2 || str_contains($note, 'إجازة')) $existingFilesMap['file_bachelor_cert'] = $att;
                                if ($att->attachment_type_id == 3 || str_contains($note, 'قبل المؤهل')) $existingFilesMap['file_prev_qual_cert'] = $att;
                                if ($att->attachment_type_id == 4 || str_contains($note, 'شهادة الماجستير')) $existingFilesMap['file_master_cert'] = $att;
                                if ($att->attachment_type_id == 5 || str_contains($note, 'كشف علامات')) $existingFilesMap['file_master_transcript'] = $att;
                                if ($att->attachment_type_id == 6 || str_contains($note, 'ملخص عن الأطروحة')) $existingFilesMap['file_thesis_abstract'] = $att;
                                if ($att->attachment_type_id == 7 || str_contains($note, 'المكتبة الوطنية') || str_contains($note, 'مكتبة الأسد')) $existingFilesMap['file_library_receipt'] = $att;
                                if ($att->attachment_type_id == 8 || str_contains($note, 'التسجيل والمناقشة')) $existingFilesMap['file_reg_defense_doc'] = $att;
                                if ($att->attachment_type_id == 9 || str_contains($note, 'خبرة')) $existingFilesMap['file_experience_cert'] = $att;
                                if ($att->attachment_type_id == 10 || str_contains($note, 'عقود')) $existingFilesMap['file_private_uni_contracts'] = $att;
                                if ($att->attachment_type_id == 11 || str_contains($note, 'رواتب')) $existingFilesMap['file_salary_receipts'] = $att;
                                if ($att->attachment_type_id == 12 || str_contains($note, 'ICDL')) $existingFilesMap['file_icdl_cert'] = $att;
                                if ($att->attachment_type_id == 13 || str_contains($note, 'إنكليزية') || str_contains($note, 'اللغة')) $existingFilesMap['file_english_cert'] = $att;
                                if ($att->attachment_type_id == 14 || str_contains($note, 'رسوم') || str_contains($note, 'إيصال تسديد')) $existingFilesMap['file_fees_receipt'] = $att;
                                if ($att->attachment_type_id == 15 || str_contains($note, 'جواز السفر') || str_contains($note, 'جواز')) $existingFilesMap['file_passport'] = $att;
                                if ($att->attachment_type_id == 16 || str_contains($note, 'معادلة الشهادة الثانوية') || str_contains($note, 'معادلة الثانوية')) $existingFilesMap['file_hs_decision'] = $att;
                                if ($att->attachment_type_id == 17 || str_contains($note, 'معادلة الإجازة') || str_contains($note, 'معادلة البكالوريوس')) $existingFilesMap['file_ba_decision'] = $att;
                                if ($att->attachment_type_id == 18 || str_contains($note, 'الإيفاد')) $existingFilesMap['file_envoy_decision'] = $att;
                                if ($att->attachment_type_id == 19 || str_contains($note, 'وثائق ومرفقات ثبوتية')) $existingFilesMap['file_other_attachments'] = $att;
                            }
                        }
                    }
                @endphp

                <div class="row g-3">
                    <!-- 1. شهادة الثانوية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">1. نسخة مصدقة عن شهادة الثانوية العامة <span class="text-danger">*</span></label>
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
                            <label class="form-label label-sm fw-bold ">قرار معادلة الشهادة الثانوية غير السورية <span class="text-danger" id="hs_att_req_badge">*</span></label>
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

                    <!-- 2. شهادة الإجازة الجامعية الأولى -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">2. نسخة مصدقة عن شهادة الإجازة الجامعية الأولى (البكالوريوس) <span class="text-danger">*</span></label>
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

                    <!-- قرار معادلة الإجازة الجامعية الأولى غير السورية -->
                    <div class="col-md-6" id="ba_decision_att_box" style="display: {{ $isBaForeign ? 'block' : 'none' }};">
                        <div class="border rounded p-3 bg-light h-100 border-warning">
                            <label class="form-label label-sm fw-bold ">قرار معادلة الإجازة الجامعية الأولى غير السورية <span class="text-danger" id="ba_att_req_badge">*</span></label>
                            <input type="file" name="file_ba_decision" id="file_ba_decision" class="form-control academic-input" accept=".pdf" {{ $isBaForeign && empty($existingFilesMap['file_ba_decision']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_ba_decision']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_ba_decision']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. الشهادة قبل المؤهل العلمي الأخير -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">3. الشهادة قبل المؤهل العلمي الأخير <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_prev_qual_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_prev_qual_cert']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_prev_qual_cert']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. شهادة الماجستير الخارجي -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">4. نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي <span class="text-danger">*</span></label>
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

                    <!-- قرار الإيفاد الرسمي للدراسة في الخارج -->
                    <div class="col-md-6" id="envoy_att_box" style="display: {{ old('is_envoy', $isEnvoy) ? 'block' : 'none' }};">
                        <div class="border rounded p-3 bg-light h-100 border-warning">
                            <label class="form-label label-sm fw-bold ">قرار الإيفاد الرسمي للدراسة في الخارج <span class="text-danger" id="envoy_att_req_badge">*</span></label>
                            <input type="file" name="file_envoy_decision" id="file_envoy_decision" class="form-control academic-input" accept=".pdf" {{ old('is_envoy', $isEnvoy) && empty($existingFilesMap['file_envoy_decision']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_envoy_decision']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_envoy_decision']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. كشف علامات الماجستير -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">5. كشف علامات الماجستير إن وجد <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
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

                    <!-- 6. ملخص عن الأطروحة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">6. ملخص عن أطروحة الماجستير باللغة العربية <span class="text-danger">*</span></label>
                            <input type="file" name="file_thesis_abstract" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_thesis_abstract']) ? 'required' : '' }}>
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

                    <!-- 7. إيصال المكتبة الوطنية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">7. إيصال إيداع الأطروحة لدى المكتبة الوطنية <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_library_receipt" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_library_receipt']))
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                    <a href="{{ asset('storage/' . $existingFilesMap['file_library_receipt']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                        <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. وثيقة بتاريخ التسجيل والمناقشة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">8. وثيقة تثبت تواريخ التسجيل والمباشرة والمناقشة لدرجة الماجستير <span class="text-danger">*</span></label>
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

                    <!-- 9. شهادة خبرة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">9. شهادة الخبرة التدريسية المثبتة داخل الجامعات السورية <span class="text-danger" id="exp_cert_required_badge" style="display: {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'inline' : 'none' }};">*</span> <span class="text-muted fs-8 fw-normal" id="exp_cert_optional_badge" style="display: {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'none' : 'inline' }};">(خاص بالمسار النظري)</span></label>
                            <input type="file" name="file_experience_cert" id="file_experience_cert" class="form-control academic-input" accept=".pdf" {{ old('has_syrian_experience', $hasExp) == 'yes' && empty($existingFilesMap['file_experience_cert']) ? 'required' : '' }}>
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

                    <!-- 10. العقود مع الجامعة الخاصة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">10. العقود مع الجامعة الخاصة <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_private_uni_contracts" class="form-control academic-input" accept=".pdf">
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

                    <!-- 11. إيصالات الرواتب التي قبضت من الجامعة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">11. إيصالات الرواتب التي قبضت من الجامعة <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
                            <input type="file" name="file_salary_receipts" class="form-control academic-input" accept=".pdf">
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

                    <!-- 12. شهادة ICDL -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">12. شهادة مهارات الحاسوب (ICDL) <span class="text-danger">*</span></label>
                            <input type="file" name="file_icdl_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_icdl_cert']) ? 'required' : '' }}>
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

                    <!-- 13. شهادة اختبار اللغة الإنكليزية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">13. وثيقة اجتياز اختبار اللغة الأجنبية (الإنكليزية) <span class="text-danger">*</span></label>
                            <input type="file" name="file_english_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_english_cert']) ? 'required' : '' }}>
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

                    <!-- 14. رسوم التعادل -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">14. إيصال تسديد رسم تعادل الماجستير الخارجي (100,000 ل.س) <span class="text-danger">*</span></label>
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

                    <!-- 15. جواز السفر -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">15. صورة عن جواز السفر وصفحات الأختام والإقامة ببلد الدراسة <span class="text-danger">*</span></label>
                            <input type="file" name="file_passport" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_passport']) ? 'required' : '' }}>
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

                    <!-- 16. مرفقات أخرى -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">16. وثائق ومرفقات ثبوتية داعمة أخرى <span class="text-muted fs-8 fw-normal">(اختياري)</span></label>
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
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-baCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-baUni">---</span></div>
                                <div class="col-md-6"><strong>الكلية:</strong> <span id="preview-baFaculty">---</span></div>
                                <div class="col-md-6"><strong>القسم / التخصص:</strong> <span id="preview-baDept">---</span></div>
                                <div class="col-md-6"><strong>التقدير / المعدل:</strong> <span id="preview-baRank">---</span></div>
                                <div class="col-md-6"><strong>تاريخ التسجيل:</strong> <span id="preview-baRegDate">---</span></div>
                                <div class="col-md-6"><strong>تاريخ التخرج:</strong> <span id="preview-baGrantDate">---</span></div>
                                <div class="col-md-6" id="preview-baDecisionContainer"><strong>رقم قرار المعادلة السوري:</strong> <span id="preview-baDecisionNo">---</span></div>
                            </div>
                        </div>

                        <!-- Group 4: Foreign Master's & Track -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-award me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير الخارجي والمسار:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>بلد الدراسة (الدولة المانحة):</strong> <span id="preview-maCountry">---</span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-maUni">---</span></div>
                                <div class="col-md-6"><strong>الكلية المانحة:</strong> <span id="preview-maFaculty">---</span></div>
                                <div class="col-md-6"><strong>القسم / الاختصاص العام:</strong> <span id="preview-maDept">---</span></div>
                                <div class="col-md-6"><strong>الاختصاص الدقيق:</strong> <span id="preview-maSpec">---</span></div>
                                <div class="col-md-6"><strong>التقدير / المعدل:</strong> <span id="preview-maRank">---</span></div>
                                <div class="col-md-6"><strong>تاريخ المنح / التخرج:</strong> <span id="preview-maGrantDate">---</span></div>
                                <div class="col-md-6"><strong>مسار الطلب:</strong> <span id="preview-maTrack" class="badge bg-warning-subtle text-warning-emphasis border">---</span></div>
                                <div class="col-12" id="preview-expContainer" style="display: none;">
                                    <div class="card p-2 border-0 mt-2" style="background-color: var(--surface-container-low);">
                                        <strong>الخبرة التدريسية داخل القطر (> سنتين):</strong>
                                        <div>الجهة: <span id="preview-expPlace">---</span> | من عام: <span id="preview-expFrom">---</span> إلى عام: <span id="preview-expTo">---</span></div>
                                    </div>
                                </div>
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

                    <button type="button" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" onclick="triggerSaveDraft()" title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة ومتابعة لاحقاً
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary px-3 py-2 fw-bold" id="btn-quick-review" onclick="quickReturnToReview()" style="display: none;" title="العودة مباشرة لخطوة المراجعة والتدقيق النهائي">
                        <i class="fa-solid fa-clipboard-check me-1"></i> العودة للمراجعة والإرسال
                    </button>

                    <button type="button" class="btn btn-primary px-4 py-2" id="btn-next" onclick="changeStep(1)">
                        التالي <i class="fa-solid fa-arrow-left ms-1"></i>
                    </button>

                    <button type="submit" formnovalidate name="action" value="submit_final" class="btn btn-gold-cta px-5 py-2" id="btn-submit" style="display: none;">
                        إنهاء وإرسال الطلب للوزارة <i class="fa-solid fa-paper-plane ms-1"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 7;
    let hasVisitedReview = {{ optional($draft)->id ? 'true' : 'false' }};

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
        const section = document.getElementById('step-' + step);
        if (!section) return true;

        const requiredInputs = section.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            // Ignore elements inside hidden containers (e.g. display: none)
            if (input.offsetParent === null) {
                return;
            }

            if (input.type === 'file') {
                const parentDiv = input.closest('.border.rounded') || input.parentElement;
                if (parentDiv && parentDiv.querySelector('.badge.bg-success-subtle')) {
                    return; // Already uploaded
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

    function toggleBaCountry(countryId) {
        const box = document.getElementById('ba_decision_box');
        const noInput = document.getElementById('ba_decision_no');
        const dateInput = document.getElementById('ba_decision_date');
        const attBox = document.getElementById('ba_decision_att_box');
        const attInput = document.getElementById('file_ba_decision');

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

    function toggleBaUniOther(val) {}
    function updateBaRowCols() {}

    function toggleMaCountry(val) {}

    function toggleEnvoyBox(checked) {
        const box = document.getElementById('envoy_details_box');
        const noInput = document.getElementById('envoy_decision');
        const dateInput = document.getElementById('envoy_date');
        const attBox = document.getElementById('envoy_att_box');
        const attInput = document.getElementById('file_envoy_decision');

        if (checked) {
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

    function toggleExperienceFields(val) {
        const container = document.getElementById('experience_details_container');
        const badgeReq = document.getElementById('exp_cert_required_badge');
        const badgeOpt = document.getElementById('exp_cert_optional_badge');
        const fileInput = document.getElementById('file_experience_cert');

        if (val === 'yes') {
            if (container) container.style.display = 'block';
            if (badgeReq) badgeReq.style.display = 'inline';
            if (badgeOpt) badgeOpt.style.display = 'none';
            if (fileInput && !fileInput.closest('.border.rounded')?.querySelector('.badge.bg-success-subtle')) {
                fileInput.setAttribute('required', 'required');
            }
        } else {
            if (container) container.style.display = 'none';
            if (badgeReq) badgeReq.style.display = 'none';
            if (badgeOpt) badgeOpt.style.display = 'inline';
            if (fileInput) {
                fileInput.removeAttribute('required');
                fileInput.classList.remove('is-invalid');
            }
        }
    }

    // ==========================================
    // RESIDENCY MOVEMENTS LOGIC (حركات الإقامة)
    // ==========================================
    let movementCounter = {{ $draftResidences->count() ?? 0 }};

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

    function addResidencyMovement() {
        const entryDate = document.getElementById('mov_entry_date')?.value;
        const entryAirport = document.getElementById('mov_entry_airport')?.value.trim() || '';
        const exitDate = document.getElementById('mov_exit_date')?.value;
        const exitAirport = document.getElementById('mov_exit_airport')?.value.trim() || '';
        const pageNumber = document.getElementById('mov_page_number')?.value.trim() || '';

        if (!entryDate || !exitDate) {
            alert('يرجى إدخال تاريخ الدخول وتاريخ الخروج لحركة الإقامة.');
            return;
        }

        const inD = new Date(entryDate);
        const outD = new Date(exitDate);

        if (outD < inD) {
            alert('تاريخ الخروج يجب أن يكون مساوياً أو بعد تاريخ الدخول.');
            return;
        }

        const stay = getDetailedStay(entryDate, exitDate);

        movementCounter++;
        const tbody = document.getElementById('movements_tbody');
        const noRow = document.getElementById('no_movements_row');
        if (noRow) noRow.remove();

        const tr = document.createElement('tr');
        tr.setAttribute('data-days', stay.totalDays);
        tr.innerHTML = `
            <td>${tbody.children.length + 1}</td>
            <td><strong class="text-dark">${entryDate}</strong></td>
            <td>${entryAirport || '---'}</td>
            <td><strong class="text-dark">${exitDate}</strong></td>
            <td>${exitAirport || '---'}</td>
            <td><span class="badge bg-secondary-subtle text-secondary border">${pageNumber || '---'}</span></td>
            <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1">${stay.years} سنة و ${stay.months} شهر و ${stay.days} يوم</span></td>
            <td>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteResidencyMovement(this)" title="حذف الحركة">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <input type="hidden" name="residences[${movementCounter}][entry_date]" value="${entryDate}">
                <input type="hidden" name="residences[${movementCounter}][entry_airport]" value="${entryAirport}">
                <input type="hidden" name="residences[${movementCounter}][exit_date]" value="${exitDate}">
                <input type="hidden" name="residences[${movementCounter}][exit_airport]" value="${exitAirport}">
                <input type="hidden" name="residences[${movementCounter}][page_number]" value="${pageNumber}">
            </td>
        `;
        tbody.appendChild(tr);

        // Clear inputs
        document.getElementById('mov_entry_date').value = '';
        document.getElementById('mov_entry_airport').value = '';
        document.getElementById('mov_exit_date').value = '';
        document.getElementById('mov_exit_airport').value = '';
        document.getElementById('mov_page_number').value = '';

        recalculateTotalStay();
    }

    function deleteResidencyMovement(btn) {
        const row = btn.closest('tr');
        row.remove();
        const tbody = document.getElementById('movements_tbody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr id="no_movements_row"><td colspan="8" class="text-muted py-4">لم يتم تسجيل أي حركة دخول أو خروج بعد. يرجى ملء الحقول أعلاه والضغط على (إضافة حركة الإقامة إلى الجدول).</td></tr>';
        } else {
            Array.from(tbody.children).forEach((r, idx) => {
                if (r.children[0]) r.children[0].textContent = idx + 1;
            });
        }
        recalculateTotalStay();
    }

    function recalculateTotalStay() {
        const tbody = document.getElementById('movements_tbody');
        let totalDays = 0;

        tbody.querySelectorAll('tr').forEach(tr => {
            const inDateInput = tr.querySelector('input[name*="[entry_date]"]');
            const outDateInput = tr.querySelector('input[name*="[exit_date]"]');
            if (inDateInput && outDateInput && inDateInput.value && outDateInput.value) {
                const stay = getDetailedStay(inDateInput.value, outDateInput.value);
                totalDays += stay.totalDays;
            }
        });

        const years = Math.floor(totalDays / 365);
        const months = Math.floor((totalDays % 365) / 30);
        const days = (totalDays % 365) % 30;

        const yEl = document.getElementById('total_stay_years');
        const mEl = document.getElementById('total_stay_months');
        const dEl = document.getElementById('total_stay_days');
        if (yEl) yEl.textContent = years;
        if (mEl) mEl.textContent = months;
        if (dEl) dEl.textContent = days;

        const previewStay = document.getElementById('preview-totalStay');
        if (previewStay) {
            previewStay.textContent = `${years} سنة و ${months} شهر و ${days} يوم`;
        }
        const previewResCount = document.getElementById('preview-resCount');
        if (previewResCount) {
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
        let baCountryText = baCountrySelect.options[baCountrySelect.selectedIndex]?.text || '---';
        document.getElementById('preview-baCountry').textContent = baCountryText;
        document.getElementById('preview-baUni').textContent = form.ba_university_other.value || '---';
        document.getElementById('preview-baFaculty').textContent = form.ba_faculty.value || '---';
        document.getElementById('preview-baDept').textContent = (form.ba_department.value || '---') + (form.ba_specialization.value ? ' / ' + form.ba_specialization.value : '');
        document.getElementById('preview-baRank').textContent = form.ba_rank.value || '---';
        document.getElementById('preview-baRegDate').textContent = formatDateDisplay(form.ba_registration_date.value);
        document.getElementById('preview-baGrantDate').textContent = formatDateDisplay(form.ba_grant_date.value);

        const isBaForeign = baCountrySelect.value != {{ $syriaId ?? 1 }};
        const baDecBox = document.getElementById('preview-baDecisionContainer');
        if (baDecBox) {
            if (isBaForeign && form.ba_decision_no && form.ba_decision_no.value.trim()) {
                baDecBox.style.display = 'block';
                document.getElementById('preview-baDecisionNo').textContent = form.ba_decision_no.value + (form.ba_decision_date && form.ba_decision_date.value ? ' (تاريخ: ' + formatDateDisplay(form.ba_decision_date.value) + ')' : '');
            } else {
                baDecBox.style.display = 'none';
            }
        }

        // Group 4: Master & Track
        const maCountrySelect = form.ma_country_id;
        let maCountryText = maCountrySelect.options[maCountrySelect.selectedIndex]?.text || '---';
        document.getElementById('preview-maCountry').textContent = maCountryText;
        document.getElementById('preview-maUni').textContent = form.ma_university_other.value || '---';
        document.getElementById('preview-maFaculty').textContent = form.ma_faculty.value || '---';
        document.getElementById('preview-maDept').textContent = form.ma_department.value || '---';
        document.getElementById('preview-maSpec').textContent = form.ma_specialization.value || '---';
        document.getElementById('preview-maGrantDate').textContent = formatDateDisplay(form.ma_grant_date.value);
        document.getElementById('preview-maRank').textContent = form.ma_rank.value || '---';

        const hasExp = form.has_syrian_experience.value === 'yes';
        const trackBadge = document.getElementById('preview-maTrack');
        const expBox = document.getElementById('preview-expContainer');
        if (hasExp) {
            trackBadge.className = 'badge bg-primary-subtle border border-primary';
            trackBadge.textContent = 'ماجستير خارجي - نظري (خبرة سنتين داخل سوريا - يتطلب مقابلة وأهلية)';
            if (expBox) {
                expBox.style.display = 'block';
                document.getElementById('preview-expPlace').textContent = form.experience_universities ? (form.experience_universities.value || '---') : '---';
                document.getElementById('preview-expFrom').textContent = form.experience_from_year ? (form.experience_from_year.value || '---') : '---';
                document.getElementById('preview-expTo').textContent = form.experience_to_year ? (form.experience_to_year.value || '---') : '---';
            }
        } else {
            trackBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning';
            trackBadge.textContent = 'ماجستير خارجي - تطبيقي (عضو هيئة فنية - تدريس تطبيقي بدون مقابلة)';
            if (expBox) {
                expBox.style.display = 'none';
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
        recalculateTotalStay();

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

                if ((actionInput && actionInput.value === 'save_draft') || 
                    (isDraftInput && isDraftInput.value === '1') || 
                    (submitter && submitter.value === 'save_draft')) {
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

<style>
    .wizard-steps-container {
        position: relative;
        padding: 0 10px;
    }

    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        right: 0;
        left: 0;
        height: 4px;
        background-color: var(--surface-container-high);
        z-index: 0;
    }

    .wizard-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
    }

    .wizard-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 2.5px solid var(--outline-variant);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: var(--on-surface-variant);
        transition: all 0.25s ease;
    }

    .wizard-label {
        font-size: 0.76rem;
        font-weight: 600;
        margin-top: 6px;
        color: var(--on-surface-variant);
        text-align: center;
    }

    .wizard-step.active .wizard-icon {
        border-color: var(--heritage-gold);
        background-color: var(--primary-container);
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(180, 83, 9, 0.2);
    }

    .wizard-step.active .wizard-label {
        color: var(--primary-container);
        font-weight: 700;
    }

    .wizard-step.completed .wizard-icon {
        border-color: var(--heritage-gold);
        background-color: var(--heritage-gold);
        color: #ffffff;
    }

    .academic-input {
        border-radius: 4px;
        border-color: var(--outline-variant);
        padding: 0.55rem 0.75rem;
    }

    .academic-input:focus {
        border-color: var(--heritage-gold);
        box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.15);
    }
</style>

@endsection

@extends('layouts.university')

@section('title', 'تقديم طلب تعادل دكتوراه سورية')

@section('content')

@php
    $candidate = $draft ? $draft->candidate : null;
    $hsEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'ثانوية')) || $e->education_level_id == 4 || $e->education_level_id == 6 || ($e->section_name && in_array($e->section_name, ['علمي', 'أدبي', 'تجاري', 'صناعي'])); }) : null;
    $baEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'إجازة')) || $e->education_level_id == 1; }) : null;
    $dipEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'دبلوم')) || ($e->notes && str_contains($e->notes, 'دبلوم')); }) : null;
    $maEd = $draft ? $draft->educations->first(function($e) { return ((optional($e->level)->name && str_contains(optional($e->level)->name, 'ماجستير')) || $e->education_level_id == 2) && (!$e->notes || !str_contains($e->notes, 'دبلوم')); }) : null;
    $phdEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'دكتوراه')) || $e->education_level_id == 3; }) : null;

    $existingFiles = [];
    if ($draft) {
        foreach ($draft->educations as $ed) {
            foreach ($ed->attachments as $att) {
                if ($att->notes) {
                    if (str_contains($att->notes, 'ثانوية') && !str_contains($att->notes, 'قرار')) {
                        $existingFiles['file_hs_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الثانوية')) {
                        $existingFiles['hs_decision_file'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'الإجازة') && !str_contains($att->notes, 'قرار')) {
                        $existingFiles['file_ba_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرار معادلة الشهادة الجامعية')) {
                        $existingFiles['ba_decision_file'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'دبلوم')) {
                        $existingFiles['file_diploma_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'شهادة ماجستير')) {
                        $existingFiles['file_ma_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرار معادلة شهادة الماجستير')) {
                        $existingFiles['ma_decision_file'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرارات مجلس الجامعة للماجستير')) {
                        $existingFiles['file_ma_council_decisions'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'شهادة الدكتوراه')) {
                        $existingFiles['file_phd_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ملخص')) {
                        $existingFiles['file_thesis_summary'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'قرارات مجلس الجامعة للدكتوراه')) {
                        $existingFiles['file_phd_council_decisions'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ICDL')) {
                        $existingFiles['file_icdl'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'اللغة')) {
                        $existingFiles['file_english_test'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'كتاب الجامعة')) {
                        $existingFiles['file_uni_request'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'السيرة')) {
                        $existingFiles['file_cv'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'إيصال') || str_contains($att->notes, 'رسوم التعادل')) {
                        $existingFiles['file_payment'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'أخرى') || str_contains($att->notes, 'اخرى')) {
                        $existingFiles['file_other_attachments'] = $att->file_path;
                    }
                }
            }
        }
    }
@endphp

<!-- BREADCRUMBS & PAGE HEADER -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" style="color: var(--primary-container); text-decoration: none;">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" style="color: var(--primary-container); text-decoration: none;">خيارات التعادل</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">دكتوراه سورية</li>
            </ol>
        </nav>
        <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
            <i class="fa-solid fa-user-graduate me-2" style="color: var(--heritage-gold);"></i> معاملة تعادل درجة دكتوراه سورية
        </h3>
        <p class="body-md text-muted mb-0">الرجاء إدخال البيانات المطلوبة بدقة والتنقل بين الخطوات لرفع المستندات وإرسال الطلب لمجلس التعليم العالي.</p>
    </div>
</div>

<!-- WIZARD MAIN CONTAINER -->
<div class="card border-0 shadow-sm" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
    <div class="card-body p-4 p-md-5">
        
        <!-- Multi-Step Progress Indicators (7 STEPS TOTAL) -->
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
                <span class="wizard-label d-none d-md-inline">الدكتوراه</span>
            </div>
            <div class="wizard-step" data-step="6">
                <div class="wizard-icon">6</div>
                <span class="wizard-label d-none d-md-inline">المرفقات</span>
            </div>
            <div class="wizard-step" data-step="7">
                <div class="wizard-icon">7</div>
                <span class="wizard-label d-none d-md-inline">المراجعة</span>
            </div>
        </div>

        <!-- Form Tag -->
        <form action="{{ route('university.apply.syrian_doctorate.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">

            <!-- ================= STEP 1: PERSONAL INFO & UNIVERSITY REQUEST ================= -->
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب طلب التقييم الصادر عن الجامعة
                </h5>
                
                <input type="hidden" name="equivalence_frequency" value="تعادل للمرة الأولى">
                <input type="hidden" name="has_previous_degree" value="0">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم المرشح الكامل *</label>
                        <input type="text" name="full_name" id="input-fullName" class="form-control academic-input" placeholder="الاسم والنسبة" value="{{ old('full_name', optional(optional($draft)->candidate)->full_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأب *</label>
                        <input type="text" name="father_name" id="input-fatherName" class="form-control academic-input" placeholder="اسم الأب" value="{{ old('father_name', optional(optional($draft)->candidate)->father_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأم ونسبتها *</label>
                        <input type="text" name="mother_name" id="input-motherName" class="form-control academic-input" placeholder="اسم ونسبة الأم" value="{{ old('mother_name', optional(optional($draft)->candidate)->mother_name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنسية *</label>
                        <select name="nationality_id" id="input-nationality" class="form-select academic-input" onchange="updateSyrianStatus(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('nationality_id', optional(optional($draft)->candidate)->nationality_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_syrian" id="input-isSyrian" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الرقم الوطني / رقم جواز السفر *</label>
                        <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="الرقم الوطني المكون من 11 خانة" value="{{ old('national_id', optional(optional($draft)->candidate)->national_id) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الميلاد *</label>
                        <input type="date" name="dob" id="input-dob" class="form-control academic-input" value="{{ old('dob', optional(optional($draft)->candidate)->dob) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الوظيفة الحالية للمرشح *</label>
                        <input type="text" name="job_title" id="input-jobTitle" class="form-control academic-input" placeholder="مثال: دكتور، باحث، أستاذ محاضر" value="{{ old('job_title', optional(optional($draft)->candidate)->job_title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنس *</label>
                        <select name="gender" id="input-gender" class="form-select academic-input" required>
                            <option value="ذكر" {{ old('gender', optional(optional($draft)->candidate)->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                            <option value="أنثى" {{ old('gender', optional(optional($draft)->candidate)->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">البريد الإلكتروني *</label>
                        <input type="email" name="email" id="input-email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', optional(optional($draft)->candidate)->email) }}" oninput="this.setCustomValidity('')" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف المحمول *</label>
                        <input type="text" name="mobile" id="input-mobile" class="form-control academic-input" placeholder="09xxxxxxxx" maxlength="10" pattern="[0-9]{10}" value="{{ old('mobile', optional(optional($draft)->candidate)->mobile) }}" oninput="this.setCustomValidity(''); this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف الأرضي</label>
                        <input type="text" name="phone" id="input-phone" class="form-control academic-input" placeholder="011xxxxxxx" maxlength="10" pattern="[0-9]{10}" value="{{ old('phone', optional(optional($draft)->candidate)->phone) }}" oninput="this.setCustomValidity(''); this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">عنوان الإقامة الحالي بالتفصيل *</label>
                        <textarea name="address" id="input-address" class="form-control academic-input" rows="2" placeholder="المحافظة - المدينة - الشارع - البناء" required>{{ old('address', optional(optional($draft)->candidate)->address) }}</textarea>
                    </div>

                    <!-- UNIVERSITY EVALUATION REQUEST LETTER DETAILS -->
                    <div class="col-12 mt-4">
                        <div class="card p-3.5 shadow-sm border-0" style="background-color: var(--surface-container-low); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);">
                                <i class="fa-solid fa-file-signature me-1.5" style="color: var(--heritage-gold);"></i> بيانات كتاب طلب التقييم الصادر عن الجامعة
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم كتاب طلب التقييم الصادر عن الجامعة *</label>
                                    <input type="text" name="req_no" id="input-reqNo" class="form-control academic-input" placeholder="أدخل أرقام كتاب الجامعة فقط" pattern="[0-9]+" value="{{ old('req_no', optional($draft)->new_uni_request_no) }}" oninput="this.setCustomValidity(''); this.value = this.value.replace(/[^0-9]/g, '')" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ كتاب طلب التقييم الصادر عن الجامعة *</label>
                                    <input type="date" name="req_date" id="input-reqDate" class="form-control academic-input" value="{{ old('req_date', optional($draft)->new_uni_request_date) }}" required>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="form-check form-switch p-3 border rounded d-flex align-items-center gap-3" style="background-color: #f0f7ff; border-color: #93c5fd !important; border-right: 4px solid #1D4ED8 !important;">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="input-isFirstTime" name="is_first_time" value="1" {{ old('is_first_time', optional($draft)->is_first_time ?? 1) ? 'checked' : '' }} style="width: 2.4em; height: 1.3em; cursor: pointer;">
                                        <label class="form-check-label fw-bold text-dark mb-0 label-md" for="input-isFirstTime" style="cursor: pointer;">
                                            هل يقدم المرشح معاملة التعادل للمرة الأولى؟ (نفقات على طلب تعادل للمرة الأولى دكتوراه سورية)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 2: HIGH SCHOOL INFO ================= -->
            <div class="form-section" id="step-2" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات الشهادة الثانوية للمرشح
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الدولة المانحة للثانوية *</label>
                        <select name="hs_country_id" id="input-hsCountry" class="form-select academic-input" onchange="toggleHsCountrySection(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('hs_country_id', optional($hsEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">نوع البكالوريا *</label>
                        <select name="hs_type" id="input-hsType" class="form-select academic-input" required>
                            @php $oldHsType = old('hs_type', optional($hsEd)->section_name); @endphp
                            <option value="علمي" {{ $oldHsType == 'علمي' ? 'selected' : '' }}>علمي</option>
                            <option value="أدبي" {{ $oldHsType == 'أدبي' ? 'selected' : '' }}>أدبي</option>
                            <option value="تجاري" {{ $oldHsType == 'تجاري' ? 'selected' : '' }}>تجاري</option>
                            <option value="صناعي" {{ $oldHsType == 'صناعي' ? 'selected' : '' }}>صناعي</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الحصول على الشهادة *</label>
                        <input type="date" name="hs_grant_date" id="input-hsDate" class="form-control academic-input" value="{{ old('hs_grant_date', optional($hsEd)->grant_date) }}" required>
                    </div>

                    <!-- Conditional high school equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="hs-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الشهادة الثانوية غير السورية</h6>
                            <p class="label-sm text-muted mb-3">بما أن الشهادة الثانوية غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم وتاريخ قرار المعادلة الصادر عن وزارة التربية السورية، ورفع صورة القرار في خطوة المرفقات النهائية (إجباري).</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار معادلة الشهادة الثانوية *</label>
                                    <input type="text" name="hs_decision_no" id="input-hsDecisionNo" class="form-control academic-input" placeholder="أدخل رقم القرار الرسمي" value="{{ old('hs_decision_no', $hsEd && $hsEd->notes ? preg_replace('/.*رقم قرار المعادلة الثانوية:\s*([^\|]+).*/u', '$1', $hsEd->notes) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ قرار معادلة الشهادة الثانوية *</label>
                                    <input type="date" name="hs_decision_date" id="input-hsDecisionDate" class="form-control academic-input" value="{{ old('hs_decision_date', $hsEd && $hsEd->notes && str_contains($hsEd->notes, 'تاريخ القرار:') ? preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $hsEd->notes) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 3: BACHELOR'S DEGREE ================= -->
            <div class="form-section" id="step-3" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: بيانات الإجازة الجامعية الأولى (البكالوريوس)
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الدولة المانحة للإجازة *</label>
                        <select name="ba_country_id" id="input-baCountry" class="form-select academic-input" onchange="toggleBaCountrySection(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ba_country_id', optional($baEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="ba-uni-select-container">
                        <label class="form-label label-md fw-medium text-dark">الجامعة المانحة *</label>
                        <select name="ba_university_id" id="input-baUniId" class="form-select academic-input">
                            <option value="">-- اختر الجامعة --</option>
                            @foreach($universities as $uni)
                                @if($uni->country && $uni->country->name === 'سوريا')
                                    <option value="{{ $uni->id }}" {{ old('ba_university_id', optional($baEd)->university_id) == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="ba-uni-text-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">اسم الجامعة الأجنبية / الجهة المانحة *</label>
                        <input type="text" name="ba_university_other" id="input-baUniOther" class="form-control academic-input" placeholder="اسم الجامعة الكامل" value="{{ old('ba_university_other', optional($baEd)->section_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        @php $oldBaRank = old('ba_rank', optional($baEd)->rank); @endphp
                        <select name="ba_rank" id="input-baRank" class="form-select academic-input" required>
                            <option value="امتياز" {{ $oldBaRank == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ $oldBaRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldBaRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ $oldBaRank == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية والفرع (التخصص العام) *</label>
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: كلية العلوم" value="{{ old('ba_faculty', optional($baEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق) *</label>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: قسم الكيمياء الحيوية" value="{{ old('ba_department', optional($baEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالإجازة *</label>
                        <input type="date" name="ba_registration_date" id="input-baRegDate" class="form-control academic-input" value="{{ old('ba_registration_date', optional($baEd)->registration_date) }}" oninput="this.setCustomValidity(''); const g = document.getElementById('input-baGrantDate'); if(g) g.setCustomValidity('');" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التخرج / الحصول عليها *</label>
                        <input type="date" name="ba_grant_date" id="input-baGrantDate" class="form-control academic-input" value="{{ old('ba_grant_date', optional($baEd)->grant_date) }}" oninput="this.setCustomValidity('')" required>
                    </div>

                    <!-- Conditional bachelor's equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="ba-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الإجازة الجامعية الأولى غير السورية</h6>
                            <p class="label-sm text-muted mb-3">بما أن الإجازة الجامعية الأولى غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم وتاريخ قرار المعادلة الصادر عن وزارة التعليم العالي والبحث العلمي السورية، ورفع صورة القرار في خطوة المرفقات النهائية (إجباري).</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار تعادل الإجازة الجامعية *</label>
                                    <input type="text" name="ba_decision_no" id="input-baDecisionNo" class="form-control academic-input" placeholder="أدخل رقم قرار التعادل الرسمي" value="{{ old('ba_decision_no', $baEd && $baEd->notes ? preg_replace('/.*رقم قرار معادلة الإجازة:\s*([^\|]+).*/u', '$1', $baEd->notes) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ قرار تعادل الإجازة الجامعية *</label>
                                    <input type="date" name="ba_decision_date" id="input-baDecisionDate" class="form-control academic-input" value="{{ old('ba_decision_date', $baEd && $baEd->notes && str_contains($baEd->notes, 'تاريخ القرار:') ? preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $baEd->notes) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 4: MASTER'S DEGREE & OPTIONAL DIPLOMA ================= -->
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: بيانات درجة الماجستير (والدبلوم إن وجد)
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الدولة المانحة للماجستير *</label>
                        <select name="ma_country_id" id="input-maCountry" class="form-select academic-input" onchange="toggleMaCountrySection(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ma_country_id', optional($maEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="ma-uni-select-container">
                        <label class="form-label label-md fw-medium text-dark">الجامعة المانحة للماجستير *</label>
                        <select name="ma_university_id" id="input-maUniId" class="form-select academic-input">
                            <option value="">-- اختر الجامعة --</option>
                            @foreach($universities as $uni)
                                @if($uni->country && $uni->country->name === 'سوريا')
                                    <option value="{{ $uni->id }}" {{ old('ma_university_id', optional($maEd)->university_id) == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="ma-uni-text-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">اسم الجامعة المانحة للماجستير *</label>
                        <input type="text" name="ma_university_other" id="input-maUniOther" class="form-control academic-input" placeholder="اسم الجامعة الكامل" value="{{ old('ma_university_other', optional($maEd)->section_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        @php $oldMaRank = old('ma_rank', optional($maEd)->rank); @endphp
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="امتياز" {{ $oldMaRank == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ $oldMaRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldMaRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ $oldMaRank == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية (التخصص العام للماجستير) *</label>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة الميكانيكية والكهربائية" value="{{ old('ma_faculty', optional($maEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق للماجستير) *</label>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="مثال: هندسة الحواسيب والأتمتة" value="{{ old('ma_department', optional($maEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالماجستير *</label>
                        <input type="date" name="ma_registration_date" id="input-maRegDate" class="form-control academic-input" value="{{ old('ma_registration_date', optional($maEd)->registration_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ المناقشة *</label>
                        <input type="date" name="ma_defense_date" id="input-maDefenseDate" class="form-control academic-input" value="{{ old('ma_defense_date', optional($maEd)->defense_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ منح الدرجة *</label>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" value="{{ old('ma_grant_date', optional($maEd)->grant_date) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">اسم الأستاذ المشرف على الماجستير *</label>
                        <input type="text" name="ma_supervisor" id="input-maSupervisor" class="form-control academic-input" placeholder="اسم المشرف الرئيسي ولقبه العلمي" value="{{ old('ma_supervisor', optional($maEd)->supervisor_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">عنوان رسالة الماجستير *</label>
                        <input type="text" name="ma_thesis_title" id="input-maThesisTitle" class="form-control academic-input" placeholder="عنوان الرسالة باللغة العربية كاملاً" value="{{ old('ma_thesis_title', optional($maEd)->thesis_title) }}" required>
                    </div>

                    <!-- Conditional Master's equivalence if country is not Syria (OPTIONAL for Syrian Doctorate) -->
                    <div class="col-12 mt-4" id="ma-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة شهادة الماجستير غير السورية (اختياري)</h6>
                            <p class="label-sm text-muted mb-3">بما أن شهادة الماجستير غير صادرة عن إحدى الجامعات السورية، يمكنك إدخال رقم وتاريخ قرار التعادل الصادر عن مجلس التعليم العالي وإرفاق صورة القرار إن وجد (اختياري في هذه المعاملة).</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار تعادل الماجستير (اختياري)</label>
                                    <input type="text" name="ma_decision_no" id="input-maDecisionNo" class="form-control academic-input" placeholder="أدخل رقم قرار التعادل إن وجد" value="{{ old('ma_decision_no', $maEd && $maEd->notes ? preg_replace('/.*رقم قرار معادلة شهادة الماجستير:\s*([^\|]+).*/u', '$1', $maEd->notes) : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ قرار تعادل الماجستير (اختياري)</label>
                                    <input type="date" name="ma_decision_date" id="input-maDecisionDate" class="form-control academic-input" value="{{ old('ma_decision_date', $maEd && $maEd->notes && str_contains($maEd->notes, 'تاريخ القرار:') ? preg_replace('/.*تاريخ القرار:\s*([0-9\-]+).*/u', '$1', $maEd->notes) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OPTIONAL POSTGRADUATE DIPLOMA -->
                    <div class="col-12 mt-3">
                        <div class="card p-3 border shadow-sm" style="background-color: #f8fafc; border-color: #e2e8f0 !important; border-radius: 6px;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="check-hasDiploma" name="has_diploma" value="1" {{ old('has_diploma', $dipEd ? 1 : 0) ? 'checked' : '' }} onchange="toggleDiplomaSection(this)" style="cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark mb-0 label-md" for="check-hasDiploma" style="cursor: pointer;">
                                    هل يحمل المرشح شهادة دبلوم دراسات عليا / دبلوم تأهيل وتخصص (إن وجد)؟
                                </label>
                            </div>
                            <div id="diploma-fields-container" style="display: {{ old('has_diploma', $dipEd ? 1 : 0) ? 'block' : 'none' }};">
                                <div class="row g-3 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label label-md fw-medium text-dark">الجامعة المانحة للدبلوم</label>
                                        <select name="dip_university_id" id="input-dipUni" class="form-select academic-input">
                                            <option value="">-- اختر الجامعة --</option>
                                            @foreach($universities as $uni)
                                                @if($uni->country && $uni->country->name === 'سوريا')
                                                    <option value="{{ $uni->id }}" {{ old('dip_university_id', optional($dipEd)->university_id) == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label label-md fw-medium text-dark">كلية / تخصص الدبلوم</label>
                                        <input type="text" name="dip_faculty" id="input-dipFaculty" class="form-control academic-input" placeholder="اسم الدبلوم" value="{{ old('dip_faculty', optional($dipEd)->general_specialization) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label label-md fw-medium text-dark">تاريخ الحصول على الدبلوم</label>
                                        <input type="date" name="dip_grant_date" id="input-dipDate" class="form-control academic-input" value="{{ old('dip_grant_date', optional($dipEd)->grant_date) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 5: SYRIAN DOCTORATE DEGREE ================= -->
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-award fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: بيانات درجة الدكتوراه السورية (المؤهل المراد تعادله)
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الجامعة السورية المانحة للدكتوراه *</label>
                        <select name="phd_university_id" id="input-phdUniId" class="form-select academic-input" required>
                            <option value="">-- اختر الجامعة السورية --</option>
                            @foreach($universities as $uni)
                                @if($uni->country && $uni->country->name === 'سوريا')
                                    <option value="{{ $uni->id }}" {{ old('phd_university_id', optional($phdEd)->university_id ?? Auth::user()->university_id) == $uni->id ? 'selected' : '' }}>{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        @php $oldPhdRank = old('phd_rank', optional($phdEd)->rank); @endphp
                        <select name="phd_rank" id="input-phdRank" class="form-select academic-input" required>
                            <option value="شرف" {{ $oldPhdRank == 'شرف' ? 'selected' : '' }}>شرف</option>
                            <option value="امتياز" {{ $oldPhdRank == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ $oldPhdRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldPhdRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                            <option value="مقبول" {{ $oldPhdRank == 'مقبول' ? 'selected' : '' }}>مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية (التخصص العام) *</label>
                        <input type="text" name="phd_faculty" id="input-phdFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة الميكانيكية والكهربائية" value="{{ old('phd_faculty', optional($phdEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق) *</label>
                        <input type="text" name="phd_department" id="input-phdDept" class="form-control academic-input" placeholder="مثال: قسم هندسة الحواسيب والأتمتة" value="{{ old('phd_department', optional($phdEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالدكتوراه *</label>
                        <input type="date" name="phd_registration_date" id="input-phdRegDate" class="form-control academic-input" value="{{ old('phd_registration_date', optional($phdEd)->registration_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ المناقشة *</label>
                        <input type="date" name="phd_defense_date" id="input-phdDefenseDate" class="form-control academic-input" value="{{ old('phd_defense_date', optional($phdEd)->defense_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ منح الدرجة *</label>
                        <input type="date" name="phd_grant_date" id="input-phdGrantDate" class="form-control academic-input" value="{{ old('phd_grant_date', optional($phdEd)->grant_date) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">اسم الأستاذ المشرف على الدكتوراه *</label>
                        <input type="text" name="phd_supervisor" id="input-phdSupervisor" class="form-control academic-input" placeholder="اسم المشرف الرئيسي ولقبه العلمي" value="{{ old('phd_supervisor', optional($phdEd)->supervisor_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">عنوان أطروحة الدكتوراه *</label>
                        <input type="text" name="phd_thesis_title" id="input-phdThesisTitle" class="form-control academic-input" placeholder="عنوان الأطروحة باللغة العربية كاملاً" value="{{ old('phd_thesis_title', optional($phdEd)->thesis_title) }}" required>
                    </div>

                    <!-- Experience Section (Optional) -->
                    <div class="col-12 mt-3">
                        <div class="card p-3 border shadow-sm" style="background-color: #f8fafc; border-color: #e2e8f0 !important; border-radius: 6px;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="check-hasExperience" name="has_experience" value="1" {{ old('has_experience', optional($phdEd)->experience_from_year ? 1 : 0) ? 'checked' : '' }} onchange="toggleExperienceSection(this)" style="cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark mb-0 label-md" for="check-hasExperience" style="cursor: pointer;">
                                    هل يمتلك المرشح خبرة تدريسية جامعية أو بحثية سابقة؟
                                </label>
                            </div>
                            <div id="experience-fields-container" style="display: {{ old('has_experience', optional($phdEd)->experience_from_year ? 1 : 0) ? 'block' : 'none' }};">
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label label-md fw-medium text-dark">مكان الخبرة التدريسية / الجامعة</label>
                                        <input type="text" name="exp_place" id="input-expPlace" class="form-control academic-input" placeholder="الجامعة أو الكلية أو المعهد" value="{{ old('exp_place', optional($phdEd)->notes ? preg_replace('/.*مكان الخبرة التدريسية:\s*([^\|]+).*/u', '$1', $phdEd->notes) : '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label label-md fw-medium text-dark">تاريخ البدء</label>
                                        <input type="date" name="exp_from_year" id="input-expFrom" class="form-control academic-input" value="{{ old('exp_from_year', optional($phdEd)->experience_from_year) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label label-md fw-medium text-dark">تاريخ الانتهاء</label>
                                        <input type="date" name="exp_to_year" id="input-expTo" class="form-control academic-input" value="{{ old('exp_to_year', optional($phdEd)->experience_to_year) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 6: ATTACHMENTS UPLOAD (13 ITEMS) ================= -->
            <div class="form-section" id="step-6" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-folder-open fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: رفع المرفقات والوثائق الثبوتية للدكتوراه السورية
                </h5>
                
                <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center gap-2" style="background-color: #f0f7ff; border-color: #93c5fd; border-radius: 4px;">
                    <i class="fa-solid fa-circle-info text-primary fs-5"></i>
                    <span class="fs-8 text-dark">يجب أن تكون جميع المرفقات بصيغة ملفات <strong>(PDF)</strong> واضحة، وبحجم أقصى <strong>2 ميغابايت</strong> لكل ملف.</span>
                </div>

                <div class="row g-3">
                    <!-- 1. شهادة الثانوية -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">1. شهادة الثانوية العامة *</label>
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

                    <!-- 2. شهادة الإجازة الجامعة -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">2. شهادة الإجازة الجامعة (البكالوريوس) *</label>
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

                    <!-- 3. شهادة دبلوم إن وجد -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">3. شهادة دبلوم إن وجد (اختياري)</label>
                        <input type="file" name="file_diploma_cert" id="input-fileDipCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_diploma_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_diploma_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 4. شهادة ماجستير -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">4. شهادة ماجستير *</label>
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

                    <!-- 5. شهادة الدكتوراه -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">5. شهادة الدكتوراه السورية المصدقة أصولاً *</label>
                        <input type="file" name="file_phd_cert" id="input-filePhdCert" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_phd_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_phd_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 6. ملخص عن الأطروحة -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">6. ملخص عن أطروحة الدكتوراه باللغة العربية *</label>
                        <input type="file" name="file_thesis_summary" id="input-fileThesisSummary" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_thesis_summary']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_thesis_summary']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 7. قرارات مجلس الجامعة للدكتوراه -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">7. قرارات مجلس الجامعة للدكتوراه (وثيقة تواريخ التسجيل والمناقشة والمنح) <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_phd_council_decisions" id="input-filePhdDecisions" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_phd_council_decisions']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_phd_council_decisions']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 8. قرارات مجلس الجامعة للماجستير -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">8. قرارات مجلس الجامعة للماجستير (وثيقة تواريخ التسجيل والمناقشة والمنح) <span class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="file_ma_council_decisions" id="input-fileMaDecisions" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_council_decisions']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_council_decisions']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 9. شهادة ICDL -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">9. شهادة ICDL *</label>
                        <input type="file" name="file_icdl" id="input-fileIcdl" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_icdl']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_icdl']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 10. شهادة اختبار اللغة الإنكليزية -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">10. شهادة اختبار اللغة الإنكليزية *</label>
                        <input type="file" name="file_english_test" id="input-fileEnglishTest" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_english_test']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_english_test']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 11. رسوم التعادل (125,000 ل.س) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">11. إيصال تسديد رسم تعادل 125,000 ل.س للدكتوراه *</label>
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

                    <!-- Official University Request Letter & CV -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">كتاب طلب التقويم الصادر عن الجامعة *</label>
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

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">السيرة الذاتية للمرشح *</label>
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

                    <!-- Non-Syrian Decisions conditional files -->
                    <div class="col-md-6" id="att-hs-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">قرار معادلة الشهادة الثانوية غير السورية *</label>
                        <input type="file" name="hs_decision_file" id="input-fileHsDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['hs_decision_file']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['hs_decision_file']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6" id="att-ba-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">قرار معادلة الإجازة الجامعية غير السورية *</label>
                        <input type="file" name="ba_decision_file" id="input-fileBaDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['ba_decision_file']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['ba_decision_file']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6" id="att-ma-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark" id="label-maDecisionFile">قرار معادلة شهادة الماجستير غير السورية <span id="badge-maDecisionOptional" class="text-muted fw-normal fs-8">(اختياري)</span></label>
                        <input type="file" name="ma_decision_file" id="input-fileMaDecision" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['ma_decision_file']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['ma_decision_file']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 12. مرفقات أخرى -->
                    <div class="col-12 mt-3">
                        <label class="form-label label-md fw-medium text-dark">12. مرفقات أخرى (اختياري)</label>
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

            <!-- ================= STEP 7: REVIEW & SUBMIT ================= -->
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
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-baCountry"></span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة / الجهة المانحة:</strong> <span id="preview-baUni"></span></div>
                                <div class="col-md-6"><strong>التخصص العام (الكلية):</strong> <span id="preview-baFaculty"></span></div>
                                <div class="col-md-6"><strong>التخصص الدقيق (القسم):</strong> <span id="preview-baDept"></span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-baRank"></span></div>
                                <div class="col-md-6"><strong>تاريخ التسجيل:</strong> <span id="preview-baRegDate"></span></div>
                                <div class="col-md-6"><strong>تاريخ التخرج:</strong> <span id="preview-baGrantDate"></span></div>
                                <div class="col-md-6" id="preview-baDecisionContainer"><strong>رقم قرار المعادلة السوري:</strong> <span id="preview-baDecisionNo"></span></div>
                            </div>
                        </div>

                        <!-- Group 4: Master's & Diploma -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير (والدبلوم إن وجد):</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-maCountry"></span></div>
                                <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-maUni"></span></div>
                                <div class="col-md-6"><strong>التخصص العام (الكلية):</strong> <span id="preview-maFaculty"></span></div>
                                <div class="col-md-6"><strong>التخصص الدقيق (القسم):</strong> <span id="preview-maDept"></span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-maRank"></span></div>
                                <div class="col-md-6"><strong>المشرف العلمي:</strong> <span id="preview-maSupervisor"></span></div>
                                <div class="col-md-6"><strong>تواريخ التسجيل / المناقشة / المنح:</strong> 
                                    <span id="preview-maRegDate"></span> / <span id="preview-maDefDate"></span> / <span id="preview-maGrantDate"></span>
                                </div>
                                <div class="col-12"><strong>عنوان رسالة الماجستير:</strong> <span id="preview-maThesisTitle"></span></div>
                                <div class="col-md-6" id="preview-maDecisionContainer"><strong>رقم وتاريخ قرار تعادل الماجستير (إن وجد):</strong> <span id="preview-maDecisionNo"></span></div>
                                <div class="col-12 mt-2" id="preview-diplomaContainer" style="display: none;">
                                    <div class="card p-2 border-0" style="background-color: var(--surface-container-low);">
                                        <strong>دبلوم دراسات عليا / تأهيل وتخصص:</strong>
                                        <div>الجامعة: <span id="preview-dipUni"></span> | الكلية: <span id="preview-dipFaculty"></span> | تاريخ المنح: <span id="preview-dipDate"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Group 5: Syrian Doctorate & Experience -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-award me-1" style="color: var(--heritage-gold);"></i> 5. بيانات درجة الدكتوراه السورية والخبرة التدريسية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(5)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الجامعة السورية المانحة:</strong> <span id="preview-phdUni"></span></div>
                                <div class="col-md-6"><strong>الكلية (التخصص العام):</strong> <span id="preview-phdFaculty"></span></div>
                                <div class="col-md-6"><strong>القسم (التخصص الدقيق):</strong> <span id="preview-phdDept"></span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-phdRank"></span></div>
                                <div class="col-md-6"><strong>المشرف العلمي:</strong> <span id="preview-phdSupervisor"></span></div>
                                <div class="col-md-6"><strong>تواريخ التسجيل / المناقشة / المنح:</strong> 
                                    <span id="preview-phdRegDate"></span> / <span id="preview-phdDefDate"></span> / <span id="preview-phdGrantDate"></span>
                                </div>
                                <div class="col-12"><strong>عنوان أطروحة الدكتوراه:</strong> <strong class="text-primary" id="preview-phdThesisTitle"></strong></div>
                                
                                <!-- Experience Section in Report -->
                                <div class="col-12 mt-2" id="preview-phdExperienceContainer" style="display: none;">
                                    <div class="card p-2 border-0" style="background-color: var(--surface-container-low);">
                                        <strong>الخبرة التدريسية:</strong> 
                                        <div>الجهة/المكان: <span id="preview-phdExpPlace"></span> | من تاريخ: <span id="preview-phdExpFrom"></span> إلى تاريخ: <span id="preview-phdExpTo"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Group 6: University Evaluation Request -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 6. كتاب طلب التقويم الصادر عن الجامعة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo"></span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final check warning confirmation -->
                <div class="form-check form-switch mt-4 p-3 border rounded d-flex align-items-center gap-3" style="background-color: var(--surface-container-low); border-color: var(--outline-variant) !important;">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required style="width: 2.2em; height: 1.2em; cursor: pointer;">
                    <label class="form-check-label fw-bold text-dark mb-0 label-md" for="chkConfirm" style="cursor: pointer;">
                        نصادق نحن في إدارة الجامعة على صحة كافة البيانات والوثائق المرفقة أعلاه، ونتحمل المسؤولية القانونية كاملة عن أي معلومات مغلوطة.
                    </label>
                </div>
            </div>

            <!-- ================= BUTTONS NAVIGATION ================= -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 pt-3 border-top" style="border-top-color: var(--outline-variant) !important;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-navy px-4 py-2" id="btn-prev" onclick="prevStep()" style="display: none;">
                        <i class="fa-solid fa-arrow-right me-1"></i> السابق
                    </button>
                    <div id="spacer-prev"></div>

                    <button type="submit" formnovalidate name="action" value="save_draft" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة ومتابعة لاحقاً
                    </button>
                </div>

                <div>
                    <button type="button" class="btn btn-primary px-4 py-2" id="btn-next" onclick="nextStep()">
                        التالي <i class="fa-solid fa-arrow-left ms-1"></i>
                    </button>

                    <button type="submit" name="action" value="submit_final" class="btn btn-gold-cta px-5 py-2 fw-bold shadow-sm" id="btn-submit" style="display: none;">
                        إنهاء وإرسال الطلب للوزارة <i class="fa-solid fa-paper-plane ms-1"></i>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    let currentStep = 1;
    const totalSteps = 7;
    const syriaCountryId = "{{ $syriaId }}";

    function updateSyrianStatus(selectElem) {
        const isSyrian = (selectElem.value == syriaCountryId);
        document.getElementById('input-isSyrian').value = isSyrian ? 1 : 0;
    }

    function toggleHsCountrySection(selectElem) {
        const isSyria = (selectElem.value == syriaCountryId);
        const section = document.getElementById('hs-equivalence-section');
        const attContainer = document.getElementById('att-hs-decision-file-container');
        const decNo = document.getElementById('input-hsDecisionNo');
        const decDate = document.getElementById('input-hsDecisionDate');

        if (section) section.style.display = isSyria ? 'none' : 'block';
        if (attContainer) attContainer.style.display = isSyria ? 'none' : 'block';
        if (decNo) decNo.required = !isSyria;
        if (decDate) decDate.required = !isSyria;
    }

    function toggleBaCountrySection(selectElem) {
        const isSyria = (selectElem.value == syriaCountryId);
        const selectContainer = document.getElementById('ba-uni-select-container');
        const textContainer = document.getElementById('ba-uni-text-container');
        const eqSection = document.getElementById('ba-equivalence-section');
        const attContainer = document.getElementById('att-ba-decision-file-container');
        const uniId = document.getElementById('input-baUniId');
        const uniOther = document.getElementById('input-baUniOther');
        const decNo = document.getElementById('input-baDecisionNo');
        const decDate = document.getElementById('input-baDecisionDate');

        if (isSyria) {
            if (selectContainer) selectContainer.style.display = 'block';
            if (textContainer) textContainer.style.display = 'none';
            if (eqSection) eqSection.style.display = 'none';
            if (attContainer) attContainer.style.display = 'none';
            if (uniId) uniId.required = true;
            if (uniOther) uniOther.required = false;
            if (decNo) decNo.required = false;
            if (decDate) decDate.required = false;
        } else {
            if (selectContainer) selectContainer.style.display = 'none';
            if (textContainer) textContainer.style.display = 'block';
            if (eqSection) eqSection.style.display = 'block';
            if (attContainer) attContainer.style.display = 'block';
            if (uniId) uniId.required = false;
            if (uniOther) uniOther.required = true;
            if (decNo) decNo.required = true;
            if (decDate) decDate.required = true;
        }
    }

    function updateMaDecisionAttachmentLabel() {
        const maDecisionNo = document.getElementById('input-maDecisionNo');
        const maDecisionDate = document.getElementById('input-maDecisionDate');
        const badge = document.getElementById('badge-maDecisionOptional');
        const hasData = (maDecisionNo && maDecisionNo.value.trim() !== '') || (maDecisionDate && maDecisionDate.value !== '');
        if (badge) {
            if (hasData) {
                badge.innerHTML = '<span class="text-danger fw-bold">* (إجباري لإدخال بيانات القرار)</span>';
            } else {
                badge.innerHTML = '<span class="text-muted fw-normal fs-8">(اختياري)</span>';
            }
        }
    }

    function toggleMaCountrySection(selectElem) {
        const isSyria = (selectElem.value == syriaCountryId);
        const selectContainer = document.getElementById('ma-uni-select-container');
        const textContainer = document.getElementById('ma-uni-text-container');
        const eqSection = document.getElementById('ma-equivalence-section');
        const attContainer = document.getElementById('att-ma-decision-file-container');
        const uniId = document.getElementById('input-maUniId');
        const uniOther = document.getElementById('input-maUniOther');
        const decNo = document.getElementById('input-maDecisionNo');
        const decDate = document.getElementById('input-maDecisionDate');

        if (isSyria) {
            if (selectContainer) selectContainer.style.display = 'block';
            if (textContainer) textContainer.style.display = 'none';
            if (eqSection) eqSection.style.display = 'none';
            if (attContainer) attContainer.style.display = 'none';
            if (uniId) uniId.required = true;
            if (uniOther) uniOther.required = false;
            if (decNo) decNo.required = false;
            if (decDate) decDate.required = false;
        } else {
            if (selectContainer) selectContainer.style.display = 'none';
            if (textContainer) textContainer.style.display = 'block';
            if (eqSection) eqSection.style.display = 'block';
            if (attContainer) attContainer.style.display = 'block';
            if (uniId) uniId.required = false;
            if (uniOther) uniOther.required = true;
            // Master's equivalence decision inputs are OPTIONAL for Syrian Doctorate
            if (decNo) decNo.required = false;
            if (decDate) decDate.required = false;
        }
        updateMaDecisionAttachmentLabel();
    }

    function toggleDiplomaSection(checkElem) {
        const container = document.getElementById('diploma-fields-container');
        if (container) container.style.display = checkElem.checked ? 'block' : 'none';
    }

    function toggleExperienceSection(checkElem) {
        const container = document.getElementById('experience-fields-container');
        if (container) container.style.display = checkElem.checked ? 'block' : 'none';
    }

    function showStep(step) {
        document.querySelectorAll('.form-section').forEach(sec => sec.style.display = 'none');
        const currentSection = document.getElementById(`step-${step}`);
        if (currentSection) currentSection.style.display = 'block';

        // Update step badges
        document.querySelectorAll('.wizard-step').forEach((s, idx) => {
            const stepNum = idx + 1;
            s.classList.remove('active', 'completed');
            if (stepNum === step) {
                s.classList.add('active');
            } else if (stepNum < step) {
                s.classList.add('completed');
            }
        });

        // Update progress bar
        const progress = ((step - 1) / (totalSteps - 1)) * 100;
        const bar = document.getElementById('wizard-progress-bar');
        if (bar) bar.style.width = `${progress}%`;

        // Controls
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');

        if (btnPrev) btnPrev.style.display = (step === 1) ? 'none' : 'inline-block';
        if (btnNext) btnNext.style.display = (step === totalSteps) ? 'none' : 'inline-block';
        if (btnSubmit) btnSubmit.style.display = (step === totalSteps) ? 'inline-block' : 'none';

        if (step === 7) {
            populateReview();
        }

        window.scrollTo({ top: 150, behavior: 'smooth' });
    }

    function validateStep(step) {
        const currentSection = document.getElementById(`step-${step}`);
        if (!currentSection) return true;

        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        for (let inp of inputs) {
            if (inp.offsetParent !== null && !inp.checkValidity()) {
                inp.reportValidity();
                return false;
            }
        }

        if (step === 1) {
            // Step 1: Personal Info Validation
            const mobileInput = document.getElementById('input-mobile');
            const phoneInput = document.getElementById('input-phone');
            const emailInput = document.getElementById('input-email');

            if (mobileInput) {
                const mobileVal = mobileInput.value.trim();
                if (!/^[0-9]{10}$/.test(mobileVal)) {
                    mobileInput.setCustomValidity('رقم الهاتف المحمول يجب أن يكون مكوناً من 10 أرقام (مثال: 0991168727).');
                    mobileInput.reportValidity();
                    mobileInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    mobileInput.focus();
                    return false;
                } else {
                    mobileInput.setCustomValidity('');
                }
            }

            if (phoneInput && phoneInput.value.trim() !== '') {
                const phoneVal = phoneInput.value.trim();
                if (!/^[0-9]{10}$/.test(phoneVal)) {
                    phoneInput.setCustomValidity('رقم الهاتف الأرضي يجب أن يكون مكوناً من 10 أرقام (مثال: 0113414768).');
                    phoneInput.reportValidity();
                    phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    phoneInput.focus();
                    return false;
                } else {
                    phoneInput.setCustomValidity('');
                }
            }

            if (emailInput) {
                const emailVal = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailVal)) {
                    emailInput.setCustomValidity('يرجى إدخال عنوان بريد إلكتروني صحيح (مثال: example@domain.com).');
                    emailInput.reportValidity();
                    emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    emailInput.focus();
                    return false;
                } else {
                    emailInput.setCustomValidity('');
                }
            }
        } else if (step === 2) {
            // Step 2: High School Info Validation
            const hsCountry = document.getElementById('input-hsCountry');
            if (hsCountry && hsCountry.value != syriaCountryId) {
                const decNo = document.getElementById('input-hsDecisionNo');
                const decDate = document.getElementById('input-hsDecisionDate');
                if (decNo && !decNo.value.trim()) {
                    decNo.setCustomValidity('يرجى إدخال رقم قرار معادلة الشهادة الثانوية غير السورية للمتابعة.');
                    decNo.reportValidity();
                    decNo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    decNo.focus();
                    return false;
                } else if (decNo) {
                    decNo.setCustomValidity('');
                }

                if (decDate && !decDate.value) {
                    decDate.setCustomValidity('يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية للمتابعة.');
                    decDate.reportValidity();
                    decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    decDate.focus();
                    return false;
                } else if (decDate) {
                    decDate.setCustomValidity('');
                }
            }
        } else if (step === 3) {
            // Step 3: Bachelor's Degree Info Validation
            const baCountry = document.getElementById('input-baCountry');
            if (baCountry && baCountry.value != syriaCountryId) {
                const uniOther = document.getElementById('input-baUniOther');
                if (uniOther && !uniOther.value.trim()) {
                    uniOther.setCustomValidity('يرجى إدخال اسم الجامعة الأجنبية / الجهة المانحة.');
                    uniOther.reportValidity();
                    uniOther.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    uniOther.focus();
                    return false;
                } else if (uniOther) {
                    uniOther.setCustomValidity('');
                }

                const decNo = document.getElementById('input-baDecisionNo');
                const decDate = document.getElementById('input-baDecisionDate');
                if (decNo && !decNo.value.trim()) {
                    decNo.setCustomValidity('يرجى إدخال رقم قرار تعادل الإجازة الجامعية الأولى غير السورية للمتابعة.');
                    decNo.reportValidity();
                    decNo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    decNo.focus();
                    return false;
                } else if (decNo) {
                    decNo.setCustomValidity('');
                }

                if (decDate && !decDate.value) {
                    decDate.setCustomValidity('يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية الأولى غير السورية للمتابعة.');
                    decDate.reportValidity();
                    decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    decDate.focus();
                    return false;
                } else if (decDate) {
                    decDate.setCustomValidity('');
                }
            } else if (baCountry) {
                const uniId = document.getElementById('input-baUniId');
                if (uniId && !uniId.value) {
                    uniId.setCustomValidity('يرجى اختيار الجامعة المانحة للإجازة.');
                    uniId.reportValidity();
                    uniId.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    uniId.focus();
                    return false;
                } else if (uniId) {
                    uniId.setCustomValidity('');
                }
            }

            const regDate = document.getElementById('input-baRegDate');
            const grantDate = document.getElementById('input-baGrantDate');
            if (regDate && grantDate && regDate.value && grantDate.value) {
                if (grantDate.value < regDate.value) {
                    grantDate.setCustomValidity('تاريخ التخرج يجب أن يكون بعد تاريخ التسجيل.');
                    grantDate.reportValidity();
                    grantDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    grantDate.focus();
                    return false;
                }
            }
        } else if (step === 4) {
            // Step 4: Master's Degree Info Validation (Decision fields are explicitly OPTIONAL)
            const maCountry = document.getElementById('input-maCountry');
            if (maCountry && maCountry.value != syriaCountryId) {
                const uniOther = document.getElementById('input-maUniOther');
                if (uniOther && !uniOther.value.trim()) {
                    uniOther.setCustomValidity('يرجى إدخال اسم الجامعة المانحة للماجستير.');
                    uniOther.reportValidity();
                    uniOther.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    uniOther.focus();
                    return false;
                } else if (uniOther) {
                    uniOther.setCustomValidity('');
                }
            } else if (maCountry) {
                const uniId = document.getElementById('input-maUniId');
                if (uniId && !uniId.value) {
                    uniId.setCustomValidity('يرجى اختيار الجامعة المانحة للماجستير.');
                    uniId.reportValidity();
                    uniId.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    uniId.focus();
                    return false;
                } else if (uniId) {
                    uniId.setCustomValidity('');
                }
            }

            const regDate = document.getElementById('input-maRegDate');
            const defDate = document.getElementById('input-maDefenseDate');
            const grantDate = document.getElementById('input-maGrantDate');
            if (regDate && defDate && defDate.value < regDate.value) {
                defDate.setCustomValidity('تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل.');
                defDate.reportValidity();
                defDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                defDate.focus();
                return false;
            }
            if (defDate && grantDate && grantDate.value < defDate.value) {
                grantDate.setCustomValidity('تاريخ المنح يجب أن يكون بعد تاريخ المناقشة.');
                grantDate.reportValidity();
                grantDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                grantDate.focus();
                return false;
            }
        } else if (step === 5) {
            const regDate = document.getElementById('input-phdRegDate');
            const defDate = document.getElementById('input-phdDefenseDate');
            const grantDate = document.getElementById('input-phdGrantDate');
            if (regDate && defDate && defDate.value < regDate.value) {
                defDate.setCustomValidity('تاريخ المناقشة للدكتوراه يجب أن يكون بعد تاريخ التسجيل.');
                defDate.reportValidity();
                defDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                defDate.focus();
                return false;
            }
            if (defDate && grantDate && grantDate.value < defDate.value) {
                grantDate.setCustomValidity('تاريخ المنح للدكتوراه يجب أن يكون بعد تاريخ المناقشة.');
                grantDate.reportValidity();
                grantDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                grantDate.focus();
                return false;
            }
        } else if (step === 6) {
            // Required attachments validation for Syrian Doctorate
            const requiredAttachments = [
                { id: 'input-fileHsCert', name: 'شهادة الثانوية العامة' },
                { id: 'input-fileBaCert', name: 'شهادة الإجازة الجامعة (البكالوريوس)' },
                { id: 'input-fileMaCert', name: 'شهادة ماجستير' },
                { id: 'input-filePhdCert', name: 'شهادة الدكتوراه السورية المصدقة أصولاً' },
                { id: 'input-fileThesisSummary', name: 'ملخص عن أطروحة الدكتوراه باللغة العربية' },
                { id: 'input-fileIcdl', name: 'شهادة ICDL' },
                { id: 'input-fileEnglishTest', name: 'شهادة اختبار اللغة الإنكليزية' },
                { id: 'input-filePayment', name: 'إيصال تسديد رسم تعادل 125,000 ل.س للدكتوراه' },
                { id: 'input-fileUniRequest', name: 'كتاب طلب التقويم الصادر عن الجامعة' },
                { id: 'input-fileCv', name: 'السيرة الذاتية للمرشح' }
            ];

            // Check non-syrian high school decision
            const hsCountry = document.getElementById('input-hsCountry');
            const hsDecisionNo = document.getElementById('input-hsDecisionNo');
            const hsDecisionDate = document.getElementById('input-hsDecisionDate');
            if ((hsCountry && hsCountry.value != syriaCountryId) || (hsDecisionNo && hsDecisionNo.value.trim() !== '') || (hsDecisionDate && hsDecisionDate.value !== '')) {
                requiredAttachments.push({ id: 'input-fileHsDecision', name: 'قرار معادلة الشهادة الثانوية غير السورية' });
            }

            // Check non-syrian bachelor decision
            const baCountry = document.getElementById('input-baCountry');
            const baDecisionNo = document.getElementById('input-baDecisionNo');
            const baDecisionDate = document.getElementById('input-baDecisionDate');
            if ((baCountry && baCountry.value != syriaCountryId) || (baDecisionNo && baDecisionNo.value.trim() !== '') || (baDecisionDate && baDecisionDate.value !== '')) {
                requiredAttachments.push({ id: 'input-fileBaDecision', name: 'قرار معادلة الإجازة الجامعية غير السورية' });
            }

            // Check non-syrian master decision (Required ONLY IF user entered decision no or date)
            const maDecisionNo = document.getElementById('input-maDecisionNo');
            const maDecisionDate = document.getElementById('input-maDecisionDate');
            const hasMaDecisionData = (maDecisionNo && maDecisionNo.value.trim() !== '') || (maDecisionDate && maDecisionDate.value !== '');
            if (hasMaDecisionData) {
                requiredAttachments.push({ id: 'input-fileMaDecision', name: 'قرار معادلة شهادة الماجستير غير السورية' });
            }

            for (const att of requiredAttachments) {
                const inputEl = document.getElementById(att.id);
                if (inputEl) {
                    const hasFile = inputEl.files && inputEl.files.length > 0;
                    const parentContainer = inputEl.closest('.col-md-6, .col-12');
                    const isAlreadyUploaded = parentContainer && parentContainer.querySelector('.badge.bg-success-subtle');

                    if (!hasFile && !isAlreadyUploaded) {
                        inputEl.setCustomValidity(`يرجى رفع ملف (${att.name}) بصيغة PDF للمتابعة.`);
                        inputEl.reportValidity();
                        inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        inputEl.focus();
                        return false;
                    } else {
                        inputEl.setCustomValidity('');
                    }
                }
            }
        }

        return true;
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    function goToStep(step) {
        if (step >= 1 && step <= totalSteps) {
            currentStep = step;
            showStep(currentStep);
        }
    }

    function formatDateDisplay(val) {
        if (!val || val === '-') return '-';
        const match = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return `${match[3]}/${match[2]}/${match[1]}`;
        }
        return val;
    }

    function populateReview() {
        const getVal = id => {
            const el = document.getElementById(id);
            if (!el) return '-';
            if (el.tagName === 'SELECT') {
                return el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '-';
            }
            return el.value || '-';
        };

        // Group 1: Personal Details
        document.getElementById('preview-fullName').textContent = getVal('input-fullName');
        document.getElementById('preview-fatherName').textContent = getVal('input-fatherName');
        document.getElementById('preview-motherName').textContent = getVal('input-motherName');
        document.getElementById('preview-nationalId').textContent = getVal('input-nationalId');
        document.getElementById('preview-dob').textContent = formatDateDisplay(getVal('input-dob'));
        document.getElementById('preview-jobTitle').textContent = getVal('input-jobTitle');
        document.getElementById('preview-gender').textContent = getVal('input-gender');
        document.getElementById('preview-email').textContent = getVal('input-email');
        document.getElementById('preview-mobile').textContent = getVal('input-mobile');
        document.getElementById('preview-address').textContent = getVal('input-address');

        // Group 2: High School
        const hsCountry = document.getElementById('input-hsCountry');
        document.getElementById('preview-hsCountry').textContent = hsCountry ? (hsCountry.options[hsCountry.selectedIndex] ? hsCountry.options[hsCountry.selectedIndex].text : '-') : '-';
        document.getElementById('preview-hsType').textContent = getVal('input-hsType');
        document.getElementById('preview-hsDate').textContent = formatDateDisplay(getVal('input-hsDate'));
        if (hsCountry && hsCountry.value != syriaCountryId) {
            document.getElementById('preview-hsDecisionContainer').style.display = 'block';
            document.getElementById('preview-hsDecisionNo').textContent = getVal('input-hsDecisionNo');
        } else {
            document.getElementById('preview-hsDecisionContainer').style.display = 'none';
        }

        // Group 3: Bachelor's
        const baCountry = document.getElementById('input-baCountry');
        const isBaSyria = (baCountry && baCountry.value == syriaCountryId);
        document.getElementById('preview-baCountry').textContent = baCountry ? (baCountry.options[baCountry.selectedIndex] ? baCountry.options[baCountry.selectedIndex].text : '-') : '-';
        if (isBaSyria) {
            const baUni = document.getElementById('input-baUniId');
            document.getElementById('preview-baUni').textContent = baUni ? (baUni.options[baUni.selectedIndex] ? baUni.options[baUni.selectedIndex].text : '-') : '-';
            document.getElementById('preview-baDecisionContainer').style.display = 'none';
        } else {
            document.getElementById('preview-baUni').textContent = getVal('input-baUniOther');
            document.getElementById('preview-baDecisionContainer').style.display = 'block';
            document.getElementById('preview-baDecisionNo').textContent = getVal('input-baDecisionNo');
        }
        document.getElementById('preview-baFaculty').textContent = getVal('input-baFaculty');
        document.getElementById('preview-baDept').textContent = getVal('input-baDept');
        document.getElementById('preview-baRank').textContent = getVal('input-baRank');
        document.getElementById('preview-baRegDate').textContent = formatDateDisplay(getVal('input-baRegDate'));
        document.getElementById('preview-baGrantDate').textContent = formatDateDisplay(getVal('input-baGrantDate'));

        // Group 4: Master's & Diploma
        const maCountry = document.getElementById('input-maCountry');
        const isMaSyria = (maCountry && maCountry.value == syriaCountryId);
        document.getElementById('preview-maCountry').textContent = maCountry ? (maCountry.options[maCountry.selectedIndex] ? maCountry.options[maCountry.selectedIndex].text : '-') : '-';
        if (isMaSyria) {
            const maUni = document.getElementById('input-maUniId');
            document.getElementById('preview-maUni').textContent = maUni ? (maUni.options[maUni.selectedIndex] ? maUni.options[maUni.selectedIndex].text : '-') : '-';
            document.getElementById('preview-maDecisionContainer').style.display = 'none';
        } else {
            document.getElementById('preview-maUni').textContent = getVal('input-maUniOther');
            const maDecNo = getVal('input-maDecisionNo');
            const maDecDate = formatDateDisplay(getVal('input-maDecisionDate'));
            if (maDecNo !== '-' || maDecDate !== '-') {
                document.getElementById('preview-maDecisionContainer').style.display = 'block';
                document.getElementById('preview-maDecisionNo').textContent = `${maDecNo} (تاريخ: ${maDecDate})`;
            } else {
                document.getElementById('preview-maDecisionContainer').style.display = 'none';
            }
        }
        document.getElementById('preview-maFaculty').textContent = getVal('input-maFaculty');
        document.getElementById('preview-maDept').textContent = getVal('input-maDept');
        document.getElementById('preview-maRank').textContent = getVal('input-maRank');
        document.getElementById('preview-maSupervisor').textContent = getVal('input-maSupervisor');
        document.getElementById('preview-maRegDate').textContent = formatDateDisplay(getVal('input-maRegDate'));
        document.getElementById('preview-maDefDate').textContent = formatDateDisplay(getVal('input-maDefenseDate'));
        document.getElementById('preview-maGrantDate').textContent = formatDateDisplay(getVal('input-maGrantDate'));
        document.getElementById('preview-maThesisTitle').textContent = getVal('input-maThesisTitle');

        // Diploma preview
        const hasDiploma = document.getElementById('input-hasDiploma');
        if (hasDiploma && hasDiploma.checked) {
            document.getElementById('preview-diplomaContainer').style.display = 'block';
            document.getElementById('preview-dipUni').textContent = getVal('input-dipUni');
            document.getElementById('preview-dipFaculty').textContent = getVal('input-dipFaculty');
            document.getElementById('preview-dipDate').textContent = formatDateDisplay(getVal('input-dipGrantDate'));
        } else {
            document.getElementById('preview-diplomaContainer').style.display = 'none';
        }

        // Group 5: Syrian Doctorate & Experience
        const phdUni = document.getElementById('input-phdUniId');
        document.getElementById('preview-phdUni').textContent = phdUni ? (phdUni.options[phdUni.selectedIndex] ? phdUni.options[phdUni.selectedIndex].text : '-') : '-';
        document.getElementById('preview-phdFaculty').textContent = getVal('input-phdFaculty');
        document.getElementById('preview-phdDept').textContent = getVal('input-phdDept');
        document.getElementById('preview-phdRank').textContent = getVal('input-phdRank');
        document.getElementById('preview-phdSupervisor').textContent = getVal('input-phdSupervisor');
        document.getElementById('preview-phdRegDate').textContent = formatDateDisplay(getVal('input-phdRegDate'));
        document.getElementById('preview-phdDefDate').textContent = formatDateDisplay(getVal('input-phdDefenseDate'));
        document.getElementById('preview-phdGrantDate').textContent = formatDateDisplay(getVal('input-phdGrantDate'));
        document.getElementById('preview-phdThesisTitle').textContent = getVal('input-phdThesisTitle');

        // Doctorate Experience preview
        const hasExp = document.getElementById('input-hasExperience');
        if (hasExp && hasExp.checked) {
            document.getElementById('preview-phdExperienceContainer').style.display = 'block';
            document.getElementById('preview-phdExpPlace').textContent = getVal('input-expPlace');
            document.getElementById('preview-phdExpFrom').textContent = formatDateDisplay(getVal('input-expFrom'));
            document.getElementById('preview-phdExpTo').textContent = formatDateDisplay(getVal('input-expTo'));
        } else {
            document.getElementById('preview-phdExperienceContainer').style.display = 'none';
        }

        // Group 6: Request
        document.getElementById('preview-reqNo').textContent = getVal('input-reqNo');
        document.getElementById('preview-reqDate').textContent = formatDateDisplay(getVal('input-reqDate'));
    }

    // Init on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        const hsCountry = document.getElementById('input-hsCountry');
        if (hsCountry) toggleHsCountrySection(hsCountry);

        const baCountry = document.getElementById('input-baCountry');
        if (baCountry) toggleBaCountrySection(baCountry);

        const maCountry = document.getElementById('input-maCountry');
        if (maCountry) toggleMaCountrySection(maCountry);

        const maDecNo = document.getElementById('input-maDecisionNo');
        if (maDecNo) maDecNo.addEventListener('input', updateMaDecisionAttachmentLabel);

        const maDecDate = document.getElementById('input-maDecisionDate');
        if (maDecDate) maDecDate.addEventListener('input', updateMaDecisionAttachmentLabel);

        updateMaDecisionAttachmentLabel();

        const initialStep = {{ request('step', 1) }};
        showStep(initialStep);

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                this.setCustomValidity('');
            });
        });
    });
</script>

<style>
    /* Wizard steps progress styling */
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 2.5rem;
    }

    .wizard-progress {
        position: absolute;
        top: 20px;
        right: 0;
        height: 4px;
        background-color: var(--heritage-gold);
        z-index: 1;
        transition: width 0.35s ease;
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
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 6px;
        color: var(--on-surface-variant);
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

@extends('layouts.university')

@section('title', 'تقديم طلب تعادل ماجستير سوري')

@section('content')

@php
    $candidate = $draft ? $draft->candidate : null;
    $hsEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'ثانوية')) || $e->education_level_id == 4 || $e->education_level_id == 6 || ($e->section_name && in_array($e->section_name, ['علمي', 'أدبي', 'تجاري', 'صناعي'])); }) : null;
    $baEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'إجازة')) || $e->education_level_id == 1; }) : null;
    $maEd = $draft ? $draft->educations->first(function($e) { return (optional($e->level)->name && str_contains(optional($e->level)->name, 'ماجستير')) || $e->education_level_id == 2 || $e->education_level_id == 3; }) : null;

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
                    } elseif (str_contains($att->notes, 'شهادة الماجستير')) {
                        $existingFiles['file_ma_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'تواريخ')) {
                        $existingFiles['file_ma_dates'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'ملخص')) {
                        $existingFiles['file_thesis_summary'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'كتاب الجامعة')) {
                        $existingFiles['file_uni_request'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'اللغة')) {
                        $existingFiles['file_lang_icdl'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'السيرة')) {
                        $existingFiles['file_cv'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'إيصال')) {
                        $existingFiles['file_payment'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'خبرة')) {
                        $existingFiles['file_exp_cert'] = $att->file_path;
                    } elseif (str_contains($att->notes, 'العقود')) {
                        $existingFiles['file_contracts'] = $att->file_path;
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
                <li class="breadcrumb-item active text-muted" aria-current="page">ماجستير سوري</li>
            </ol>
        </nav>
        <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
            <i class="fa-solid fa-file-invoice-dollar me-2" style="color: var(--heritage-gold);"></i> معاملة تعادل درجة ماجستير سورية
        </h3>
        <p class="body-md text-muted mb-0">الرجاء إدخال البيانات المطلوبة بدقة والتنقل بين الخطوات لرفع المستندات وإرسال الطلب لمجلس التعليم العالي.</p>
    </div>
</div>

<!-- WIZARD MAIN CONTAINER -->
<div class="card border-0 shadow-sm" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
    <div class="card-body p-4 p-md-5">
        
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
        <form action="{{ route('university.apply.syrian_masters.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">

            <!-- ================= STEP 1: PERSONAL INFO & UNIVERSITY REQUEST ================= -->
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب طلب التقييم الصادر عن الجامعة
                </h5>
                
                <!-- DEFAULT EQUIVALENCE FREQUENCY: FIRST TIME -->
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
                        <input type="text" name="job_title" id="input-jobTitle" class="form-control academic-input" placeholder="مثال: مهندس، موظف، معيد" value="{{ old('job_title', optional(optional($draft)->candidate)->job_title) }}" required>
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
                                            هل يقدم المرشح معاملة التعادل للمرة الأولى؟
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

            <!-- ================= STEP 3: UNIVERSITY DEGREE ================= -->
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
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: هندسة المعلوماتية" value="{{ old('ba_faculty', optional($baEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق) *</label>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: هندسة البرمجيات ونظم المعلومات" value="{{ old('ba_department', optional($baEd)->exact_specialization) }}" required>
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

            <!-- ================= STEP 4: SYRIAN MASTER'S INFO ================= -->
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: بيانات درجة الماجستير السورية والخبرة
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجامعة المانحة للماجستير *</label>
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
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        @php $oldMaRank = old('ma_rank', optional($maEd)->rank); @endphp
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="امتياز" {{ $oldMaRank == 'امتياز' ? 'selected' : '' }}>امتياز</option>
                            <option value="جيد جداً" {{ $oldMaRank == 'جيد جداً' ? 'selected' : '' }}>جيد جداً</option>
                            <option value="جيد" {{ $oldMaRank == 'جيد' ? 'selected' : '' }}>جيد</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأستاذ المشرف *</label>
                        <input type="text" name="ma_supervisor" id="input-maSupervisor" class="form-control academic-input" placeholder="الاسم الثنائي للمشرف مع اللقب العلمي" value="{{ old('ma_supervisor', optional($maEd)->supervisor_name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية والفرع (التخصص العام للماجستير) *</label>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="كلية الهندسة المدنية" value="{{ old('ma_faculty', optional($maEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق للماجستير) *</label>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="إدارة المشاريع" value="{{ old('ma_department', optional($maEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالدرجة *</label>
                        <input type="date" name="ma_registration_date" id="input-maRegDate" class="form-control academic-input" value="{{ old('ma_registration_date', optional($maEd)->registration_date) }}" oninput="this.setCustomValidity(''); const d = document.getElementById('input-maDefDate'); if(d) d.setCustomValidity(''); const g = document.getElementById('input-maGrantDate'); if(g) g.setCustomValidity('');" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ المناقشة *</label>
                        <input type="date" name="ma_defense_date" id="input-maDefDate" class="form-control academic-input" value="{{ old('ma_defense_date', optional($maEd)->defense_date) }}" oninput="this.setCustomValidity(''); const g = document.getElementById('input-maGrantDate'); if(g) g.setCustomValidity('');" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ منح الدرجة (الحصول على الشهادة) *</label>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" value="{{ old('ma_grant_date', optional($maEd)->grant_date) }}" onchange="checkMasterGrantDateForExperience()" oninput="this.setCustomValidity(''); checkMasterGrantDateForExperience();" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">عنوان رسالة الماجستير (الأطروحة) بالتفصيل *</label>
                        <textarea name="ma_thesis_title" id="input-maThesisTitle" class="form-control academic-input" rows="2" placeholder="أدخل عنوان رسالة الماجستير كما هو مذكور في مصدقة التخرج" required>{{ old('ma_thesis_title', optional($maEd)->thesis_title) }}</textarea>
                    </div>

                    <!-- Experience details toggle (> 2 years since master grant date) -->
                    <div class="col-12 mt-4" id="experience-toggle-container" style="display: none;">
                        <div class="card border-0 shadow-sm p-3" style="background-color: var(--surface-container-low); border: 1px solid var(--outline-variant) !important; border-radius: 4px;">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="input-hasExperience" name="has_experience" value="1" {{ old('has_experience', optional($maEd)->experience_from_year ? '1' : '0') == '1' ? 'checked' : '' }} onchange="toggleExperienceSection(this)">
                                <label class="form-check-label fw-bold text-dark ms-2 label-md" for="input-hasExperience">هل يمتلك المرشح خبرة تدريسية تفوق سنتين؟</label>
                            </div>
                            <div class="row g-3" id="experience-details-section" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">مكان الخبرة التدريسية (الجهة/الجامعة) *</label>
                                    <input type="text" name="exp_place" id="input-expPlace" class="form-control academic-input" placeholder="اسم الكلية أو الجامعة والمعهد" value="{{ old('exp_place', optional($maEd)->notes ? preg_replace('/.*مكان الخبرة التدريسية:\s*/u', '', $maEd->notes) : '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-md fw-medium text-dark">من تاريخ *</label>
                                    <input type="date" name="exp_from_year" id="input-expFrom" class="form-control academic-input" value="{{ old('exp_from_year', optional($maEd)->experience_from_year) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-md fw-medium text-dark">إلى تاريخ *</label>
                                    <input type="date" name="exp_to_year" id="input-expTo" class="form-control academic-input" value="{{ old('exp_to_year', optional($maEd)->experience_to_year) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 5: ATTACHMENTS ================= -->
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: رفع المرفقات والمستندات الثبوتية المطلوبة (بصيغة PDF فقط - أقصى حجم 2 ميغابايت لكل ملف)
                </h5>
                
                <div class="alert border-0 shadow-sm mb-4" style="background-color: var(--surface-container-low); border-right: 4px solid var(--primary-container) !important; color: var(--primary-container); border-radius: 4px;">
                    <i class="fa-solid fa-info-circle me-1" style="color: var(--heritage-gold);"></i> يرجى التأكد من رفع ملفات PDF واضحة ومصدقة أصولاً، <strong>بحجم لا يتجاوز 2 ميغابايت لكل مرفق</strong> لعدم تعليق المعاملة ولضمان سرعة معالجة وحفظ الملفات.
                </div>

                <div class="row g-4">
                    <!-- High School Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية *</label>
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

                    <!-- HS Equivalence Decision File (Conditional) -->
                    <div class="col-md-6" id="hs-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الثانوية (وزارة التربية) *</label>
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

                    <!-- Bachelor Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى *</label>
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

                    <!-- Bachelor Equivalence Decision File (Conditional) -->
                    <div class="col-md-6" id="ba-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الجامعية الأولى *</label>
                        <input type="file" name="ba_decision_file" id="input-baDecisionFile" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['ba_decision_file']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['ba_decision_file']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Master Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن شهادة الماجستير *</label>
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

                    <!-- Master Registration / Defense dates doc -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">وثيقة تواريخ التسجيل والمناقشة والمنح بالماجستير *</label>
                        <input type="file" name="file_ma_dates" id="input-fileMaDates" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_ma_dates']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_dates']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Arabic Thesis Summary -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">ملخص باللغة العربية عن رسالة الماجستير إلكترونياً *</label>
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

                    <!-- University Request Doc -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية *</label>
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

                    <!-- Language & ICDL Certificates -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">شهادة اللغة الإنكليزية + شهادة ICDL معتمدة *</label>
                        <input type="file" name="file_lang_icdl" id="input-fileLangIcdl" class="form-control academic-input" accept=".pdf">
                        @if(isset($existingFiles['file_lang_icdl']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع سابقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_lang_icdl']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- CV -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">السيرة الذاتية للمرشح كاملة *</label>
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

                    <!-- Payment Receipt -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">إيصال تسديد رسم تعادل 100,000 ل.س *</label>
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

                    <!-- Experience Certificate (Conditional) -->
                    <div class="col-md-6 exp-conditional-file" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">شهادة خبرة لا تقل عن سنتين ما بعد الدرجة *</label>
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

                    <!-- Contracts & Salary Slips (Conditional) -->
                    <div class="col-md-6 exp-conditional-file" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">العقود وإيصالات الرواتب مصدقة أصولاً *</label>
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

                    <!-- Other Attachments (Optional) -->
                    <div class="col-12 mt-3">
                        <label class="form-label label-md fw-medium text-dark">مرفقات أخرى (اختياري)</label>
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

                        <!-- Group 4: Master's -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير والخبرة التدريسية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(4)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الجامعة المانحة (سورية):</strong> <span id="preview-maUni"></span></div>
                                <div class="col-md-6"><strong>التخصص العام:</strong> <span id="preview-maFaculty"></span></div>
                                <div class="col-md-6"><strong>التخصص الدقيق:</strong> <span id="preview-maDept"></span></div>
                                <div class="col-md-6"><strong>التقدير:</strong> <span id="preview-maRank"></span></div>
                                <div class="col-md-6"><strong>المشرف العلمي:</strong> <span id="preview-maSupervisor"></span></div>
                                <div class="col-md-6"><strong>تواريخ التسجيل / المناقشة / المنح:</strong> 
                                    <span id="preview-maRegDate"></span> / <span id="preview-maDefDate"></span> / <span id="preview-maGrantDate"></span>
                                </div>
                                <div class="col-12"><strong>عنوان أطروحة الماجستير:</strong> <span id="preview-maThesisTitle"></span></div>
                                
                                <!-- Experience Section in Report -->
                                <div class="col-12 mt-2" id="preview-experience-container" style="display: none;">
                                    <div class="card p-2 border-0" style="background-color: var(--surface-container-low);">
                                        <strong>الخبرة التدريسية (> سنتين):</strong> 
                                        <div>الجهة/المكان: <span id="preview-expPlace"></span> | من عام: <span id="preview-expFrom"></span> إلى عام: <span id="preview-expTo"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Group 5: University Evaluation Request -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 5. كتاب طلب التقويم الصادر عن الجامعة:</h6>
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

                    <button type="submit" formnovalidate name="action" value="save_draft" class="btn btn-outline-warning px-3 py-2 fw-bold" id="btn-draft" title="حفظ البيانات المعبأة كمسودة للعودة إليها لاحقاً برقم الطلب">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة ومتابعة لاحقاً
                    </button>
                </div>

                <div>
                    <button type="button" class="btn btn-primary px-4 py-2" id="btn-next" onclick="changeStep(1)">
                        التالي <i class="fa-solid fa-arrow-left ms-1"></i>
                    </button>

                    <button type="submit" name="action" value="submit_final" class="btn btn-gold-cta px-5 py-2" id="btn-submit" style="display: none;">
                        إنهاء وإرسال الطلب للوزارة <i class="fa-solid fa-paper-plane ms-1"></i>
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 6;
    const syriaCountryId = "{{ $syriaId }}";

    // Toggle experience details
    function toggleExperienceSection(checkbox) {
        const expSection = document.getElementById('experience-details-section');
        const inputs = expSection.querySelectorAll('input');
        const fileInputs = document.querySelectorAll('.exp-conditional-file');

        const hasExistingExpCert = {{ isset($existingFiles['file_exp_cert']) ? 'true' : 'false' }};
        const hasExistingContracts = {{ isset($existingFiles['file_contracts']) ? 'true' : 'false' }};

        if (checkbox.checked) {
            expSection.style.display = 'flex';
            inputs.forEach(input => input.required = true);
            fileInputs.forEach(div => {
                div.style.display = 'block';
                const fileInp = div.querySelector('input');
                if (fileInp) {
                    if (fileInp.name === 'file_exp_cert') {
                        fileInp.required = !hasExistingExpCert;
                    } else if (fileInp.name === 'file_contracts') {
                        fileInp.required = !hasExistingContracts;
                    } else {
                        fileInp.required = true;
                    }
                }
            });
        } else {
            expSection.style.display = 'none';
            inputs.forEach(input => {
                input.required = false;
                input.value = '';
            });
            fileInputs.forEach(div => {
                div.style.display = 'none';
                const fileInp = div.querySelector('input');
                if (fileInp) fileInp.required = false;
            });
        }
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

        const hasExpChk = document.getElementById('input-hasExperience');
        if (hasExpChk && hasExpChk.checked) {
            toggleExperienceSection(hasExpChk);
        } else {
            checkMasterGrantDateForExperience();
        }
    });

    function updateSyrianStatus(select) {
        const inputIsSyrian = document.getElementById('input-isSyrian');
        if (select.value == syriaCountryId) {
            inputIsSyrian.value = '1';
        } else {
            inputIsSyrian.value = '0';
        }
    }

    // Step navigation
    function changeStep(direction) {
        // Validate inputs in current step before proceeding forward
        if (direction === 1) {
            const currentSection = document.getElementById(`step-${currentStep}`);
            const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
            
            let isValid = true;
            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isValid = false;
                }
            });
            if (!isValid) return;

            const todayStr = new Date().toISOString().split('T')[0];

            if (currentStep === 1) {
                // Step 1: Personal Info Validation
                const mobileInput = document.getElementById('input-mobile');
                const phoneInput = document.getElementById('input-phone');
                const emailInput = document.getElementById('input-email');

                if (mobileInput) {
                    const mobileVal = mobileInput.value.trim();
                    if (!/^[0-9]{10}$/.test(mobileVal)) {
                        mobileInput.setCustomValidity('رقم الهاتف المحمول يجب أن يكون مكوناً من 10 أرقام (مثال: 0991168727).');
                        mobileInput.reportValidity();
                        return;
                    } else {
                        mobileInput.setCustomValidity('');
                    }
                }

                if (phoneInput && phoneInput.value.trim() !== '') {
                    const phoneVal = phoneInput.value.trim();
                    if (!/^[0-9]{10}$/.test(phoneVal)) {
                        phoneInput.setCustomValidity('رقم الهاتف الأرضي يجب أن يكون مكوناً من 10 أرقام (مثال: 0113414768).');
                        phoneInput.reportValidity();
                        return;
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
                        return;
                    } else {
                        emailInput.setCustomValidity('');
                    }
                }
            } else if (currentStep === 2) {
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
                        return;
                    } else if (decNo) {
                        decNo.setCustomValidity('');
                    }

                    if (decDate && !decDate.value) {
                        decDate.setCustomValidity('يرجى إدخال تاريخ قرار معادلة الشهادة الثانوية غير السورية للمتابعة.');
                        decDate.reportValidity();
                        decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decDate.focus();
                        return;
                    } else if (decDate) {
                        decDate.setCustomValidity('');
                    }
                }
            } else if (currentStep === 3) {
                // Step 3: Bachelor's Degree Info Validation
                const baCountry = document.getElementById('input-baCountry');
                if (baCountry && baCountry.value != syriaCountryId) {
                    const uniOther = document.getElementById('input-baUniOther');
                    if (uniOther && !uniOther.value.trim()) {
                        uniOther.setCustomValidity('يرجى إدخال اسم الجامعة الأجنبية / الجهة المانحة.');
                        uniOther.reportValidity();
                        uniOther.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        uniOther.focus();
                        return;
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
                        return;
                    } else if (decNo) {
                        decNo.setCustomValidity('');
                    }

                    if (decDate && !decDate.value) {
                        decDate.setCustomValidity('يرجى إدخال تاريخ قرار تعادل الإجازة الجامعية الأولى غير السورية للمتابعة.');
                        decDate.reportValidity();
                        decDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        decDate.focus();
                        return;
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
                        return;
                    } else if (uniId) {
                        uniId.setCustomValidity('');
                    }
                }

                const baRegInput = document.getElementById('input-baRegDate');
                const baGrantInput = document.getElementById('input-baGrantDate');

                if (baRegInput && baGrantInput) {
                    const regDate = baRegInput.value;
                    const grantDate = baGrantInput.value;

                    if (grantDate <= regDate) {
                        baGrantInput.setCustomValidity('تاريخ التخرج من الإجازة يجب أن يكون بعد تاريخ التسجيل بالإجازة.');
                        baGrantInput.reportValidity();
                        baGrantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        baGrantInput.focus();
                        return;
                    } else if (grantDate > todayStr) {
                        baGrantInput.setCustomValidity('تاريخ التخرج من الإجازة يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.');
                        baGrantInput.reportValidity();
                        baGrantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        baGrantInput.focus();
                        return;
                    } else {
                        baGrantInput.setCustomValidity('');
                    }
                }
            } else if (currentStep === 4) {
                // Step 4: Master's Degree Info Validation
                const maRegInput = document.getElementById('input-maRegDate');
                const maDefInput = document.getElementById('input-maDefDate');
                const maGrantInput = document.getElementById('input-maGrantDate');

                if (maRegInput && maDefInput) {
                    const regDate = maRegInput.value;
                    const defDate = maDefInput.value;

                    if (defDate <= regDate) {
                        maDefInput.setCustomValidity('تاريخ المناقشة يجب أن يكون بعد تاريخ التسجيل بالدرجة.');
                        maDefInput.reportValidity();
                        return;
                    } else {
                        maDefInput.setCustomValidity('');
                    }
                }

                if (maDefInput && maGrantInput) {
                    const defDate = maDefInput.value;
                    const grantDate = maGrantInput.value;

                    if (grantDate <= defDate) {
                        maGrantInput.setCustomValidity('تاريخ منح الدرجة (الحصول على الشهادة) يجب أن يكون بعد تاريخ المناقشة.');
                        maGrantInput.reportValidity();
                        return;
                    } else if (grantDate > todayStr) {
                        maGrantInput.setCustomValidity('تاريخ منح الدرجة (الحصول على الشهادة) يجب أن يكون قبل أو يساوي اليوم الحالي وليس في المستقبل.');
                        maGrantInput.reportValidity();
                        return;
                    } else {
                        maGrantInput.setCustomValidity('');
                    }
                }
            } else if (currentStep === 5) {
                // Step 5: Attachments Validation for Syrian Masters
                const requiredAttachments = [
                    { id: 'input-fileHsCert', name: 'شهادة الدراسة الثانوية' },
                    { id: 'input-fileBaCert', name: 'شهادة الإجازة الجامعة (البكالوريوس)' },
                    { id: 'input-fileMaCert', name: 'شهادة الماجستير السورية المصدقة أصولاً' },
                    { id: 'input-fileMaDates', name: 'وثيقة تواريخ التسجيل والمناقشة والمنح بالماجستير' },
                    { id: 'input-fileThesisSummary', name: 'ملخص عن رسالة الماجستير باللغة العربية' },
                    { id: 'input-fileLangIcdl', name: 'شهادة اللغة الإنكليزية + شهادة ICDL معتمدة' },
                    { id: 'input-filePayment', name: 'إيصال تسديد رسم تعادل 100,000 ل.س للماجستير' },
                    { id: 'input-fileUniRequest', name: 'كتاب طلب التقويم الصادر عن الجامعة' },
                    { id: 'input-fileCv', name: 'السيرة الذاتية للمرشح' }
                ];

                // Check non-syrian high school decision
                const hsCountry = document.getElementById('input-hsCountry');
                const hsDecisionNo = document.getElementById('input-hsDecisionNo');
                const hsDecisionDate = document.getElementById('input-hsDecisionDate');
                if ((hsCountry && hsCountry.value != syriaCountryId) || (hsDecisionNo && hsDecisionNo.value.trim() !== '') || (hsDecisionDate && hsDecisionDate.value !== '')) {
                    requiredAttachments.push({ id: 'input-hsDecisionFile', name: 'قرار معادلة الشهادة الثانوية غير السورية' });
                }

                // Check non-syrian bachelor decision
                const baCountry = document.getElementById('input-baCountry');
                const baDecisionNo = document.getElementById('input-baDecisionNo');
                const baDecisionDate = document.getElementById('input-baDecisionDate');
                if ((baCountry && baCountry.value != syriaCountryId) || (baDecisionNo && baDecisionNo.value.trim() !== '') || (baDecisionDate && baDecisionDate.value !== '')) {
                    requiredAttachments.push({ id: 'input-baDecisionFile', name: 'قرار معادلة الإجازة الجامعية غير السورية' });
                }

                // Check experience certificate if has_experience is checked
                const hasExp = document.getElementById('input-hasExperience');
                if (hasExp && hasExp.checked) {
                    requiredAttachments.push({ id: 'input-fileExpCert', name: 'شهادة خبرة لا تقل عن سنتين ما بعد الدرجة' });
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
                            return;
                        } else {
                            inputEl.setCustomValidity('');
                        }
                    }
                }
            }
        }

        // Hide current step
        document.getElementById(`step-${currentStep}`).style.display = 'none';
        document.getElementById(`step-${currentStep}`).classList.remove('active');

        // Update step index
        currentStep += direction;

        // Show new step
        const newSection = document.getElementById(`step-${currentStep}`);
        newSection.style.display = 'block';
        newSection.classList.add('active');

        // If entering final step (review), populate preview fields
        if (currentStep === 6) {
            updateReportPreview();
        }

        // Update indicators
        updateWizardProgress();

        // Scroll smooth to wizard top
        window.scrollTo({ top: 150, behavior: 'smooth' });
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

        if (currentStep === 1) {
            btnPrev.style.display = 'none';
            spacerPrev.style.display = 'block';
        } else {
            btnPrev.style.display = 'block';
            spacerPrev.style.display = 'none';
        }

        if (currentStep === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-block';
        } else {
            btnNext.style.display = 'inline-block';
            btnSubmit.style.display = 'none';
        }
    }

    function goToStep(step) {
        if (step >= 1 && step <= totalSteps) {
            currentStep = step;
            showStep(currentStep);
            window.scrollTo({ top: 150, behavior: 'smooth' });
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

    function updateReportPreview() {
        // Personal details
        document.getElementById('preview-fullName').innerText = document.getElementById('input-fullName').value;
        document.getElementById('preview-fatherName').innerText = document.getElementById('input-fatherName').value;
        document.getElementById('preview-motherName').innerText = document.getElementById('input-motherName').value;
        
        const nationalitySelect = document.getElementById('input-nationality');
        document.getElementById('preview-nationalId').innerText = document.getElementById('input-nationalId').value;
        document.getElementById('preview-dob').innerText = formatDateDisplay(document.getElementById('input-dob').value);
        document.getElementById('preview-jobTitle').innerText = document.getElementById('input-jobTitle').value;
        document.getElementById('preview-gender').innerText = document.getElementById('input-gender').value;
        document.getElementById('preview-email').innerText = document.getElementById('input-email').value;
        document.getElementById('preview-mobile').innerText = document.getElementById('input-mobile').value;
        document.getElementById('preview-address').innerText = document.getElementById('input-address').value;

        // HS details
        const hsCountrySelect = document.getElementById('input-hsCountry');
        document.getElementById('preview-hsCountry').innerText = hsCountrySelect.options[hsCountrySelect.selectedIndex].text;
        document.getElementById('preview-hsType').innerText = document.getElementById('input-hsType').value;
        document.getElementById('preview-hsDate').innerText = formatDateDisplay(document.getElementById('input-hsDate').value);
        
        if (hsCountrySelect.value != syriaCountryId) {
            document.getElementById('preview-hsDecisionContainer').style.display = 'block';
            document.getElementById('preview-hsDecisionNo').innerText = document.getElementById('input-hsDecisionNo').value;
        } else {
            document.getElementById('preview-hsDecisionContainer').style.display = 'none';
        }

        // BA details
        const baCountrySelect = document.getElementById('input-baCountry');
        document.getElementById('preview-baCountry').innerText = baCountrySelect.options[baCountrySelect.selectedIndex].text;
        
        if (baCountrySelect.value == syriaCountryId) {
            const baUniSelect = document.getElementById('input-baUniId');
            document.getElementById('preview-baUni').innerText = baUniSelect.options[baUniSelect.selectedIndex].text;
            document.getElementById('preview-baDecisionContainer').style.display = 'none';
        } else {
            document.getElementById('preview-baUni').innerText = document.getElementById('input-baUniOther').value;
            document.getElementById('preview-baDecisionContainer').style.display = 'block';
            document.getElementById('preview-baDecisionNo').innerText = document.getElementById('input-baDecisionNo').value;
        }
        document.getElementById('preview-baFaculty').innerText = document.getElementById('input-baFaculty').value;
        document.getElementById('preview-baDept').innerText = document.getElementById('input-baDept').value;
        document.getElementById('preview-baRank').innerText = document.getElementById('input-baRank').value;
        document.getElementById('preview-baRegDate').innerText = formatDateDisplay(document.getElementById('input-baRegDate').value);
        document.getElementById('preview-baGrantDate').innerText = formatDateDisplay(document.getElementById('input-baGrantDate').value);

        // MA details
        const maUniSelect = document.getElementById('input-maUniId');
        document.getElementById('preview-maUni').innerText = maUniSelect.options[maUniSelect.selectedIndex].text;
        document.getElementById('preview-maFaculty').innerText = document.getElementById('input-maFaculty').value;
        document.getElementById('preview-maDept').innerText = document.getElementById('input-maDept').value;
        document.getElementById('preview-maRank').innerText = document.getElementById('input-maRank').value;
        document.getElementById('preview-maSupervisor').innerText = document.getElementById('input-maSupervisor').value;
        document.getElementById('preview-maRegDate').innerText = formatDateDisplay(document.getElementById('input-maRegDate').value);
        document.getElementById('preview-maDefDate').innerText = formatDateDisplay(document.getElementById('input-maDefDate').value);
        document.getElementById('preview-maGrantDate').innerText = formatDateDisplay(document.getElementById('input-maGrantDate').value);
        document.getElementById('preview-maThesisTitle').innerText = document.getElementById('input-maThesisTitle').value;

        // Experience
        const hasExp = document.getElementById('input-hasExperience').checked;
        if (hasExp) {
            document.getElementById('preview-experience-container').style.display = 'block';
            document.getElementById('preview-expPlace').innerText = document.getElementById('input-expPlace').value;
            document.getElementById('preview-expFrom').innerText = formatDateDisplay(document.getElementById('input-expFrom').value);
            document.getElementById('preview-expTo').innerText = formatDateDisplay(document.getElementById('input-expTo').value);
        } else {
            document.getElementById('preview-experience-container').style.display = 'none';
        }

        // Request
        document.getElementById('preview-reqNo').innerText = document.getElementById('input-reqNo').value;
        document.getElementById('preview-reqDate').innerText = formatDateDisplay(document.getElementById('input-reqDate').value);
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

                area.innerHTML = `
                    <div class="alert alert-success py-2 px-3 fs-8 mt-2 shadow-sm border-0" style="background-color: #E6F4EA; color: #137333;">
                        <i class="fa-solid fa-circle-check fs-6 me-1.5" style="color: #137333;"></i>
                        <strong>تم الاستعلام والتعبئة بنجاح بالرقم الوطني (${c.national_id}):</strong><br>
                        تم جلب وتعبئة البيانات الشخصية والمؤهلات العلمية السابقة للمرشح (<strong>${c.full_name}</strong>) تلقائياً عبر جميع الخطوات! يمكنك الضغط على "التالي" لمتابعة الخطوات ومراجعة أو إضافة مرفقات جديدة.
                    </div>
                `;
            })
            .catch(err => {
                area.innerHTML = '<div class="alert alert-danger py-1.5 px-3 fs-8 mt-2">حدث خطأ أثناء إجراء عملية الاستعلام. يرجى التأكد من الرقم الوطني وإعادة المحاولة.</div>';
            });
    }

    function checkMasterGrantDateForExperience() {
        const grantDateInput = document.getElementById('input-maGrantDate');
        const container = document.getElementById('experience-toggle-container');
        const switchInput = document.getElementById('input-hasExperience');
        const detailsSection = document.getElementById('experience-details-section');

        if (!grantDateInput || !grantDateInput.value) {
            if (container) container.style.display = 'none';
            if (switchInput) switchInput.checked = false;
            if (detailsSection) detailsSection.style.display = 'none';
            return;
        }

        const grantDate = new Date(grantDateInput.value);
        const today = new Date();

        let yearsDiff = today.getFullYear() - grantDate.getFullYear();
        let monthsDiff = today.getMonth() - grantDate.getMonth();
        let daysDiff = today.getDate() - grantDate.getDate();

        if (monthsDiff < 0 || (monthsDiff === 0 && daysDiff < 0)) {
            yearsDiff--;
        }

        if (yearsDiff >= 2) {
            if (container) container.style.display = 'block';
        } else {
            if (container) container.style.display = 'none';
            if (switchInput) switchInput.checked = false;
            if (detailsSection) detailsSection.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hsCountry = document.getElementById('input-hsCountry');
        if (hsCountry) toggleHsCountrySection(hsCountry);

        const baCountry = document.getElementById('input-baCountry');
        if (baCountry) toggleBaCountrySection(baCountry);

        checkMasterGrantDateForExperience();

        const initialStep = {{ request('step', 1) }};
        showStep(initialStep);

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                this.setCustomValidity('');
            });
        });
    });
</script>
@endpush

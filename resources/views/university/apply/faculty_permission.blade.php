@extends('layouts.university')

@section('title', 'معاملة السماح بالتدريس - أعضاء الهيئة التدريسية')

@section('content')

@php
    $candidate = $draft ? $draft->candidate : null;
    $govEd = $draft ? $draft->educations->first(function($e) { 
        return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية')); 
    }) : null;
    $phdEd = $draft ? $draft->educations->first(function($e) { 
        return $e->thesis_title === 'شهادة الدكتوراه' || ((optional($e->level)->name && str_contains(optional($e->level)->name, 'دكتوراه')) && $e->thesis_title !== 'عضو هيئة تدريسية في جامعة حكومية'); 
    }) : null;
    $maEd = $draft ? $draft->educations->first(function($e) { 
        return $e->thesis_title === 'شهادة الماجستير' || ((optional($e->level)->name && str_contains(optional($e->level)->name, 'ماجستير')) || $e->education_level_id == 2); 
    }) : null;

    $existingFiles = [];
    if ($draft) {
        foreach ($draft->educations as $ed) {
            foreach ($ed->attachments as $att) {
                if ($att->attachment_type_id == 7 || ($att->notes && (str_contains($att->notes, 'طلب تقويم') || str_contains($att->notes, 'كتاب ترشيح') || str_contains($att->notes, 'كتاب الجامعة')))) {
                    $existingFiles['file_uni_request'] = $att->file_path;
                } elseif ($att->attachment_type_id == 8 || ($att->notes && (str_contains($att->notes, 'شهادة الدكتوراه') || str_contains($att->notes, 'مصدقة الدكتوراه')))) {
                    $existingFiles['file_phd_cert'] = $att->file_path;
                } elseif ($att->attachment_type_id == 9 || ($att->notes && (str_contains($att->notes, 'بيان وضع') || str_contains($att->notes, 'بطاقة ذاتية')))) {
                    $existingFiles['file_service_statement'] = $att->file_path;
                } elseif ($att->attachment_type_id == 10 || ($att->notes && (str_contains($att->notes, 'إيصال') || str_contains($att->notes, '125,000') || str_contains($att->notes, 'رسم تعادل')))) {
                    $existingFiles['file_payment'] = $att->file_path;
                } elseif ($att->attachment_type_id == 11 || ($att->notes && (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'الهوية الشخصية')))) {
                    $existingFiles['file_id_card'] = $att->file_path;
                } elseif ($att->attachment_type_id == 12 || ($att->notes && (str_contains($att->notes, 'شهادة الماجستير') || str_contains($att->notes, 'مصدقة الماجستير')))) {
                    $existingFiles['file_ma_cert'] = $att->file_path;
                } elseif ($att->notes && str_contains($att->notes, 'أخرى')) {
                    $existingFiles['file_other_attachments'] = $att->file_path;
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
                <li class="breadcrumb-item active text-muted" aria-current="page">معاملة السماح بالتدريس</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid fa-chalkboard-user me-2" style="color: var(--heritage-gold);"></i> معاملة السماح بالتدريس (أعضاء الهيئة التدريسية)
                </h3>
                <p class="body-md text-muted mb-0">معاملة مخصصة حصراً للسادة أعضاء الهيئة التدريسية القائمين على رأس عملهم في الجامعات الحكومية السورية لطلب السماح بالتدريس في الجامعات الخاصة.</p>
            </div>
            @if($draft)
                <span class="badge px-3 py-2 border fs-7 fw-bold" style="background-color: #FAF6EE; color: #8A651E; border-color: #D9C394 !important;">
                    <i class="fa-solid fa-file-pen me-1"></i> تعديل مسودة طلب: #{{ $draft->application_no }}
                </span>
            @endif
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

        <!-- Multi-Step Progress Indicators (5 STEPS TOTAL) -->
        <div class="wizard-steps" id="wizard-steps-container">
            <div class="wizard-progress" id="wizard-progress-bar" style="width: 0%;"></div>
            
            <div class="wizard-step active" data-step="1">
                <div class="wizard-icon">1</div>
                <span class="wizard-label d-none d-md-inline">الشخصية والجامعة</span>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="wizard-icon">2</div>
                <span class="wizard-label d-none d-md-inline">الجامعة الحكومية</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-icon">3</div>
                <span class="wizard-label d-none d-md-inline">الشهادات العلمية</span>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="wizard-icon">4</div>
                <span class="wizard-label d-none d-md-inline">المرفقات</span>
            </div>
            <div class="wizard-step" data-step="5">
                <div class="wizard-icon">5</div>
                <span class="wizard-label d-none d-md-inline">المراجعة</span>
            </div>
        </div>

        <!-- Form Tag -->
        <form action="{{ route('university.apply.faculty_permission.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">

            {{-- =========================================================================
                 STEP 1: PERSONAL DETAILS & UNIVERSITY EVALUATION REQUEST
            ========================================================================= --}}
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب طلب التقييم الصادر عن الجامعة
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم المرشح الكامل *</label>
                        <input type="text" name="full_name" id="input-fullName" class="form-control academic-input" placeholder="الاسم والنسبة" value="{{ old('full_name', optional($candidate)->full_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأب *</label>
                        <input type="text" name="father_name" id="input-fatherName" class="form-control academic-input" placeholder="اسم الأب" value="{{ old('father_name', optional($candidate)->father_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأم ونسبتها *</label>
                        <input type="text" name="mother_name" id="input-motherName" class="form-control academic-input" placeholder="اسم ونسبة الأم" value="{{ old('mother_name', optional($candidate)->mother_name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنسية *</label>
                        <select name="nationality_id" id="input-nationality" class="form-select academic-input" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('nationality_id', optional($candidate)->nationality_id ?? 1) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_syrian" id="input-isSyrian" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الرقم الوطني *</label>
                        <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="الرقم الوطني المكون من 11 خانة" value="{{ old('national_id', optional($candidate)->national_id) }}" maxlength="11" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الميلاد *</label>
                        <input type="date" name="dob" id="input-dob" class="form-control academic-input" value="{{ old('dob', optional($candidate)->dob ? \Carbon\Carbon::parse($candidate->dob)->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنس *</label>
                        <select name="gender" id="input-gender" class="form-select academic-input" required>
                            <option value="ذكر" {{ old('gender', optional($candidate)->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                            <option value="أنثى" {{ old('gender', optional($candidate)->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الهاتف المحمول *</label>
                        <input type="text" name="mobile" id="input-mobile" class="form-control academic-input" placeholder="09xxxxxxxx" maxlength="10" value="{{ old('mobile', optional($candidate)->mobile) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الهاتف الأرضي</label>
                        <input type="text" name="phone" id="input-phone" class="form-control academic-input" placeholder="011xxxxxxx" maxlength="10" value="{{ old('phone', optional($candidate)->phone) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">البريد الإلكتروني *</label>
                        <input type="email" name="email" id="input-email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', optional($candidate)->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">عنوان الإقامة الحالي بالتفصيل *</label>
                        <input type="text" name="address" id="input-address" class="form-control academic-input" placeholder="المحافظة - المدينة - الشارع - البناء" value="{{ old('address', optional($candidate)->address) }}" required>
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
                                    <input type="text" name="req_no" id="input-reqNo" class="form-control academic-input" placeholder="أدخل رقم كتاب الجامعة" value="{{ old('req_no', optional($draft)->new_uni_request_no) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ كتاب طلب التقييم الصادر عن الجامعة *</label>
                                    <input type="date" name="req_date" id="input-reqDate" class="form-control academic-input" value="{{ old('req_date', optional($draft)->new_uni_request_date ? \Carbon\Carbon::parse($draft->new_uni_request_date)->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 2: PUBLIC UNIVERSITY EMPLOYMENT & ACADEMIC RANK
            ========================================================================= --}}
            <div class="form-section" id="step-2" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات التعيين والصفة في الجامعة الحكومية السورية
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الجامعة الحكومية السورية التابع لها *</label>
                        <select name="gov_university_id" id="input-govUniId" class="form-select academic-input" onchange="toggleGovUniOther()" required>
                            <option value="">-- اختر الجامعة الحكومية --</option>
                            @foreach($govUniversities as $gu)
                                <option value="{{ $gu->id }}" {{ old('gov_university_id', optional($govEd)->university_id) == $gu->id ? 'selected' : '' }}>{{ $gu->name }}</option>
                            @endforeach
                            <option value="other" {{ old('gov_university_other', optional($govEd)->university_other) ? 'selected' : '' }}>أخرى (جامعة حكومية سورية أخرى)</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="gov_uni_other_div" style="display: {{ old('gov_university_other', optional($govEd)->university_other) ? 'block' : 'none' }};">
                        <label class="form-label label-md fw-medium text-dark">اسم الجامعة الحكومية :</label>
                        <input type="text" name="gov_university_other" id="input-govUniOther" class="form-control academic-input" placeholder="أدخل اسم الجامعة الحكومية" value="{{ old('gov_university_other', optional($govEd)->university_other) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الرتبة / الصفة الأكاديمية بالجامعة الحكومية *</label>
                        <select name="academic_rank" id="input-academicRank" class="form-select academic-input" required>
                            <option value="">-- اختر الرتبة الأكاديمية --</option>
                            <option value="مدرس" {{ old('academic_rank', optional($govEd)->rank) == 'مدرس' ? 'selected' : '' }}>مدرس</option>
                            <option value="أستاذ مساعد" {{ old('academic_rank', optional($govEd)->rank) == 'أستاذ مساعد' ? 'selected' : '' }}>أستاذ مساعد</option>
                            <option value="أستاذ" {{ old('academic_rank', optional($govEd)->rank) == 'أستاذ' ? 'selected' : '' }}>أستاذ (بروفيسور)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية التابع لها بالجامعة الحكومية *</label>
                        <input type="text" name="gov_faculty" id="input-govFaculty" class="form-control academic-input" placeholder="مثال: كلية التربية / كلية الاقتصاد" value="{{ old('gov_faculty', optional($govEd)->faculty ?: optional($govEd)->general_specialization) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم التابع له بالجامعة الحكومية *</label>
                        <input type="text" name="gov_department" id="input-govDept" class="form-control academic-input" placeholder="مثال: قسم أصول التربية / قسم إدارة الأعمال" value="{{ old('gov_department', optional($govEd)->department ?: optional($govEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="alert alert-warning py-2.5 px-3 border-0 rounded d-flex align-items-center gap-2 mb-0" style="background-color: #FFFBEB; color: #B45309; font-size: 0.88rem;">
                            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                            <span><strong>ملاحظة هامة:</strong> يجب أن تكون بيانات الكلية والقسم مطابقة للبيان الصادر في البطاقة الذاتية أو بيان الوضع من الجامعة الحكومية.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 3: SCIENTIFIC DEGREES (DOCTORATE & OPTIONAL MASTER)
            ========================================================================= --}}
            <div class="form-section" id="step-3" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-graduation-cap fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: بيانات المؤهلات والشهادات العلمية
                </h5>

                <!-- Primary Qualification: PhD Degree -->
                <div class="card p-3.5 mb-4 shadow-sm border-0" style="background-color: var(--surface-container-low); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-container);">
                        <i class="fa-solid fa-certificate me-1.5" style="color: var(--heritage-gold);"></i> بيانات شهادة الدكتوراه (المؤهل الأساسي)
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">الجامعة المانحة لشهادة الدكتوراه *</label>
                            <select name="phd_university_id" id="input-phdUniId" class="form-select academic-input" onchange="togglePhdUniOther()" required>
                                <option value="">-- اختر الجامعة المانحة --</option>
                                @foreach($govUniversities as $gu)
                                    <option value="{{ $gu->id }}" {{ old('phd_university_id', optional($phdEd)->university_id) == $gu->id ? 'selected' : '' }}>{{ $gu->name }}</option>
                                @endforeach
                                <option value="other" {{ old('phd_university_other', optional($phdEd)->university_other) ? 'selected' : '' }}>جامعة أخرى (خارجية / معترف بها)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="phd_uni_other_div" style="display: {{ old('phd_university_other', optional($phdEd)->university_other) ? 'block' : 'none' }};">
                            <label class="form-label label-md fw-medium text-dark">اسم الجامعة المانحة لدرجة الدكتوراه :</label>
                            <input type="text" name="phd_university_other" id="input-phdUniOther" class="form-control academic-input" placeholder="أدخل اسم الجامعة المانحة" value="{{ old('phd_university_other', optional($phdEd)->university_other) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">تاريخ / سنة منح شهادة الدكتوراه *</label>
                            <input type="date" name="phd_grant_date" id="input-phdGrantDate" class="form-control academic-input" value="{{ old('phd_grant_date', optional($phdEd)->grant_date ? \Carbon\Carbon::parse($phdEd->grant_date)->format('Y-m-d') : '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">الكلية المانحة لشهادة الدكتوراه *</label>
                            <input type="text" name="phd_faculty" id="input-phdFaculty" class="form-control academic-input" placeholder="مثال: كلية التربية" value="{{ old('phd_faculty', optional($phdEd)->faculty ?: optional($phdEd)->general_specialization) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label label-md fw-medium text-dark">القسم / الاختصاص الدقيق لشهادة الدكتوراه *</label>
                            <input type="text" name="phd_department" id="input-phdDept" class="form-control academic-input" placeholder="مثال: أصول التربية" value="{{ old('phd_department', optional($phdEd)->department ?: optional($phdEd)->exact_specialization) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Optional Master's Degree -->
                <div class="card p-3.5 shadow-sm border-0" style="background-color: var(--surface-container-low); border-radius: 4px;">
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-2 mb-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="has_master" id="input-hasMaster" value="1" onchange="toggleMasterSection()" {{ old('has_master', $maEd ? '1' : '0') == '1' ? 'checked' : '' }} style="width: 2.2em; height: 1.2em; cursor: pointer;">
                        <label class="form-check-label fw-bold text-dark mb-0 label-md" for="input-hasMaster" style="cursor: pointer;">
                            <i class="fa-solid fa-scroll me-1" style="color: var(--heritage-gold);"></i> إضافة بيانات شهادة الماجستير (اختياري كوثيقة مسبقة)
                        </label>
                    </div>

                    <div id="master_section_div" style="display: {{ old('has_master', $maEd ? '1' : '0') == '1' ? 'block' : 'none' }};">
                        <div class="row g-3 pt-2 border-top">
                            <div class="col-md-6">
                                <label class="form-label label-md fw-medium text-dark">الجامعة المانحة لشهادة الماجستير :</label>
                                <select name="ma_university_id" id="input-maUniId" class="form-select academic-input" onchange="toggleMaUniOther()">
                                    <option value="">-- اختر الجامعة المانحة --</option>
                                    @foreach($govUniversities as $gu)
                                        <option value="{{ $gu->id }}" {{ old('ma_university_id', optional($maEd)->university_id) == $gu->id ? 'selected' : '' }}>{{ $gu->name }}</option>
                                    @endforeach
                                    <option value="other" {{ old('ma_university_other', optional($maEd)->university_other) ? 'selected' : '' }}>جامعة أخرى</option>
                                </select>
                            </div>

                            <div class="col-md-6" id="ma_uni_other_div" style="display: {{ old('ma_university_other', optional($maEd)->university_other) ? 'block' : 'none' }};">
                                <label class="form-label label-md fw-medium text-dark">اسم الجامعة المانحة للماجستير :</label>
                                <input type="text" name="ma_university_other" id="input-maUniOther" class="form-control academic-input" placeholder="أدخل اسم الجامعة المانحة" value="{{ old('ma_university_other', optional($maEd)->university_other) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label label-md fw-medium text-dark">تاريخ / سنة منح الماجستير :</label>
                                <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" value="{{ old('ma_grant_date', optional($maEd)->grant_date ? \Carbon\Carbon::parse($maEd->grant_date)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label label-md fw-medium text-dark">الكلية المانحة للماجستير :</label>
                                <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="مثال: كلية التربية" value="{{ old('ma_faculty', optional($maEd)->faculty ?: optional($maEd)->general_specialization) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label label-md fw-medium text-dark">القسم / الاختصاص الدقيق للماجستير :</label>
                                <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="مثال: أصول التربية" value="{{ old('ma_department', optional($maEd)->department ?: optional($maEd)->exact_specialization) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 4: REQUIRED ATTACHMENTS (OFFICIAL CHECKLIST)
            ========================================================================= --}}
            <div class="form-section" id="step-4" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center justify-content-between" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <span class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-folder-open fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: رفع الوثائق والمرفقات الرسمية المطلوبة
                    </span>
                    <span class="badge px-2.5 py-1.5 fs-8" style="background-color: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;">صيغة PDF فقط - الحد الأقصى 2MB</span>
                </h5>

                <div class="row g-4">
                    
                    <!-- 1. طلب التقويم / كتاب ترشيح الجامعة الخاصة -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_uni_request" id="input-fileUniRequest" class="form-control academic-input" accept=".pdf" {{ empty($existingFiles['file_uni_request']) ? 'required' : '' }}>
                        @if(!empty($existingFiles['file_uni_request']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_uni_request']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 2. مصدقة شهادة الدكتوراه -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            نسخة مصدقة أصولاً عن شهادة الدكتوراه <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_phd_cert" id="input-filePhdCert" class="form-control academic-input" accept=".pdf" {{ empty($existingFiles['file_phd_cert']) ? 'required' : '' }}>
                        @if(!empty($existingFiles['file_phd_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_phd_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 3. بيان وضع أو بطاقة ذاتية من الجامعة الحكومية -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            بيان وضع أو بطاقة ذاتية من الجامعة الحكومية <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_service_statement" id="input-fileServiceStatement" class="form-control academic-input" accept=".pdf" {{ empty($existingFiles['file_service_statement']) ? 'required' : '' }}>
                        @if(!empty($existingFiles['file_service_statement']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_service_statement']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 4. إيصال تسديد رسم التعادل -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            إيصال تسديد رسم التعادل (125,000 ل.س) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_payment" id="input-filePayment" class="form-control academic-input" accept=".pdf" {{ empty($existingFiles['file_payment']) ? 'required' : '' }}>
                        @if(!empty($existingFiles['file_payment']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_payment']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 5. صورة عن الهوية الشخصية -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            صورة عن الهوية الشخصية (الوجهين) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_id_card" id="input-fileIdCard" class="form-control academic-input" accept=".pdf" {{ empty($existingFiles['file_id_card']) ? 'required' : '' }}>
                        @if(!empty($existingFiles['file_id_card']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_id_card']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 6. نسخة مصدقة عن شهادة الماجستير (اختياري) -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">
                            نسخة مصدقة عن شهادة الماجستير (اختياري)
                        </label>
                        <input type="file" name="file_ma_cert" id="input-fileMaCert" class="form-control academic-input" accept=".pdf">
                        @if(!empty($existingFiles['file_ma_cert']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_ma_cert']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 7. مرفقات إضافية (اختياري) -->
                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">
                            وثائق داعمة أو قرارات سابقة إضافية (اختياري)
                        </label>
                        <input type="file" name="file_other_attachments" id="input-fileOtherAttachments" class="form-control academic-input" accept=".pdf">
                        @if(!empty($existingFiles['file_other_attachments']))
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مرفوع مسبقاً</span>
                                <a href="{{ asset('storage/' . $existingFiles['file_other_attachments']) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-7 fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> استعراض الـ PDF الحالي
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 5: REVIEW & FINAL SUBMISSION
            ========================================================================= --}}
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-clipboard-check fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: مراجعة وتدقيق بيانات الطلب قبل الإرسال النهائي
                </h5>
                
                <p class="label-md text-muted mb-4">يرجى مراجعة كافة البيانات المدخلة قبل النقر على زر إنهاء الإرسال. يمكنك التعديل والرجوع لأي خطوة سابقة.</p>

                <div class="card p-4 shadow-sm border-0 mb-4" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
                    <div class="text-center pb-3 mb-4 border-bottom" style="border-bottom-color: var(--outline-variant) !important;">
                        <h4 class="fw-bold mb-1" style="color: var(--primary-container);">تقرير طلب سماح بالتدريس لعضو هيئة تدريسية</h4>
                        <div class="text-muted fw-bold label-sm">مجلس التعليم العالي - وزارة التعليم العالي والبحث العلمي</div>
                    </div>

                    <div class="row g-4">
                        
                        <!-- Group 1: Personal Info -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-user me-1" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية لعضو الهيئة التدريسية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4"><strong>الاسم الكامل:</strong> <span id="preview-fullName">---</span></div>
                                <div class="col-md-4"><strong>اسم الأب:</strong> <span id="preview-fatherName">---</span></div>
                                <div class="col-md-4"><strong>اسم الأم:</strong> <span id="preview-motherName">---</span></div>
                                <div class="col-md-4"><strong>الجنسية:</strong> <span id="preview-nationality">---</span></div>
                                <div class="col-md-4"><strong>الرقم الوطني:</strong> <span id="preview-nationalId">---</span></div>
                                <div class="col-md-4"><strong>تاريخ الميلاد:</strong> <span id="preview-dob">---</span></div>
                                <div class="col-md-4"><strong>الجنس:</strong> <span id="preview-gender">---</span></div>
                                <div class="col-md-4"><strong>رقم الموبايل:</strong> <span id="preview-mobile">---</span></div>
                                <div class="col-md-4"><strong>الهاتف الأرضي:</strong> <span id="preview-phone">---</span></div>
                                <div class="col-md-6"><strong>البريد الإلكتروني:</strong> <span id="preview-email">---</span></div>
                                <div class="col-md-6"><strong>مكان الإقامة والعنوان:</strong> <span id="preview-address">---</span></div>
                            </div>
                        </div>

                        <!-- Group 2: University Evaluation Request -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 2. كتاب طلب التقييم الصادر عن الجامعة الخاصة:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(1)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo">---</span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate">---</span></div>
                            </div>
                        </div>

                        <!-- Group 3: Public University Info -->
                        <div class="col-12 border-bottom pb-3" style="border-bottom-color: var(--outline-variant) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> 3. بيانات الجامعة الحكومية والصفة الوظيفية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(2)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الجامعة الحكومية:</strong> <span id="preview-govUni">---</span></div>
                                <div class="col-md-6"><strong>الرتبة الأكاديمية:</strong> <span id="preview-academicRank">---</span></div>
                                <div class="col-md-6"><strong>الكلية:</strong> <span id="preview-govFaculty">---</span></div>
                                <div class="col-md-6"><strong>القسم / الاختصاص:</strong> <span id="preview-govDept">---</span></div>
                            </div>
                        </div>

                        <!-- Group 4: Scientific Degrees -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. الشهادات والمؤهلات العلمية:</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 fs-8 fw-bold" onclick="goToStep(3)"><i class="fa-solid fa-pen-to-square me-1"></i> تعديل</button>
                            </div>
                            <div class="card p-3 border-0 mb-3" style="background-color: #ffffff;">
                                <strong class="mb-2 d-block text-primary"><i class="fa-solid fa-certificate me-1"></i> درجة الدكتوراه (الأساسية):</strong>
                                <div class="row g-2">
                                    <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-phdUni">---</span></div>
                                    <div class="col-md-6"><strong>سنة/تاريخ المنح:</strong> <span id="preview-phdGrantDate">---</span></div>
                                    <div class="col-md-6"><strong>الكلية المانحة:</strong> <span id="preview-phdFaculty">---</span></div>
                                    <div class="col-md-6"><strong>القسم / الاختصاص الدقيق:</strong> <span id="preview-phdDept">---</span></div>
                                </div>
                            </div>

                            <div class="card p-3 border-0" id="preview-master-block" style="background-color: #ffffff; display: none;">
                                <strong class="mb-2 d-block text-secondary"><i class="fa-solid fa-scroll me-1"></i> درجة الماجستير (الداعمة):</strong>
                                <div class="row g-2">
                                    <div class="col-md-6"><strong>الجامعة المانحة:</strong> <span id="preview-maUni">---</span></div>
                                    <div class="col-md-6"><strong>سنة/تاريخ المنح:</strong> <span id="preview-maGrantDate">---</span></div>
                                    <div class="col-md-6"><strong>الكلية المانحة:</strong> <span id="preview-maFaculty">---</span></div>
                                    <div class="col-md-6"><strong>القسم / الاختصاص:</strong> <span id="preview-maDept">---</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final check warning confirmation -->
                <div class="form-check form-switch mt-4 p-3 border rounded d-flex align-items-center gap-3" style="background-color: var(--surface-container-low); border-color: var(--outline-variant) !important;">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required style="width: 2.2em; height: 1.2em;">
                    <label class="form-check-label fw-bold text-dark mb-0 label-md" for="chkConfirm">
                        نصادق نحن في إدارة الجامعة على صحة كافة البيانات والوثائق المرفقة أعلاه، وأن المرشح عضو هيئة تدريسية قائم على رأس عمله في الجامعة الحكومية المذكورة.
                    </label>
                </div>
            </div>

            {{-- =========================================================================
                 BUTTONS NAVIGATION FOOTER
            ========================================================================= --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5 pt-3 border-top" style="border-top-color: var(--outline-variant) !important;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-navy px-4 py-2" id="btn-prev" onclick="changeStep(-1)" style="display: none;">
                        <i class="fa-solid fa-arrow-right me-1"></i> السابق
                    </button>
                    <div id="spacer-prev"></div>

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

<script>
    let currentStep = 1;
    const totalSteps = 5;

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
        }

        // Update progress bar
        const progressPct = ((step - 1) / (totalSteps - 1)) * 100;
        document.getElementById('wizard-progress-bar').style.width = progressPct + '%';

        // Update step indicators
        const stepEls = document.querySelectorAll('.wizard-step');
        stepEls.forEach(el => {
            const stepNum = parseInt(el.getAttribute('data-step'));
            el.classList.remove('active', 'completed');
            if (stepNum === step) {
                el.classList.add('active');
            } else if (stepNum < step) {
                el.classList.add('completed');
            }
        });

        // Toggle buttons
        const btnPrev = document.getElementById('btn-prev');
        const spacerPrev = document.getElementById('spacer-prev');
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');

        if (step > 1) {
            btnPrev.style.display = 'inline-block';
            if (spacerPrev) spacerPrev.style.display = 'none';
        } else {
            btnPrev.style.display = 'none';
            if (spacerPrev) spacerPrev.style.display = 'block';
        }

        if (step === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-block';
            populateReview();
        } else {
            btnNext.style.display = 'inline-block';
            btnSubmit.style.display = 'none';
        }

        window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function changeStep(delta) {
        const newStep = currentStep + delta;
        if (delta > 0) {
            if (!validateCurrentStep(currentStep)) {
                return;
            }
        }
        if (newStep >= 1 && newStep <= totalSteps) {
            showStep(newStep);
        }
    }

    function goToStep(step) {
        if (step >= 1 && step <= totalSteps) {
            showStep(step);
        }
    }

    function validateCurrentStep(step) {
        const section = document.getElementById('step-' + step);
        if (!section) return true;

        const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        let firstInvalid = null;

        inputs.forEach(input => {
            // Check if file input has existing uploaded badge
            if (input.type === 'file') {
                const parentDiv = input.closest('.border.rounded');
                if (parentDiv && parentDiv.querySelector('.badge.bg-success-subtle')) {
                    input.setCustomValidity('');
                    return;
                }
            }

            if (!input.checkValidity()) {
                isValid = false;
                if (!firstInvalid) firstInvalid = input;
            }
        });

        if (!isValid && firstInvalid) {
            firstInvalid.reportValidity();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    function toggleGovUniOther() {
        const val = document.getElementById('input-govUniId').value;
        const otherDiv = document.getElementById('gov_uni_other_div');
        const otherInput = document.getElementById('input-govUniOther');
        if (val === 'other') {
            otherDiv.style.display = 'block';
            otherInput.required = true;
        } else {
            otherDiv.style.display = 'none';
            otherInput.required = false;
        }
    }

    function togglePhdUniOther() {
        const val = document.getElementById('input-phdUniId').value;
        const otherDiv = document.getElementById('phd_uni_other_div');
        const otherInput = document.getElementById('input-phdUniOther');
        if (val === 'other') {
            otherDiv.style.display = 'block';
            otherInput.required = true;
        } else {
            otherDiv.style.display = 'none';
            otherInput.required = false;
        }
    }

    function toggleMaUniOther() {
        const val = document.getElementById('input-maUniId').value;
        const otherDiv = document.getElementById('ma_uni_other_div');
        if (val === 'other') {
            otherDiv.style.display = 'block';
        } else {
            otherDiv.style.display = 'none';
        }
    }

    function toggleMasterSection() {
        const isChecked = document.getElementById('input-hasMaster').checked;
        document.getElementById('master_section_div').style.display = isChecked ? 'block' : 'none';
    }

    function populateReview() {
        const form = document.getElementById('wizard-form');
        
        // Step 1: Personal & Uni Request
        document.getElementById('preview-fullName').textContent = form.full_name.value || '---';
        document.getElementById('preview-fatherName').textContent = form.father_name.value || '---';
        document.getElementById('preview-motherName').textContent = form.mother_name.value || '---';
        const natSelect = form.nationality_id;
        document.getElementById('preview-nationality').textContent = natSelect.options[natSelect.selectedIndex]?.text || '---';
        document.getElementById('preview-nationalId').textContent = form.national_id.value || '---';
        document.getElementById('preview-dob').textContent = form.dob.value || '---';
        document.getElementById('preview-gender').textContent = form.gender.value || '---';
        document.getElementById('preview-mobile').textContent = form.mobile.value || '---';
        document.getElementById('preview-phone').textContent = form.phone.value || '---';
        document.getElementById('preview-email').textContent = form.email.value || '---';
        document.getElementById('preview-address').textContent = form.address.value || '---';

        document.getElementById('preview-reqNo').textContent = form.req_no.value || '---';
        document.getElementById('preview-reqDate').textContent = form.req_date.value || '---';

        // Step 2: Gov Uni
        const govUniSelect = form.gov_university_id;
        const govUniText = govUniSelect.value === 'other' ? form.gov_university_other.value : (govUniSelect.options[govUniSelect.selectedIndex]?.text || '---');
        document.getElementById('preview-govUni').textContent = govUniText || '---';
        document.getElementById('preview-academicRank').textContent = form.academic_rank.value || '---';
        document.getElementById('preview-govFaculty').textContent = form.gov_faculty.value || '---';
        document.getElementById('preview-govDept').textContent = form.gov_department.value || '---';

        // Step 3: PhD & Master
        const phdUniSelect = form.phd_university_id;
        const phdUniText = phdUniSelect.value === 'other' ? form.phd_university_other.value : (phdUniSelect.options[phdUniSelect.selectedIndex]?.text || '---');
        document.getElementById('preview-phdUni').textContent = phdUniText || '---';
        document.getElementById('preview-phdGrantDate').textContent = form.phd_grant_date.value || '---';
        document.getElementById('preview-phdFaculty').textContent = form.phd_faculty.value || '---';
        document.getElementById('preview-phdDept').textContent = form.phd_department.value || '---';

        if (form.has_master && form.has_master.checked) {
            document.getElementById('preview-master-block').style.display = 'block';
            const maUniSelect = form.ma_university_id;
            const maUniText = maUniSelect.value === 'other' ? form.ma_university_other.value : (maUniSelect.options[maUniSelect.selectedIndex]?.text || '---');
            document.getElementById('preview-maUni').textContent = maUniText || '---';
            document.getElementById('preview-maGrantDate').textContent = form.ma_grant_date.value || '---';
            document.getElementById('preview-maFaculty').textContent = form.ma_faculty.value || '---';
            document.getElementById('preview-maDept').textContent = form.ma_department.value || '---';
        } else {
            document.getElementById('preview-master-block').style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);

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
                const submitter = e.submitter;
                if (submitter && submitter.value === 'save_draft') {
                    return true;
                }

                // On final submit: remove required from files that have uploaded badges
                document.querySelectorAll('input[type="file"]').forEach(function(input) {
                    const parentDiv = input.closest('.border.rounded') || input.closest('.col-md-6') || input.parentElement;
                    if (parentDiv && parentDiv.querySelector('.badge.bg-success-subtle')) {
                        input.removeAttribute('required');
                    }
                });

                // Check final step confirmation
                const chkConfirm = document.getElementById('chkConfirm');
                if (chkConfirm && !chkConfirm.checked) {
                    e.preventDefault();
                    goToStep(5);
                    chkConfirm.focus();
                    chkConfirm.setCustomValidity('يرجى المصادقة على الإقرار بصحة البيانات للمتابعة.');
                    chkConfirm.reportValidity();
                    return false;
                }
            });
        }
    });
</script>
@endsection

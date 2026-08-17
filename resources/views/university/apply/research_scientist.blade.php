@extends('layouts.university')

@section('title', 'طلب اعتماد باحث في مراكز البحوث للتدريس')

@section('content')

@php
    $candidate = $draft ? $draft->candidate : null;
    $rcEd = $draft ? $draft->educations->first(function($e) { 
        return $e->thesis_title === 'باحث في مركز بحوث' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'بحوث')); 
    }) : null;
    $phdEd = $draft ? $draft->educations->first(function($e) { 
        return str_contains($e->thesis_title, 'الدكتوراه') || (optional($e->level)->name && str_contains(optional($e->level)->name, 'دكتوراه')); 
    }) : null;

    $existingFiles = [];
    if ($draft) {
        foreach ($draft->educations as $ed) {
            foreach ($ed->attachments as $att) {
                if ($att->attachment_type_id == 7 || ($att->notes && (str_contains($att->notes, 'طلب تقويم') || str_contains($att->notes, 'كتاب ترشيح') || str_contains($att->notes, 'كتاب الجامعة')))) {
                    $existingFiles['file_uni_request'] = $att->file_path;
                } elseif ($att->attachment_type_id == 8 || ($att->notes && (str_contains($att->notes, 'شهادة الدكتوراه') || str_contains($att->notes, 'مصدقة الدكتوراه')))) {
                    $existingFiles['file_phd_cert'] = $att->file_path;
                } elseif ($att->attachment_type_id == 9 || ($att->notes && (str_contains($att->notes, 'بيان وضع') || str_contains($att->notes, 'بحوث')))) {
                    $existingFiles['file_service_statement'] = $att->file_path;
                } elseif ($att->attachment_type_id == 10 || ($att->notes && (str_contains($att->notes, 'إيصال') || str_contains($att->notes, 'رسم تعادل')))) {
                    $existingFiles['file_payment'] = $att->file_path;
                } elseif ($att->attachment_type_id == 11 || ($att->notes && (str_contains($att->notes, 'هوية') || str_contains($att->notes, 'الهوية الشخصية')))) {
                    $existingFiles['file_id_card'] = $att->file_path;
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
                <li class="breadcrumb-item active text-muted" aria-current="page">باحث في مراكز البحوث</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem;">
                    <i class="fa-solid fa-microscope me-2" style="color: var(--heritage-gold);"></i> طلب اعتماد باحث في مراكز البحوث للتدريس
                </h3>
                <p class="body-md text-muted mb-0">معاملة مخصصة لتقويم واعتماد شهادات الدكتوراه للباحثين العاملين في مراكز البحوث السورية لطلب التدريس في الجامعات الخاصة.</p>
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
                <span class="wizard-label d-none d-md-inline">مركز البحوث</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-icon">3</div>
                <span class="wizard-label d-none d-md-inline">شهادة الدكتوراه</span>
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
        <form action="{{ route('university.apply.research_scientist.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ optional($draft)->id }}">

            {{-- =========================================================================
                 STEP 1: PERSONAL DETAILS & PRIVATE UNIVERSITY REQUEST
            ========================================================================= --}}
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية وبيانات كتاب الجامعة الخاصة
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم المرشح *</label>
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
                                <option value="{{ $c->id }}" {{ old('nationality_id', optional($candidate)->nationality_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_syrian" id="input-isSyrian" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الرقم الوطني *</label>
                        <div class="input-group">
                            @php
                                $draftNatId = optional($candidate)->national_id;
                                if ($draftNatId && str_starts_with($draftNatId, 'TMP-')) {
                                    $draftNatId = '';
                                }
                            @endphp
                            <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="11 خانة" value="{{ old('national_id', $draftNatId) }}" maxlength="11" required>
                            <button class="btn btn-outline-secondary px-2.5" type="button" id="btn-lookup-candidate" title="استرجاع البيانات المحفوظة تلقائياً">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
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
                        <input type="email" name="email" id="input-email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', optional($candidate)->email ?: (Auth::user()->university->email ?? Auth::user()->email)) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">عنوان الإقامة الحالي *</label>
                        <input type="text" name="address" id="input-address" class="form-control academic-input" placeholder="المحافظة - المدينة - الشارع" value="{{ old('address', optional($candidate)->address) }}" required>
                    </div>

                    <!-- PRIVATE UNIVERSITY REQUEST DETAILS -->
                    <div class="col-12 mt-4">
                        <div class="p-3.5 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-right: 4px solid var(--heritage-gold);">
                            <h6 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                                <i class="fa-solid fa-building-columns me-1.5" style="color: var(--heritage-gold);"></i> بيانات كتاب الجامعة الخاصة ومكان التدريس المطلوب
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم كتاب الجامعة الخاصة *</label>
                                    <input type="text" name="req_no" id="input-reqNo" class="form-control academic-input" placeholder="مثال: 89 / ص" value="{{ old('req_no', optional($draft)->new_uni_request_no) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">تاريخ كتاب الجامعة الخاصة *</label>
                                    <input type="date" name="req_date" id="input-reqDate" class="form-control academic-input" value="{{ old('req_date', optional($draft)->new_uni_request_date ? \Carbon\Carbon::parse($draft->new_uni_request_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">الكلية المرشح للتدريس فيها *</label>
                                    <input type="text" name="work_faculty" id="input-workFaculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة الميكانيكية والكهربائية" value="{{ old('work_faculty', optional($draft)->work_faculty) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">القسم / الاختصاص المرشح لتدريسه *</label>
                                    <input type="text" name="work_department" id="input-workDepartment" class="form-control academic-input" placeholder="مثال: هندسة الطاقة والحرارة" value="{{ old('work_department', optional($draft)->work_department) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة
                    </button>
                    <button type="button" class="btn btn-solid-navy px-4 py-2 next-step" data-next="2">
                        التالي: بيانات مركز البحوث <i class="fa-solid fa-arrow-left ms-2"></i>
                    </button>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 2: RESEARCH CENTER EMPLOYMENT DETAILS
            ========================================================================= --}}
            <div class="form-section d-none" id="step-2">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-microscope fs-5" style="color: var(--heritage-gold);"></i> الخطوة 2: بيانات التعيين والعمل لدى مركز البحوث
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">اسم مركز البحوث التابع له الباحث *</label>
                        <input type="text" name="rc_center_name" id="input-rcCenterName" class="form-control academic-input" placeholder="مثال: مركز الدراسات والبحوث العلمية" value="{{ old('rc_center_name', optional($rcEd)->faculty ?? 'مركز الدراسات والبحوث العلمية') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم / المعهد / الدائرة في مركز البحوث *</label>
                        <input type="text" name="rc_department" id="input-rcDepartment" class="form-control academic-input" placeholder="مثال: معهد الطاقة والبحوث الفيزيائية" value="{{ old('rc_department', optional($rcEd)->department) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الصفة الوظيفية / الرتبة البحثية *</label>
                        <select name="rc_rank" id="input-rcRank" class="form-select academic-input" required>
                            <option value="باحث" {{ old('rc_rank', optional($rcEd)->rank) == 'باحث' ? 'selected' : '' }}>باحث</option>
                            <option value="رئيس باحثين" {{ old('rc_rank', optional($rcEd)->rank) == 'رئيس باحثين' ? 'selected' : '' }}>رئيس باحثين</option>
                            <option value="باحث رئيسي" {{ old('rc_rank', optional($rcEd)->rank) == 'باحث رئيسي' ? 'selected' : '' }}>باحث رئيسي</option>
                            <option value="باحث مساعد" {{ old('rc_rank', optional($rcEd)->rank) == 'باحث مساعد' ? 'selected' : '' }}>باحث مساعد</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-outline-navy px-4 py-2 prev-step" data-prev="1">
                        <i class="fa-solid fa-arrow-right me-2"></i> السابق: البيانات الشخصية
                    </button>
                    <div>
                        <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary btn-sm px-3 me-2">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة
                        </button>
                        <button type="button" class="btn btn-solid-navy px-4 py-2 next-step" data-next="3">
                            التالي: شهادة الدكتوراه <i class="fa-solid fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 3: DOCTORATE DEGREE DETAILS
            ========================================================================= --}}
            <div class="form-section d-none" id="step-3">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-award fs-5" style="color: var(--heritage-gold);"></i> الخطوة 3: بيانات شهادة الدكتوراه (المؤهل العلمي الأساسي)
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الدولة المانحة لشهادة الدكتوراه *</label>
                        <select name="phd_country_id" id="input-phdCountry" class="form-select academic-input" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('phd_country_id', optional($phdEd)->country_id ?? $syriaId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الجامعة / المؤسسة الأكاديمية المانحة *</label>
                        <select name="phd_university_id" id="input-phdUniId" class="form-select academic-input mb-1">
                            <option value="">-- اختر الجامعة إن كانت سورية --</option>
                            @foreach($universities as $u)
                                <option value="{{ $u->id }}" {{ old('phd_university_id', optional($phdEd)->university_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="phd_university_other" id="input-phdUniOther" class="form-control academic-input mt-1" placeholder="أو اكتب اسم الجامعة/المعهد المانح بالخارج (مثلاً: المدرسة الوطنية العليا للمناجم في نانت)" value="{{ old('phd_university_other', optional($phdEd)->university_other) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية / المعهد المانح لدرجة الدكتوراه *</label>
                        <input type="text" name="phd_faculty" id="input-phdFaculty" class="form-control academic-input" placeholder="مثال: هندسة الطاقة والحرارة والاحتراق" value="{{ old('phd_faculty', optional($phdEd)->faculty ?? optional($phdEd)->general_specialization) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الاختصاص الدقيق لدرجة الدكتوراه *</label>
                        <input type="text" name="phd_department" id="input-phdDepartment" class="form-control academic-input" placeholder="مثال: الطاقة والحرارة والاحتراق" value="{{ old('phd_department', optional($phdEd)->department ?? optional($phdEd)->exact_specialization) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">سنة / تاريخ منح درجة الدكتوراه *</label>
                        <input type="date" name="phd_grant_date" id="input-phdGrantDate" class="form-control academic-input" value="{{ old('phd_grant_date', optional($phdEd)->grant_date ? \Carbon\Carbon::parse($phdEd->grant_date)->format('Y-m-d') : '') }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-outline-navy px-4 py-2 prev-step" data-prev="2">
                        <i class="fa-solid fa-arrow-right me-2"></i> السابق: بيانات مركز البحوث
                    </button>
                    <div>
                        <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary btn-sm px-3 me-2">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة
                        </button>
                        <button type="button" class="btn btn-solid-navy px-4 py-2 next-step" data-next="4">
                            التالي: المرفقات والوثائق <i class="fa-solid fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 4: REQUIRED ATTACHMENTS
            ========================================================================= --}}
            <div class="form-section d-none" id="step-4">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-folder-open fs-5" style="color: var(--heritage-gold);"></i> الخطوة 4: المرفقات والوثائق الثبوتية الإلزامية (PDF فقط - بحد أقصى 2MB للملف)
                </h5>

                <div class="row g-4">
                    <!-- 1. Private University Request Letter -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>1. طلب التقويم / كتاب ترشيح الجامعة الخاصة *</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">إلزامي</span>
                            </label>
                            <input type="file" name="file_uni_request" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_uni_request']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. PhD Certificate -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>2. مصدقة / شهادة الدكتوراه *</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">إلزامي</span>
                            </label>
                            <input type="file" name="file_phd_cert" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_phd_cert']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Service Statement from Research Center -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>3. بيان الوضع الوظيفي من مركز البحوث *</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">إلزامي</span>
                            </label>
                            <input type="file" name="file_service_statement" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_service_statement']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Payment Receipt -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>4. إيصال تسديد رسم التعادل *</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">إلزامي</span>
                            </label>
                            <input type="file" name="file_payment" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_payment']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. National ID Card -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>5. صورة عن الهوية الشخصية *</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">إلزامي</span>
                            </label>
                            <input type="file" name="file_id_card" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_id_card']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. Other Attachments -->
                    <div class="col-md-6">
                        <div class="attachment-upload-card p-3 rounded" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                <span>6. مرفقات ووثائق داعمة أخرى</span>
                                <span class="badge bg-secondary-subtle text-secondary fs-8">اختياري</span>
                            </label>
                            <input type="file" name="file_other_attachments" class="form-control form-control-sm academic-input" accept="application/pdf">
                            @if(!empty($existingFiles['file_other_attachments']))
                                <div class="mt-1.5 fs-8 text-success"><i class="fa-solid fa-check-circle me-1"></i> يوجد ملف مرفق مسبقاً</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-outline-navy px-4 py-2 prev-step" data-prev="3">
                        <i class="fa-solid fa-arrow-right me-2"></i> السابق: شهادة الدكتوراه
                    </button>
                    <div>
                        <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary btn-sm px-3 me-2">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة
                        </button>
                        <button type="button" class="btn btn-solid-navy px-4 py-2 next-step" data-next="5" id="btn-to-review">
                            التالي: مراجعة الطلب وإرساله <i class="fa-solid fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 STEP 5: REVIEW & FINAL SUBMISSION
            ========================================================================= --}}
            <div class="form-section d-none" id="step-5">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-clipboard-check fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: مراجعة البيانات والإرسال النهائي
                </h5>

                <div class="alert alert-info py-2 px-3 mb-4 rounded border-0" style="background-color: #EFF6FF; color: #1D4ED8; border-right: 4px solid #3B82F6 !important;">
                    <i class="fa-solid fa-circle-info me-1"></i> يرجى التأكد من صحة كافة البيانات المدخلة والمرفقات قبل الضغط على زر الإرسال النهائي.
                </div>

                <!-- Review Summary Card -->
                <div class="card p-4 mb-4" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px;">
                    <div class="row g-3 fs-7">
                        <div class="col-md-6"><b>اسم الباحث:</b> <span id="rev-name" class="fw-bold text-dark">---</span></div>
                        <div class="col-md-6"><b>الرقم الوطني:</b> <span id="rev-nid">---</span></div>
                        <div class="col-md-6"><b>مركز البحوث:</b> <span id="rev-rcCenter" class="fw-bold text-primary">---</span></div>
                        <div class="col-md-6"><b>القسم / الرتبة:</b> <span id="rev-rcDept">---</span></div>
                        <div class="col-md-6"><b>الكلية والقسم المطلوب تدريسهما:</b> <span id="rev-workFaculty">---</span></div>
                        <div class="col-md-6"><b>رقم وتاريخ كتاب الجامعة:</b> <span id="rev-reqInfo">---</span></div>
                        <div class="col-md-12"><b>شهادة الدكتوراه الممنوحة:</b> <span id="rev-phdInfo" class="fw-bold">---</span></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-outline-navy px-4 py-2 prev-step" data-prev="4">
                        <i class="fa-solid fa-arrow-right me-2"></i> السابق: تعديل المرفقات
                    </button>
                    <div>
                        <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary btn-sm px-3 me-2">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كمسودة
                        </button>
                        <button type="submit" class="btn btn-success px-5 py-2.5 fw-bold shadow-sm" style="background-color: #059669; border-color: #059669;">
                            <i class="fa-solid fa-paper-plane me-2"></i> إرسال الطلب رسمياً
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.wizard-step');
    const sections = document.querySelectorAll('.form-section');
    const progressBar = document.getElementById('wizard-progress-bar');

    function goToStep(stepNumber) {
        sections.forEach(s => s.classList.add('d-none'));
        sections.forEach(s => s.classList.remove('active'));
        
        const targetSection = document.getElementById('step-' + stepNumber);
        if (targetSection) {
            targetSection.classList.remove('d-none');
            targetSection.classList.add('active');
        }

        steps.forEach(s => {
            const sn = parseInt(s.getAttribute('data-step'));
            if (sn < stepNumber) {
                s.classList.add('completed');
                s.classList.remove('active');
            } else if (sn === stepNumber) {
                s.classList.add('active');
                s.classList.remove('completed');
            } else {
                s.classList.remove('active', 'completed');
            }
        });

        if (progressBar) {
            const percent = ((stepNumber - 1) / (steps.length - 1)) * 100;
            progressBar.style.width = percent + '%';
        }

        window.scrollTo({ top: 100, behavior: 'smooth' });

        // Update Review Summary if on Step 5
        if (stepNumber === 5) {
            document.getElementById('rev-name').innerText = document.getElementById('input-fullName').value || '---';
            document.getElementById('rev-nid').innerText = document.getElementById('input-nationalId').value || '---';
            document.getElementById('rev-rcCenter').innerText = document.getElementById('input-rcCenterName').value || '---';
            document.getElementById('rev-rcDept').innerText = (document.getElementById('input-rcDepartment').value || '') + ' (' + (document.getElementById('input-rcRank').value || '') + ')';
            document.getElementById('rev-workFaculty').innerText = (document.getElementById('input-workFaculty').value || '') + ' - ' + (document.getElementById('input-workDepartment').value || '');
            document.getElementById('rev-reqInfo').innerText = (document.getElementById('input-reqNo').value || '') + ' تاريخ ' + (document.getElementById('input-reqDate').value || '');
            document.getElementById('rev-phdInfo').innerText = (document.getElementById('input-phdFaculty').value || '') + ' - ' + (document.getElementById('input-phdDepartment').value || '') + ' (' + (document.getElementById('input-phdGrantDate').value || '') + ')';
        }
    }

    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function () {
            const next = parseInt(this.getAttribute('data-next'));
            goToStep(next);
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', function () {
            const prev = parseInt(this.getAttribute('data-prev'));
            goToStep(prev);
        });
    });

    // Auto-Lookup Candidate
    const lookupBtn = document.getElementById('btn-lookup-candidate');
    if (lookupBtn) {
        lookupBtn.addEventListener('click', function() {
            const nid = document.getElementById('input-nationalId').value;
            if (!nid || nid.length < 5) {
                alert('يرجى إدخال الرقم الوطني للمرشح أولاً للبحث');
                return;
            }
            fetch('{{ route("university.candidate.lookup") }}?national_id=' + encodeURIComponent(nid))
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.candidate) {
                        const c = res.candidate;
                        if (c.full_name) document.getElementById('input-fullName').value = c.full_name;
                        if (c.father_name) document.getElementById('input-fatherName').value = c.father_name;
                        if (c.mother_name) document.getElementById('input-motherName').value = c.mother_name;
                        if (c.dob) document.getElementById('input-dob').value = c.dob.substring(0, 10);
                        if (c.gender) document.getElementById('input-gender').value = c.gender;
                        if (c.mobile) document.getElementById('input-mobile').value = c.mobile;
                        if (c.phone) document.getElementById('input-phone').value = c.phone;
                        if (c.email) document.getElementById('input-email').value = c.email;
                        if (c.address) document.getElementById('input-address').value = c.address;
                        alert('تم جلب بيانات الباحث المحفوظة مسبقاً بنجاح.');
                    } else {
                        alert('لم يتم العثور على سجل سابق لهذا الرقم الوطني. يرجى المتابعة وملء البيانات.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('تعذر استرجاع البيانات.');
                });
        });
    }
});
</script>

<style>
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
    height: 3px;
    background-color: var(--heritage-gold);
    z-index: 1;
    transition: width 0.3s ease;
}
.wizard-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    right: 0;
    left: 0;
    height: 3px;
    background-color: var(--outline-variant);
    z-index: 0;
}
.wizard-step {
    position: relative;
    z-index: 2;
    text-align: center;
    background: transparent;
}
.wizard-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 2px solid var(--outline-variant);
    color: var(--outline);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin: 0 auto 6px;
    transition: all 0.25s ease;
}
.wizard-step.active .wizard-icon {
    border-color: var(--heritage-gold);
    background-color: var(--primary-container);
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.2);
}
.wizard-step.completed .wizard-icon {
    border-color: #059669;
    background-color: #059669;
    color: #ffffff;
}
.wizard-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--outline);
}
.wizard-step.active .wizard-label {
    color: var(--primary-container);
    font-weight: 700;
}
</style>

@endsection

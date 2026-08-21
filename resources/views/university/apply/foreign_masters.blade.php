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
        <form action="{{ route('university.apply.foreign_masters.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
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
                        <input type="email" name="email" class="form-control academic-input" placeholder="name@example.com" value="{{ old('email', $draftCandidate->email ?? '') }}" required>
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
                        <input type="text" name="req_no" class="form-control academic-input" placeholder="مثال: 124/ص" value="{{ old('req_no', optional($draft)->new_uni_request_no ?? '') }}" required>
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
                    $hsCountryId = $draftHsEd ? $draftHsEd->country_id : 1;
                    $hsType = $draftHsEd ? $draftHsEd->section_name : '';
                    $hsYear = $draftHsEd ? substr($draftHsEd->grant_date, 0, 4) : '';
                    $hsDecNo = $draftHsEd && $draftHsEd->notes ? preg_replace('/.*رقم قرار المعادلة الثانوية:\s*([^\|]+).*/u', '$1', $draftHsEd->notes) : '';
                @endphp

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">بلد الحصول على الشهادة الثانوية <span class="text-danger">*</span></label>
                        <select name="hs_country_id" id="hs_country_id" class="form-select academic-input" onchange="toggleHsDecision(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('hs_country_id', $hsCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">سنة الحصول على الشهادة الثانوية <span class="text-danger">*</span></label>
                        <input type="number" name="hs_grant_date" class="form-control academic-input" placeholder="مثال: 2012" min="1950" max="{{ date('Y') }}" value="{{ old('hs_grant_date', $hsYear) }}" required>
                    </div>

                    <div class="col-md-6" id="hs_decision_box" style="display: {{ old('hs_country_id', $hsCountryId) == 1 ? 'none' : 'block' }};">
                        <label class="form-label label-sm fw-bold text-primary">رقم قرار معادلة الشهادة الثانوية غير السورية</label>
                        <input type="text" name="hs_decision_no" class="form-control academic-input" placeholder="أدخل رقم قرار المعادلة الصادر عن وزارة التربية" value="{{ old('hs_decision_no', $hsDecNo) }}">
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
                    $baCountryId = $draftBaEd ? $draftBaEd->country_id : 1;
                    $baUniId = $draftBaEd ? $draftBaEd->university_id : '';
                    $baUniOther = $draftBaEd ? $draftBaEd->section_name : '';
                    $baFaculty = $draftBaEd ? $draftBaEd->general_specialization : '';
                    $baDept = $draftBaEd ? $draftBaEd->exact_specialization : '';
                    $baSpec = $draftBaEd ? $draftBaEd->section_name : '';
                    $baRegDate = $draftBaEd ? $draftBaEd->registration_date : '';
                    $baGrantDate = $draftBaEd ? $draftBaEd->grant_date : '';
                    $baRank = $draftBaEd ? $draftBaEd->rank : '';
                    $baDecNo = $draftBaEd && $draftBaEd->notes ? preg_replace('/.*رقم قرار معادلة الإجازة:\s*([^\|]+).*/u', '$1', $draftBaEd->notes) : '';
                @endphp

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">بلد الإجازة الجامعية الأولى <span class="text-danger">*</span></label>
                        <select name="ba_country_id" id="ba_country_id" class="form-select academic-input" onchange="toggleBaCountry(this.value)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ba_country_id', $baCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الجامعة المانحة للإجازة الجامعية <span class="text-danger">*</span></label>
                        <select name="ba_university_id" id="ba_university_id" class="form-select academic-input" onchange="toggleBaUniOther(this.value)" required>
                            <option value="">-- اختر الجامعة --</option>
                            @foreach($universities as $u)
                                <option value="{{ $u->id }}" {{ old('ba_university_id', $baUniId) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                            <option value="other" {{ old('ba_university_id', $baUniId) == 'other' || ($draftBaEd && !$baUniId && $baUniOther) ? 'selected' : '' }}>جامعة أخرى (خارجية / خاصة)</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="ba_university_other_box" style="display: {{ old('ba_university_id', $baUniId) == 'other' || ($draftBaEd && !$baUniId && $baUniOther) ? 'block' : 'none' }};">
                        <label class="form-label label-sm fw-bold text-primary">اسم الجامعة المانحة (أخرى)</label>
                        <input type="text" name="ba_university_other" class="form-control academic-input" placeholder="أدخل اسم الجامعة المانحة للإجازة" value="{{ old('ba_university_other', $baUniOther) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الكلية المانحة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_faculty" class="form-control academic-input" placeholder="مثال: كلية العلوم" value="{{ old('ba_faculty', $baFaculty) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">القسم لدرجة الإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_department" class="form-control academic-input" placeholder="مثال: قسم الكيمياء" value="{{ old('ba_department', $baDept) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الاختصاص لدرجة الإجازة <span class="text-danger">*</span></label>
                        <input type="text" name="ba_specialization" class="form-control academic-input" placeholder="مثال: كيمياء تطبيقية" value="{{ old('ba_specialization', $baSpec) }}" required>
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

                    <div class="col-md-6" id="ba_decision_box" style="display: {{ old('ba_country_id', $baCountryId) == 1 ? 'none' : 'block' }};">
                        <label class="form-label label-sm fw-bold text-primary">رقم قرار معادلة الإجازة الجامعية غير السورية</label>
                        <input type="text" name="ba_decision_no" class="form-control academic-input" placeholder="رقم قرار المعادلة الصادر عن لجنة تعادل الشهادات" value="{{ old('ba_decision_no', $baDecNo) }}">
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
                    $maCountryId = $draftMaEd ? $draftMaEd->country_id : 2;
                    $maUniId = $draftMaEd ? $draftMaEd->university_id : '';
                    $maUniOther = $draftMaEd ? $draftMaEd->university_other : '';
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

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">بلد الحصول على درجة الماجستير (بلد الدراسة) <span class="text-danger">*</span></label>
                        <select name="ma_country_id" id="ma_country_id" class="form-select academic-input" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('ma_country_id', $maCountryId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الجامعة الخارجية المانحة للماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_university_other" id="ma_university_other" class="form-control academic-input" placeholder="اسم الجامعة الخارجية (مثال: جامعة بيروت العربية / جامعة القاهرة)" value="{{ old('ma_university_other', $maUniOther) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-sm fw-bold">الكلية المانحة لدرجة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_faculty" class="form-control academic-input" placeholder="مثال: كلية الهندسة المعلوماتية / كلية الآداب" value="{{ old('ma_faculty', $maFaculty) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">القسم / الاختصاص العام لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_department" class="form-control academic-input" placeholder="مثال: هندسة البرمجيات ونظم المعلومات" value="{{ old('ma_department', $maDept) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-sm fw-bold">الاختصاص الدقيق لشهادة الماجستير <span class="text-danger">*</span></label>
                        <input type="text" name="ma_specialization" class="form-control academic-input" placeholder="مثال: النمذجة والمحاكاة الحاسوبية / أمن البيانات" value="{{ old('ma_specialization', $maSpec) }}" required>
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
                                <i class="fa-solid fa-plane-departure me-1 text-primary"></i> هل المرشح موفد رسمياً للدراسة في الخارج بقرار من وزارة التعليم العالي أو إحدى الجهات العامة؟
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12" id="envoy_details_box" style="display: {{ old('is_envoy', $isEnvoy) ? 'block' : 'none' }};">
                        <div class="row g-3 p-3 bg-light rounded border border-warning">
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold">رقم قرار الإيفاد</label>
                                <input type="text" name="envoy_decision" class="form-control academic-input" placeholder="مثال: 452/إ" value="{{ old('envoy_decision', $envoyDec) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label label-sm fw-bold">تاريخ قرار الإيفاد</label>
                                <input type="date" name="envoy_date" class="form-control academic-input" value="{{ old('envoy_date', $envoyDate) }}">
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
                        <p class="fs-7 text-muted mb-3">
                            <i class="fa-solid fa-circle-info me-1 text-warning"></i>
                            <strong>تعليمات وزارة التعليم العالي:</strong> الحاصل على ماجستير خارجي والقادم حديثاً دون خبرة تدريسية مثبتة داخل الجامعات السورية لمدة سنتين، يمنح <strong>قرار تدريس الجوانب التطبيقية (عضو هيئة فنية)</strong> بدون مقابلة ليكتسب خبرة سنتين داخل البلد. أما من لديه خبرة تدريسية مثبتة داخل سوريا لمدة سنتين فأكثر فيتقدم لمسار <strong>تدريس المقررات النظرية</strong> (يتطلب مقابلة وأهلية).
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark fs-7">هل يمتلك المرشح خبرة تدريسية مثبتة داخل الجامعات السورية لمدة سنتين فأكثر؟ <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_syrian_experience" id="exp_no" value="no" {{ old('has_syrian_experience', $hasExp) == 'no' ? 'checked' : '' }} onchange="toggleExperienceFields('no')">
                                    <label class="form-check-label fw-bold text-dark" for="exp_no">
                                        لا (مسار تدريس الجوانب التطبيقية - عضو هيئة فنية - بدون مقابلة)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_syrian_experience" id="exp_yes" value="yes" {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'checked' : '' }} onchange="toggleExperienceFields('yes')">
                                    <label class="form-check-label fw-bold text-dark" for="exp_yes">
                                        نعم (مسار تدريس المقررات النظرية - خبرة مثبتة سنتين فأكثر - يتطلب مقابلة وأهلية)
                                    </label>
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
                <div class="card p-3 mb-4 border bg-light">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fs-8 fw-bold mb-1">بلد الدراسة</label>
                            <input type="text" id="mov_country" class="form-control form-control-sm academic-input" placeholder="اسم بلد الدراسة">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-8 fw-bold mb-1">الجامعة</label>
                            <input type="text" id="mov_university" class="form-control form-control-sm academic-input" placeholder="اسم الجامعة الخارجية">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-8 fw-bold mb-1">تاريخ الدخول لبلد الدراسة <span class="text-danger">*</span></label>
                            <input type="date" id="mov_entry_date" class="form-control form-control-sm academic-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-8 fw-bold mb-1">مطار / منفذ الدخول</label>
                            <input type="text" id="mov_entry_airport" class="form-control form-control-sm academic-input" placeholder="مثال: مطار رفيق الحريري / مطار القاهرة">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label class="form-label fs-8 fw-bold mb-1">تاريخ الخروج من بلد الدراسة <span class="text-danger">*</span></label>
                            <input type="date" id="mov_exit_date" class="form-control form-control-sm academic-input">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label class="form-label fs-8 fw-bold mb-1">مطار / منفذ الخروج</label>
                            <input type="text" id="mov_exit_airport" class="form-control form-control-sm academic-input" placeholder="مثال: مطار دمشق الدولي">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label class="form-label fs-8 fw-bold mb-1">رقم صفحة الجواز</label>
                            <input type="text" id="mov_page_number" class="form-control form-control-sm academic-input" placeholder="مثال: ص 14">
                        </div>
                        <div class="col-md-3 mt-2 text-end">
                            <button type="button" class="btn btn-solid-navy btn-sm px-4 fw-bold w-100" onclick="addResidencyMovement()">
                                <i class="fa-solid fa-plus me-1"></i> إضافة حركة الإقامة
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MOVEMENTS TABLE -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover align-middle text-center" id="movements_table" style="font-size: 0.85rem;">
                        <thead class="table-light fw-bold" style="color: var(--imperial-navy);">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 15%;">بلد الدراسة / الجامعة</th>
                                <th style="width: 20%;">تاريخ ومطار الدخول</th>
                                <th style="width: 20%;">تاريخ ومطار الخروج</th>
                                <th style="width: 10%;">رقم الصفحة</th>
                                <th style="width: 20%;">مدة الإقامة المحسوبة</th>
                                <th style="width: 10%;">إجراءات</th>
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
                                    $daysDiff = $inDate && $outDate && $outDate->gte($inDate) ? $inDate->diffInDays($outDate) : 0;
                                    $calcY = floor($daysDiff / 365);
                                    $calcM = floor(($daysDiff % 365) / 30);
                                    $calcD = ($daysDiff % 365) % 30;
                                @endphp
                                <tr data-row-id="{{ $idx + 1 }}">
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ optional($draftMaEd->country)->name ?? 'بلد الدراسة' }}<br><small class="text-muted">{{ $draftMaEd->university_other }}</small></td>
                                    <td><strong>{{ $res->entry_date }}</strong><br><small class="text-muted">{{ $res->entry_airport ?: '---' }}</small></td>
                                    <td><strong>{{ $res->exit_date }}</strong><br><small class="text-muted">{{ $res->exit_airport ?: '---' }}</small></td>
                                    <td><span class="badge bg-secondary">{{ $res->page_number ?: '---' }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success">{{ $calcY }} سنة و {{ $calcM }} شهر و {{ $calcD }} يوم</span></td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteResidencyMovement(this)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <input type="hidden" name="residences[{{ $idx }}][country_name]" value="{{ optional($draftMaEd->country)->name ?? '' }}">
                                        <input type="hidden" name="residences[{{ $idx }}][university_name]" value="{{ $draftMaEd->university_other ?? '' }}">
                                        <input type="hidden" name="residences[{{ $idx }}][entry_date]" value="{{ $res->entry_date }}">
                                        <input type="hidden" name="residences[{{ $idx }}][entry_airport]" value="{{ $res->entry_airport }}">
                                        <input type="hidden" name="residences[{{ $idx }}][exit_date]" value="{{ $res->exit_date }}">
                                        <input type="hidden" name="residences[{{ $idx }}][exit_airport]" value="{{ $res->exit_airport }}">
                                        <input type="hidden" name="residences[{{ $idx }}][page_number]" value="{{ $res->page_number }}">
                                    </td>
                                </tr>
                            @empty
                                <tr id="no_movements_row">
                                    <td colspan="7" class="text-muted py-3">لم يتم تسجيل أي حركة دخول أو خروج بعد. يرجى ملء الحقول أعلاه والضغط على (إضافة حركة الإقامة).</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL STAY DURATION BANNER (مجموع مدة الإقامة) -->
                <div class="card border-primary p-3 bg-primary-subtle text-primary-emphasis text-center rounded-3 shadow-xs">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-clock-rotate-left me-1"></i> إجمالي مدة الإقامة المحسوبة خارج القطر :</h6>
                    <div class="d-flex justify-content-center align-items-center gap-3 fs-5 fw-bold">
                        <div><span id="total_stay_years" class="badge bg-primary fs-6 px-3 py-2">0</span> سنة</div>
                        <div><span id="total_stay_months" class="badge bg-primary fs-6 px-3 py-2">0</span> شهر</div>
                        <div><span id="total_stay_days" class="badge bg-primary fs-6 px-3 py-2">0</span> يوم</div>
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
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_secondary_cert']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. شهادة الإجازة الجامعية الأولى -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">2. نسخة مصدقة عن شهادة الإجازة الجامعية الأولى (البكالوريوس) <span class="text-danger">*</span></label>
                            <input type="file" name="file_bachelor_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_bachelor_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_bachelor_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_bachelor_cert']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. الشهادة قبل المؤهل العلمي الأخير -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">3. الشهادة قبل المؤهل العلمي الأخير <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_prev_qual_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_prev_qual_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. شهادة الماجستير الخارجي -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">4. نسخة مصدقة أصولاً عن شهادة الماجستير الخارجي <span class="text-danger">*</span></label>
                            <input type="file" name="file_master_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_master_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_master_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_master_cert']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. كشف علامات الماجستير -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">5. كشف علامات الماجستير إن وجد <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_master_transcript" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_master_transcript']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. ملخص عن الأطروحة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">6. ملخص عن أطروحة الماجستير باللغة العربية <span class="text-danger">*</span></label>
                            <input type="file" name="file_thesis_abstract" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_thesis_abstract']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_thesis_abstract']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_thesis_abstract']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 7. إيصال المكتبة الوطنية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">7. إيصال إيداع الأطروحة لدى المكتبة الوطنية <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_library_receipt" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_library_receipt']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. وثيقة بتاريخ التسجيل والمناقشة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">8. وثيقة تثبت تواريخ التسجيل والمباشرة والمناقشة لدرجة الماجستير <span class="text-danger">*</span></label>
                            <input type="file" name="file_reg_defense_doc" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_reg_defense_doc']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_reg_defense_doc']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 9. شهادة خبرة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">9. شهادة الخبرة التدريسية المثبتة داخل الجامعات السورية <span class="text-danger" id="exp_cert_required_badge" style="display: {{ old('has_syrian_experience', $hasExp) == 'yes' ? 'inline' : 'none' }};">*</span></label>
                            <input type="file" name="file_experience_cert" id="file_experience_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_experience_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 10. العقود مع الجامعة الخاصة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">10. العقود مع الجامعة الخاصة <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_private_uni_contracts" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_private_uni_contracts']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 11. إيصالات الرواتب التي قبضت من الجامعة -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">11. إيصالات الرواتب التي قبضت من الجامعة <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_salary_receipts" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_salary_receipts']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 12. شهادة ICDL -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">12. شهادة مهارات الحاسوب (ICDL) <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_icdl_cert" class="form-control academic-input" accept=".pdf">
                            @if(!empty($existingFilesMap['file_icdl_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 13. شهادة اختبار اللغة الإنكليزية -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">13. وثيقة اجتياز اختبار اللغة الأجنبية (الإنكليزية) <span class="text-danger">*</span></label>
                            <input type="file" name="file_english_cert" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_english_cert']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_english_cert']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 14. رسوم التعادل -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">14. إيصال تسديد رسم تعادل الماجستير الخارجي (100,000 ل.س) <span class="text-danger">*</span></label>
                            <input type="file" name="file_fees_receipt" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_fees_receipt']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_fees_receipt']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_fees_receipt']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 15. جواز السفر -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">15. صورة عن جواز السفر وصفحات الأختام والإقامة ببلد الدراسة <span class="text-danger">*</span></label>
                            <input type="file" name="file_passport" class="form-control academic-input" accept=".pdf" {{ empty($existingFilesMap['file_passport']) ? 'required' : '' }}>
                            @if(!empty($existingFilesMap['file_passport']))
                                <div class="mt-2"><span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> تم رفع الملف سابقاً: {{ basename($existingFilesMap['file_passport']->file_path) }}</span></div>
                            @endif
                        </div>
                    </div>

                    <!-- 16. مرفقات أخرى -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="form-label label-sm fw-bold">16. وثائق ومرفقات ثبوتية داعمة أخرى <span class="text-muted">(اختياري)</span></label>
                            <input type="file" name="file_other_attachments" class="form-control academic-input" accept=".pdf">
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

                <div class="row g-4 mb-4">
                    <!-- Personal Info Card -->
                    <div class="col-md-6">
                        <div class="card p-3 border h-100 shadow-2xs">
                            <h6 class="fw-bold text-prestigious border-bottom pb-2 mb-3"><i class="fa-solid fa-user me-1 text-warning"></i> 1. البيانات الشخصية وبيانات الترشيح</h6>
                            <div class="row g-2 fs-7">
                                <div class="col-6"><strong>الاسم والكنية:</strong> <span id="preview-name">---</span></div>
                                <div class="col-6"><strong>اسم الأب:</strong> <span id="preview-father">---</span></div>
                                <div class="col-6"><strong>اسم الأم:</strong> <span id="preview-mother">---</span></div>
                                <div class="col-6"><strong>الرقم الوطني:</strong> <span id="preview-nationalId">---</span></div>
                                <div class="col-6"><strong>الجنسية:</strong> <span id="preview-nationality">---</span></div>
                                <div class="col-6"><strong>رقم الموبايل:</strong> <span id="preview-mobile">---</span></div>
                                <div class="col-12"><strong>البريد الإلكتروني:</strong> <span id="preview-email">---</span></div>
                                <div class="col-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo">---</span></div>
                                <div class="col-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate">---</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Foreign Master Info Card -->
                    <div class="col-md-6">
                        <div class="card p-3 border h-100 shadow-2xs">
                            <h6 class="fw-bold text-prestigious border-bottom pb-2 mb-3"><i class="fa-solid fa-award me-1 text-warning"></i> 2. درجة الماجستير الخارجي والمسار</h6>
                            <div class="row g-2 fs-7">
                                <div class="col-6"><strong>بلد الدراسة:</strong> <span id="preview-maCountry">---</span></div>
                                <div class="col-6"><strong>الجامعة المانحة:</strong> <span id="preview-maUni">---</span></div>
                                <div class="col-6"><strong>الكلية:</strong> <span id="preview-maFaculty">---</span></div>
                                <div class="col-6"><strong>القسم / الاختصاص العام:</strong> <span id="preview-maDept">---</span></div>
                                <div class="col-12"><strong>الاختصاص الدقيق:</strong> <span id="preview-maSpec">---</span></div>
                                <div class="col-6"><strong>تاريخ المنح:</strong> <span id="preview-maGrantDate">---</span></div>
                                <div class="col-6"><strong>المعدل / التقدير:</strong> <span id="preview-maRank">---</span></div>
                                <div class="col-12"><strong>مسار الطلب:</strong> <span id="preview-maTrack" class="badge bg-warning-subtle text-warning-emphasis border">---</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stay Duration Preview Card -->
                    <div class="col-12">
                        <div class="card p-3 border shadow-2xs">
                            <h6 class="fw-bold text-prestigious border-bottom pb-2 mb-3"><i class="fa-solid fa-passport me-1 text-warning"></i> 3. ملخص مدة الإقامة ببلد الدراسة</h6>
                            <div class="row g-2 align-items-center fs-7">
                                <div class="col-md-6">
                                    <strong>عدد حركات الإقامة المسجلة:</strong> <span id="preview-resCount" class="badge bg-secondary">0</span> حركات
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <strong>إجمالي مدة الإقامة المحسوبة:</strong> <span id="preview-totalStay" class="badge bg-primary fs-7">0 سنة و 0 شهر و 0 يوم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-4 border border-warning bg-light rounded-3 mb-4">
                    <div class="form-check">
                        <input class="form-check-input ms-0 me-3" type="checkbox" id="chkConfirm" required>
                        <label class="form-check-label fw-bold text-dark fs-7" for="chkConfirm">
                            أصادق وأقر بصفتي ممثل الجامعة الخاصة بأن جميع البيانات والمرفقات المدرجة أعلاه صحيحة ومطابقة للوثائق الرسمية المعتمدة والأختام المرفقة بجواز السفر، وأتحمل كامل المسؤولية القانونية حيال ذلك.
                        </label>
                    </div>
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
    const totalSteps = 7;

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

        if (step === 1) {
            btnPrev.style.display = 'none';
            spacerPrev.style.display = 'block';
        } else {
            btnPrev.style.display = 'inline-block';
            spacerPrev.style.display = 'none';
        }

        if (step === totalSteps) {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'inline-block';
            populateReview();
        } else {
            btnNext.style.display = 'inline-block';
            btnSubmit.style.display = 'none';
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
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

    function validateCurrentStep(step) {
        const section = document.getElementById('step-' + step);
        if (!section) return true;

        const requiredInputs = section.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
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

    function toggleHsDecision(countryId) {
        const box = document.getElementById('hs_decision_box');
        if (box) {
            box.style.display = (countryId == '1') ? 'none' : 'block';
        }
    }

    function toggleBaCountry(countryId) {
        const box = document.getElementById('ba_decision_box');
        if (box) {
            box.style.display = (countryId == '1') ? 'none' : 'block';
        }
    }

    function toggleBaUniOther(val) {
        const box = document.getElementById('ba_university_other_box');
        if (box) {
            box.style.display = (val === 'other') ? 'block' : 'none';
        }
    }

    function toggleEnvoyBox(checked) {
        const box = document.getElementById('envoy_details_box');
        if (box) {
            box.style.display = checked ? 'block' : 'none';
        }
    }

    function toggleExperienceFields(val) {
        const container = document.getElementById('experience_details_container');
        const badge = document.getElementById('exp_cert_required_badge');
        const fileInput = document.getElementById('file_experience_cert');

        if (val === 'yes') {
            if (container) container.style.display = 'block';
            if (badge) badge.style.display = 'inline';
            if (fileInput) fileInput.setAttribute('required', 'required');
        } else {
            if (container) container.style.display = 'none';
            if (badge) badge.style.display = 'none';
            if (fileInput) fileInput.removeAttribute('required');
        }
    }

    // ==========================================
    // RESIDENCY MOVEMENTS LOGIC (حركات الإقامة)
    // ==========================================
    let movementCounter = {{ $draftResidences->count() ?? 0 }};

    function addResidencyMovement() {
        const country = document.getElementById('mov_country').value.trim() || (document.getElementById('ma_country_id').options[document.getElementById('ma_country_id').selectedIndex]?.text || 'بلد الدراسة');
        const university = document.getElementById('mov_university').value.trim() || document.getElementById('ma_university_other').value.trim() || '---';
        const entryDate = document.getElementById('mov_entry_date').value;
        const entryAirport = document.getElementById('mov_entry_airport').value.trim();
        const exitDate = document.getElementById('mov_exit_date').value;
        const exitAirport = document.getElementById('mov_exit_airport').value.trim();
        const pageNumber = document.getElementById('mov_page_number').value.trim();

        if (!entryDate || !exitDate) {
            alert('يرجى إدخال تاريخ الدخول وتاريخ الخروج لحركة الإقامة.');
            return;
        }

        const inD = new Date(entryDate);
        const outD = new Date(exitDate);

        if (outD < inD) {
            alert('تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.');
            return;
        }

        const diffTime = Math.abs(outD - inD);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const calcYears = Math.floor(diffDays / 365);
        const calcMonths = Math.floor((diffDays % 365) / 30);
        const calcDays = (diffDays % 365) % 30;

        movementCounter++;
        const tbody = document.getElementById('movements_tbody');
        const noRow = document.getElementById('no_movements_row');
        if (noRow) noRow.remove();

        const tr = document.createElement('tr');
        tr.setAttribute('data-days', diffDays);
        tr.innerHTML = `
            <td>${tbody.children.length + 1}</td>
            <td><strong>${country}</strong><br><small class="text-muted">${university}</small></td>
            <td><strong>${entryDate}</strong><br><small class="text-muted">${entryAirport || '---'}</small></td>
            <td><strong>${exitDate}</strong><br><small class="text-muted">${exitAirport || '---'}</small></td>
            <td><span class="badge bg-secondary">${pageNumber || '---'}</span></td>
            <td><span class="badge bg-success-subtle text-success border border-success">${calcYears} سنة و ${calcMonths} شهر و ${calcDays} يوم</span></td>
            <td>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteResidencyMovement(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <input type="hidden" name="residences[${movementCounter}][country_name]" value="${country}">
                <input type="hidden" name="residences[${movementCounter}][university_name]" value="${university}">
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
            tbody.innerHTML = '<tr id="no_movements_row"><td colspan="7" class="text-muted py-3">لم يتم تسجيل أي حركة دخول أو خروج بعد.</td></tr>';
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
                const inD = new Date(inDateInput.value);
                const outD = new Date(outDateInput.value);
                if (outD >= inD) {
                    totalDays += Math.ceil(Math.abs(outD - inD) / (1000 * 60 * 60 * 24));
                }
            }
        });

        const years = Math.floor(totalDays / 365);
        const months = Math.floor((totalDays % 365) / 30);
        const days = (totalDays % 365) % 30;

        document.getElementById('total_stay_years').textContent = years;
        document.getElementById('total_stay_months').textContent = months;
        document.getElementById('total_stay_days').textContent = days;

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

    function populateReview() {
        const form = document.getElementById('wizard-form');
        if (!form) return;

        // Step 1: Personal
        document.getElementById('preview-name').textContent = form.full_name.value || '---';
        document.getElementById('preview-father').textContent = form.father_name.value || '---';
        document.getElementById('preview-mother').textContent = form.mother_name.value || '---';
        document.getElementById('preview-nationalId').textContent = form.national_id.value || '---';
        document.getElementById('preview-mobile').textContent = form.mobile.value || '---';
        document.getElementById('preview-email').textContent = form.email.value || '---';
        document.getElementById('preview-reqNo').textContent = form.req_no.value || '---';
        document.getElementById('preview-reqDate').textContent = form.req_date.value || '---';

        const natSelect = form.nationality_id;
        document.getElementById('preview-nationality').textContent = natSelect.options[natSelect.selectedIndex]?.text || '---';

        // Step 4: Master & Track
        const maCountrySelect = form.ma_country_id;
        document.getElementById('preview-maCountry').textContent = maCountrySelect.options[maCountrySelect.selectedIndex]?.text || '---';
        document.getElementById('preview-maUni').textContent = form.ma_university_other.value || '---';
        document.getElementById('preview-maFaculty').textContent = form.ma_faculty.value || '---';
        document.getElementById('preview-maDept').textContent = form.ma_department.value || '---';
        document.getElementById('preview-maSpec').textContent = form.ma_specialization.value || '---';
        document.getElementById('preview-maGrantDate').textContent = form.ma_grant_date.value || '---';
        document.getElementById('preview-maRank').textContent = form.ma_rank.value || '---';

        const hasExp = form.has_syrian_experience.value === 'yes';
        const trackBadge = document.getElementById('preview-maTrack');
        if (hasExp) {
            trackBadge.className = 'badge bg-primary-subtle text-primary border border-primary';
            trackBadge.textContent = 'ماجستير خارجي نظري (خبرة سنتين داخل سوريا - يتطلب مقابلة وأهلية)';
        } else {
            trackBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning';
            trackBadge.textContent = 'ماجستير خارجي تطبيقي (عضو هيئة فنية - تدريس تطبيقي بدون مقابلة)';
        }

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
                    goToStep(7);
                    chkConfirm.focus();
                    chkConfirm.setCustomValidity('يرجى المصادقة على الإقرار بصحة البيانات للمتابعة.');
                    chkConfirm.reportValidity();
                    return false;
                }
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

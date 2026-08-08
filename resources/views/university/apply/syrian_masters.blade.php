@extends('layouts.university')

@section('title', 'تقديم طلب تعادل ماجستير سوري')

@section('content')

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
        
        <!-- Multi-Step Progress Indicators -->
        <div class="wizard-steps" id="wizard-steps-container">
            <div class="wizard-progress" id="wizard-progress-bar" style="width: 0%;"></div>
            
            <div class="wizard-step active" data-step="1">
                <div class="wizard-icon">1</div>
                <span class="wizard-label d-none d-md-inline">الشخصية</span>
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
                <span class="wizard-label d-none d-md-inline">المقررات</span>
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
        <form action="{{ route('university.apply.syrian_masters.submit') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
            @csrf

            <!-- ================= STEP 1: PERSONAL INFO ================= -->
            <div class="form-section active" id="step-1">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-user fs-5" style="color: var(--heritage-gold);"></i> الخطوة 1: المعلومات الشخصية للمرشح وتكرار الطلب
                </h5>
                
                <!-- EQUIVALENCE FREQUENCY CHOICE & CANDIDATE LOOKUP -->
                <div class="card mb-4 border p-3.5" style="background-color: var(--surface-container-low); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                    <label class="form-label fw-bold mb-2" style="color: var(--primary-container); font-size: 0.98rem;">
                        <i class="fa-solid fa-repeat me-1.5" style="color: var(--heritage-gold);"></i> تكرار تقديم طلب التعادل لهذا المرشح : <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex flex-wrap gap-4 align-items-center mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="equivalence_frequency" id="freq_first" value="تعادل للمرة الأولى" checked onchange="toggleCandidateLookup(false)">
                            <label class="form-check-label fw-bold" for="freq_first" style="color: var(--primary-container); cursor: pointer;">
                                📌 تعادل للمرة الأولى (أول تقديم لطلب تعادل بالوزارة لهذا المرشح)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="equivalence_frequency" id="freq_second" value="تعادل للمرة الثانية" onchange="toggleCandidateLookup(true)">
                            <label class="form-check-label fw-bold" for="freq_second" style="color: var(--primary-container); cursor: pointer;">
                                🔄 تعادل للمرة الثانية أو أكثر (سبق للمرشح تقديم طلب تعادل سابق بالوزارة)
                            </label>
                        </div>
                    </div>

                    <!-- Candidate Lookup Container (Visible when "تعادل للمرة الثانية" is checked) -->
                    <div id="candidate_lookup_box" class="mt-3 p-3 bg-white rounded border d-none">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold fs-7" style="color: var(--primary-container);">
                                <i class="fa-solid fa-magnifying-glass me-1" style="color: var(--heritage-gold);"></i> استعلام وجلب بيانات المرشح ومؤهلاته السابقة بالرقم الوطني:
                            </span>
                            <span class="badge bg-light text-muted border fs-8">الرقم الوطني للمرشح (المكون من 11 خانة)</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" id="candidate_search_input" class="form-control academic-input" placeholder="ادخل الرقم الوطني للمرشح لاسترجاع كافة البيانات والمؤهلات...">
                            <button type="button" class="btn btn-primary fw-bold px-3" onclick="performCandidateLookup()">
                                <i class="fa-solid fa-search me-1"></i> استعلام بالرقم الوطني وجلب البيانات
                            </button>
                        </div>
                        <div id="lookup_results_area" class="mt-2"></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم المرشح الكامل *</label>
                        <input type="text" name="full_name" id="input-fullName" class="form-control academic-input" placeholder="الاسم والنسبة" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأب *</label>
                        <input type="text" name="father_name" id="input-fatherName" class="form-control academic-input" placeholder="اسم الأب" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأم ونسبتها *</label>
                        <input type="text" name="mother_name" id="input-motherName" class="form-control academic-input" placeholder="اسم ونسبة الأم" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنسية *</label>
                        <select name="nationality_id" id="input-nationality" class="form-select academic-input" onchange="updateSyrianStatus(this)" required>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ $c->name === 'سوريا' ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="is_syrian" id="input-isSyrian" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الرقم الوطني / رقم جواز السفر *</label>
                        <input type="text" name="national_id" id="input-nationalId" class="form-control academic-input" placeholder="الرقم الوطني المكون من 11 خانة" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الميلاد *</label>
                        <input type="date" name="dob" id="input-dob" class="form-control academic-input" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الوظيفة الحالية للمرشح *</label>
                        <input type="text" name="job_title" id="input-jobTitle" class="form-control academic-input" placeholder="مثال: مهندس، موظف، معيد" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">الجنس *</label>
                        <select name="gender" id="input-gender" class="form-select academic-input" required>
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">البريد الإلكتروني *</label>
                        <input type="email" name="email" id="input-email" class="form-control academic-input" placeholder="name@example.com" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف المحمول *</label>
                        <input type="text" name="mobile" id="input-mobile" class="form-control academic-input" placeholder="09xxxxxxxx" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الهاتف الأرضي</label>
                        <input type="text" name="phone" id="input-phone" class="form-control academic-input" placeholder="011xxxxxxx">
                    </div>

                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">عنوان الإقامة الحالي بالتفصيل *</label>
                        <textarea name="address" id="input-address" class="form-control academic-input" rows="2" placeholder="المحافظة - المدينة - الشارع - البناء" required></textarea>
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
                                <option value="{{ $c->id }}" {{ $c->name === 'سوريا' ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">نوع البكالوريا *</label>
                        <select name="hs_type" id="input-hsType" class="form-select academic-input" required>
                            <option value="علمي">علمي</option>
                            <option value="أدبي">أدبي</option>
                            <option value="تجاري">تجاري</option>
                            <option value="صناعي">صناعي</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ الحصول على الشهادة *</label>
                        <input type="date" name="hs_grant_date" id="input-hsDate" class="form-control academic-input" required>
                    </div>

                    <!-- Conditional high school equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="hs-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الشهادة الثانوية</h6>
                            <p class="label-sm text-muted mb-3">بما أن الشهادة الثانوية غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم قرار المعادلة من وزارة التربية السورية ورفع صورة قرار المعادلة في خطوة المرفقات النهائية.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار معادلة الشهادة الثانوية *</label>
                                    <input type="text" name="hs_decision_no" id="input-hsDecisionNo" class="form-control academic-input" placeholder="أدخل رقم القرار الرسمي">
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
                                <option value="{{ $c->id }}" {{ $c->name === 'سوريا' ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="ba-uni-select-container">
                        <label class="form-label label-md fw-medium text-dark">الجامعة المانحة *</label>
                        <select name="ba_university_id" id="input-baUniId" class="form-select academic-input">
                            <option value="">-- اختر الجامعة --</option>
                            @foreach($universities as $uni)
                                @if($uni->country && $uni->country->name === 'سوريا')
                                    <option value="{{ $uni->id }}">{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="ba-uni-text-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">اسم الجامعة الأجنبية / الجهة المانحة *</label>
                        <input type="text" name="ba_university_other" id="input-baUniOther" class="form-control academic-input" placeholder="اسم الجامعة الكامل">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        <select name="ba_rank" id="input-baRank" class="form-select academic-input" required>
                            <option value="امتياز">امتياز</option>
                            <option value="جيد جداً">جيد جداً</option>
                            <option value="جيد">جيد</option>
                            <option value="مقبول">مقبول</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية والفرع (التخصص العام) *</label>
                        <input type="text" name="ba_faculty" id="input-baFaculty" class="form-control academic-input" placeholder="مثال: هندسة المعلوماتية" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق) *</label>
                        <input type="text" name="ba_department" id="input-baDept" class="form-control academic-input" placeholder="مثال: هندسة البرمجيات ونظم المعلومات" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالإجازة *</label>
                        <input type="date" name="ba_registration_date" id="input-baRegDate" class="form-control academic-input" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التخرج / الحصول عليها *</label>
                        <input type="date" name="ba_grant_date" id="input-baGrantDate" class="form-control academic-input" required>
                    </div>

                    <!-- Conditional bachelor's equivalence if country is not Syria -->
                    <div class="col-12 mt-4" id="ba-equivalence-section" style="display: none;">
                        <div class="card p-3 shadow-sm border-0" style="background-color: var(--warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                            <h6 class="fw-bold mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-1"></i> إدخال قرار معادلة الإجازة الجامعية الأولى</h6>
                            <p class="label-sm text-muted mb-3">بما أن الإجازة الجامعية الأولى غير صادرة عن الجمهورية العربية السورية، يرجى إدخال رقم قرار المعادلة الصادر عن وزارة التعليم العالي والبحث العلمي السورية ورفع صورة قرار المعادلة في خطوة المرفقات النهائية.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">رقم قرار تعادل الإجازة الجامعية *</label>
                                    <input type="text" name="ba_decision_no" id="input-baDecisionNo" class="form-control academic-input" placeholder="أدخل رقم قرار التعادل الرسمي">
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
                                    <option value="{{ $uni->id }}">{{ $uni->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">التقدير / المرتبة *</label>
                        <select name="ma_rank" id="input-maRank" class="form-select academic-input" required>
                            <option value="امتياز">امتياز</option>
                            <option value="جيد جداً">جيد جداً</option>
                            <option value="جيد">جيد</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">اسم الأستاذ المشرف *</label>
                        <input type="text" name="ma_supervisor" id="input-maSupervisor" class="form-control academic-input" placeholder="الاسم الثنائي للمشرف مع اللقب العلمي" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">الكلية والفرع (التخصص العام للماجستير) *</label>
                        <input type="text" name="ma_faculty" id="input-maFaculty" class="form-control academic-input" placeholder="كلية الهندسة المدنية" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">القسم (التخصص الدقيق للماجستير) *</label>
                        <input type="text" name="ma_department" id="input-maDept" class="form-control academic-input" placeholder="إدارة المشاريع" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ التسجيل بالدرجة *</label>
                        <input type="date" name="ma_registration_date" id="input-maRegDate" class="form-control academic-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ المناقشة *</label>
                        <input type="date" name="ma_defense_date" id="input-maDefDate" class="form-control academic-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label label-md fw-medium text-dark">تاريخ منح الدرجة (الحصول على الشهادة) *</label>
                        <input type="date" name="ma_grant_date" id="input-maGrantDate" class="form-control academic-input" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label label-md fw-medium text-dark">عنوان رسالة الماجستير (الأطروحة) بالتفصيل *</label>
                        <textarea name="ma_thesis_title" id="input-maThesisTitle" class="form-control academic-input" rows="2" placeholder="أدخل عنوان رسالة الماجستير كما هو مذكور في مصدقة التخرج" required></textarea>
                    </div>

                    <!-- Experience details toggle (> 2 years) -->
                    <div class="col-12 mt-4">
                        <div class="card border-0 shadow-sm p-3" style="background-color: var(--surface-container-low); border: 1px solid var(--outline-variant) !important; border-radius: 4px;">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="input-hasExperience" name="has_experience" value="1" onchange="toggleExperienceSection(this)">
                                <label class="form-check-label fw-bold text-dark ms-2 label-md" for="input-hasExperience">هل يمتلك المرشح خبرة تدريسية تفوق سنتين؟</label>
                            </div>
                            <div class="row g-3" id="experience-details-section" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label label-md fw-medium text-dark">مكان الخبرة التدريسية (الجهة/الجامعة) *</label>
                                    <input type="text" name="exp_place" id="input-expPlace" class="form-control academic-input" placeholder="اسم الكلية أو الجامعة والمعهد">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-md fw-medium text-dark">من عام *</label>
                                    <input type="number" name="exp_from_year" id="input-expFrom" class="form-control academic-input" placeholder="2021">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label label-md fw-medium text-dark">إلى عام *</label>
                                    <input type="number" name="exp_to_year" id="input-expTo" class="form-control academic-input" placeholder="2023">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= STEP 5: UNIVERSITY REQUEST & COURSES ================= -->
            <div class="form-section" id="step-5" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-list-check fs-5" style="color: var(--heritage-gold);"></i> الخطوة 5: بيانات طلب الجامعة والمقررات المرشح لتدريسها
                </h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">رقم كتاب طلب التقويم الصادر عن الجامعة *</label>
                        <input type="text" name="req_no" id="input-reqNo" class="form-control academic-input" placeholder="رقم الكتاب الرسمي" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">تاريخ كتاب طلب التقويم *</label>
                        <input type="date" name="req_date" id="input-reqDate" class="form-control academic-input" required>
                    </div>
                </div>

                <!-- Proposed Courses to teach Table -->
                <div class="card p-3 shadow-sm border-0" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px; border: 1px solid var(--outline-variant) !important; background-color: #ffffff;">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-bottom-color: var(--outline-variant) !important;">
                        <h6 class="fw-bold mb-0" style="color: var(--primary-container);"><i class="fa-solid fa-book me-1" style="color: var(--heritage-gold);"></i> المقررات التي سيدرسها المرشح في الجامعة</h6>
                        <button type="button" class="btn btn-sm btn-action" onclick="addCourseRow()">
                            <i class="fa-solid fa-plus me-1"></i> إضافة مقرر
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table mohe-table align-middle">
                            <thead>
                                <tr>
                                    <th>اسم المقرر الدراسي *</th>
                                    <th>الكلية *</th>
                                    <th>القسم *</th>
                                    <th style="width: 80px;" class="text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="courses-tbody">
                                <tr>
                                    <td>
                                        <input type="text" name="courses[0][name]" class="form-control form-control-sm academic-input course-name-input" placeholder="مثال: معمارية الحاسب" required>
                                    </td>
                                    <td>
                                        <input type="text" name="courses[0][faculty]" class="form-control form-control-sm academic-input course-faculty-input" placeholder="مثال: هندسة المعلوماتية" required>
                                    </td>
                                    <td>
                                        <input type="text" name="courses[0][department]" class="form-control form-control-sm academic-input course-dept-input" placeholder="مثال: قسم البرمجيات" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCourseRow(this)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= STEP 6: ATTACHMENTS ================= -->
            <div class="form-section" id="step-6" style="display: none;">
                <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center gap-2" style="color: var(--primary-container); border-bottom-color: var(--outline-variant) !important;">
                    <i class="fa-solid fa-paperclip fs-5" style="color: var(--heritage-gold);"></i> الخطوة 6: رفع المرفقات والمستندات الثبوتية المطلوبة (بصيغة PDF فقط)
                </h5>
                
                <div class="alert border-0 shadow-sm mb-4" style="background-color: var(--surface-container-low); border-right: 4px solid var(--primary-container) !important; color: var(--primary-container); border-radius: 4px;">
                    <i class="fa-solid fa-info-circle me-1" style="color: var(--heritage-gold);"></i> يرجى التأكد من رفع ملفات PDF واضحة ومصدقة أصولاً لعدم تعليق المعاملة من قبل لجنة التعادل.
                </div>

                <div class="row g-4">
                    <!-- High School Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية *</label>
                        <input type="file" name="file_hs_cert" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- HS Equivalence Decision File (Conditional) -->
                    <div class="col-md-6" id="hs-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الثانوية (وزارة التربية) *</label>
                        <input type="file" name="hs_decision_file" id="input-hsDecisionFile" class="form-control academic-input" accept=".pdf">
                    </div>

                    <!-- Bachelor Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى *</label>
                        <input type="file" name="file_ba_cert" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Bachelor Equivalence Decision File (Conditional) -->
                    <div class="col-md-6" id="ba-decision-file-container" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">صورة عن قرار معادلة الشهادة الجامعية الأولى *</label>
                        <input type="file" name="ba_decision_file" id="input-baDecisionFile" class="form-control academic-input" accept=".pdf">
                    </div>

                    <!-- Master Cert -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">نسخة مصدقة أصولاً عن شهادة الماجستير *</label>
                        <input type="file" name="file_ma_cert" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Master Registration / Defense dates doc -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">وثيقة تواريخ التسجيل والمناقشة والمنح بالماجستير *</label>
                        <input type="file" name="file_ma_dates" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Arabic Thesis Summary -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">ملخص باللغة العربية عن رسالة الماجستير إلكترونياً *</label>
                        <input type="file" name="file_thesis_summary" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- University Request Doc -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية *</label>
                        <input type="file" name="file_uni_request" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Language & ICDL Certificates -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">شهادة اللغة الإنكليزية + شهادة ICDL معتمدة *</label>
                        <input type="file" name="file_lang_icdl" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- CV -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">السيرة الذاتية للمرشح كاملة *</label>
                        <input type="file" name="file_cv" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Payment Receipt -->
                    <div class="col-md-6">
                        <label class="form-label label-md fw-medium text-dark">إيصال تسديد رسم تعادل 100,000 ل.س *</label>
                        <input type="file" name="file_payment" class="form-control academic-input" accept=".pdf" required>
                    </div>

                    <!-- Experience Certificate (Conditional) -->
                    <div class="col-md-6 exp-conditional-file" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">شهادة خبرة لا تقل عن سنتين ما بعد الدرجة *</label>
                        <input type="file" name="file_exp_cert" id="input-fileExpCert" class="form-control academic-input" accept=".pdf">
                    </div>

                    <!-- Contracts & Salary Slips (Conditional) -->
                    <div class="col-md-6 exp-conditional-file" style="display: none;">
                        <label class="form-label label-md fw-medium text-dark">العقود وإيصالات الرواتب مصدقة أصولاً *</label>
                        <input type="file" name="file_contracts" id="input-fileContracts" class="form-control academic-input" accept=".pdf">
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
                        <div class="col-12 border-bottom pb-2" style="border-bottom-color: var(--outline-variant) !important;">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);"><i class="fa-solid fa-user me-1" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية للمرشح:</h6>
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
                        <div class="col-12 border-bottom pb-2" style="border-bottom-color: var(--outline-variant) !important;">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 2. بيانات الشهادة الثانوية:</h6>
                            <div class="row g-2">
                                <div class="col-md-6"><strong>الدولة المانحة:</strong> <span id="preview-hsCountry"></span></div>
                                <div class="col-md-6"><strong>نوع البكالوريا:</strong> <span id="preview-hsType"></span></div>
                                <div class="col-md-6"><strong>تاريخ الحصول عليها:</strong> <span id="preview-hsDate"></span></div>
                                <div class="col-md-6" id="preview-hsDecisionContainer"><strong>رقم قرار المعادلة السوري:</strong> <span id="preview-hsDecisionNo"></span></div>
                            </div>
                        </div>

                        <!-- Group 3: Bachelor's -->
                        <div class="col-12 border-bottom pb-2" style="border-bottom-color: var(--outline-variant) !important;">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);"><i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> 3. بيانات الإجازة الجامعية الأولى (البكالوريوس):</h6>
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
                        <div class="col-12 border-bottom pb-2" style="border-bottom-color: var(--outline-variant) !important;">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);"><i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 4. بيانات درجة الماجستير والخبرة التدريسية:</h6>
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

                        <!-- Group 5: Request & Courses -->
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: var(--primary-container);"><i class="fa-solid fa-file-signature me-1" style="color: var(--heritage-gold);"></i> 5. كتاب طلب الجامعة والمقررات المرشح لتدريسها:</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6"><strong>رقم كتاب الجامعة:</strong> <span id="preview-reqNo"></span></div>
                                <div class="col-md-6"><strong>تاريخ كتاب الجامعة:</strong> <span id="preview-reqDate"></span></div>
                            </div>
                            <strong>المقررات المقترحة لتدريسها:</strong>
                            <div class="table-responsive mt-2">
                                <table class="table mohe-table text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th>اسم المقرر الدراسي</th>
                                            <th>الكلية</th>
                                            <th>القسم</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-courses-tbody">
                                        <!-- Will be dynamically populated via JS -->
                                    </tbody>
                                </table>
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
            <div class="d-flex justify-content-between mt-5 pt-3 border-top" style="border-top-color: var(--outline-variant) !important;">
                <button type="button" class="btn btn-outline-navy px-4 py-2" id="btn-prev" onclick="changeStep(-1)" style="display: none;">
                    <i class="fa-solid fa-arrow-right me-1"></i> السابق
                </button>
                <div id="spacer-prev"></div> <!-- Spacer if step 1 -->

                <button type="button" class="btn btn-primary px-4 py-2" id="btn-next" onclick="changeStep(1)">
                    التالي <i class="fa-solid fa-arrow-left ms-1"></i>
                </button>

                <button type="submit" class="btn btn-gold-cta px-5 py-2" id="btn-submit" style="display: none;">
                    إنهاء وإرسال الطلب للوزارة <i class="fa-solid fa-paper-plane ms-1"></i>
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 7;
    const syriaCountryId = "{{ $syriaId }}";

    // Initialize course row counter
    let courseCount = 1;

    function addCourseRow() {
        const tbody = document.getElementById('courses-tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" name="courses[${courseCount}][name]" class="form-control form-control-sm academic-input course-name-input" placeholder="مثال: معمارية الحاسب" required>
            </td>
            <td>
                <input type="text" name="courses[${courseCount}][faculty]" class="form-control form-control-sm academic-input course-faculty-input" placeholder="مثال: هندسة المعلوماتية" required>
            </td>
            <td>
                <input type="text" name="courses[${courseCount}][department]" class="form-control form-control-sm academic-input course-dept-input" placeholder="مثال: قسم البرمجيات" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCourseRow(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        courseCount++;
    }

    function removeCourseRow(btn) {
        const tbody = document.getElementById('courses-tbody');
        if (tbody.rows.length > 1) {
            btn.closest('tr').remove();
        }
    }

    // Toggle experience details
    function toggleExperienceSection(checkbox) {
        const expSection = document.getElementById('experience-details-section');
        const inputs = expSection.querySelectorAll('input');
        const fileInputs = document.querySelectorAll('.exp-conditional-file');

        if (checkbox.checked) {
            expSection.style.display = 'flex';
            inputs.forEach(input => input.required = true);
            fileInputs.forEach(div => {
                div.style.display = 'block';
                div.querySelector('input').required = true;
            });
        } else {
            expSection.style.display = 'none';
            inputs.forEach(input => {
                input.required = false;
                input.value = '';
            });
            fileInputs.forEach(div => {
                div.style.display = 'none';
                div.querySelector('input').required = false;
            });
        }
    }

    // Toggle High School Decision inputs
    function toggleHsCountrySection(select) {
        const section = document.getElementById('hs-equivalence-section');
        const input = document.getElementById('input-hsDecisionNo');
        const fileContainer = document.getElementById('hs-decision-file-container');
        const fileInput = fileContainer.querySelector('input');

        if (select.value != syriaCountryId) {
            section.style.display = 'block';
            input.required = true;
            fileContainer.style.display = 'block';
            fileInput.required = true;
        } else {
            section.style.display = 'none';
            input.required = false;
            input.value = '';
            fileContainer.style.display = 'none';
            fileInput.required = false;
        }
    }

    // Toggle Bachelor's Decision inputs
    function toggleBaCountrySection(select) {
        const selectContainer = document.getElementById('ba-uni-select-container');
        const textContainer = document.getElementById('ba-uni-text-container');
        const section = document.getElementById('ba-equivalence-section');
        const input = document.getElementById('input-baDecisionNo');
        const fileContainer = document.getElementById('ba-decision-file-container');
        const fileInput = fileContainer.querySelector('input');

        if (select.value == syriaCountryId) {
            selectContainer.style.display = 'block';
            selectContainer.querySelector('select').required = true;
            textContainer.style.display = 'none';
            textContainer.querySelector('input').required = false;
            textContainer.querySelector('input').value = '';
            section.style.display = 'none';
            input.required = false;
            input.value = '';
            fileContainer.style.display = 'none';
            fileInput.required = false;
        } else {
            selectContainer.style.display = 'none';
            selectContainer.querySelector('select').required = false;
            selectContainer.querySelector('select').value = '';
            textContainer.style.display = 'block';
            textContainer.querySelector('input').required = true;
            section.style.display = 'block';
            input.required = true;
            fileContainer.style.display = 'block';
            fileInput.required = true;
        }
    }

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
        if (currentStep === 7) {
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
            btnSubmit.style.display = 'block';
        } else {
            btnNext.style.display = 'block';
            btnSubmit.style.display = 'none';
        }
    }

    function updateReportPreview() {
        // Personal details
        document.getElementById('preview-fullName').innerText = document.getElementById('input-fullName').value;
        document.getElementById('preview-fatherName').innerText = document.getElementById('input-fatherName').value;
        document.getElementById('preview-motherName').innerText = document.getElementById('input-motherName').value;
        
        const nationalitySelect = document.getElementById('input-nationality');
        document.getElementById('preview-nationalId').innerText = document.getElementById('input-nationalId').value;
        document.getElementById('preview-dob').innerText = document.getElementById('input-dob').value;
        document.getElementById('preview-jobTitle').innerText = document.getElementById('input-jobTitle').value;
        document.getElementById('preview-gender').innerText = document.getElementById('input-gender').value;
        document.getElementById('preview-email').innerText = document.getElementById('input-email').value;
        document.getElementById('preview-mobile').innerText = document.getElementById('input-mobile').value;
        document.getElementById('preview-address').innerText = document.getElementById('input-address').value;

        // HS details
        const hsCountrySelect = document.getElementById('input-hsCountry');
        document.getElementById('preview-hsCountry').innerText = hsCountrySelect.options[hsCountrySelect.selectedIndex].text;
        document.getElementById('preview-hsType').innerText = document.getElementById('input-hsType').value;
        document.getElementById('preview-hsDate').innerText = document.getElementById('input-hsDate').value;
        
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
        document.getElementById('preview-baRegDate').innerText = document.getElementById('input-baRegDate').value;
        document.getElementById('preview-baGrantDate').innerText = document.getElementById('input-baGrantDate').value;

        // MA details
        const maUniSelect = document.getElementById('input-maUniId');
        document.getElementById('preview-maUni').innerText = maUniSelect.options[maUniSelect.selectedIndex].text;
        document.getElementById('preview-maFaculty').innerText = document.getElementById('input-maFaculty').value;
        document.getElementById('preview-maDept').innerText = document.getElementById('input-maDept').value;
        document.getElementById('preview-maRank').innerText = document.getElementById('input-maRank').value;
        document.getElementById('preview-maSupervisor').innerText = document.getElementById('input-maSupervisor').value;
        document.getElementById('preview-maRegDate').innerText = document.getElementById('input-maRegDate').value;
        document.getElementById('preview-maDefDate').innerText = document.getElementById('input-maDefDate').value;
        document.getElementById('preview-maGrantDate').innerText = document.getElementById('input-maGrantDate').value;
        document.getElementById('preview-maThesisTitle').innerText = document.getElementById('input-maThesisTitle').value;

        // Experience
        const hasExp = document.getElementById('input-hasExperience').checked;
        if (hasExp) {
            document.getElementById('preview-experience-container').style.display = 'block';
            document.getElementById('preview-expPlace').innerText = document.getElementById('input-expPlace').value;
            document.getElementById('preview-expFrom').innerText = document.getElementById('input-expFrom').value;
            document.getElementById('preview-expTo').innerText = document.getElementById('input-expTo').value;
        } else {
            document.getElementById('preview-experience-container').style.display = 'none';
        }

        // Request & Courses
        document.getElementById('preview-reqNo').innerText = document.getElementById('input-reqNo').value;
        document.getElementById('preview-reqDate').innerText = document.getElementById('input-reqDate').value;

        // Populate course preview table
        const previewCoursesTbody = document.getElementById('preview-courses-tbody');
        previewCoursesTbody.innerHTML = '';
        
        const names = document.querySelectorAll('.course-name-input');
        const faculties = document.querySelectorAll('.course-faculty-input');
        const departments = document.querySelectorAll('.course-dept-input');

        names.forEach((nameInput, index) => {
            const nameVal = nameInput.value;
            const facVal = faculties[index] ? faculties[index].value : '';
            const deptVal = departments[index] ? departments[index].value : '';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${nameVal}</td>
                <td>${facVal}</td>
                <td>${deptVal}</td>
            `;
            previewCoursesTbody.appendChild(row);
        });
    }

    function toggleCandidateLookup(show) {
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
</script>
@endpush

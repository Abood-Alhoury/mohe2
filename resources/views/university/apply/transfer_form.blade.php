@extends('layouts.university')

@section('title', 'نموذج تحويل قرار المعادلة')

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" class="text-decoration-none text-muted">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" class="text-decoration-none text-muted">خيارات التعادل</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.transfer') }}" class="text-decoration-none text-muted">البحث عن قرار سابق</a></li>
                <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">تقديم طلب تحويل المعادلة</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-right-left me-2" style="color: var(--heritage-gold);"></i>
            نموذج تحويل قرار المعادلة ونقل التكليف المؤسسي
        </h3>
    </div>
</div>

<!-- Institutional Informational Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1A2A44 0%, #0F1A2C 100%); border-radius: 14px; border-right: 5px solid var(--heritage-gold) !important;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold" style="border-radius: 6px;">خدمة نقل التكليف</span>
                    <span class="badge bg-white text-primary px-3 py-1 fw-bold" style="border-radius: 6px;">معاملة سابقة صادرة: #{{ $parentApp->application_no }}</span>
                </div>
                <h4 class="fw-bold mb-2 text-white">تحويل قرار المعادلة ونقل التكليف بين الجامعات</h4>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    تعديل جهة التكليف والمقررات الدراسية للمرشح المكلّف الصادر بحقه قرار معادلة سابق بعد إنهاء تكليفه بالجامعة السابقة.
                </p>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                <div class="p-3 d-inline-block rounded-circle" style="background: rgba(197,160,89,0.15); border: 2px solid var(--heritage-gold);">
                    <i class="fa-solid fa-right-left text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Display Validation Errors -->
@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4 p-3" style="border-radius: 12px; border-right: 5px solid #EF4444 !important;">
        <div class="fw-bold mb-2"><i class="fa-solid fa-circle-exclamation me-1"></i> يرجى تصحيح الملاحظات والأخطاء التالية للتمكن من التقديم:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('university.apply.transfer.submit') }}" method="POST" enctype="multipart/form-data" id="transferForm" x-data="courseManager()">
    @csrf
    <input type="hidden" name="parent_application_id" value="{{ $parentApp->id }}">

    <!-- SECTION 1: LOCKED & READ-ONLY CANDIDATE & DECISION DATA -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid var(--heritage-gold) !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-lock text-warning me-2"></i>
                1. البيانات الشخصية وقرار المعادلة الصادر السابق (معلومات مقفلة ومحميّة)
            </h5>
            <span class="badge bg-secondary-subtle text-dark border px-3 py-1 fw-bold">
                <i class="fa-solid fa-shield-halved me-1"></i> محميّة قانونياً
            </span>
        </div>
        <div class="card-body p-4" style="background-color: #FAF9F6;">
            <!-- Candidate Info Summary Box -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الاسم الكامل للمرشح:</span>
                        <strong class="fs-6" style="color: var(--imperial-navy);">{{ $parentApp->candidate->full_name }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الرقم الوطني:</span>
                        <strong class="fs-6 font-monospace" style="color: var(--imperial-navy);">{{ $parentApp->candidate->national_id }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الجنسية والنوع الاجتماعي:</span>
                        <strong class="fs-6">{{ optional($parentApp->candidate->nationality)->name ?? 'سوري' }} ({{ $parentApp->candidate->gender ?? 'ذكر' }})</strong>
                    </div>
                </div>
            </div>

            <!-- Previous Decision Details Box -->
            @php
                $decision = $parentApp->latestDecision;
                $primaryEd = $parentApp->educations->first();
            @endphp
            <div class="p-3.5 rounded-3 mb-2" style="background: #FFFBF0; border: 1.5px solid #F3E8C9;">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-certificate text-warning fs-3"></i>
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-1">قرار المعادلة السابق الصادر</span>
                                <div class="fw-bold text-dark fs-6">
                                    رقم القرار: <span class="font-monospace text-primary">{{ $decision->decision_no ?? 'صادر رسمياً' }}</span>
                                    @if($decision && $decision->decision_date)
                                        | تاريخ الصدور: <span class="text-muted">{{ $decision->decision_date }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 border-start ps-4">
                        <span class="text-muted d-block fs-8">الجامعة والجهة السابقة المكلّف بها:</span>
                        <strong class="text-dark">{{ optional($parentApp->workUniversity)->name ?? 'جامعة سابقة' }} - {{ $parentApp->work_faculty ?? '' }} ({{ $parentApp->work_department ?? '' }})</strong>
                        @if($primaryEd)
                            <div class="text-muted small mt-1">
                                الشهادة المعتمدة: {{ optional($primaryEd->level)->name }} من {{ optional($primaryEd->university)->name }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <small class="text-muted d-block mt-2"><i class="fa-solid fa-info-circle me-1"></i> لا يتاح تعديل البيانات الشخصية أو بيانات الشهادات السابقة للحفاظ على حجية القرار الوزاري الصادر أصولاً.</small>
        </div>
    </div>

    <!-- SECTION 2: EDITABLE NEW UNIVERSITY & ASSIGNMENT DETAILS -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid #3B82F6 !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-building-columns text-primary me-2"></i>
                2. بيانات التكليف والجامعة الجديدة (متاحة للتعديل والنقل)
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fw-bold">
                الجهة الجديدة الطالبة للتعادل
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Target University Auto-read from Account -->
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">
                        الجامعة الجديدة (الجهة مقدمة الطلب) <span class="text-danger">*</span>:
                    </label>
                    <input type="hidden" name="work_university_id" value="{{ Auth::user()->university_id }}">
                    <input type="text" 
                           class="form-control bg-light fw-bold text-primary" 
                           value="{{ optional(Auth::user()->university)->name ?? 'جامعتك الحالية' }}" 
                           readonly>
                    <small class="text-muted fs-8">يتم اعتماد جهة التحويل تلقائياً من حساب الجامعة الحالي.</small>
                </div>

                <!-- New Faculty -->
                <div class="col-md-4">
                    <label for="work_faculty" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        الكلية الجديدة <span class="text-danger">*</span>:
                    </label>
                    <input type="text" 
                           name="work_faculty" 
                           id="work_faculty" 
                           class="form-control" 
                           placeholder="مثال: كلية الهندسة المعلوماتية" 
                           value="{{ old('work_faculty', $parentApp->work_faculty) }}" 
                           required>
                </div>

                <!-- New Department -->
                <div class="col-md-4">
                    <label for="work_department" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        القسم الجديد <span class="text-danger">*</span>:
                    </label>
                    <input type="text" 
                           name="work_department" 
                           id="work_department" 
                           class="form-control" 
                           placeholder="مثال: قسم البرمجيات ونظم المعلومات" 
                           value="{{ old('work_department', $parentApp->work_department) }}" 
                           required>
                </div>

                <!-- New University Assignment Letter Number -->
                <div class="col-md-6">
                    <label for="new_uni_request_no" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        رقم كتاب الجامعة الجديدة الصادر بالتكليف <span class="text-danger">*</span>:
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag text-muted"></i></span>
                        <input type="text" 
                               name="new_uni_request_no" 
                               id="new_uni_request_no" 
                               class="form-control" 
                               placeholder="مثال: 1245/ص أ" 
                               value="{{ old('new_uni_request_no') }}" 
                               required>
                    </div>
                </div>

                <!-- New University Assignment Letter Date -->
                <div class="col-md-6">
                    <label for="new_uni_request_date" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        تاريخ كتاب الجامعة الجديدة <span class="text-danger">*</span>:
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-regular fa-calendar text-muted"></i></span>
                        <input type="date" 
                               name="new_uni_request_date" 
                               id="new_uni_request_date" 
                               class="form-control" 
                               value="{{ old('new_uni_request_date', date('Y-m-d')) }}" 
                               required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: COURSE SUBJECTS MANAGEMENT (SIMPLE ADD / REMOVE COURSES) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid #10B981 !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-book-open text-success me-2"></i>
                    3. المقررات الدراسية (إضافة وحذف المواد)
                </h5>
                <small class="text-muted">يمكنك إضافة مقررات جديدة أو حذف مقررات سابقة حسب متطلبات الجامعة الجديدة.</small>
            </div>
            <button type="button" @click="addCourse()" class="btn btn-sm btn-success fw-bold px-3" style="border-radius: 6px;">
                <i class="fa-solid fa-plus me-1"></i> إضافة مقرر دراسي جديد
            </button>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th>اسم المقرر الدراسي (Course Name)</th>
                            <th style="width: 100px;" class="text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(course, index) in courses" :key="index">
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted" x-text="index + 1"></td>
                                <td>
                                    <input type="text" 
                                           :name="'courses[' + index + '][course_name]'" 
                                           x-model="course.course_name" 
                                           class="form-control form-control-sm" 
                                           placeholder="أدخل اسم المادة أو المقرر الدراسي" 
                                           required>
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removeCourse(index)" class="btn btn-sm btn-outline-danger border-0" title="حذف المقرر">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty state when no courses -->
                        <tr x-show="courses.length === 0">
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-open fs-3 d-block mb-2 text-secondary"></i>
                                لا توجد مقررات دراسية مضافة حالياً. انقر على زر <strong>"إضافة مقرر دراسي جديد"</strong> لأعلى لإضافة المواد.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECTION 4: THE 3 MANDATORY ATTACHMENTS FOR TRANSFER -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid #8B5CF6 !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-paperclip text-purple me-2" style="color: #8B5CF6;"></i>
                    4. المرفقات والوثائق المطلوبة لطلب تحويل المعادلة (ثلاث وثائق أساسية)
                </h5>
                <small class="text-muted">جميع الوثائق الثلاث إجبارية ويجب رفعها بصيغة PDF أو صور واضحة (بحجم أقصى 10 ميغابايت لكل ملف).</small>
            </div>
            <span class="badge bg-purple-subtle text-purple border px-3 py-1 fw-bold" style="color: #8B5CF6; border-color: #DDD6FE !important; background: #F3E8FF;">
                3 وثائق رسمية
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Attachment 1: Cancellation Decision -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 bg-light" style="border-right: 4px solid #EF4444 !important;">
                        <label for="file_cancellation" class="form-label fw-bold" style="color: var(--imperial-navy);">
                            <i class="fa-solid fa-file-circle-xmark text-danger me-1"></i>
                            1. قرار إنهاء/إلغاء التكليف من الجامعة السابقة <span class="text-danger">*</span>:
                        </label>
                        <input type="file" name="file_cancellation" id="file_cancellation" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-1 fs-8">صورة قرار إنهاء أو إلغاء التكليف الصادر عن الجامعة السابقة.</small>
                    </div>
                </div>

                <!-- Attachment 2: Equivalence Fee Payment Receipt -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 bg-light" style="border-right: 4px solid #10B981 !important;">
                        <label for="file_payment" class="form-label fw-bold" style="color: var(--imperial-navy);">
                            <i class="fa-solid fa-receipt text-success me-1"></i>
                            2. صورة إشعار/وصل تسديد رسوم التعادل للتحويل <span class="text-danger">*</span>:
                        </label>
                        <input type="file" name="file_payment" id="file_payment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-1 fs-8">إيصال تسديد رسم تعادل تحويل قرار المعادلة في البنك أو المالية.</small>
                    </div>
                </div>

                <!-- Attachment 3: New University Assignment Request Letter -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100 bg-light" style="border-right: 4px solid #3B82F6 !important;">
                        <label for="file_new_uni_request" class="form-label fw-bold" style="color: var(--imperial-navy);">
                            <i class="fa-solid fa-file-signature text-primary me-1"></i>
                            3. كتاب الجامعة الجديدة للتكليف الجديد والطلب <span class="text-danger">*</span>:
                        </label>
                        <input type="file" name="file_new_uni_request" id="file_new_uni_request" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-1 fs-8">كتاب الجامعة الجديدة الصادر يطلب تحويل قرار المعادلة وتكليفه لديها.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 5: ACTION BUTTONS -->
    <div class="card border-0 shadow-sm mb-5" style="border-radius: 14px;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <a href="{{ route('university.apply.transfer') }}" class="btn btn-outline-secondary px-4 fw-bold">
                <i class="fa-solid fa-arrow-right me-1"></i> إلغاء والعودة
            </a>

            <div>
                <button type="submit" name="action" value="submit" class="btn btn-primary px-5 py-2.5 fw-bold fs-6 shadow-sm" style="background: var(--imperial-navy); border-color: var(--imperial-navy); border-radius: 8px;">
                    <i class="fa-solid fa-paper-plane me-2" style="color: var(--heritage-gold);"></i>
                    إرسال طلب تحويل المعادلة رسمياً إلى الوزارة
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Alpine JS Course Subject Manager Script -->
<script>
    function courseManager() {
        return {
            courses: @json($initialCourses),
            addCourse() {
                this.courses.push({
                    course_name: ''
                });
            },
            removeCourse(index) {
                this.courses.splice(index, 1);
            }
        }
    }
</script>

@endsection

@extends('layouts.university')

@section('title', 'نموذج تقديم طلب إضافة مقررات دراسية جديدة')

@section('content')

<!-- Header Breadcrumb & Title -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" class="text-decoration-none text-muted">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.options') }}" class="text-decoration-none text-muted">خيارات التعادل</a></li>
                <li class="breadcrumb-item"><a href="{{ route('university.apply.add_courses') }}" class="text-decoration-none text-muted">البحث بالرقم الوطني</a></li>
                <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">تقديم طلب إضافة المقررات</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-book-medical me-2" style="color: var(--heritage-gold);"></i>
            نموذج تقديم طلب إضافة مقررات دراسية جديدة
        </h3>
    </div>
</div>

<!-- Institutional Informational Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1A2A44 0%, #0F1A2C 100%); border-radius: 14px; border-right: 5px solid var(--heritage-gold) !important;">
    <div class="card-body p-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold" style="border-radius: 6px;">خدمة إضافة المقررات</span>
                    <span class="badge bg-white text-primary px-3 py-1 fw-bold" style="border-radius: 6px;">معاملة سابقة صادرة: #{{ $parentApp->application_no }}</span>
                </div>
                <h4 class="fw-bold mb-2 text-white">تقديم طلب إضافة مواد ومقررات دراسية جديدة</h4>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    إضافة أسطر ومواد دراسية جديدة للدكتور/المرشح المكلّف لدى جامعتكم وتسديد الرسم المقرر لدى الوزارة.
                </p>
            </div>
            <div class="col-md-3 text-center d-none d-md-block">
                <div class="p-3 d-inline-block rounded-circle" style="background: rgba(197,160,89,0.15); border: 2px solid var(--heritage-gold);">
                    <i class="fa-solid fa-book-medical text-warning" style="font-size: 3rem;"></i>
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

<!-- MAIN FORM -->
<form action="{{ route('university.apply.add_courses.submit') }}" method="POST" enctype="multipart/form-data" x-data="addCoursesManager()">
    @csrf
    <input type="hidden" name="parent_application_id" value="{{ $parentApp->id }}">

    <!-- SECTION 1: CANDIDATE & UNIVERSITY LOCKED INFORMATION -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid var(--heritage-gold) !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-user-lock text-warning me-2"></i>
                1. بيانات المرشح والتكليف الحالي في الجامعة (مقفلة ومسجلة رسمياً)
            </h5>
            <span class="badge bg-secondary-subtle text-dark border px-3 py-1 fw-bold">
                <i class="fa-solid fa-shield-halved me-1"></i> مبيّنة أصولاً
            </span>
        </div>
        <div class="card-body p-4" style="background-color: #FAF9F6;">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">اسم المرشح الكامل:</span>
                        <strong class="fs-6" style="color: var(--imperial-navy);">{{ optional($parentApp->candidate)->full_name }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الرقم الوطني:</span>
                        <strong class="fs-6 font-monospace" style="color: var(--imperial-navy);">{{ optional($parentApp->candidate)->national_id }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الجنسية والوظيفة:</span>
                        <strong class="fs-6">{{ optional($parentApp->candidate)->is_syrian ? 'سورية' : 'غير سورية' }} ({{ optional($parentApp->candidate)->job_title }})</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الجامعة المكلف بها:</span>
                        <strong class="fs-6 text-primary fw-bold">{{ optional($parentApp->workUniversity)->name }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">الكلية المعنية:</span>
                        <strong class="fs-6" style="color: var(--imperial-navy);">{{ $parentApp->work_faculty }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded border shadow-2xs">
                        <span class="text-muted d-block fs-8 mb-1">القسم المعني:</span>
                        <strong class="fs-6" style="color: var(--imperial-navy);">{{ $parentApp->work_department }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: EXISTING PREVIOUS COURSES TABLE (READ-ONLY) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid var(--imperial-navy) !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>
                2. المقررات الدراسية السابقة الصادر بها القرار (للمعاينة فقط)
            </h5>
            <span class="badge bg-light text-dark border px-3 py-1 fw-bold">مقرات سابقة</span>
        </div>
        <div class="card-body p-4">
            @if($parentApp->courses && $parentApp->courses->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead style="background: var(--imperial-navy); color: white;">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>اسم المقرر الدراسي (في القرار السابق)</th>
                            <th style="width: 150px;" class="text-center">حالة المقرر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parentApp->courses as $idx => $prevCourse)
                        <tr>
                            <td class="text-center font-monospace fw-bold">{{ $idx + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $prevCourse->course_name }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border px-2.5 py-1.5 fs-8">{{ $prevCourse->course_status ?? 'مستوفى' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-muted small">لا توجد مقررات دراسية مسجلة في القرار السابق.</div>
            @endif
        </div>
    </div>

    <!-- SECTION 3: ADD NEW COURSES MANAGER (إضافة مقررات جديدة) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid #10B981 !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-plus-circle text-success me-2"></i>
                    3. المقررات الدراسية الجديدة المراد إضافتها (إضافة مواد فقط)
                </h5>
                <small class="text-muted">قم بالنقر على زر <strong>"إضافة مقرر دراسي جديد"</strong> لإدراج المواد الجديدة المراد تكليفه بتدريسها.</small>
            </div>
            <button type="button" @click="addCourse()" class="btn btn-sm btn-success fw-bold px-3 shadow-2xs" style="border-radius: 6px;">
                <i class="fa-solid fa-plus me-1"></i> إضافة مقرر دراسي جديد
            </button>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border mb-0">
                    <thead style="background: #065F46; color: white;">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th>اسم المقرر الدراسي الجديد (New Course Name)</th>
                            <th style="width: 100px;" class="text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(course, index) in newCourses" :key="index">
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted" x-text="index + 1"></td>
                                <td>
                                    <input type="text" 
                                           :name="'courses[' + index + '][course_name]'" 
                                           x-model="course.course_name" 
                                           class="form-control" 
                                           placeholder="أدخل اسم المادة أو المقرر الدراسي الجديد" 
                                           required>
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removeCourse(index)" class="btn btn-sm btn-outline-danger border-0" title="حذف السطر">
                                        <i class="fa-solid fa-trash-can fs-6"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="newCourses.length === 0">
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-plus fs-3 d-block mb-2 text-success"></i>
                                انقر على زر <strong>"إضافة مقرر دراسي جديد"</strong> لأعلى لإدراج أسماء المواد المراد إضافتها للدكتور.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECTION 4: SINGLE MANDATORY ATTACHMENT (إيصال تسديد الرسم) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden; border-top: 4px solid var(--heritage-gold) !important;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>
                    4. الوثيقة المطلوبة لطلب إضافة المقررات (مرفق واحد فقط)
                </h5>
                <small class="text-muted">مطلوب رفع إشعار/وصل تسديد رسم إضافة المقررات الدراسية بصيغة PDF أو صورة واضحة.</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border px-3 py-1 fw-bold">وثيقة إجبارية واحدة</span>
        </div>
        <div class="card-body p-4">
            <div class="col-md-8 col-lg-7">
                <div class="p-3 rounded-3 border bg-light" style="border-right: 4px solid #10B981 !important;">
                    <label for="file_payment" class="form-label fw-bold" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-receipt text-success me-1"></i>
                        صورة إشعار/وصل تسديد رسوم إضافة المقررات <span class="text-danger">*</span>:
                    </label>
                    <input type="file" name="file_payment" id="file_payment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted d-block mt-1 fs-8">إيصال تسديد رسم تعادل إضافة المقررات الدراسية لدى البنك أو المالية.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 5: SUBMIT ACTION BUTTON -->
    <div class="card border-0 shadow-sm mb-5" style="border-radius: 14px;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <a href="{{ route('university.apply.add_courses') }}" class="btn btn-outline-secondary px-4 fw-bold">
                <i class="fa-solid fa-arrow-right me-1"></i> إلغاء والعودة
            </a>

            <div>
                <button type="submit" class="btn btn-solid-navy px-5 py-2.5 fw-bold fs-6 shadow-sm">
                    <i class="fa-solid fa-paper-plane me-2" style="color: var(--heritage-gold);"></i>
                    إرسال طلب إضافة المقررات رسمياً إلى الوزارة
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Alpine JS Add Courses Script -->
<script>
    function addCoursesManager() {
        return {
            newCourses: [
                { course_name: '' }
            ],
            addCourse() {
                this.newCourses.push({
                    course_name: ''
                });
            },
            removeCourse(index) {
                this.newCourses.splice(index, 1);
            }
        }
    }
</script>
@endsection

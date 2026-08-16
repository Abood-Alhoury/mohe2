@extends('layouts.admin')

@section('title', 'تعديل البيانات والشهادات للمرشح - ' . ($candidate->full_name ?? ''))

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- TOP HEADER & APPLICATION SUMMARY SLAB -->
<div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header py-3 px-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: var(--imperial-navy);">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 bg-white bg-opacity-10 rounded-circle border" style="border-color: var(--heritage-gold) !important;">
                <i class="fa-solid fa-user-pen fs-4" style="color: var(--heritage-gold-light);"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-white" style="font-size: 1.25rem;">
                    تعديل بيانات الطلب والمرشح: <span style="color: var(--heritage-gold-light);">{{ $candidate->full_name ?? 'غير محدد' }}</span>
                </h5>
                <span class="fs-8 text-white-50">إدارة وتحديث البيانات الرسمية وكتاب الجامعة الخاصة والشهادات المرفقة</span>
            </div>
        </div>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-gold-cta btn-sm px-3 fw-bold shadow-sm">
            <i class="fa-solid fa-arrow-right me-1"></i> عودة لجدول الطلبات
        </a>
    </div>

    <div class="card-body p-3 bg-light border-bottom">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-2">
                <span class="text-muted fs-8 d-block fw-bold">ID المرشح</span>
                <span class="fw-bold fs-6 text-dark">#{{ $candidate->id ?? '' }}</span>
            </div>
            <div class="col-6 col-md-2">
                <span class="text-muted fs-8 d-block fw-bold">الجامعة الطالبة</span>
                <span class="fw-bold fs-6 text-primary">{{ $application->workUniversity->name ?? 'غير محددة' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">نوع الطلب المعالج</span>
                <span class="badge bg-white text-dark border px-2 py-1 fs-7 fw-bold shadow-sm">{{ $application->request_type }}</span>
            </div>
            <div class="col-6 col-md-2">
                <span class="text-muted fs-8 d-block fw-bold">رقم المعاملة</span>
                <span class="fw-bold fs-6 text-success">{{ $application->application_no }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">حالة الطلب الحالية</span>
                @if($application->status == 'بانتظار الوثائق')
                    <span class="badge-status badge-paper fs-7 px-3 py-1.5 fw-bold"><i class="fa-solid fa-file-circle-exclamation me-1"></i> {{ $application->status }}</span>
                @elseif($application->status == 'تم الصدور')
                    <span class="badge-status badge-approved fs-7 px-3 py-1.5 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> {{ $application->status }}</span>
                @else
                    <span class="badge-status badge-study fs-7 px-3 py-1.5 fw-bold">{{ $application->status }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: بيانات كتاب طلب التقييم الصادر عن الجامعة الخاصة -->
<div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">
            <i class="fa-solid fa-file-signature me-2" style="color: var(--heritage-gold);"></i> 1. بيانات كتاب طلب التقييم الصادر عن الجامعة الخاصة :
        </h6>
        <span class="badge bg-gold-subtle text-dark border border-warning px-2.5 py-1 fw-bold fs-8">كتاب الجامعة والترشيح</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_details', $application->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم كتاب الجامعة الخاصة :</label>
                    <input type="text" name="new_uni_request_no" class="form-control fw-bold" value="{{ old('new_uni_request_no', $application->new_uni_request_no) }}" placeholder="مثال: 1234/ص">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ كتاب الجامعة الخاصة :</label>
                    <input type="date" name="new_uni_request_date" class="form-control" value="{{ old('new_uni_request_date', $application->new_uni_request_date) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة الخاصة الطالبة للتقويم :</label>
                    <select name="work_university_id" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $application->work_university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-gold-cta px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات كتاب الجامعة الخاصة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION 2: البيانات الشخصية للمرشح -->
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--imperial-navy) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">
            <i class="fa-solid fa-address-card me-2" style="color: var(--heritage-gold);"></i> 2. البيانات الشخصية وبيانات الاتصال للمرشح :
        </h6>
        <span class="badge bg-light text-secondary border">بيانات المتقدم الرسمية</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_candidate', $application->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">اسم المرشح الكامل :</label>
                    <input type="text" name="full_name" class="form-control fw-bold" value="{{ $candidate->full_name }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">اسم الأب :</label>
                    <input type="text" name="father_name" class="form-control" value="{{ $candidate->father_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">اسم الأم ونسبتها :</label>
                    <input type="text" name="mother_name" class="form-control" value="{{ $candidate->mother_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الرقم الوطني :</label>
                    <input type="text" name="national_id" class="form-control fw-bold" value="{{ $candidate->national_id }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجنسية :</label>
                    <input type="text" class="form-control bg-light" value="{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجنس :</label>
                    <select name="gender" class="form-select">
                        <option value="ذكر" {{ $candidate->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                        <option value="أنثى" {{ $candidate->gender == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ الميلاد :</label>
                    <input type="date" name="dob" class="form-control" value="{{ $candidate->dob }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">البريد الإلكتروني :</label>
                    <input type="email" name="email" class="form-control" value="{{ $candidate->email }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">العنوان ومكان الإقامة :</label>
                    <input type="text" name="address" class="form-control" value="{{ $candidate->address }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الوظيفة / الصفة :</label>
                    <input type="text" name="job_title" class="form-control" value="{{ $candidate->job_title }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم الهاتف الأرضي :</label>
                    <input type="text" name="phone" class="form-control" value="{{ $candidate->phone }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم الجوال :</label>
                    <input type="text" name="mobile" class="form-control fw-bold" value="{{ $candidate->mobile }}">
                </div>
            </div>
            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات البيانات الشخصية
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SECTION 3: بيانات التعيين والجامعة الحكومية السورية (لعضو هيئة تدريس - سماح بالتدريس) --}}
@if($isFacultyPermission || $govEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid #0D9488 !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-teal text-white fs-7 px-2.5 py-1" style="background-color: #0D9488;"><i class="fa-solid fa-building-columns me-1"></i> التعيين الحكومي</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">بيانات الجامعة الحكومية السورية والصفة الوظيفية</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات التعيين بالجامعة الحكومية</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @if($govEd)
                <input type="hidden" name="education_id" value="{{ $govEd->id }}">
                <input type="hidden" name="education_level_id" value="{{ $govEd->education_level_id }}">
            @else
                <input type="hidden" name="education_level_id" value="3">
                <input type="hidden" name="thesis_title" value="عضو هيئة تدريسية في جامعة حكومية">
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة الحكومية السورية التابع لها :</label>
                    <select name="university_id" class="form-select">
                        <option value="">-- اختر الجامعة الحكومية السورية --</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ optional($govEd)->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الرتبة الأكاديمية بالجامعة الحكومية :</label>
                    <select name="rank" class="form-select fw-bold">
                        <option value="مدرس" {{ optional($govEd)->rank == 'مدرس' ? 'selected' : '' }}>مدرس</option>
                        <option value="أستاذ مساعد" {{ optional($govEd)->rank == 'أستاذ مساعد' ? 'selected' : '' }}>أستاذ مساعد</option>
                        <option value="أستاذ" {{ optional($govEd)->rank == 'أستاذ' ? 'selected' : '' }}>أستاذ</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الكلية التابع لها بالجامعة الحكومية :</label>
                    <input type="text" name="faculty" class="form-control" value="{{ optional($govEd)->faculty ?: optional($govEd)->general_specialization }}" placeholder="مثال: كلية الهندسة المدنية">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">القسم التابع له بالجامعة الحكومية :</label>
                    <input type="text" name="department" class="form-control" value="{{ optional($govEd)->department ?: optional($govEd)->exact_specialization }}" placeholder="مثال: قسم الإنشاءات">
                </div>
            </div>

            @if($govEd && $govEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> بيان الوضع والوثائق المرفوعة للتعيين الحكومي:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($govEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'بيان وضع وظيفي' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / بيان وضع وظيفي جديد :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات التعيين الحكومي
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- SECTION 4: الشهادات والمؤهلات الأكاديمية -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.1rem;">
        <i class="fa-solid fa-graduation-cap me-2" style="color: var(--heritage-gold);"></i> 4. الشهادات والمؤهلات الأكاديمية والدرجات العلمية :
    </h6>
    <span class="text-muted fs-8">ملاحظة: يتم عرض وإتاحة تعديل المؤهلات المسجلة للمرشح</span>
</div>

{{-- 4.1 شهادة دكتوراه --}}
@if($phdEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold fs-7"><i class="fa-solid fa-star me-1"></i> المؤهل الأساسي (الدكتوراه)</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">شهادة درجة الدكتوراه</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الدكتوراه والجامعة المانحة</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $phdEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $phdEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة لشهادة الدكتوراه :</label>
                    <select name="university_id" class="form-select">
                        <option value="">-- اختر الجامعة المانحة --</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $phdEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ / سنة الحصول على الدكتوراه :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $phdEd->grant_date }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الكلية المانحة للدكتوراه :</label>
                    <input type="text" name="faculty" class="form-control" value="{{ $phdEd->faculty ?: $phdEd->general_specialization }}" placeholder="مثال: كلية الهندسة المدنية">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">القسم / الاختصاص الدقيق للدكتوراه :</label>
                    <input type="text" name="department" class="form-control" value="{{ $phdEd->department ?: $phdEd->exact_specialization }}" placeholder="مثال: الهندسة الإنشائية">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">عنوان أطروحة الدكتوراه :</label>
                    <input type="text" name="thesis_title" class="form-control fw-bold" style="color: var(--imperial-navy);" value="{{ $phdEd->thesis_title == 'شهادة الدكتوراه' ? '' : $phdEd->thesis_title }}" placeholder="أدخل عنوان الأطروحة إن وجد">
                </div>
            </div>

            <!-- Attachments List for PhD -->
            @if($phdEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للدكتوراه:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($phdEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'وثيقة الدكتوراه' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / مرفق جديد للدكتوراه :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-gold-cta px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الدكتوراه
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 4.2 شهادة ماجستير (تظهر إذا تم إدخالها بالطلب) --}}
@if($masterEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary text-white fs-7">مؤهل سابِق/داعِم</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">شهادة درجة الماجستير</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الماجستير</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $masterEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $masterEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة للماجستير :</label>
                    <select name="university_id" class="form-select">
                        <option value="">-- اختر الجامعة --</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $masterEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $masterEd->grant_date }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الكلية المانحة :</label>
                    <input type="text" name="faculty" class="form-control" value="{{ $masterEd->faculty ?: $masterEd->general_specialization }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">القسم / الاختصاص :</label>
                    <input type="text" name="department" class="form-control" value="{{ $masterEd->department ?: $masterEd->exact_specialization }}">
                </div>
            </div>

            <!-- Attachments List for Master -->
            @if($masterEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للماجستير:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($masterEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الماجستير' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / مرفق جديد للماجستير :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الماجستير
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 4.3 الشهادة الجامعية الأولى (الإجازة) --}}
@if($bachelorEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white fs-7">مؤهل سابق</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">الشهادة الجامعية الأولى (الإجازة)</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الإجازة الجامعية</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $bachelorEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $bachelorEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الدولة المانحة :</label>
                    <select name="country_id" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $bachelorEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة :</label>
                    <select name="university_id" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $bachelorEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الكلية / الاختصاص العام :</label>
                    <input type="text" name="general_specialization" class="form-control" value="{{ $bachelorEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">القسم / الاختصاص الدقيق :</label>
                    <input type="text" name="exact_specialization" class="form-control" value="{{ $bachelorEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح / التخرج :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $bachelorEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">التقدير / المعدل :</label>
                    <input type="text" name="rank" class="form-control" value="{{ $bachelorEd->rank }}">
                </div>
            </div>

            <!-- Attachments List for Bachelor -->
            @if($bachelorEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للإجازة:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($bachelorEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الإجازة' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / مرفق جديد للإجازة :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الإجازة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- SECTION 5: جميع الوثائق والمرفقات الرسمية الخاصة بالطلب --}}
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--imperial-navy) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">
            <i class="fa-solid fa-folder-open me-2" style="color: var(--heritage-gold);"></i> 5. جميع الوثائق والمرفقات المرفوعة المعالجة في المعاملة :
        </h6>
        <span class="badge bg-light text-dark border">المستندات الرسمية</span>
    </div>
    <div class="card-body p-4 bg-white">
        @php
            $allAtts = collect();
            foreach($application->educations as $edItem) {
                foreach($edItem->attachments as $aItem) {
                    $allAtts->push($aItem);
                }
            }
        @endphp

        @if($allAtts->count() > 0)
            <div class="row g-3">
                @foreach($allAtts as $attObj)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-between" style="background-color: var(--surface-container-low, #f8fafc);">
                            <div class="mb-2">
                                <div class="fw-bold text-dark fs-7 mb-1">
                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                    {{ optional($attObj->attachmentType)->name ?? 'وثيقة مرفقة' }}
                                </div>
                                <div class="fs-8 text-muted">{{ $attObj->notes ?? 'لا توجد ملاحظات إضافية' }}</div>
                            </div>
                            <div class="pt-2 border-top text-end">
                                <a href="{{ asset('storage/' . $attObj->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 fs-8 fw-bold">
                                    <i class="fa-solid fa-eye me-1"></i> معاينة واستعراض PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                لا توجد مرفقات مسجلة حالياً لهذا الطلب.
            </div>
        @endif
    </div>
</div>

@endsection

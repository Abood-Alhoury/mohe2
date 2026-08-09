@extends('layouts.admin')

@section('title', 'تعديل البيانات والشهادات للمرشح - ' . ($candidate->full_name ?? ''))

@section('content')

<!-- TOP HEADER & APPLICATION SUMMARY SLAB -->
<div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header py-3 px-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: var(--imperial-navy);">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 bg-white bg-opacity-10 rounded-circle border" style="border-color: var(--heritage-gold) !important;">
                <i class="fa-solid fa-user-pen fs-4" style="color: var(--heritage-gold-light);"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-white" style="font-size: 1.25rem;">
                    تعديل البيانات الشخصية والشهادات للمرشح: <span style="color: var(--heritage-gold-light);">{{ $candidate->full_name ?? 'غير محدد' }}</span>
                </h5>
                <span class="fs-8 text-white-50">تعديل وقود البيانات المدخلة حصراً لهذا الطلب</span>
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
                <span class="text-muted fs-8 d-block fw-bold">اسم الجامعة</span>
                <span class="fw-bold fs-6 text-primary">{{ $application->workUniversity->name ?? 'غير محددة' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">نوع الطلب المعالج</span>
                <span class="badge bg-white text-dark border px-2 py-1 fs-7 fw-bold shadow-sm">{{ $application->request_type }}</span>
            </div>
            <div class="col-6 col-md-2">
                <span class="text-muted fs-8 d-block fw-bold">رقم الطلب</span>
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

<!-- SECTION 1: البيانات الشخصية للمرشح -->
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--imperial-navy) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">
            <i class="fa-solid fa-address-card me-2" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية وبيانات الاتصال :
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
                    <input type="text" name="full_name" class="form-control" value="{{ $candidate->full_name }}" required>
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
                    <input type="text" name="national_id" class="form-control" value="{{ $candidate->national_id }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجنسية :</label>
                    <input type="text" class="form-control bg-light" value="{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}" readonly>
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
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">العنوان الحالي :</label>
                    <input type="text" name="address" class="form-control" value="{{ $candidate->address }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الوظيفة :</label>
                    <input type="text" name="job_title" class="form-control" value="{{ $candidate->job_title }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم الهاتف الأرضي :</label>
                    <input type="text" name="phone" class="form-control" value="{{ $candidate->phone }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم الجوال :</label>
                    <input type="text" name="mobile" class="form-control" value="{{ $candidate->mobile }}">
                </div>
            </div>
            <div class="text-end border-top pt-3">
                <button type="submit" class="btn btn-gold-cta px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات البيانات الشخصية
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION 2: الشهادات والمؤهلات العلمية المدخلة حصراً لهذا الطلب -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.1rem;">
        <i class="fa-solid fa-graduation-cap me-2" style="color: var(--heritage-gold);"></i> 2. الشهادات والمؤهلات الأكاديمية المدخلة لهذا الطلب :
    </h6>
    <span class="text-muted fs-8">ملاحظة: يتم عرض وإتاحة تعديل المؤهلات التي أدخلها المتقدم في هذا الطلب فقط</span>
</div>

{{-- 2.1 الشهادة الثانوية (تظهر فقط إذا تم إدخالها بالطلب) --}}
@if($highSchoolEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white fs-7">مؤهل سابِق</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">الشهادة الثانوية العامة</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الثانوية العامة</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $highSchoolEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $highSchoolEd->education_level_id }}">
            
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الدولة المانحة :</label>
                    <select name="country_id" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $highSchoolEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الفرع / القسم :</label>
                    <input type="text" name="section_name" class="form-control" value="{{ $highSchoolEd->section_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ الحصول عليها :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $highSchoolEd->grant_date }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">ملاحظات / رقم قرار المعادلة (إن وجد) :</label>
                    <input type="text" name="notes" class="form-control" value="{{ $highSchoolEd->notes }}">
                </div>
            </div>

            <!-- Attachments List for Secondary School -->
            @if($highSchoolEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للثانوية:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($highSchoolEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'وثيقة ثانوية' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / مرفق جديد للثانوية :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الثانوية
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 2.2 الشهادة الجامعية الأولى / الإجازة (تظهر فقط إذا تم إدخالها بالطلب) --}}
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
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ التسجيل :</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ $bachelorEd->registration_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح / التخرج :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $bachelorEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">التقدير / المعدل :</label>
                    <input type="text" name="rank" class="form-control" value="{{ $bachelorEd->rank }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">ملاحظات قرار المعادلة أو التفاصيل :</label>
                    <input type="text" name="notes" class="form-control" value="{{ $bachelorEd->notes }}">
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

{{-- 2.3 دبلوم الدراسات العليا (تظهر فقط إذا تم إدخالها بالطلب) --}}
@if($diplomaEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white fs-7">مؤهل سابق</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">دبلوم الدراسات العليا</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الدبلوم</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $diplomaEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $diplomaEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الدولة المانحة :</label>
                    <select name="country_id" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $diplomaEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة :</label>
                    <select name="university_id" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $diplomaEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الاختصاص العام :</label>
                    <input type="text" name="general_specialization" class="form-control" value="{{ $diplomaEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الاختصاص الدقيق :</label>
                    <input type="text" name="exact_specialization" class="form-control" value="{{ $diplomaEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $diplomaEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">التقدير / المرتبة :</label>
                    <input type="text" name="rank" class="form-control" value="{{ $diplomaEd->rank }}">
                </div>
            </div>

            <!-- Attachments List for Diploma -->
            @if($diplomaEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للدبلوم:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($diplomaEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الدبلوم' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="row g-2 align-items-center border-top pt-3">
                <div class="col-md-8">
                    <label class="form-label fs-8 fw-bold mb-1" style="color: var(--imperial-navy);">إضافة وثيقة / مرفق جديد للدبلوم :</label>
                    <input type="file" name="new_attachment" class="form-control form-control-sm" accept=".pdf,image/*">
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-4">
                    <button type="submit" class="btn btn-solid-navy px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الدبلوم
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 2.4 شهادة ماجستير (تظهر فقط إذا تم إدخالها بالطلب) --}}
@if($masterEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold fs-7"><i class="fa-solid fa-star me-1"></i> المؤهل المطلوب تعادله</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">شهادة درجة الماجستير</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات وأطروحة الماجستير</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $masterEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $masterEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الدولة المانحة :</label>
                    <select name="country_id" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $masterEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة :</label>
                    <select name="university_id" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $masterEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الكلية / الاختصاص العام :</label>
                    <input type="text" name="general_specialization" class="form-control" value="{{ $masterEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">القسم / الاختصاص الدقيق :</label>
                    <input type="text" name="exact_specialization" class="form-control" value="{{ $masterEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ التسجيل بالدرجة :</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ $masterEd->registration_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المناقشة :</label>
                    <input type="date" name="defense_date" class="form-control" value="{{ $masterEd->defense_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح / الحصول عليها :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $masterEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">اسم المشرف على الأطروحة :</label>
                    <input type="text" name="supervisor_name" class="form-control" value="{{ $masterEd->supervisor_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">المرتبة / التقدير :</label>
                    <input type="text" name="rank" class="form-control" value="{{ $masterEd->rank }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">عنوان الأطروحة باللغة العربية :</label>
                    <input type="text" name="thesis_title" class="form-control fw-bold" style="color: var(--imperial-navy);" value="{{ $masterEd->thesis_title }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم قرار الإيفاد (إن وجد) :</label>
                    <input type="text" name="envoy_decision" class="form-control" value="{{ $masterEd->envoy_decision }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ قرار الإيفاد :</label>
                    <input type="date" name="envoy_date" class="form-control" value="{{ $masterEd->envoy_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الخبرة التدريسية (من - إلى) :</label>
                    <div class="input-group">
                        <input type="number" name="experience_from_year" class="form-control" placeholder="من" value="{{ $masterEd->experience_from_year }}">
                        <span class="input-group-text bg-white">-</span>
                        <input type="number" name="experience_to_year" class="form-control" placeholder="إلى" value="{{ $masterEd->experience_to_year }}">
                    </div>
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
                    <button type="submit" class="btn btn-gold-cta px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ تعديلات الماجستير
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 2.5 شهادة دكتوراه (تظهر فقط إذا تم إدخالها بالطلب) --}}
@if($phdEd)
<div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold fs-7"><i class="fa-solid fa-star me-1"></i> المؤهل المطلوب تعادله</span>
            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy); font-size: 1.05rem;">شهادة درجة الدكتوراه</h6>
        </div>
        <span class="fs-8 text-muted">تعديل بيانات الدكتوراه والأطروحة</span>
    </div>
    <div class="card-body p-4 bg-white">
        <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="education_id" value="{{ $phdEd->id }}">
            <input type="hidden" name="education_level_id" value="{{ $phdEd->education_level_id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الدولة المانحة :</label>
                    <select name="country_id" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $phdEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الجامعة المانحة :</label>
                    <select name="university_id" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $phdEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ التسجيل :</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ $phdEd->registration_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المناقشة :</label>
                    <input type="date" name="defense_date" class="form-control" value="{{ $phdEd->defense_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">تاريخ المنح :</label>
                    <input type="date" name="grant_date" class="form-control" value="{{ $phdEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">اسم المشرف :</label>
                    <input type="text" name="supervisor_name" class="form-control" value="{{ $phdEd->supervisor_name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الاختصاص العام :</label>
                    <input type="text" name="general_specialization" class="form-control" value="{{ $phdEd->general_specialization }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">الاختصاص الدقيق :</label>
                    <input type="text" name="exact_specialization" class="form-control" value="{{ $phdEd->exact_specialization }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold" style="color: var(--imperial-navy);">عنوان الأطروحة :</label>
                    <input type="text" name="thesis_title" class="form-control fw-bold" style="color: var(--imperial-navy);" value="{{ $phdEd->thesis_title }}">
                </div>
            </div>

            <!-- Attachments List for PhD -->
            @if($phdEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> الوثائق والمرفقات المرفوعة للدكتوراه:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($phdEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الدكتوراه' }}
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

@endsection

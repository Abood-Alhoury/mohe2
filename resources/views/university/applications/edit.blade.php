@extends('layouts.university')

@section('title', 'تعديل واستكمال وثائق الطلب - ' . ($application->application_no ?? ''))

@section('content')

<!-- HEADER & APPLICATION SUMMARY BANNER -->
<div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
    <div class="card-header py-3 px-4 text-white d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: var(--primary-container);">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 bg-white bg-opacity-10 rounded-circle border" style="border-color: var(--heritage-gold) !important;">
                <i class="fa-solid fa-file-pen fs-4" style="color: var(--heritage-gold-light);"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-white" style="font-size: 1.25rem;">
                    تعديل واستكمال وثائق الطلب رقم: <span style="color: var(--heritage-gold-light);">#{{ $application->application_no }}</span>
                </h5>
                <span class="fs-8 text-white-50">إعادة إدخال البيانات المفقودة وإرفاق الثبوتيات المطلوبة لوزارة التعليم العالي</span>
            </div>
        </div>
        <a href="{{ route('university.dashboard') }}" class="btn btn-gold-cta btn-sm px-3 fw-bold shadow-sm text-decoration-none">
            <i class="fa-solid fa-arrow-right me-1"></i> عودة للوحة تحكم الجامعة
        </a>
    </div>

    <div class="card-body p-3 bg-light border-bottom">
        <div class="row g-3 text-center align-items-center">
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">اسم المرشح (الطالب)</span>
                <span class="fw-bold fs-6 text-dark">{{ $candidate->full_name ?? 'غ/م' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">نوع الطلب</span>
                <span class="badge bg-white text-dark border px-2 py-1 fs-7 fw-bold shadow-sm">{{ $application->request_type }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">تاريخ تقديم الطلب</span>
                <span class="fw-bold fs-6 text-secondary">{{ $application->created_at ? $application->created_at->format('d/m/Y H:i') : '' }}</span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted fs-8 d-block fw-bold">حالة الطلب الحالية</span>
                <span class="badge-status badge-paper fs-7 px-3 py-1.5 fw-bold"><i class="fa-solid fa-file-circle-exclamation me-1"></i> {{ $application->status }}</span>
            </div>
        </div>
    </div>
</div>

<!-- ALERT NOTICE -->
<div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3.5" role="alert" style="background-color: var(--warning-container); color: var(--on-warning-container); border-right: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
    <i class="fa-solid fa-triangle-exclamation fs-2 me-3" style="color: var(--warning);"></i>
    <div>
        <h6 class="fw-bold mb-1" style="color: var(--warning);">⚠️ بانتظار استكمال الوثائق والبيانات</h6>
        <p class="mb-0 small">طلب التعادل هذا يتطلب استكمال بيانات أو رفع ثبوتيات ومرفقات جديدة. قم بتعديل الحقول أدناه وإرفاق المستندات المطلوبة ثم اضغط على زر (حفظ التعديلات وإرسال للمجلس).</p>
    </div>
</div>

<form action="{{ route('university.applications.update', $application->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- SECTION 1: البيانات الشخصية للمرشح -->
    <div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--primary-container) !important; border-radius: 4px;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color: var(--primary-container); font-size: 1.05rem;">
                <i class="fa-solid fa-user me-2" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية وبيانات التواصل للمرشح:
            </h6>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">اسم المرشح :</label>
                    <input type="text" name="candidate[full_name]" class="form-control" value="{{ $candidate->full_name }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">اسم الأب :</label>
                    <input type="text" name="candidate[father_name]" class="form-control" value="{{ $candidate->father_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">اسم الأم ونسبتها :</label>
                    <input type="text" name="candidate[mother_name]" class="form-control" value="{{ $candidate->mother_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">الرقم الوطني :</label>
                    <input type="text" name="candidate[national_id]" class="form-control" value="{{ $candidate->national_id }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">تاريخ الميلاد :</label>
                    <input type="date" name="candidate[dob]" class="form-control" value="{{ $candidate->dob }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">البريد الإلكتروني :</label>
                    <input type="email" name="candidate[email]" class="form-control" value="{{ $candidate->email }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">رقم الجوال :</label>
                    <input type="text" name="candidate[mobile]" class="form-control" value="{{ $candidate->mobile }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">الوظيفة :</label>
                    <input type="text" name="candidate[job_title]" class="form-control" value="{{ $candidate->job_title }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" style="color: var(--primary-container);">العنوان :</label>
                    <input type="text" name="candidate[address]" class="form-control" value="{{ $candidate->address }}">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: الشهادات والمؤهلات العلمية والمرفقات (مدخلة حصراً) -->
    <h6 class="fw-bold mb-3" style="color: var(--primary-container); font-size: 1.1rem;">
        <i class="fa-solid fa-graduation-cap me-2" style="color: var(--heritage-gold);"></i> 2. الشهادات والمرفقات المدخلة في هذا الطلب:
    </h6>

    {{-- الشهادة الثانوية العامة --}}
    @if($highSchoolEd)
    <div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color: var(--primary-container);">الشهادة الثانوية العامة</h6>
            <span class="badge bg-light text-dark border">مؤهل سابِق</span>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">الدولة المانحة :</label>
                    <select name="educations[{{ $highSchoolEd->id }}][country_id]" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $highSchoolEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الفرع / القسم :</label>
                    <input type="text" name="educations[{{ $highSchoolEd->id }}][section_name]" class="form-control" value="{{ $highSchoolEd->section_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المنح :</label>
                    <input type="date" name="educations[{{ $highSchoolEd->id }}][grant_date]" class="form-control" value="{{ $highSchoolEd->grant_date }}">
                </div>
            </div>

            <!-- Existing attachments -->
            @if($highSchoolEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> المرفقات الحالية للثانوية:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($highSchoolEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'وثيقة ثانوية' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-3 bg-light rounded border">
                <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-upload me-1 text-primary"></i> رفع وثيقة / مرفق جديد للثانوية العامة (PDF) :</label>
                <input type="file" name="attachments[{{ $highSchoolEd->id }}][]" class="form-control" accept=".pdf,image/*">
            </div>
        </div>
    </div>
    @endif

    {{-- الشهادة الجامعية الأولى (الإجازة) --}}
    @if($bachelorEd)
    <div class="card border mb-4 shadow-sm" style="border-top: 3px solid var(--heritage-gold) !important; border-radius: 4px;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color: var(--primary-container);">الشهادة الجامعية الأولى (الإجازة)</h6>
            <span class="badge bg-light text-dark border">مؤهل سابِق</span>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">الدولة المانحة :</label>
                    <select name="educations[{{ $bachelorEd->id }}][country_id]" class="form-select">
                        @foreach($countries as $cnt)
                            <option value="{{ $cnt->id }}" {{ $bachelorEd->country_id == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الجامعة المانحة :</label>
                    <select name="educations[{{ $bachelorEd->id }}][university_id]" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $bachelorEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الكلية / الاختصاص العام :</label>
                    <input type="text" name="educations[{{ $bachelorEd->id }}][general_specialization]" class="form-control" value="{{ $bachelorEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">القسم / الاختصاص الدقيق :</label>
                    <input type="text" name="educations[{{ $bachelorEd->id }}][exact_specialization]" class="form-control" value="{{ $bachelorEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المنح :</label>
                    <input type="date" name="educations[{{ $bachelorEd->id }}][grant_date]" class="form-control" value="{{ $bachelorEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">المعدل / التقدير :</label>
                    <input type="text" name="educations[{{ $bachelorEd->id }}][rank]" class="form-control" value="{{ $bachelorEd->rank }}">
                </div>
            </div>

            <!-- Existing attachments -->
            @if($bachelorEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> المرفقات الحالية للإجازة:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($bachelorEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الإجازة' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-3 bg-light rounded border">
                <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-upload me-1 text-primary"></i> رفع وثيقة / مرفق جديد للإجازة الجامعية (PDF) :</label>
                <input type="file" name="attachments[{{ $bachelorEd->id }}][]" class="form-control" accept=".pdf,image/*">
            </div>
        </div>
    </div>
    @endif

    {{-- شهادة الماجستير --}}
    @if($masterEd)
    <div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color: var(--primary-container); font-size: 1.05rem;">درجة الماجستير (المؤهل المراد تعادله)</h6>
            <span class="badge bg-warning text-dark fw-bold fs-7">المؤهل الرئيسي</span>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">الجامعة المانحة :</label>
                    <select name="educations[{{ $masterEd->id }}][university_id]" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $masterEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الكلية :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][general_specialization]" class="form-control" value="{{ $masterEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">القسم :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][exact_specialization]" class="form-control" value="{{ $masterEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ التسجيل :</label>
                    <input type="date" name="educations[{{ $masterEd->id }}][registration_date]" class="form-control" value="{{ $masterEd->registration_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المناقشة :</label>
                    <input type="date" name="educations[{{ $masterEd->id }}][defense_date]" class="form-control" value="{{ $masterEd->defense_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المنح :</label>
                    <input type="date" name="educations[{{ $masterEd->id }}][grant_date]" class="form-control" value="{{ $masterEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">اسم المشرف :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][supervisor_name]" class="form-control" value="{{ $masterEd->supervisor_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">التقدير / المرتبة :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][rank]" class="form-control" value="{{ $masterEd->rank }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">مكان الخبرة التدريسية :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][notes]" class="form-control" value="{{ $masterEd->notes }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">عنوان الرسالة / الأطروحة :</label>
                    <input type="text" name="educations[{{ $masterEd->id }}][thesis_title]" class="form-control fw-bold" value="{{ $masterEd->thesis_title }}">
                </div>
            </div>

            <!-- Existing attachments -->
            @if($masterEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> المرفقات الثبوتية المرفوعة للماجستير:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($masterEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الماجستير' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-3 bg-light rounded border">
                <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-cloud-arrow-up me-1 text-primary"></i> إرفاق ملف وثيقة / ثبوتية جديدة للماجستير (PDF) :</label>
                <input type="file" name="attachments[{{ $masterEd->id }}][]" class="form-control" accept=".pdf,image/*" multiple>
            </div>
        </div>
    </div>
    @endif

    {{-- شهادة الدكتوراه --}}
    @if($phdEd)
    <div class="card border mb-4 shadow-sm" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color: var(--primary-container); font-size: 1.05rem;">درجة الدكتوراه (المؤهل المراد تعادله)</h6>
            <span class="badge bg-warning text-dark fw-bold fs-7">المؤهل الرئيسي</span>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">الجامعة المانحة :</label>
                    <select name="educations[{{ $phdEd->id }}][university_id]" class="form-select">
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $phdEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الكلية :</label>
                    <input type="text" name="educations[{{ $phdEd->id }}][general_specialization]" class="form-control" value="{{ $phdEd->general_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">القسم :</label>
                    <input type="text" name="educations[{{ $phdEd->id }}][exact_specialization]" class="form-control" value="{{ $phdEd->exact_specialization }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المناقشة :</label>
                    <input type="date" name="educations[{{ $phdEd->id }}][defense_date]" class="form-control" value="{{ $phdEd->defense_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">تاريخ المنح :</label>
                    <input type="date" name="educations[{{ $phdEd->id }}][grant_date]" class="form-control" value="{{ $phdEd->grant_date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">اسم المشرف :</label>
                    <input type="text" name="educations[{{ $phdEd->id }}][supervisor_name]" class="form-control" value="{{ $phdEd->supervisor_name }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">عنوان الأطروحة :</label>
                    <input type="text" name="educations[{{ $phdEd->id }}][thesis_title]" class="form-control fw-bold" value="{{ $phdEd->thesis_title }}">
                </div>
            </div>

            <!-- Existing attachments -->
            @if($phdEd->attachments->count() > 0)
            <div class="mb-3 p-3 bg-light rounded border">
                <span class="fw-bold fs-8 d-block mb-2 text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i> المرفقات الثبوتية المرفوعة للدكتوراه:</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($phdEd->attachments as $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-navy py-1 px-2 text-decoration-none">
                            <i class="fa-solid fa-file-pdf me-1 text-danger"></i> {{ $att->notes ?? 'مرفق الدكتوراه' }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-3 bg-light rounded border">
                <label class="form-label fw-bold text-dark mb-1"><i class="fa-solid fa-cloud-arrow-up me-1 text-primary"></i> إرفاق ملف وثيقة / ثبوتية جديدة للدكتوراه (PDF) :</label>
                <input type="file" name="attachments[{{ $phdEd->id }}][]" class="form-control" accept=".pdf,image/*" multiple>
            </div>
        </div>
    </div>
    @endif

    <!-- SUBMIT BUTTON -->
    <div class="text-end mb-5">
        <button type="submit" class="btn btn-gold-cta py-3 px-5 fw-bold fs-6 shadow">
            <i class="fa-solid fa-paper-plane me-2"></i> حفظ التعديلات والمرفقات المطلوبة وإشعار مجلس التعليم العالي
        </button>
    </div>
</form>

@endsection

@extends('layouts.admin')

@section('title', 'تعديل البيانات الشخصية للمرشح - ' . ($candidate->full_name ?? ''))

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-primary"><i class="fa-solid fa-user-pen me-2"></i> تعديل البيانات الشخصية والشهادات للمرشح</h5>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-right me-1"></i> عودة لجدول الطلبات
        </a>
    </div>
    <div class="card-body">

        <!-- Header Summary Bar matching EditProcesses.aspx -->
        <div class="row bg-light p-3 rounded border mb-4 text-center">
            <div class="col-md-2">
                <span class="text-muted fw-bold">ID المرشح :</span>
                <span class="fw-bold fs-6 text-dark d-block">{{ $candidate->id ?? '' }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted fw-bold">اسم الجامعة :</span>
                <span class="fw-bold fs-6 text-primary d-block">{{ $application->workUniversity->name ?? 'غير محددة' }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted fw-bold">نوع الطلب :</span>
                <span class="fw-bold fs-6 text-dark d-block">{{ $application->request_type }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted fw-bold">رقم الطلب :</span>
                <span class="fw-bold fs-6 text-success d-block">{{ $application->application_no }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted fw-bold">تاريخ تقديم الطلب :</span>
                <span class="fw-bold fs-6 text-dark d-block">{{ $application->created_at ? $application->created_at->format('Y-m-d H:i') : '' }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted fw-bold">حالة الطلب :</span>
                <span class="badge bg-primary fs-6 d-block mt-1">{{ $application->status }}</span>
            </div>
        </div>

        <!-- SECTION 1: البيانات الشخصية -->
        <div class="card border mb-4">
            <div class="card-header bg-white border-bottom border-2 border-danger text-danger fw-bold fs-6">
                <i class="fa-solid fa-address-card me-2"></i> البيانات الشخصية :
            </div>
            <div class="card-body">
                <form action="{{ route('admin.applications.update_candidate', $application->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الرقم الوطني :</label>
                            <input type="text" name="national_id" class="form-control" value="{{ $candidate->national_id }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الاسم :</label>
                            <input type="text" name="full_name" class="form-control" value="{{ $candidate->full_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الجنسية :</label>
                            <input type="text" class="form-control" value="{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الجنس :</label>
                            <select name="gender" class="form-select">
                                <option value="ذكر" {{ $candidate->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                <option value="أنثى" {{ $candidate->gender == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ الميلاد :</label>
                            <input type="date" name="dob" class="form-control" value="{{ $candidate->dob }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">البريد الإلكتروني :</label>
                            <input type="email" name="email" class="form-control" value="{{ $candidate->email }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">العنوان :</label>
                            <input type="text" name="address" class="form-control" value="{{ $candidate->address }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الوظيفة :</label>
                            <input type="text" name="job_title" class="form-control" value="{{ $candidate->job_title }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">رقم الهاتف :</label>
                            <input type="text" name="phone" class="form-control" value="{{ $candidate->phone }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">رقم الموبايل :</label>
                            <input type="text" name="mobile" class="form-control" value="{{ $candidate->mobile }}">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-secondary px-4 fw-bold">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SECTION 2: الشهادات التي يحملها المرشح -->
        <h5 class="text-danger fw-bold mb-3 border-bottom pb-2">
            <i class="fa-solid fa-graduation-cap me-2"></i> الشهادات التي يحملها المرشح :
        </h5>

        <!-- 2.1 الشهادة الثانوية -->
        <div class="card border mb-3">
            <div class="card-header bg-light fw-bold text-dark">
                الشهادة الثانوية :
            </div>
            <div class="card-body">
                <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="education_id" value="{{ $highSchoolEd->id ?? '' }}">
                    <input type="hidden" name="education_level_id" value="{{ $educationLevels->where('name', 'ثانوية عامة')->first()->id ?? 6 }}">
                    <div class="row g-3 mb-2">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الدولة :</label>
                            <select name="country_id" class="form-select">
                                @foreach($countries as $cnt)
                                    <option value="{{ $cnt->id }}" {{ ($highSchoolEd->country_id ?? 1) == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">القسم (الفرع) :</label>
                            <input type="text" name="section_name" class="form-control" value="{{ $highSchoolEd->section_name ?? 'علمي' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ المنح :</label>
                            <input type="date" name="grant_date" class="form-control" value="{{ $highSchoolEd->grant_date ?? '' }}">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-secondary px-4 fw-bold">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2.2 شهادة ماجستير -->
        <div class="card border mb-3">
            <div class="card-header bg-light fw-bold text-dark">
                شهادة ماجستير :
            </div>
            <div class="card-body">
                <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="education_id" value="{{ $masterEd->id ?? '' }}">
                    <input type="hidden" name="education_level_id" value="{{ $educationLevels->where('name', 'ماجستير')->first()->id ?? 3 }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الدولة المانحة :</label>
                            <select name="country_id" class="form-select">
                                @foreach($countries as $cnt)
                                    <option value="{{ $cnt->id }}" {{ ($masterEd->country_id ?? 1) == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الجامعة المانحة :</label>
                            <select name="university_id" class="form-select">
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ ($masterEd->university_id ?? 1) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ التسجيل :</label>
                            <input type="date" name="registration_date" class="form-control" value="{{ $masterEd->registration_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ المنح :</label>
                            <input type="date" name="grant_date" class="form-control" value="{{ $masterEd->grant_date ?? '' }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">عنوان الأطروحة :</label>
                            <input type="text" name="thesis_title" class="form-control" value="{{ $masterEd->thesis_title ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">رقم قرار الإيفاد :</label>
                            <input type="text" name="envoy_decision" class="form-control" value="{{ $masterEd->envoy_decision ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ قرار الإيفاد :</label>
                            <input type="date" name="envoy_date" class="form-control" value="{{ $masterEd->envoy_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الاختصاص العام :</label>
                            <input type="text" name="general_specialization" class="form-control" value="{{ $masterEd->general_specialization ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الاختصاص الدقيق :</label>
                            <input type="text" name="exact_specialization" class="form-control" value="{{ $masterEd->exact_specialization ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">اسم المشرف :</label>
                            <input type="text" name="supervisor_name" class="form-control" value="{{ $masterEd->supervisor_name ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">المرتبة / التقدير :</label>
                            <input type="text" name="rank" class="form-control" value="{{ $masterEd->rank ?? '' }}">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-secondary px-4 fw-bold">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2.3 شهادة دكتوراه -->
        <div class="card border mb-4">
            <div class="card-header bg-light fw-bold text-dark">
                شهادة دكتوراه :
            </div>
            <div class="card-body">
                <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="education_id" value="{{ $phdEd->id ?? '' }}">
                    <input type="hidden" name="education_level_id" value="{{ $educationLevels->where('name', 'دكتوراه')->first()->id ?? 4 }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الدولة المانحة :</label>
                            <select name="country_id" class="form-select">
                                @foreach($countries as $cnt)
                                    <option value="{{ $cnt->id }}" {{ ($phdEd->country_id ?? 1) == $cnt->id ? 'selected' : '' }}>{{ $cnt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الجامعة المانحة :</label>
                            <select name="university_id" class="form-select">
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ ($phdEd->university_id ?? 1) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ التسجيل :</label>
                            <input type="date" name="registration_date" class="form-control" value="{{ $phdEd->registration_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ المناقشة :</label>
                            <input type="date" name="defense_date" class="form-control" value="{{ $phdEd->defense_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ المنح :</label>
                            <input type="date" name="grant_date" class="form-control" value="{{ $phdEd->grant_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">عنوان الأطروحة :</label>
                            <input type="text" name="thesis_title" class="form-control" value="{{ $phdEd->thesis_title ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الاختصاص العام :</label>
                            <input type="text" name="general_specialization" class="form-control" value="{{ $phdEd->general_specialization ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الاختصاص الدقيق :</label>
                            <input type="text" name="exact_specialization" class="form-control" value="{{ $phdEd->exact_specialization ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">اسم المشرف :</label>
                            <input type="text" name="supervisor_name" class="form-control" value="{{ $phdEd->supervisor_name ?? '' }}">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-secondary px-4 fw-bold">Update</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

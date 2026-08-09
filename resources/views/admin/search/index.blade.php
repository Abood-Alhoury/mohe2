@extends('layouts.admin')

@section('title', 'بحث حسب - استعلام طلبات التعادل')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-primary"><i class="fa-solid fa-magnifying-glass me-2"></i> البحث في سجلات ومعاملات معادلة الشهادات</h5>
    </div>
    <div class="card-body">

        <!-- Top Search Sub-Tabs matching legacy ASPX -->
        <div class="legacy-tab-bar mb-4">
            <a href="{{ route('admin.search.index', ['mode' => 'name_faculty']) }}" class="legacy-tab-btn {{ $mode == 'name_faculty' ? 'active' : '' }}">
                حسب الاسم والكلية
            </a>
            <a href="{{ route('admin.search.index', ['mode' => 'name_university']) }}" class="legacy-tab-btn {{ $mode == 'name_university' ? 'active' : '' }}">
                حسب الاسم والجامعة
            </a>
            <a href="{{ route('admin.search.index', ['mode' => 'name_qualification']) }}" class="legacy-tab-btn {{ $mode == 'name_qualification' ? 'active' : '' }}">
                حسب الاسم والمؤهل العلمي
            </a>
            <a href="{{ route('admin.search.index', ['mode' => 'number_date']) }}" class="legacy-tab-btn {{ $mode == 'number_date' ? 'active' : '' }}">
                حسب رقم وتاريخ الطلب
            </a>
        </div>

        <!-- Search Form Container matching legacy ASPX layout -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-7">
                <div class="p-4 bg-light rounded border shadow-sm">
                    <form action="{{ route('admin.search.index') }}" method="GET">
                        <input type="hidden" name="mode" value="{{ $mode }}">

                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label text-end fw-bold">الاسم :</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" value="{{ $name }}" placeholder="اسم الطالب والمرشح">
                            </div>
                        </div>

                        @if($mode == 'name_faculty')
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label text-end fw-bold">الكلية :</label>
                            <div class="col-sm-9">
                                <input type="text" name="faculty" class="form-control" value="{{ $faculty }}" placeholder="مثل: كلية الهندسة المدنية">
                            </div>
                        </div>
                        @elseif($mode == 'name_university')
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label text-end fw-bold">الجامعة :</label>
                            <div class="col-sm-9">
                                <select name="university_id" class="form-select">
                                    <option value="">-- اختر الجامعة --</option>
                                    @foreach($universities as $u)
                                        <option value="{{ $u->id }}" {{ $universityId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @elseif($mode == 'number_date')
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label text-end fw-bold">رقم الطلب :</label>
                            <div class="col-sm-9">
                                <input type="text" name="app_no" class="form-control" value="{{ $appNo }}" placeholder="رقم المعاملة أو الطلب">
                            </div>
                        </div>
                        @endif

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i> بحث</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search Results Grid -->
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-table me-2 text-primary"></i> نتائج البحث المعروضة</h6>
        <div class="table-responsive">
            <table class="table mohe-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>نوع الطلب</th>
                        <th>الجامعة</th>
                        <th>الاسم</th>
                        <th>الكلية</th>
                        <th>المؤهل العلمي</th>
                        <th>وضع الطلب</th>
                        <th style="width: 180px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $res)
                    @php
                        $lastEducation = $res->educations->last();
                    @endphp
                    <tr>
                        <td class="fw-bold text-secondary">{{ $res->id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $res->request_type ?? 'تعادل' }}</span></td>
                        <td class="fw-bold">{{ $res->workUniversity->name ?? 'جامعة غير محددة' }}</td>
                        <td class="text-primary fw-bold">{{ $res->candidate->full_name ?? 'غ/م' }}</td>
                        <td>{{ $res->work_faculty ?? 'إدارة جامعة' }}</td>
                        <td>{{ $lastEducation->level->name ?? 'إجازة جامعية' }}</td>
                        <td>
                            @if($res->status == 'قيد الدراسة')
                                <span class="badge badge-status badge-study">قيد الدراسة</span>
                            @elseif($res->status == 'تم الصدور' || $res->status == 'موافقة')
                                <span class="badge badge-status badge-approved">تم الصدور</span>
                            @else
                                <span class="badge bg-secondary text-white">{{ $res->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.reports.show', $res->id) }}" class="btn btn-outline-primary fw-bold">
                                    <i class="fa-solid fa-eye me-1"></i> Select
                                </a>
                                <a href="{{ route('admin.applications.edit', $res->id) }}" class="btn btn-outline-warning fw-bold">
                                    <i class="fa-solid fa-pen me-1"></i> Update
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            @if($hasSearched)
                                لا توجد طلبات تعادل تطابق معايير البحث المدخلة.
                            @else
                                يرجى إدخال معايير البحث في الأعلى ثم الضغط على زر (بحث).
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

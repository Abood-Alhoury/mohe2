@extends('layouts.admin')
@section('title', 'مذكرة العرض - ' . ($candidate->full_name ?? ''))

@push('styles')
<style>
@media print { 
    .no-print, .mohe-header, .mohe-nav, header, footer { display:none!important; } 
    .moz-wrapper { box-shadow:none!important; border:none!important; margin:0!important; width:100%!important; max-width:100%!important; padding:0!important; } 
    body { background:#fff!important; margin:0!important; padding:0!important; } 
}
.moz-wrapper { direction:rtl; font-family:'Cairo','Tahoma',sans-serif; font-size:13px; background:#fff; max-width:900px; margin:0 auto 30px; padding:24px 32px; box-shadow:0 2px 16px rgba(0,0,0,.13); border:1px solid #ccc; color:#000; }
.moz-header { display:flex; align-items:center; justify-content:center; gap:18px; margin-bottom:4px; padding-bottom:10px; border-bottom:3px solid #C9B037; }
.moz-header img { width:110px; height:auto; }
.moz-header-text { text-align:center; }
.moz-header-text .ar { font-size:15px; font-weight:bold; color:#000; }
.moz-header-text .en { font-size:11px; color:#555; letter-spacing:1px; }
.moz-title { text-align:center; font-size:18px; font-weight:bold; color:#1E3A5F; margin:10px 0 14px; }
.moz-section { background:#1E3A5F; color:#fff; font-weight:bold; font-size:13px; padding:5px 12px; margin:14px 0 0; border-right:5px solid #C9B037; }
.mt td { padding:4px 8px; font-size:12.5px; border:1px solid #ddd; color:#000; }
.mt td.l { background:#f5f7fa; font-weight:bold; white-space:nowrap; width:150px; }
.mt { width:100%; border-collapse:collapse; margin:0; }
.cname { background:#eef3f8; border:1px solid #c0d0e0; padding:6px; font-size:15px; font-weight:bold; color:#000; text-align:center; margin:4px 0; }
.wblock { background:#f7f7f7; border:1px solid #ccc; padding:7px 12px; font-size:12.5px; margin-top:2px; color:#000; }
.wblock b { color:#000; }
.dblock { border:1px solid #c0d0e0; margin:8px 0; }
.dblock-h { background:#1E3A5F; color:#fff; font-weight:bold; font-size:12.5px; padding:4px 10px; }
.dt { width:100%; border-collapse:collapse; font-size:12px; margin:6px 0; }
.dt th { background:#1E3A5F; color:#fff; padding:5px 8px; text-align:center; }
.dt td { border:1px solid #bbb; padding:4px 8px; text-align:center; color:#000; }
.ct th { background:#d4dce8; color:#000; padding:4px 8px; text-align:center; border:1px solid #ccc; }
.ct td { border:1px solid #ccc; padding:4px 8px; text-align:center; color:#000; }
.ct { width:100%; border-collapse:collapse; font-size:12px; }
.ebox { background:#fafafa; border:1px solid #C9B037; padding:8px 14px; margin-top:10px; font-size:12.5px; color:#000; }
.subh { background:#eef3f8; color:#000; font-weight:bold; font-size:12px; padding:3px 8px; border-bottom:1px solid #c0d0e0; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary fw-bold"><i class="fa-solid fa-arrow-right me-1"></i> العودة لجدول الطلبات</a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-mohe-gold fw-bold"><i class="fa-solid fa-print me-1"></i> طباعة</button>
        <a href="{{ route('admin.reports.download_pdf', $application->id) }}" target="_blank" class="btn btn-danger fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> تنزيل PDF</a>
        <a href="{{ route('admin.reports.consolidated', $application->id) }}" class="btn btn-success fw-bold"><i class="fa-solid fa-layer-group me-1"></i> المرفقات المدمجة</a>
    </div>
</div>

<div class="moz-wrapper">
    <div class="moz-header">
        <div class="moz-header-text">
            <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
            <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
        </div>
        <img src="{{ asset('assets/logo.jpg') }}" alt="شعار الوزارة">
    </div>
    <div class="moz-title">(مذكرة العرض)</div>

    {{-- 1. البيانات الشخصية --}}
    <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr><td class="l">نوع الطلب :</td><td style="font-weight:bold;">{{ $application->request_type ?? 'تعادل' }}</td><td class="l">ID :</td><td>{{ $candidate->id }}</td></tr>
    </table>
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    <table class="mt">
        <tr><td class="l">رقم الوطني :</td><td>{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    <div class="wblock">
        <b>المرشح للعمل في قسم :</b> {{ $application->work_department ?? '---' }} &nbsp;
        <b>في كلية :</b> {{ $application->work_faculty ?? '---' }} &nbsp;
        <b>في جامعة :</b> {{ $application->workUniversity->name ?? '---' }}
        <span style="color:#555; font-size:11px;"> التي تطلب الجامعة تكليفه بتدريسها إلى قرار معادلة شهادته</span>
    </div>

    {{-- 2. المقررات --}}
    <div class="moz-section" style="margin-top:14px;"><i class="fa-solid fa-book-bookmark me-1"></i> المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
    <table class="ct">
        <thead><tr><th>اسم المقرر</th><th>القسم</th><th>الكلية</th><th>حالة المقرر</th></tr></thead>
        <tbody>
            @forelse($application->courses as $c)
            <tr><td style="font-weight:bold;">{{ $c->course_name }}</td><td>{{ $c->department }}</td><td>{{ $c->faculty }}</td><td>{{ $c->course_status }}</td></tr>
            @empty
            <tr><td colspan="4" style="color:#c00; font-weight:bold; text-align:center;">لا توجد مقررات تطلبها الجامعة</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- 3. الشهادات --}}
    <div class="moz-section" style="margin-top:14px;"><i class="fa-solid fa-award me-1"></i> الشهادات التي يحملها المرشح :</div>

    {{-- ثانوية --}}
    <div class="dblock"><div class="dblock-h">الشهادة الثانوية :</div><table class="mt">
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td><td class="l">القسم :</td><td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td></tr>
    </table></div>

    {{-- إجازة جامعية --}}
    @if($bachelorEd)
    <div class="dblock"><div class="dblock-h">شهادة الإجازة الجامعية :</div><table class="mt">
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $bachelorEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td></tr>
    </table></div>
    @endif

    {{-- ماجستير --}}
    @if($masterEd)
    <div class="dblock"><div class="dblock-h">شهادة ماجستير {{ optional(optional($masterEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div><table class="mt">
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($masterEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $masterEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $masterEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $masterEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $masterEd->grant_date ?? '---' }}</td><td class="l">اسم المشرف :</td><td>{{ $masterEd->supervisor_name ?? '---' }}</td></tr>
        <tr><td class="l">عنوان الأطروحة :</td><td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td></tr>
    </table>
    @if($masterEd->residences && $masterEd->residences->count() > 0)
    <div class="subh">مجموع الإقامة بمرحلة الماجستير :</div>
    <table class="dt"><thead><tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr></thead><tbody>
        @foreach($masterEd->residences as $r)
        <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>2</td></tr>
        @endforeach
    </tbody></table>
    @endif
    </div>
    @endif

    {{-- دكتوراه --}}
    @if($phdEd)
    <div class="dblock"><div class="dblock-h">شهادة دكتوراه {{ optional(optional($phdEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div><table class="mt">
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($phdEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $phdEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $phdEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $phdEd->registration_date ?? '---' }}</td><td class="l">تاريخ المناقشة :</td><td>{{ $phdEd->defense_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $phdEd->grant_date ?? '---' }}</td></tr>
        <tr><td class="l">اسم المشرف :</td><td>{{ $phdEd->supervisor_name ?? '---' }}</td><td class="l">عنوان الأطروحة :</td><td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td></tr>
        <tr><td class="l">معلومات أخرى :</td><td colspan="5">{{ $phdEd->notes ?? 'لا توجد' }}</td></tr>
    </table>
    @if($phdEd->residences && $phdEd->residences->count() > 0)
    <div class="subh">تاريخ دخول بلد الدراسة :</div>
    <table class="dt"><thead><tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr></thead><tbody>
        @foreach($phdEd->residences as $r)
        <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>4</td></tr>
        @endforeach
    </tbody></table>
    @endif
    </div>
    @endif

    {{-- 4. معلومات إضافية --}}
    <div class="moz-section" style="margin-top:14px;"><i class="fa-solid fa-circle-info me-1"></i> معلومات إضافية :</div>
    <div class="ebox">
        <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
        <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
        <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
    </div>
</div>
@endsection

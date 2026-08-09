@extends('layouts.admin')
@section('title', 'مذكرة العرض - ' . ($candidate->full_name ?? ''))

@push('styles')
<style>
@media print { 
    .no-print, .mohe-header, .mohe-nav, header, footer { display:none!important; } 
    .moz-wrapper { box-shadow:none!important; border:none!important; margin:0!important; width:100%!important; max-width:100%!important; padding:0!important; } 
    body { background:#fff!important; margin:0!important; padding:0!important; } 
}

.moz-wrapper { 
    direction: rtl; 
    font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; 
    font-size: 13.5px; 
    background: #ffffff; 
    max-width: 920px; 
    margin: 0 auto 30px; 
    padding: 30px 40px; 
    box-shadow: 0px 4px 25px rgba(26, 42, 68, 0.08); 
    border: 1px solid var(--outline-variant); 
    border-top: 4px solid var(--heritage-gold) !important;
    border-radius: 4px;
    color: #111C2C; 
}

.moz-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    margin-bottom: 8px; 
    padding-bottom: 12px; 
    border-bottom: 3px double var(--heritage-gold); 
}

.moz-header-text .ar { 
    font-size: 16px; 
    font-weight: 700; 
    color: var(--imperial-navy); 
}

.moz-header-text .en { 
    font-size: 11px; 
    color: #555; 
    letter-spacing: 0.5px; 
}

.moz-title { 
    text-align: center; 
    font-size: 19px; 
    font-weight: 700; 
    color: var(--imperial-navy); 
    margin: 12px 0 16px; 
}

.moz-section { 
    background-color: var(--imperial-navy); 
    color: #ffffff; 
    font-weight: 700; 
    font-size: 13.5px; 
    padding: 6px 14px; 
    margin: 16px 0 6px; 
    border-right: 4px solid var(--heritage-gold); 
    border-radius: 2px;
}

.mt { width: 100%; border-collapse: collapse; margin: 0; }
.mt td { padding: 6px 10px; font-size: 13px; border: 1px solid var(--outline-variant); color: #111C2C; }
.mt td.l { background: #f5f3f5; font-weight: 700; color: var(--imperial-navy); white-space: nowrap; width: 160px; }

.cname { 
    background: #f0f3ff; 
    border: 1px solid var(--outline-variant); 
    border-right: 4px solid var(--heritage-gold);
    padding: 8px 12px; 
    font-size: 15px; 
    font-weight: 700; 
    color: var(--imperial-navy); 
    text-align: center; 
    margin: 6px 0; 
}

.wblock { 
    background: #faf9fb; 
    border: 1px solid var(--outline-variant); 
    padding: 9px 14px; 
    font-size: 13px; 
    margin-top: 6px; 
    color: #111C2C; 
}

.dblock { border: 1px solid var(--outline-variant); margin: 10px 0; border-radius: 3px; overflow: hidden; }
.dblock-h { background: var(--imperial-navy); color: #ffffff; font-weight: 700; font-size: 13px; padding: 5px 12px; border-bottom: 2px solid var(--heritage-gold); }

.dt { width: 100%; border-collapse: collapse; font-size: 12px; margin: 0; }
.dt th { background: var(--imperial-navy); color: #ffffff; padding: 6px 8px; text-align: center; font-weight: 600; }
.dt td { border: 1px solid var(--outline-variant); padding: 5px 8px; text-align: center; color: #111C2C; }

.ct { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.ct th { background: var(--imperial-navy); color: #ffffff; padding: 6px 10px; text-align: center; font-weight: 600; }
.ct td { border: 1px solid var(--outline-variant); padding: 6px 10px; text-align: center; color: #111C2C; }

.ebox { 
    background: #faf9fb; 
    border: 1px solid var(--outline-variant); 
    border-right: 4px solid var(--heritage-gold);
    padding: 10px 16px; 
    margin-top: 10px; 
    font-size: 13px; 
    color: #111C2C; 
}
.subh { background: #f0f3ff; color: var(--imperial-navy); font-weight: 700; font-size: 12.5px; padding: 4px 10px; border-bottom: 1px solid var(--outline-variant); }
</style>
@endpush

@section('content')
<!-- SYSTEM ACTION BAR -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print p-3 bg-white shadow-sm rounded border border-secondary-subtle">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3">
        <i class="fa-solid fa-arrow-right me-1"></i> العودة لجدول الطلبات
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-solid-navy fw-bold px-3">
            <i class="fa-solid fa-print me-1"></i> طباعة
        </button>
        <a href="{{ route('admin.reports.download_pdf', $application->id) }}" target="_blank" class="btn btn-gold-cta fw-bold px-3">
            <i class="fa-solid fa-file-pdf me-1"></i> تنزيل PDF
        </a>
        <a href="{{ route('admin.reports.download_consolidated_pdf', $application->id) }}" target="_blank" class="btn btn-outline-gold fw-bold px-3" title="تنزيل حزمة الملف المدموج (مذكرة العرض + كافـة المرفقات والشهادات كملف PDF واحد)">
            <i class="fa-solid fa-layer-group me-1"></i> المرفقات المدمجة (PDF مدموج)
        </a>
    </div>
</div>

<!-- MOZHAKKARA DOCUMENT PAPER -->
<div class="moz-wrapper">
    <div class="moz-header">
        <div class="moz-header-text text-start">
            <div class="ar">الجمهورية العربية السورية</div>
            <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
            <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
        </div>
        <div class="mohe-emblem-ring">
            <img src="{{ asset('assets/logo.jpg') }}" alt="شعار الوزارة" onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
        </div>
    </div>
    
    <div class="moz-title">(مذكرة العرض)</div>

    {{-- 1. البيانات الشخصية --}}
    <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع الطلب :</td>
            <td style="font-weight:bold; color: var(--imperial-navy);">{{ $application->request_type ?? 'تعادل' }}</td>
            <td class="l">ID :</td>
            <td class="fw-bold">{{ $candidate->id }}</td>
        </tr>
    </table>
    
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    
    <table class="mt">
        <tr><td class="l">الرقم الوطني :</td><td class="fw-bold">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: var(--imperial-navy); font-weight: 600;">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    
    <div class="wblock">
        <b>المرشح للعمل في قسم :</b> {{ $application->work_department ?? '---' }} &nbsp;| &nbsp;
        <b>في كلية :</b> {{ $application->work_faculty ?? '---' }} &nbsp;| &nbsp;
        <b>في جامعة :</b> {{ $application->workUniversity->name ?? '---' }}
        <span style="color:#555; font-size:11.5px; display: block; margin-top: 3px;">التي تطلب الجامعة تكليفه بتدريسها استناداً إلى قرار معادلة شهادته العلمية.</span>
    </div>

    {{-- 2. المقررات --}}
    <div class="moz-section"><i class="fa-solid fa-book-bookmark me-1"></i> المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
    <table class="ct">
        <thead>
            <tr>
                <th>اسم المقرر</th>
                <th>القسم</th>
                <th>الكلية</th>
                <th>حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->courses as $c)
            <tr>
                <td style="font-weight:bold; color: var(--imperial-navy);">{{ $c->course_name }}</td>
                <td>{{ $c->department }}</td>
                <td>{{ $c->faculty }}</td>
                <td>{{ $c->course_status }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات تطلبها الجامعة</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- 3. الشهادات --}}
    <div class="moz-section"><i class="fa-solid fa-award me-1"></i> الشهادات التي يحملها المرشح :</div>

    {{-- ثانوية --}}
    <div class="dblock">
        <div class="dblock-h">الشهادة الثانوية :</div>
        <table class="mt">
            <tr>
                <td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td>
                <td class="l">القسم :</td><td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td>
                <td class="l">تاريخ المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
            </tr>
        </table>
    </div>

    {{-- إجازة جامعية --}}
    @if($bachelorEd)
    <div class="dblock">
        <div class="dblock-h">شهادة الإجازة الجامعية :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $bachelorEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td></tr>
        </table>
    </div>
    @endif

    {{-- ماجستير --}}
    @if($masterEd)
    <div class="dblock">
        <div class="dblock-h">شهادة ماجستير {{ optional(optional($masterEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($masterEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $masterEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $masterEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $masterEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $masterEd->grant_date ?? '---' }}</td><td class="l">اسم المشرف :</td><td>{{ $masterEd->supervisor_name ?? '---' }}</td></tr>
            <tr><td class="l">عنوان الأطروحة :</td><td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td></tr>
        </table>
        @if($masterEd->residences && $masterEd->residences->count() > 0)
        <div class="subh">مجموع الإقامة بمرحلة الماجستير :</div>
        <table class="dt">
            <thead>
                <tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr>
            </thead>
            <tbody>
                @foreach($masterEd->residences as $r)
                <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>2</td></tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- دكتوراه --}}
    @if($phdEd)
    <div class="dblock">
        <div class="dblock-h">شهادة دكتوراه {{ optional(optional($phdEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($phdEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $phdEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $phdEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $phdEd->registration_date ?? '---' }}</td><td class="l">تاريخ المناقشة :</td><td>{{ $phdEd->defense_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $phdEd->grant_date ?? '---' }}</td></tr>
            <tr><td class="l">اسم المشرف :</td><td>{{ $phdEd->supervisor_name ?? '---' }}</td><td class="l">عنوان الأطروحة :</td><td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td></tr>
            <tr><td class="l">معلومات أخرى :</td><td colspan="5">{{ $phdEd->notes ?? 'لا توجد' }}</td></tr>
        </table>
        @if($phdEd->residences && $phdEd->residences->count() > 0)
        <div class="subh">تاريخ دخول بلد الدراسة :</div>
        <table class="dt">
            <thead>
                <tr><th>تاريخ الدخول</th><th>مطار الدخول</th><th>تاريخ الخروج</th><th>مطار الخروج</th><th>رقم الصفحة</th><th>يوم</th><th>شهر</th><th>سنة</th></tr>
            </thead>
            <tbody>
                @foreach($phdEd->residences as $r)
                <tr><td>{{ $r->entry_date }}</td><td>{{ $r->entry_airport }}</td><td>{{ $r->exit_date }}</td><td>{{ $r->exit_airport }}</td><td>{{ $r->page_number }}</td><td>0</td><td>0</td><td>4</td></tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- 4. معلومات إضافية --}}
    <div class="moz-section"><i class="fa-solid fa-circle-info me-1"></i> معلومات إضافية :</div>
    <div class="ebox">
        <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
        <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
        <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
    </div>
</div>
@endsection

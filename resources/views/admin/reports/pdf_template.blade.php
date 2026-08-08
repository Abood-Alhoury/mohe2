<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>مذكرة العرض - {{ $candidate->full_name ?? '' }}</title>
    <style>
        body { font-family:'DejaVu Sans',sans-serif; direction:rtl; text-align:right; font-size:11px; color:#000; margin:0; padding:10px; }
        .header { text-align:center; border-bottom:3px solid #C9B037; padding-bottom:8px; margin-bottom:10px; }
        .header .ar { font-size:14px; font-weight:bold; color:#000; }
        .header .en { font-size:10px; color:#555; }
        .title { font-size:16px; font-weight:bold; color:#1E3A5F; text-align:center; margin:8px 0 12px; }
        .section { background:#1E3A5F; color:#fff; font-weight:bold; font-size:12px; padding:4px 10px; margin:12px 0 0; border-right:4px solid #C9B037; }
        table { width:100%; border-collapse:collapse; margin:0; }
        td { padding:3px 6px; font-size:10.5px; border:1px solid #ddd; color:#000; }
        td.l { background:#f5f7fa; font-weight:bold; white-space:nowrap; width:130px; }
        .cname { background:#eef3f8; border:1px solid #c0d0e0; padding:5px; font-size:13px; font-weight:bold; color:#000; text-align:center; margin:3px 0; }
        .wblock { background:#f7f7f7; border:1px solid #ccc; padding:5px 8px; font-size:10.5px; margin-top:2px; }
        .dblock { border:1px solid #c0d0e0; margin:6px 0; }
        .dblock-h { background:#1E3A5F; color:#fff; font-weight:bold; font-size:11px; padding:3px 8px; }
        .dt th { background:#1E3A5F; color:#fff; padding:3px 6px; text-align:center; font-size:10px; }
        .dt td { border:1px solid #bbb; padding:3px 6px; text-align:center; }
        .ebox { background:#fafafa; border:1px solid #C9B037; padding:6px 10px; margin-top:8px; font-size:10.5px; }
        .subh { background:#eef3f8; font-weight:bold; font-size:10px; padding:2px 6px; }
        .fw-bold { font-weight:bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
        <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
    </div>
    <div class="title">(مذكرة العرض)</div>

    <div class="section">البيانات الشخصية للمرشح :</div>
    <table><tr><td class="l">نوع الطلب :</td><td class="fw-bold">{{ $application->request_type ?? 'تعادل' }}</td><td class="l">ID :</td><td>{{ $candidate->id }}</td></tr></table>
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    <table>
        <tr><td class="l">رقم الوطني :</td><td>{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    <div class="wblock"><b>المرشح للعمل في قسم :</b> {{ $application->work_department ?? '---' }} <b>في كلية :</b> {{ $application->work_faculty ?? '---' }} <b>في جامعة :</b> {{ $application->workUniversity->name ?? '---' }}</div>

    <div class="section">المقررات التي يدرسها :</div>
    <table><thead><tr><td class="l" style="text-align:center;">اسم المقرر</td><td class="l" style="text-align:center;">القسم</td><td class="l" style="text-align:center;">الكلية</td><td class="l" style="text-align:center;">الحالة</td></tr></thead><tbody>
        @forelse($application->courses as $c)
        <tr><td class="fw-bold">{{ $c->course_name }}</td><td>{{ $c->department }}</td><td>{{ $c->faculty }}</td><td>{{ $c->course_status }}</td></tr>
        @empty
        <tr><td colspan="4" style="text-align:center; color:#c00;">لا توجد مقررات</td></tr>
        @endforelse
    </tbody></table>

    <div class="section">الشهادات التي يحملها المرشح :</div>

    <div class="dblock"><div class="dblock-h">الشهادة الثانوية :</div><table>
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td><td class="l">القسم :</td><td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td></tr>
    </table></div>

    @if($bachelorEd)
    <div class="dblock"><div class="dblock-h">شهادة الإجازة الجامعية :</div><table>
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $bachelorEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td></tr>
    </table></div>
    @endif

    @if($masterEd)
    <div class="dblock"><div class="dblock-h">شهادة ماجستير :</div><table>
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($masterEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $masterEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $masterEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $masterEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $masterEd->grant_date ?? '---' }}</td><td class="l">اسم المشرف :</td><td>{{ $masterEd->supervisor_name ?? '---' }}</td></tr>
        <tr><td class="l">عنوان الأطروحة :</td><td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td></tr>
    </table></div>
    @endif

    @if($phdEd)
    <div class="dblock"><div class="dblock-h">شهادة دكتوراه :</div><table>
        <tr><td class="l">الدولة المانحة :</td><td>{{ optional($phdEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td></tr>
        <tr><td class="l">التخصص العام :</td><td>{{ $phdEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $phdEd->rank ?? '---' }}</td></tr>
        <tr><td class="l">تاريخ التسجيل :</td><td>{{ $phdEd->registration_date ?? '---' }}</td><td class="l">تاريخ المناقشة :</td><td>{{ $phdEd->defense_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $phdEd->grant_date ?? '---' }}</td></tr>
        <tr><td class="l">اسم المشرف :</td><td>{{ $phdEd->supervisor_name ?? '---' }}</td><td class="l">عنوان الأطروحة :</td><td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td></tr>
    </table></div>
    @endif

    <div class="section">معلومات إضافية :</div>
    <div class="ebox">
        <div><b>هل المرشح جنسيته السورية :</b> {{ $candidate->is_syrian ? 'نعم' : 'لا' }}</div>
        <div><b>هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</b> {{ $application->has_previous_degree ? 'نعم' : 'لا' }}</div>
        <div><b>نظام دراسة المرشح :</b> {{ $application->study_system ?? '---' }}</div>
    </div>
</body>
</html>

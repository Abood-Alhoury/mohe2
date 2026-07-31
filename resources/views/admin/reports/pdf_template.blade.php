<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>مذكرة العرض - {{ $candidate->full_name ?? '' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }
        .fw-bold { font-weight: bold; }
        .header {
            border-bottom: 2px solid #c5a059;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 18px;
            color: #0f392b;
            margin-top: 5px;
        }
        .section-title {
            color: #dc3545;
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table, th, td {
            border: 1px solid #a0a0a0;
        }
        th, td {
            padding: 5px;
            text-align: center;
        }
        th { background-color: #f0f0f0; }
        .box {
            border: 1px solid #ccc;
            padding: 8px;
            background-color: #fcfcfc;
            margin-bottom: 10px;
        }
        .row {
            margin-bottom: 5px;
        }
        .col-half {
            display: inline-block;
            width: 48%;
        }
    </style>
</head>
<body>

    <div class="header text-center">
        <div>الجمهورية العربية السورية</div>
        <div class="fw-bold" style="font-size: 14px;">وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي</div>
        <div class="title fw-bold">(مذكرة العرض)</div>
    </div>

    <!-- البيانات الشخصية -->
    <div class="section-title">البيانات الشخصية للمرشح :</div>
    <div class="box">
        <div class="row">
            <span class="fw-bold">نوع الطلب :</span> {{ $application->request_type }} | 
            <span class="fw-bold">ID :</span> {{ $candidate->id }} | 
            <span class="fw-bold">اسم المرشح :</span> <span class="fw-bold text-primary">{{ $candidate->full_name }}</span>
        </div>
        <div class="row">
            <span class="fw-bold">الرقم الوطني :</span> {{ $candidate->national_id }} | 
            <span class="fw-bold">الجنسية :</span> {{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }} | 
            <span class="fw-bold">تاريخ الميلاد :</span> {{ $candidate->dob }}
        </div>
        <div class="row">
            <span class="fw-bold">رقم الهاتف :</span> {{ $candidate->phone }} | 
            <span class="fw-bold">رقم الموبايل :</span> {{ $candidate->mobile }} | 
            <span class="fw-bold">الوظيفة :</span> {{ $candidate->job_title }}
        </div>
        <div class="row">
            <span class="fw-bold">البريد الإلكتروني :</span> {{ $candidate->email }} | 
            <span class="fw-bold">العنوان :</span> {{ $candidate->address }}
        </div>
        <div class="row" style="margin-top: 5px;">
            <span class="fw-bold">المرشح للعمل في قسم :</span> {{ $application->work_department ?? 'الرياضيات والفيزياء' }} 
            في كلية : {{ $application->work_faculty ?? 'كلية الهندسة المدنية' }} 
            جامعة : {{ $application->workUniversity->name ?? '' }}
        </div>
    </div>

    <!-- المقررات المطلوبة -->
    <div class="section-title">المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
    <table>
        <thead>
            <tr>
                <th>اسم المقرر</th>
                <th>القسم</th>
                <th>الكلية</th>
                <th>حالة المقرر</th>
            </tr>
        </thead>
        <tbody>
            @forelse($application->courses as $crs)
            <tr>
                <td class="fw-bold">{{ $crs->course_name }}</td>
                <td>{{ $crs->department }}</td>
                <td>{{ $crs->faculty }}</td>
                <td>{{ $crs->course_status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-danger">لا توجد مقررات تطلبها الجامعة</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- الشهادات -->
    <div class="section-title">الشهادات التي يحملها المرشح :</div>

    @if($highSchoolEd)
    <div class="box">
        <div class="fw-bold text-primary">الشهادة الثانوية :</div>
        <div>
            الدولة المانحة : {{ $highSchoolEd->country->name ?? 'سوريا' }} | 
            القسم : {{ $highSchoolEd->section_name ?? 'علمي' }} | 
            تاريخ المنح : {{ $highSchoolEd->grant_date }}
        </div>
    </div>
    @endif

    @if($bachelorEd)
    <div class="box">
        <div class="fw-bold text-primary">شهادة الإجازة الجامعية :</div>
        <div>
            الدولة المانحة : {{ $bachelorEd->country->name ?? 'سوريا' }} | 
            الجهة المانحة : {{ $bachelorEd->university->name ?? 'جامعة دمشق' }} | 
            المرتبة : {{ $bachelorEd->rank }}
        </div>
        <div>
            التخصص العام : {{ $bachelorEd->general_specialization }} | 
            التخصص الدقيق : {{ $bachelorEd->exact_specialization }}
        </div>
    </div>
    @endif

    @if($masterEd)
    <div class="box">
        <div class="fw-bold text-primary">شهادة ماجستير :</div>
        <div>
            الدولة المانحة : {{ $masterEd->country->name ?? 'سوريا' }} | 
            الجهة المانحة : {{ $masterEd->university->name ?? 'جامعة دمشق' }} | 
            المرتبة : {{ $masterEd->rank }}
        </div>
        <div>
            التخصص العام : {{ $masterEd->general_specialization }} | 
            التخصص الدقيق : {{ $masterEd->exact_specialization }} | 
            اسم المشرف : {{ $masterEd->supervisor_name }}
        </div>
        <div>عنوان الأطروحة : {{ $masterEd->thesis_title }}</div>
    </div>
    @endif

    @if($phdEd)
    <div class="box">
        <div class="fw-bold text-primary">شهادة دكتوراه :</div>
        <div>
            الدولة المانحة : {{ $phdEd->country->name ?? 'مصر' }} | 
            الجهة المانحة : {{ $phdEd->university->name ?? 'جامعة القاهرة' }} | 
            المرتبة : {{ $phdEd->rank }}
        </div>
        <div>
            التخصص العام : {{ $phdEd->general_specialization }} | 
            التخصص الدقيق : {{ $phdEd->exact_specialization }} | 
            اسم المشرف : {{ $phdEd->supervisor_name }}
        </div>
        <div>عنوان الأطروحة : {{ $phdEd->thesis_title }}</div>
    </div>
    @endif

    <div class="section-title">معلومات إضافية :</div>
    <div class="box">
        <div>هل المرشح جنسيته السورية : نعم</div>
        <div>هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير ؟ True</div>
        <div>نظام دراسة المرشح : غير مطابق</div>
    </div>

</body>
</html>

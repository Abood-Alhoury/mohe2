@extends('layouts.admin')
@section('title', 'قرار أهلية تقديم المقابلة - ' . ($candidateName ?? ''))

@push('styles')
<style>
@media print { 
    @page {
        size: A4 portrait;
        margin: 5mm 8mm !important;
    }
    
    body * {
        visibility: hidden !important;
    }

    .decision-paper-wrapper, 
    .decision-paper-wrapper * {
        visibility: visible !important;
    }

    .decision-paper-wrapper {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 5mm 8mm !important;
        box-shadow: none !important;
        border: none !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        min-height: 275mm !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3" dir="rtl">
    <div class="d-flex flex-column align-items-center w-100">
        
        <!-- ACTION BAR -->
        <div class="w-100 mb-4 no-print p-3.5 bg-white shadow-sm rounded border d-flex flex-wrap justify-content-between align-items-center gap-3" style="max-width: 850px;">
            <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline-navy fw-bold px-3">
                <i class="fa-solid fa-arrow-right me-1.5"></i> العودة لجدول المقابلات
            </a>

            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <button onclick="printCleanDocument()" class="btn btn-solid-navy fw-bold px-4 py-2 shadow-xs">
                    <i class="fa-solid fa-print me-1.5"></i> طباعة قرار الأهلية
                </button>

                <button onclick="printCleanDocument()" class="btn btn-gold-cta fw-bold px-4 py-2 shadow-xs">
                    <i class="fa-solid fa-file-pdf me-1.5"></i> تنزيل PDF
                </button>
            </div>
        </div>

        <!-- DOCUMENT PAPER (A4 PROPORTIONS 210mm x 297mm) -->
        <div class="d-flex justify-content-center w-100 overflow-auto py-2">
            <div class="decision-paper-wrapper" style="direction: rtl; font-family: 'Traditional Arabic', 'IBM Plex Sans Arabic', 'Segoe UI', serif; font-size: 16px; line-height: 1.85; color: #000000; background: #ffffff; width: 210mm; min-height: 297mm; padding: 20mm 22mm 18mm 22mm; margin: 0 auto; box-shadow: 0 10px 35px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; border-radius: 2px; position: relative; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">

                <!-- TOP & MIDDLE CONTENT -->
                <div class="paper-body-content" style="flex: 1 0 auto;">

                    <!-- TOP HEADER -->
                    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
                        <tr>
                            <!-- Right: Arabic Header -->
                            <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5;">
                                الجمهورية العربية السورية<br>
                                مجلس التعليم العالي<br>
                                لجنة التأهيل ومعادلة الدرجات العلمية
                            </td>

                            <!-- Center: Official Emblem Logo -->
                            <td style="width: 20%; text-align: center; vertical-align: middle;">
                                <img src="{{ asset('assets/logo.jpg') }}" alt="شعار مجلس التعليم العالي" style="width: 88px; height: 88px; object-fit: contain; border-radius: 50%;">
                            </td>

                            <!-- Left: English Header -->
                            <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #111111;">
                                Syrian Arab Republic<br>
                                council of Higher Education
                            </td>
                        </tr>
                    </table>

                    <!-- DOCUMENT TITLE -->
                    <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px;">
                        قرار أهلية وتحديد موعد المقابلة رقم / &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; / ل.م
                    </div>

                    <!-- PREAMBLE -->
                    <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85;">
                        <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
                        <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
                        <div>وقرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15 وتعديلاته.</div>
                        <div>وكتاب {{ $uniName }} رقم /{{ $uniReqNo }}/ تاريخ {{ $uniReqDate }}</div>
                    </div>

                    <!-- DECREE HEADER -->
                    <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline;">
                        يقرر ما يأتي:
                    </div>

                    <!-- ARTICLE 1 -->
                    <div class="article-div" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15;">
                        <strong>المادة 1-</strong> منح السيد <strong>{{ $candidateName }}</strong>، الحائز درجة الماجستير في <strong>{{ $masterSpec }}</strong> الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baSpec }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، الأهلية لتقديم المقابلة الشفهية والعملية المطلوبة للتعادل في اختصاص <strong>{{ $teachingDept }}</strong> بالجامعات الخاصة السورية.
                    </div>

                    <!-- ARTICLE 2 (INTERVIEW SCHEDULE) -->
                    <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px; line-height: 2.0;">
                        <strong>المادة 2-</strong> يُحدد موعد المقابلة الشفهية والعملية للمرشح المذكور يوم <strong>{{ $interviewDate }}</strong> في <strong>{{ $interviewTime }}</strong>، في مقر وزارة التعليم العالي والبحث العلمي - القاعة الرئيسية للمقابلات.
                    </div>

                    <!-- ARTICLE 3 -->
                    <div class="article-div" style="font-size: 16.5px; margin-bottom: 28px;">
                        <strong>المادة 3-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
                    </div>

                    <!-- SIGNATURES SECTION -->
                    <table class="signatures-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; border: none !important;" border="0">
                        <tr>
                            <!-- Right Column: Director of Equivalence -->
                            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 15px; font-weight: bold; line-height: 1.5;">
                                <div>مدير التعادل والإنتاج العلمي</div>
                                <div style="margin-top: 8px; font-size: 16px;">المهندس عمار هلال</div>
                            </td>

                            <!-- Left Column: Secretary General & Chairman -->
                            <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 15px; font-weight: bold; line-height: 1.5;">
                                <div>
                                    <div>أمين مجلس التعليم العالي</div>
                                    <div style="margin-top: 8px; font-size: 16px;">الدكتور علي الجاسم</div>
                                </div>

                                <div style="margin-top: 28px; line-height: 1.35;">
                                    <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                                    <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                                    <div style="margin-top: 8px; font-size: 16.5px;">الدكتور عبد الحميد الخالد</div>
                                </div>
                            </td>
                        </tr>
                    </table>

                </div>

                <!-- FOOTER: COPIES TO -->
                <div class="copies-div" style="font-size: 14px; line-height: 1.7; page-break-inside: avoid; border-top: 1px solid #cbd5e1; padding-top: 12px; margin-top: auto;">
                    <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">صورة إلى:</div>
                    <div>- أمانة سر مجلس التعليم العالي (للتبليغ وإجراء المقابلة)</div>
                    <div>- الجامعة الخاصة المعنية ({{ $uniName }})</div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function printCleanDocument() {
    const originalTitle = document.title;
    const rawName = "{{ $candidateName }}".replace(/[/\\?%*:|"<>]/g, '').trim();
    document.title = "قرار_أهلية_مقابلة_" + (rawName || "المتقدم");
    window.print();
    setTimeout(function() {
        document.title = originalTitle;
    }, 1500);
}
</script>
@endsection

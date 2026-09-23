@extends('layouts.admin')
@section('title', ($docType === 'eligibility' ? 'توليد قرار أهلية رسمي - ' : 'توليد قرار معادلة رسمي - ') . ($candidateName ?? ''))

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap');

/* الخط الأساسي وتأثيرات التحرير المباشر على الشاشة */
.decision-paper-wrapper, 
.decision-paper-wrapper * {
    font-family: 'IBM Plex Sans Arabic', 'Traditional Arabic', 'Segoe UI', Tahoma, sans-serif !important;
}

[contenteditable="true"] {
    transition: background-color 0.2s ease, border-color 0.2s ease;
    border-radius: 4px;
    padding: 1px 3px;
}
[contenteditable="true"]:hover {
    background-color: #f8fafc !important;
    outline: 1px dashed #cbd5e1 !important;
    cursor: text;
}
[contenteditable="true"]:focus {
    background-color: #ffffff !important;
    outline: 2px solid #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column align-items-center w-100">
        
        <!-- شريط أدوات القرار (التبديل وزر الطباعة/PDF الموحد) -->
        <div class="w-100 mb-4 no-print p-3.5 bg-white shadow-sm rounded border d-flex flex-wrap justify-content-between align-items-center gap-3" style="max-width: 850px;">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3">
                    <i class="fa-solid fa-arrow-right me-1.5"></i> عودة
                </a>

                @if(!$isFacultyPermission && !$isApplied)
                    <div class="btn-group shadow-2xs rounded" role="group">
                        <a href="{{ route('admin.reports.generate_decision', ['id' => $application->id, 'type' => 'equivalence']) }}" 
                           class="btn btn-sm fw-bold px-3 py-2 {{ $docType === 'equivalence' ? 'btn-solid-navy' : 'btn-outline-navy' }}">
                            <i class="fa-solid fa-file-signature me-1.5"></i> 1. قرار التعادل (المعادلة)
                        </a>
                        @if($canEligibility)
                            <a href="{{ route('admin.reports.generate_decision', ['id' => $application->id, 'type' => 'eligibility']) }}" 
                               class="btn btn-sm fw-bold px-3 py-2 {{ $docType === 'eligibility' ? 'btn-solid-navy' : 'btn-outline-navy' }}">
                                <i class="fa-solid fa-award me-1.5"></i> 2. قرار الأهلية
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-2 opacity-60" disabled title="قرار الأهلية متاح فقط عندما تكون حالة الطلب (بانتظار إصدار القرار)">
                                <i class="fa-solid fa-lock me-1.5"></i> 2. قرار الأهلية
                            </button>
                        @endif
                    </div>
                @elseif($isApplied)
                    <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-7 fw-bold">
                        <i class="fa-solid fa-briefcase me-1.5"></i> قرار تعادل ماجستير تطبيقي (تكليف تدريس جوانب تطبيقية)
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-7 fw-bold">
                        <i class="fa-solid fa-stamp me-1.5"></i> قرار السماح بالتدريس (رسمي)
                    </span>
                @endif
            </div>

            <!-- زر موحد للطباعة وحفظ الـ PDF مع الحفاظ على التعديلات الحية -->
            <div class="d-flex align-items-center gap-2.5">
                <button type="button" onclick="printOrSavePdf()" class="btn btn-solid-navy fw-bold px-4 py-2 shadow-xs" title="طباعة أو تصدير PDF مطابق للتعديلات">
                    <i class="fa-solid fa-print me-1.5"></i> طباعة / تصدير PDF
                </button>
            </div>
        </div>

        <!-- ورقة القرار الرسمية المعروضة -->
        <div class="d-flex justify-content-center w-100 overflow-auto py-2">
            @include('admin.reports.generated_decision_paper')
        </div>

    </div>
</div>

<script>
function printOrSavePdf() {
    // 1. تحديد حاوية الورقة المعروضة حالياً على الشاشة
    const paper = document.querySelector('.decision-paper-wrapper');
    if (!paper) {
        window.print();
        return;
    }

    // 2. التقاط كود الـ HTML الحي للورقة بكافة التعديلات اليدوية (رقم القرار، النصوص، التواريخ)
    const paperContent = paper.innerHTML;

    // 3. تجهيز اسم الملف التلقائي عند اختيار "Save as PDF"
    const rawName = "{{ $candidateName }}".replace(/[/\\?%*:|"<>]/g, '').trim();
    const appNo = "{{ $application->application_no ?? $application->id }}".replace(/[/\\?%*:|"<>]/g, '').trim();
    const docPrefix = "{{ $docType === 'eligibility' ? 'قرار_أهلية_' : 'قرار_معادلة_' }}";
    const targetTitle = docPrefix + rawName + '_طلب_' + appNo;

    // 4. إنشاء إطار طباعة معزول (Iframe) لمنع أي تداخل مع باقي عناصر الصفحة
    let printFrame = document.getElementById('secure-print-frame');
    if (!printFrame) {
        printFrame = document.createElement('iframe');
        printFrame.id = 'secure-print-frame';
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);
    }

    const frameDoc = printFrame.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>${targetTitle}</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap');
                
                @page {
                    size: A4 portrait;
                    margin: 8mm 12mm;
                }
                
                body {
                    font-family: 'IBM Plex Sans Arabic', 'Traditional Arabic', Tahoma, sans-serif !important;
                    direction: rtl;
                    text-align: right;
                    color: #000000;
                    background: #ffffff;
                    margin: 0;
                    padding: 0;
                    font-size: 14.5px;
                    line-height: 1.6;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                
                * {
                    box-sizing: border-box;
                    font-family: inherit;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                td {
                    vertical-align: top;
                }

                [contenteditable="true"] {
                    outline: none !important;
                    border: none !important;
                    background: transparent !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                }
            </style>
        </head>
        <body>
            <div class="decision-paper-wrapper" style="width: 100%; margin: 0 auto; background: #fff;">
                ${paperContent}
            </div>
        </body>
        </html>
    `);
    frameDoc.close();

    // 5. تشغيل نافذة الطباعة بعد تحميل الخطوط والصور داخل الإطار
    setTimeout(() => {
        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();
    }, 350);
}
</script>
@endsection
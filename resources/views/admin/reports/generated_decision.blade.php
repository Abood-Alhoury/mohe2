@extends('layouts.admin')
@section('title', ($docType === 'eligibility' ? 'توليد قرار أهلية رسمي - ' : 'توليد قرار معادلة رسمي - ') . ($candidateName ?? ''))

@push('styles')
<style>
/* Interactive Live Editing Hover & Focus Effects */
[contenteditable="true"] {
    transition: background-color 0.2s ease, border-color 0.2s ease;
    border-radius: 4px;
    padding: 2px 4px;
}
[contenteditable="true"]:hover {
    background-color: #f8fafc !important;
    outline: 1px dashed #cbd5e1 !important;
    cursor: text;
}
[contenteditable="true"]:focus {
    background-color: #ffffff !important;
    outline: 2px solid #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
}

@media print { 
    @page {
        size: A4 portrait;
        margin: 5mm 8mm !important;
    }

    [contenteditable="true"], [contenteditable="true"]:hover, [contenteditable="true"]:focus {
        background-color: transparent !important;
        outline: none !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
    
    /* Hide all site chrome & show ONLY the decision paper */
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

    .paper-body-content {
        flex: 1 0 auto !important;
    }

    .copies-div {
        margin-top: auto !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column align-items-center w-100">
        
        <!-- SYSTEM ACTION BAR (DOCUMENT TYPE TOGGLE + PRINT & DOWNLOAD) -->
        <div class="w-100 mb-4 no-print p-3.5 bg-white shadow-sm rounded border d-flex flex-wrap justify-content-between align-items-center gap-3" style="max-width: 850px;">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3">
                    <i class="fa-solid fa-arrow-right me-1.5"></i> عودة
                </a>

                <!-- TOGGLE DECISION TYPES: EQUIVALENCE vs ELIGIBILITY -->
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
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-2 opacity-60" disabled title="قرار الأهلية متاح فقط عندما تكون حالة الطلب (بانتظار إصدار القرار) بعد اجتياز المقابلة">
                            <i class="fa-solid fa-lock me-1.5"></i> 2. قرار الأهلية (بانتظار إصدار القرار فقط)
                        </button>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <button onclick="printCleanDocument()" class="btn btn-solid-navy fw-bold px-4 py-2 shadow-xs">
                    <i class="fa-solid fa-print me-1.5"></i> طباعة القرار
                </button>

                <a href="{{ route('admin.reports.download_generated_decision_pdf', ['id' => $application->id, 'type' => $docType]) }}" target="_blank" class="btn btn-gold-cta fw-bold px-4 py-2 shadow-xs">
                    <i class="fa-solid fa-file-pdf me-1.5"></i> تنزيل PDF
                </a>
            </div>
        </div>

        <!-- GENERATION NOTICE BANNER -->
        <div class="w-100 mb-4 no-print p-3 rounded border border-primary-subtle shadow-xs" style="max-width: 850px; background-color: #f0f9ff; color: #0369a1;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles fs-4" style="color: #0284c7;"></i>
                <div>
                    <h6 class="fw-bold mb-0.5" style="color: #0369a1;">{{ $decisionTitle }}</h6>
                    <p class="mb-0 small text-secondary">معاينة القرار بحجم A4 الحقيقي. يمكن التبديل بين (قرار المعادلة) و(قرار الأهلية) من الأزرار بالأعلى، وتعديل النصوص مباشرة قبل الطباعة أو التحميل.</p>
                </div>
            </div>
        </div>

        <!-- DECISION DOCUMENT PAPER PREVIEW (EXACT A4 SHEET PROPORTIONS 210mm x 297mm) -->
        <div class="d-flex justify-content-center w-100 overflow-auto py-2">
            @include('admin.reports.generated_decision_paper')
        </div>

    </div>
</div>

<script>
function printCleanDocument() {
    const originalTitle = document.title;
    const rawName = "{{ $candidateName }}".replace(/[/\\?%*:|"<>]/g, '').trim();
    const docPrefix = "{{ $docType === 'eligibility' ? 'قرار_أهلية_رسمي_' : 'قرار_معادلة_رسمي_' }}";
    document.title = docPrefix + (rawName || "المتقدم");
    
    window.print();
    
    setTimeout(function() {
        document.title = originalTitle;
    }, 1500);
}
</script>
@endsection

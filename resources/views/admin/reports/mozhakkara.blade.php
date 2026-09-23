@extends('layouts.admin')
@section('title', 'مذكرة العرض (A4) - ' . ($candidate->full_name ?? ''))

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap');

@media print { 
    @page {
        size: A4 portrait;
        margin: 6mm 8mm !important;
    }
    
    .no-print, .mohe-header, .mohe-nav, header, footer, nav { 
        display: none !important; 
    } 
    
    body * {
        visibility: hidden !important;
    }
    
    .moz-wrapper, .moz-wrapper * {
        visibility: visible !important;
    }
    
    .moz-wrapper { 
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
        min-height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    } 
    
    .moz-section {
        background-color: #1A2A44 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .mt, .dblock, .ct, .dt, .cname, .wblock {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    
    body { 
        background: #ffffff !important; 
        margin: 0 !important; 
        padding: 0 !important; 
    } 
}

.moz-wrapper { 
    direction: rtl; 
    font-family: 'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, system-ui, sans-serif; 
    font-size: 12px; 
    line-height: 1.45;
    background: #ffffff; 
    width: 210mm;
    max-width: 210mm;
    min-height: 297mm; 
    margin: 0 auto 30px !important; 
    padding: 10mm 14mm 10mm 14mm; 
    box-shadow: 0px 8px 30px rgba(26, 42, 68, 0.12); 
    border: 1px solid #e2e8f0; 
    border-top: 4px solid var(--heritage-gold, #C5A059) !important;
    border-radius: 2px;
    color: #111C2C; 
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.moz-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    margin-bottom: 8px; 
    padding-bottom: 8px; 
    border-bottom: 3px double var(--heritage-gold, #C5A059); 
}

.moz-header-text .ar { 
    font-size: 15px; 
    font-weight: 700; 
    color: #1A2A44 !important; 
}

.moz-header-text .en { 
    font-size: 10px; 
    color: #555; 
    letter-spacing: 0.5px; 
}

.moz-title { 
    text-align: center; 
    font-size: 17px; 
    font-weight: 700; 
    color: #1A2A44 !important; 
    margin: 8px 0 10px; 
}

.moz-section { 
    background-color: #1A2A44 !important; 
    color: #ffffff !important; 
    font-weight: 700; 
    font-size: 12px; 
    padding: 4px 10px; 
    margin: 8px 0 4px; 
    border-right: 4px solid var(--heritage-gold, #C5A059); 
    border-radius: 2px;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.mt { width: 100%; border-collapse: collapse; margin: 0; }
.mt td { padding: 3.5px 8px; font-size: 12px; border: 1px solid var(--outline-variant, #c5c6ce); color: #111C2C; line-height: 1.4; }
.mt td.l { background: #f5f3f5; font-weight: 700; color: #1A2A44 !important; white-space: nowrap; width: 140px; }

.cname { 
    background: #f0f3ff; 
    border: 1px solid var(--outline-variant, #c5c6ce); 
    border-right: 4px solid var(--heritage-gold, #C5A059);
    padding: 5px 10px; 
    font-size: 13.5px; 
    font-weight: 700; 
    color: #1A2A44 !important; 
    text-align: center; 
    margin: 5px 0; 
}

.wblock { 
    background: #faf9fb; 
    border: 1px solid var(--outline-variant, #c5c6ce); 
    padding: 6px 10px; 
    font-size: 12px; 
    margin-top: 4px; 
    color: #111C2C; 
}

.dblock { border: 1px solid var(--outline-variant, #c5c6ce); margin: 6px 0; border-radius: 3px; overflow: hidden; }
.dblock-h { background: #1A2A44 !important; color: #ffffff !important; font-weight: 700; font-size: 12px; padding: 4px 10px; border-bottom: 2px solid var(--heritage-gold, #C5A059); }

.dt { width: 100%; border-collapse: collapse; font-size: 11.5px; margin: 0; }
.dt th { background: #1A2A44 !important; color: #ffffff !important; padding: 4px 6px; text-align: center; font-weight: 600; }
.dt td { border: 1px solid var(--outline-variant, #c5c6ce); padding: 3.5px 6px; text-align: center; color: #111C2C; }

.ct { width: 100%; border-collapse: collapse; font-size: 12px; }
.ct th { background: #1A2A44 !important; color: #ffffff !important; padding: 4px 8px; text-align: center; font-weight: 600; }
.ct td { border: 1px solid var(--outline-variant, #c5c6ce); padding: 4px 8px; text-align: center; color: #111C2C; }

.ebox { 
    background: #faf9fb; 
    border: 1px solid var(--outline-variant, #c5c6ce); 
    border-right: 4px solid var(--heritage-gold, #C5A059);
    padding: 6px 12px; 
    margin-top: 6px; 
    font-size: 12px; 
    color: #111C2C; 
}
.subh { background: #f0f3ff; color: #1A2A44 !important; font-weight: 700; font-size: 12px; padding: 3px 8px; border-bottom: 1px solid var(--outline-variant, #c5c6ce); }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column align-items-center w-100">
        <!-- SYSTEM ACTION BAR -->
        <div class="w-100 mb-4 no-print p-3.5 bg-white shadow-sm rounded border d-flex flex-wrap justify-content-between align-items-center gap-3" style="max-width: 210mm;">
            <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3">
                <i class="fa-solid fa-arrow-right me-1.5"></i> العودة لجدول الطلبات
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-solid-navy fw-bold px-4 py-2 shadow-xs">
                    <i class="fa-solid fa-print me-1.5"></i> طباعة A4
                </button>
                <a href="{{ route('admin.reports.download_pdf', $application->id) }}" target="_blank" class="btn btn-gold-cta fw-bold px-3 py-2 shadow-xs">
                    <i class="fa-solid fa-file-pdf me-1.5"></i> تنزيل PDF
                </a>
                <a href="{{ route('admin.reports.download_consolidated_pdf', $application->id) }}" target="_blank" class="btn btn-outline-gold fw-bold px-3 py-2" title="تنزيل حزمة الملف المدموج (مذكرة العرض + كافـة المرفقات والشهادات كملف PDF واحد)">
                    <i class="fa-solid fa-layer-group me-1.5"></i> المرفقات المدمجة (PDF)
                </a>
            </div>
        </div>

        <!-- MOZHAKKARA DOCUMENT PAPER (EXACT A4 PAPER DIMENSIONS 210mm x 297mm) -->
        <div class="d-flex justify-content-center w-100 overflow-auto py-2">
            @include('admin.reports.mozhakkara_paper_snippet')
        </div>
    </div>
</div>
@endsection

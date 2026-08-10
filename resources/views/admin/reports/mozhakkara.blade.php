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
    width: 100%;
    max-width: 920px; 
    margin: 0 auto 30px !important; 
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
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column align-items-center w-100">
        <!-- SYSTEM ACTION BAR -->
        <div class="w-100 mb-4 no-print p-3 bg-white shadow-sm rounded border border-secondary-subtle d-flex justify-content-between align-items-center" style="max-width: 920px;">
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
        <div class="w-100" style="max-width: 920px;">
            @include('admin.reports.mozhakkara_paper_snippet')
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'عرض المرفقات المدمجة - ' . ($application->candidate->full_name ?? ''))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="{{ route('admin.reports.show', $application->id) }}" class="btn btn-secondary fw-bold">
        <i class="fa-solid fa-arrow-right me-1"></i> العودة لمذكرة العرض
    </a>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.download_consolidated_pdf', $application->id) }}" target="_blank" class="btn btn-gold-cta fw-bold shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> 📥 تنزيل الحزمة المدمجة كاملة كـ PDF
        </a>
        <button onclick="window.print()" class="btn btn-solid-navy fw-bold">
            <i class="fa-solid fa-print me-1"></i> طباعة الحزمة المدمجة كاملة
        </button>
    </div>
</div>

<div class="alert alert-info border-2 shadow-sm d-flex align-items-center gap-3">
    <i class="fa-solid fa-layer-group fs-2 text-info"></i>
    <div>
        <h6 class="alert-heading mb-1 fw-bold">المستند المدمج لطلب التعادل رقم {{ $application->application_no }}</h6>
        <div>تتألف الحزمة المدمجة من <strong>الصفحة الأولى (مذكرة العرض)</strong> تليها كافة الوثائق والمرفقات الصادرة والشهادات المرفوعة من الطالب والجامعة.</div>
    </div>
</div>

<!-- PAGE 1: Mozhakkara Document Paper -->
<div class="card mb-4 border-2 border-primary shadow-sm" style="border-radius: 10px; overflow: hidden;">
    <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center py-2.5">
        <span><i class="fa-solid fa-file-invoice me-2"></i> الصفحة 1 : مذكرة العرض المحدثة وتفاصيل التكليف</span>
        <span class="badge bg-white text-primary fw-bold px-3 py-1">الصفحة الأولى من الملف المدمج</span>
    </div>
    <div class="card-body p-3 bg-light">
        @include('admin.reports.mozhakkara_paper_snippet')
    </div>
</div>

<!-- PAGE 2+: Uploaded Document Attachments -->
@php
    $attachments = collect();
    
    // 1. Current Application Attachments (Latest 3 Transfer Documents & Degree attachments)
    foreach($application->educations as $ed) {
        foreach($ed->attachments->sortByDesc('id') as $att) {
            if (!$attachments->contains('id', $att->id)) {
                $attachments->push($att);
            }
        }
    }

    // 2. Parent Application Attachments (if transfer application)
    if ($application->parentApplication) {
        foreach($application->parentApplication->educations as $pEd) {
            foreach($pEd->attachments->sortByDesc('id') as $att) {
                if (!$attachments->contains('id', $att->id)) {
                    $attachments->push($att);
                }
            }
        }
    }
@endphp

<h5 class="fw-bold text-dark my-4"><i class="fa-solid fa-paperclip me-2 text-danger"></i> وثائق ومرفقات الطالب المدمجة (الصفحات التالية) :</h5>

@forelse($attachments as $index => $attachment)
<div class="card mb-4 border shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">
            <i class="fa-solid fa-file-pdf me-2 text-danger"></i> 
            المرفق رقم {{ $index + 2 }} : {{ $attachment->notes ?: ($attachment->attachmentType->name ?? 'وثيقة رسمية') }}
            @if($attachment->notes && $attachment->attachmentType)
                <small class="text-muted ms-2">({{ $attachment->attachmentType->name }})</small>
            @endif
        </span>
        <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-expand me-1"></i> فتح في نافذة مستقلة
        </a>
    </div>
    <div class="card-body p-0 text-center bg-secondary-subtle">
        @if(str_ends_with(strtolower($attachment->file_path), '.pdf'))
            <iframe src="{{ asset('storage/' . $attachment->file_path) }}" style="width: 100%; height: 750px;" frameborder="0"></iframe>
        @else
            <img src="{{ asset('storage/' . $attachment->file_path) }}" class="img-fluid p-3" style="max-height: 800px;" alt="مرفق شهادة">
        @endif
    </div>
</div>
@empty
<div class="card border p-4 text-center text-muted mb-4 bg-light">
    <i class="fa-solid fa-folder-open fs-1 mb-2 text-secondary"></i>
    <h6>جميع المرفقات المطلوبة لهذا الطلب مدمجة ضمن الملف الأساسي.</h6>
</div>
@endforelse

@endsection

@extends('layouts.admin')

@section('title', 'تعديل بيانات وإضبارة المرشح - ' . ($candidate->full_name ?? '') . ' | مجلس التعليم العالي')

@push('styles')
<style>
    :root {
        --imperial-navy: #0e1e38;
        --imperial-navy-dark: #081224;
        --heritage-gold: #c59b27;
        --heritage-gold-light: #f5d77f;
        --heritage-gold-glow: rgba(197, 155, 39, 0.25);
        --emerald: #10b981;
        --emerald-bg: #ecfdf5;
        --bg-page: #f1f5f9;
        --surface-card: #ffffff;
        --border-subtle: #e2e8f0;
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* STICKY DOSSIER HEADER */
    .sticky-dossier-bar {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1.5px solid var(--border-subtle);
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        padding: 14px 24px;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }

    .dossier-bar-content {
        max-width: 1440px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .candidate-summary {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .candidate-avatar {
        width: 48px;
        height: 48px;
        background: #e0f2fe;
        color: #0369a1;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        border: 1.5px solid #bae6fd;
        flex-shrink: 0;
    }

    .candidate-meta-text h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--imperial-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 3px;
    }

    .meta-chips-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--text-muted);
        flex-wrap: wrap;
    }

    .chip-badge {
        background: #f1f5f9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
        font-family: monospace;
    }

    .chip-status-pill {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.76rem;
    }

    .dossier-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-act {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.86rem;
        font-weight: 700;
        border: 1.5px solid var(--border-subtle);
        background: white;
        color: var(--text-dark);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-act:hover {
        background: #f8fafc;
        border-color: var(--imperial-navy);
        color: var(--imperial-navy);
    }

    /* SECTION NAV TABS */
    .section-nav-tabs {
        max-width: 1440px;
        margin: 0 auto 22px;
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 6px;
    }

    .tab-anchor-btn {
        background: white;
        border: 1.5px solid var(--border-subtle);
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .tab-anchor-btn:hover {
        border-color: var(--heritage-gold);
        color: var(--imperial-navy);
        background: #fffdfa;
    }

    .tab-anchor-btn.active {
        background: var(--imperial-navy);
        color: white;
        border-color: var(--imperial-navy);
        box-shadow: 0 4px 12px rgba(14, 30, 56, 0.15);
    }

    .tab-anchor-btn.active i {
        color: var(--heritage-gold-light);
    }

    /* MAIN CONTENT SECTIONS */
    .sections-wrapper {
        max-width: 1440px;
        margin: 0 auto;
        display: grid;
        gap: 24px;
    }

    .form-section-card {
        background: white;
        border-radius: 14px;
        border: 1.5px solid var(--border-subtle);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        overflow: hidden;
        scroll-margin-top: 100px;
    }

    .section-header {
        padding: 16px 24px;
        background: #f8fafc;
        border-bottom: 1.5px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .section-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--imperial-navy);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-section-save {
        background: linear-gradient(135deg, var(--heritage-gold) 0%, #aa841d 100%);
        color: #0b1526;
        font-weight: 800;
        font-size: 0.84rem;
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px var(--heritage-gold-glow);
        transition: all 0.2s;
    }

    .btn-section-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(197, 155, 39, 0.35);
        color: #081224;
    }

    .section-body {
        padding: 24px;
    }

    /* INPUTS MATRIX GRID */
    .inputs-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    @media (max-width: 1024px) {
        .inputs-grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 650px) {
        .inputs-grid-3 { grid-template-columns: 1fr; }
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field-group.full-width {
        grid-column: 1 / -1;
    }

    .field-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--imperial-navy);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .field-control {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1.5px solid var(--border-subtle);
        background: #f8fafc;
        color: var(--text-dark);
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s;
    }

    .field-control:focus {
        background: #ffffff;
        border-color: var(--imperial-navy);
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    /* SECTION ATTACHMENT STRIP */
    .section-attachments-strip {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px dashed var(--border-subtle);
    }

    .strip-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--text-muted);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .attachment-badges-flow {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .doc-badge-pill {
        background: #ffffff;
        border: 1.5px solid #fecaca;
        color: #dc2626;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.15s;
    }

    .doc-badge-pill:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    .upload-field-row {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--border-subtle);
        flex-wrap: wrap;
    }

    /* REPOSITORY GRID FOR ALL DOCUMENTS (SECTION 5) */
    .all-docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }

    .doc-card-item {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s;
    }

    .doc-card-item:hover {
        border-color: var(--heritage-gold);
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
        transform: translateY(-2px);
    }

    .doc-card-info {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow: hidden;
    }

    .pdf-icon-sq {
        width: 38px;
        height: 38px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .doc-card-texts span {
        display: block;
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--imperial-navy);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .doc-card-texts small {
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    .btn-quick-preview {
        padding: 6px 12px;
        background: #f8fafc;
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 800;
        color: #0284c7;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: 0.15s;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-quick-preview:hover {
        background: #0284c7;
        color: white;
        border-color: #0284c7;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0" dir="rtl">

    @php
        $isDocApp = str_contains($application->request_type ?? '', 'دكتور') || ($phdEd !== null);
        $firstLetter = mb_substr($candidate->full_name ?? 'م', 0, 1, 'UTF-8');
        
        // Collect all attachments across educations
        $allAtts = collect();
        foreach($application->educations as $edItem) {
            foreach($edItem->attachments as $aItem) {
                $allAtts->push($aItem);
            }
        }
    @endphp

    <!-- STICKY DOSSIER HEADER -->
    <div class="sticky-dossier-bar">
        <div class="dossier-bar-content">
            <div class="candidate-summary">
                <div class="candidate-avatar">{{ $firstLetter }}</div>
                <div class="candidate-meta-text">
                    <h2>
                        {{ $candidate->full_name ?? 'غير محدد' }}
                        <span class="chip-status-pill">
                            <i class="fa-solid fa-circle-check"></i> {{ $application->status }}
                        </span>
                    </h2>
                    <div class="meta-chips-row">
                        <span class="chip-badge">{{ $application->application_no }}</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-graduation-cap text-primary"></i> {{ $application->request_type }}</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-building-columns text-secondary"></i> {{ $application->workUniversity->name ?? 'غير محددة' }}</span>
                    </div>
                </div>
            </div>

            <div class="dossier-actions">
                <a href="{{ route('admin.applications.index') }}" class="btn-act">
                    <i class="fa-solid fa-arrow-right"></i> عودة لجدول الطلبات
                </a>
                <a href="#section-5" class="btn-act" style="border-color: var(--heritage-gold); color: var(--imperial-navy);">
                    <i class="fa-solid fa-folder-closed" style="color: var(--heritage-gold);"></i> المرفقات ({{ $allAtts->count() }})
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION NAV TABS -->
    <nav class="section-nav-tabs">
        <a href="#section-1" class="tab-anchor-btn active"><i class="fa-solid fa-file-contract"></i> 1. كتاب التقويم الرسمي</a>
        <a href="#section-2" class="tab-anchor-btn"><i class="fa-solid fa-user-tie"></i> 2. البيانات الشخصية والاتصال</a>
        @if($phdEd)
            <a href="#section-phd" class="tab-anchor-btn"><i class="fa-solid fa-graduation-cap"></i> 3. درجة الدكتوراه</a>
        @endif
        @if($masterEd)
            <a href="#section-3" class="tab-anchor-btn"><i class="fa-solid fa-book-open"></i> {{ $isDocApp ? '4. الماجستير' : '3. الماجستير' }}</a>
        @endif
        @if($isFacultyPermission || $govEd)
            <a href="#section-gov" class="tab-anchor-btn"><i class="fa-solid fa-building-columns"></i> التعيين الحكومي</a>
        @endif
        @if($bachelorEd)
            <a href="#section-4" class="tab-anchor-btn"><i class="fa-solid fa-graduation-cap"></i> الإجازة الجامعية الأولى</a>
        @endif
        <a href="#section-5" class="tab-anchor-btn"><i class="fa-solid fa-box-archive"></i> المستودع الشامل للمرفقات</a>
    </nav>

    <!-- SECTIONS WRAPPER -->
    <main class="sections-wrapper">

        <!-- SECTION 1: UNIVERSITY EVALUATION LETTER -->
        <section class="form-section-card" id="section-1">
            <form action="{{ route('admin.applications.update_details', $application->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-file-signature" style="color: var(--heritage-gold);"></i>
                        1. بيانات كتاب طلب التقييم الصادر عن الجامعة الخاصة
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ تعديلات كتاب الجامعة
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">رقم كتاب الجامعة الخاصة :</label>
                            <input type="text" name="new_uni_request_no" class="field-control fw-bold" value="{{ old('new_uni_request_no', $application->new_uni_request_no) }}" placeholder="مثال: 105">
                        </div>
                        <div class="field-group">
                            <label class="field-label">تاريخ كتاب الجامعة الخاصة :</label>
                            <input type="date" name="new_uni_request_date" class="field-control" value="{{ old('new_uni_request_date', $application->new_uni_request_date) }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">الجامعة الخاصة الطالبة للتقويم :</label>
                            <select name="work_university_id" class="field-control">
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ $application->work_university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <!-- SECTION 2: PERSONAL DATA -->
        <section class="form-section-card" id="section-2">
            <form action="{{ route('admin.applications.update_candidate', $application->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-address-card" style="color: var(--heritage-gold);"></i>
                        2. البيانات الشخصية وبيانات الاتصال للمرشح
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ البيانات الشخصية
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">اسم المرشح الكامل :</label>
                            <input type="text" name="full_name" class="field-control fw-bold" value="{{ $candidate->full_name }}" required>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الرقم الوطني (11 خانة) :</label>
                            <input type="text" name="national_id" class="field-control fw-bold" value="{{ $candidate->national_id }}" style="font-family: monospace;">
                        </div>
                        <div class="field-group">
                            <label class="field-label">تاريخ الميلاد :</label>
                            <input type="date" name="dob" class="field-control" value="{{ $candidate->dob }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">اسم الأب :</label>
                            <input type="text" name="father_name" class="field-control" value="{{ $candidate->father_name }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">اسم الأم ونسبتها :</label>
                            <input type="text" name="mother_name" class="field-control" value="{{ $candidate->mother_name }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">الجنسية :</label>
                            <input type="text" class="field-control bg-light" value="{{ $candidate->is_syrian ? 'سورية' : (optional($candidate->nationality)->name ?? 'سورية') }}" readonly>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الجنس :</label>
                            <select name="gender" class="field-control">
                                <option value="ذكر" {{ $candidate->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                <option value="أنثى" {{ $candidate->gender == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الوظيفة / الصفة :</label>
                            <input type="text" name="job_title" class="field-control" value="{{ $candidate->job_title }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">رقم الهاتف الجوال :</label>
                            <input type="text" name="mobile" class="field-control fw-bold" value="{{ $candidate->mobile }}" dir="ltr">
                        </div>
                        <div class="field-group">
                            <label class="field-label">البريد الإلكتروني :</label>
                            <input type="email" name="email" class="field-control" value="{{ $candidate->email }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">رقم الهاتف الأرضي :</label>
                            <input type="text" name="phone" class="field-control" value="{{ $candidate->phone }}" placeholder="اختياري">
                        </div>
                        <div class="field-group full-width">
                            <label class="field-label">العنوان ومكان الإقامة التفصيلي :</label>
                            <input type="text" name="address" class="field-control" value="{{ $candidate->address }}">
                        </div>
                    </div>
                </div>
            </form>
        </section>

        {{-- SECTION PHD (IF APPLICABLE) --}}
        @if($phdEd)
        <section class="form-section-card" id="section-phd">
            <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="education_id" value="{{ $phdEd->id }}">
                <input type="hidden" name="education_level_id" value="{{ $phdEd->education_level_id }}">

                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-graduation-cap" style="color: var(--heritage-gold);"></i>
                        شهادة درجة الدكتوراه
                        <span style="font-size: 0.74rem; background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 4px; font-weight: bold;">المؤهل الأساسي</span>
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ تعديلات الدكتوراه
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">1. الجامعة المانحة لشهادة الدكتوراه :</label>
                            <select name="university_id" class="field-control fw-bold">
                                <option value="">-- اختر الجامعة --</option>
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ $phdEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="university_other" class="field-control mt-1.5" value="{{ $phdEd->university_other }}" placeholder="أو اسم جامعة أخرى (إذا لم تكن بالقائمة)">
                        </div>
                        <div class="field-group">
                            <label class="field-label">2. الكلية :</label>
                            <input type="text" name="faculty" class="field-control" value="{{ $phdEd->faculty ?: $phdEd->general_specialization }}" placeholder="مثال: كلية الحقوق">
                        </div>
                        <div class="field-group">
                            <label class="field-label">3. القسم :</label>
                            <input type="text" name="department" class="field-control" value="{{ $phdEd->department ?: $phdEd->exact_specialization }}" placeholder="مثال: القانون العام">
                        </div>
                        <div class="field-group">
                            <label class="field-label">4. تاريخ / سنة المنح :</label>
                            <input type="date" name="grant_date" class="field-control" value="{{ $phdEd->grant_date }}">
                        </div>
                        <div class="field-group full-width">
                            <label class="field-label">5. عنوان أطروحة الدكتوراه :</label>
                            <input type="text" name="thesis_title" class="field-control fw-bold" value="{{ $phdEd->thesis_title == 'شهادة الدكتوراه' ? '' : $phdEd->thesis_title }}" placeholder="أدخل عنوان الأطروحة">
                        </div>
                    </div>

                    <!-- Attached Files Strip -->
                    <div class="section-attachments-strip">
                        <div class="strip-title"><i class="fa-solid fa-paperclip"></i> الوثائق والمرفقات المرفوعة الخاصة بالدكتوراه :</div>
                        <div class="attachment-badges-flow">
                            @foreach($phdEd->attachments as $att)
                                <a href="{{ route('admin.attachments.view', ['id' => $att->id, 'filename' => 'preview.pdf']) }}" target="_blank" rel="noopener noreferrer" class="doc-badge-pill">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $att->notes ?: (optional($att->attachmentType)->name ?? 'وثيقة الدكتوراه') }}
                                </a>
                            @endforeach
                        </div>
                        <div class="upload-field-row">
                            <span style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);"><i class="fa-solid fa-cloud-arrow-up"></i> رفع / استبدال مرفق جديد للدكتوراه :</span>
                            <input type="file" name="new_attachment" accept=".pdf,image/*" data-max-mb="5" style="font-size: 0.82rem;">
                        </div>
                    </div>
                </div>
            </form>
        </section>
        @endif

        <!-- SECTION 3: MASTER'S DEGREE -->
        @if($masterEd)
        <section class="form-section-card" id="section-3">
            <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="education_id" value="{{ $masterEd->id }}">
                <input type="hidden" name="education_level_id" value="{{ $masterEd->education_level_id }}">

                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-book-bookmark" style="color: #0284c7;"></i>
                        {{ $isDocApp ? 'مؤهل سابق / داعم (درجة الماجستير)' : '3. المؤهل العلمي والدرجة المعالجة (درجة الماجستير)' }}
                        <span style="font-size: 0.74rem; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                            {{ $isDocApp ? 'مؤهل سابق' : 'المؤهل الحالي المطلوب' }}
                        </span>
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ تعديلات الماجستير
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">1. الجامعة المانحة للماجستير :</label>
                            @if($isDocApp)
                                <input type="text" 
                                       name="university_name" 
                                       class="field-control fw-bold" 
                                       value="{{ old('university_name', optional($masterEd->university)->name ?: ($masterEd->university_other ?: ($masterEd->university_text ?: ''))) }}" 
                                       placeholder="أدخل اسم الجامعة">
                            @else
                                <select name="university_id" class="field-control fw-bold">
                                    <option value="">-- اختر الجامعة المانحة --</option>
                                    @foreach($universities as $u)
                                        <option value="{{ $u->id }}" {{ $masterEd->university_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="field-group">
                            <label class="field-label">2. الكلية :</label>
                            <input type="text" name="faculty" class="field-control" value="{{ $masterEd->faculty ?: $masterEd->general_specialization }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">3. القسم :</label>
                            <input type="text" name="department" class="field-control" value="{{ $masterEd->department ?: $masterEd->exact_specialization }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">4. الاختصاص العام :</label>
                            <input type="text" name="general_specialization" class="field-control" value="{{ $masterEd->general_specialization }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">5. الاختصاص الدقيق (اختياري) :</label>
                            <input type="text" name="exact_specialization" class="field-control" value="{{ $masterEd->exact_specialization ?: $masterEd->section_name }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">6. اسم الأستاذ المشرف :</label>
                            <input type="text" name="supervisor_name" class="field-control" value="{{ $masterEd->supervisor_name ?: $masterEd->supervisor }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">7. التقدير العام :</label>
                            <select name="rank" class="field-control">
                                <option value="">-- اختر التقدير --</option>
                                @foreach(['ممتاز', 'جيد جداً', 'جيد', 'مقبول'] as $r)
                                    <option value="{{ $r }}" {{ ($masterEd->rank == $r || str_replace(' ', '', $masterEd->rank) == str_replace(' ', '', $r)) ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">8. تاريخ التسجيل بالدرجة :</label>
                            <input type="date" name="registration_date" class="field-control" value="{{ $masterEd->registration_date }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">9. تاريخ المناقشة :</label>
                            <input type="date" name="defense_date" class="field-control" value="{{ $masterEd->defense_date }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">10. تاريخ منح الدرجة :</label>
                            <input type="date" name="grant_date" class="field-control" value="{{ $masterEd->grant_date }}">
                        </div>
                        <div class="field-group full-width">
                            <label class="field-label">11. عنوان رسالة الماجستير (الأطروحة) بالتفصيل :</label>
                            <textarea name="thesis_title" class="field-control fw-bold" rows="2">{{ $masterEd->thesis_title }}</textarea>
                        </div>
                    </div>

                    <!-- Attached Files Strip -->
                    <div class="section-attachments-strip">
                        <div class="strip-title"><i class="fa-solid fa-paperclip"></i> الوثائق والمرفقات المرفوعة الخاصة بالماجستير :</div>
                        <div class="attachment-badges-flow">
                            @foreach($masterEd->attachments as $att)
                                <a href="{{ route('admin.attachments.view', ['id' => $att->id, 'filename' => 'preview.pdf']) }}" target="_blank" rel="noopener noreferrer" class="doc-badge-pill">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $att->notes ?: (optional($att->attachmentType)->name ?? 'وثيقة الماجستير') }}
                                </a>
                            @endforeach
                        </div>
                        <div class="upload-field-row">
                            <span style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);"><i class="fa-solid fa-cloud-arrow-up"></i> رفع / استبدال مرفق جديد للماجستير :</span>
                            <input type="file" name="new_attachment" accept=".pdf,image/*" data-max-mb="5" style="font-size: 0.82rem;">
                        </div>
                    </div>
                </div>
            </form>
        </section>
        @endif

        {{-- GOVERNMENT FACULTY SECTION (IF APPLICABLE) --}}
        @if($isFacultyPermission || $govEd)
        <section class="form-section-card" id="section-gov">
            <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @if($govEd)
                    <input type="hidden" name="education_id" value="{{ $govEd->id }}">
                    <input type="hidden" name="education_level_id" value="{{ $govEd->education_level_id }}">
                @else
                    <input type="hidden" name="education_level_id" value="3">
                    <input type="hidden" name="thesis_title" value="عضو هيئة تدريسية في جامعة حكومية">
                @endif

                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-building-columns" style="color: #0D9488;"></i>
                        بيانات الجامعة الحكومية السورية والصفة الوظيفية
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ تعديلات التعيين الحكومي
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">الجامعة الحكومية السورية التابع لها :</label>
                            <input type="text" name="university_name" class="field-control fw-bold" value="{{ optional($govEd)->university->name ?? (optional($govEd)->university_other ?? '') }}" placeholder="مثال: جامعة دمشق، جامعة حلب...">
                        </div>
                        <div class="field-group">
                            <label class="field-label">الرتبة الأكاديمية بالجامعة الحكومية :</label>
                            <select name="rank" class="field-control fw-bold">
                                <option value="مدرس" {{ optional($govEd)->rank == 'مدرس' ? 'selected' : '' }}>مدرس</option>
                                <option value="أستاذ مساعد" {{ optional($govEd)->rank == 'أستاذ مساعد' ? 'selected' : '' }}>أستاذ مساعد</option>
                                <option value="أستاذ" {{ optional($govEd)->rank == 'أستاذ' ? 'selected' : '' }}>أستاذ</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الكلية التابع لها بالجامعة الحكومية :</label>
                            <input type="text" name="faculty" class="field-control" value="{{ optional($govEd)->faculty ?: optional($govEd)->general_specialization }}" placeholder="مثال: كلية الهندسة المدنية">
                        </div>
                        <div class="field-group">
                            <label class="field-label">القسم التابع له بالجامعة الحكومية :</label>
                            <input type="text" name="department" class="field-control" value="{{ optional($govEd)->department ?: optional($govEd)->exact_specialization }}" placeholder="مثال: قسم الإنشاءات">
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($govEd && $govEd->attachments->count() > 0)
                    <div class="section-attachments-strip">
                        <div class="strip-title"><i class="fa-solid fa-paperclip"></i> بيان الوضع والوثائق المرفوعة للتعيين الحكومي:</div>
                        <div class="attachment-badges-flow">
                            @foreach($govEd->attachments as $att)
                                <a href="{{ route('admin.attachments.view', ['id' => $att->id, 'filename' => 'preview.pdf']) }}" target="_blank" rel="noopener noreferrer" class="doc-badge-pill">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $att->notes ?? 'بيان وضع وظيفي' }}
                                </a>
                            @endforeach
                        </div>
                        <div class="upload-field-row">
                            <span style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);"><i class="fa-solid fa-cloud-arrow-up"></i> إضافة وثيقة / بيان وضع وظيفي جديد :</span>
                            <input type="file" name="new_attachment" accept=".pdf,image/*" style="font-size: 0.82rem;">
                        </div>
                    </div>
                    @endif
                </div>
            </form>
        </section>
        @endif

        <!-- SECTION 4: BACHELOR'S DEGREE -->
        @if($bachelorEd)
        <section class="form-section-card" id="section-4">
            <form action="{{ route('admin.applications.update_education', $application->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="education_id" value="{{ $bachelorEd->id }}">
                <input type="hidden" name="education_level_id" value="{{ $bachelorEd->education_level_id }}">

                <div class="section-header">
                    <div class="section-title">
                        <i class="fa-solid fa-graduation-cap" style="color: var(--heritage-gold);"></i>
                        بيانات الشهادة الجامعية الأولى (الإجازة - مؤهل سابق)
                    </div>
                    <button type="submit" class="btn-section-save">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ تعديلات الإجازة
                    </button>
                </div>
                <div class="section-body">
                    <div class="inputs-grid-3">
                        <div class="field-group">
                            <label class="field-label">1. الدولة المانحة للإجازة :</label>
                            <input type="text" 
                                   name="country_name" 
                                   class="field-control fw-bold" 
                                   value="{{ old('country_name', optional($bachelorEd->country)->name ?: ($bachelorEd->country_other ?? 'سوريا')) }}" 
                                   placeholder="أدخل الدولة (مثال: سوريا، مصر...)">
                        </div>
                        <div class="field-group">
                            <label class="field-label">2. الجامعة المانحة للإجازة :</label>
                            <input type="text" 
                                   name="university_name" 
                                   class="field-control fw-bold" 
                                   value="{{ old('university_name', optional($bachelorEd->university)->name ?: ($bachelorEd->university_text ?: ($bachelorEd->university_other ?: ''))) }}" 
                                   placeholder="أدخل الجامعة (مثال: جامعة دمشق، جامعة حلب...)">
                        </div>
                        <div class="field-group">
                            <label class="field-label">3. الكلية :</label>
                            <input type="text" name="faculty" class="field-control" value="{{ $bachelorEd->faculty ?: $bachelorEd->general_specialization }}" placeholder="مثال: كلية الهندسة">
                        </div>
                        <div class="field-group">
                            <label class="field-label">4. القسم :</label>
                            <input type="text" name="department" class="field-control" value="{{ $bachelorEd->department }}" placeholder="مثال: قسم المعلوماتية">
                        </div>
                        <div class="field-group">
                            <label class="field-label">5. اختصاص (اختياري) :</label>
                            <input type="text" name="exact_specialization" class="field-control" value="{{ $bachelorEd->exact_specialization ?: $bachelorEd->section_name }}">
                        </div>
                        <div class="field-group">
                            <label class="field-label">6. التقدير العام :</label>
                            <select name="rank" class="field-control">
                                <option value="">-- اختر التقدير --</option>
                                @foreach(['ممتاز', 'جيد جداً', 'جيد', 'مقبول'] as $r)
                                    <option value="{{ $r }}" {{ ($bachelorEd->rank == $r || str_replace(' ', '', $bachelorEd->rank) == str_replace(' ', '', $r)) ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">7. تاريخ الحصول على الدرجة :</label>
                            <input type="date" name="grant_date" class="field-control" value="{{ $bachelorEd->grant_date }}">
                        </div>
                    </div>

                    <!-- Attached Files Strip -->
                    <div class="section-attachments-strip">
                        <div class="strip-title"><i class="fa-solid fa-paperclip"></i> الوثائق والمرفقات المرفوعة للإجازة :</div>
                        <div class="attachment-badges-flow">
                            @foreach($bachelorEd->attachments as $att)
                                <a href="{{ route('admin.attachments.view', ['id' => $att->id, 'filename' => 'preview.pdf']) }}" target="_blank" rel="noopener noreferrer" class="doc-badge-pill">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $att->notes ?: (optional($att->attachmentType)->name ?? 'مصدقة الإجازة الجامعية الأولى') }}
                                </a>
                            @endforeach
                        </div>
                        <div class="upload-field-row">
                            <span style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);"><i class="fa-solid fa-cloud-arrow-up"></i> رفع / استبدال مرفق جديد للإجازة :</span>
                            <input type="file" name="new_attachment" accept=".pdf,image/*" style="font-size: 0.82rem;">
                        </div>
                    </div>
                </div>
            </form>
        </section>
        @endif

        <!-- SECTION 5: ALL ATTACHMENTS REPOSITORY -->
        <section class="form-section-card" id="section-5">
            <div class="section-header">
                <div class="section-title">
                    <i class="fa-solid fa-boxes-stacked" style="color: var(--heritage-gold);"></i>
                    5. المستودع الشامل لجميع المرفقات والوثائق المودعة بالمعاملة
                </div>
                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: bold;">
                    إجمالي: {{ $allAtts->count() }} وثائق رسمية
                </span>
            </div>
            <div class="section-body">
                @if($allAtts->count() > 0)
                <div class="all-docs-grid">
                    @foreach($allAtts as $attObj)
                    <div class="doc-card-item">
                        <div class="doc-card-info">
                            <div class="pdf-icon-sq"><i class="fa-solid fa-file-pdf"></i></div>
                            <div class="doc-card-texts">
                                <span>{{ $attObj->notes ?: (optional($attObj->attachmentType)->name ?? 'وثيقة رسمية') }}</span>
                                <small>{{ optional($attObj->attachmentType)->name ?? 'ملف PDF معتمد' }}</small>
                            </div>
                        </div>
                        <a href="{{ route('admin.attachments.view', ['id' => $attObj->id, 'filename' => 'preview.pdf']) }}" target="_blank" rel="noopener noreferrer" class="btn-quick-preview">
                            <i class="fa-solid fa-eye"></i> معاينة
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-2 mb-2 d-block" style="color: #94a3b8;"></i>
                    لا توجد مرفقات مسجلة حالياً لهذه المعاملة.
                </div>
                @endif
            </div>
        </section>

    </main>

</div>

<script>
    document.querySelectorAll('.tab-anchor-btn').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            document.querySelectorAll('.tab-anchor-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endsection
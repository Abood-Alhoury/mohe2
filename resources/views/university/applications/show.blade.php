<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضبارة المعاملة رقم {{ $application->application_no ?? '#' . $application->id }} | مجلس التعليم العالي</title>
    <!-- خطوط عربية وأيقونات FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --imperial-navy: #0e1e38;
            --imperial-navy-dark: #081224;
            --imperial-navy-light: #18335d;
            --heritage-gold: #c59b27;
            --heritage-gold-light: #f3d47f;
            --bg-body: #f4f6fa;
            --surface-card: #ffffff;
            --border-color: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --emerald: #059669;
            --emerald-bg: #ecfdf5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        /* Top Government Bar */
        .gov-top-bar {
            background: linear-gradient(135deg, var(--imperial-navy-dark) 0%, var(--imperial-navy) 100%);
            color: white;
            padding: 12px 30px;
            border-bottom: 3.5px solid var(--heritage-gold);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .gov-branding {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .gov-logo-box {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.08);
            border: 1px solid var(--heritage-gold-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--heritage-gold-light);
        }

        .gov-titles h1 {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .gov-titles span {
            font-size: 0.78rem;
            color: #94a3b8;
            display: block;
        }

        .gov-user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.06);
            padding: 6px 14px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Main Container */
        .dossier-container {
            max-width: 1400px;
            width: 95%;
            margin: 24px auto 0;
        }

        /* Executive Dossier Header (Identity Bar) */
        .dossier-hero {
            background: white;
            border-radius: 12px;
            padding: 22px 28px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            border-right: 5px solid var(--heritage-gold);
            flex-wrap: wrap;
            gap: 18px;
        }

        .dossier-hero-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .candidate-avatar {
            width: 65px;
            height: 65px;
            border-radius: 12px;
            background: #e0f2fe;
            color: #0369a1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            border: 2px solid #bae6fd;
        }

        .candidate-meta h2 {
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--imperial-navy);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dossier-app-no {
            background: #f1f5f9;
            color: #475569;
            font-family: monospace;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
        }

        .candidate-sub-tags {
            display: flex;
            gap: 12px;
            margin-top: 6px;
            font-size: 0.85rem;
            color: var(--text-muted);
            align-items: center;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hero {
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            border: 1px solid var(--border-color);
            background: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s;
        }

        .btn-hero:hover {
            background: #f8fafc;
            border-color: var(--imperial-navy);
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--heritage-gold) 0%, #a88117 100%);
            color: #0b1526;
            font-weight: 800;
            border: none;
            box-shadow: 0 4px 12px rgba(197, 155, 39, 0.25);
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(197, 155, 39, 0.35);
            background: linear-gradient(135deg, #d4a933 0%, var(--heritage-gold) 100%);
            color: #0b1526;
        }

        /* ISSUED BANNER (HERO DECISION CARD) */
        .decision-success-box {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: white;
            border-radius: 12px;
            padding: 20px 26px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 24px rgba(6, 78, 59, 0.18);
            border: 1.5px solid #10b981;
            flex-wrap: wrap;
            gap: 15px;
        }

        .decision-success-info {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .decision-seal-icon {
            width: 52px;
            height: 52px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: var(--heritage-gold-light);
        }

        .decision-success-titles h3 {
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .decision-pills {
            display: flex;
            gap: 12px;
            font-size: 0.85rem;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .decision-badge-item {
            background: rgba(255,255,255,0.15);
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
        }

        /* 2-Columns Grid: Candidate Details + Application Meta */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 900px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        .card-panel {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 3px 12px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        .card-panel-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-panel-header h3 {
            font-size: 0.96rem;
            font-weight: 800;
            color: var(--imperial-navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-panel-body {
            padding: 20px;
        }

        .info-matrix {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 20px;
        }

        .info-cell span {
            display: block;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-cell strong {
            font-size: 0.92rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        /* ACADEMIC TIMELINE (360 JOURNEY) */
        .timeline-section {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.02);
        }

        .section-headline {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--imperial-navy);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .timeline-stepper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            position: relative;
        }

        @media (max-width: 1024px) {
            .timeline-stepper {
                grid-template-columns: 1fr;
            }
        }

        .degree-card {
            background: #f8fafc;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            position: relative;
            transition: all 0.2s;
        }

        .degree-card:hover {
            border-color: var(--heritage-gold);
            background: #fff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        .degree-card.active-phd {
            border-color: #8b5cf6;
            background: #faf7ff;
            border-top: 4px solid #8b5cf6;
        }

        .degree-tag {
            font-size: 0.75rem;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .degree-title {
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--imperial-navy);
            margin-bottom: 8px;
        }

        .degree-meta-list {
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.7;
        }

        .degree-meta-list div {
            display: flex;
            justify-content: space-between;
        }

        .degree-meta-list strong {
            color: var(--text-dark);
        }

        /* ATTACHMENTS GRID */
        .attachments-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.02);
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 14px;
        }

        .file-item-card {
            background: #fcfdfe;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .file-item-card:hover {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);
        }

        .file-item-info {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .pdf-symbol {
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

        .file-names-box {
            overflow: hidden;
        }

        .file-name-txt {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--imperial-navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .file-date-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .file-actions {
            display: flex;
            gap: 6px;
        }

        .btn-file-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: white;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.15s;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-file-icon:hover {
            background: var(--imperial-navy);
            color: white;
            border-color: var(--imperial-navy);
        }

        .btn-file-icon.preview:hover {
            background: #0284c7;
            border-color: #0284c7;
        }
    </style>
</head>
<body>

    @php
        // 1. Determine all attachments
        $allAttachments = collect();
        if ($application->educations) {
            foreach($application->educations as $ed) {
                if ($ed->attachments) {
                    foreach($ed->attachments as $att) {
                        if (!$allAttachments->contains('id', $att->id)) {
                            $allAttachments->push($att);
                        }
                    }
                }
            }
        }
        if ($application->parentApplication && $application->parentApplication->educations) {
            foreach($application->parentApplication->educations as $pEd) {
                if ($pEd->attachments) {
                    foreach($pEd->attachments as $att) {
                        if (!$allAttachments->contains('id', $att->id)) {
                            $allAttachments->push($att);
                        }
                    }
                }
            }
        }

        // 2. Decision Record
        $decision = $application->latestDecision ?? ($application->decisions ? $application->decisions->sortByDesc('id')->first() : null);

        // 3. Candidate First Initial
        $initial = mb_substr(trim($candidate->full_name ?? 'م'), 0, 1, 'UTF-8');
        if (in_array($initial, ['ا', 'أ', 'إ', 'آ'])) {
            $initial = 'أ';
        }
    @endphp

    <!-- TOP HEADER -->
    <header class="gov-top-bar">
        <div class="gov-branding">
            <div class="gov-logo-box">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="gov-titles">
                <h1>الجمهورية العربية السورية | مجلس التعليم العالي والتعادل الأكاديمي</h1>
                <span>بوابة الجامعات الخاصة | نظام استعراض وتدقيق المعاملات الرسمية</span>
            </div>
        </div>
        <div class="gov-user-pill">
            <i class="fa-solid fa-building-columns" style="color: var(--heritage-gold-light);"></i>
            <span style="font-weight: 800; font-size: 0.88rem;">{{ $application->workUniversity->name ?? (Auth::user()->university->name ?? 'الجامعة الخاصة') }}</span>
        </div>
    </header>

    <div class="dossier-container">

        <!-- 1. HERO IDENTITY CARD -->
        <section class="dossier-hero">
            <div class="dossier-hero-left">
                <div class="candidate-avatar">{{ $initial }}</div>
                <div class="candidate-meta">
                    <h2>
                        {{ $candidate->full_name ?? 'غير محدد' }}
                        <span class="dossier-app-no">{{ $application->application_no ?? ('#' . $application->id) }}</span>
                    </h2>
                    <div class="candidate-sub-tags">
                        <span><i class="fa-solid fa-id-card"></i> {{ $candidate->national_id ?? $candidate->id_number ?? '—' }}</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-building-columns"></i> {{ $application->workUniversity->name ?? 'غير محدد' }}</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-landmark"></i> {{ $application->work_faculty }} ({{ $application->work_department ?? '—' }})</span>
                    </div>
                </div>
            </div>

            <div class="hero-actions">
                <a href="{{ route('university.dashboard') }}" class="btn-hero">
                    <i class="fa-solid fa-arrow-right"></i> رجوع للطلبات
                </a>
                <button onclick="window.print()" class="btn-hero">
                    <i class="fa-solid fa-print"></i> طباعة الإضبارة
                </button>
                <a href="#attachments-anchor" class="btn-hero btn-hero-primary">
                    <i class="fa-solid fa-box-archive"></i> المرفقات ({{ $allAttachments->count() }})
                </a>
            </div>
        </section>

        <!-- 2. HERO ISSUED DECISION BANNER (HIGHLIGHT) -->
        @if($decision)
        <section class="decision-success-box">
            <div class="decision-success-info">
                <div class="decision-seal-icon">
                    <i class="fa-solid fa-stamp"></i>
                </div>
                <div class="decision-success-titles">
                    <h3>تم صدور واعتماد القرار الرسمي الموحد</h3>
                    <div class="decision-pills">
                        @if($decision->decision_no)
                            <span class="decision-badge-item"><i class="fa-solid fa-file-contract"></i> قرار المعادلة رقم: <strong>{{ $decision->decision_no }}</strong></span>
                        @endif
                        @if($decision->decision_date)
                            <span class="decision-badge-item"><i class="fa-regular fa-calendar-check"></i> تاريخ القرار: <strong>{{ \Carbon\Carbon::parse($decision->decision_date)->format('d/m/Y') }}</strong></span>
                        @endif
                        @if($decision->eligibility_decision_no)
                            <span class="decision-badge-item"><i class="fa-solid fa-certificate"></i> قرار الأهلية رقم: <strong>{{ $decision->eligibility_decision_no }}</strong></span>
                        @endif
                        @if($decision->eligibility_decision_date)
                            <span class="decision-badge-item"><i class="fa-regular fa-calendar"></i> تاريخ الأهلية: <strong>{{ \Carbon\Carbon::parse($decision->eligibility_decision_date)->format('d/m/Y') }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- UNIFIED DECISION PDF BUTTON -->
            <a href="{{ route('university.applications.view_decision_pdf', $application->id) }}" target="_blank" class="btn-hero btn-hero-primary" style="background: white; color: #064e3b; font-size: 0.95rem; border: none; padding: 12px 20px;">
                <i class="fa-solid fa-file-pdf" style="color: var(--heritage-gold);"></i> 
                معاينة القرار الرسمي الصادر (PDF)
            </a>
        </section>
        @endif

        <!-- 3. PROFILE & APPLICATION META MATRIX -->
        <div class="profile-grid">
            <!-- Personal Information Panel -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><i class="fa-solid fa-user-tie" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية للمرشح</h3>
                    <span class="badge" style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                        {{ optional($candidate->nationality)->name ?? ($candidate->is_syrian ? 'سوري الجنسية' : 'غير سوري') }}
                    </span>
                </div>
                <div class="card-panel-body">
                    <div class="info-matrix">
                        <div class="info-cell">
                            <span>الاسم والكنية الكاملة :</span>
                            <strong>{{ $candidate->full_name ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>اسم الأب والأم :</span>
                            <strong>{{ $candidate->father_name ?? '—' }} / {{ $candidate->mother_name ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>الرقم الوطني :</span>
                            <strong style="font-family: monospace; letter-spacing: 0.5px;">{{ $candidate->national_id ?? $candidate->id_number ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>تاريخ الميلاد والوظيفة :</span>
                            <strong>{{ $candidate->dob ? \Carbon\Carbon::parse($candidate->dob)->format('d/m/Y') : '—' }} | {{ $candidate->job_title ?? 'لا يعمل' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>رقم الهاتف المحمول :</span>
                            <strong dir="ltr">{{ $candidate->mobile ?? $candidate->phone ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>البريد الإلكتروني الجامعي :</span>
                            <strong style="font-size: 0.85rem;">{{ $candidate->email ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Status Panel -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><i class="fa-solid fa-file-invoice" style="color: var(--heritage-gold);"></i> 2. حالة وتفاصيل المعاملة</h3>
                    @if($application->status === 'تم الصدور')
                        <span class="badge" style="background: #ecfdf5; color: #059669; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-size: 0.78rem;">
                            <i class="fa-solid fa-circle-check"></i> مكتملة (تم الصدور)
                        </span>
                    @elseif($application->status === 'تحت التدقيق الأولي')
                        <span class="badge" style="background: #eff6ff; color: #1d4ed8; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-size: 0.78rem;">
                            <i class="fa-solid fa-magnifying-glass"></i> تحت التدقيق الأولي
                        </span>
                    @elseif($application->status === 'مرفوض')
                        <span class="badge" style="background: #fef2f2; color: #dc2626; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-size: 0.78rem;">
                            <i class="fa-solid fa-circle-xmark"></i> مرفوض
                        </span>
                    @else
                        <span class="badge" style="background: #fffbeb; color: #d97706; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-size: 0.78rem;">
                            {{ $application->status }}
                        </span>
                    @endif
                </div>
                <div class="card-panel-body">
                    <div class="info-matrix">
                        <div class="info-cell">
                            <span>نوع المسار الأكاديمي :</span>
                            <strong style="color: #7c3aed;"><i class="fa-solid fa-graduation-cap"></i> {{ $application->request_type }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>تاريخ تقديم الطلب :</span>
                            <strong>{{ $application->created_at ? $application->created_at->format('d/m/Y - h:i A') : '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>الجامعة المرفوعة منها :</span>
                            <strong>{{ $application->workUniversity->name ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>الكلية والقسم المستهدف :</span>
                            <strong>{{ $application->work_faculty }} - {{ $application->work_department ?? '—' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>نظام الدراسة والخبرة :</span>
                            <strong>{{ $application->study_system ?? 'نظام فصلي' }}</strong>
                        </div>
                        <div class="info-cell">
                            <span>الرقم المرجعي بالوزارة :</span>
                            <strong style="font-family: monospace;">{{ $application->application_no ?? ('MOHE-' . $application->id) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. ACADEMIC JOURNEY TIMELINE (THE 360 DEGREE STEPPER) -->
        <section class="timeline-section">
            <div class="section-headline">
                <span><i class="fa-solid fa-timeline" style="color: var(--heritage-gold);"></i> 3. السجل الأكاديمي والشهادات المسجلة (التسلسل العلمي)</span>
                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">مسار موثق أصولاً من الثانوية حتى أعلى درجة</span>
            </div>

            @php
                $govEd = $application->educations->first(function($e) {
                    return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
                });
            @endphp
            <div class="timeline-stepper">
                @if($govEd)
                <!-- Gov Faculty Employment -->
                <div class="degree-card">
                    <div class="degree-tag">
                        <span>التعيين بالجامعة الحكومية</span>
                        <i class="fa-solid fa-building-columns text-muted"></i>
                    </div>
                    <div class="degree-title">
                        {{ $govEd->rank ?? 'عضو هيئة تدريسية' }} - {{ $govEd->university?->name ?? ($govEd->university_other ?? 'جامعة حكومية') }}
                    </div>
                    <div class="degree-meta-list">
                        <div><span>الكلية:</span> <strong>{{ $govEd->faculty ?: ($govEd->general_specialization ?: '—') }}</strong></div>
                        <div><span>القسم:</span> <strong>{{ $govEd->department ?: ($govEd->exact_specialization ?: '—') }}</strong></div>
                    </div>
                </div>
                @endif

                @if($highSchoolEd)
                <!-- High School -->
                <div class="degree-card">
                    <div class="degree-tag">
                        <span>1. الشهادة الثانوية</span>
                        <i class="fa-solid fa-school text-muted"></i>
                    </div>
                    <div class="degree-title">
                        الثانوية العامة ({{ $highSchoolEd->general_specialization ?? 'الفرع العلمي' }})
                    </div>
                    <div class="degree-meta-list">
                        <div><span>بلد المنح:</span> <strong>{{ $highSchoolEd?->country?->name ?? 'الجمهورية العربية السورية' }}</strong></div>
                        <div><span>سنة الحصول:</span> <strong>{{ $highSchoolEd?->graduation_date ? \Carbon\Carbon::parse($highSchoolEd->graduation_date)->format('Y') : ($highSchoolEd?->graduation_year ?? '—') }}</strong></div>
                    </div>
                </div>
                @endif

                @if($bachelorEd)
                <!-- Bachelor Degree -->
                <div class="degree-card">
                    <div class="degree-tag">
                        <span>2. الإجازة الجامعية</span>
                        <i class="fa-solid fa-building-columns text-muted"></i>
                    </div>
                    <div class="degree-title">
                        {{ $bachelorEd?->general_specialization ? 'إجازة في ' . $bachelorEd->general_specialization : ($bachelorEd?->faculty ? 'إجازة في ' . $bachelorEd->faculty : 'إجازة جامعية') }}
                    </div>
                    <div class="degree-meta-list">
                        <div><span>الجامعة المانحة:</span> <strong>{{ $bachelorEd?->university?->name ?? $bachelorEd?->university_other ?? ($bachelorEd?->faculty ?? '—') }}</strong></div>
                        <div><span>التقدير العام:</span> <strong style="color: var(--emerald);">{{ $bachelorEd?->rank ?? 'جيد جداً' }}</strong></div>
                        <div><span>تاريخ المنح:</span> <strong>{{ $bachelorEd?->graduation_date ? \Carbon\Carbon::parse($bachelorEd->graduation_date)->format('d/m/Y') : ($bachelorEd?->grant_date ? \Carbon\Carbon::parse($bachelorEd->grant_date)->format('d/m/Y') : '—') }}</strong></div>
                    </div>
                </div>
                @endif

                <!-- Master Degree -->
                @if($masterEd)
                <div class="degree-card {{ !$phdEd ? 'active-phd' : '' }}">
                    <div class="degree-tag" {!! !$phdEd ? 'style="color: #7c3aed;"' : '' !!}>
                        <span>3. درجة الماجستير {{ !$phdEd ? '(محل التكليف)' : '' }}</span>
                        <i class="fa-solid fa-book-open text-muted"></i>
                    </div>
                    <div class="degree-title" {!! !$phdEd ? 'style="color: #6d28d9;"' : '' !!}>
                        {{ $masterEd->exact_specialization ?? $masterEd->general_specialization ?? 'ماجستير' }}
                    </div>
                    <div class="degree-meta-list">
                        <div><span>الجامعة والكلية:</span> <strong>{{ $masterEd->university?->name ?? ($masterEd->university_other ?? '') }} {{ $masterEd->faculty ? '- ' . $masterEd->faculty : '' }}</strong></div>
                        <div><span>المشرف العلمي:</span> <strong>{{ $masterEd->supervisor_name ?? $masterEd->supervisor ?? '—' }}</strong></div>
                        <div><span>التقدير:</span> <strong style="color: var(--emerald);">{{ $masterEd->rank ?? 'جيد جداً' }}</strong></div>
                        <div><span>تاريخ المنح:</span> <strong>{{ $masterEd->graduation_date ? \Carbon\Carbon::parse($masterEd->graduation_date)->format('d/m/Y') : ($masterEd->grant_date ? \Carbon\Carbon::parse($masterEd->grant_date)->format('d/m/Y') : '—') }}</strong></div>
                    </div>
                </div>
                @endif

                <!-- PhD Degree (Active Goal) -->
                @if($phdEd)
                <div class="degree-card active-phd">
                    <div class="degree-tag" style="color: #7c3aed;">
                        <span>4. درجة الدكتوراه (محل التكليف)</span>
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div class="degree-title" style="color: #6d28d9;">
                        {{ $phdEd->exact_specialization ?? $phdEd->general_specialization ?? 'دكتوراه الفلسفة' }}
                    </div>
                    <div class="degree-meta-list">
                        <div><span>الجامعة والكلية:</span> <strong>{{ $phdEd->university?->name ?? ($phdEd->university_other ?? '') }} {{ $phdEd->faculty ? '- ' . $phdEd->faculty : '' }}</strong></div>
                        <div><span>التقدير الوزاري:</span> <strong style="color: #7c3aed;">{{ $phdEd->rank ?? 'شرف / امتياز' }}</strong></div>
                        <div><span>تاريخ منح الدرجة:</span> <strong>{{ $phdEd->graduation_date ? \Carbon\Carbon::parse($phdEd->graduation_date)->format('d/m/Y') : ($phdEd->grant_date ? \Carbon\Carbon::parse($phdEd->grant_date)->format('d/m/Y') : '—') }}</strong></div>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <!-- 4. TEACHING EXPERIENCES SECTION -->
        @if($application->experiences && $application->experiences->isNotEmpty())
        @php
            $expDays = $application->total_experience_days;
            $expYears = floor($expDays / 365);
            $expMonths = floor(($expDays % 365) / 30);
            $expRemDays = ($expDays % 365) % 30;
            $isTheoretical = $application->has_two_years_experience;
        @endphp
        <section class="timeline-section" style="margin-bottom: 24px;">
            <div class="section-headline">
                <span><i class="fa-solid fa-chalkboard-user" style="color: var(--heritage-gold);"></i> 4. بيان الخبرات التدريسية ومسار التقييم ({{ $application->experiences->count() }} فترات)</span>
                <div>
                    @if($isTheoretical)
                        <span class="badge px-3 py-2 fs-6 shadow-sm border border-success" style="background-color: #15803d; color: #ffffff;">
                            <i class="fa-solid fa-circle-check me-1 text-warning"></i> ماجستير نظري (مستوفي شرط السنتين ≥ 730 يوماً)
                        </span>
                    @else
                        <span class="badge px-3 py-2 fs-6 shadow-sm border border-primary" style="background-color: #1e3a5f; color: #ffffff;">
                            <i class="fa-solid fa-chalkboard me-1 text-info"></i> ماجستير تطبيقي (هيئة فنية - خبرة أقل من سنتين)
                        </span>
                    @endif
                </div>
            </div>

            <!-- KPI Summary Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 border rounded text-center shadow-xs bg-light">
                        <span class="text-muted d-block fs-8 mb-1">السنوات</span>
                        <span class="fs-4 fw-bold text-dark">{{ $expYears }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded text-center shadow-xs bg-light">
                        <span class="text-muted d-block fs-8 mb-1">الشهور</span>
                        <span class="fs-4 fw-bold text-dark">{{ $expMonths }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded text-center shadow-xs bg-light">
                        <span class="text-muted d-block fs-8 mb-1">الأيام المتبقية</span>
                        <span class="fs-4 fw-bold text-dark">{{ $expRemDays }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded text-center shadow-xs" style="background-color: {{ $isTheoretical ? '#f0fdf4' : '#eff6ff' }}; border-color: {{ $isTheoretical ? '#86efac' : '#bfdbfe' }} !important;">
                        <span class="text-muted d-block fs-8 mb-1">إجمالي الأيام</span>
                        <span class="fs-4 fw-bold {{ $isTheoretical ? 'text-success' : 'text-primary' }}">{{ $expDays }}</span> <span class="fs-8">يوماً</span>
                    </div>
                </div>
            </div>

            <!-- Experience Periods Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
                    <thead style="background: #f8fafc; color: var(--imperial-navy); font-weight: 800; font-size: 0.85rem;">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>الجامعة / الكلية / الجهة التعليمية</th>
                            <th>الصفة أو المقرر التدريسي</th>
                            <th class="text-center">تاريخ البدء</th>
                            <th class="text-center">تاريخ الانتهاء</th>
                            <th class="text-center">المدة المحسوبة</th>
                            <th class="text-center">الأيام</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.88rem;">
                        @foreach($application->experiences as $idx => $exp)
                        <tr>
                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <strong class="text-dark">{{ $exp->institution_name }}</strong>
                            </td>
                            <td>{{ $exp->role_or_subject ?: '---' }}</td>
                            <td class="text-center"><strong class="text-dark">{{ \Carbon\Carbon::parse($exp->start_date)->format('d/m/Y') }}</strong></td>
                            <td class="text-center"><strong class="text-dark">{{ \Carbon\Carbon::parse($exp->end_date)->format('d/m/Y') }}</strong></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                    {{ $exp->formatted_duration }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $exp->duration_days }} يوماً</span>
                            </td>
                            <td class="text-muted">{{ $exp->notes ?: '---' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($isTheoretical)
                <div class="alert alert-success mt-3 mb-0 d-flex align-items-center gap-2 border-success" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                    <div>
                        <strong>تأكيد مسار الماجستير النظري:</strong> مجموع الخبرة التدريسية يعادل سنتين على الأقل ({{ $expDays }} يوماً ≥ 730 يوماً). تم استيفاء شرط الأهلية التدريسية ويتوجب إجراء المقابلة وتقديم وثائق الخبرة المعتمدة.
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-3 mb-0 d-flex align-items-center gap-2 border-primary" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-info-circle fs-5 text-primary"></i>
                    <div>
                        <strong>تأكيد مسار الماجستير التطبيقي (هيئة فنية):</strong> مجموع فترات الخبرة أقل من سنتين ({{ $expDays }} يوماً &lt; 730 يوماً). يتم اعتماد المرشح كعضو هيئة فنية تدريسية دون الحاجة إلى إجراء المقابلة الشفهية.
                    </div>
                </div>
            @endif
        </section>
        @endif

        <!-- 5. UPLOADED OFFICIAL ATTACHMENTS GRID -->
        <section class="attachments-card" id="attachments-anchor">
            <div class="section-headline">
                <span><i class="fa-solid fa-folder-closed" style="color: var(--heritage-gold);"></i> {{ ($application->experiences && $application->experiences->isNotEmpty()) ? '5' : '4' }}. الوثائق والمرفقات الرسمية المودعة مع المعاملة ({{ $allAttachments->count() }} مستنداً)</span>
                <span style="font-size: 0.8rem; color: #0284c7; font-weight: bold;"><i class="fa-solid fa-shield-check"></i> جميع المرفقات مدققة ومطابقة للأصل</span>
            </div>

            <div class="files-grid">
                @forelse($allAttachments as $att)
                    @php
                        $isPdf = str_ends_with(strtolower($att->file_path), '.pdf');
                        $fullPath = storage_path('app/public/' . $att->file_path);
                        $fileSize = 'مستند رقمي';
                        if (file_exists($fullPath)) {
                            $bytes = filesize($fullPath);
                            if ($bytes >= 1048576) {
                                $fileSize = number_format($bytes / 1048576, 1) . ' MB';
                            } elseif ($bytes >= 1024) {
                                $fileSize = number_format($bytes / 1024, 0) . ' KB';
                            } else {
                                $fileSize = $bytes . ' B';
                            }
                        }
                        $fileName = $att->notes ?: ($att->attachmentType->name ?? 'وثيقة رسمية');
                        $fileDate = $att->created_at ? $att->created_at->format('d/m/Y') : date('d/m/Y');
                    @endphp
                    <div class="file-item-card">
                        <div class="file-item-info">
                            <div class="pdf-symbol" {!! !$isPdf ? 'style="background: #e0f2fe; color: #0284c7;"' : '' !!}>
                                <i class="fa-solid {{ $isPdf ? 'fa-file-pdf' : 'fa-file-image' }}"></i>
                            </div>
                            <div class="file-names-box">
                                <span class="file-name-txt" title="{{ $fileName }}">{{ $fileName }}</span>
                                <span class="file-date-sub">{{ $fileDate }} • {{ $fileSize }}</span>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn-file-icon preview" title="معاينة بالمتصفح">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ asset('storage/' . $att->file_path) }}" download class="btn-file-icon" title="تحميل">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                        <span>لا توجد مرفقات مودعة مع هذا الطلب.</span>
                    </div>
                @endforelse
            </div>
        </section>

    </div>

</body>
</html>

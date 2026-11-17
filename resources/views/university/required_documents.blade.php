@extends('layouts.university')

@section('title', 'اللائحة التنفيذية للأوراق والثبوتيات المطلوبة للتعادل - مجلس التعليم العالي')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-navy: #091322;
        --secondary-navy: #0e1e38;
        --accent-gold: #c59b27;
        --accent-gold-light: #f5d77f;
        --accent-gold-glow: rgba(197, 155, 39, 0.25);
        --emerald-accent: #10b981;
        --emerald-glow: rgba(16, 185, 129, 0.2);
        --bg-page: #f1f5f9;
        --surface-card: #ffffff;
        --border-subtle: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
    }

    .req-docs-container {
        max-width: 1440px;
        margin: 10px auto 40px;
        font-family: 'Tajawal', sans-serif;
    }

    /* 1. HERO HEADER */
    .docs-hero-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px 28px;
        border: 1.5px solid var(--border-subtle);
        border-right: 6px solid var(--accent-gold);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(14, 30, 56, 0.03);
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .circular-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .docs-hero-card h2 {
        font-size: 1.45rem;
        font-weight: 900;
        color: var(--secondary-navy);
        margin-bottom: 4px;
    }

    .docs-hero-card p {
        font-size: 0.88rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    .hero-actions-side {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-new-app-glow {
        background: linear-gradient(135deg, var(--accent-gold) 0%, #aa841d 100%);
        color: #091322 !important;
        font-weight: 800;
        font-size: 0.92rem;
        padding: 11px 22px;
        border-radius: 10px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px var(--accent-gold-glow);
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .btn-new-app-glow:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(197, 155, 39, 0.45);
        color: #091322 !important;
    }

    .btn-print-official {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        color: #334155 !important;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-print-official:hover {
        background: var(--secondary-navy);
        color: #ffffff !important;
        border-color: var(--secondary-navy);
    }

    /* 2. HR NOTICE ALERT */
    .hr-notice-banner {
        background: #fffdf5;
        border: 1.5px solid #fde68a;
        border-right: 5px solid var(--accent-gold);
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 22px;
        box-shadow: 0 2px 10px rgba(197, 155, 39, 0.05);
    }

    .notice-icon {
        font-size: 26px;
        color: #d97706;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .notice-content h4 {
        font-size: 0.95rem;
        font-weight: 800;
        color: #92400e;
        margin-bottom: 4px;
    }

    .notice-content p {
        font-size: 0.86rem;
        color: #78350f;
        line-height: 1.65;
        margin-bottom: 0;
    }

    /* 3. NAVIGATION TABS (PILL BAR) */
    .track-tabs-bar {
        display: flex;
        gap: 8px;
        background: #ffffff;
        padding: 8px;
        border-radius: 14px;
        border: 1.5px solid var(--border-subtle);
        margin-bottom: 24px;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .tab-pill-btn {
        flex: 1;
        background: transparent;
        border: 1.5px solid transparent;
        padding: 11px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.88rem;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.22s ease;
    }

    .tab-pill-btn:hover {
        background: #f8fafc;
        color: var(--secondary-navy);
    }

    .tab-pill-btn.active {
        background: var(--secondary-navy);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(14, 30, 56, 0.2);
    }

    .tab-pill-btn.active i {
        color: var(--accent-gold-light);
    }

    /* 4. TAB CONTENTS */
    .req-tab-content {
        display: none;
    }

    .req-tab-content.active {
        display: block;
        animation: fadeInTab 0.25s ease-in-out;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Section Slab */
    .requirement-slab-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1.5px solid var(--border-subtle);
        padding: 24px 28px;
        box-shadow: 0 4px 20px rgba(14, 30, 56, 0.03);
        margin-bottom: 25px;
    }

    .slab-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding-bottom: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .slab-title-group h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--secondary-navy);
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .slab-title-group p {
        font-size: 0.84rem;
        color: var(--text-muted);
        margin-bottom: 0;
    }

    /* Fee Announcement Card */
    .fee-announcement-card {
        background: #ecfdf5;
        border: 1.5px solid #a7f3d0;
        color: #065f46;
        padding: 9px 18px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        font-size: 0.88rem;
    }

    .fee-announcement-card strong {
        font-family: 'Plus Jakarta Sans', 'Tajawal', sans-serif;
        font-size: 1.05rem;
    }

    /* Document Item Cards */
    .document-item-card {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.22s ease;
    }

    .document-item-card:hover {
        border-color: var(--accent-gold);
        background: #fffdfa;
        transform: translateX(-4px);
        box-shadow: 0 4px 14px rgba(197, 155, 39, 0.08);
    }

    .doc-left-cluster {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }

    .doc-squircle-num {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--secondary-navy);
        color: var(--accent-gold-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.92rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(14, 30, 56, 0.15);
    }

    .doc-content-text {
        font-size: 0.92rem;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.6;
    }

    .doc-content-text strong {
        color: var(--secondary-navy);
    }

    .doc-right-cluster {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .doc-type-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .doc-type-badge.cert {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .doc-type-badge.original {
        background: #fefce8;
        color: #a16207;
        border-color: #fef08a;
    }

    .doc-type-badge.electronic {
        background: #f5f3ff;
        color: #7c3aed;
        border-color: #ddd6fe;
    }

    .doc-type-badge.financial {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    .doc-check-icon {
        color: #10b981;
        font-size: 1.05rem;
    }

    /* Split Bento Layout for Foreign PhD */
    .foreign-phd-split-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 22px;
    }

    @media (max-width: 991px) {
        .foreign-phd-split-grid {
            grid-template-columns: 1fr;
        }
    }

    .production-exam-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1.5px solid #a7f3d0;
        border-radius: 14px;
        padding: 22px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .production-exam-card h5 {
        font-size: 1rem;
        font-weight: 800;
        color: #065f46;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .production-exam-card p {
        font-size: 0.85rem;
        color: #047857;
        margin-bottom: 16px;
    }

    .production-spec-box {
        background: #ffffff;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .spec-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #d1fae5;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* 4-Bento Grid for External Certifications */
    .certifications-bento-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
    }

    .cert-rule-bento-card {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.22s ease;
        position: relative;
        overflow: hidden;
    }

    .cert-rule-bento-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(14, 30, 56, 0.07);
        border-color: #cbd5e1;
    }

    .cert-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 14px;
    }

    .cert-rule-bento-card h5 {
        font-size: 1rem;
        font-weight: 800;
        color: var(--secondary-navy);
        margin-bottom: 8px;
    }

    .cert-rule-bento-card p {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.65;
        margin-bottom: 0;
    }

    /* Footer Bottom Strip */
    .docs-footer-strip {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 14px;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    /* Print Rules */
    @media print {
        header, aside, footer, .sidebar-container, .modern-cyber-sidebar,
        .hero-actions-side, .track-tabs-bar, .docs-footer-strip,
        .mohe-header, .executive-footer {
            display: none !important;
        }

        body, main {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .req-docs-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        .docs-hero-card {
            border: 1.5px solid #000 !important;
            box-shadow: none !important;
            page-break-after: avoid;
        }

        .req-tab-content {
            display: block !important;
            page-break-after: always;
        }

        .requirement-slab-card {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .document-item-card {
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
<div class="req-docs-container">





    <!-- 3. INTERACTIVE TRACK TABS (PILL BAR) -->
    <div class="track-tabs-bar" role="tablist">
        <button type="button" class="tab-pill-btn active" onclick="switchRequirementTab('tab-master-sy', this)">
            <i class="fa-solid fa-graduation-cap"></i> الماجستير (جامعة سورية)
        </button>
        <button type="button" class="tab-pill-btn" onclick="switchRequirementTab('tab-master-foreign', this)">
            <i class="fa-solid fa-globe"></i> الماجستير (جامعة غير سورية)
        </button>
        <button type="button" class="tab-pill-btn" onclick="switchRequirementTab('tab-phd-sy', this)">
            <i class="fa-solid fa-award"></i> الدكتوراه (جامعة سورية)
        </button>
        <button type="button" class="tab-pill-btn" onclick="switchRequirementTab('tab-phd-foreign', this)">
            <i class="fa-solid fa-microscope"></i> الدكتوراه (غير سورية) والإنتاج
        </button>
        <button type="button" class="tab-pill-btn" onclick="switchRequirementTab('tab-faculty', this)">
            <i class="fa-solid fa-building-columns"></i> أعضاء الهيئة التدريسية
        </button>
        <button type="button" class="tab-pill-btn" onclick="switchRequirementTab('tab-certifications', this)">
            <i class="fa-solid fa-stamp"></i> تصديقات الوثائق الخارجية
        </button>
    </div>

    <!-- 4. TAB CONTENTS -->

    <!-- TAB 1: M.Sc Syrian University -->
    <div class="req-tab-content active" id="tab-master-sy">
        <div class="requirement-slab-card" style="border-top: 4px solid var(--secondary-navy);">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-graduation-cap" style="color: var(--accent-gold);"></i> الوثائق المطلوبة لتعادل درجة الماجستير (الصادرة عن جامعة سورية)</h3>
                    <p>للمرشحين للتعيين في الهيئة التدريسية بالجامعات الخاصة السورية</p>
                </div>
                <div class="fee-announcement-card">
                    <i class="fa-solid fa-receipt fs-6"></i>
                    <span>رسم التعادل المعتمد:</span>
                    <strong>100,000 ل.س</strong>
                </div>
            </div>

            <div class="checklist-items-wrap">
                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">1</div>
                        <div class="doc-content-text"><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية موجه أصولاً لمجلس التعليم العالي.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">2</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">3</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى:</strong> (شهادة البكالوريوس أو الليسانس).</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">4</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الماجستير.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">5</div>
                        <div class="doc-content-text"><strong>وثيقة مصدقة أصولاً متضمنة التواريخ العلمية:</strong> (تاريخ التسجيل بالدرجة، تاريخ المناقشة، وتاريخ الحصول على الشهادة).</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">6</div>
                        <div class="doc-content-text"><strong>ملخص باللغة العربية عن رسالة الماجستير:</strong> (مقدم إلكترونياً بصيغة PDF).</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge electronic">ملف إلكتروني PDF</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">7</div>
                        <div class="doc-content-text"><strong>شهادة خبرة تدريسية:</strong> لا تقل عن سنتين ما بعد حصوله على الدرجة العلمية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">شهادة خبرة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">8</div>
                        <div class="doc-content-text"><strong>العقود وإيصالات الرواتب مصدقة أصولاً:</strong> تثبت ممارسة الخبرة التدريسية في الجامعة.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">وثائق مالية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">9</div>
                        <div class="doc-content-text"><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية السورية للمعلوماتية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">شهادة كفاءة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">10</div>
                        <div class="doc-content-text"><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته الحالية وعنوانه وبريده ورقم هاتفه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">سيرة ذاتية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">11</div>
                        <div class="doc-content-text"><strong>إيصال تسديد رسم التعادل:</strong> بقيمة 100,000 ل.س لحملة درجة الماجستير.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge financial">إيصال مالي</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('university.apply.syrian_masters') }}" class="btn-new-app-glow">
                    <i class="fa-solid fa-paper-plane"></i> البدء بتقديم طلب تعادل ماجستير سوري
                </a>
            </div>
        </div>
    </div>

    <!-- TAB 2: M.Sc Foreign University -->
    <div class="req-tab-content" id="tab-master-foreign">
        <div class="requirement-slab-card" style="border-top: 4px solid #0284c7;">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-globe" style="color: #0284c7;"></i> الوثائق المطلوبة لتعادل درجة الماجستير (غير السورية - الخارجية)</h3>
                    <p>للمرشحين الحاصلين على شهادات من جامعات عربية أو أجنبية</p>
                </div>
                <div class="fee-announcement-card">
                    <i class="fa-solid fa-receipt fs-6"></i>
                    <span>رسم التعادل المعتمد:</span>
                    <strong>100,000 ل.س</strong>
                </div>
            </div>

            <div class="checklist-items-wrap">
                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">1</div>
                        <div class="doc-content-text"><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">2</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">3</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">4</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الماجستير الخارجية.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">5</div>
                        <div class="doc-content-text"><strong>وثيقة مصدقة أصولاً متضمنة تواريخ الدراسة:</strong> (تاريخ التسجيل بالدرجة، تاريخ المناقشة، وتاريخ الحصول على الشهادة لكل درجتي الماجستير والبكالوريوس).</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">6</div>
                        <div class="doc-content-text"><strong>ملخص باللغة العربية عن رسالة الماجستير إلكترونياً (PDF).</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge electronic">ملف إلكتروني PDF</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">7</div>
                        <div class="doc-content-text"><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">شهادة كفاءة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">8</div>
                        <div class="doc-content-text"><strong>صور عن جوازات السفر:</strong> بالإضافة لإبراز جوازات السفر الأصلية للمطابقة.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">وثائق سفر</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">9</div>
                        <div class="doc-content-text"><strong>إثبات الإقامة أصولاً:</strong> نموذج إثبات إقامة (يُعطى من مكتب التعادل) أو وثيقة حركة الهجرة والجوازات لبيان الإقامة أثناء الدراسة.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">إثبات إقامة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">10</div>
                        <div class="doc-content-text"><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">سيرة ذاتية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">11</div>
                        <div class="doc-content-text"><strong>إيصال تسديد رسم تعادل:</strong> بقيمة 100,000 ل.س.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge financial">إيصال مالي</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">12</div>
                        <div class="doc-content-text"><strong>إبراز الشهادات الأصلية:</strong> لكافة المراحل الدراسية عند المطابقة الميدانية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge original">أصل الشهادات</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2 flex-wrap">
                <a href="{{ route('university.apply.foreign_masters_applied') }}" class="btn-new-app-glow">
                    <i class="fa-solid fa-paper-plane"></i> تقديم طلب ماجستير خارجي (تطبيقي)
                </a>
                <a href="{{ route('university.apply.foreign_masters_theoretical') }}" class="btn-new-app-glow">
                    <i class="fa-solid fa-book-open-reader"></i> تقديم طلب ماجستير خارجي (نظري)
                </a>
            </div>
        </div>
    </div>

    <!-- TAB 3: Ph.D Syrian University -->
    <div class="req-tab-content" id="tab-phd-sy">
        <div class="requirement-slab-card" style="border-top: 4px solid var(--accent-gold);">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-award" style="color: var(--accent-gold);"></i> الوثائق المطلوبة لتعادل درجة الدكتوراه (الصادرة عن جامعة سورية)</h3>
                    <p>وفحص الإنتاج العلمي للمرشحين للتعيين في الجامعات الخاصة السورية</p>
                </div>
                <div class="fee-announcement-card">
                    <i class="fa-solid fa-receipt fs-6"></i>
                    <span>رسم التعادل المعتمد:</span>
                    <strong>125,000 ل.س</strong>
                </div>
            </div>

            <div class="checklist-items-wrap">
                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">1</div>
                        <div class="doc-content-text"><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">2</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">3</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">4</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الماجستير.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">5</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الدكتوراه السورية.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">6</div>
                        <div class="doc-content-text"><strong>وثيقة مصدقة أصولاً متضمنة تواريخ التسجيل والمناقشة:</strong> وتاريخ الحصول على الشهادة لكل درجتي الماجستير والدكتوراه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">7</div>
                        <div class="doc-content-text"><strong>ملخص باللغة العربية عن رسالة الدكتوراه إلكترونياً (PDF).</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge electronic">ملف إلكتروني PDF</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">8</div>
                        <div class="doc-content-text"><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">شهادة كفاءة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">9</div>
                        <div class="doc-content-text"><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">سيرة ذاتية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">10</div>
                        <div class="doc-content-text"><strong>إيصال تسديد رسم تعادل:</strong> بقيمة 125,000 ل.س لحملة درجة الدكتوراه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge financial">إيصال مالي</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('university.apply.syrian_doctorate') }}" class="btn-new-app-glow">
                    <i class="fa-solid fa-paper-plane"></i> البدء بتقديم طلب تعادل دكتوراه سورية
                </a>
            </div>
        </div>
    </div>

    <!-- TAB 4: Ph.D Foreign University & Scientific Production -->
    <div class="req-tab-content" id="tab-phd-foreign">
        <div class="requirement-slab-card" style="border-top: 4px solid #16a34a;">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-microscope" style="color: #16a34a;"></i> الوثائق المطلوبة (للتعادل وفحص الإنتاج العلمي) للدكتوراه غير السورية</h3>
                    <p>للمرشحين الحاصلين على الدكتوراه الخارجية للتعيين بوظيفة مدرس في الجامعات الخاصة</p>
                </div>
                <div class="fee-announcement-card">
                    <i class="fa-solid fa-receipt fs-6"></i>
                    <span>رسم التعادل المعتمد:</span>
                    <strong>125,000 ل.س</strong>
                </div>
            </div>

            <!-- Split Bento Layout -->
            <div class="foreign-phd-split-grid">
                <!-- Right Side: 16 Items -->
                <div>
                    <h5 class="fw-bold mb-3" style="color: var(--secondary-navy); font-size: 1rem;">
                        <i class="fa-solid fa-list-ol text-success me-1"></i> أولاً: الوثائق والمستندات الثبوتية للدكتوراه غير السورية (16 بنداً):
                    </h5>

                    <div class="checklist-items-wrap">
                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">1</div>
                                <div class="doc-content-text">صورة عن الهوية الشخصية أو جواز السفر.</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">ثبوتية شخصية</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">2</div>
                                <div class="doc-content-text">شهادة البكالوريا (الثانوية العامة).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">نسخة مصدقة</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">3</div>
                                <div class="doc-content-text">قرار معادلة درجة البكالوريا من وزارة التربية (في حال كانت غير سورية).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge cert">شرطي لغير السورية</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">4</div>
                                <div class="doc-content-text">الإجازة الجامعية مصدقة أصولاً (دون اشتراط قرار معادلة).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">نسخة مصدقة</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">5</div>
                                <div class="doc-content-text">شهادة الماجستير مصدقة أصولاً (+ قرار معادلة الماجستير في حال تمت معادلتها من قبل).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">نسخة مصدقة</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">6</div>
                                <div class="doc-content-text">شهادة الدكتوراه مصدقة ومترجمة أصولاً.</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">نسخة مصدقة</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">7</div>
                                <div class="doc-content-text">قرارات التسجيل والمناقشة والمنح لدرجة الدكتوراه.</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge cert">وثيقة رسمية</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">8</div>
                                <div class="doc-content-text">السيرة الذاتية للمرشح.</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">سيرة ذاتية</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">9</div>
                                <div class="doc-content-text">إيصال الدفع بقيمة 125,000 ل.س (رسم تعادل الدكتوراه).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge financial">إيصال مالي</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">10</div>
                                <div class="doc-content-text">إيصال المكتبة الوطنية (اختياري).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">اختياري</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">11</div>
                                <div class="doc-content-text">كشف علامات الدكتوراه (اختياري).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">اختياري</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">12</div>
                                <div class="doc-content-text">شهادة ICDL (اختياري).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">اختياري</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">13</div>
                                <div class="doc-content-text">شهادة اللغة الإنكليزية (اختياري).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">اختياري</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">14</div>
                                <div class="doc-content-text">جواز السفر مع صفحات الأختام (إلزامي لغير المعفى من الإقامة، واختياري للتخصص النظري المعفى).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">وثائق سفر</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">15</div>
                                <div class="doc-content-text">حركة الهجرة والجوازات (إلزامي لغير المعفى من الإقامة، واختياري للتخصص النظري المعفى).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">إثبات إقامة</span></div>
                        </div>

                        <div class="document-item-card">
                            <div class="doc-left-cluster">
                                <div class="doc-squircle-num">16</div>
                                <div class="doc-content-text">مرفقات أخرى / بحوث أو وثائق داعمة (اختياري).</div>
                            </div>
                            <div class="doc-right-cluster"><span class="doc-type-badge">اختياري</span></div>
                        </div>
                    </div>
                </div>

                <!-- Left Side: Scientific Production -->
                <div>
                    <div class="production-exam-card">
                        <div>
                            <h5><i class="fa-solid fa-book-bookmark"></i> ثانياً: وثائق فحص الإنتاج العلمي</h5>
                            <p>تُقدم بالتوازي مع الملف بآن واحد للمرشحين للتعيين بوظيفة مدرس من حملة الدكتوراه غير السورية:</p>

                            <div class="production-spec-box">
                                <div class="spec-icon-badge">
                                    <i class="fa-solid fa-book"></i>
                                </div>
                                <div>
                                    <strong style="color: #065f46; font-size: 0.92rem; display: block;">ثلاث نسخ ورقية</strong>
                                    <span style="font-size: 0.8rem; color: #047857;">عن أطروحة الدكتوراه مجلدة أصولاً</span>
                                </div>
                            </div>

                            <div class="production-spec-box">
                                <div class="spec-icon-badge">
                                    <i class="fa-solid fa-file-lines"></i>
                                </div>
                                <div>
                                    <strong style="color: #065f46; font-size: 0.92rem; display: block;">ثلاث ملخصات عربية</strong>
                                    <span style="font-size: 0.8rem; color: #047857;">عن الأطروحة باللغة العربية لا يقل كل ملخص عن (25) صفحة</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="border-color: #a7f3d0 !important;">
                            <a href="{{ route('university.apply.foreign_doctorate') }}" class="btn-new-app-glow w-100 justify-content-center">
                                <i class="fa-solid fa-paper-plane"></i> تقديم تعادل دكتوراه خارجية
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: Government University Faculty Members -->
    <div class="req-tab-content" id="tab-faculty">
        <div class="requirement-slab-card" style="border-top: 4px solid #d97706;">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-building-columns" style="color: #d97706;"></i> الوثائق المطلوبة لأعضاء الهيئة التدريسية بالجامعات الحكومية السورية</h3>
                    <p>حملة الدكتوراه من أعضاء الهيئة التدريسية بالجامعات الحكومية للمرشحين للتعيين بالجامعات الخاصة</p>
                </div>
                <div class="fee-announcement-card">
                    <i class="fa-solid fa-receipt fs-6"></i>
                    <span>رسم التعادل المعتمد:</span>
                    <strong>125,000 ل.س</strong>
                </div>
            </div>

            <div class="checklist-items-wrap">
                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">1</div>
                        <div class="doc-content-text"><strong>كتاب صادر عن الجامعة الخاصة:</strong> يتضمن طلب تقويم درجاته العلمية موجه للوزارة.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">وثيقة رسمية</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">2</div>
                        <div class="doc-content-text"><strong>نسخة مصدقة أصولاً عن شهادة الدكتوراه.</strong></div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge">نسخة مصدقة</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">3</div>
                        <div class="doc-content-text"><strong>بيان وضع أو بطاقة ذاتية رسمية:</strong> صادر عن الجامعة الحكومية يثبت كونه عضو هيئة تدريسية رسمياً.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge cert">بيان وضع رسمي</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>

                <div class="document-item-card">
                    <div class="doc-left-cluster">
                        <div class="doc-squircle-num">4</div>
                        <div class="doc-content-text"><strong>إيصال تسديد رسم تعادل:</strong> بقيمة 125,000 ل.س من حملة درجة الدكتوراه.</div>
                    </div>
                    <div class="doc-right-cluster">
                        <span class="doc-type-badge financial">إيصال مالي</span>
                        <i class="fa-solid fa-circle-check doc-check-icon"></i>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('university.apply.faculty_permission') }}" class="btn-new-app-glow">
                    <i class="fa-solid fa-paper-plane"></i> تقديم طلب سماح بالتدريس لعضو هيئة تدريسية
                </a>
            </div>
        </div>
    </div>

    <!-- TAB 6: External Certifications & Legal Rules -->
    <div class="req-tab-content" id="tab-certifications">
        <div class="requirement-slab-card" style="border-top: 4px solid #dc2626;">
            <div class="slab-header-row">
                <div class="slab-title-group">
                    <h3><i class="fa-solid fa-stamp" style="color: #dc2626;"></i> الشروط القانونية والتصديقات الإلزامية لجميع الوثائق والشهادات غير السورية</h3>
                    <p>يجب الالتزام التام بالضوابط التالية قبل مسح ورفع أي وثيقة غير سورية على المنظومة الإلكترونية</p>
                </div>
                <div class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill font-monospace fw-bold fs-7">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> تصديقات ملزمة أصولاً
                </div>
            </div>

            <!-- 4-Bento Grid -->
            <div class="certifications-bento-grid">
                <div class="cert-rule-bento-card">
                    <div class="cert-icon-circle" style="background: #fef2f2; color: #dc2626;">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <h5>1. التوثيق والتصديق الرسمي</h5>
                    <p>يجب أن تكون جميع الوثائق والشهادات الصادرة خارج سوريا مصدقة أصولاً من <strong>وزارة الخارجية والمغتربين في الجمهورية العربية السورية</strong> وخاتم البعثة الدبلوماسية المعتمدة.</p>
                </div>

                <div class="cert-rule-bento-card">
                    <div class="cert-icon-circle" style="background: #eff6ff; color: #2563eb;">
                        <i class="fa-solid fa-language"></i>
                    </div>
                    <h5>2. الترجمة القانونية المعتمدة</h5>
                    <p>الوثائق والشهادات الصادرة بلغة غير العربية يجب أن تكون <strong>مترجمة وموثقة أصولاً من وزارة العدل</strong> ونقابة المترجمين المعتمدين بالجمهورية العربية السورية.</p>
                </div>

                <div class="cert-rule-bento-card">
                    <div class="cert-icon-circle" style="background: #fefce8; color: #ca8a04;">
                        <i class="fa-solid fa-passport"></i>
                    </div>
                    <h5>3. جوازات السفر وحركة الإقامة</h5>
                    <p>إرفاق صور جواز السفر بالإضافة لإبراز الجواز الأصلي، وتقديم <strong>نموذج إثبات إقامة صادر عن مكتب التعادل</strong> أو وثيقة حركة هجرة وجوازات رسمية تثبت الإقامة طيلة سنوات الدراسة.</p>
                </div>

                <div class="cert-rule-bento-card">
                    <div class="cert-icon-circle" style="background: #f0fdf4; color: #16a34a;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h5>4. المطابقة الميدانية وإبراز الأصول</h5>
                    <p>يلتزم مدير الموارد البشرية بالجامعة الخاصة بإبراز <strong>كافة الشهادات والوثائق الأصلية</strong> أمام لجنة المطابقة والتعادل بمجلس التعليم العالي عند طلبها رسمياً.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. FOOTER STRIP -->
    <div class="docs-footer-strip">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-invoice text-secondary fs-5"></i>
            <span class="fs-7 text-muted">هذه اللائحة هي الدليل الإجرائي المعتمد قانوناً لدى وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي.</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" onclick="window.print()" class="btn-print-official">
                <i class="fa-solid fa-print"></i> طباعة اللائحة الرسمية
            </button>
            <a href="{{ route('university.apply.options') }}" class="btn-new-app-glow">
                <i class="fa-solid fa-circle-plus"></i> تقديم معاملة جديدة
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function switchRequirementTab(tabId, btn) {
        // Deactivate all buttons and tabs
        document.querySelectorAll('.tab-pill-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.req-tab-content').forEach(c => {
            c.classList.remove('active');
            c.style.display = 'none';
        });

        // Activate target button and tab
        if (btn) btn.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) {
            target.classList.add('active');
            target.style.display = 'block';
        }
    }
</script>
@endpush

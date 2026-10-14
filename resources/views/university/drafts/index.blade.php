@extends('layouts.university')

@section('title', 'إدارة المسودات والمعاملات المحفوظة - بوابة الجامعات')

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

    .drafts-page-wrapper {
        font-family: 'Tajawal', sans-serif;
    }

    /* 1. HERO BENTO BANNER */
    .hero-bento-card {
        background: linear-gradient(135deg, var(--secondary-navy) 0%, #13284c 100%);
        border-radius: 16px;
        padding: 24px 30px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(14, 30, 56, 0.12);
        border: 1px solid rgba(255,255,255,0.08);
        margin-bottom: 22px;
    }

    .hero-bento-card::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(197,155,39,0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-welcome-text h2 {
        font-size: 1.45rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 6px;
        color: #ffffff;
    }

    .hero-welcome-text p {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .hero-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--accent-gold) 0%, #aa841d 100%);
        color: #0b1526 !important;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px var(--accent-gold-glow);
        transition: all 0.25s ease;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .hero-cta-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(197, 155, 39, 0.4);
        color: #0b1526 !important;
    }

    /* 2. STATS & PILL SEARCH BAR */
    .drafts-filter-slab {
        background: #ffffff;
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .drafts-stats-badge-box {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stats-icon-tile {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.25) 100%);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stats-label-group .stats-title {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: block;
        font-weight: 600;
    }

    .stats-label-group .stats-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--secondary-navy);
        font-family: 'Plus Jakarta Sans', 'Tajawal', sans-serif;
    }

    .pill-search-container {
        position: relative;
        width: 380px;
        max-width: 100%;
    }

    .pill-search-container input {
        width: 100%;
        padding: 10px 44px 10px 38px;
        border: 1.5px solid var(--border-subtle);
        border-radius: 30px;
        font-size: 0.88rem;
        outline: none;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .pill-search-container input:focus {
        background: #ffffff;
        border-color: var(--secondary-navy);
        box-shadow: 0 0 0 3px rgba(14, 30, 56, 0.08);
    }

    .pill-search-container .search-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .pill-search-container .clear-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        display: none;
    }

    .pill-search-container .clear-icon:hover {
        color: #dc2626;
    }

    /* 3. BENTO CARDS GRID */
    .drafts-bento-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .bento-draft-card {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(14, 30, 56, 0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .bento-draft-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(14, 30, 56, 0.08);
        border-color: #cbd5e1;
    }

    /* Card Top Bar */
    .draft-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    .draft-code-badge {
        font-family: monospace;
        font-weight: 800;
        font-size: 0.8rem;
        background: #f1f5f9;
        color: #475569;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .draft-app-no-badge {
        font-family: monospace;
        font-weight: 700;
        font-size: 0.75rem;
        background: rgba(197, 155, 39, 0.1);
        color: #9a7617;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid rgba(197, 155, 39, 0.25);
    }

    .draft-time-pill {
        font-size: 0.74rem;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Candidate Info Cluster */
    .draft-candidate-cluster {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .candidate-squircle-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0e1e38 0%, #1e3a68 100%);
        color: #f5d77f;
        font-size: 1.25rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(14, 30, 56, 0.2);
        flex-shrink: 0;
        border: 1.5px solid rgba(197, 155, 39, 0.4);
    }

    .candidate-details {
        overflow: hidden;
    }

    .candidate-name {
        font-size: 1rem;
        font-weight: 800;
        color: var(--secondary-navy);
        margin: 0 0 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .candidate-nid {
        font-size: 0.78rem;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Academic Meta */
    .draft-academic-meta {
        background: #f8fafc;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 14px;
    }

    .meta-faculty-box {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: #334155;
        font-weight: 600;
    }

    .meta-faculty-box i {
        color: #94a3b8;
    }

    /* Badges */
    .pill-degree {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f5f3ff;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pill-degree.pill-sky {
        background: #f0f9ff;
        color: #0284c7;
        border-color: #bae6fd;
    }

    .pill-degree.pill-emerald {
        background: #f0fdfa;
        color: #0d9488;
        border-color: #99f6e4;
    }

    .pill-degree.pill-amber {
        background: #fffbeb;
        color: #d97706;
        border-color: #fde68a;
    }

    /* Progress Slab */
    .draft-progress-slab {
        margin-bottom: 16px;
    }

    .progress-title {
        font-size: 0.76rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .progress-percent-badge {
        font-size: 0.76rem;
        font-weight: 800;
        color: #b45309;
        background: #fef3c7;
        padding: 1px 7px;
        border-radius: 8px;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .progress-track {
        height: 7px;
        border-radius: 10px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .progress-bar-glow {
        height: 100%;
        background: linear-gradient(90deg, #c59b27, #f59e0b);
        border-radius: 10px;
        transition: width 0.4s ease;
    }

    /* Bottom Actions */
    .draft-card-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 4px;
    }

    .btn-resume-draft {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--secondary-navy) 0%, #1e3a68 100%);
        color: #ffffff !important;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 10px 16px;
        border-radius: 10px;
        text-decoration: none !important;
        transition: all 0.22s ease;
        box-shadow: 0 2px 8px rgba(14, 30, 56, 0.15);
    }

    .btn-resume-draft:hover {
        background: #091322;
        color: #f5d77f !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(14, 30, 56, 0.25);
    }

    .btn-delete-draft {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .btn-delete-draft:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: scale(1.06);
    }

    /* 4. EMPTY STATE */
    .empty-drafts-slab {
        background: #ffffff;
        border: 1.5px solid var(--border-subtle);
        border-radius: 20px;
        padding: 50px 24px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    .empty-drafts-halo-icon {
        width: 76px;
        height: 76px;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(197, 155, 39, 0.12) 0%, rgba(197, 155, 39, 0.25) 100%);
        border: 2px solid rgba(197, 155, 39, 0.4);
        box-shadow: 0 0 20px rgba(197, 155, 39, 0.2);
        color: var(--accent-gold);
        font-size: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
    }

    .empty-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--secondary-navy);
        margin-bottom: 8px;
    }

    .empty-subtitle {
        font-size: 0.88rem;
        color: var(--text-muted);
        max-width: 480px;
        margin: 0 auto 20px;
        line-height: 1.7;
    }

    .btn-action-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 20px;
        border-radius: 10px;
        border: 1.5px solid var(--secondary-navy);
        color: var(--secondary-navy) !important;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none !important;
        transition: all 0.2s;
    }

    .btn-action-outline:hover {
        background: var(--secondary-navy);
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="drafts-page-wrapper">

    <!-- 1. HERO BENTO BANNER -->
    <section class="hero-bento-card">
        <div class="hero-welcome-text">
            <h2>
                <i class="fa-solid fa-box-archive" style="color: var(--accent-gold-light);"></i>
                إدارة المسودات والمعاملات المحفوظة
            </h2>
            <p>هنا تجد كافة المعاملات التي قيد الإدخال أو المحفوظة كمسودة، يمكنك العودة لاستكمال بياناتها ومرفقاتها وإرسالها أصولاً لمجلس التعليم العالي.</p>
        </div>
        <div>
            @if(!empty($siteLocked) && $siteLocked)
                <button class="hero-cta-btn opacity-50" style="cursor: not-allowed;" title="تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة" disabled>
                    <i class="fa-solid fa-lock"></i> التقديم مغلق حالياً
                </button>
            @else
                <a href="{{ route('university.apply.options') }}" class="hero-cta-btn">
                    <i class="fa-solid fa-plus-circle"></i> تقديم طلب تعادل جديد
                </a>
            @endif
        </div>
    </section>

    <!-- Site Closed Alert Notice -->
    @if(!empty($siteLocked) && $siteLocked)
    <div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3.5" role="alert" style="background-color: #FEE2E2; color: #991B1B; border-right: 4px solid #DC2626 !important; border-radius: 12px;">
        <i class="fa-solid fa-lock fs-3 me-3 text-danger"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1 text-danger"><i class="fa-solid fa-ban me-1"></i> تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة</h6>
            <p class="mb-0 small" style="color: #7F1D1D;">{{ $siteNotice ?: 'الموقع مغلق حالياً من قبل الإدارة لتلقي طلبات جديدة. يمكنك تصفح واستكمال مسوداتك السابقة بشكل طبيعي.' }}</p>
        </div>
    </div>
    @endif

    <!-- 2. STATS & PILL SEARCH BAR -->
    <div class="drafts-filter-slab">
        <!-- Stats Badge -->
        <div class="drafts-stats-badge-box">
            <div class="stats-icon-tile">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div class="stats-label-group">
                <span class="stats-title">إجمالي المسودات قيد الإنجاز</span>
                <span class="stats-value">{{ number_format($draftsCount) }} <small class="text-muted fs-8 fw-normal">مسودة</small></span>
            </div>
        </div>

        <!-- Pill Search Bar -->
        <div class="pill-search-container">
            <i class="fa-solid fa-magnifying-glass search-icon" id="drafts-spinner"></i>
            <input type="text" 
                   id="drafts-search-input" 
                   value="{{ $search ?? '' }}" 
                   placeholder="ابحث باسم المرشح، الرقم الوطني، الكلية..." 
                   autocomplete="off"
                   onkeyup="filterDraftsCards()">
            <button type="button" id="drafts-clear-btn" class="clear-icon" title="مسح البحث">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- 3. DRAFTS DATA CARDS WRAPPER -->
    <div id="drafts-table-wrapper">
        @include('university.drafts.partials._table')
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Instant Client-Side Filter on Cards
    function filterDraftsCards() {
        const input = document.getElementById('drafts-search-input');
        const query = (input?.value || '').toLowerCase().trim();
        const clearBtn = document.getElementById('drafts-clear-btn');
        const cards = document.querySelectorAll('.bento-draft-card');

        if (clearBtn) {
            clearBtn.style.display = query ? 'inline-block' : 'none';
        }

        let visibleCount = 0;
        cards.forEach(card => {
            const text = (card.getAttribute('data-search-text') || card.innerText).toLowerCase();
            const matches = text.includes(query);
            card.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });

        // Handle empty indicator if client filter yields 0 matches
        let clientEmptyMsg = document.getElementById('client-empty-filter-msg');
        const container = document.getElementById('drafts-cards-container');
        if (visibleCount === 0 && query && container) {
            if (!clientEmptyMsg) {
                clientEmptyMsg = document.createElement('div');
                clientEmptyMsg.id = 'client-empty-filter-msg';
                clientEmptyMsg.className = 'empty-drafts-slab col-12 w-100 my-3 text-center';
                clientEmptyMsg.innerHTML = `
                    <div class="empty-drafts-halo-icon mb-2" style="width: 56px; height: 56px; font-size: 1.4rem;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: var(--secondary-navy);">لم يتم العثور على أي نتائج مطابقة</h5>
                    <p class="text-muted small mb-0">لا توجد مسودات مطابقة لعبارة البحث: "<strong>${query}</strong>".</p>
                `;
                container.parentNode.insertBefore(clientEmptyMsg, container.nextSibling);
            }
        } else if (clientEmptyMsg) {
            clientEmptyMsg.remove();
        }
    }

    // Debounced AJAX search for cross-page results
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('drafts-search-input');
        const clearBtn = document.getElementById('drafts-clear-btn');
        const spinnerIcon = document.getElementById('drafts-spinner');
        const tableWrapper = document.getElementById('drafts-table-wrapper');

        let debounceTimer = null;
        let abortController = null;

        function executeServerSearch(term) {
            if (!tableWrapper) return;

            if (abortController) abortController.abort();
            abortController = new AbortController();

            if (spinnerIcon) {
                spinnerIcon.className = 'fa-solid fa-circle-notch fa-spin search-icon';
                spinnerIcon.style.color = '#c59b27';
            }

            const fetchUrl = new URL("{{ route('university.drafts.index') }}", window.location.origin);
            if (term) {
                fetchUrl.searchParams.set('search', term);
            }
            fetchUrl.searchParams.set('ajax', '1');

            fetch(fetchUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: abortController.signal
            })
            .then(res => {
                if (!res.ok) throw new Error('Search failed');
                return res.text();
            })
            .then(html => {
                tableWrapper.innerHTML = html;
                filterDraftsCards();

                // Sync URL query without reloading
                const newUrl = new URL(window.location.href);
                if (term) {
                    newUrl.searchParams.set('search', term);
                } else {
                    newUrl.searchParams.delete('search');
                }
                window.history.replaceState({}, '', newUrl.toString());
            })
            .catch(err => {
                if (err.name !== 'AbortError') console.error(err);
            })
            .finally(() => {
                if (spinnerIcon) {
                    spinnerIcon.className = 'fa-solid fa-magnifying-glass search-icon';
                    spinnerIcon.style.color = '#94a3b8';
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterDraftsCards();

                clearTimeout(debounceTimer);
                const term = this.value.trim();
                debounceTimer = setTimeout(() => {
                    executeServerSearch(term);
                }, 400);
            });
        }

        if (clearBtn && searchInput) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearBtn.style.display = 'none';
                filterDraftsCards();
                executeServerSearch('');
                searchInput.focus();
            });
        }
    });
</script>
@endpush
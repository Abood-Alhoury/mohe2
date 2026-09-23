@extends('layouts.university')

@section('title', 'إدارة المسودات والمعاملات المحفوظة - لوحة تحكم الجامعة')

@section('content')

<!-- 1. BREADCRUMBS & PAGE HEADER -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('university.dashboard') }}" style="color: var(--primary-container); text-decoration: none;">الرئيسية</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">إدارة المسودات</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="headline-md text-prestigious mb-1" style="font-size: 1.5rem; color: #1A2A44;">
                    <i class="fa-solid fa-floppy-disk me-2" style="color: #C5A059;"></i> إدارة المسودات والمعاملات المحفوظة
                </h3>
                <p class="body-md text-muted mb-0">
                    هنا تجد كافة الطلبات والمعاملات التي قيد الإدخال أو المحفوظة كمسودة، يمكنك العودة لاستكمال إدخال البيانات ورفع المرفقات وإرسالها أصولاً للوزارة.
                </p>
            </div>
            <div>
                @if(!empty($siteLocked) && $siteLocked)
                    <button type="button" class="btn btn-outline-secondary py-2 px-3 fw-bold" disabled title="تقديم الطلبات مغلق بقرار الإدارة">
                        <i class="fa-solid fa-lock me-1"></i> تقديم طلب مغلق حالياً
                    </button>
                @else
                    <a href="{{ route('university.apply.options') }}" class="btn btn-gold-cta py-2 px-3.5 shadow-xs fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> تقديم طلب تعادل جديد
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Site Closed Alert Notice -->
@if(!empty($siteLocked) && $siteLocked)
<div class="alert border-0 shadow-sm d-flex align-items-center mb-4 p-3.5" role="alert" style="background-color: #FEE2E2; color: #991B1B; border-right: 4px solid #DC2626 !important; border-radius: 6px;">
    <i class="fa-solid fa-lock fs-3 me-3 text-danger"></i>
    <div>
        <h6 class="alert-heading fw-bold mb-1 text-danger"><i class="fa-solid fa-ban me-1"></i> تقديم الطلبات الجديدة مغلق حالياً لجميع الجامعات بقرار الإدارة</h6>
        <p class="mb-0 small" style="color: #7F1D1D;">{{ $siteNotice ?: 'الموقع مغلق حالياً من قبل الإدارة لتلقي طلبات جديدة. يمكنك تصفح واستكمال مسوداتك السابقة بشكل طبيعي.' }}</p>
    </div>
</div>
@endif

<!-- 2. DRAFTS SEARCH & STATS BAR -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; border: 1px solid #e2e8f0; background: #ffffff;">
    <div class="card-body p-3.5">
        <div class="row align-items-center g-3">
            <!-- Stats -->
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center bg-warning-subtle rounded-circle" style="width: 46px; height: 46px;">
                        <i class="fa-solid fa-folder-open fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-8 text-muted fw-medium">إجمالي المسودات قيد الإنجاز</div>
                        <div class="fs-5 fw-bold" style="color: #1A2A44;">{{ number_format($draftsCount) }} <span class="fs-7 fw-normal text-muted">مسودة</span></div>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="col-12 col-md-8">
                <form id="drafts-search-form" action="{{ route('university.drafts.index') }}" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-2xs rounded" style="overflow: hidden; border: 1px solid #C5C6CE;">
                        <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                            <i class="fa-solid fa-magnifying-glass" id="drafts-spinner" style="color: #C5A059;"></i>
                        </span>
                        <input type="text" 
                               id="drafts-search-input"
                               name="search" 
                               value="{{ $search ?? '' }}" 
                               class="form-control border-0 bg-white shadow-none ps-1 py-2" 
                               placeholder="البحث باسم المرشح، الرقم الوطني، رقم المسودة، أو الكلية..." 
                               style="font-size: 0.9rem;"
                               autocomplete="off">
                        <button type="button" 
                                id="drafts-clear-btn" 
                                class="input-group-text bg-white border-0 text-muted px-3" 
                                title="مسح البحث"
                                style="cursor: pointer; display: {{ !empty($search) ? 'inline-flex' : 'none' }};">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button type="submit" id="drafts-submit-btn" class="btn btn-gold-cta px-4 fw-bold border-0">بحث في المسودات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. DRAFTS DATA TABLE WRAPPER -->
<div id="drafts-table-wrapper">
    @include('university.drafts.partials._table')
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('drafts-search-form');
    const searchInput = document.getElementById('drafts-search-input');
    const clearBtn = document.getElementById('drafts-clear-btn');
    const spinnerIcon = document.getElementById('drafts-spinner');
    const tableWrapper = document.getElementById('drafts-table-wrapper');

    let debounceTimer = null;
    let abortController = null;

    function fetchDrafts(extraParams = {}) {
        if (!tableWrapper) return;

        const query = searchInput ? searchInput.value.trim() : '';

        if (clearBtn) {
            clearBtn.style.display = query ? 'inline-flex' : 'none';
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        if (spinnerIcon) {
            spinnerIcon.className = 'fa-solid fa-circle-notch fa-spin';
            spinnerIcon.style.color = '#C5A059';
        }

        const fetchUrl = new URL("{{ route('university.drafts.index') }}", window.location.origin);
        if (query) {
            fetchUrl.searchParams.set('search', query);
        }

        for (const [key, value] of Object.entries(extraParams)) {
            if (value) {
                fetchUrl.searchParams.set(key, value);
            } else {
                fetchUrl.searchParams.delete(key);
            }
        }

        fetchUrl.searchParams.set('ajax', '1');

        fetch(fetchUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            signal: abortController.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.text();
        })
        .then(html => {
            tableWrapper.innerHTML = html;

            // Sync URL without reloading
            const stateUrl = new URL(fetchUrl.toString());
            stateUrl.searchParams.delete('ajax');
            window.history.replaceState({}, '', stateUrl.toString());
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Error fetching drafts:', err);
            }
        })
        .finally(() => {
            if (spinnerIcon) {
                spinnerIcon.className = 'fa-solid fa-magnifying-glass';
                spinnerIcon.style.color = '#C5A059';
            }
        });
    }

    // 1. Live Instant Search as you type (350ms debounce)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchDrafts({ page: 1 });
            }, 350);
        });
    }

    // 2. Submit search form on Enter or Search Button
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchDrafts({ page: 1 });
        });
    }

    // 3. Clear button
    if (clearBtn && searchInput) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            this.style.display = 'none';
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchDrafts({ page: 1 });
            searchInput.focus();
        });
    }

    // 4. Reset link in empty state
    document.addEventListener('click', function(e) {
        const resetLink = e.target.closest('#drafts-table-wrapper a[href*="university/drafts"]');
        if (resetLink && !resetLink.href.includes('options') && !resetLink.href.includes('edit')) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchDrafts({ page: 1 });
        }
    });

    // 5. Intercept pagination clicks for seamless AJAX navigation
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#drafts-table-wrapper .pagination a');
        if (pageLink && pageLink.href) {
            e.preventDefault();
            const pageUrl = new URL(pageLink.href);
            const pageNum = pageUrl.searchParams.get('page');
            fetchDrafts({ page: pageNum });
        }
    });
});
</script>
@endpush
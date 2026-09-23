@extends('layouts.admin')

@section('title', 'طلبات التعادل - إدارة وقرارات')

@section('content')
<div class="card-academic-table" x-data="{ showFilter: {{ $statusFilter || $universityFilter ? 'true' : 'false' }} }">
    
    <!-- HEADER BAR: TITLE ON RIGHT, SEARCH BOX IN MIDDLE, FILTER & RESET BUTTONS ON FAR LEFT -->
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3" style="margin-bottom: 20px;">
        <!-- 1. RIGHT: SECTION TITLE -->
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5" style="color: var(--imperial-navy);"></i>
            <h5 class="fw-bold mb-0" style="color: var(--imperial-navy);">سجلات وطلبات معادلة الشهادات العلمية</h5>
        </div>

        <!-- 2. CENTER: INSTANT SEARCH BOX (LIVE AJAX SEARCH) -->
        <div class="flex-grow-1 mx-md-3" style="max-width: 420px;">
            <form id="admin-apps-search-form" action="{{ route('admin.applications.index') }}" method="GET" class="position-relative m-0">
                <div class="input-group input-group-sm shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--outline-variant);">
                    <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                        <i class="fa-solid fa-magnifying-glass" id="admin-apps-spinner" style="color: var(--heritage-gold);"></i>
                    </span>
                    <input type="text" 
                           id="admin-apps-search-input"
                           name="search" 
                           value="{{ $searchQuery ?? '' }}" 
                           class="form-control border-0 bg-white shadow-none ps-1" 
                           placeholder="البحث باسم المتقدم أو رقم المعاملة أو الكلية..." 
                           style="font-size: 0.88rem;"
                           autocomplete="off">
                    <button type="button" 
                            id="admin-apps-clear-btn" 
                            class="input-group-text bg-white border-0 text-muted px-2" 
                            title="مسح البحث"
                            style="cursor: pointer; display: {{ !empty($searchQuery) ? 'inline-flex' : 'none' }};">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="submit" id="admin-apps-submit-btn" class="btn btn-gold-cta px-3 fw-bold border-0">بحث</button>
                </div>
            </form>
        </div>

        <!-- 3. LEFT: FILTER & RESET BUTTONS -->
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-gold-cta btn-sm px-3 fw-bold" 
                    type="button" 
                    @click="showFilter = !showFilter">
                <i class="fa-solid fa-filter me-1"></i> فرز الطلبات
            </button>
            <a href="{{ route('admin.applications.index') }}" id="admin-apps-reset-btn" class="btn btn-outline-navy btn-sm px-3 fw-bold">
                <i class="fa-solid fa-rotate-left me-1"></i> إعادة ضبط
            </a>
        </div>
    </div>

    <!-- FILTER COLLAPSIBLE PANEL -->
    <div x-show="showFilter" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-white border-bottom p-4" style="background-color: #FBF9FB !important;">
        <form id="admin-apps-filter-form" action="{{ route('admin.applications.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">نوع المعاملة :</label>
                <select name="request_type" id="filter-request-type" class="form-select">
                    <option value="">-- كافة أنواع الطلبات --</option>
                    @foreach($requestTypesList as $rt)
                        <option value="{{ $rt->name }}" {{ ($requestTypeFilter == $rt->name || request('request_type') == $rt->name) ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">تصفية حسب حالة الطلب :</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">-- كافة الحالات --</option>
                    @foreach(($filterStatusesList ?? $statusesList) as $st)
                        <option value="{{ $st }}" {{ $statusFilter == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">تصفية حسب الجامعة :</label>
                <select name="university_id" id="filter-university-id" class="form-select">
                    <option value="">-- كافة الجامعات --</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->id }}" {{ $universityFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-solid-navy w-100 py-2"><i class="fa-solid fa-magnifying-glass me-1"></i> فرز</button>
            </div>
        </form>
    </div>

    <!-- DATA TABLE WRAPPER -->
    <div id="applications-table-wrapper">
        @include('admin.applications.partials._table')
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('admin-apps-search-form');
    const searchInput = document.getElementById('admin-apps-search-input');
    const clearBtn = document.getElementById('admin-apps-clear-btn');
    const spinnerIcon = document.getElementById('admin-apps-spinner');
    const filterForm = document.getElementById('admin-apps-filter-form');
    const resetBtn = document.getElementById('admin-apps-reset-btn');
    const tableWrapper = document.getElementById('applications-table-wrapper');

    const filterReqType = document.getElementById('filter-request-type');
    const filterStatus = document.getElementById('filter-status');
    const filterUni = document.getElementById('filter-university-id');

    let debounceTimer = null;
    let abortController = null;

    function fetchAdminApplications(extraParams = {}) {
        if (!tableWrapper) return;

        const query = searchInput ? searchInput.value.trim() : '';
        const reqType = filterReqType ? filterReqType.value : '';
        const status = filterStatus ? filterStatus.value : '';
        const uniId = filterUni ? filterUni.value : '';

        if (clearBtn) {
            clearBtn.style.display = query ? 'inline-flex' : 'none';
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        if (spinnerIcon) {
            spinnerIcon.className = 'fa-solid fa-circle-notch fa-spin';
            spinnerIcon.style.color = 'var(--heritage-gold)';
        }

        const fetchUrl = new URL("{{ route('admin.applications.index') }}", window.location.origin);
        if (query) fetchUrl.searchParams.set('search', query);
        if (reqType) fetchUrl.searchParams.set('request_type', reqType);
        if (status) fetchUrl.searchParams.set('status', status);
        if (uniId) fetchUrl.searchParams.set('university_id', uniId);

        // Merge any extra params (such as page)
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

            // Sync URL without reloading or scrolling
            const stateUrl = new URL(fetchUrl.toString());
            stateUrl.searchParams.delete('ajax');
            window.history.replaceState({}, '', stateUrl.toString());
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Error fetching applications:', err);
            }
        })
        .finally(() => {
            if (spinnerIcon) {
                spinnerIcon.className = 'fa-solid fa-magnifying-glass';
                spinnerIcon.style.color = 'var(--heritage-gold)';
            }
        });
    }

    // 1. Live Instant Search as you type (350ms debounce)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchAdminApplications({ page: 1 });
            }, 350);
        });
    }

    // 2. Submit search form on Enter or Search Button
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchAdminApplications({ page: 1 });
        });
    }

    // 3. Clear button
    if (clearBtn && searchInput) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            this.style.display = 'none';
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchAdminApplications({ page: 1 });
            searchInput.focus();
        });
    }

    // 4. Instant Filter change on dropdown select
    [filterReqType, filterStatus, filterUni].forEach(el => {
        if (el) {
            el.addEventListener('change', function() {
                if (debounceTimer) clearTimeout(debounceTimer);
                fetchAdminApplications({ page: 1 });
            });
        }
    });

    // 5. Filter Form Submit
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchAdminApplications({ page: 1 });
        });
    }

    // 6. Reset Filters button
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (filterReqType) filterReqType.value = '';
            if (filterStatus) filterStatus.value = '';
            if (filterUni) filterUni.value = '';
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchAdminApplications({ page: 1 });
        });
    }

    // 7. Intercept pagination clicks for seamless AJAX navigation
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#applications-table-wrapper .pagination a');
        if (pageLink && pageLink.href) {
            e.preventDefault();
            const pageUrl = new URL(pageLink.href);
            const pageNum = pageUrl.searchParams.get('page');
            fetchAdminApplications({ page: pageNum });
        }
    });
});
</script>
@if(request()->has('open_message'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var openAppId = "{{ request()->get('open_message') }}";
        var modalEl = document.getElementById('messageModal' + openAppId);
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            var chatBox = modalEl.querySelector('.messages-chat-box');
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endif
@endpush

@endsection

@extends('layouts.admin')

@section('title', 'إصدار قرارات تعادل الماجستير الداخلي والأهلية - وزارة التعليم العالي')

@section('content')

<!-- NAV TABS FOR DECISION TYPES -->
@include('admin.decisions._nav_tabs', ['active' => 'master'])

<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار قرارات تعادل الماجستير والأهلية
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-file-signature" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار ورصد قرارات تعادل الماجستير والأهلية</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <form action="{{ route('admin.decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب الماجستير (البحث الذكي المباشر عن المرشح) --}}
                    <div class="mb-3 text-start" x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedName: '',
                        items: [
                            @foreach($approvedApps as $ap)
                            {
                                id: '{{ $ap->id }}',
                                name: '{{ addslashes($ap->candidate->full_name ?? '') }}',
                                appNo: '{{ addslashes($ap->application_no ?? '') }}',
                                university: '{{ addslashes($ap->workUniversity->name ?? '') }}'
                            },
                            @endforeach
                        ],
                        get filteredItems() {
                            if (!this.search || this.search === this.selectedName) {
                                return this.items;
                            }
                            const q = this.search.toLowerCase().trim();
                            return this.items.filter(i => 
                                i.name.toLowerCase().includes(q) || 
                                i.appNo.toLowerCase().includes(q) || 
                                i.university.toLowerCase().includes(q)
                            );
                        },
                        select(item) {
                            this.selectedId = item.id;
                            this.selectedName = item.name + ' (' + item.appNo + ' - ' + item.university + ')';
                            this.search = this.selectedName;
                            this.open = false;
                        },
                        clear() {
                            this.selectedId = '';
                            this.selectedName = '';
                            this.search = '';
                        }
                    }" @click.outside="open = false; if(!selectedId) { search = ''; } else { search = selectedName; }">

                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            اختر طلب تعادل الماجستير الموافق عليه :
                        </label>

                        <input type="hidden" name="application_id" :value="selectedId" required>

                        <div class="position-relative">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted ps-2.5">
                                    <i class="fa-solid fa-magnifying-glass" style="color: var(--heritage-gold); font-size: 0.8rem;"></i>
                                </span>
                                <input type="text" 
                                       x-model="search" 
                                       @focus="open = true" 
                                       @input="open = true; if(search !== selectedName) { selectedId = ''; }"
                                       class="form-control form-control-sm border-start-0 text-start shadow-none" 
                                       style="direction: rtl; text-align: right !important; font-size: 0.85rem;" 
                                       placeholder="اكتب اسم المرشح أو رقم المعاملة للبحث..." 
                                       autocomplete="off">
                                <template x-if="selectedId">
                                    <button type="button" @click="clear()" class="btn btn-sm btn-outline-secondary border-0 px-2" title="إلغاء الاختيار">
                                        <i class="fa-solid fa-xmark text-danger"></i>
                                    </button>
                                </template>
                            </div>

                            <!-- Dropdown list -->
                            <div x-show="open" 
                                 x-transition 
                                 class="position-absolute start-0 end-0 bg-white border rounded shadow-lg mt-1 overflow-auto" 
                                 style="max-height: 220px; z-index: 1050; border-color: var(--outline-variant) !important; display: none;">
                                
                                <template x-for="item in filteredItems" :key="item.id">
                                    <div @click="select(item)" 
                                         class="p-2 border-bottom d-flex flex-column text-start"
                                         style="cursor: pointer; border-color: #F1F5F9 !important;"
                                         :style="selectedId === item.id ? 'background-color: #FAF6EE; border-right: 3px solid var(--heritage-gold);' : ''"
                                         onmouseover="this.style.backgroundColor='#F8FAFC'" 
                                         onmouseout="this.style.backgroundColor=(selectedId === item.id ? '#FAF6EE' : '#FFFFFF')">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="fw-bold text-dark fs-7" x-text="item.name"></span>
                                            <span class="badge bg-light text-secondary border fs-9 font-monospace" x-text="item.appNo"></span>
                                        </div>
                                        <div class="fs-9 text-muted mt-0.5 d-flex align-items-center gap-1">
                                            <i class="fa-solid fa-building-columns fs-10 text-secondary opacity-75"></i>
                                            <span x-text="item.university"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="filteredItems.length === 0" class="p-3 text-center text-muted fs-8">
                                    <i class="fa-solid fa-circle-exclamation me-1 text-warning"></i> لا يوجد مرشح ماجستير مطابق للبحث
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- سطر 1: رقم قرار الأهلية وتاريخ الصدور (تفريغ جميع القيم افتراضياً) --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار الأهلية (اختياري) :
                            </label>
                            <input
                                type="text"
                                name="eligibility_decision_no"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                                placeholder="مثال: أ.هـ/502/2026"
                                value=""
                            >
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ صدور الأهلية :
                            </label>
                            <input
                                type="date"
                                name="eligibility_decision_date"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                                value=""
                            >
                        </div>
                    </div>

                    {{-- تحميل نسخة قرار الأهلية (PDF) --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            تحميل نسخة قرار الأهلية (PDF) :
                        </label>
                        <input
                            type="file"
                            name="eligibility_decision_file"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            accept=".pdf,image/*"
                        >
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- سطر 2: رقم قرار المعادلة وتاريخ الصدور (تفريغ جميع القيم افتراضياً) --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار معادلة الماجستير :
                            </label>
                            <input
                                type="text"
                                name="decision_no"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                                placeholder="مثال: م.ل.ق/105/2026"
                                value=""
                                required
                            >
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ صدور المعادلة :
                            </label>
                            <input
                                type="date"
                                name="decision_date"
                                class="form-control form-control-sm text-start"
                                style="direction: rtl; text-align: right !important;"
                                value=""
                                required
                            >
                        </div>
                    </div>

                    {{-- تحميل نسخة قرار التعادل الموقع (PDF) --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            تحميل نسخة قرار تعادل الماجستير الموقع (PDF) :
                        </label>
                        <input
                            type="file"
                            name="decision_file"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            accept=".pdf,image/*"
                            required
                        >
                    </div>

                    {{-- ملاحظات القرار --}}
                    <div class="mb-3.5 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            ملاحظات القرار :
                        </label>
                        <textarea
                            name="notes"
                            class="form-control form-control-sm text-start"
                            style="direction: rtl; text-align: right !important;"
                            rows="2"
                            placeholder="ملاحظات رئيس مجلس التعليم العالي"
                        ></textarea>
                    </div>

                    {{-- زر الإرسال --}}
                    <button
                        type="submit"
                        class="btn btn-gold-cta py-2.5 w-100 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                    >
                        <span>إرسال القرار ورصد الصدور</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: أرشيف قرارات تعادل الماجستير الصادرة
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-box-archive" style="color: var(--heritage-gold-light);"></i>
                    <span>قرارات تعادل الماجستير والأهلية الصادرة المرسلة للجامعات</span>
                </h5>
                <span class="badge bg-white bg-opacity-10 text-white border border-white-50 px-2.5 py-1 fs-8 fw-normal">
                    إجمالي القرارات: {{ $issuedDecisions->count() }}
                </span>
            </div>

            {{-- شريط البحث الأكاديمي الموحد --}}
            <div class="p-3 bg-white border-bottom" dir="rtl">
                <form action="{{ route('admin.decisions.index') }}" method="GET" class="position-relative m-0">
                    <div class="input-group input-group-sm shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--outline-variant);">
                        <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                            <i class="fa-solid fa-magnifying-glass" style="color: var(--heritage-gold);"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ $search ?? '' }}" 
                               class="form-control border-0 bg-white shadow-none ps-1" 
                               placeholder="البحث باسم المرشح أو رقم القرار أو اسم الجامعة..." 
                               style="font-size: 0.88rem;"
                               autocomplete="off">
                        @if(!empty($search))
                            <a href="{{ route('admin.decisions.index') }}" 
                               class="input-group-text bg-white border-0 text-muted px-2 text-decoration-none" title="مسح البحث">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-gold-cta px-4 fw-bold border-0">بحث</button>
                    </div>
                </form>
            </div>

            {{-- جدول القرارات الصادرة --}}
            <div class="card-body p-0" dir="rtl">
                <div class="table-responsive">
                    <table class="table mohe-table align-middle text-center mb-0">
                        <thead>
                            <tr>
                                <th>رقم قرار المعادلة</th>
                                <th>تاريخ المعادلة</th>
                                <th>رقم قرار الأهلية</th>
                                <th>تاريخ الأهلية</th>
                                <th>اسم المرشح</th>
                                <th>الجامعة</th>
                                <th style="min-width: 100px;">القرارات (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                            <tr>
                                <td class="fw-bold" style="color: var(--imperial-navy);">{{ $dec->decision_no }}</td>
                                <td class="text-muted fs-7">{{ $dec->decision_date ? format_sys_date($dec->decision_date) : '-' }}</td>
                                <td class="fw-bold text-secondary">{{ $dec->eligibility_decision_no ?? '-' }}</td>
                                <td class="text-muted fs-7">{{ $dec->eligibility_decision_date ? format_sys_date($dec->eligibility_decision_date) : '-' }}</td>
                                <td class="fw-bold text-dark">{{ $dec->application->candidate->full_name ?? '-' }}</td>
                                <td class="text-secondary fw-semibold fs-7">{{ $dec->application->workUniversity->name ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        @if($dec->eligibility_file_path)
                                            <a href="{{ asset('storage/' . $dec->eligibility_file_path) }}" target="_blank" 
                                               class="btn btn-sm btn-light border border-info text-info p-1.5 rounded shadow-2xs d-inline-flex align-items-center justify-content-center" 
                                               style="width: 32px; height: 32px;"
                                               title="تحميل قرار الأهلية (PDF)">
                                                <i class="fa-solid fa-file-invoice fs-6"></i>
                                            </a>
                                        @endif
                                        <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" 
                                           class="btn btn-sm btn-light border border-danger text-danger p-1.5 rounded shadow-2xs d-inline-flex align-items-center justify-content-center" 
                                           style="width: 32px; height: 32px;"
                                           title="تحميل قرار معادلة الماجستير (PDF)">
                                            <i class="fa-solid fa-file-pdf fs-6"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="opacity-50 mb-2">
                                        <i class="fa-solid fa-stamp fs-1" style="color: var(--imperial-navy);"></i>
                                    </div>
                                    <p class="mb-0">لا توجد قرارات تعادل ماجستير صادرة حالياً.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

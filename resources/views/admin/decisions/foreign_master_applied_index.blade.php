@extends('layouts.admin')

@section('title', 'إصدار قرارات تعادل الماجستير الخارجي التطبيقي')

@section('content')

<!-- NAV TABS FOR DECISION TYPES -->
@include('admin.decisions._nav_tabs', ['active' => 'foreign_master_applied'])

<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار قرارات تعادل الماجستير الخارجي التطبيقي
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-screwdriver-wrench" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار قرارات الماجستير الخارجي التطبيقي</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <!-- Info banner: No eligibility needed -->
                <div class="alert alert-warning py-2 px-2.5 mb-3 border-0 small d-flex align-items-start gap-2" style="background-color: #fffbeb; color: #92400e; font-size: 0.8rem; border-radius: 4px;">
                    <i class="fa-solid fa-circle-info fs-6 mt-0.5 text-warning flex-shrink-0"></i>
                    <div>
                        <strong>مسار تطبيقي (هيئة فنية):</strong> يعتمد لتدريس الجوانب التطبيقية والمخبرية فقط، <u>ولا يتطلب إجراء مقابلة أو صدور قرار أهلية</u>.
                    </div>
                </div>

                <form action="{{ route('admin.foreign_master_applied_decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب الماجستير الخارجي التطبيقي --}}
                    <div class="mb-3 text-start" x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedName: '',
                        selectedType: '',
                        items: [
                            @foreach($approvedApps as $ap)
                            {
                                id: '{{ $ap->id }}',
                                name: '{{ addslashes($ap->candidate->full_name ?? '') }}',
                                appNo: '{{ addslashes($ap->application_no ?? '') }}',
                                university: '{{ addslashes($ap->workUniversity->name ?? '') }}',
                                type: '{{ addslashes($ap->request_type ?? 'ماجستير خارجي تطبيقي') }}'
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
                                i.university.toLowerCase().includes(q) ||
                                i.type.toLowerCase().includes(q)
                            );
                        },
                        select(item) {
                            this.selectedId = item.id;
                            this.selectedName = item.name + ' (' + item.appNo + ' - ' + item.university + ')';
                            this.selectedType = item.type;
                            this.search = this.selectedName;
                            this.open = false;
                        },
                        clear() {
                            this.selectedId = '';
                            this.selectedName = '';
                            this.selectedType = '';
                            this.search = '';
                        },
                        init() {
                            const preId = '{{ request('app_id') }}';
                            if (preId) {
                                const found = this.items.find(i => i.id == preId);
                                if (found) {
                                    this.select(found);
                                }
                            }
                        }
                    }" x-init="init()" @click.outside="open = false; if(!selectedId) { search = ''; } else { search = selectedName; }">

                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            اختر طلب الماجستير الخارجي التطبيقي الموافق عليه :
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
                                 class="position-absolute start-0 end-0 bg-white border rounded shadow-lg mt-1 p-0 overflow-auto text-start" 
                                 style="max-height: 220px; z-index: 1050; direction: rtl;">
                                <template x-for="item in filteredItems" :key="item.id">
                                    <div @click="select(item)" 
                                         class="p-2 border-bottom cursor-pointer hover-bg-light small d-flex flex-column gap-0.5"
                                         style="transition: background-color 0.15s ease;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark" x-text="item.name"></span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-9" x-text="item.appNo"></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center text-muted fs-9">
                                            <span x-text="item.university"></span>
                                            <span class="badge bg-light text-secondary border fs-9" x-text="item.type"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="filteredItems.length === 0">
                                    <div class="p-3 text-center text-muted small">
                                        <i class="fa-solid fa-inbox me-1"></i> لا توجد طلبات ماجستير خارجي تطبيقي مطابقة بحالة بانتظار إصدار القرار.
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Shortcut button to Generate Official Decision Text & PDF -->
                        <template x-if="selectedId">
                            <div class="mt-2 text-start">
                                <a :href="'/admin/reports/' + selectedId + '/generate-decision'" 
                                   target="_blank" 
                                   class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                    <span>توليد ومعاينة نص القرار الرسمي التطبيقي والـ PDF</span>
                                </a>
                            </div>
                        </template>
                    </div>

                    <hr class="my-2 text-muted opacity-25">

                    {{-- سطر: رقم قرار التعادل وتاريخ الصدور --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                رقم قرار التعادل <span class="text-danger">*</span> :
                            </label>
                            <input type="text" name="decision_no" class="form-control form-control-sm text-start" placeholder="مثال: 55 / ل.م" value="" required>
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                                تاريخ قرار التعادل <span class="text-danger">*</span> :
                            </label>
                            <input type="date" name="decision_date" class="form-control form-control-sm text-start" value="" required>
                        </div>
                    </div>

                    {{-- ملف قرار التعادل --}}
                    <div class="mb-2.5 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            ملف قرار تعادل الماجستير التطبيقي (PDF) <span class="text-danger">*</span> :
                        </label>
                        <input type="file" name="decision_file" class="form-control form-control-sm text-start" accept=".pdf,image/*" required>
                    </div>

                    {{-- ملاحظات --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">ملاحظات إضافية :</label>
                        <textarea name="notes" class="form-control form-control-sm text-start" rows="2" placeholder="أدخل أي ملاحظات خاصة بقرار الماجستير الخارجي التطبيقي..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-solid-navy btn-sm w-100 fw-bold py-2 shadow-2xs d-flex align-items-center justify-content-center gap-1.5">
                        <i class="fa-solid fa-file-circle-check text-warning"></i>
                        <span>حفظ وإصدار القرار التطبيقي وإشعار الجامعة</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: جدول سجل قرارات تعادل الماجستير الخارجي التطبيقي الصادرة
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-top: 3.5px solid var(--imperial-navy) !important; border-radius: 4px;">
            <!-- Header with Search -->
            <div class="card-header py-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0 fs-6 fw-bold d-flex align-items-center gap-2" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-clipboard-list" style="color: var(--heritage-gold);"></i>
                    <span>سجل قرارات تعادل الماجستير الخارجي التطبيقي الصادرة</span>
                </h5>

                <form method="GET" action="{{ route('admin.foreign_master_applied_decisions.index') }}" class="d-flex align-items-center gap-1.5 m-0">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" 
                               name="search" 
                               value="{{ $search ?? '' }}" 
                               class="form-control form-control-sm border-end-0 text-start shadow-none" 
                               placeholder="بحث باسم المرشح أو القرار..." 
                               style="font-size: 0.8rem;">
                        <button class="btn btn-outline-secondary border-start-0 bg-white" type="submit">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </button>
                    </div>
                    @if(!empty($search))
                        <a href="{{ route('admin.foreign_master_applied_decisions.index') }}" class="btn btn-sm btn-outline-danger px-2" title="إلغاء البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.84rem;">
                        <thead style="background-color: #f8fafc; color: var(--imperial-navy); border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th class="py-2.5 px-3 text-start">المرشح / الجامعة</th>
                                <th class="py-2.5 px-2">رقم المعاملة</th>
                                <th class="py-2.5 px-2">قرار التعادل التطبيقي</th>
                                <th class="py-2.5 px-2">تاريخ الصدور</th>
                                <th class="py-2.5 px-2">المسار</th>
                                <th class="py-2.5 px-3 text-center">الإجراءات والملف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                                @php
                                    $app = $dec->application;
                                    $cand = $app ? $app->candidate : null;
                                    $uni = $app ? $app->workUniversity : null;
                                @endphp
                                <tr>
                                    <!-- Candidate and University -->
                                    <td class="py-2.5 px-3 text-start">
                                        <div class="fw-bold text-dark">{{ $cand ? $cand->full_name : '---' }}</div>
                                        <div class="text-muted fs-9">{{ $uni ? $uni->name : '---' }}</div>
                                    </td>

                                    <!-- Application Number -->
                                    <td class="py-2.5 px-2">
                                        <span class="badge bg-light text-secondary border fw-mono">
                                            #{{ $app ? $app->application_no : '---' }}
                                        </span>
                                    </td>

                                    <!-- Applied Decision Number -->
                                    <td class="py-2.5 px-2">
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 fw-bold">
                                            {{ $dec->decision_no ?? '---' }}
                                        </span>
                                    </td>

                                    <!-- Issue Date -->
                                    <td class="py-2.5 px-2">
                                        <div class="fw-bold text-dark">{{ format_sys_date($dec->decision_date) }}</div>
                                        <div class="text-muted fs-9">{{ $dec->created_at ? $dec->created_at->diffForHumans() : '' }}</div>
                                    </td>

                                    <!-- Track Badge -->
                                    <td class="py-2.5 px-2">
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1 fs-9">
                                            <i class="fa-solid fa-screwdriver-wrench me-1"></i> تطبيقي (هيئة فنية)
                                        </span>
                                    </td>

                                    <!-- Actions & File Download -->
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="d-inline-flex align-items-center gap-1.5">
                                            @if($dec->file_path)
                                                <a href="{{ Storage::url($dec->file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-xs btn-outline-primary px-2 py-1 shadow-2xs d-inline-flex align-items-center gap-1"
                                                   title="تحميل قرار التعادل التطبيقي">
                                                    <i class="fa-solid fa-file-pdf text-danger"></i>
                                                    <span>القرار</span>
                                                </a>
                                            @endif

                                            @if($app)
                                                <a href="{{ route('admin.reports.generate_decision', $app->id) }}" 
                                                   target="_blank" 
                                                   class="btn btn-xs btn-outline-secondary px-2 py-1 shadow-2xs d-inline-flex align-items-center gap-1"
                                                   title="معاينة نموذج القرار الرسمي وطباعته">
                                                    <i class="fa-solid fa-print text-muted"></i>
                                                    <span>نموذج A4</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-muted text-center">
                                        <i class="fa-solid fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
                                        لا توجد قرارات تعادل ماجستير خارجي تطبيقي مسجلة حالياً.
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

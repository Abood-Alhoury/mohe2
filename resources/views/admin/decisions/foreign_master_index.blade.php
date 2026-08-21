@extends('layouts.admin')

@section('title', 'إصدار قرارات تعادل الماجستير الخارجي')

@section('content')

<!-- NAV TABS FOR DECISION TYPES -->
@include('admin.decisions._nav_tabs', ['active' => 'foreign_master'])

<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار قرارات تعادل الماجستير الخارجي
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-earth-americas" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار ورصد قرارات الماجستير الخارجي</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <form action="{{ route('admin.foreign_master_decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب الماجستير الخارجي (البحث الذكي المباشر عن المرشح) --}}
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
                                type: '{{ addslashes($ap->request_type ?? 'ماجستير خارجي') }}'
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
                        }
                    }" @click.outside="open = false; if(!selectedId) { search = ''; } else { search = selectedName; }">

                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">
                            اختر طلب الماجستير الخارجي الموافق عليه :
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
                                        <div class="fs-9 text-muted mt-0.5 d-flex align-items-center justify-content-between">
                                            <span><i class="fa-solid fa-building-columns fs-10 text-secondary opacity-75 me-1"></i> <span x-text="item.university"></span></span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis fs-10" x-text="item.type"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="filteredItems.length === 0" class="p-3 text-center text-muted fs-8">
                                    <i class="fa-solid fa-circle-exclamation me-1 text-warning"></i> لا يوجد مرشح مطابق للبحث
                                </div>
                            </div>
                        </div>

                        <!-- Template Generator Action Link if selected -->
                        <template x-if="selectedId">
                            <div class="mt-2 text-start">
                                <a :href="'/admin/reports/' + selectedId + '/generate-decision'" 
                                   target="_blank" 
                                   class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                    <span>توليد ومعاينة نص القرار الرسمي والـ PDF</span>
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
                            <input type="text" name="decision_no" class="form-control form-control-sm text-start" placeholder="مثال: 45 / ل.م" value="" required>
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
                            ملف قرار تعادل الماجستير الخارجي (PDF) <span class="text-danger">*</span> :
                        </label>
                        <input type="file" name="decision_file" class="form-control form-control-sm text-start" accept=".pdf,image/*" required>
                    </div>

                    <hr class="my-2.5 text-muted opacity-25">

                    {{-- قسم قرار الأهلية (خاص بالماجستير الخارجي النظري) --}}
                    <div class="p-2.5 bg-light rounded border mb-2.5 text-start">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold small mb-0 text-primary">
                                <i class="fa-solid fa-certificate me-1 text-warning"></i> قرار أهلية التدريس (للمسار النظري) :
                            </label>
                            <span class="badge bg-secondary-subtle text-secondary fs-9">اختياري</span>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fs-9 fw-bold mb-1">رقم قرار الأهلية :</label>
                                <input type="text" name="eligibility_decision_no" class="form-control form-control-sm text-start" placeholder="مثال: 89">
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-9 fw-bold mb-1">تاريخ ثبوت الأهلية :</label>
                                <input type="date" name="eligibility_decision_date" class="form-control form-control-sm text-start">
                            </div>
                        </div>

                        <div>
                            <label class="form-label fs-9 fw-bold mb-1">ملف وثيقة الأهلية (PDF) :</label>
                            <input type="file" name="eligibility_file" class="form-control form-control-sm text-start" accept=".pdf,image/*">
                        </div>
                    </div>

                    {{-- ملاحظات --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small d-block text-start mb-1" style="color: var(--imperial-navy);">ملاحظات إضافية :</label>
                        <textarea name="notes" class="form-control form-control-sm text-start" rows="2" placeholder="أدخل أي ملاحظات رسمية خاصة بقرار الماجستير الخارجي..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-solid-navy btn-sm w-100 fw-bold py-2 shadow-2xs d-flex align-items-center justify-content-center gap-1.5">
                        <i class="fa-solid fa-file-circle-check text-warning"></i>
                        <span>حفظ وإصدار القرار وإشعار الجامعة فوراً</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: جدول سجل قرارات تعادل الماجستير الخارجي الصادرة
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-top: 3.5px solid var(--imperial-navy) !important; border-radius: 4px;">
            <!-- Header with Search -->
            <div class="card-header py-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0 fs-6 fw-bold d-flex align-items-center gap-2" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-clipboard-list" style="color: var(--heritage-gold);"></i>
                    <span>سجل قرارات تعادل الماجستير الخارجي الصادرة</span>
                    <span class="badge bg-secondary rounded-pill fs-8">{{ $issuedDecisions->count() }}</span>
                </h5>

                <!-- Search form -->
                <form action="{{ route('admin.foreign_master_decisions.index') }}" method="GET" class="d-flex align-items-center gap-1">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث برقم القرار، الاسم، الجامعة..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-solid-navy btn-sm px-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('admin.foreign_master_decisions.index') }}" class="btn btn-outline-secondary btn-sm" title="إلغاء البحث">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card-body p-0 bg-white">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light fw-bold" style="color: var(--imperial-navy);">
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 14%;">رقم المعاملة</th>
                                <th style="width: 22%;">اسم المرشح والجامعة</th>
                                <th style="width: 14%;">نوع المسار</th>
                                <th style="width: 16%;">رقم وتاريخ القرار</th>
                                <th style="width: 15%;">قرار الأهلية</th>
                                <th style="width: 15%;">تحميل ومعاينة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $idx => $dec)
                                @php
                                    $app = $dec->application;
                                    $cand = optional($app)->candidate;
                                    $uni = optional($app)->workUniversity;
                                    $isTheoretical = str_contains($app->request_type ?? '', 'نظري') || $dec->eligibility_decision_no;
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">{{ $app->application_no ?? '---' }}</span>
                                    </td>
                                    <td class="text-start">
                                        <strong class="text-dark">{{ $cand->full_name ?? '---' }}</strong>
                                        <div class="fs-9 text-muted">{{ $uni->name ?? 'الجامعة الخاصة' }}</div>
                                    </td>
                                    <td>
                                        @if($isTheoretical)
                                            <span class="badge bg-primary-subtle text-primary border border-primary fs-9">ماجستير خارجي (نظري)</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-9">ماجستير خارجي (تطبيقي)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ $dec->decision_no ?? '---' }}</strong>
                                        <div class="fs-9 text-muted">{{ $dec->decision_date ? format_sys_date($dec->decision_date) : '---' }}</div>
                                    </td>
                                    <td>
                                        @if($dec->eligibility_decision_no)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info fs-9">
                                                رقم: {{ $dec->eligibility_decision_no }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-9">غير مطلوب</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if($dec->file_path)
                                                <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" class="btn btn-xs btn-outline-danger" title="عرض وتحميل نسخة القرار الرسمية">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </a>
                                            @endif
                                            @if($app)
                                                <a href="{{ route('admin.reports.generate_decision', $app->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="معاينة القرار الرسمي المولّد">
                                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                </a>
                                                <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-xs btn-outline-secondary" title="عرض تفاصيل الطلب">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted py-4">
                                        <i class="fa-solid fa-folder-open fs-3 d-block mb-2 text-secondary opacity-50"></i>
                                        لا توجد قرارات تعادل ماجستير خارجي مسجلة أو مطابقة للبحث حالياً.
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

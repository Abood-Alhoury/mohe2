@extends('layouts.admin')

@section('title', 'إصدار قرارات تعادل الدكتوراه الخارجية غير السورية والأهلية - وزارة التعليم العالي')

@section('content')

<!-- NAV TABS FOR DECISION TYPES -->
@include('admin.decisions._nav_tabs', ['active' => 'foreign_doctorate'])

<div class="row g-3" dir="rtl">

    {{-- ============================================================
         العمود الأيمن: نموذج إصدار قرارات تعادل الدكتوراه الخارجية والأهلية
    ============================================================ --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100" style="border-top: 3.5px solid var(--heritage-gold) !important; border-radius: 4px;">
            <!-- Header -->
            <div class="card-header py-2.5 px-3 text-white" style="background-color: var(--imperial-navy) !important;">
                <h5 class="mb-0 fs-6 fw-bold text-white d-flex align-items-center gap-2 text-start" dir="rtl">
                    <i class="fa-solid fa-file-signature" style="color: var(--heritage-gold-light);"></i>
                    <span>إصدار ورصد قرارات الدكتوراه الخارجية والأهلية</span>
                </h5>
            </div>
            <div class="card-body p-3.5 bg-white" dir="rtl">
                <form action="{{ route('admin.foreign_doctorate_decisions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- اختر طلب الدكتوراه الخارجية --}}
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
                            اختر طلب تعادل الدكتوراه الخارجية الموافق عليه :
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
                                            <i class="fa-solid fa-building-columns" style="color: var(--heritage-gold); font-size: 0.75rem;"></i>
                                            <span x-text="item.university"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="filteredItems.length === 0" class="p-3 text-center text-muted fs-8">
                                    لا توجد طلبات دكتوراه خارجية بانتظار إصدار القرار مطابقة للبحث
                                </div>
                            </div>
                        </div>

                        <div class="form-text fs-9 text-muted text-start mt-1">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> تظهر هنا طلبات الدكتوراه الخارجية التي اجتازت تقييم الإنتاج العلمي والمقابلة بنجاح.
                        </div>
                    </div>

                    {{-- 1. بيانات قرار الأهلية --}}
                    <div class="p-2.5 rounded mb-3 border text-start" style="background-color: #F8FAFC; border-color: #E2E8F0 !important;">
                        <span class="badge bg-navy text-gold mb-2 d-inline-flex align-items-center gap-1 py-1 px-2">
                            <i class="fa-solid fa-user-check"></i> مستند 1: قرار الأهلية العلمية
                        </span>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">رقم قرار الأهلية</label>
                                <input type="text" name="eligibility_decision_no" class="form-control form-control-sm text-start" placeholder="مثال: 175/أ">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">تاريخ الأهلية</label>
                                <input type="date" name="eligibility_decision_date" class="form-control form-control-sm text-start" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1">ملف قرار الأهلية (PDF)</label>
                                <input type="file" name="eligibility_decision_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>

                    {{-- 2. بيانات قرار التعادل الرسمي --}}
                    <div class="p-2.5 rounded mb-3 border text-start" style="background-color: #FAF6EE; border-color: #EBDCB9 !important;">
                        <span class="badge mb-2 d-inline-flex align-items-center gap-1 py-1 px-2" style="background-color: var(--heritage-gold); color: #000;">
                            <i class="fa-solid fa-stamp"></i> مستند 2: قرار تعادل الدكتوراه غير السورية الرسمي <span class="text-danger">*</span>
                        </span>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">رقم قرار التعادل <span class="text-danger">*</span></label>
                                <input type="text" name="decision_no" class="form-control form-control-sm text-start" placeholder="مثال: 320/ل.م" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">تاريخ القرار <span class="text-danger">*</span></label>
                                <input type="date" name="decision_date" class="form-control form-control-sm text-start" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1">ملف قرار التعادل المعتمد (PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="decision_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold mb-1">ملاحظات إضافية</label>
                        <textarea name="notes" class="form-control form-control-sm text-start" rows="2" placeholder="ملاحظات حول قرار تعادل الدكتوراه الخارجية..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-solid-navy w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-xs">
                        <i class="fa-solid fa-paper-plane" style="color: var(--heritage-gold-light);"></i>
                        <span>تثبيت القرارات وإشعار الجامعة فوراً</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         العمود الأيسر: قوائم الطلبات الجاهزة وسجل القرارات الصادرة
    ============================================================ --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        
        {{-- الطلبات الجاهزة لإصدار القرارات --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 4px;">
            <div class="card-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold fs-7 text-navy">
                    <i class="fa-solid fa-clock-rotate-left text-gold me-1"></i> طلبات الدكتوراه الخارجية الجاهزة لإصدار القرار ({{ $approvedApps->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center fs-7">
                        <thead class="table-light fs-8">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم المرشح</th>
                                <th>الجامعة الطالبة</th>
                                <th>أطروحة الدكتوراه</th>
                                <th>التوليد والطباعة الرسمية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvedApps as $app)
                                @php
                                    $phdEd = $app->educations->first(function($e) {
                                        return optional($e->level)->name && str_contains(optional($e->level)->name, 'دكتوراه');
                                    });
                                @endphp
                                <tr>
                                    <td class="fw-bold font-monospace">{{ $app->application_no }}</td>
                                    <td class="text-start fw-bold text-navy">{{ optional($app->candidate)->full_name }}</td>
                                    <td>{{ optional($app->workUniversity)->name }}</td>
                                    <td class="text-start" style="max-width: 200px;">
                                        <div class="text-truncate" title="{{ optional($phdEd)->thesis_title }}">
                                            {{ optional($phdEd)->thesis_title ?? '---' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.reports.generate_decision', [$app->id, 'type' => 'eligibility']) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="توليد قرار الأهلية للدكتوراه">
                                                <i class="fa-solid fa-user-check"></i> الأهلية
                                            </a>
                                            <a href="{{ route('admin.reports.generate_decision', [$app->id, 'type' => 'equivalence']) }}" target="_blank" class="btn btn-xs btn-warning text-dark fw-bold" title="توليد قرار تعادل الدكتوراه غير السورية الرسمي">
                                                <i class="fa-solid fa-stamp"></i> قرار التعادل
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        لا توجد طلبات دكتوراه خارجية بحالة (بانتظار إصدار القرار) حالياً.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- سجل القرارات الصادرة للدكتوراه الخارجية --}}
        <div class="card border-0 shadow-sm" style="border-radius: 4px;">
            <div class="card-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold fs-7 text-navy">
                    <i class="fa-solid fa-list-check text-gold me-1"></i> سجل قرارات تعادل الدكتوراه الخارجية الصادرة ({{ $issuedDecisions->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center fs-7">
                        <thead class="table-light fs-8">
                            <tr>
                                <th>رقم القرار</th>
                                <th>المرشح</th>
                                <th>الجامعة</th>
                                <th>قرار الأهلية</th>
                                <th>تاريخ الصدور</th>
                                <th>الوثائق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issuedDecisions as $dec)
                                <tr>
                                    <td class="fw-bold text-success">{{ $dec->decision_no }}</td>
                                    <td class="text-start fw-bold text-navy">{{ optional(optional($dec->application)->candidate)->full_name }}</td>
                                    <td>{{ optional(optional($dec->application)->workUniversity)->name }}</td>
                                    <td>
                                        @if($dec->eligibility_decision_no)
                                            <span class="badge bg-light text-dark border">{{ $dec->eligibility_decision_no }}</span>
                                        @else
                                            <span class="text-muted small">---</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $dec->decision_date ? date('Y/m/d', strtotime($dec->decision_date)) : '' }}</td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            @if($dec->file_path)
                                                <a href="{{ asset('storage/' . $dec->file_path) }}" target="_blank" class="btn btn-xs btn-outline-danger" title="عرض قرار التعادل الرسمي">
                                                    <i class="fa-solid fa-file-pdf"></i> القرار
                                                </a>
                                            @endif
                                            @if($dec->eligibility_file_path)
                                                <a href="{{ asset('storage/' . $dec->eligibility_file_path) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="عرض قرار الأهلية">
                                                    <i class="fa-solid fa-file-pdf"></i> الأهلية
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        لم يتم إصدار أي قرارات تعادل للدكتوراه الخارجية حتى الآن.
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

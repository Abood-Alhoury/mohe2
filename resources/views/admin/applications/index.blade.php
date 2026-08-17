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

        <!-- 2. CENTER: INSTANT SEARCH BOX (BY CANDIDATE NAME / APP NO / FACULTY) -->
        <div class="flex-grow-1 mx-md-3" style="max-width: 420px;">
            <form action="{{ route('admin.applications.index') }}" method="GET" class="position-relative m-0">
                @if($statusFilter) <input type="hidden" name="status" value="{{ $statusFilter }}"> @endif
                @if($universityFilter) <input type="hidden" name="university_id" value="{{ $universityFilter }}"> @endif
                
                <div class="input-group input-group-sm shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--outline-variant);">
                    <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                        <i class="fa-solid fa-magnifying-glass" style="color: var(--heritage-gold);"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $searchQuery ?? '' }}" 
                           class="form-control border-0 bg-white shadow-none ps-1" 
                           placeholder="البحث باسم المتقدم أو رقم المعاملة أو الكلية..." 
                           style="font-size: 0.88rem;">
                    @if(!empty($searchQuery))
                        <a href="{{ route('admin.applications.index', array_filter(['status' => $statusFilter, 'university_id' => $universityFilter])) }}" 
                           class="input-group-text bg-white border-0 text-muted px-2 text-decoration-none" title="مسح البحث">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-gold-cta px-3 fw-bold border-0">بحث</button>
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
            <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy btn-sm px-3 fw-bold">
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
        <form action="{{ route('admin.applications.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">نوع المعاملة :</label>
                <select name="request_type" class="form-select" onchange="this.form.submit()">
                    <option value="">-- كافة أنواع الطلبات --</option>
                    @foreach(['ماجستير سوري', 'دكتوراه سوري', 'ماجستير خارجي', 'دكتوراه خارجي', 'ماجستير تطبيقي', 'عضو هيئة تدريسية', 'باحث في مراكز البحوث'] as $rt)
                        <option value="{{ $rt }}" {{ request('request_type') == $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">تصفية حسب حالة الطلب :</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- كافة الحالات --</option>
                    @foreach(($filterStatusesList ?? $statusesList) as $st)
                        <option value="{{ $st }}" {{ $statusFilter == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" style="color: var(--imperial-navy);">تصفية حسب الجامعة :</label>
                <select name="university_id" class="form-select" onchange="this.form.submit()">
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

    <!-- DATA TABLE -->
    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th style="width: 135px;" class="text-center">معرفات الطلب</th>
                    <th>نوع الطلب</th>
                    <th>الجامعة</th>
                    <th>اسم المرشح</th>
                    <th>الكلية</th>
                    <th>المؤهل العلمي</th>
                    <th class="text-center" style="width: 140px;">وضع الطلب</th>
                    <th class="text-center" style="width: 100px;">القرارات</th>
                    <th class="text-center" style="width: 130px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                @php
                    // Requirement 2: المؤهل العلمي هو آخر مؤهل أدخله المستخدم في طلبه
                    $lastEducation = $app->educations->last();
                    $isForbiddenStatus = in_array($app->status, ['بانتظار الوثائق', 'مرفوض', 'معلق']);
                    
                    $reqType = $app->request_type ?? '';
                    $isFacultyPermission = str_contains($reqType, 'سماح') || str_contains($reqType, 'هيئة تدريسية');
                    $isApplied = str_contains($reqType, 'تطبيقي');
                    $isResearchCenter = str_contains($reqType, 'بحوث') || str_contains($reqType, 'باحث');
                    $isForeignDoctorate = str_contains($reqType, 'دكتوراه خارجي') || str_contains($reqType, 'دكتورة خارجي') || str_contains($reqType, 'دكتوراه غير سورية');
                    $isSingleDecisionType = $isFacultyPermission || $isApplied || $isResearchCenter;

                    if ($isSingleDecisionType) {
                        $rowStatuses = ['تحت التدقيق الأولي', 'بانتظار الوثائق', 'بانتظار إصدار القرار', 'مرفوض'];
                    } elseif ($isForeignDoctorate) {
                        $rowStatuses = ['تحت التدقيق الأولي', 'بانتظار الوثائق', 'لجنة عامة', 'بانتظار لجنة إنتاج علمي', 'بانتظار المقابلة', 'بانتظار إصدار القرار', 'مرفوض'];
                    } else {
                        // Standard types: ماجستير سوري، دكتوراه سوري، ماجستير خارجي (NO Scientific Production committee)
                        $rowStatuses = ['تحت التدقيق الأولي', 'بانتظار الوثائق', 'لجنة عامة', 'بانتظار المقابلة', 'بانتظار إصدار القرار', 'مرفوض'];
                    }
                @endphp
                <tr>
                    <!-- 1. معرفات الطلب والمرشح (بطاقة مدمجة مختصرة الارتفاع) -->
                    <td class="text-center align-middle py-1 px-1">
                        @php
                            $candidateTotalCount = optional($app->candidate)->applications ? optional($app->candidate)->applications->where('status', '!=', 'مسودة')->count() : 1;
                        @endphp
                        <div class="d-flex flex-column gap-1 align-items-center justify-content-center p-1 rounded shadow-2xs mx-auto" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; max-width: 140px;">
                            <!-- Top Horizontal Row: Candidate ID - Total Applications Count - App ID -->
                            <div class="d-flex align-items-center justify-content-between w-100 gap-1">
                                <span class="badge rounded-1" style="background-color: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-size: 0.68rem; font-weight: 600; padding: 2px 4px;" title="معرّف المرشح: {{ $app->candidate_id ?? optional($app->candidate)->id }}">
                                    <i class="fa-solid fa-user me-0.5" style="color: var(--heritage-gold);"></i> {{ $app->candidate_id ?? optional($app->candidate)->id }}
                                </span>
                                <span class="badge rounded-1" style="background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-size: 0.68rem; font-weight: 700; padding: 2px 4px;" title="إجمالي طلبات المرشح: {{ $candidateTotalCount }}">
                                    <i class="fa-solid fa-layer-group me-0.5"></i> {{ $candidateTotalCount }}
                                </span>
                                <span class="badge rounded-1" style="background-color: var(--imperial-navy); color: #FFFFFF; font-size: 0.70rem; font-weight: 700; padding: 2px 4px;" title="معرّف الطلب في النظام (Application ID)">
                                    ID: {{ $app->id }}
                                </span>
                            </div>

                            <!-- Application Official Code (Golden Heritage Box) -->
                            <div class="fw-bold font-monospace text-center w-100 rounded py-0.5 px-1" style="background-color: #FAF6EE; color: #8A651E; border: 1px dashed #D9C394; font-size: 0.78rem; letter-spacing: 0.2px;" title="رقم المعاملة / الطلب الرسمي">
                                <i class="fa-solid fa-barcode me-0.5 opacity-75" style="color: var(--heritage-gold);"></i>{{ $app->application_no ?? ('TR-' . $app->id) }}
                            </div>
                        </div>
                    </td>

                    <!-- 2. Request Type -->
                    <td>
                        <span class="badge-academic-tag" style="white-space: nowrap;">{{ $app->request_type ?? 'تعادل جديد' }}</span>
                    </td>

                    <!-- 3. University -->
                    <td class="fw-bold" style="color: var(--imperial-navy); font-size: 0.84rem;">{{ $app->workUniversity->name ?? 'غير محددة' }}</td>

                    <!-- 4. Candidate Name (Guaranteed Single Line) -->
                    <td style="white-space: nowrap;">
                        <span class="fw-bold text-dark" style="font-size: 0.84rem; white-space: nowrap;">{{ $app->candidate->full_name ?? 'غ/م' }}</span>
                    </td>

                    <!-- 5. Faculty / Branch -->
                    <td style="font-size: 0.82rem;">{{ $app->work_faculty ?? 'إدارة جامعة' }}</td>

                    <!-- 6. Degree Level -->
                    <td class="text-secondary fw-semibold" style="font-size: 0.82rem;">{{ optional(optional($lastEducation)->level)->name ?? 'إجازة جامعية' }}</td>

                    <!-- 7. Application Status -->
                    <td class="text-center align-middle">
                        @if($app->status === 'تم الصدور')
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2.5 py-1.5 fs-7">
                                <i class="fa-solid fa-circle-check me-1 text-success"></i> تم الصدور
                            </span>
                        @else
                            <!-- Quick Status Update Form -->
                            <form action="{{ route('admin.applications.update_status', $app->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm fw-bold text-center" style="font-size: 0.80rem; min-width: 130px; border-color: var(--outline-variant);">
                                    @foreach($rowStatuses as $st)
                                        <option value="{{ $st }}" {{ $app->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </td>

                    <!-- 8. Decision Attachment & Decision Generation (PURE ICONS ONLY) -->
                    <td class="text-center align-middle">
                        @php
                            $canGenerateDecision = in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار', 'تم الصدور']);
                            $canGenerateEquivalenceNonSingle = !$isSingleDecisionType && !in_array($app->status, ['مسودة', 'مرفوض', 'بانتظار الوثائق']);
                            $canGenerateEligibility = !$isSingleDecisionType && $canGenerateDecision;
                            $canAttachDecision = in_array($app->status, ['بانتظار إصدار القرار', 'بانتظار صدور القرار']);
                        @endphp
                        <div class="d-flex align-items-center justify-content-center gap-2 mx-auto">
                            @if($canAttachDecision)
                                <button type="button" class="btn btn-sm btn-solid-navy p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#decisionModal{{ $app->id }}" title="إرفاق القرار">
                                    <i class="fa-solid fa-cloud-arrow-up fs-7" style="color: var(--heritage-gold-light);"></i>
                                </button>
                            @elseif($app->status === 'تم الصدور')
                                <span class="d-inline-flex align-items-center justify-content-center rounded bg-success-subtle border border-success-subtle text-success" style="width: 32px; height: 32px;" title="تم رصد القرار">
                                    <i class="fa-solid fa-stamp fs-7"></i>
                                </span>
                            @else
                                <button type="button" class="btn btn-sm btn-secondary opacity-40 p-0 d-inline-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px;" disabled title="إرفاق القرار متاح بحالة (بانتظار إصدار القرار)">
                                    <i class="fa-solid fa-lock fs-7"></i>
                                </button>
                            @endif

                            @if($isFacultyPermission)
                                @if($canGenerateDecision)
                                    <a href="{{ route('admin.reports.generate_decision', ['id' => $app->id, 'type' => 'equivalence']) }}" class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" style="width: 32px; height: 32px; border: 1px solid #10b981; color: #047857; background-color: #ecfdf5;" title="توليد قرار السماح بالتدريس">
                                        <i class="fa-solid fa-chalkboard-user fs-7" style="color: #047857;"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary opacity-40 p-0 d-inline-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px;" disabled title="توليد قرار السماح متاح بحالة (بانتظار إصدار القرار)">
                                        <i class="fa-solid fa-chalkboard-user fs-7 text-muted"></i>
                                    </button>
                                @endif
                            @elseif($isApplied)
                                @if($canGenerateDecision)
                                    <a href="{{ route('admin.reports.generate_decision', ['id' => $app->id, 'type' => 'equivalence']) }}" class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" style="width: 32px; height: 32px; border: 1px solid #3b82f6; color: #1d4ed8; background-color: #eff6ff;" title="توليد قرار تكليف ماجستير تطبيقي">
                                        <i class="fa-solid fa-briefcase fs-7" style="color: #1d4ed8;"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary opacity-40 p-0 d-inline-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px;" disabled title="توليد قرار التكليف متاح بحالة (بانتظار إصدار القرار)">
                                        <i class="fa-solid fa-briefcase fs-7 text-muted"></i>
                                    </button>
                                @endif
                            @elseif($isResearchCenter)
                                @if($canGenerateDecision)
                                    <a href="{{ route('admin.reports.generate_decision', ['id' => $app->id, 'type' => 'equivalence']) }}" class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" style="width: 32px; height: 32px; border: 1px solid #0284c7; color: #0369a1; background-color: #f0f9ff;" title="توليد قرار باحث مراكز البحوث">
                                        <i class="fa-solid fa-microscope fs-7" style="color: #0369a1;"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary opacity-40 p-0 d-inline-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px;" disabled title="توليد قرار باحث مراكز البحوث متاح بحالة (بانتظار إصدار القرار)">
                                        <i class="fa-solid fa-microscope fs-7 text-muted"></i>
                                    </button>
                                @endif
                            @else
                                @if($canGenerateEquivalenceNonSingle)
                                    <a href="{{ route('admin.reports.generate_decision', ['id' => $app->id, 'type' => 'equivalence']) }}" class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" style="width: 32px; height: 32px; border: 1px solid #93c5fd; color: #1d4ed8; background-color: #eff6ff;" title="توليد قرار التعادل (التكليف)">
                                        <i class="fa-solid fa-file-signature fs-7" style="color: #1d4ed8;"></i>
                                    </a>
                                @endif

                                @if($canGenerateEligibility)
                                    <a href="{{ route('admin.reports.generate_decision', ['id' => $app->id, 'type' => 'eligibility']) }}" class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" style="width: 32px; height: 32px; border: 1px solid #c084fc; color: #7e22ce; background-color: #f3e8ff;" title="توليد قرار الأهلية">
                                        <i class="fa-solid fa-award fs-7" style="color: #7e22ce;"></i>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </td>

                    <!-- 9. Actions Column (Combined 3 Icons: Messages, Edit, Mozhakkara/Select) -->
                    <td class="text-center align-middle">
                        <div class="d-flex align-items-center justify-content-center gap-2 mx-auto">
                            <!-- Messages -->
                            <button type="button" class="btn btn-sm btn-outline-navy position-relative p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs" style="width: 33px; height: 33px;" data-bs-toggle="modal" data-bs-target="#messageModal{{ $app->id }}" title="محادثات ورسائل هذا الطلب">
                                <i class="fa-solid fa-comments fs-7"></i>
                                @if($app->messages->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-xs" style="font-size: 0.62rem; padding: 2px 4px; border: 1.5px solid #ffffff;">
                                        {{ $app->messages->count() }}
                                    </span>
                                @endif
                            </button>

                            <!-- Edit -->
                            <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-sm btn-outline-gold p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs" style="width: 33px; height: 33px;" title="تعديل بيانات وملفات الطلب">
                                <i class="fa-solid fa-pen-to-square fs-7"></i>
                            </a>

                            <!-- Select / Mozhakkara -->
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs" style="width: 33px; height: 33px;" title="عرض مذكرة العرض والوثائق (Select)">
                                <i class="fa-solid fa-file-invoice fs-7"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">لا توجد طلبات معادلة تطابق الخيارات المحددة.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
    <div class="card-footer bg-white py-3 border-top">
        {{ $applications->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- MODALS CONTAINER (PLACED OUTSIDE TABLE TO PREVENT FLICKERING/BLINKING BUG) -->
@foreach($applications as $app)

    <!-- Modal 1: Upload Equivalence Decision -->
    <div class="modal fade" id="decisionModal{{ $app->id }}" tabindex="-1" aria-labelledby="decisionModalLabel{{ $app->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                <form action="{{ route('admin.applications.update_status', $app->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="تم الصدور">
                    
                    <div class="modal-header text-white" style="background-color: var(--imperial-navy);">
                        <h5 class="modal-title fs-6 fw-bold" id="decisionModalLabel{{ $app->id }}">
                            <i class="fa-solid fa-stamp me-2" style="color: var(--heritage-gold-light);"></i> إرفاق قرار تعادل صادر للطالب {{ $app->candidate->full_name ?? '' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--imperial-navy);">رقم القرار الوزاري :</label>
                            <input type="text" name="decision_no" class="form-control" value="قرار-{{ $app->application_no }}/{{ date('Y') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--imperial-navy);">رفع صورة / نسخة قرار التعادل (PDF/صورة) :</label>
                            <input type="file" name="decision_file" class="form-control" accept=".pdf,image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--imperial-navy);">ملاحظات القرار :</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات مجلس التعليم العالي"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-gold-cta px-4 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> حفظ وتنزيل القرار للجامعة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Messages & Notifications -->
    <div class="modal fade" id="messageModal{{ $app->id }}" tabindex="-1" aria-labelledby="messageModalLabel{{ $app->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid var(--heritage-gold) !important; border-radius: 4px;">
                <div class="modal-header text-white" style="background-color: var(--imperial-navy);">
                    <h5 class="modal-title fs-6 fw-bold" id="messageModalLabel{{ $app->id }}">
                        <i class="fa-solid fa-comments me-2" style="color: var(--heritage-gold-light);"></i> الرسائل والإشعارات مع الجامعة - طلب رقم {{ $app->application_no }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Chat History Box -->
                    <div class="messages-chat-box p-3 bg-light rounded border mb-4" style="max-height: 300px; overflow-y: auto;">
                        @forelse($app->messages as $msg)
                            <div class="d-flex mb-3 {{ $msg->sender_id == (Auth::id() ?? 1) ? 'justify-content-start' : 'justify-content-end' }}">
                                <div class="p-3 rounded-3 shadow-sm {{ $msg->sender_id == (Auth::id() ?? 1) ? 'text-white' : 'bg-white text-dark border' }}" 
                                     style="max-width: 75%; {{ $msg->sender_id == (Auth::id() ?? 1) ? 'background-color: var(--imperial-navy) !important;' : '' }}">
                                    <div class="fw-bold fs-7 mb-1" style="{{ $msg->sender_id == (Auth::id() ?? 1) ? 'color: var(--heritage-gold-light);' : 'color: var(--imperial-navy);' }}">
                                        {{ $msg->sender->name ?? 'مدير التعادل' }}
                                    </div>
                                    <div>{{ $msg->message }}</div>
                                    <div class="fs-8 mt-1 text-end opacity-75">{{ $msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '' }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-3 m-0">لا توجد رسائل سابقة بخصوص هذا الطلب.</p>
                        @endforelse
                    </div>

                    <!-- Send Message Form -->
                    <form action="{{ route('admin.applications.send_message', $app->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--imperial-navy);">إرسال رسالة/إشعار موجه للجامعة بخصوص هذا الطلب :</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="اكتب ملاحظاتك أو طلب الاستكمال هنا..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-gold-cta px-4 py-2"><i class="fa-solid fa-paper-plane me-1"></i> إرسال الرسالة للجامعة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endforeach

@push('scripts')
@if(request()->has('open_message'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var openAppId = "{{ request()->get('open_message') }}";
        var modalEl = document.getElementById('messageModal' + openAppId);
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            // Scroll chat box to bottom
            var chatBox = modalEl.querySelector('.messages-chat-box');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }
    });
</script>
@endif
@endpush

@endsection

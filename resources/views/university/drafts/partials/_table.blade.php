<div class="card-academic-table mb-4" style="border-top: 4px solid var(--heritage-gold) !important;">
    <div class="table-header-slab d-flex flex-wrap align-items-center justify-content-between gap-3 bg-white p-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5" style="color: #C5A059;"></i>
            <h6 class="fw-bold mb-0" style="color: #1A2A44; font-size: 1.05rem;">
                قائمة المسودات غير المكتملة
            </h6>
            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1 fs-8">
                {{ $drafts->total() }} مسودة
            </span>
        </div>
        <p class="text-muted small mb-0">يمكنك الضغط على زر "استكمال الطلب" للعودة إلى نموذج التقديم بكافة البيانات والمرفقات السابقة.</p>
    </div>

    @if($drafts->count() > 0)
    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th style="width: 110px;">رقم المسودة</th>
                    <th>اسم المرشح</th>
                    <th>الرقم الوطني</th>
                    <th>نوع التعادل</th>
                    <th>الكلية والقسم</th>
                    <th>آخر حفظ وتعديل</th>
                    <th class="text-center" style="width: 220px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drafts as $draft)
                @php
                    $candidateName = optional($draft->candidate)->full_name;
                    if (!$candidateName || $candidateName === 'مسودة غير مكتملة') {
                        $candidateName = 'مرشح قيد الإدخال (مسودة)';
                    }
                    $nationalId = optional($draft->candidate)->national_id;
                    if ($nationalId && str_starts_with($nationalId, 'TMP-')) {
                        $nationalId = 'غير محدد بعد';
                    }
                @endphp
                <tr>
                    <td class="fw-bold text-muted">#Draft-{{ $draft->id }}</td>
                    <td>
                        <div class="fw-bold" style="color: #1A2A44; font-size: 0.95rem;">
                            {{ $candidateName }}
                        </div>
                        @if($draft->application_no)
                            <small class="text-muted fs-8">رمز الطلب: {{ $draft->application_no }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-8">
                            {{ $nationalId ?: 'غير محدد بعد' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-8 fw-medium">
                            {{ $draft->request_type ?? 'معاملة تعادل' }}
                        </span>
                    </td>
                    <td class="fs-7 text-muted">
                        {{ $draft->work_faculty ?: 'غير محددة' }}
                        @if($draft->work_department)
                            - {{ $draft->work_department }}
                        @endif
                    </td>
                    <td class="fs-8 text-muted">
                        <div><i class="fa-regular fa-clock me-1 text-warning"></i> {{ $draft->updated_at ? $draft->updated_at->format('d/m/Y H:i') : '-' }}</div>
                        <small class="text-black-50">تم البدء: {{ $draft->created_at ? $draft->created_at->format('d/m/Y') : '-' }}</small>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="{{ route('university.applications.edit', $draft->id) }}" class="btn btn-sm btn-solid-navy px-2.5 py-1.5 fs-8 fw-bold d-inline-flex align-items-center gap-1.5" title="استكمال إدخال المعاملة">
                                <i class="fa-solid fa-pen-to-square"></i>
                               
                            </a>
                            <form action="{{ route('university.applications.delete_draft', $draft->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('هل أنت متأكد تماماً من رغبتك في حذف هذه المسودة وإلغاء المعاملة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1.5 fs-8" title="حذف المسودة نهائياً">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($drafts->hasPages())
    <div class="p-3 border-top bg-white d-flex justify-content-center">
        {{ $drafts->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="text-center py-5 px-3 bg-white">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle rounded-circle mb-3" style="width: 70px; height: 70px;">
            <i class="fa-solid fa-folder-open fs-2 text-warning"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #1A2A44;">لا توجد مسودات محفوظة حالياً</h5>
        <p class="text-muted small mb-4" style="max-width: 450px; margin: 0 auto;">
            @if(!empty($search))
                لم نجد أي مسودة تطابق كلمة البحث ("{{ $search }}"). جرب البحث بكلمة أخرى أو مسح البحث.
            @else
                عند بدء تعبئة أي طلب تعادل واختيار <strong>"حفظ كمسودة ومتابعة لاحقاً"</strong> أو عند مغادرة النموذج، ستُحفظ المعاملة هنا مباشرة لتعود إليها في أي وقت وتكملها.
            @endif
        </p>
        @if(!empty($search))
            <a href="{{ route('university.drafts.index') }}" class="btn btn-outline-navy btn-sm px-3 py-1.5 fw-bold">
                <i class="fa-solid fa-rotate-left me-1"></i> مسح تصفية البحث
            </a>
        @elseif(empty($siteLocked) || !$siteLocked)
            <a href="{{ route('university.apply.options') }}" class="btn btn-gold-cta btn-sm px-4 py-2 fw-bold">
                <i class="fa-solid fa-plus me-1"></i> بدء تقديم طلب جديد الآن
            </a>
        @endif
    </div>
    @endif
</div>


@if($drafts->count() > 0)
    <div class="drafts-bento-grid" id="drafts-cards-container">
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

                // Initial Letter
                $firstChar = mb_substr($candidateName, 0, 1, 'UTF-8');
                if ($candidateName === 'مرشح قيد الإدخال (مسودة)') {
                    $firstChar = 'م';
                }

                // Degree Types
                $reqType = (string)$draft->request_type;
                $reqTypeId = (int)$draft->request_type_id;
                $isDoc = $reqTypeId === 3 || $reqTypeId === 7 || str_contains($reqType, 'دكتور') || str_contains($reqType, 'دكتورة');
                $isMasterTheo = $reqTypeId === 2 || $reqTypeId === 6 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'نظري'));
                $isMasterApp = $reqTypeId === 1 || $reqTypeId === 5 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'تطبيقي'));
                $isFac = $reqTypeId === 4 || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس');

                // Progress calculation
                $progress = 25;
                if (!empty($draft->candidate) && $draft->candidate->full_name && $draft->candidate->full_name !== 'مسودة غير مكتملة') {
                    $progress += 25;
                }
                if (!empty($draft->work_faculty)) {
                    $progress += 20;
                }
                if ($draft->educations && $draft->educations->count() > 0) {
                    $progress += 20;
                }
                $progress = min($progress, 90);

                $searchCorpus = strtolower($candidateName . ' ' . $nationalId . ' ' . $draft->id . ' ' . $draft->application_no . ' ' . $draft->work_faculty . ' ' . $draft->work_department . ' ' . $reqType);
            @endphp

            <div class="bento-draft-card" data-search-text="{{ $searchCorpus }}">
                <!-- Card Header: ID & Degree Badge & Relative Time -->
                <div class="draft-card-top">
                    <div class="d-flex align-items-center gap-2">
                        <span class="draft-code-badge">
                            <i class="fa-solid fa-hashtag"></i> Draft-{{ $draft->id }}
                        </span>
                        @if($draft->application_no)
                            <span class="draft-app-no-badge">{{ $draft->application_no }}</span>
                        @endif
                    </div>
                    <span class="draft-time-pill" title="{{ $draft->updated_at ? $draft->updated_at->format('Y-m-d H:i') : '' }}">
                        <i class="fa-regular fa-clock"></i> {{ $draft->updated_at ? $draft->updated_at->diffForHumans() : 'مؤخراً' }}
                    </span>
                </div>

                <!-- Candidate Info with Squircle Avatar -->
                <div class="draft-candidate-cluster">
                    <div class="candidate-squircle-avatar">
                        <span>{{ $firstChar }}</span>
                    </div>
                    <div class="candidate-details">
                        <h4 class="candidate-name" title="{{ $candidateName }}">{{ $candidateName }}</h4>
                        <div class="candidate-nid">
                            <i class="fa-regular fa-id-card"></i>
                            <span class="font-monospace">{{ $nationalId ?: 'الرقم الوطني غير مدخل' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Degree & Faculty Details -->
                <div class="draft-academic-meta">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="meta-label text-muted" style="font-size: 0.8rem; font-weight: 600;">مسار المعاملة:</span>
                        @if($isDoc)
                            <span class="pill-degree"><i class="fa-solid fa-graduation-cap"></i> {{ $draft->request_type ?? 'دكتوراه داخلي' }}</span>
                        @elseif($isMasterTheo)
                            <span class="pill-degree pill-sky"><i class="fa-solid fa-book-open"></i> {{ $draft->request_type }}</span>
                        @elseif($isMasterApp)
                            <span class="pill-degree pill-emerald"><i class="fa-solid fa-briefcase"></i> {{ $draft->request_type }}</span>
                        @elseif($isFac)
                            <span class="pill-degree pill-amber"><i class="fa-solid fa-chalkboard-user"></i> {{ $draft->request_type }}</span>
                        @else
                            <span class="pill-degree"><i class="fa-solid fa-graduation-cap"></i> {{ $draft->request_type ?? 'معاملة تعادل' }}</span>
                        @endif
                    </div>

                    <div class="meta-faculty-box">
                        <i class="fa-solid fa-building-columns"></i>
                        <span class="text-truncate">
                            {{ $draft->work_faculty ?: 'الكلية غير محددة بعد' }}
                            @if($draft->work_department)
                                - {{ $draft->work_department }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Completion Progress Bar -->
                <div class="draft-progress-slab">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="progress-title">نسبة اكتمال البيانات</span>
                        <span class="progress-percent-badge">{{ $progress }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar-glow" style="width: {{ $progress }}%;"></div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="draft-card-actions">
                    <a href="{{ route('university.applications.edit', $draft->id) }}" class="btn-resume-draft">
                        <span>استكمال الطلب</span>
                        <i class="fa-solid fa-play fs-8"></i>
                    </a>

                    <form action="{{ route('university.applications.delete_draft', $draft->id) }}" 
                          method="POST" 
                          class="m-0"
                          onsubmit="return confirm('هل أنت متأكد تماماً من رغبتك في حذف مسودة المعاملة رقم ({{ $draft->id }}) نهائياً؟ لا يمكن التراجع عن هذا الإجراء.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete-draft" title="حذف المسودة نهائياً">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($drafts->hasPages())
        <div class="drafts-pagination-wrap mt-4 d-flex justify-content-center">
            {{ $drafts->links() }}
        </div>
    @endif
@else
    <!-- EMPTY STATE -->
    <div class="empty-drafts-slab">
        <div class="empty-drafts-halo-icon">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <h4 class="empty-title">لا توجد مسودات محفوظة حالياً</h4>
        <p class="empty-subtitle">
            @if(!empty($search))
                لم يتم العثور على أي مسودة مطابقة لكلمة البحث: <strong>"{{ $search }}"</strong>. جرب البحث برقم أو اسم آخر.
            @else
                كافة المعاملات التي تبدأ بإدخالها وتختار <strong>"حفظ كمسودة ومتابعة لاحقاً"</strong> ستظهر هنا في هذه اللوحة لتعود إليها وتستكملها أصولاً في أي وقت.
            @endif
        </p>

        <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
            @if(!empty($search))
                <a href="{{ route('university.drafts.index') }}" class="btn-action-outline">
                    <i class="fa-solid fa-rotate-left"></i> إلغاء البحث وعرض كافة المسودات
                </a>
            @elseif(empty($siteLocked) || !$siteLocked)
                <a href="{{ route('university.apply.options') }}" class="hero-cta-btn">
                    <i class="fa-solid fa-plus-circle"></i> بدء تقديم طلب جديد الآن
                </a>
            @endif
        </div>
    </div>
@endif

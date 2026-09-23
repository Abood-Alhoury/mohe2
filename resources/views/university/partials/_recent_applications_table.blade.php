    <div class="table-responsive">
        <table class="table-academic">
            <thead>
                <tr>
                    <th style="width: 90px;">رقم الطلب</th>
                    <th>اسم المرشح (الطالب)</th>
                    <th>نوع التعادل</th>
                    <th>القسم المرشح له</th>
                    <th>حالة الطلب الحالية</th>
                    <th>تاريخ التقديم</th>
                    <th style="width: 220px;" class="text-center">الإجراءات والخدمات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentApplications as $app)
                <tr>
                    <td class="fw-bold" style="color: #C5A059;">{{ $app->application_no ?? $app->id }}</td>
                    <td class="fw-bold" style="color: #1A2A44;">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                    <td>
                        @if($app->request_type == 'تحويل قرار المعادلة')
                            <span class="badge bg-primary-subtle text-primary border border-primary fw-bold px-2.5 py-1 label-sm">
                                <i class="fa-solid fa-right-left me-1"></i> تحويل قرار معادلة
                            </span>
                        @elseif($app->request_type == 'إضافة مقررات دراسية')
                            <span class="badge bg-success-subtle text-success border border-success fw-bold px-2.5 py-1 label-sm">
                                <i class="fa-solid fa-book-medical me-1"></i> إضافة مقررات دراسية
                            </span>
                        @else
                            <span class="badge bg-light text-dark border fw-medium px-2 py-1 label-sm">{{ $app->request_type ?? 'تعادل' }}</span>
                        @endif
                    </td>
                    <td class="fs-7 text-muted">{{ $app->work_faculty ?? '' }} - {{ $app->work_department ?? 'غ/م' }}</td>
                    <td>
                        @if($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                            <span class="badge-status badge-study">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> تحت التدقيق الأولي
                            </span>
                        @elseif($app->status == 'بانتظار الوثائق')
                            <span class="badge-status badge-paper">
                                <i class="fa-solid fa-file-circle-exclamation me-1"></i> بانتظار الوثائق
                            </span>
                        @elseif($app->status == 'لجنة عامة')
                            <span class="badge-status badge-suspended">
                                <i class="fa-solid fa-users me-1"></i> لجنة عامة
                            </span>
                        @elseif($app->status == 'بانتظار لجنة إنتاج علمي')
                            <span class="badge-status badge-suspended" style="background-color: rgba(147, 51, 234, 0.1); color: #9333ea; border-color: rgba(147, 51, 234, 0.2);">
                                <i class="fa-solid fa-flask me-1"></i> بانتظار لجنة إنتاج علمي
                            </span>
                        @elseif($app->status == 'بانتظار المقابلة')
                            <span class="badge-status badge-suspended" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5; border-color: rgba(79, 70, 229, 0.2);">
                                <i class="fa-solid fa-user-tie me-1"></i> بانتظار المقابلة
                            </span>
                        @elseif($app->status == 'تم الصدور' || $app->status == 'موافقة')
                            <span class="badge-status badge-approved">
                                <i class="fa-solid fa-award me-1"></i> تم الصدور
                            </span>
                        @else
                            <span class="badge-status badge-study">{{ $app->status }}</span>
                        @endif
                    </td>
                    <td class="text-muted label-sm">{{ $app->created_at ? $app->created_at->format('d/m/Y') : 'غ/م' }}</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-1.5">
                            {{-- 1. زر العين (معاينة تفاصيل الطلب) - متاح دائماً --}}
                            <a href="{{ route('university.applications.show', $app->id) }}" class="btn btn-sm btn-outline-info px-2 py-1 shadow-sm" title="فتح صفحة مستقلة لمعاينة كافة تفاصيل المعاملة والمؤهلات">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            @if($app->status == 'تم الصدور')
                                {{-- للمعاملات الصادرة رسمياً: زر العين + تنزيل قرار التعادل الصادر + زر المراسلات --}}
                                @if($app->latestDecision)
                                    <a href="{{ asset('storage/' . $app->latestDecision->file_path) }}" target="_blank" class="btn btn-sm btn-gold-cta px-2 py-1 text-decoration-none shadow-sm" title="تحميل وتنزيل قرار التعادل الصادر">
                                        <i class="fa-solid fa-stamp" style="color: var(--imperial-navy);"></i>
                                    </a>
                                @endif

                                {{-- المراسلات والإشعارات --}}
                                <a href="{{ route('university.messages') }}?application_id={{ $app->id }}" class="btn btn-sm btn-outline-navy px-2 py-1 shadow-sm" title="مراسلة الوزارة ومتابعة الملاحظات حول هذا الطلب">
                                    <i class="fa-solid fa-comments"></i>
                                </a>
                            @else
                                {{-- لباقي المعاملات الجارية (تحت التدقيق الأولي، إلخ): زر العين، تنزيل المذكرة، التنبيهات، والمراسلات --}}
                                @if($app->status == 'بانتظار الوثائق')
                                    <a href="{{ route('university.applications.edit', $app->id) }}" class="btn btn-sm btn-warning px-2 py-1 shadow-sm" title="تعديل البيانات واستكمال الوثائق المطلوبة">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif

                                {{-- تحميل تقرير ومذكرة عرض الطلب PDF --}}
                                <a href="{{ route('university.applications.download_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-danger px-2 py-1 text-decoration-none shadow-sm" title="تحميل وطباعة تقرير ومذكرة عرض الطلب (PDF)">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                {{-- المراسلات والإشعارات --}}
                                <a href="{{ route('university.messages') }}?application_id={{ $app->id }}" class="btn btn-sm btn-outline-navy px-2 py-1 shadow-sm" title="مراسلة الوزارة ومتابعة الملاحظات حول هذا الطلب">
                                    <i class="fa-solid fa-comments"></i>
                                </a>
                            @endif
                        </div>

                        <!-- Read-Only Application View Modal -->
                        <div class="modal fade text-start" id="viewAppModal{{ $app->id }}" tabindex="-1" aria-labelledby="viewAppModalLabel{{ $app->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #071526 0%, #152B47 100%); border-bottom: 3px solid var(--heritage-gold);">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm" style="border: 2px solid var(--heritage-gold); width: 42px; height: 42px;">
                                                <i class="fa-solid fa-file-contract fs-5" style="color: var(--imperial-navy);"></i>
                                            </div>
                                            <div>
                                                <h6 class="modal-title fw-bold text-white mb-0" id="viewAppModalLabel{{ $app->id }}">
                                                    معاينة واستعراض كامل بيانات المعاملة (عرض فقط - غير قابل للتعديل)
                                                </h6>
                                                <small class="text-white-50 fs-8">رقم المعاملة: {{ $app->application_no }} | المرشح: {{ optional($app->candidate)->full_name }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 bg-light" style="max-height: 78vh; overflow-y: auto;">
                                        
                                        <!-- Status & Quick PDF Bar -->
                                        <div class="bg-white p-3 rounded shadow-sm border mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-right: 4px solid var(--heritage-gold) !important;">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="fs-7 fw-bold text-dark">حالة الطلب الحالية:</span>
                                                <span class="badge bg-warning text-dark fw-bold fs-7">{{ $app->status }}</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('university.applications.download_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-danger fw-bold">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> مذكرة العرض PDF
                                                </a>
                                                <a href="{{ route('university.applications.download_consolidated_pdf', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-gold fw-bold">
                                                    <i class="fa-solid fa-file-zipper me-1"></i> المرفقات المدمجة PDF
                                                </a>
                                            </div>
                                        </div>

                                        <!-- 1. Personal Candidate Information -->
                                        <div class="bg-white p-3.5 rounded shadow-sm border mb-4">
                                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
                                                <i class="fa-solid fa-user-graduate me-1" style="color: var(--heritage-gold);"></i> 1. البيانات الشخصية للمرشح
                                            </h6>
                                            <div class="row g-3 fs-7">
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">اسم المرشح:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->full_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">اسم الأب:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->father_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">اسم الأم:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->mother_name ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">الرقم الوطني:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->national_id ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">تاريخ الميلاد:</span>
                                                    <span class="fw-bold text-dark">{{ optional($app->candidate)->dob ?? 'غ/م' }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block fs-8">الجنسية:</span>
                                                    <span class="fw-bold text-dark">{{ optional(optional($app->candidate)->nationality)->name ?? 'سوري' }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted d-block fs-8">الكلية والفرع المرشح له:</span>
                                                    <span class="fw-bold text-dark">{{ $app->work_faculty }} - {{ $app->work_department }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. Academic Degrees Summary -->
                                        <div class="bg-white p-3.5 rounded shadow-sm border mb-4">
                                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--imperial-navy);">
                                                <i class="fa-solid fa-graduation-cap me-1" style="color: var(--heritage-gold);"></i> 2. المؤهلات الأكاديمية المدخلة بالطلب
                                            </h6>
                                            <div class="d-flex flex-column gap-3">
                                                @forelse($app->educations as $ed)
                                                    <div class="p-3 rounded border bg-light">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="badge bg-navy text-white fw-bold fs-8" style="background-color: var(--imperial-navy);">
                                                                {{ optional($ed->level)->name ?? 'مؤهل أكاديمي' }}
                                                            </span>
                                                            <span class="text-muted fs-8">الدولة: {{ optional($ed->country)->name ?? 'غ/م' }}</span>
                                                        </div>
                                                        <div class="row g-2 fs-8 text-dark">
                                                            <div class="col-md-4"><strong>الجامعة المانحة:</strong> {{ optional($ed->university)->name ?? ($ed->section_name ?? 'غ/م') }}</div>
                                                            <div class="col-md-4"><strong>الاختصاص العام:</strong> {{ $ed->general_specialization ?? 'غ/م' }}</div>
                                                            <div class="col-md-4"><strong>الاختصاص الدقيق:</strong> {{ $ed->exact_specialization ?? 'غ/م' }}</div>
                                                            @if($ed->thesis_title)
                                                                <div class="col-12 mt-1"><strong>عنوان الأطروحة:</strong> {{ $ed->thesis_title }}</div>
                                                            @endif
                                                        </div>

                                                        <!-- Attachments List -->
                                                        @if($ed->attachments && $ed->attachments->count() > 0)
                                                            <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">
                                                                <span class="fs-8 fw-bold text-muted me-1"><i class="fa-solid fa-paperclip"></i> المرفقات والوثائق:</span>
                                                                @foreach($ed->attachments as $att)
                                                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-2 fs-8">
                                                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> {{ $att->notes ?: (optional($att->attachmentType)->name ?? 'مرفق') }}
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="text-muted fs-7 mb-0">لا توجد مؤهلات مسجلة.</p>
                                                @endforelse
                                            </div>
                                        </div>

                                    </div>
                                    <div class="modal-footer bg-white">
                                        <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">إغلاق النافذة</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-magnifying-glass d-block fs-2 mb-3" style="color: var(--heritage-gold);"></i>
                        @php $currentSearch = !empty($search) ? $search : request('search'); @endphp
                        @if(!empty($currentSearch))
                            <h6 class="fw-bold text-dark mb-1">لم يتم العثور على أي نتائج مطابقة</h6>
                            <p class="text-muted small mb-0">لا توجد طلبات تطابق عبارة البحث: <strong>"{{ $currentSearch }}"</strong>. يرجى التأكد من صحة الاسم أو الرقم وإعادة المحاولة.</p>
                        @else
                            <h6 class="fw-bold text-dark mb-1">لا توجد طلبات مقدمة حالياً</h6>
                            <p class="text-muted small mb-0">لم تقم الكلية أو الجامعة بتقديم أي طلبات لمجلس التعليم العالي بعد.</p>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

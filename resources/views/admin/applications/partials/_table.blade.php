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
                    $isForMaster = str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري');
                    $isFacultyPermission = !$isForMaster && (str_contains($reqType, 'سماح') || str_contains($reqType, 'هيئة تدريسية'));
                    $isApplied = str_contains($reqType, 'تطبيقي');
                    $isForeignDoctorate = str_contains($reqType, 'دكتوراه خارجي') || str_contains($reqType, 'دكتورة خارجي') || str_contains($reqType, 'دكتوراه غير سورية');
                    $isSingleDecisionType = $isFacultyPermission || ($isApplied && !$isForMaster);

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
                                <span class="badge rounded-1" style="background-color: var(--heritage-gold-light); color: #334155; font-size: 0.70rem; font-weight: 700; padding: 2px 4px;" title="معرّف الطلب في النظام (Application ID)">
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

                            // Calculate appropriate decision page route based on application type
                            $isForMaster = str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري');
                            $isForApplied = $isForMaster && (str_contains($reqType, 'تطبيقي') || !str_contains($reqType, 'نظري'));
                            $isForTheo = $isForMaster && !$isForApplied;
                            $isDoc = !$isForMaster && (str_contains($reqType, 'دكتوراه') || str_contains($reqType, 'دكتورة'));
                            $isFac = !$isForMaster && !$isDoc && (str_contains($reqType, 'سماح') || str_contains($reqType, 'هيئة تدريسية') || str_contains($reqType, 'بحوث') || str_contains($reqType, 'باحث'));
                            $isAppMaster = !$isForMaster && !$isDoc && !$isFac && str_contains($reqType, 'تطبيقي');

                            if ($isForApplied) {
                                $targetDecisionUrl = route('admin.foreign_master_applied_decisions.index', ['app_id' => $app->id]);
                            } elseif ($isForTheo) {
                                $targetDecisionUrl = route('admin.foreign_master_theoretical_decisions.index', ['app_id' => $app->id]);
                            } elseif ($isFac) {
                                $targetDecisionUrl = route('admin.faculty_decisions.index', ['app_id' => $app->id]);
                            } elseif ($isDoc) {
                                $targetDecisionUrl = route('admin.doctorate_decisions.index', ['app_id' => $app->id]);
                            } elseif ($isAppMaster) {
                                $targetDecisionUrl = route('admin.applied_decisions.index', ['app_id' => $app->id]);
                            } else {
                                $targetDecisionUrl = route('admin.decisions.index', ['app_id' => $app->id]);
                            }
                        @endphp
                        <div class="d-flex align-items-center justify-content-center gap-2 mx-auto">
                            @if($app->status === 'تم الصدور')
                                <a href="{{ $targetDecisionUrl }}?search={{ urlencode($app->application_no ?? ($app->candidate->full_name ?? '')) }}" 
                                   class="d-inline-flex align-items-center justify-content-center rounded bg-success-subtle border border-success-subtle text-success text-decoration-none shadow-2xs" 
                                   style="width: 32px; height: 32px;" 
                                   title="تم رصد القرار - عرض في سجل القرارات">
                                    <i class="fa-solid fa-stamp fs-7"></i>
                                </a>
                            @elseif($canAttachDecision)
                                <a href="{{ $targetDecisionUrl }}" 
                                   class="btn btn-sm btn-solid-navy p-0 d-inline-flex align-items-center justify-content-center rounded shadow-2xs text-decoration-none" 
                                   style="width: 32px; height: 32px;" 
                                   title="إرفاق القرار (الانتقال لصفحة إرفاق القرارات المناسبة)">
                                    <i class="fa-solid fa-cloud-arrow-up fs-7" style="color: var(--heritage-gold-light);"></i>
                                </a>
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

                            @if(!$canAttachDecision && $app->status !== 'تم الصدور' && !$isFacultyPermission && !$isApplied && !$canGenerateEquivalenceNonSingle && !$canGenerateEligibility)
                                <span class="text-muted fs-8">-</span>
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
                <div class="modal-header text-white" style="background-color: #1A2A44;">
                    <h5 class="modal-title fs-6 fw-bold" id="messageModalLabel{{ $app->id }}">
                        <i class="fa-solid fa-comments me-2" style="color: var(--heritage-gold-light, #FED488);"></i> الرسائل والإشعارات مع الجامعة - طلب رقم {{ $app->application_no }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Chat History Box -->
                    <div class="messages-chat-box p-3 rounded border mb-4" style="max-height: 350px; overflow-y: auto; background-color: #f8fafc;">
                        @forelse($app->messages as $msg)
                            @php
                                $isOutgoing = ($msg->sender_id == (Auth::id() ?? 1) 
                                            || (optional($msg->sender)->user_type ?? '') == 'admin' 
                                            || (optional($msg->sender)->role ?? '') == 'admin'
                                            || (optional($msg->sender)->role_id == 1));
                            @endphp
                            <div class="mohe-chat-row {{ $isOutgoing ? 'outgoing' : 'incoming' }}" style="margin-bottom: 12px; display: flex; {{ $isOutgoing ? 'justify-content: flex-start;' : 'justify-content: flex-end;' }}">
                                <div class="mohe-chat-bubble {{ $isOutgoing ? 'outgoing' : 'incoming' }}" 
                                     style="max-width: 80%; min-width: 180px; padding: 10px 14px; border-radius: 12px; {{ $isOutgoing ? 'background: linear-gradient(135deg, #1A2A44 0%, #0f1d33 100%) !important; color: #ffffff !important; border: 1px solid rgba(197, 160, 89, 0.3); border-bottom-left-radius: 2px;' : 'background: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1; border-bottom-right-radius: 2px;' }} box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                                    <!-- Sender Badge -->
                                    <div class="mohe-chat-bubble-sender" style="font-size: 0.78rem; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; {{ $isOutgoing ? 'color: #FED488 !important;' : 'color: #1A2A44 !important;' }}">
                                        @if($isOutgoing)
                                            <i class="fa-solid fa-shield-halved fs-8"></i> مدير التعادل (الوزارة)
                                        @else
                                            <i class="fa-solid fa-building-columns fs-8"></i> {{ optional($app->workUniversity)->name ?? ($msg->sender->name ?? 'الجامعة') }}
                                        @endif
                                    </div>

                                    <!-- Message Text -->
                                    <p class="mohe-chat-bubble-text" style="margin: 0; white-space: pre-line; font-size: 0.90rem; line-height: 1.55; {{ $isOutgoing ? 'color: #ffffff !important;' : 'color: #1e293b !important;' }}">
                                        {{ $msg->message }}
                                    </p>

                                    <!-- Message Meta: Time -->
                                    <div class="mohe-chat-bubble-meta" style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; margin-top: 5px; font-size: 0.70rem; {{ $isOutgoing ? 'color: rgba(255, 255, 255, 0.8) !important;' : 'color: #64748b !important;' }}">
                                        <span>{{ $msg->created_at ? $msg->created_at->format('d/m/Y h:i A') : '' }}</span>
                                        @if($isOutgoing)
                                            <i class="fa-solid fa-check-double" style="color: #FED488; font-size: 0.75rem;" title="{{ $msg->is_read ? 'تمت القراءة' : 'تم التسليم' }}"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4 m-0"><i class="fa-regular fa-comments fs-3 d-block mb-2 opacity-50"></i>لا توجد رسائل سابقة بخصوص هذا الطلب.</p>
                        @endforelse
                    </div>

                    <!-- Send Message Form -->
                    <form action="{{ route('admin.applications.send_message', $app->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: #1A2A44;">إرسال رسالة/إشعار موجه للجامعة بخصوص هذا الطلب :</label>
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


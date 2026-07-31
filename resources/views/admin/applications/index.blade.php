@extends('layouts.admin')

@section('title', 'طلبات التعادل - إدارة وقرارات')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header">
        <h5 class="mohe-card-title"><i class="fa-solid fa-list-check me-2 text-success" style="color: #0f392b !important;"></i> سحلات وطلبات معادلة الشهادات العلمية</h5>

        <!-- Legacy Filter Buttons bar matching legacy ASPX -->
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-mohe-gold px-3 font-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fa-solid fa-filter me-1"></i> فرز الطلبات
            </button>
            <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-rotate-left me-1"></i> إعادة ضبط
            </a>
        </div>
    </div>

    <!-- Filter Collapsible Panel -->
    <div class="collapse {{ $statusFilter || $universityFilter ? 'show' : '' }}" id="filterCollapse">
        <div class="card-body bg-light border-bottom">
            <form action="{{ route('admin.applications.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">تصفية حسب حالة الطلب :</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- كافة الحالات --</option>
                        @foreach($statusesList as $st)
                            <option value="{{ $st }}" {{ $statusFilter == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">تصفية حسب الجامعة المعنية :</label>
                    <select name="university_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- كافة الجامعات --</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $universityFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-mohe-primary w-100 fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i> فرز</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <!-- Exact Table matching legacy ASPX grid -->
            <table class="table mohe-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>نوع الطلب</th>
                        <th>الجامعة</th>
                        <th>الاسم</th>
                        <th>الكلية / المهارة</th>
                        <th>المؤهل العلمي</th>
                        <th>وضع الطلب</th>
                        <th>إرفاق قرار التعادل</th>
                        <th style="width: 100px;">Messages</th>
                        <th style="width: 70px;">Edit</th>
                        <th style="width: 70px;">Select</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    @php
                        $highestEducation = $app->educations->sortByDesc('education_level_id')->first();
                    @endphp
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->id }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $app->request_type ?? 'تعادل' }}</span></td>
                        <td class="fw-bold">{{ $app->workUniversity->name ?? 'غير محددة' }}</td>
                        <td class="text-primary fw-bold">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                        <td>{{ $app->work_faculty ?? 'إدارة جامعة' }}</td>
                        <td>{{ $highestEducation->level->name ?? 'إجازة جامعية' }}</td>
                        <td>
                            <!-- Status Dropdown Quick Change -->
                            <form action="{{ route('admin.applications.update_status', $app->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm fw-bold border-2 text-center" style="font-size: 0.82rem; min-width: 130px; color: #000;">
                                    @foreach($statusesList as $st)
                                        <option value="{{ $st }}" {{ $app->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>
                            @if($app->latestDecision)
                                <a href="{{ asset('storage/' . $app->latestDecision->file_path) }}" target="_blank" class="btn btn-xs btn-outline-success fw-bold">
                                    <i class="fa-solid fa-file-pdf me-1"></i> تحميل القرار
                                </a>
                            @else
                                <button type="button" class="btn btn-xs btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#decisionModal{{ $app->id }}">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> إرفاق قرار
                                </button>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none fw-bold text-info" data-bs-toggle="modal" data-bs-target="#messageModal{{ $app->id }}">
                                Messages
                                @if($app->messages->count() > 0)
                                    <span class="badge rounded-pill bg-danger fs-7">{{ $app->messages->count() }}</span>
                                @endif
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('admin.applications.edit', $app->id) }}" class="btn btn-sm btn-link text-decoration-none fw-bold text-warning">
                                Edit
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.reports.show', $app->id) }}" class="btn btn-sm btn-link text-decoration-none fw-bold text-primary">
                                Select
                            </a>
                        </td>
                    </tr>

                    <!-- Modal 1: Upload Equivalence Decision -->
                    <div class="modal fade" id="decisionModal{{ $app->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.applications.update_status', $app->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="تم الصدور">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title fs-6"><i class="fa-solid fa-stamp me-1"></i> إرفاق قرار تعادل صادر للطالب {{ $app->candidate->full_name ?? '' }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">رقم القرار الوزاري :</label>
                                            <input type="text" name="decision_no" class="form-control" value="قرار-{{ $app->application_no }}/{{ date('Y') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">رفع صورة / نسخة قرار التعادل (PDF/صورة) :</label>
                                            <input type="file" name="decision_file" class="form-control" accept=".pdf,image/*" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">ملاحظات القرار :</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات مجلس التعليم العالي"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> حفظ وتنزيل القرار للجامعة</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal 2: Messages & Notifications -->
                    <div class="modal fade" id="messageModal{{ $app->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title fs-6"><i class="fa-solid fa-comments me-2"></i> الرسائل والإشعارات مع الجامعة - طلب رقم {{ $app->application_no }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="messages-chat-box p-3 bg-light rounded border mb-3" style="max-height: 300px; overflow-y: auto;">
                                        @forelse($app->messages as $msg)
                                            <div class="d-flex mb-3 {{ $msg->sender_id == (Auth::id() ?? 1) ? 'justify-content-start' : 'justify-content-end' }}">
                                                <div class="p-3 rounded-3 shadow-sm {{ $msg->sender_id == (Auth::id() ?? 1) ? 'bg-primary text-white' : 'bg-white text-dark border' }}" style="max-width: 75%;">
                                                    <div class="fw-bold fs-7 mb-1">{{ $msg->sender->name ?? 'مدير التعادل' }}</div>
                                                    <div>{{ $msg->message }}</div>
                                                    <div class="fs-8 mt-1 text-end opacity-75">{{ $msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '' }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center text-muted py-3">لا توجد رسائل سابقة بخصوص هذا الطلب.</p>
                                        @endforelse
                                    </div>

                                    <form action="{{ route('admin.applications.send_message', $app->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">إرسال رسالة/إشعار موجه للجامعة بخصوص هذا الطلب :</label>
                                            <textarea name="message" class="form-control" rows="3" placeholder="اكتب ملاحظاتك أو طلب الاستكمال هنا..." required></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-info text-white fw-bold"><i class="fa-solid fa-paper-plane me-1"></i> إرسال الرسالة للجامعة</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">لا توجد طلبات معادلة تطابق الخيارات المحددة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($applications->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $applications->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

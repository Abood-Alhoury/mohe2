@extends('layouts.admin')
@section('title', 'سجل المحادثات والرسائل الرسمية')

@section('content')
<!-- Page Title Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold text-prestigious mb-1">
            <i class="fa-solid fa-comments me-2" style="color: var(--heritage-gold);"></i>
            سجل المراسلات والمحادثات مع الجامعات
        </h2>
        <p class="text-muted small mb-0">مركز متابعة طلبات الحث، التنبيهات، ووثائق استكمال دراسة معاملات التعادل مع الجامعات</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3">
            <i class="fa-solid fa-list-check me-1"></i> سجل الطلبات
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm border border-secondary-subtle d-flex align-items-center gap-3" style="border-right: 4px solid var(--imperial-navy) !important;">
            <div class="p-3 rounded-circle" style="background: #f0f3ff; color: var(--imperial-navy);">
                <i class="fa-solid fa-comments fs-4"></i>
            </div>
            <div>
                <span class="text-muted fs-8 fw-semibold d-block">إجمالي طلبات المراسلة</span>
                <h4 class="fw-bold mb-0" style="color: var(--imperial-navy);">{{ $applicationsList->count() }} طلب</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm border border-secondary-subtle d-flex align-items-center gap-3" style="border-right: 4px solid var(--heritage-gold) !important;">
            <div class="p-3 rounded-circle" style="background: #fffdf5; color: var(--heritage-gold);">
                <i class="fa-solid fa-envelope-open-text fs-4"></i>
            </div>
            <div>
                <span class="text-muted fs-8 fw-semibold d-block">إجمالي الرسائل المسجلة</span>
                <h4 class="fw-bold mb-0 text-dark">{{ $totalMessagesCount }} رسالة</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white p-3 rounded shadow-sm border border-secondary-subtle d-flex align-items-center gap-3" style="border-right: 4px solid #dc2626 !important;">
            <div class="p-3 rounded-circle bg-danger-subtle text-danger">
                <i class="fa-solid fa-bell fs-4"></i>
            </div>
            <div>
                <span class="text-muted fs-8 fw-semibold d-block">الرسائل التنبيهية غير المقروءة</span>
                <h4 class="fw-bold mb-0 text-danger">{{ $unreadCount }} رسالة جديدة</h4>
            </div>
        </div>
    </div>
</div>

<!-- Master-Detail Chat Center Layout -->
<div class="row g-3">
    <!-- Right Sidebar: Applications / Conversations List -->
    <div class="col-lg-4 col-md-5">
        <div class="bg-white rounded shadow-sm border border-secondary-subtle overflow-hidden h-100 d-flex flex-column" style="min-height: 600px;">
            <div class="p-3 border-bottom bg-light">
                <h6 class="fw-bold mb-2" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-filter me-1" style="color: var(--heritage-gold);"></i> تصفية وشبكة المحادثات
                </h6>
                <form action="{{ route('admin.messages.index') }}" method="GET" class="d-flex flex-column gap-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="اسم المرشح / الرقم الوطني / المعاملة" value="{{ $search }}">
                        <button class="btn btn-solid-navy" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    <select name="university_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">جميع الجامعات المسجلة</option>
                        @foreach($universities as $u)
                            <option value="{{ $u->id }}" {{ $uniFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- List of Conversation Cards -->
            <div class="flex-grow-1 overflow-y-auto" style="max-height: 520px;">
                @forelse($applicationsList as $appItem)
                    @php
                        $lastMsg = $appItem->messages->first();
                        $unreadCountApp = $appItem->messages->where('sender_id', '!=', Auth::id() ?? 1)->where('is_read', false)->count();
                        $isSelected = $selectedApp && $selectedApp->id == $appItem->id;
                    @endphp
                    <a href="{{ route('admin.messages.index', ['application_id' => $appItem->id, 'university_id' => $uniFilter, 'search' => $search]) }}" 
                       class="d-block p-3 border-bottom text-decoration-none text-dark transition-all {{ $isSelected ? 'bg-light border-start border-primary border-4 shadow-sm' : 'hover-bg-light' }}">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge bg-warning text-dark fw-bold fs-8">{{ $appItem->application_no }}</span>
                            <small class="text-muted fs-8">{{ $lastMsg && $lastMsg->created_at ? $lastMsg->created_at->diffForHumans() : '' }}</small>
                        </div>
                        <div class="fw-bold mb-1" style="color: var(--imperial-navy); font-size: 0.92rem;">
                            {{ $appItem->candidate->full_name ?? 'المرشح' }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary-subtle text-dark fs-8">
                                <i class="fa-solid fa-university me-1"></i> {{ optional($appItem->workUniversity)->name ?? 'الجامعة' }}
                            </span>
                            @if($unreadCountApp > 0)
                                <span class="badge bg-danger rounded-pill fs-8">{{ $unreadCountApp }} جديد</span>
                            @endif
                        </div>
                        @if($lastMsg)
                            <p class="text-muted small text-truncate mb-0" style="max-width: 280px; font-size: 0.82rem;">
                                {{ $lastMsg->message }}
                            </p>
                        @endif
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fa-regular fa-comments fs-1 mb-2 d-block opacity-50"></i>
                        لا توجد محادثات أو طلبات رسائل مطابقة لخيارات البحث
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Left Main Area: Selected Conversation Thread -->
    <div class="col-lg-8 col-md-7">
        <div class="bg-white rounded shadow-sm border border-secondary-subtle overflow-hidden h-100 d-flex flex-column" style="min-height: 600px;">
            @if($selectedApp)
                <!-- Conversation Header Bar -->
                <div class="p-3 border-bottom bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center p-2 rounded-circle bg-white shadow-sm" style="border: 2px solid var(--heritage-gold); width: 48px; height: 48px;">
                            <i class="fa-solid fa-user-graduate fs-4" style="color: var(--imperial-navy);"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--imperial-navy);">
                                {{ $selectedApp->candidate->full_name ?? 'اسم المرشح' }}
                            </h6>
                            <small class="text-muted d-block">
                                <span class="badge bg-warning text-dark me-1">{{ $selectedApp->application_no }}</span>
                                {{ optional($selectedApp->workUniversity)->name }} &nbsp;|&nbsp; 
                                <span class="fw-semibold" style="color: var(--imperial-navy);">حالة الطلب: {{ $selectedApp->status }}</span>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.applications.edit', $selectedApp->id) }}" class="btn btn-sm btn-outline-navy fw-bold px-2.5">
                            <i class="fa-solid fa-pen-to-square me-1"></i> تعديل بيانات الطلب
                        </a>
                        <a href="{{ route('admin.reports.show', $selectedApp->id) }}" target="_blank" class="btn btn-sm btn-outline-gold fw-bold px-2.5">
                            <i class="fa-solid fa-file-pdf me-1"></i> مذكرة العرض
                        </a>
                    </div>
                </div>

                <!-- Chat History Messages Thread -->
                <div class="flex-grow-1 p-3 overflow-y-auto bg-white" id="adminChatContainer" style="max-height: 440px; background-color: #faf9fb;">
                    @forelse($selectedApp->messages as $msg)
                        @php
                            $isAdmin = ($msg->sender_id == Auth::id() || (optional($msg->sender)->user_type ?? '') == 'admin' || (optional($msg->sender)->role ?? '') == 'admin');
                        @endphp
                        <div class="mb-3 d-flex flex-column {{ $isAdmin ? 'align-items-start' : 'align-items-end' }}">
                            <div class="p-3 rounded-3 shadow-sm border {{ $isAdmin ? 'bg-navy text-white ms-4' : 'bg-white text-dark me-4 border-warning' }}" 
                                 style="max-width: 82%; {{ $isAdmin ? 'background-color: var(--imperial-navy); border-right: 4px solid var(--heritage-gold) !important;' : 'border-left: 4px solid var(--heritage-gold) !important;' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-3">
                                    <span class="fw-bold fs-8 {{ $isAdmin ? 'text-white-50' : 'text-primary' }}">
                                        <i class="fa-solid {{ $isAdmin ? 'fa-user-shield' : 'fa-university' }} me-1"></i>
                                        {{ $isAdmin ? 'مدير التعادل (أدمن الوزارة)' : (optional($selectedApp->workUniversity)->name ?? 'الجامعة') }}
                                    </span>
                                    <small class="fs-8 {{ $isAdmin ? 'text-white-50' : 'text-muted' }}">{{ $msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '' }}</small>
                                </div>
                                <div class="fs-7 lh-base text-wrap" style="white-space: pre-line;">{{ $msg->message }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-regular fa-comments fs-1 mb-2 d-block opacity-50"></i>
                            لا توجد رسائل سابقة لهذا الطلب. يمكنك كتابة أول ملاحظة للجامعة أدناه.
                        </div>
                    @endforelse
                </div>

                <!-- Reply Input Form Bar -->
                <div class="p-3 border-top bg-light">
                    <form action="{{ route('admin.applications.send_message', $selectedApp->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark fs-8 mb-1">
                                <i class="fa-solid fa-reply me-1 text-primary"></i> إرسال رد أو توجيه جديد للجامعة حول هذا الطلب:
                            </label>
                            <textarea name="message" class="form-control form-control-sm" rows="3" placeholder="اكتب ردك أو التوجيه أو طلب استكمال الأوراق هنا..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fs-8"><i class="fa-solid fa-lock me-1"></i> سيصل الإشعار فوراً لحساب الجامعة المسجلة</small>
                            <button type="submit" class="btn btn-solid-navy btn-sm px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-paper-plane me-1"></i> إرسال الرد للجامعة
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Empty State when no conversation selected -->
                <div class="text-center py-5 my-auto text-muted">
                    <div class="d-inline-flex p-4 rounded-circle bg-light mb-3">
                        <i class="fa-solid fa-comments fs-1" style="color: var(--heritage-gold);"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">مركز المراسلات والمحادثات</h5>
                    <p class="text-muted small">يرجى اختيار معاملة من القائمة الجانبية لعرض المحادثة الكاملة وإرسال التوجيهات</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chatBox = document.getElementById('adminChatContainer');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });
</script>
@endpush

@endsection

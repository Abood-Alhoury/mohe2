@extends('layouts.admin')
@section('title', 'سجل المحادثات والرسائل الرسمية')

@section('content')

<!-- Page Title Header & Top Summary Chips -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-container);">
            <i class="fa-solid fa-comments me-2" style="color: var(--heritage-gold);"></i>
            سجل المراسلات والمحادثات مع الجامعات
        </h4>
        <p class="text-muted fs-7 mb-0">مركز التراسل الفوري وتوجيه الجامعات واستكمال ملفات معاملات التعادل الأكاديمي</p>
    </div>
    
    <!-- Quick Statistics Pills -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-1.5 rounded-pill border shadow-sm fs-8">
            <i class="fa-solid fa-folder-open" style="color: var(--primary-container);"></i>
            <span class="text-muted">الطلبات:</span>
            <strong style="color: var(--primary-container);">{{ $applicationsList->count() }}</strong>
        </div>
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-1.5 rounded-pill border shadow-sm fs-8">
            <i class="fa-solid fa-envelope" style="color: var(--heritage-gold);"></i>
            <span class="text-muted">إجمالي الرسائل:</span>
            <strong>{{ $totalMessagesCount }}</strong>
        </div>
        @if($unreadCount > 0)
            <div class="d-flex align-items-center gap-2 bg-danger-subtle text-danger px-3 py-1.5 rounded-pill border border-danger-subtle fs-8 fw-bold">
                <i class="fa-solid fa-bell"></i>
                <span>{{ $unreadCount }} رسالة واردة جديدة</span>
            </div>
        @endif
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-navy fw-bold px-3 ms-1">
            <i class="fa-solid fa-list-check me-1"></i> سجل الطلبات
        </a>
    </div>
</div>

<!-- WhatsApp-Style Full Height Chat Window -->
<div class="mohe-chat-window shadow-sm">

    <!-- Right Sidebar: Applications & Filters -->
    <aside class="mohe-chat-sidebar">
        <!-- Sidebar Header with Filters -->
        <div class="mohe-chat-sidebar-header">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold fs-7" style="color: var(--primary-container);">
                    <i class="fa-solid fa-filter me-1" style="color: var(--heritage-gold);"></i> شبكة المحادثات
                </span>
                <span class="badge bg-white text-dark border px-2.5 py-1 fs-8 fw-bold">
                    {{ $applicationsList->count() }} معاملة
                </span>
            </div>

            <!-- Server Search & University Filter Form -->
            <form action="{{ route('admin.messages.index') }}" method="GET" class="d-flex flex-column gap-2" id="adminFilterForm">
                <div class="mohe-chat-search-wrap mt-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" 
                           name="search" 
                           id="adminSearchInput"
                           placeholder="بحث بالمرشح أو رقم المعاملة..." 
                           value="{{ $search }}"
                           onkeyup="filterAdminConversationsLive()">
                </div>
                <select name="university_id" class="form-select form-select-sm" style="border-radius: 18px; font-size: 0.8rem;" onchange="this.form.submit()">
                    <option value="">جميع الجامعات المسجلة</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->id }}" {{ $uniFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Conversations Scrollable List -->
        <div class="mohe-chat-list" id="adminConversationsList">
            @forelse($applicationsList as $appItem)
                @php
                    $lastMsg = $appItem->messages->first();
                    $unreadCountApp = $appItem->messages->where('sender_id', '!=', Auth::id() ?? 1)->where('is_read', false)->count();
                    $isSelected = ($selectedApp && $selectedApp->id == $appItem->id);
                    $candidateName = optional($appItem->candidate)->full_name ?? 'مرشح غير محدد';
                    $uniName = optional($appItem->workUniversity)->name ?? 'الجامعة';
                    $appNo = $appItem->application_no ?? $appItem->id;
                @endphp
                <a href="{{ route('admin.messages.index', ['application_id' => $appItem->id, 'university_id' => $uniFilter, 'search' => $search]) }}" 
                   class="mohe-chat-thread-item {{ $isSelected ? 'active' : '' }}"
                   data-search-term="{{ mb_strtolower($candidateName . ' ' . $appNo . ' ' . $uniName) }}">
                    
                    <!-- Avatar with initials or icon -->
                    <div class="mohe-chat-avatar avatar-admin">
                        <i class="fa-solid fa-university"></i>
                    </div>

                    <!-- Thread Summary Info -->
                    <div class="mohe-chat-thread-info">
                        <div class="mohe-chat-thread-top">
                            <span class="mohe-chat-thread-name">{{ $candidateName }}</span>
                            <span class="mohe-chat-thread-time">
                                {{ $lastMsg && $lastMsg->created_at ? $lastMsg->created_at->diffForHumans(null, true, true) : '' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <span class="badge bg-light text-dark border fs-8">#{{ $appNo }}</span>
                            <span class="badge-status badge-study fs-8" style="padding: 2px 6px;">{{ $appItem->status }}</span>
                        </div>
                        <div class="text-muted fs-8 text-truncate mb-1" style="max-width: 250px;">
                            <i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold); font-size: 0.72rem;"></i>
                            {{ $uniName }}
                        </div>
                        <div class="mohe-chat-thread-sub">
                            <p class="mohe-chat-thread-msg">
                                @if($lastMsg && $lastMsg->sender_id == Auth::id())
                                    <i class="fa-solid fa-check-double text-muted me-1 fs-8"></i>
                                @endif
                                {{ $lastMsg ? $lastMsg->message : 'لا توجد رسائل' }}
                            </p>
                            @if($unreadCountApp > 0)
                                <span class="mohe-chat-unread-badge">{{ $unreadCountApp }} جديد</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 px-3 text-muted">
                    <i class="fa-regular fa-comments fs-1 mb-3 d-block" style="color: var(--heritage-gold); opacity: 0.7;"></i>
                    <h6 class="fw-bold mb-1 text-dark">لا توجد محادثات</h6>
                    <p class="fs-8 text-muted mb-0">لا توجد محادثات مطابقة لخيارات البحث أو التصفية المحددة.</p>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- Left Main Area: WhatsApp Chat Screen -->
    <main class="mohe-chat-main">
        @if($selectedApp)
            <!-- 1. WhatsApp-Style Top Header Bar -->
            <header class="mohe-chat-header">
                <div class="mohe-chat-header-user">
                    <div class="mohe-chat-avatar avatar-admin" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                    </div>
                    <div>
                        <div class="mohe-chat-header-title d-flex align-items-center gap-2">
                            <span>{{ optional($selectedApp->candidate)->full_name }}</span>
                            <span class="badge bg-warning text-dark fw-bold fs-8">طلب #{{ $selectedApp->application_no }}</span>
                        </div>
                        <div class="mohe-chat-header-meta">
                            <span><i class="fa-solid fa-building-columns text-primary fs-8"></i> {{ optional($selectedApp->workUniversity)->name }}</span>
                            <span>•</span>
                            <span class="badge-status badge-study fs-8" style="padding: 2px 6px;">{{ $selectedApp->status }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="mohe-chat-header-actions">
                    <a href="{{ route('admin.applications.edit', $selectedApp->id) }}" class="btn btn-sm btn-outline-navy fw-bold" title="تعديل وتدقيق المعاملة">
                        <i class="fa-solid fa-pen-to-square me-1"></i> تعديل الطلب
                    </a>
                    <a href="{{ route('admin.reports.show', $selectedApp->id) }}" target="_blank" class="btn btn-sm btn-outline-gold fw-bold" title="عرض وطباعة مذكرة العرض">
                        <i class="fa-solid fa-file-pdf me-1"></i> مذكرة العرض
                    </a>
                    <a href="{{ route('admin.messages.index', ['application_id' => $selectedApp->id, 'university_id' => $uniFilter, 'search' => $search]) }}" class="btn btn-sm btn-outline-secondary" title="تحديث المحادثة">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </header>

            <!-- 2. Chat Canvas / Wallpaper Area with WhatsApp Bubbles -->
            <div class="mohe-chat-canvas" id="adminChatScrollArea">
                <!-- Institutional Start Notice -->
                <div class="mohe-chat-date-pill">
                    <i class="fa-solid fa-lock me-1" style="color: var(--heritage-gold);"></i> سجل المراسلات المعتمد مع {{ optional($selectedApp->workUniversity)->name }} بخصوص الطلب ({{ $selectedApp->application_no }})
                </div>

                @php
                    $lastDate = null;
                @endphp

                @forelse($selectedApp->messages as $msg)
                    @php
                        $msgDate = $msg->created_at ? $msg->created_at->format('Y-m-d') : null;
                        $isAdmin = ($msg->sender_id == Auth::id() 
                                 || (optional($msg->sender)->user_type ?? '') == 'admin' 
                                 || (optional($msg->sender)->role ?? '') == 'admin'
                                 || (optional($msg->sender)->role_id == 1));
                        
                        // For Admin view: Admin is outgoing, University is incoming
                        $isOutgoing = $isAdmin;
                    @endphp

                    @if($msgDate && $msgDate !== $lastDate)
                        @php
                            $lastDate = $msgDate;
                            $todayStr = now()->format('Y-m-d');
                            $yesterdayStr = now()->subDay()->format('Y-m-d');
                            $dateDisplay = ($msgDate === $todayStr) ? 'اليوم' : (($msgDate === $yesterdayStr) ? 'أمس' : $msg->created_at->format('d/m/Y'));
                        @endphp
                        <div class="mohe-chat-date-pill">
                            {{ $dateDisplay }}
                        </div>
                    @endif

                    <div class="mohe-chat-row {{ $isOutgoing ? 'outgoing' : 'incoming' }}">
                        <div class="mohe-chat-bubble {{ $isOutgoing ? 'outgoing' : 'incoming' }}">
                            <!-- Sender Label -->
                            <div class="mohe-chat-bubble-sender">
                                @if($isOutgoing)
                                    <i class="fa-solid fa-shield-halved fs-8 me-1" style="color: var(--heritage-gold-light);"></i> مدير التعادل (أنت)
                                @else
                                    <i class="fa-solid fa-building-columns fs-8 me-1" style="color: var(--primary-container);"></i> {{ optional($selectedApp->workUniversity)->name ?? 'الجامعة' }}
                                @endif
                            </div>

                            <!-- Message Text Body -->
                            <p class="mohe-chat-bubble-text">{{ $msg->message }}</p>

                            <!-- Footer Meta: Time & WhatsApp Checkmarks -->
                            <div class="mohe-chat-bubble-meta">
                                <span>{{ $msg->created_at ? $msg->created_at->format('h:i A') : '' }}</span>
                                @if($isOutgoing)
                                    <i class="fa-solid fa-check-double mohe-chat-tick" title="{{ $msg->is_read ? 'تمت القراءة من قبل الجامعة' : 'تم التسليم' }}"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 my-auto text-muted">
                        <i class="fa-regular fa-envelope-open fs-2 mb-2 d-block" style="color: var(--heritage-gold); opacity: 0.6;"></i>
                        <p class="fs-7 mb-0">لا توجد رسائل سابقة لهذه المعاملة. يمكنك كتابة أول توجيه أو استفسار للجامعة بالأسفل.</p>
                    </div>
                @endforelse
            </div>

            <!-- 3. WhatsApp-Style Message Input Dock -->
            <div class="mohe-chat-dock">
                <form action="{{ route('admin.applications.send_message', $selectedApp->id) }}" method="POST" id="adminChatReplyForm" class="d-flex align-items-center w-100 gap-2 m-0">
                    @csrf
                    <textarea name="message" 
                              id="adminChatInputText"
                              class="form-control mohe-chat-textarea" 
                              rows="1" 
                              placeholder="اكتب توجيهكم أو طلب استكمال الأوراق للجامعة هنا... (Enter للإرسال، Shift+Enter لسطر جديد)" 
                              required></textarea>
                    
                    <!-- Circular Send Button -->
                    <button type="submit" class="mohe-chat-send-btn" title="إرسال التوجيه للجامعة">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <!-- WhatsApp Desktop Empty State -->
            <div class="mohe-chat-empty-state">
                <div class="mohe-chat-empty-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h4 class="fw-bold mb-2" style="color: var(--primary-container);">
                    مركز المراسلات والمحادثات مع الجامعات
                </h4>
                <p class="text-muted fs-7 mb-4" style="max-width: 480px; line-height: 1.6;">
                    اختر معاملة من القائمة الجانبية لعرض المحادثة الكاملة مع الجامعة المعنية ومتابعة وثائق واستكمال دراسة قرارات التعادل.
                </p>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white border shadow-sm fs-8 text-muted">
                    <i class="fa-solid fa-shield-halved" style="color: var(--heritage-gold);"></i>
                    <span>نظام التراسل المؤسسي المعتمد - الجمهورية العربية السورية</span>
                </div>
            </div>
        @endif
    </main>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto scroll to bottom
        var chatBox = document.getElementById('adminChatScrollArea');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Auto-expand textarea & Enter to submit
        var adminInput = document.getElementById('adminChatInputText');
        var adminForm = document.getElementById('adminChatReplyForm');

        if (adminInput && adminForm) {
            adminInput.focus();

            adminInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (adminInput.value.trim() !== '') {
                        adminForm.submit();
                    }
                }
            });

            adminInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (Math.min(this.scrollHeight, 110)) + 'px';
            });
        }
    });

    // Instant client-side filter
    function filterAdminConversationsLive() {
        var query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
        var items = document.querySelectorAll('#adminConversationsList .mohe-chat-thread-item');

        items.forEach(function(item) {
            var term = item.getAttribute('data-search-term') || '';
            if (term.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endpush

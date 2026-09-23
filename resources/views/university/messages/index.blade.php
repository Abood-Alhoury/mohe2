@extends('layouts.university')

@section('title', 'مركز المراسلات والمحادثات الرسمية')

@section('content')

@php
    $groupedConversations = $messages->groupBy('application_id');
    $activeAppId = request()->query('application_id', $groupedConversations->keys()->first());
    $activeConversation = $activeAppId ? \App\Models\ApplicationMessage::where('application_id', $activeAppId)->with('sender.role')->orderBy('created_at', 'asc')->get() : collect();
    $activeApp = $activeAppId ? \App\Models\Application::with(['candidate', 'workUniversity'])->find($activeAppId) : null;
@endphp

<!-- Page Title Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--primary-container);">
            <i class="fa-solid fa-comments me-2" style="color: var(--heritage-gold);"></i>
            مركز المراسلات والمحادثات الرسمية
        </h4>
        <p class="text-muted fs-7 mb-0">منصة التراسل الفوري المباشر مع مدير التعادل بمجلس التعليم العالي لمتابعة المعاملات واستكمال الأوراق</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge px-3 py-2 border" style="background-color: var(--surface-container-low); color: var(--primary-container); font-size: 0.82rem;">
            <i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i>
            {{ Auth::user()->university->name ?? 'الجامعة المسجلة' }}
        </span>
    </div>
</div>

<!-- WhatsApp-Style Full Height Chat Window -->
<div class="mohe-chat-window shadow-sm">

    <!-- Right Sidebar: Conversations Threads List -->
    <aside class="mohe-chat-sidebar">
        <!-- Sidebar Header -->
        <div class="mohe-chat-sidebar-header">
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-bold fs-7" style="color: var(--primary-container);">
                    <i class="fa-solid fa-inbox me-1" style="color: var(--heritage-gold);"></i> المحادثات النشطة
                </span>
                <span class="badge bg-white text-dark border px-2.5 py-1 fs-8 fw-bold">
                    {{ $groupedConversations->count() }} معاملة
                </span>
            </div>

            <!-- Live Search Filter Input -->
            <div class="mohe-chat-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="threadSearchInput" placeholder="بحث بالمرشح أو رقم المعاملة..." onkeyup="filterConversations()">
            </div>
        </div>

        <!-- Conversations Scrollable List -->
        <div class="mohe-chat-list" id="conversationsList">
            @forelse($groupedConversations as $appId => $msgs)
                @php
                    $appInfo = $msgs->first()->application;
                    $latestMsg = $msgs->sortByDesc('created_at')->first();
                    $unreadCount = $msgs->where('is_read', false)->where('sender_id', '!=', Auth::id())->count();
                    $isActive = ($activeAppId == $appId);
                    $candidateName = optional($appInfo->candidate)->full_name ?? 'مرشح غير محدد';
                    $appNo = $appInfo->application_no ?? $appInfo->id;
                @endphp
                <a href="{{ route('university.messages', ['application_id' => $appId]) }}" 
                   class="mohe-chat-thread-item {{ $isActive ? 'active' : '' }}"
                   data-search-term="{{ mb_strtolower($candidateName . ' ' . $appNo) }}">
                    
                    <!-- Avatar with initials or icon -->
                    <div class="mohe-chat-avatar avatar-university">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>

                    <!-- Thread Summary Info -->
                    <div class="mohe-chat-thread-info">
                        <div class="mohe-chat-thread-top">
                            <span class="mohe-chat-thread-name">{{ $candidateName }}</span>
                            <span class="mohe-chat-thread-time">
                                {{ $latestMsg && $latestMsg->created_at ? $latestMsg->created_at->diffForHumans(null, true, true) : '' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <span class="badge bg-light text-dark border fs-8">#{{ $appNo }}</span>
                            <span class="badge-status badge-study fs-8" style="padding: 2px 6px;">{{ $appInfo->status }}</span>
                        </div>
                        <div class="mohe-chat-thread-sub">
                            <p class="mohe-chat-thread-msg">
                                @if($latestMsg && $latestMsg->sender_id == Auth::id())
                                    <i class="fa-solid fa-check-double text-muted me-1 fs-8"></i>
                                @endif
                                {{ $latestMsg ? $latestMsg->message : 'لا توجد رسائل' }}
                            </p>
                            @if($unreadCount > 0)
                                <span class="mohe-chat-unread-badge">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 px-3 text-muted">
                    <i class="fa-regular fa-comments fs-1 mb-3 d-block" style="color: var(--heritage-gold); opacity: 0.7;"></i>
                    <h6 class="fw-bold mb-1 text-dark">لا توجد محادثات جارية</h6>
                    <p class="fs-8 text-muted mb-0">ستظهر هنا أي رسائل أو استفسارات يتم تبادلها مع مدير التعادل بالوزارة حول معاملاتكم.</p>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- Left Main Area: WhatsApp Chat Screen -->
    <main class="mohe-chat-main">
        @if($activeApp)
            <!-- 1. WhatsApp-Style Top Header Bar -->
            <header class="mohe-chat-header">
                <div class="mohe-chat-header-user">
                    <div class="mohe-chat-avatar avatar-university" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                    </div>
                    <div>
                        <div class="mohe-chat-header-title d-flex align-items-center gap-2">
                            <span>{{ optional($activeApp->candidate)->full_name }}</span>
                            <span class="badge bg-warning text-dark fw-bold fs-8">طلب #{{ $activeApp->application_no }}</span>
                        </div>
                        <div class="mohe-chat-header-meta">
                            <span><i class="fa-solid fa-circle-check text-success fs-8"></i> {{ $activeApp->status }}</span>
                            <span>•</span>
                            <span>{{ optional($activeApp->workUniversity)->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="mohe-chat-header-actions">
                    <a href="{{ route('university.applications.show_mozhakkara', $activeApp->id) }}" target="_blank" class="btn btn-sm btn-outline-navy fw-bold" title="عرض وطباعة مذكرة العرض الرسمية">
                        <i class="fa-solid fa-file-pdf me-1" style="color: var(--heritage-gold);"></i> مذكرة العرض
                    </a>
                    <a href="{{ route('university.messages', ['application_id' => $activeApp->id]) }}" class="btn btn-sm btn-outline-secondary" title="تحديث الرسائل">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </header>

            <!-- 2. Chat Canvas / Wallpaper Area with WhatsApp Bubbles -->
            <div class="mohe-chat-canvas" id="chatScrollArea">
                <!-- Institutional Conversation Start Notice -->
                <div class="mohe-chat-date-pill">
                    <i class="fa-solid fa-lock me-1" style="color: var(--heritage-gold);"></i> سجل المراسلات الرسمي المشفر لمعاملة التعادل رقم ({{ $activeApp->application_no }})
                </div>

                @php
                    $lastDate = null;
                @endphp

                @forelse($activeConversation as $chat)
                    @php
                        $msgDate = $chat->created_at ? $chat->created_at->format('Y-m-d') : null;
                        $isAdminSender = ($chat->sender && $chat->sender->role && $chat->sender->role->name === 'admin') 
                                      || (optional($chat->sender)->user_type === 'admin')
                                      || ($chat->sender_id != Auth::id() && optional($chat->sender)->role_id == 1);
                        
                        // For university view: university user is outgoing, admin is incoming
                        $isOutgoing = !$isAdminSender;
                    @endphp

                    @if($msgDate && $msgDate !== $lastDate)
                        @php
                            $lastDate = $msgDate;
                            $todayStr = now()->format('Y-m-d');
                            $yesterdayStr = now()->subDay()->format('Y-m-d');
                            $dateDisplay = ($msgDate === $todayStr) ? 'اليوم' : (($msgDate === $yesterdayStr) ? 'أمس' : $chat->created_at->format('d/m/Y'));
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
                                    <i class="fa-solid fa-building-columns fs-8 me-1"></i> الجامعة (أنت)
                                @else
                                    <i class="fa-solid fa-shield-halved fs-8 me-1" style="color: var(--heritage-gold);"></i> مدير التعادل (مجلس التعليم العالي)
                                @endif
                            </div>

                            <!-- Message Text Body -->
                            <p class="mohe-chat-bubble-text">{{ $chat->message }}</p>

                            <!-- Footer Meta: Time & WhatsApp Checkmarks -->
                            <div class="mohe-chat-bubble-meta">
                                <span>{{ $chat->created_at ? $chat->created_at->format('h:i A') : '' }}</span>
                                @if($isOutgoing)
                                    <i class="fa-solid fa-check-double mohe-chat-tick" title="{{ $chat->is_read ? 'تمت القراءة من قبل الوزارة' : 'تم التسليم' }}"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 my-auto text-muted">
                        <i class="fa-regular fa-envelope-open fs-2 mb-2 d-block" style="color: var(--heritage-gold); opacity: 0.6;"></i>
                        <p class="fs-7 mb-0">لا توجد رسائل سابقة لهذه المعاملة. يمكنك كتابة أول استفسار لمدير التعادل بالأسفل.</p>
                    </div>
                @endforelse
            </div>

            <!-- 3. WhatsApp-Style Message Input Dock -->
            <div class="mohe-chat-dock">
                <form action="{{ route('university.applications.reply', $activeApp->id) }}" method="POST" id="chatReplyForm" class="d-flex align-items-center w-100 gap-2 m-0">
                    @csrf
                    <textarea name="message" 
                              id="chatInputText"
                              class="form-control mohe-chat-textarea" 
                              rows="1" 
                              placeholder="اكتب رسالتكم أو استفساركم هنا... (Enter للإرسال، Shift+Enter لسطر جديد)" 
                              required></textarea>
                    
                    <!-- Circular Send Button -->
                    <button type="submit" class="mohe-chat-send-btn" title="إرسال الرسالة">
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
                    مركز المراسلات والمحادثات الأكاديمية
                </h4>
                <p class="text-muted fs-7 mb-4" style="max-width: 480px; line-height: 1.6;">
                    اختر معاملة من القائمة الجانبية لاستعراض سجل المحادثة الكاملة مع مدير التعادل بوزارة التعليم العالي ومتابعة الملاحظات واستكمال النواقص.
                </p>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white border shadow-sm fs-8 text-muted">
                    <i class="fa-solid fa-shield-halved" style="color: var(--heritage-gold);"></i>
                    <span>نظام التراسل المؤسسي المعتمد لقرارات ومعاملات تعادل الشهادات</span>
                </div>
            </div>
        @endif
    </main>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto scroll to latest message
        const chatCanvas = document.getElementById('chatScrollArea');
        if (chatCanvas) {
            chatCanvas.scrollTop = chatCanvas.scrollHeight;
        }

        // Auto-expand textarea & Enter to submit
        const chatInput = document.getElementById('chatInputText');
        const chatForm = document.getElementById('chatReplyForm');

        if (chatInput && chatForm) {
            chatInput.focus();

            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (chatInput.value.trim() !== '') {
                        chatForm.submit();
                    }
                }
            });

            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (Math.min(this.scrollHeight, 110)) + 'px';
            });
        }
    });

    // Instant filter for conversations list
    function filterConversations() {
        const query = document.getElementById('threadSearchInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('#conversationsList .mohe-chat-thread-item');

        items.forEach(function(item) {
            const term = item.getAttribute('data-search-term') || '';
            if (term.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endpush

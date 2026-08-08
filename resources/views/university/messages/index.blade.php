@extends('layouts.university')

@section('title', 'مركز المراسلات والإشعارات')

@section('content')

@php
    $groupedConversations = $messages->groupBy('application_id');
    $activeAppId = request()->query('application_id', $groupedConversations->keys()->first());
    $activeConversation = $activeAppId ? \App\Models\ApplicationMessage::where('application_id', $activeAppId)->with('sender')->orderBy('created_at', 'asc')->get() : collect();
    $activeApp = $activeAppId ? \App\Models\Application::with('candidate')->find($activeAppId) : null;
@endphp

<div class="row g-4">
    <!-- Conversations Sidebar List -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important; overflow: hidden;">
            <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold" style="color: var(--primary-container);">
                    <i class="fa-solid fa-list-check me-1" style="color: var(--heritage-gold);"></i> معاملات تحتوي مراسلات
                </h6>
                <span class="badge bg-light text-dark border label-sm">{{ $groupedConversations->count() }} معاملة</span>
            </div>
            <div class="list-group list-group-flush" style="max-height: 550px; overflow-y: auto;">
                @forelse($groupedConversations as $appId => $msgs)
                    @php
                        $appInfo = $msgs->first()->application;
                        $latestMsg = $msgs->first();
                        $unreadCount = $msgs->where('is_read', false)->where('sender_id', '!=', Auth::id())->count();
                    @endphp
                    <a href="?application_id={{ $appId }}" class="list-group-item list-group-item-action p-3 {{ $activeAppId == $appId ? 'active bg-light border-start-0' : '' }} border-bottom" style="{{ $activeAppId == $appId ? 'border-right: 4px solid var(--heritage-gold) !important; background-color: var(--surface-container-low) !important;' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary fs-8" style="background-color: var(--primary-container) !important;">طلب #{{ $appInfo->application_no ?? $appInfo->id }}</span>
                            <small class="text-muted label-sm">{{ $latestMsg->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="fw-bold fs-7 mb-1" style="color: var(--primary-container);">{{ $appInfo->candidate->full_name ?? 'المرشح' }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted text-truncate label-sm" style="max-width: 210px;">{{ $latestMsg->message }}</span>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-envelope-open-text fs-2 mb-2 d-block" style="color: var(--heritage-gold);"></i>
                        لا توجد مراسلات حالياً.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Conversation Chat Box -->
    <div class="col-lg-8">
        @if($activeApp)
            <div class="card border-0 shadow-sm d-flex flex-column" style="border-radius: 8px; height: 600px; border-top: 3px solid var(--heritage-gold) !important;">
                <!-- Chat Header -->
                <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 fw-bold" style="color: var(--primary-container);">
                            <i class="fa-solid fa-user me-1" style="color: var(--heritage-gold);"></i> المراسلة بخصوص المرشح: {{ $activeApp->candidate->full_name }}
                        </h6>
                        <small class="text-muted label-sm">
                            طلب رقم: <span class="fw-bold text-dark">#{{ $activeApp->application_no }}</span> | الحالة: 
                            <span class="badge-status badge-study ms-1">{{ $activeApp->status }}</span>
                        </small>
                    </div>
                </div>

                <!-- Chat Messages Body -->
                <div class="card-body flex-grow-1 p-3 overflow-y-auto" id="chatContainer" style="background-color: var(--surface-container-low); overflow-y: auto;">
                    @foreach($activeConversation as $chat)
                        @php
                            $isAdminSender = $chat->sender->role && $chat->sender->role->name === 'admin';
                        @endphp
                        <div class="d-flex mb-3 {{ !$isAdminSender ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="card border-0 {{ !$isAdminSender ? 'bg-white shadow-sm' : 'text-white' }}" 
                                 style="max-width: 75%; border-radius: 8px; {{ !$isAdminSender ? 'border-top-right-radius: 0; border: 1px solid var(--outline-variant) !important;' : 'background: linear-gradient(135deg, var(--primary-container), var(--primary)) !important; border-top-left-radius: 0;' }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center gap-4 mb-1">
                                        <small class="fw-bold label-sm {{ !$isAdminSender ? 'text-primary' : '' }}" style="{{ $isAdminSender ? 'color: var(--heritage-gold-light) !important;' : 'color: var(--primary-container) !important;' }}">
                                            {{ !$isAdminSender ? 'الجامعة (أنت)' : 'مدير التعادل (مجلس التعليم العالي)' }}
                                        </small>
                                        <small class="label-sm opacity-75 {{ !$isAdminSender ? 'text-muted' : 'text-white-50' }}">
                                            {{ $chat->created_at->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                    <p class="mb-0 body-md" style="line-height: 1.6; white-space: pre-line; font-size: 0.92rem;">{{ $chat->message }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Chat Reply Box -->
                <div class="p-3 border-top bg-white" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <form action="{{ route('university.applications.reply', $activeApp->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <textarea name="message" class="form-control academic-input" rows="2" placeholder="اكتب ردكم أو استفساركم هنا للإرسال لمدير التعادل بالوزارة..." required></textarea>
                            <button class="btn btn-mohe-primary px-4" type="submit">
                                إرسال الرد <i class="fa-solid fa-paper-plane ms-1" style="color: var(--heritage-gold);"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm d-flex align-items-center justify-content-center py-5 h-100" style="border-radius: 8px; border-top: 3px solid var(--heritage-gold) !important;">
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-comments fs-1 mb-3" style="color: var(--heritage-gold);"></i>
                    <h5 class="fw-bold" style="color: var(--primary-container);">لا توجد محادثة نشطة</h5>
                    <p class="mb-0 label-sm">الرجاء اختيار معاملة من القائمة الجانبية لاستعراض سجل المراسلات والرد عليها.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Scroll chat body to bottom
    const chatContainer = document.getElementById('chatContainer');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
</script>
@endpush

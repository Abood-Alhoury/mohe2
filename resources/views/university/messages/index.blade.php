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
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="p-3 bg-light border-bottom">
                <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-list-check me-1"></i> معاملات تحتوي على مراسلات</h6>
            </div>
            <div class="list-group list-group-flush" style="max-height: 550px; overflow-y: auto;">
                @forelse($groupedConversations as $appId => $msgs)
                    @php
                        $appInfo = $msgs->first()->application;
                        $latestMsg = $msgs->first();
                        $unreadCount = $msgs->where('is_read', false)->where('sender_id', '!=', Auth::id())->count();
                    @endphp
                    <a href="?application_id={{ $appId }}" class="list-group-item list-group-item-action p-3 {{ $activeAppId == $appId ? 'active bg-light border-primary text-dark' : '' }} border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary fs-8">رقم الطلب: {{ $appInfo->application_no ?? $appInfo->id }}</span>
                            <small class="text-muted fs-8">{{ $latestMsg->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="fw-bold fs-7 mb-1 text-dark">{{ $appInfo->candidate->full_name ?? 'المرشح' }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted text-truncate fs-8" style="max-width: 220px;">{{ $latestMsg->message }}</span>
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-envelope-open-text fs-2 mb-2 d-block"></i>
                        لا توجد مراسلات حالياً.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Conversation Chat Box -->
    <div class="col-lg-8">
        @if($activeApp)
            <div class="card border-0 shadow-sm d-flex flex-column" style="border-radius: 12px; height: 600px;">
                <!-- Chat Header -->
                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 fw-bold text-primary">المراسلة بخصوص المرشح: {{ $activeApp->candidate->full_name }}</h6>
                        <small class="text-muted">
                            <i class="fa-solid fa-file-invoice me-1"></i> طلب رقم: {{ $activeApp->application_no }} | الحالة: 
                            <span class="badge bg-info text-dark fs-8">{{ $activeApp->status }}</span>
                        </small>
                    </div>
                </div>

                <!-- Chat Messages Body -->
                <div class="card-body flex-grow-1 p-3 overflow-y-auto bg-slate-50" id="chatContainer" style="background-color: #f8fafc; overflow-y: auto;">
                    @foreach($activeConversation as $chat)
                        @php
                            $isAdminSender = $chat->sender->role && $chat->sender->role->name === 'admin';
                        @endphp
                        <div class="d-flex mb-3 {{ !$isAdminSender ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="card border-0 {{ !$isAdminSender ? 'bg-white shadow-sm' : 'text-white' }}" 
                                 style="max-width: 70%; border-radius: 15px; {{ !$isAdminSender ? 'border-top-left-radius: 0;' : 'background-color: var(--mohe-navy); border-top-right-radius: 0;' }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center gap-4 mb-1">
                                        <small class="fw-bold {{ !$isAdminSender ? 'text-primary' : 'text-warning' }}">
                                            {{ !$isAdminSender ? 'الجامعة (أنت)' : 'مدير التعادل (مجلس التعليم العالي)' }}
                                        </small>
                                        <small class="fs-8 text-opacity-70 text-muted" style="font-size: 0.7rem;">
                                            {{ $chat->created_at->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                    <p class="mb-0 fs-7" style="line-height: 1.6; white-space: pre-line;">{{ $chat->message }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Chat Reply Box -->
                <div class="p-3 border-top bg-white" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <form action="{{ route('university.applications.reply', $activeApp->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <textarea name="message" class="form-control" rows="2" placeholder="اكتب ردكم أو استفساركم هنا للإرسال لمدير التعادل..." required></textarea>
                            <button class="btn btn-mohe-primary px-4" type="submit">
                                إرسال الرد <i class="fa-solid fa-paper-plane ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm d-flex align-items-center justify-content-center py-5 h-100" style="border-radius: 12px;">
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-comments fs-1 mb-3 text-warning"></i>
                    <h5 class="fw-bold">لا يوجد محادثة نشطة</h5>
                    <p class="mb-0">الرجاء اختيار معاملة من القائمة الجانبية لاستعراض سجل المراسلات والرد عليها.</p>
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

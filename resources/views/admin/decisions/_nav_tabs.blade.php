@php
    $currentRoute = Route::currentRouteName();
    $active = $active ?? '';
@endphp

<!-- COMPACT & ELEGANT PILL TABS FOR THE 5 CONSOLIDATED DECISION TRACKS -->
<div class="d-flex align-items-center justify-content-start mb-3.5 flex-wrap gap-2" role="tablist">
    {{-- 1. الماجستير التطبيقي (داخلي + خارجي تطبيقي - ورقة وحيدة) --}}
    <a href="{{ route('admin.applied_decisions.index') }}" 
       class="btn {{ ($active === 'applied' || $currentRoute === 'admin.applied_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الماجستير التطبيقي (داخلي وخارجي - قرار تعادل/معادلة فقط بدون أهلية - ورقة وحيدة)">
        <i class="fa-solid fa-briefcase" style="{{ ($active === 'applied' || $currentRoute === 'admin.applied_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الماجستير التطبيقي</span>
        <span class="badge rounded-pill bg-light text-dark fs-9 border ms-0.5">📄 ورقة وحيدة</span>
    </a>

    {{-- 2. الماجستير النظري (داخلي + خارجي نظري - ورقة موحدة) --}}
    <a href="{{ route('admin.decisions.index') }}" 
       class="btn {{ ($active === 'master' || $active === 'theoretical' || $currentRoute === 'admin.decisions.index' || $currentRoute === 'admin.theoretical_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الماجستير النظري (داخلي وخارجي - قرار التعادل والأهلية الموحد - ورقة واحدة)">
        <i class="fa-solid fa-graduation-cap" style="{{ ($active === 'master' || $active === 'theoretical' || $currentRoute === 'admin.decisions.index' || $currentRoute === 'admin.theoretical_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الماجستير النظري</span>
        <span class="badge rounded-pill bg-light text-dark fs-9 border ms-0.5">📄 ورقة موحدة</span>
    </a>

    {{-- 3. السماح بالتدريس (أعضاء الجامعات الحكومية - ورقة وحيدة) --}}
    <a href="{{ route('admin.faculty_decisions.index') }}" 
       class="btn {{ ($active === 'faculty' || $currentRoute === 'admin.faculty_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات السماح بالتدريس لأعضاء الهيئة التدريسية بالجامعات الحكومية - ورقة وحيدة">
        <i class="fa-solid fa-chalkboard-user" style="{{ ($active === 'faculty' || $currentRoute === 'admin.faculty_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>السماح بالتدريس</span>
        <span class="badge rounded-pill bg-light text-dark fs-9 border ms-0.5">📄 ورقة وحيدة</span>
    </a>

    {{-- 4. الدكتوراه (دكتوراه سورية + دكتوراه خارجية - ورقة موحدة) --}}
    <a href="{{ route('admin.doctorate_decisions.index') }}" 
       class="btn {{ ($active === 'doctorate' || $currentRoute === 'admin.doctorate_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الدكتوراه (سورية وخارجية - قرار التعادل والأهلية الموحد - ورقة واحدة)">
        <i class="fa-solid fa-user-graduate" style="{{ ($active === 'doctorate' || $currentRoute === 'admin.doctorate_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الدكتوراه</span>
        <span class="badge rounded-pill bg-light text-dark fs-9 border ms-0.5">📄 ورقة موحدة</span>
    </a>

    {{-- 5. قرارات عدم الموافقة (الطلبات المرفوضة - ورقة وحيدة) --}}
    <a href="{{ route('admin.rejected_decisions.index') }}" 
       class="btn {{ ($active === 'rejected' || $currentRoute === 'admin.rejected_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       style="{{ ($active === 'rejected' || $currentRoute === 'admin.rejected_decisions.index') ? 'background-color: #dc2626 !important; border-color: #dc2626 !important; color: white !important;' : 'color: #dc2626 !important; border-color: #fca5a5 !important;' }}"
       title="إصدار كتب وقرارات عدم الموافقة المسببة للطلبات المرفوضة - ورقة وحيدة">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 0.82rem;"></i>
        <span>قرارات عدم الموافقة</span>
        <span class="badge rounded-pill bg-light text-danger fs-9 border ms-0.5">📄 ورقة وحيدة</span>
    </a>
</div>

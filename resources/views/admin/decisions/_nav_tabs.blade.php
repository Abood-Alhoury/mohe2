@php
    $currentRoute = Route::currentRouteName();
    $active = $active ?? '';
@endphp

<!-- COMPACT & ELEGANT PILL TABS FOR DECISION TYPES -->
<div class="d-flex align-items-center justify-content-start mb-3.5 flex-wrap gap-2" role="tablist">
    <a href="{{ route('admin.decisions.index') }}" 
       class="btn {{ ($active === 'master' || $currentRoute === 'admin.decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الماجستير الداخلي">
        <i class="fa-solid fa-graduation-cap" style="{{ ($active === 'master' || $currentRoute === 'admin.decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الماجستير الداخلي</span>
    </a>

    <a href="{{ route('admin.doctorate_decisions.index') }}" 
       class="btn {{ ($active === 'doctorate' || $currentRoute === 'admin.doctorate_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الدكتوراه الداخلية">
        <i class="fa-solid fa-user-graduate" style="{{ ($active === 'doctorate' || $currentRoute === 'admin.doctorate_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الدكتوراه الداخلية</span>
    </a>

    <a href="{{ route('admin.applied_decisions.index') }}" 
       class="btn {{ ($active === 'applied' || $currentRoute === 'admin.applied_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الماجستير التطبيقي">
        <i class="fa-solid fa-briefcase" style="{{ ($active === 'applied' || $currentRoute === 'admin.applied_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الماجستير التطبيقي</span>
    </a>

    <a href="{{ route('admin.faculty_decisions.index') }}" 
       class="btn {{ ($active === 'faculty' || $currentRoute === 'admin.faculty_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات السماح بالتدريس للهيئة التدريسية">
        <i class="fa-solid fa-chalkboard-user" style="{{ ($active === 'faculty' || $currentRoute === 'admin.faculty_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>السماح بالتدريس</span>
    </a>

    <a href="{{ route('admin.foreign_master_decisions.index') }}" 
       class="btn {{ ($active === 'foreign_master' || $currentRoute === 'admin.foreign_master_decisions.index') ? 'btn-solid-navy' : 'btn-outline-navy bg-white' }} fw-bold px-3 py-1.5 rounded-pill shadow-xs fs-7 d-inline-flex align-items-center gap-1.5"
       title="إصدار قرارات الماجستير الخارجي (تطبيقي ونظري)">
        <i class="fa-solid fa-earth-americas" style="{{ ($active === 'foreign_master' || $currentRoute === 'admin.foreign_master_decisions.index') ? 'color: var(--heritage-gold-light);' : '' }} font-size: 0.82rem;"></i>
        <span>الماجستير الخارجي</span>
    </a>
</div>

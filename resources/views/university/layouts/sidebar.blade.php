<!-- MODERN LUXURY UNIVERSITY SIDEBAR -->
<aside class="modern-cyber-sidebar sidebar-container" :class="{ 'expanded': isExpanded }">
    <!-- Collapse Pill Toggle Button -->
    <button type="button" 
            @click="isExpanded = !isExpanded" 
            class="sidebar-pill-toggle" 
            :title="isExpanded ? 'طَي القائمة الجانبية' : 'توسيع القائمة الجانبية'">
        <i class="fa-solid fa-chevron-left" :class="{ 'rotated': isExpanded }"></i>
    </button>

    <!-- University Profile Header -->
    <div class="sidebar-uni-brand">
        <div class="uni-avatar-frame">
            <div class="uni-icon-wrap">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <span class="online-indicator" title="متصل"></span>
        </div>
        <div class="uni-identity-text">
            <h4 class="uni-username">{{ Auth::user()->name ?? 'uni_aljazeera' }}</h4>
            <span class="uni-fullname">{{ Auth::user()->university->name ?? 'جامعة الجزيرة الخاصة' }}</span>
            <span class="uni-status-tag"><i class="fa-solid fa-circle-check"></i> معتمد رسمي</span>
        </div>
    </div>

    <!-- Faded Gradient Divider -->
    <div class="sidebar-divider"></div>

    <!-- Navigation Items -->
    <nav class="sidebar-nav-list">
        <!-- 1. Dashboard -->
        <a href="{{ route('university.dashboard') }}" 
           class="nav-item-link {{ request()->routeIs('university.dashboard*') ? 'active' : '' }}"
           :title="!isExpanded ? 'لوحة التحكم الرئيسية' : ''">
            <div class="nav-icon-squircle icon-navy">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <span class="nav-text">لوحة التحكم الرئيسية</span>
        </a>

        <!-- 2. New Application -->
        <a href="{{ route('university.apply.options') }}" 
           class="nav-item-link {{ request()->routeIs('university.apply*') ? 'active' : '' }}"
           :title="!isExpanded ? 'تقديم معاملة تعادل' : ''">
            <div class="nav-icon-squircle icon-emerald">
                <i class="fa-solid fa-file-circle-plus"></i>
            </div>
            <span class="nav-text">تقديم معاملة تعادل</span>
        </a>

        <!-- 3. Archive: أرشيف القرارات والمعاملات -->
        <a href="{{ route('university.archive') }}" 
           class="nav-item-link {{ request()->routeIs('university.archive*') ? 'active' : '' }}"
           :title="!isExpanded ? 'أرشيف القرارات والمعاملات' : ''">
            <div class="nav-icon-squircle icon-gold">
                <i class="fa-solid fa-stamp"></i>
            </div>
            <span class="nav-text">أرشيف القرارات والمعاملات</span>
            @if(isset($approvedCount) && $approvedCount > 0)
                <span class="nav-counter-badge emerald ms-auto">{{ $approvedCount }}</span>
            @endif
        </a>

        <!-- 4. Saved Drafts -->
        <a href="{{ route('university.drafts.index') }}" 
           class="nav-item-link {{ request()->routeIs('university.drafts*') ? 'active' : '' }}"
           :title="!isExpanded ? 'إدارة المسودات المحفوظة' : ''">
            <div class="nav-icon-squircle icon-amber">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <span class="nav-text">إدارة المسودات المحفوظة</span>
            @if(isset($draftsCount) && $draftsCount > 0)
                <span class="nav-counter-badge amber ms-auto">{{ $draftsCount }}</span>
            @endif
        </a>

        <!-- 4. Messages & Notifications -->
        <a href="{{ route('university.messages') }}" 
           class="nav-item-link {{ request()->routeIs('university.messages*') ? 'active' : '' }}"
           :title="!isExpanded ? 'المراسلات والإشعارات' : ''">
            <div class="nav-icon-squircle icon-sky">
                <i class="fa-solid fa-comments"></i>
            </div>
            <span class="nav-text">المراسلات والإشعارات</span>
            @if(isset($notifications) && $notifications->count() > 0)
                <span class="nav-counter-badge danger ms-auto">{{ $notifications->count() }}</span>
            @endif
        </a>

        <!-- 5. Required Documents -->
        <a href="{{ route('university.required_documents') }}" 
           class="nav-item-link {{ request()->routeIs('university.required_documents*') ? 'active' : '' }}"
           :title="!isExpanded ? 'الأوراق المطلوبة للتعادل' : ''">
            <div class="nav-icon-squircle icon-rose">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <span class="nav-text">الأوراق المطلوبة للتعادل</span>
        </a>
    </nav>
</aside>

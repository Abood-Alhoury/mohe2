<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'بوابة الخدمات الإلكترونية للجامعات') - مجلس التعليم العالي</title>
    
    <!-- Google Fonts: IBM Plex Sans Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Vite CSS/JS (Tailwind CSS v4 & custom design tokens) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom MoHE Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/mohe.css') }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --imperial-navy: #1A2A44;
            --imperial-navy-dark: #04152E;
            --heritage-gold: #C5A059;
            --heritage-gold-light: #FED488;
            --surface-bg: #F9F9FF;
            --surface-card: #FFFFFF;
            --outline-variant: #C5C6CE;
        }

        body {
            font-family: 'IBM Plex Sans Arabic', system-ui, -apple-system, sans-serif;
            background-color: var(--surface-bg);
            color: #111C2C;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .mohe-header {
            background-color: var(--imperial-navy);
            color: #ffffff;
            border-bottom: 3px solid var(--heritage-gold);
            box-shadow: 0 4px 12px rgba(4, 21, 46, 0.2);
            padding: 0.85rem 1.5rem;
            z-index: 50;
        }

        .mohe-emblem-ring {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            border: 2px solid var(--heritage-gold);
            padding: 2px;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }

        .mohe-emblem-ring img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .mohe-footer-institutional {
            background-color: #e4e2e4;
            color: #44474D;
            border-top: 1px solid var(--outline-variant);
            padding: 1.25rem 2rem;
            font-size: 0.85rem;
            margin-top: auto;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-[#F9F9FF]" x-data="{ isExpanded: localStorage.getItem('sidebar_expanded') === 'true' }" x-init="$watch('isExpanded', val => localStorage.setItem('sidebar_expanded', val))">

    <!-- 1. TOP INSTITUTIONAL HEADER BANNER -->
    <header class="mohe-header">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('university.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                    <!-- Circular Gold Ring Logo Emblem with Hover Glow -->
                    <div class="mohe-emblem-ring">
                        <img src="{{ asset('assets/logo.jpg') }}" 
                             alt="وزارة التعليم العالي"
                             onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge" style="background: rgba(197,160,89,0.2); color: #FED488; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; border: 1px solid rgba(197,160,89,0.35); border-radius: 4px; padding: 2px 7px;">
                                الجمهورية العربية السورية | مجلس التعليم العالي
                            </span>
                        </div>
                        <h1 class="brand-title">بوابة الجامعات للتعادل الأكاديمي</h1>
                        <p class="brand-subtitle">النظام الإلكتروني الموحد لإدارة ورفع طلبات معادلة الشهادات والدرجات العلمية</p>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                @php
                    $siteLocked = \App\Models\SiteSetting::get('site_locked', '0') === '1';
                @endphp
                @if($siteLocked)
                    <span class="status-badge">
                        <span class="status-dot pulse-red"></span>
                        <span>الموقع مغلق للجامعات</span>
                    </span>
                @else
                    <span class="status-badge">
                        <span class="status-dot pulse-green"></span>
                        <span>النظام متاح وشغال</span>
                    </span>
                @endif

                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-light position-relative p-2" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-color: rgba(197, 160, 89, 0.4);">
                        <i class="fa-regular fa-bell fs-5" style="color: var(--heritage-gold-light);"></i>
                        @if(isset($notifications) && $notifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                {{ $notifications->count() }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg py-0 border-0" aria-labelledby="notifDropdown" style="width: 320px; border-top: 3px solid var(--heritage-gold) !important;">
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold" style="color: var(--imperial-navy);"><i class="fa-solid fa-bell me-1" style="color: var(--heritage-gold);"></i> الإشعارات والرسائل</h6>
                            @if(isset($notifications) && $notifications->count() > 0)
                                <span class="badge bg-danger">{{ $notifications->count() }} جديدة</span>
                            @endif
                        </div>
                        <div style="max-height: 320px; overflow-y: auto;">
                            @if(isset($notifications) && $notifications->count() > 0)
                                @foreach($notifications as $notif)
                                    <a href="{{ route('university.messages') }}" class="dropdown-item p-3 border-bottom text-wrap">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-warning text-dark fs-8">{{ $notif->application->application_no ?? 'طلب' }}</span>
                                            <small class="text-muted fs-8">{{ $notif->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="small fw-bold text-dark mb-1">{{ $notif->application->candidate->full_name ?? 'المرشح' }}</div>
                                        <div class="text-muted text-truncate fs-7" style="max-width: 280px;">{{ $notif->message }}</div>
                                    </a>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-regular fa-bell-slash fs-2 mb-2 d-block"></i>
                                    لا توجد إشعارات جديدة حالياً
                                </div>
                            @endif
                        </div>
                        <div class="p-2 border-top text-center bg-light">
                            <a href="{{ route('university.messages') }}" class="btn btn-sm btn-link fw-bold text-decoration-none" style="color: var(--imperial-navy);">
                                استعراض كافة المراسلات <i class="fa-solid fa-arrow-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- MODERN INSTITUTIONAL USER PROFILE DROPDOWN -->
                <div class="dropdown">
                    <div class="user-menu-pill" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="user-avatar-circle">
                            <i class="fa-solid fa-university"></i>
                        </div>
                        <div class="d-none d-sm-block text-start pe-1">
                            <span class="fw-bold d-block lh-1 text-white" style="font-size: 0.88rem;">{{ Auth::user()->name ?? 'حساب الجامعة' }}</span>
                            <span class="text-white-50 lh-1" style="font-size: 0.72rem;">{{ Auth::user()->university->name ?? 'جامعة مسجلة' }}</span>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.75rem; color: var(--heritage-gold);"></i>
                    </div>

                    <div class="dropdown-menu dropdown-menu-end user-dropdown-card shadow-lg p-0">
                        <div class="user-dropdown-header">
                            <div class="d-inline-flex align-items-center justify-content-center p-1 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 54px; height: 54px;">
                                <i class="fa-solid fa-university fs-4" style="color: var(--imperial-navy);"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-white">{{ Auth::user()->name ?? 'حساب الجامعة' }}</h6>
                            <small class="text-white-50 fs-8">{{ Auth::user()->email ?? 'uni@mohe.gov.sy' }}</small>
                        </div>
                        <div class="py-2 bg-white">
                            <a class="user-dropdown-item" href="{{ route('university.messages') }}">
                                <i class="fa-regular fa-envelope text-primary fs-5"></i>
                                <span>مركز المراسلات والإشعارات</span>
                            </a>
                            <div class="dropdown-divider my-1"></div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button class="user-dropdown-item logout-item w-100 border-0 bg-transparent text-start" type="submit">
                                    <i class="fa-solid fa-right-from-bracket fs-5"></i>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- 2. MAIN LAYOUT WITH COLLAPSED-BY-DEFAULT SIDEBAR & CANVAS -->
    <div class="d-flex flex-1">
        
        <!-- GLOBAL SIDEBAR FOR UNIVERSITY PORTAL (COLLAPSED BY DEFAULT) -->
        <aside class="sidebar-container" :class="{ 'expanded': isExpanded }">
            <!-- MIDDLE-EDGE TOGGLE ARROW BUTTON -->
            <button type="button" 
                    @click="isExpanded = !isExpanded" 
                    class="sidebar-edge-toggle" 
                    :title="isExpanded ? 'طَي القائمة الجانبية' : 'توسيع القائمة الجانبية'">
                <i class="fa-solid fa-chevron-left" :class="{ 'rotated': isExpanded }"></i>
            </button>
            
            <!-- University User Profile Box -->
            <div class="sidebar-profile-box text-center p-3 mb-2 border-bottom border-secondary-subtle">
                <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 60px; height: 60px;">
                    <i class="fa-solid fa-university fs-3" style="color: var(--imperial-navy);"></i>
                </div>
                <h6 class="fw-bold mb-0 text-truncate" style="color: var(--imperial-navy);">{{ Auth::user()->name }}</h6>
                <small class="text-muted text-truncate d-block">{{ Auth::user()->university->name ?? 'الجامعة المسجلة' }}</small>
            </div>

            <!-- University Sidebar Links -->
            <nav class="d-flex flex-column gap-1 p-2">
                <a href="{{ route('university.dashboard') }}" 
                   class="sidebar-link {{ request()->routeIs('university.dashboard') ? 'active' : '' }}"
                   :title="!isExpanded ? 'لوحة التحكم الرئيسية' : ''">
                    <i class="fa-solid fa-chart-line fs-5"></i>
                    <span class="sidebar-text-label">لوحة التحكم الرئيسية</span>
                </a>

                <a href="{{ route('university.apply.options') }}" 
                   class="sidebar-link {{ request()->routeIs('university.apply*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'تقديم معاملة تعادل جديدة' : ''">
                    <i class="fa-solid fa-file-circle-plus fs-5"></i>
                    <span class="sidebar-text-label">تقديم معاملة تعادل</span>
                </a>

                <a href="#" 
                   data-bs-toggle="modal" 
                   data-bs-target="#searchModal" 
                   class="sidebar-link"
                   :title="!isExpanded ? 'البحث السريع عن المعاملات' : ''">
                    <i class="fa-solid fa-magnifying-glass fs-5" style="color: var(--heritage-gold);"></i>
                    <span class="sidebar-text-label">البحث السريع عن طلب</span>
                </a>

                <a href="{{ route('university.messages') }}" 
                   class="sidebar-link {{ request()->routeIs('university.messages') ? 'active' : '' }}"
                   :title="!isExpanded ? 'المراسلات والإشعارات' : ''">
                    <i class="fa-solid fa-comments fs-5"></i>
                    <span class="sidebar-text-label">المراسلات والإشعارات</span>
                    @if(isset($notifications) && $notifications->count() > 0)
                        <span class="badge bg-danger ms-auto sidebar-text-label">{{ $notifications->count() }}</span>
                    @endif
                </a>

                <a href="{{ route('university.required_documents') }}" 
                   class="sidebar-link {{ request()->routeIs('university.required_documents') ? 'active' : '' }}"
                   :title="!isExpanded ? 'الأوراق والشهادات المطلوبة' : ''">
                    <i class="fa-solid fa-file-circle-check fs-5"></i>
                    <span class="sidebar-text-label">الأوراق المطلوبة للتعادل</span>
                </a>

                <a href="{{ route('contact') }}" 
                   class="sidebar-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                   :title="!isExpanded ? 'الدعم والتواصل المؤسسي' : ''">
                    <i class="fa-solid fa-headset fs-5"></i>
                    <span class="sidebar-text-label">الدعم والتواصل المؤسسي</span>
                </a>
            </nav>
        </aside>

        <!-- MAIN CANVAS -->
        <main class="flex-grow-1 p-4">
            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-right: 4px solid #059669 !important;">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-right: 4px solid #ba1a1a !important;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- 3. OFFICIAL EXECUTIVE FOOTER -->
    @include('partials.footer')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة وقرارات معادلة الشهادات') - مجلس التعليم العالي</title>
    
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

    <!-- Custom MoHE Theme -->
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

        /* Top Header Styling */
        .mohe-header {
            background-color: var(--imperial-navy);
            color: #ffffff;
            border-bottom: 3px solid var(--heritage-gold);
            box-shadow: 0 4px 12px rgba(4, 21, 46, 0.2);
            padding: 0.85rem 1.5rem;
            z-index: 50;
        }

        .mohe-emblem-ring {
            width: 52px;
            height: 52px;
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

        .shadow-ambient {
            box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05);
        }

        /* Footer */
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

    <!-- 1. TOP HEADER (UNTOUCHED LOGO, TITLE, TOGGLE BUTTON & USER MENU) -->
    <header class="mohe-header">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <!-- Circular Gold Ring Logo Emblem -->
                <div class="mohe-emblem-ring">
                    <img src="{{ asset('assets/logo.jpg') }}" 
                         alt="وزارة التعليم العالي" 
                         onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                </div>
                <div>
                    <h1 class="h5 fw-bold text-white mb-0">مجلس التعليم العالي</h1>
                    <p class="small text-white-50 mb-0">نظام إدارة وقرارات معادلة الشهادات العلمية</p>
                </div>

                <!-- SIDEBAR EXPAND/COLLAPSE TOGGLE BUTTON -->
                <button type="button" 
                        @click="isExpanded = !isExpanded" 
                        class="btn-sidebar-toggle ms-3" 
                        title="طَي / توسيع القائمة الجانبية">
                    <i class="fa-solid" :class="isExpanded ? 'fa-align-right' : 'fa-bars-staggered'"></i>
                    <span class="d-none d-md-inline ms-1 fw-bold" style="font-size: 0.8rem;" x-text="isExpanded ? 'طَي' : 'توسيع'">توسيع</span>
                </button>
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

                <!-- MODERN INSTITUTIONAL USER PROFILE DROPDOWN -->
                <div class="dropdown">
                    <div class="user-menu-pill" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <div class="user-avatar-circle">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="d-none d-sm-block text-start pe-1">
                            <span class="fw-bold d-block lh-1 text-white" style="font-size: 0.88rem;">{{ Auth::user()->name ?? 'مدير التعادل' }}</span>
                            <span class="text-white-50 lh-1" style="font-size: 0.72rem;">مدير النظام (Admin)</span>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.75rem; color: var(--heritage-gold);"></i>
                    </div>

                    <div class="dropdown-menu dropdown-menu-end user-dropdown-card shadow-lg p-0">
                        <div class="user-dropdown-header">
                            <div class="d-inline-flex align-items-center justify-content-center p-1 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 54px; height: 54px;">
                                <i class="fa-solid fa-user-shield fs-4" style="color: var(--imperial-navy);"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-white">{{ Auth::user()->name ?? 'مدير التعادل' }}</h6>
                            <small class="text-white-50 fs-8">{{ Auth::user()->email ?? 'admin@mohe.gov.sy' }}</small>
                        </div>
                        <div class="py-2 bg-white">
                            <a class="user-dropdown-item" href="{{ route('admin.settings') }}">
                                <i class="fa-solid fa-gear text-primary fs-5"></i>
                                <span>إعدادات الموقع والحسابات</span>
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
        <!-- GLOBAL SIDE NAVIGATION MENU (COLLAPSED BY DEFAULT, EXPANDS ONLY ON USER TOGGLE) -->
        <aside class="sidebar-container" :class="{ 'expanded': isExpanded }">
            
            <!-- User Profile Box inside Sidebar -->
            <div class="sidebar-profile-box text-center p-3 mb-2 border-bottom border-secondary-subtle">
                <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 60px; height: 60px;">
                    <i class="fa-solid fa-user-gear fs-3" style="color: var(--imperial-navy);"></i>
                </div>
                <h6 class="fw-bold mb-0 text-truncate" style="color: var(--imperial-navy);">مدير النظام</h6>
                <small class="text-muted">لوحة التحكم الإدارية</small>
            </div>

            <!-- Side Navigation Links -->
            <nav class="d-flex flex-column gap-1 p-2">
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   :title="!isExpanded ? 'لوحة التحكم الرئيسية' : ''">
                    <i class="fa-solid fa-gauge-high fs-5"></i>
                    <span class="sidebar-text-label">لوحة التحكم الرئيسية</span>
                </a>

                <a href="{{ route('admin.settings') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'إعدادات الموقع' : ''">
                    <i class="fa-solid fa-sliders fs-5"></i>
                    <span class="sidebar-text-label">إعدادات الموقع</span>
                </a>

                <a href="{{ route('admin.applications.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.applications.index') || request()->routeIs('admin.applications.edit') ? 'active' : '' }}"
                   :title="!isExpanded ? 'سجل طلبات التعادل' : ''">
                    <i class="fa-solid fa-list-check fs-5"></i>
                    <span class="sidebar-text-label">سجل طلبات التعادل</span>
                </a>

                <a href="{{ route('admin.committee.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.committee*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'مواضيع اللجنة العامة' : ''">
                    <i class="fa-solid fa-users-rectangle fs-5"></i>
                    <span class="sidebar-text-label">مواضيع اللجنة العامة</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'التقارير السنوية' : ''">
                    <i class="fa-solid fa-chart-pie fs-5"></i>
                    <span class="sidebar-text-label">التقارير السنوية</span>
                </a>

                <a href="{{ route('admin.search.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.search*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'بحث المتقدمين' : ''">
                    <i class="fa-solid fa-magnifying-glass fs-5"></i>
                    <span class="sidebar-text-label">بحث المتقدمين</span>
                </a>

                <a href="{{ route('admin.decisions.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.decisions*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'إصدار القرارات الرسمية' : ''">
                    <i class="fa-solid fa-stamp fs-5"></i>
                    <span class="sidebar-text-label">إصدار القرارات الرسمية</span>
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

    <!-- 3. OFFICIAL FOOTER -->
    <footer class="mohe-footer-institutional">
        <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-semibold">© {{ date('Y') }} مجلس التعليم العالي - الجمهورية العربية السورية. جميع الحقوق محفوظة.</span>
            </div>
            <div class="d-flex gap-4">
                <a href="#" class="text-secondary text-decoration-none hover-navy">عن المجلس</a>
                <a href="#" class="text-secondary text-decoration-none hover-navy">سياسة الخصوصية</a>
                <a href="#" class="text-secondary text-decoration-none hover-navy">اتصل بنا</a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

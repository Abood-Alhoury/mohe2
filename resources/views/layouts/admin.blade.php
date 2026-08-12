<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة وقرارات معادلة الشهادات') - مجلس التعليم العالي</title>
    
    <!-- Site Icon / Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('assets/logo.jpg') }}">
    
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

        .shadow-ambient {
            box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05);
        }

        .mohe-footer-institutional {
            background-color: #e4e2e4;
            color: #44474D;
            border-top: 1px solid var(--outline-variant);
            padding: 1.25rem 2rem;
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* Global System-Wide Print Optimization Rules */
        @media print {
            @page {
                size: auto;
                margin: 8mm 10mm;
            }
            .mohe-header, 
            .mohe-sidebar-nav, 
            .mohe-footer-institutional, 
            .executive-footer, 
            header, 
            aside, 
            footer, 
            nav, 
            .no-print, 
            .no-print-zone,
            .btn, 
            button, 
            .alert {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            body, main, .container-fluid {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                box-shadow: none !important;
            }
            .moz-wrapper {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                border-top: none !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-[#F9F9FF]" x-data="{ isExpanded: localStorage.getItem('sidebar_expanded') === 'true' }" x-init="$watch('isExpanded', val => localStorage.setItem('sidebar_expanded', val))">

    <!-- 1. TOP HEADER (UNTOUCHED LOGO, TITLE, TOGGLE BUTTON & USER MENU) -->
    <header class="mohe-header">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                    <!-- Circular Gold Ring Logo Emblem with Hover Glow -->
                    <div class="mohe-emblem-ring">
                        <img src="{{ asset('assets/logo.jpg') }}" 
                             alt="وزارة التعليم العالي" 
                             onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge" style="background: rgba(197,160,89,0.2); color: #FED488; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; border: 1px solid rgba(197,160,89,0.35); border-radius: 4px; padding: 2px 7px;">
                                الجمهورية العربية السورية | وزارة التعليم العالي
                            </span>
                        </div>
                        <h1 class="brand-title">مجلس التعليم العالي والتعادل الأكاديمي</h1>
                        <p class="brand-subtitle">نظام الإدارة المركزية والقرارات الوزارية لمعادلة الشهادات والدرجات العلمية</p>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                @php
                    $siteLocked = \App\Models\SiteSetting::get('site_locked', '0') === '1';
                @endphp
                @if($siteLocked)
                    <span class="status-badge" style="background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.35); color: #fca5a5;">
                        <span class="status-dot pulse-red"></span>
                        <span>الموقع مغلق للجامعات</span>
                    </span>
                @else
                    <span class="status-badge" style="background: rgba(34,197,94,0.12); border-color: rgba(34,197,94,0.35); color: #86efac;">
                        <span class="status-dot pulse-green"></span>
                        <span>النظام متاح وشغال</span>
                    </span>
                @endif

                <!-- ADMIN NOTIFICATIONS DROPDOWN BUTTON -->
                <div class="dropdown">
                    <button class="btn position-relative p-2" type="button" id="adminNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false" 
                            style="border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(197, 160, 89, 0.4); backdrop-filter: blur(6px);" 
                            title="مركز التنبيهات والإشعارات القادمة من الجامعات">
                        <i class="fa-regular fa-bell fs-5" style="color: var(--heritage-gold-light);"></i>
                        @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem; box-shadow: 0 0 8px rgba(220,38,38,0.6);">
                                {{ $adminNotifications->count() }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg py-0 border-0" aria-labelledby="adminNotifDropdown" style="width: 330px; border-top: 3px solid var(--heritage-gold) !important; margin-top: 8px;">
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold" style="color: var(--imperial-navy);"><i class="fa-solid fa-bell me-1" style="color: var(--heritage-gold);"></i> إشعارات ورسائل الجامعات</h6>
                            @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                                <span class="badge bg-danger">{{ $adminNotifications->count() }} جديدة</span>
                            @endif
                        </div>
                        <div style="max-height: 330px; overflow-y: auto;">
                            @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                                @foreach($adminNotifications as $notif)
                                    <a href="{{ route('admin.applications.index') }}?open_message={{ $notif->application_id }}" class="dropdown-item p-3 border-bottom text-wrap">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-warning text-dark fs-8">{{ $notif->application->application_no ?? ('طلب #' . $notif->application_id) }}</span>
                                            <small class="text-muted fs-8">{{ $notif->created_at ? $notif->created_at->diffForHumans() : '' }}</small>
                                        </div>
                                        <div class="small fw-bold text-dark mb-1">
                                            {{ $notif->application->candidate->full_name ?? 'المرشح' }}
                                            <span class="text-muted fw-normal" style="font-size: 0.78rem;">({{ optional($notif->application->workUniversity)->name ?? 'جامعة مسجلة' }})</span>
                                        </div>
                                        <div class="text-muted text-truncate fs-7" style="max-width: 290px;">{{ $notif->message }}</div>
                                    </a>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-regular fa-bell-slash fs-2 mb-2 d-block" style="color: var(--heritage-gold);"></i>
                                    لا توجد إشعارات أو رسائل جديدة حالياً
                                </div>
                            @endif
                        </div>
                        <div class="p-2 border-top text-center bg-light">
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-link fw-bold text-decoration-none" style="color: var(--imperial-navy);">
                                استعراض كافة المحادثات والرسائل <i class="fa-solid fa-arrow-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

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
                            <a class="user-dropdown-item" href="{{ route('admin.messages.index') }}">
                                <i class="fa-regular fa-comments text-primary fs-5"></i>
                                <span>مركز المحادثات والرسائل</span>
                            </a>
                            <a class="user-dropdown-item" href="{{ route('admin.settings') }}">
                                <i class="fa-solid fa-gear text-secondary fs-5"></i>
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
            <!-- MIDDLE-EDGE TOGGLE ARROW BUTTON -->
            <button type="button" 
                    @click="isExpanded = !isExpanded" 
                    class="sidebar-edge-toggle" 
                    :title="isExpanded ? 'طَي القائمة الجانبية' : 'توسيع القائمة الجانبية'">
                <i class="fa-solid fa-chevron-left" :class="{ 'rotated': isExpanded }"></i>
            </button>
            
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
                    <div class="sidebar-icon-tile tile-amber">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <span class="sidebar-text-label">لوحة التحكم الرئيسية</span>
                </a>

                <a href="{{ route('admin.settings') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'إعدادات الموقع' : ''">
                    <div class="sidebar-icon-tile tile-indigo">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <span class="sidebar-text-label">إعدادات الموقع</span>
                </a>

                <a href="{{ route('admin.applications.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.applications.index') || request()->routeIs('admin.applications.edit') ? 'active' : '' }}"
                   :title="!isExpanded ? 'سجل طلبات التعادل' : ''">
                    <div class="sidebar-icon-tile tile-emerald">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <span class="sidebar-text-label">سجل طلبات التعادل</span>
                </a>

                <a href="{{ route('admin.interviews.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.interviews*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'بانتظار المقابلات' : ''">
                    <div class="sidebar-icon-tile" style="background: #ecfeff; color: #0891b2; border: 1px solid #cff4fc;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <span class="sidebar-text-label">بانتظار المقابلات</span>
                    @php
                        $awaitingInterviewsBadge = \App\Models\Application::where('status', 'بانتظار المقابلة')->count();
                    @endphp
                    @if($awaitingInterviewsBadge > 0)
                        <span class="badge rounded-pill ms-auto" style="font-size: 0.72rem; background-color: #0891b2 !important; color: #ffffff !important;">{{ $awaitingInterviewsBadge }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.messages.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.messages*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'سجل الرسائل والمحادثات' : ''">
                    <div class="sidebar-icon-tile tile-sky">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <span class="sidebar-text-label">سجل الرسائل والمحادثات</span>
                </a>

                <a href="{{ route('admin.committee.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.committee*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'مواضيع اللجنة العامة' : ''">
                    <div class="sidebar-icon-tile tile-purple">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                    <span class="sidebar-text-label">مواضيع اللجنة العامة</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'التقارير السنوية' : ''">
                    <div class="sidebar-icon-tile tile-rose">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <span class="sidebar-text-label">التقارير السنوية</span>
                </a>

                <a href="{{ route('admin.search.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.search*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'بحث المتقدمين' : ''">
                    <div class="sidebar-icon-tile tile-teal">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <span class="sidebar-text-label">بحث المتقدمين</span>
                </a>

                <a href="{{ route('admin.decisions.index') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.decisions*') ? 'active' : '' }}"
                   :title="!isExpanded ? 'إصدار القرارات الرسمية' : ''">
                    <div class="sidebar-icon-tile tile-amber">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <span class="sidebar-text-label">إصدار القرارات الرسمية</span>
                </a>

            </nav>
        </aside>

        <!-- MAIN CANVAS -->
        <main class="flex-grow-1 p-4">
            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" id="autoDismissAlertAdmin" role="alert" style="border-right: 4px solid #059669 !important;">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(function() {
                            var alertEl = document.getElementById('autoDismissAlertAdmin');
                            if (alertEl) {
                                alertEl.style.transition = 'opacity 0.6s ease';
                                alertEl.style.opacity = '0';
                                setTimeout(function() { alertEl.remove(); }, 600);
                            }
                        }, 2500);
                    });
                </script>
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

    <!-- Global Max 2MB File Size Validation -->
    <script>
        document.addEventListener('change', function(e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file') {
                const file = e.target.files[0];
                if (file) {
                    const maxBytes = 2 * 1024 * 1024; // 2 MB
                    if (file.size > maxBytes) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        alert('⚠️ عذراً! حجم الملف المرفق (' + fileSizeMB + ' ميغابايت) يتجاوز الحد الأقصى المسموح به (2 ميغابايت).\nيرجى اختيار ملف بحجم أصغر لضمان عدم امتلاء السيرفر.');
                        e.target.value = '';
                    }
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

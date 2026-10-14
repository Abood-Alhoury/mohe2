<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'بوابة الخدمات الإلكترونية للجامعات') - مجلس التعليم العالي</title>
    
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

    <!-- Custom MoHE Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/mohe.css') }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
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

        /* ==========================================================================
           MODERN CYBER-GOV / LUXURY NAVY & GOLD SIDEBAR
           ========================================================================== */
        .modern-cyber-sidebar {
            width: 76px;
            background: #ffffff;
            border-left: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: -4px 0 24px rgba(14, 30, 56, 0.04);
            transition: width 0.28s cubic-bezier(0.34, 1.25, 0.64, 1);
            position: sticky;
            top: 0;
            min-height: calc(100vh - 75px);
            z-index: 50;
            overflow: visible !important;
            display: flex;
            flex-direction: column;
        }

        .modern-cyber-sidebar.expanded {
            width: 275px;
        }

        /* Floating Pill Collapse Trigger */
        .sidebar-pill-toggle {
            position: absolute;
            top: 24px;
            left: -14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(14, 30, 56, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1.5px solid #c59b27;
            color: #f5d77f;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(14, 30, 56, 0.25), 0 0 10px rgba(197, 155, 39, 0.2);
            z-index: 105;
            transition: all 0.25s ease;
            outline: none !important;
        }

        .sidebar-pill-toggle:hover {
            transform: scale(1.15);
            background: #0e1e38;
            box-shadow: 0 4px 16px rgba(197, 155, 39, 0.45);
            color: #ffffff;
        }

        .sidebar-pill-toggle i {
            font-size: 0.72rem;
            transition: transform 0.3s ease;
            color: #f5d77f;
        }

        .sidebar-pill-toggle i.rotated {
            transform: rotate(180deg);
        }

        /* University Profile Header */
        .sidebar-uni-brand {
            padding: 22px 14px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.25s ease;
        }

        .modern-cyber-sidebar:not(.expanded) .sidebar-uni-brand {
            padding: 20px 8px 10px;
        }

        /* Gold Halo Glow Frame */
        .uni-avatar-frame {
            position: relative;
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0e1e38 0%, #1a365d 100%);
            border: 2px solid #c59b27;
            box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.18), 0 0 18px rgba(197, 155, 39, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .modern-cyber-sidebar:not(.expanded) .uni-avatar-frame {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            margin-bottom: 0;
        }

        .uni-icon-wrap {
            font-size: 1.45rem;
            color: #f5d77f;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modern-cyber-sidebar:not(.expanded) .uni-icon-wrap {
            font-size: 1.2rem;
        }

        /* Online Indicator with Green Pulse */
        .online-indicator {
            position: absolute;
            bottom: -2px;
            left: -2px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.8);
            animation: pulseOnline 2s infinite;
        }

        @keyframes pulseOnline {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Identity Text */
        .uni-identity-text {
            width: 100%;
            overflow: hidden;
        }

        .modern-cyber-sidebar:not(.expanded) .uni-identity-text {
            display: none;
        }

        .uni-username {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0e1e38;
            margin: 0 0 2px;
            letter-spacing: -0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .uni-fullname {
            font-size: 0.76rem;
            color: #64748b;
            display: block;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Verified Badge */
        .uni-status-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 12px;
            padding: 2px 10px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Faded Gradient Divider */
        .sidebar-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(197, 155, 39, 0.35), transparent);
            margin: 8px 16px 14px;
            border: none;
        }

        .modern-cyber-sidebar:not(.expanded) .sidebar-divider {
            margin: 10px 8px;
        }

        /* Navigation List */
        .sidebar-nav-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 10px;
        }

        .modern-cyber-sidebar:not(.expanded) .sidebar-nav-list {
            padding: 0 8px;
            gap: 10px;
        }

        /* Nav Link Item */
        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: 12px;
            text-decoration: none !important;
            transition: all 0.24s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            color: #334155;
        }

        .modern-cyber-sidebar:not(.expanded) .nav-item-link {
            justify-content: center;
            padding: 6px 0;
        }

        .nav-item-link .nav-text {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            transition: color 0.2s ease;
            flex: 1;
        }

        .modern-cyber-sidebar:not(.expanded) .nav-item-link .nav-text {
            display: none;
        }

        /* Active State */
        .nav-item-link.active {
            background: rgba(14, 30, 56, 0.04) !important;
        }

        .nav-item-link.active .nav-text {
            color: #0e1e38;
            font-weight: 800;
        }

        .nav-item-link.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: #c59b27;
            border-radius: 4px 0 0 4px;
            box-shadow: 0 0 10px rgba(197, 155, 39, 0.65);
        }

        /* Micro-Interactions on Hover */
        .nav-item-link:hover {
            transform: translateX(-4px);
            background: rgba(14, 30, 56, 0.03);
        }

        .nav-item-link:hover .nav-text {
            color: #0e1e38;
        }

        .modern-cyber-sidebar:not(.expanded) .nav-item-link:hover {
            transform: scale(1.06);
        }

        /* Squircle Icon Base */
        .nav-icon-squircle {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .nav-item-link:hover .nav-icon-squircle {
            transform: scale(1.08);
        }

        /* Squircle Color Themes */
        /* 1. Dashboard: Navy & Gold */
        .nav-icon-squircle.icon-navy {
            background: linear-gradient(135deg, #0e1e38 0%, #1e3a68 100%);
            color: #f5d77f;
            box-shadow: 0 3px 10px rgba(14, 30, 56, 0.22);
        }
        .nav-item-link:hover .icon-navy {
            box-shadow: 0 4px 15px rgba(197, 155, 39, 0.35);
        }

        /* 2. New Application: Emerald */
        .nav-icon-squircle.icon-emerald {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.24) 100%);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .nav-item-link:hover .icon-emerald {
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        }

        /* 3. Saved Drafts: Amber */
        .nav-icon-squircle.icon-amber {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.24) 100%);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .nav-item-link:hover .icon-amber {
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        }

        /* 4. Messages & Notifications: Sky */
        .nav-icon-squircle.icon-sky {
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.12) 0%, rgba(2, 132, 199, 0.24) 100%);
            color: #0284c7;
            border: 1px solid rgba(2, 132, 199, 0.25);
        }
        .nav-item-link:hover .icon-sky {
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
        }

        /* 5. Required Documents: Rose */
        .nav-icon-squircle.icon-rose {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.12) 0%, rgba(244, 63, 94, 0.24) 100%);
            color: #e11d48;
            border: 1px solid rgba(244, 63, 94, 0.25);
        }
        .nav-item-link:hover .icon-rose {
            box-shadow: 0 4px 15px rgba(244, 63, 94, 0.35);
        }

        /* 6. Support: Cyan */
        .nav-icon-squircle.icon-cyan {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.12) 0%, rgba(6, 182, 212, 0.24) 100%);
            color: #0891b2;
            border: 1px solid rgba(6, 182, 212, 0.25);
        }
        .nav-item-link:hover .icon-cyan {
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.35);
        }

        /* 7. Archive: Gold */
        .nav-icon-squircle.icon-gold {
            background: linear-gradient(135deg, rgba(197, 155, 39, 0.15) 0%, rgba(197, 155, 39, 0.28) 100%);
            color: #b48518;
            border: 1px solid rgba(197, 155, 39, 0.35);
        }
        .nav-item-link:hover .icon-gold {
            box-shadow: 0 4px 15px rgba(197, 155, 39, 0.35);
        }

        /* Counter Badges */
        .nav-counter-badge {
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .nav-counter-badge.danger {
            background: #ef4444;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
        }

        .nav-counter-badge.emerald {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
        }

        .nav-counter-badge.amber {
            background: #f59e0b;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
        }

        .modern-cyber-sidebar:not(.expanded) .nav-counter-badge {
            position: absolute;
            top: 4px;
            right: 14px;
            padding: 2px 5px;
            font-size: 0.65rem;
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
        
        <!-- GLOBAL SIDEBAR FOR UNIVERSITY PORTAL -->
        @include('university.layouts.sidebar')

        <!-- MAIN CANVAS -->
        <main class="flex-grow-1 p-4">
            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" id="autoDismissAlertUni" role="alert" style="border-right: 4px solid #059669 !important;">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(function() {
                            var alertEl = document.getElementById('autoDismissAlertUni');
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- 3. OFFICIAL EXECUTIVE FOOTER -->
    @include('partials.footer')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global File Size Validation (5MB for Theses/Dissertations/Abstracts, 2MB for Standard Files) -->
    <script>
        document.addEventListener('change', function(e) {
            if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file') {
                const file = e.target.files[0];
                if (file) {
                    const name = (e.target.name || '').toLowerCase();
                    const id = (e.target.id || '').toLowerCase();
                    const labelText = (e.target.closest('div')?.querySelector('label')?.textContent || '') + ' ' +
                                      (e.target.closest('.field-group')?.querySelector('label')?.textContent || '') + ' ' +
                                      (e.target.closest('.border')?.querySelector('label')?.textContent || '') + ' ' +
                                      (e.target.closest('.upload-field-row')?.querySelector('span')?.textContent || '') + ' ' +
                                      (e.target.getAttribute('title') || '');

                    // 5MB allowed for Master's thesis / PhD dissertation / abstracts / full thesis
                    const is5MB = (
                        e.target.dataset.maxMb === '5' ||
                        e.target.dataset.maxSize === '5' ||
                        ['file_thesis_summary', 'file_thesis_abstract', 'file_master_thesis_abstract', 'file_phd_thesis_abstract', 'file_phd_full_thesis', 'file_ma_thesis_summary', 'file_thesis', 'file_dissertation'].includes(name) ||
                        name.includes('thesis') || name.includes('abstract') || name.includes('dissertation') ||
                        id.includes('thesissummary') || id.includes('thesisabstract') || id.includes('fullthesis') ||
                        ((labelText.includes('رسالة') || labelText.includes('أطروحة') || labelText.includes('ملخص')) && !labelText.includes('إيصال') && !name.includes('receipt'))
                    );

                    const maxLimitMB = is5MB ? 5 : 2;
                    const maxBytes = maxLimitMB * 1024 * 1024;

                    if (file.size > maxBytes) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        alert('⚠️ عذراً! حجم الملف المرفق (' + fileSizeMB + ' ميغابايت) يتجاوز الحد الأقصى المسموح به (' + maxLimitMB + ' ميغابايت).\nيرجى اختيار ملف بحجم أصغر لضمان عدم امتلاء السيرفر.');
                        e.target.value = '';
                    }
                }
            }
        });
    </script>

    <!-- Native System Calendar & DD/MM/YYYY Format & Alert Dismissal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="date"]').forEach(function(input) {
                input.setAttribute('lang', 'en-GB'); // Renders day/month/year order natively in browser
                input.style.direction = 'ltr';
                input.style.textAlign = 'right';
            });
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-bs-dismiss="alert"]');
            if (btn) {
                const alertEl = btn.closest('.alert');
                if (alertEl) {
                    alertEl.style.transition = 'opacity 0.2s ease';
                    alertEl.style.opacity = '0';
                    setTimeout(() => alertEl.remove(), 200);
                }
            }
        });
    </script>

    <!-- Universal Instant Table Search (AJAX / Client-Side Live Filter with Zero Page Reload) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="search"]').forEach(function(input) {
                const form = input.closest('form');
                if (!form || form.id === 'recent-apps-search-form' || form.id === 'admin-apps-search-form' || form.id === 'drafts-search-form') {
                    return;
                }

                const cardOrSection = form.closest('.card, .card-academic-table, .container, main') || document.body;
                const table = cardOrSection.querySelector('table');

                if (table) {
                    const tbody = table.querySelector('tbody');
                    if (tbody) {
                        input.addEventListener('input', function() {
                            const term = this.value.trim().toLowerCase();
                            const rows = tbody.querySelectorAll('tr:not(.client-search-empty-row)');
                            let visibleCount = 0;

                            rows.forEach(function(row) {
                                const text = row.innerText.toLowerCase();
                                if (!term || text.includes(term)) {
                                    row.style.display = '';
                                    visibleCount++;
                                } else {
                                    row.style.display = 'none';
                                }
                            });

                            let emptyMsg = tbody.querySelector('.client-search-empty-row');
                            if (visibleCount === 0 && term) {
                                if (!emptyMsg) {
                                    const colCount = table.querySelectorAll('thead th').length || 7;
                                    emptyMsg = document.createElement('tr');
                                    emptyMsg.className = 'client-search-empty-row';
                                    emptyMsg.innerHTML = `<td colspan="${colCount}" class="text-center py-4 text-muted fs-7">لم يتم العثور على أي نتائج مطابقة لعبارة البحث: <strong>"${term}"</strong>.</td>`;
                                    tbody.appendChild(emptyMsg);
                                }
                            } else if (emptyMsg) {
                                emptyMsg.remove();
                            }
                        });

                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            input.dispatchEvent(new Event('input'));
                        });
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

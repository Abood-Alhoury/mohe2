<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة وقرارات معادلة الشهادات') - مجلس التعليم العالي</title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom MoHE Theme -->
    <link rel="stylesheet" href="{{ asset('assets/css/mohe.css') }}">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body>

    <!-- Header Banner -->
    <header class="mohe-header py-3">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <!-- Ministry Emblem SVG Icon -->
                    <div class="mohe-emblem bg-white p-2 rounded-circle shadow-sm" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid me-0 fa-landmark text-success fs-2" style="color: #0f392b !important;"></i>
                    </div>
                    <div>
                        <div class="brand-title">وزارة التعليم العالي والبحث العلمي</div>
                        <div class="brand-subtitle"><i class="fa-solid fa-graduation-cap me-1"></i> مجلس التعليم العالي - نظام إدارة وقرارات معادلة الشهادات العلمية</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    @php
                        $siteLocked = \App\Models\SiteSetting::get('site_locked', '0') === '1';
                    @endphp
                    @if($siteLocked)
                        <span class="badge bg-danger fs-6 p-2 shadow-sm"><i class="fa-solid fa-lock me-1"></i> الموقع مغلق للجامعات</span>
                    @else
                        <span class="badge bg-success fs-6 p-2 shadow-sm"><i class="fa-solid fa-lock-open me-1"></i> النظام متاح وشغال</span>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle font-bold" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-shield text-warning me-1"></i> مدير التعادل (Admin)
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear me-2 text-primary"></i> إعدادات الموقع</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i> تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="mohe-nav shadow-sm mb-4">
        <div class="container-fluid px-4">
            <ul class="nav nav-pills me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-house me-1"></i> الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                        <i class="fa-solid fa-sliders me-1"></i> إعدادات الموقع
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.applications.index') || request()->routeIs('admin.applications.edit') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}">
                        <i class="fa-solid fa-file-signature me-1"></i> طلبات التعادل
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.committee*') ? 'active' : '' }}" href="{{ route('admin.committee.index') }}">
                        <i class="fa-solid fa-users-rectangle me-1"></i> مواضيع اللجنة العامة
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                        <i class="fa-solid fa-chart-pie me-1"></i> الإحصائيات والتقارير
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.search*') ? 'active' : '' }}" href="{{ route('admin.search.index') }}">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> بحث حسب
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.decisions*') ? 'active' : '' }}" href="{{ route('admin.decisions.index') }}">
                        <i class="fa-solid fa-stamp me-1"></i> إرسال قرار التعادل
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container-fluid px-4 pb-5">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted mt-auto">
        <div class="container">
            <small>جميع الحقوق محفوظة &copy; {{ date('Y') }} - وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي - جمهورية سوريا العربية</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

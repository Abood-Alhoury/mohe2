<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'بوابة الخدمات الإلكترونية للجامعات') - مجلس التعليم العالي</title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom MoHE Theme CSS -->
    <link class="mohe-css-link" rel="stylesheet" href="{{ asset('assets/css/mohe.css') }}">
    @stack('styles')
</head>
<body>

    <!-- Header Banner -->
    <header class="mohe-header py-3">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <!-- Official MoHE Logo -->
                    <div class="mohe-emblem">
                        <img src="{{ asset('assets/logo.jpg') }}" alt="وزارة التعليم العالي">
                    </div>
                    <div class="brand-info-wrapper">
                        <div class="brand-title">وزارة التعليم العالي والبحث العلمي</div>
                        <div class="brand-subtitle"><i class="fa-solid fa-building-columns me-1"></i> البوابة الإلكترونية للجامعات السورية - نظام تعادل الشهادات</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    
                    <!-- Notifications Dropdown Center -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light position-relative p-2" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-regular fa-bell fs-5"></i>
                            @if(isset($notifications) && $notifications->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                    {{ $notifications->count() }}
                                </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg notification-dropdown py-0" aria-labelledby="notifDropdown">
                            <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-bell me-1"></i> الإشعارات والرسائل</h6>
                                @if(isset($notifications) && $notifications->count() > 0)
                                    <span class="badge bg-danger">{{ $notifications->count() }} جديدة</span>
                                @endif
                            </div>
                            <div style="max-height: 320px; overflow-y: auto;">
                                @if(isset($notifications) && $notifications->count() > 0)
                                    @foreach($notifications as $notif)
                                        <a href="{{ route('university.messages') }}" class="notification-item unread">
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
                            <div class="p-2 border-top text-center">
                                <a href="{{ route('university.messages') }}" class="btn btn-sm btn-link text-primary fw-bold text-decoration-none">
                                    استعراض كافة المراسلات <i class="fa-solid fa-arrow-left ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle fw-bold" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-university text-warning me-1"></i> 
                            {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li>
                                <div class="dropdown-header text-dark fw-bold border-bottom pb-2">
                                    {{ Auth::user()->university->name ?? 'جامعة مسجلة' }}
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('university.messages') }}">
                                    <i class="fa-regular fa-envelope me-2 text-primary"></i> مركز المراسلات
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item py-2 text-danger fw-bold" type="submit">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> تسجيل الخروج
                                    </button>
                                </form>
                            </li>
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
                    <a class="nav-link {{ request()->routeIs('university.dashboard') ? 'active' : '' }}" href="{{ route('university.dashboard') }}">
                        <i class="fa-solid fa-chart-line me-1"></i> لوحة التحكم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('university.apply*') ? 'active' : '' }}" href="{{ route('university.apply.options') }}">
                        <i class="fa-solid fa-file-circle-plus me-1"></i> تقديم معاملة تعادل
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('university.messages') ? 'active' : '' }}" href="{{ route('university.messages') }}">
                        <i class="fa-solid fa-comments me-1"></i> المراسلات والإشعارات
                        @if(isset($notifications) && $notifications->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $notifications->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container-fluid px-4 pb-5 flex-grow-1">
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

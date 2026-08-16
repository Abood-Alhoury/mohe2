<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مجلس التعليم العالي - بوابة تعادل الشهادات العلمية</title>
    
    <!-- Site Icon / Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('assets/logo.jpg') }}">
    
    <!-- Google Fonts: IBM Plex Sans Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --imperial-navy: #1A2A44;
            --imperial-navy-dark: #04152E;
            --imperial-navy-light: #24395c;
            --heritage-gold: #C5A059;
            --heritage-gold-dark: #a3813c;
            --heritage-gold-light: #FED488;
            --surface-bg: #F9F9FF;
            --surface-card: #FFFFFF;
            --outline-variant: #C5C6CE;
            --on-surface: #111C2C;
            --on-variant: #44474D;
        }

        body {
            font-family: 'IBM Plex Sans Arabic', system-ui, -apple-system, sans-serif;
            background-color: var(--surface-bg);
            color: var(--on-surface);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* 1. Official Minimalist Header */
        .mohe-header {
            background-color: var(--imperial-navy);
            border-bottom: 2px solid var(--heritage-gold);
            padding: 0.75rem 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(4, 21, 46, 0.15);
        }

        .header-title-main {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #ffffff;
        }

        .header-subtitle {
            font-size: 0.8rem;
            color: var(--heritage-gold-light);
            font-weight: 500;
        }

        .header-badge {
            background: rgba(197, 160, 89, 0.15);
            border: 1px solid var(--heritage-gold);
            color: var(--heritage-gold-light);
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
        }

        /* Main Container Layout */
        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background: radial-gradient(circle at 10% 20%, rgba(26, 42, 68, 0.03) 0%, transparent 60%),
                        radial-gradient(circle at 90% 80%, rgba(197, 160, 89, 0.04) 0%, transparent 60%);
        }

        .login-card-container {
            max-width: 1020px;
            width: 100%;
            background: var(--surface-card);
            border-radius: 4px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            border-top: 4px solid var(--heritage-gold);
            box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.06), 0px 1px 3px rgba(26, 42, 68, 0.03);
            overflow: hidden;
        }

        /* 2. Side Panel - Navy Section */
        .login-side-panel {
            background: linear-gradient(145deg, var(--imperial-navy-dark) 0%, var(--imperial-navy) 100%);
            color: #ffffff;
            padding: 3.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            border-left: 3px solid var(--heritage-gold);
        }

        /* Logo Styling & Alignment (PNG Emblem with Gold Circular Ring) */
        .logo-container-centered {
            width: 115px;
            height: 115px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            border-radius: 50%;
            border: 2.5px solid var(--heritage-gold);
            padding: 4px;
            background-color: #ffffff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        }

        .logo-img-png {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .side-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .side-subtitle {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--heritage-gold-light);
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        /* Description text pushed down as requested */
        .side-description {
            font-size: 0.92rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 1.5rem;
            padding-top: 0.75rem;
            border-top: 1px dashed rgba(197, 160, 89, 0.3);
            text-align: justify;
            text-align-last: center;
        }

        .side-footer-info {
            margin-top: auto;
            padding-top: 2rem;
            font-size: 0.82rem;
            color: var(--heritage-gold-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* 3. Form Section */
        .login-form-panel {
            padding: 3rem 2.75rem;
            background-color: #ffffff;
        }

        .badge-official {
            background-color: rgba(197, 160, 89, 0.12);
            color: var(--imperial-navy);
            border: 1px solid var(--heritage-gold);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 1.25rem;
        }

        .form-heading {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--imperial-navy);
            margin-bottom: 0.4rem;
        }

        .form-subheading {
            font-size: 0.92rem;
            color: var(--on-variant);
            margin-bottom: 2rem;
        }

        /* Input Controls */
        .academic-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--imperial-navy);
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group-academic {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-right {
            position: absolute;
            right: 14px;
            color: var(--imperial-navy-light);
            font-size: 1rem;
            z-index: 5;
            pointer-events: none;
        }

        .form-control-academic {
            width: 100%;
            padding: 0.75rem 2.6rem 0.75rem 2.6rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--on-surface);
            background-color: #ffffff;
            border: 1px solid var(--outline-variant);
            border-radius: 4px;
            transition: all 0.2s ease-in-out;
        }

        .form-control-academic:focus {
            border-color: var(--imperial-navy) !important;
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.3) !important;
            outline: none !important;
        }

        .btn-toggle-password {
            position: absolute;
            left: 12px;
            background: none;
            border: none;
            color: #75777E;
            font-size: 0.95rem;
            cursor: pointer;
            z-index: 5;
            padding: 4px;
        }

        .btn-toggle-password:hover {
            color: var(--imperial-navy);
        }

        /* Call-To-Action Button: Heritage Gold Background with Imperial Navy Text */
        .btn-primary-cta {
            width: 100%;
            background-color: var(--heritage-gold);
            color: var(--imperial-navy);
            font-weight: 700;
            font-size: 1rem;
            padding: 0.85rem 1.5rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(197, 160, 89, 0.25);
        }

        .btn-primary-cta:hover {
            background-color: var(--heritage-gold-dark);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(197, 160, 89, 0.35);
        }

        .btn-primary-cta:active {
            transform: scale(0.99);
        }

        /* Checkbox & Links */
        .form-check-input-academic {
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 3px;
            border: 1px solid var(--outline-variant);
            cursor: pointer;
        }

        .form-check-input-academic:checked {
            background-color: var(--imperial-navy);
            border-color: var(--imperial-navy);
        }

        .link-forgot-password {
            color: var(--imperial-navy);
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .link-forgot-password:hover {
            color: var(--heritage-gold-dark);
            text-decoration: underline;
        }

        /* Support Slab */
        .support-info-slab {
            margin-top: 2.25rem;
            padding: 1rem 1.25rem;
            background-color: var(--surface-bg);
            border-radius: 4px;
            border-right: 3px solid var(--heritage-gold);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .support-info-text {
            font-size: 0.83rem;
            color: var(--on-variant);
            line-height: 1.5;
        }

        .support-info-title {
            font-weight: 700;
            color: var(--imperial-navy);
            font-size: 0.88rem;
            margin-bottom: 2px;
        }

        /* 4. Footer Styling */
        .mohe-footer {
            background-color: var(--imperial-navy);
            border-top: 2px solid var(--heritage-gold);
            color: rgba(255, 255, 255, 0.8);
            padding: 1.25rem 2rem;
            font-size: 0.85rem;
            margin-top: auto;
        }

        .footer-link {
            color: var(--heritage-gold-light);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- 1. OFFICIAL MINIMALIST HEADER -->
    <header class="mohe-header">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex flex-column">
                    <span class="header-title-main">الجمهورية العربية السورية - وزارة التعليم العالي والبحث العلمي</span>
                    <span class="header-subtitle"><i class="fa-solid fa-building-columns me-1"></i> مجلس التعليم العالي - بوابة تعادل الشهادات العلمية للجامعات</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <span class="header-badge">
                    <i class="fa-solid fa-shield-halved me-1"></i> بوابة حكومية رسمية
                </span>
                <span class="text-white-50 d-none d-md-inline" style="font-size: 0.8rem;">
                    <i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y') }}
                </span>
            </div>
        </div>
    </header>

    <!-- MAIN BODY SECTION -->
    <main class="main-wrapper">
        <div class="login-card-container">
            <div class="row g-0">
                
                <!-- 2. LEFT SIDE PANEL (IMPERIAL NAVY & CENTERED ENLARGED LOGO) -->
                <div class="col-lg-5 login-side-panel d-none d-lg-flex">
                    
                    <!-- CENTERED ENLARGED LOGO (TRANSPARENT BACKGROUND & PURE CENTER ALIGNMENT) -->
                    <div class="logo-container-centered">
                        <img src="{{ asset('assets/logo.jpg') }}" 
                             alt="وزارة التعليم العالي ومجلس التعليم العالي" 
                             class="logo-img-png"
                             onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                    </div>

                    <!-- TITLES BELOW LOGO -->
                    <h2 class="side-title">مجلس التعليم العالي</h2>
                    <h4 class="side-subtitle">بوابة تعادل الشهادات العلمية للجامعات</h4>

                    <!-- PUSHED DOWN DESCRIPTION TEXT AS REQUESTED BY USER -->
                    <p class="side-description">
                        تتيح هذه البوابة المعتمدة للجامعات السورية الحكومية والخاصة إرسال طلبات تعادل شهادات الماجستير والدكتوراه للمرشحين للتدريس، ومتابعة حالة الطلبات وإجراءات الإدارة والمراسلات الثنائية بشكل مؤتمت بالكامل.
                    </p>

                    <!-- SIDE FOOTER CONTACT INFO -->
                    <div class="side-footer-info">
                        <i class="fa-solid fa-headset text-warning"></i>
                        <span>الدعم الفني المباشر: support@mohe.gov.sy</span>
                    </div>

                </div>

                <!-- 3. RIGHT FORM PANEL (LOGIN CARD) -->
                <div class="col-lg-7 login-form-panel">
                    
                    <!-- MOBILE LOGO TREATMENT (CENTERED ABOVE CARD ON SMALL SCREENS) -->
                    <div class="d-lg-none text-center mb-4">
                        <div class="logo-container-centered" style="width: 100px; height: 100px; margin-bottom: 0.75rem;">
                            <img src="{{ asset('assets/logo.jpg') }}" 
                                 alt="وزارة التعليم العالي" 
                                 class="logo-img-png"
                                 onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                        </div>
                        <h3 class="fw-bold" style="color: var(--imperial-navy); font-size: 1.25rem;">مجلس التعليم العالي</h3>
                        <p class="text-muted small mb-0">بوابة تعادل الشهادات العلمية للجامعات</p>
                    </div>

                    <!-- CARD BADGE -->
                    <div class="badge-official">
                        <i class="fa-solid fa-lock text-warning"></i>
                        <span>نظام التعادل الإلكتروني - تسجيل الدخول المعتمد</span>
                    </div>
                    
                    <h1 class="form-heading">أهلاً بكم في النظام</h1>
                    <p class="form-subheading">الرجاء إدخال بيانات الاعتماد الرسمية للوصول إلى لوحة التحكم الخاصة بالجامعة أو الإدارة.</p>

                    <!-- VALIDATION ALERTS -->
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4" style="background-color: #ffdad6; color: #93000a; border-radius: 4px;" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation fs-5"></i>
                                <span class="fw-semibold">{{ $errors->first() }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- LOGIN FORM -->
                    <form action="{{ url('/login') }}" method="POST">
                        @csrf
                        
                        <!-- Email / Username Input -->
                        <div class="mb-3.5">
                            <label for="email" class="academic-label">
                                <i class="fa-regular fa-envelope me-1 text-secondary"></i> البريد الإلكتروني أو اسم المستخدم
                            </label>
                            <div class="input-group-academic">
                                <i class="fa-solid fa-user input-icon-right"></i>
                                <input type="email" 
                                       class="form-control-academic" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="university.user@mohe.gov.sy" 
                                       required 
                                       autofocus 
                                       autocomplete="username">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3.5 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="academic-label mb-0">
                                    <i class="fa-solid fa-key me-1 text-secondary"></i> كلمة المرور
                                </label>
                                <a href="#" class="link-forgot-password">نسيت كلمة المرور؟</a>
                            </div>
                            <div class="input-group-academic">
                                <i class="fa-solid fa-lock input-icon-right"></i>
                                <input type="password" 
                                       class="form-control-academic" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••" 
                                       required 
                                       autocomplete="current-password">
                                <button type="button" class="btn-toggle-password" id="togglePasswordBtn" title="إظهار/إخفاء كلمة المرور">
                                    <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="d-flex align-items-center justify-content-between my-4">
                            <div class="form-check d-flex align-items-center gap-2 m-0">
                                <input type="checkbox" class="form-check-input-academic" id="remember" name="remember">
                                <label class="form-check-label text-secondary fw-medium" for="remember" style="font-size: 0.88rem; cursor: pointer;">
                                    تذكر بيانات الدخول في هذا الجهاز المحمي
                                </label>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON: Heritage Gold Background with Imperial Navy Text -->
                        <button type="submit" class="btn-primary-cta">
                            <span>تسجيل الدخول للنظام</span>
                            <i class="fa-solid fa-arrow-left me-1"></i>
                        </button>
                    </form>

                    <!-- QUICK HELP / SUPPORT SLAB -->
                    <div class="support-info-slab">
                        <i class="fa-solid fa-circle-info text-warning fs-5 mt-1"></i>
                        <div class="support-info-text">
                            <div class="support-info-title">هل تحتاج إلى حساب جديد للجامعة؟</div>
                            <div>يتم إنشاء الحسابات للجامعات المعتمدة وتعديل صلاحيات المستشارين واللجان حصراً من قبل مدير النظام في وزارة التعليم العالي.</div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- 4. OFFICIAL FOOTER -->
    <footer class="mohe-footer">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span>جميع الحقوق محفوظة © {{ date('Y') }} <strong>مجلس التعليم العالي - وزارة التعليم العالي والبحث العلمي</strong> - الجمهورية العربية السورية.</span>
            </div>
            <div class="d-flex align-items-center gap-4">
                <a href="#" class="footer-link"><i class="fa-solid fa-headset me-1"></i> الدعم الفني</a>
                <a href="#" class="footer-link"><i class="fa-solid fa-shield-cat me-1"></i> سياسة الخصوصية</a>
                <a href="#" class="footer-link"><i class="fa-solid fa-file-contract me-1"></i> شروط الاستخدام</a>
            </div>
        </div>
    </footer>

    <!-- Interactive Password Visibility Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    if (isPassword) {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
</body>
</html>

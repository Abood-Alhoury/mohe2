<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مجلس التعليم العالي</title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Montserrat:wght@300;400;600;700&display=swap');
        
        :root {
            --mohe-navy: #1E3A5F;
            --mohe-navy-dark: #14263f;
            --mohe-navy-light: #2b4f7c;
            --mohe-gold: #C9B037;
            --mohe-gold-light: #e0ca58;
            --mohe-gold-dark: #9e8825;
            --mohe-bg: #f8fafc;
        }

        body {
            font-family: 'Cairo', system-ui, sans-serif;
            background-color: var(--mohe-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Abstract Background Circles */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(30, 58, 95, 0.08) 0%, rgba(30, 58, 95, 0) 70%);
            top: -200px;
            right: -100px;
            z-index: -1;
        }

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201, 176, 55, 0.05) 0%, rgba(201, 176, 55, 0) 70%);
            bottom: -250px;
            left: -150px;
            z-index: -1;
        }

        .login-container {
            max-width: 950px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(30, 58, 95, 0.1);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .login-info-side {
            background: linear-gradient(135deg, var(--mohe-navy-dark) 0%, var(--mohe-navy) 100%);
            color: #ffffff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-left: 5px solid var(--mohe-gold);
        }

        .login-info-side::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(201, 176, 55, 0.05);
            top: -100px;
            left: -100px;
        }

        .login-form-side {
            padding: 3rem;
        }

        .login-title {
            font-weight: 800;
            color: var(--mohe-navy);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 700;
            color: var(--mohe-navy-dark);
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--mohe-navy);
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
        }

        .btn-login {
            background-color: var(--mohe-navy);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            width: 100%;
            transition: background 0.3s, transform 0.2s;
            box-shadow: 0 4px 10px rgba(30, 58, 95, 0.25);
        }

        .btn-login:hover {
            background-color: var(--mohe-navy-dark);
            color: white;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .login-card-badge {
            background-color: rgba(201, 176, 55, 0.15);
            color: var(--mohe-gold-dark);
            border: 1px solid var(--mohe-gold);
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .emblem-wrapper {
            width: 80px;
            height: 80px;
            background-color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            overflow: hidden;
            padding: 5px;
        }

        .quick-help {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="row g-0">
            <!-- Left Info Side -->
            <div class="col-lg-5 login-info-side d-none d-lg-flex">
                <div class="emblem-wrapper">
                    <img src="{{ asset('assets/logo.jpg') }}" alt="وزارة التعليم العالي" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h3 class="fw-extrabold mb-3">مجلس التعليم العالي</h3>
                <h5 class="text-warning fw-bold mb-4">بوابة تعادل الشهادات العلمية للجامعات</h5>
                <p class="text-white-50 leading-relaxed small mb-5">
                    تتيح هذه البوابة للجامعات السورية الحكومية والخاصة إرسال طلبات تعادل شهادات الماجستير والدكتوراه للمرشحين للتدريس، ومتابعة حالة الطلبات وإجراءات الإدارة والمراسلات الثنائية بشكل مؤتمت بالكامل.
                </p>
                <div class="mt-auto fs-7 text-white-50">
                    <i class="fa-solid fa-circle-info me-1"></i> الدعم الفني: support@mohe.gov.sy
                </div>
            </div>

            <!-- Right Form Side -->
            <div class="col-lg-7 login-form-side">
                <div class="d-lg-none text-center mb-4">
                    <div class="bg-white rounded-circle shadow-sm p-1 mx-auto mb-2" style="width: 70px; height: 70px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('assets/logo.jpg') }}" alt="وزارة التعليم العالي" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <h4 class="fw-bold text-primary">مجلس التعليم العالي</h4>
                    <h6 class="text-muted">بوابة تعادل الشهادات للجامعات</h6>
                </div>

                <span class="login-card-badge">
                    <i class="fa-solid fa-lock me-1"></i> تسجيل دخول آمن
                </span>
                
                <h2 class="login-title">أهلاً بكم في النظام</h2>
                <p class="login-subtitle">الرجاء إدخال بيانات الاعتماد للوصول إلى لوحة التحكم الخاصة بالجامعة أو الإدارة.</p>

                <!-- Validation Alerts -->
                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> 
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label mb-2"><i class="fa-regular fa-envelope me-1 text-secondary"></i> البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="username@example.com" required autofocus autocomplete="username">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="form-label mb-0"><i class="fa-solid fa-key me-1 text-secondary"></i> كلمة المرور</label>
                            <a href="#" class="fs-8 text-primary text-decoration-none fw-bold">نسيت كلمة المرور؟</a>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label fs-7 text-secondary fw-bold" for="remember">تذكرني في هذا المتصفح</label>
                    </div>

                    <button type="submit" class="btn-login py-3">
                        تسجيل الدخول <i class="fa-solid fa-arrow-left ms-2"></i>
                    </button>
                </form>

                <div class="quick-help">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-question text-warning fs-5"></i>
                        <div>
                            <strong>هل تحتاج إلى حساب جديد للجامعة؟</strong>
                            <div class="text-muted fs-8 mt-1">يتم إنشاء الحسابات للجامعات المعتمدة وتعديلها حصراً من قبل مدير النظام في وزارة التعليم العالي.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>

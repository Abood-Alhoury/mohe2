<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الإحصائيات الشاملة لطلبات التعادل - وزارة التعليم العالي</title>
    
    <!-- Google Fonts: IBM Plex Sans Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom MoHE Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/mohe.css') }}">

    <style>
        :root {
            --imperial-navy: #1A2A44;
            --imperial-navy-dark: #04152E;
            --heritage-gold: #C5A059;
            --surface-bg: #F9F9FF;
            --outline-variant: #C5C6CE;
        }

        body {
            font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif;
            background-color: #ffffff;
            color: #111C2C;
            padding: 30px;
        }

        .header-box {
            border-bottom: 3px double var(--heritage-gold);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .logo-emblem-ring {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid var(--heritage-gold);
            padding: 2px;
            background-color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .logo-emblem-ring img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .report-card {
            border: 1px solid var(--outline-variant);
            border-top: 3px solid var(--heritage-gold);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0px 4px 20px rgba(26, 42, 68, 0.05);
        }

        .report-card-header {
            background-color: var(--imperial-navy);
            color: white;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <div class="no-print print-btn">
        <button onclick="window.print()" class="btn btn-warning btn-lg shadow fw-bold text-dark" style="background-color: var(--heritage-gold) !important; border: none;">
            <i class="fa-solid fa-print me-2"></i> طباعة / حفظ كـ PDF
        </button>
    </div>

    <!-- Official Header -->
    <div class="header-box text-center">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-start">
                <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">الجمهورية العربية السورية</h5>
                <h6 class="fw-bold text-muted mb-0">وزارة التعليم العالي والبحث العلمي</h6>
                <small class="text-secondary">مجلس التعليم العالي - مديرية تعادل الشهادات</small>
            </div>
            <div>
                <div class="logo-emblem-ring" style="border: none; background: transparent; box-shadow: none;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="Logo" style="width: 75px; height: 75px; object-fit: contain;">
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold" style="color: var(--imperial-navy);">التاريخ: {{ date('d/m/Y') }}</div>
                <div class="text-muted fs-7">التقرير الإحصائي العام</div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <h3 class="fw-bold text-center mb-4" style="color: var(--imperial-navy);">
        التقرير الإحصائي الشامل لطلبات معادلة الشهادات
    </h3>

    <div class="row g-4">
        <!-- University Breakdown -->
        <div class="col-6">
            <div class="report-card">
                <div class="report-card-header text-end">
                    <i class="fa-solid fa-building-columns me-2" style="color: var(--heritage-gold-light);"></i> الإحصائيات حسب الجامعات
                </div>
                <div class="p-3">
                    <table class="table table-academic text-center mb-0">
                        <thead>
                            <tr>
                                <th>الجامعة</th>
                                <th>عدد الطلبات</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($universityBreakdown as $ub)
                            @php
                                $perc = $totalApps > 0 ? round(($ub->applications_count / $totalApps) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold text-end">{{ $ub->name }}</td>
                                <td class="fw-bold" style="color: var(--imperial-navy);">{{ $ub->applications_count }}</td>
                                <td>%{{ $perc }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="col-6">
            <div class="report-card">
                <div class="report-card-header text-end">
                    <i class="fa-solid fa-chart-pie me-2" style="color: var(--heritage-gold-light);"></i> الإحصائيات حسب حالة المعاملة
                </div>
                <div class="p-3">
                    <table class="table table-academic text-center mb-0">
                        <thead>
                            <tr>
                                <th>حالة المعاملة</th>
                                <th>عدد الطلبات</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusBreakdown as $stName => $stData)
                            @php
                                $stCount = $stData['count'];
                                $perc = $totalApps > 0 ? round(($stCount / $totalApps) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold text-end">{{ $stName }}</td>
                                <td class="fw-bold" style="color: var(--imperial-navy);">{{ $stCount }}</td>
                                <td>%{{ $perc }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="alert alert-secondary text-center fw-bold mt-3 border-0" style="background-color: #f5f3f5; color: var(--imperial-navy); border-right: 4px solid var(--heritage-gold) !important;">
        إجمالي عدد طلبات التعادل المسجلة في النظام: {{ $totalApps }} طلب
    </div>

    <!-- Signatures -->
    <div class="row mt-5 text-center">
        <div class="col-6">
            <h6 class="fw-bold" style="color: var(--imperial-navy);">رئيس قسم الإحصاء والمعلومات</h6>
            <div style="height: 50px;"></div>
            <p class="text-muted">..............................</p>
        </div>
        <div class="col-6">
            <h6 class="fw-bold" style="color: var(--imperial-navy);">مدير تعادل الشهادات الجامعية</h6>
            <div style="height: 50px;"></div>
            <p class="text-muted">..............................</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>

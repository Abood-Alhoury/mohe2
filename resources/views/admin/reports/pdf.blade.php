<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الإحصائيات الشاملة لطلبات التعادل - وزارة التعليم العالي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #1e293b;
            padding: 30px;
        }
        .header-box {
            border-bottom: 3px double #C9B037;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .logo-img {
            max-height: 80px;
        }
        .report-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        .report-card-header {
            background-color: #C9B037;
            color: white;
            padding: 12px 20px;
            font-weight: bold;
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
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow fw-bold">
            <i class="fa-solid fa-print me-2"></i> طباعة / حفظ كـ PDF
        </button>
    </div>

    <!-- Official Header -->
    <div class="header-box text-center">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-start">
                <h5 class="fw-bold mb-1">الجمهورية العربية السورية</h5>
                <h6 class="fw-bold text-muted mb-0">وزارة التعليم العالي والبحث العلمي</h6>
                <small class="text-secondary">مجلس التعليم العالي - مديرية تعادل الشهادات</small>
            </div>
            <div>
                <img src="{{ asset('assets/logo.jpg') }}" alt="Logo" class="logo-img">
            </div>
            <div class="text-end">
                <div class="fw-bold">التاريخ: {{ date('Y-m-d') }}</div>
                <div class="text-muted fs-7">التقرير الإحصائي العام</div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <h3 class="fw-bold text-center mb-4 text-dark">
        التقرير الإحصائي الشامل لطلبات معادلة الشهادات
    </h3>

    <div class="row g-4">
        <!-- University Breakdown -->
        <div class="col-6">
            <div class="report-card">
                <div class="report-card-header text-end">
                    <i class="fa-solid fa-building-columns me-2"></i> الإحصائيات حسب الجامعات
                </div>
                <div class="p-3">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
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
                                <td class="fw-bold text-primary">{{ $ub->applications_count }}</td>
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
                    <i class="fa-solid fa-chart-pie me-2"></i> الإحصائيات حسب حالة المعاملة
                </div>
                <div class="p-3">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
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
                                <td class="fw-bold text-primary">{{ $stCount }}</td>
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
    <div class="alert alert-secondary text-center fw-bold mt-3">
        إجمالي عدد طلبات التعادل المسجلة في النظام: {{ $totalApps }} طلب
    </div>

    <!-- Signatures -->
    <div class="row mt-5 text-center">
        <div class="col-6">
            <h6 class="fw-bold">رئيس قسم الإحصاء والمعلومات</h6>
            <div style="height: 50px;"></div>
            <p class="text-muted">..............................</p>
        </div>
        <div class="col-6">
            <h6 class="fw-bold">مدير تعادل الشهادات الجامعية</h6>
            <div style="height: 50px;"></div>
            <p class="text-muted">..............................</p>
        </div>
    </div>

    <script>
        // Auto trigger print dialog when page loads in new tab
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>

@extends('layouts.university')

@section('title', 'الرئيسية - لوحة تحكم الجامعة')

@section('content')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm text-white p-4" style="background: linear-gradient(135deg, var(--mohe-navy), var(--mohe-navy-dark)); border-radius: 15px;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h3 class="fw-extrabold mb-1"><i class="fa-solid fa-graduation-cap text-warning me-2"></i> مرحباً بك، {{ $universityName }}!</h3>
                    <p class="mb-0 text-white-50">أهلاً بك في بوابة الخدمات الإلكترونية المخصصة للجامعات السورية لتعادل الشهادات العلمية والتدريس.</p>
                </div>
                <div class="bg-white bg-opacity-10 p-3 rounded-3 text-center">
                    <span class="fs-8 text-white-50 d-block mb-1">حالة الاعتماد</span>
                    @if($user->card_status === 'yellow_card')
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold fs-7"><i class="fa-solid fa-triangle-exclamation me-1"></i> بطاقة صفراء (مراجعة الإدارة)</span>
                    @else
                        <span class="badge bg-success px-3 py-2 fw-bold fs-7"><i class="fa-solid fa-circle-check me-1"></i> معتمد ونشط</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Yellow Card Alert Notice -->
@if($user->card_status === 'yellow_card')
<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert" style="border-right: 5px solid #d97706 !important;">
    <i class="fa-solid fa-circle-exclamation fs-3 me-3 text-warning"></i>
    <div>
        <h6 class="alert-heading fw-bold mb-1">⚠️ تنبيه: حساب الجامعة لديه بطاقة صفراء (أنت لست معتمداً بالكامل)</h6>
        <p class="mb-0 small text-muted">حساب الجامعة الحالي لديه بطاقة صفراء من مدير التعادل. يمكنك مواصلة العمل، ولكن يرجى استكمال النواقص والتواصل مع الإدارة لرفع التجميد الجزئي.</p>
    </div>
</div>
@endif

<!-- Stats Counters -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #1E3A5F, #14263f); border-radius: 12px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">إجمالي طلبات التعادل المرسلة</h6>
                    <h2 class="card-title mb-0 font-extrabold fs-1">{{ number_format($totalApps) }}</h2>
                </div>
                <div class="fs-1 text-white-50 bg-white bg-opacity-10 p-3 rounded-circle" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-radius: 12px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">طلبات قيد الدراسة</h6>
                    <h2 class="card-title mb-0 font-extrabold fs-1">{{ number_format($underStudyCount) }}</h2>
                </div>
                <div class="fs-1 text-white-50 bg-white bg-opacity-10 p-3 rounded-circle" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #059669, #047857); border-radius: 12px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="card-subtitle text-white-50 mb-1 fw-bold">القرارات الصادرة والمقبولة</h6>
                    <h2 class="card-title mb-0 font-extrabold fs-1">{{ number_format($approvedCount) }}</h2>
                </div>
                <div class="fs-1 text-white-50 bg-white bg-opacity-10 p-3 rounded-circle" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Grid (Inspired by Legacy System Grid and design proposals) -->
<h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-cubes me-1"></i> بوابات الإجراءات السريعة</h5>
<div class="row g-3 mb-5">
    <div class="col-md-3">
        <a href="{{ route('university.apply.options') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card">
            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-file-circle-plus" style="color: var(--mohe-navy);"></i></div>
            <h6 class="fw-bold text-dark mb-1">تعادل جديد</h6>
            <p class="small text-muted mb-0">تقديم طلب معادلة جديد لمرشح للتدريس</p>
        </a>
    </div>

    <div class="col-md-3">
        <a href="#" class="card text-center p-4 h-100 text-decoration-none mohe-card" data-bs-toggle="modal" data-bs-target="#transferModal">
            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-arrows-spin" style="color: var(--mohe-navy);"></i></div>
            <h6 class="fw-bold text-dark mb-1">تحويل معادلة</h6>
            <p class="small text-muted mb-0">تحويل طلب تعادل من جامعة لأخرى</p>
        </a>
    </div>

    <div class="col-md-3">
        <a href="#" class="card text-center p-4 h-100 text-decoration-none mohe-card" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-book-medical" style="color: var(--mohe-navy);"></i></div>
            <h6 class="fw-bold text-dark mb-1">إضافة مقررات</h6>
            <p class="small text-muted mb-0">ربط وتحديد مقررات تدريسية إضافية</p>
        </a>
    </div>

    <div class="col-md-3">
        <a href="{{ route('university.messages') }}" class="card text-center p-4 h-100 text-decoration-none mohe-card">
            <div class="fs-1 text-primary mb-3 position-relative d-inline-block">
                <i class="fa-solid fa-comments" style="color: var(--mohe-navy);"></i>
                @if($notifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        {{ $notifications->count() }}
                    </span>
                @endif
            </div>
            <h6 class="fw-bold text-dark mb-1">المراسلات والإشعارات</h6>
            <p class="small text-muted mb-0">التواصل المباشر مع مدير التعادل بالوزارة</p>
        </a>
    </div>

    <div class="col-md-3">
        <a href="#" class="card text-center p-4 h-100 text-decoration-none mohe-card" data-bs-toggle="modal" data-bs-target="#searchModal">
            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-magnifying-glass" style="color: var(--mohe-navy);"></i></div>
            <h6 class="fw-bold text-dark mb-1">بحث سريع</h6>
            <p class="small text-muted mb-0">البحث بالاسم أو الرقم الوطني للمرشح</p>
        </a>
    </div>

    <div class="col-md-3">
        <a href="#" class="card text-center p-4 h-100 text-decoration-none mohe-card" data-bs-toggle="modal" data-bs-target="#requiredPapersModal">
            <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-folder-closed" style="color: var(--mohe-navy);"></i></div>
            <h6 class="fw-bold text-dark mb-1">الأوراق المطلوبة للتعادل</h6>
            <p class="small text-muted mb-0">دليل المستندات والثبوتيات الرسمية</p>
        </a>
    </div>
</div>

<!-- Recent Applications Table -->
<div class="mohe-card">
    <div class="mohe-card-header">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check fs-5 text-primary"></i>
            <h5 class="mohe-card-title">آخر الطلبات المقدمة من الجامعة لمجلس التعليم العالي</h5>
        </div>
        <div>
            <a href="{{ route('university.apply.options') }}" class="btn btn-sm btn-mohe-gold">
                <i class="fa-solid fa-plus me-1"></i> تقديم طلب جديد
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mohe-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">رقم الطلب</th>
                        <th>المرشح (الطالب)</th>
                        <th>نوع التعادل</th>
                        <th>القسم المرشح له</th>
                        <th>حالة الطلب</th>
                        <th>تاريخ التقديم</th>
                        <th style="width: 150px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentApplications as $app)
                    <tr>
                        <td class="fw-bold text-secondary">{{ $app->application_no ?? $app->id }}</td>
                        <td class="fw-bold text-primary">{{ $app->candidate->full_name ?? 'غ/م' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold">{{ $app->request_type ?? 'تعادل' }}</span>
                        </td>
                        <td>{{ $app->work_faculty ?? '' }} - {{ $app->work_department ?? 'غ/م' }}</td>
                        <td>
                            @if($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                                <span class="badge-status badge-study">تحت التدقيق الأولي</span>
                            @elseif($app->status == 'بانتظار الوثائق')
                                <span class="badge-status badge-paper">بانتظار الوثائق</span>
                            @elseif($app->status == 'تم الصدور' || $app->status == 'موافقة')
                                <span class="badge-status badge-approved">تم الصدور</span>
                            @elseif($app->status == 'معلق')
                                <span class="badge-status badge-suspended">معلق</span>
                            @elseif($app->status == 'مرفوض')
                                <span class="badge-status badge-rejected">مرفوض</span>
                            @else
                                <span class="badge-status badge-study">{{ $app->status }}</span>
                            @endif
                        </td>
                        <td class="text-muted fs-7">{{ $app->created_at ? $app->created_at->format('Y-m-d') : 'غ/م' }}</td>
                        <td>
                            <a href="{{ route('university.messages') }}" class="btn btn-sm btn-outline-primary" title="المراسلات">
                                <i class="fa-solid fa-comments"></i> مراسلة
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">لا توجد طلبات مقدمة حالياً من قبلكم.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Transfer Modal Placeholder -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-arrows-spin me-1"></i> طلب تحويل معاملة تعادل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-hourglass-start text-warning fs-1 mb-3"></i>
                <p class="fw-bold">بوابة تحويل المعاملات قيد التطوير البرمجي حالياً.</p>
                <p class="text-muted small">يرجى مراجعة إدارة الوزارة يدوياً لحين إتاحة الخدمة الإلكترونية قريباً.</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal Placeholder -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-book-medical me-1"></i> إضافة مقررات إضافية للمرشحين</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-hourglass-start text-warning fs-1 mb-3"></i>
                <p class="fw-bold">بوابة إدارة وتعديل مقررات المرشحين الإضافية قيد التطوير.</p>
                <p class="text-muted small">يمكنك إضافة وتعديل المقررات حالياً أثناء ملء الطلب الجديد في الخطوة الخامسة.</p>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> بحث سريع في المعاملات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('university.messages') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المرشح أو الرقم الوطني</label>
                        <input type="text" name="search" class="form-control" placeholder="أدخل اسم الطالب للبحث..." required>
                    </div>
                    <button type="submit" class="btn btn-mohe-primary w-100 py-2">بحث وعرض</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required Papers Modal (The 13 papers requirement list) -->
<div class="modal fade" id="requiredPapersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-folder-closed me-1"></i> قائمة الأوراق والثبوتيات المطلوبة لمعادلة الماجستير السورية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-secondary mb-3">يجب توفير ورفع صور مصدقة واضحة بصيغة PDF للمستندات التالية أثناء التقديم:</p>
                <ol class="list-group list-group-numbered fs-9" style="padding-right: 0;">
                    <li class="list-group-item">صورة عن قرار معادلة الشهادة الثانوية (إذا كانت الشهادة الثانوية غير سورية).</li>
                    <li class="list-group-item">صورة عن معادلة الشهادة الجامعية الأولى (إذا كانت غير سورية).</li>
                    <li class="list-group-item">كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</li>
                    <li class="list-group-item">نسخة مصدقة أصولاً عن شهادة الماجستير.</li>
                    <li class="list-group-item">وثيقة مصدقة أصولاً متضمنة تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة (الماجستير).</li>
                    <li class="list-group-item">ملخص باللغة العربية عن رسالة الماجستير إلكترونياً.</li>
                    <li class="list-group-item">شهادة خبرة تدريسية لا تقل عن سنتين ما بعد الحصول على الدرجة العلمية.</li>
                    <li class="list-group-item">العقود وإيصالات الرواتب مصدقة أصولاً تثبت الخبرة.</li>
                    <li class="list-group-item">شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</li>
                    <li class="list-group-item">السيرة الذاتية للمرشح متضمنة عنوان إقامته وعنوان ورقم هاتفه.</li>
                    <li class="list-group-item">إيصال تسديد رسم تعادل بقيمة 100,000 ل.س من حملة درجة الماجستير.</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

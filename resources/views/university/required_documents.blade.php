@extends('layouts.university')
@section('title', 'اللائحة التنفيذية للأوراق الثبوتية والشهادات المطلوبة للتعادل - مجلس التعليم العالي')

@section('content')
<!-- System Design Title Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--imperial-navy);">
            <i class="fa-solid fa-file-contract me-2" style="color: var(--heritage-gold);"></i>
            اللائحة التنفيذية للأوراق والثبوتيات المطلوبة للتعادل
        </h1>
        <p class="text-muted small mb-0">دليل شروط ومستندات التعادل المعتمدة أصولاً بموجب التعميم رقم (150 / ص) بتاريخ 2024/05/12</p>
    </div>
    <div>
        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fa-solid fa-print me-1"></i> طباعة اللائحة الرسمية
        </button>
    </div>
</div>

<!-- Executive Circular Notice Alert -->
<div class="alert alert-warning border-0 shadow-sm mb-4 p-3 d-flex align-items-start gap-3" style="background-color: #fffdf5; border-right: 5px solid var(--heritage-gold) !important; color: #5c4300;">
    <i class="fa-solid fa-triangle-exclamation fs-3 mt-1" style="color: var(--heritage-gold);"></i>
    <div>
        <h6 class="fw-bold mb-1" style="color: var(--imperial-navy);">تأكيد هـام جداً لمدراء الموارد البشرية وشؤون أعضاء الهيئة التدريسية:</h6>
        <p class="mb-0 fs-7 text-muted">
            بناءً على توجيهات أمين مجلس التعليم العالي ومدير التعادل والإنتاج العلمي، <strong>يرجى التأكيد الصارم على عدم إرسال أي ملف أو معاملة مرشح للوزارة إلا بعد استكمال كافة متطلبات الشروط والوثائق المطلوبة</strong> المدرجة أدناه بحسب كل درجة علمية أصولاً.
        </p>
    </div>
</div>

<!-- Category Tabs Header -->
<div class="bg-white p-2 rounded shadow-sm border mb-4">
    <ul class="nav nav-pills nav-fill gap-1" id="docTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-2.5 fs-7" id="sy-master-tab" data-bs-toggle="tab" data-bs-target="#sy-master" type="button" role="tab">
                <i class="fa-solid fa-graduation-cap me-1"></i> الماجستير (جامعة سورية)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-2.5 fs-7" id="foreign-master-tab" data-bs-toggle="tab" data-bs-target="#foreign-master" type="button" role="tab">
                <i class="fa-solid fa-globe me-1"></i> الماجستير (جامعة غير سورية)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-2.5 fs-7" id="sy-phd-tab" data-bs-toggle="tab" data-bs-target="#sy-phd" type="button" role="tab">
                <i class="fa-solid fa-user-graduate me-1"></i> الدكتوراه (جامعة سورية)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-2.5 fs-7" id="foreign-phd-tab" data-bs-toggle="tab" data-bs-target="#foreign-phd" type="button" role="tab">
                <i class="fa-solid fa-microscope me-1"></i> الدكتوراه (غير سورية) والإنتاج
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-2.5 fs-7" id="gov-faculty-tab" data-bs-toggle="tab" data-bs-target="#gov-faculty" type="button" role="tab">
                <i class="fa-solid fa-building-columns me-1"></i> أعضاء الهيئة التدريسية
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-2.5 fs-7" id="general-rules-tab" data-bs-toggle="tab" data-bs-target="#general-rules" type="button" role="tab">
                <i class="fa-solid fa-shield-check me-1"></i> تصديقات الوثائق الخارجية
            </button>
        </li>
    </ul>
</div>

<!-- Tab Contents -->
<div class="tab-content mb-5" id="docTabsContent">

    <!-- 1. M.Sc Syrian University -->
    <div class="tab-pane fade show active" id="sy-master" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid var(--imperial-navy) !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-graduation-cap me-2 text-warning"></i>
                        الوثائق المطلوبة لتعادل درجة الماجستير (الصادرة عن جامعة سورية)
                    </h5>
                    <p class="text-muted small mb-0">للمرشحين للتعيين في الهيئة التدريسية بالجامعات الخاصة السورية</p>
                </div>
                <div class="badge bg-success-subtle text-success fs-7 px-3 py-2 border border-success-subtle rounded-pill">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> رسم التعادل المطلوب: <strong>100,000 ل.س</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <ul class="list-group list-group-flush fs-7 p-0 m-0">
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">1</span>
                            <div><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية موجه أصولاً لمجلس التعليم العالي.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">2</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">3</span>
                            <div><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى:</strong> (شهادة البكالوريوس أو الليسانس).</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">4</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الماجستير.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">5</span>
                            <div><strong>وثيقة مصدقة أصولاً متضمنة التواريخ العلمية:</strong> (تاريخ التسجيل بالدرجة، تاريخ المناقشة، وتاريخ الحصول على الشهادة).</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">6</span>
                            <div><strong>ملخص باللغة العربية عن رسالة الماجستير:</strong> (مقدم إلكترونياً بصيغة PDF).</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">7</span>
                            <div><strong>شهادة خبرة تدريسية:</strong> لا تقل عن سنتين ما بعد حصوله على الدرجة العلمية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">8</span>
                            <div><strong>العقود وإيصالات الرواتب مصدقة أصولاً:</strong> تثبت ممارسة الخبرة التدريسية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">9</span>
                            <div><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية السورية للمعلوماتية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">10</span>
                            <div><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته الحالية وعنوانه وبريده ورقم هاتفه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-navy p-2 text-white fw-bold" style="background-color: var(--imperial-navy); min-width: 28px; text-align: center;">11</span>
                            <div><strong>إيصال تسديد رسم التعادل:</strong> بقيمة 100,000 ل.س لحملة الماجستير.</div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top text-end">
                <a href="{{ route('university.apply.syrian_masters') }}" class="btn btn-solid-navy px-4 py-2 fw-bold shadow-sm">
                    <i class="fa-solid fa-paper-plane me-1"></i> البدء بتقديم طلب تعادل ماجستير سوري
                </a>
            </div>
        </div>
    </div>

    <!-- 2. M.Sc Foreign University -->
    <div class="tab-pane fade" id="foreign-master" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid #0284c7 !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-globe me-2 text-info"></i>
                        الوثائق المطلوبة لتعادل درجة الماجستير (غير السورية - الخارجية)
                    </h5>
                    <p class="text-muted small mb-0">للمرشحين الحاصلين على شهادات من جامعات عربية أو أجنبية</p>
                </div>
                <div class="badge bg-success-subtle text-success fs-7 px-3 py-2 border border-success-subtle rounded-pill">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> رسم التعادل المطلوب: <strong>100,000 ل.س</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <ul class="list-group list-group-flush fs-7 p-0 m-0">
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">1</span>
                            <div><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">2</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">3</span>
                            <div><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">4</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الماجستير الخارجية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">5</span>
                            <div><strong>وثيقة مصدقة أصولاً متضمنة تواريخ الدراسة:</strong> (تاريخ التسجيل بالدرجة، تاريخ المناقشة، وتاريخ الحصول على الشهادة لكل درجتي الماجستير والبكالوريوس).</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">6</span>
                            <div><strong>ملخص باللغة العربية عن رسالة الماجستير إلكترونياً.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">7</span>
                            <div><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">8</span>
                            <div><strong>صور عن جوازات السفر:</strong> بالإضافة لإبراز جوازات السفر الأصلية للمطابقة.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">9</span>
                            <div><strong>إثبات الإقامة أصولاً:</strong> نموذج إثبات إقامة (يُعطى من مكتب التعادل) أو وثيقة حركة الهجرة والجوازات لبيان الإقامة أثناء الدراسة.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">10</span>
                            <div><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">11</span>
                            <div><strong>إيصال تسديد رسم تعادل بقيمة 100,000 ل.س.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-info p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">12</span>
                            <div><strong>إبراز الشهادات الأصلية:</strong> لكافة المراحل الدراسية عند المطابقة الميدانية.</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Ph.D Syrian University -->
    <div class="tab-pane fade" id="sy-phd" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid var(--heritage-gold) !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-user-graduate me-2" style="color: var(--heritage-gold);"></i>
                        الوثائق المطلوبة لتعادل درجة الدكتوراه (الصادرة عن جامعة سورية)
                    </h5>
                    <p class="text-muted small mb-0">وفحص الإنتاج العلمي للمرشحين للتعيين في الجامعات الخاصة السورية</p>
                </div>
                <div class="badge bg-success-subtle text-success fs-7 px-3 py-2 border border-success-subtle rounded-pill">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> رسم التعادل المطلوب: <strong>125,000 ل.س</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <ul class="list-group list-group-flush fs-7 p-0 m-0">
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">1</span>
                            <div><strong>كتاب صادر عن الجامعة:</strong> يتضمن طلب تقويم درجاته العلمية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">2</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">3</span>
                            <div><strong>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">4</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الماجستير.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">5</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الدكتوراه السورية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">6</span>
                            <div><strong>وثيقة مصدقة أصولاً متضمنة تواريخ التسجيل والمناقشة:</strong> وتاريخ الحصول على الشهادة لكل درجتي الماجستير والدكتوراه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">7</span>
                            <div><strong>ملخص باللغة العربية عن رسالة الدكتوراه إلكترونياً.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">8</span>
                            <div><strong>الكفاءة اللغوية والمعلوماتية:</strong> شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">9</span>
                            <div><strong>السيرة الذاتية للمرشح:</strong> متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">10</span>
                            <div><strong>إيصال تسديد رسم تعادل:</strong> بقيمة 125,000 ل.س لحملة درجة الدكتوراه.</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Ph.D Foreign University & Scientific Production -->
    <div class="tab-pane fade" id="foreign-phd" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid #16a34a !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-microscope me-2 text-success"></i>
                        الوثائق المطلوبة (للتعادل وفحص الإنتاج العلمي) للدكتوراه غير السورية
                    </h5>
                    <p class="text-muted small mb-0">للمرشحين الحاصلين على الدكتوراه الخارجية للتعيين بوظيفة مدرس في الجامعات الخاصة</p>
                </div>
                <div class="badge bg-success-subtle text-success fs-7 px-3 py-2 border border-success-subtle rounded-pill">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> رسم التعادل المطلوب: <strong>125,000 ل.س</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <h6 class="fw-bold mb-3" style="color: var(--imperial-navy);">أولاً: الوثائق الأكاديمية والشخصية المطلوبة (13 بند):</h6>
                    <ul class="list-group list-group-flush fs-7 p-0 m-0">
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">1</span>
                            <div>كتاب صادر عن الجامعة يتضمن طلب تقويم درجاته العلمية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">2</span>
                            <div>نسخة مصدقة أصولاً عن شهادة الدراسة الثانوية السورية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">3</span>
                            <div>نسخة مصدقة أصولاً عن الإجازة الجامعية الأولى.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">4</span>
                            <div>نسخة مصدقة أصولاً عن شهادة الماجستير.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">5</span>
                            <div>نسخة مصدقة أصولاً عن شهادة الدكتوراه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">6</span>
                            <div>وثيقة مصدقة أصولاً متضمنة تاريخ التسجيل بالدرجة وتاريخ المناقشة وتاريخ الحصول على الشهادة لكل درجتي الماجستير والدكتوراه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">7</span>
                            <div>ملخص باللغة العربية عن رسالة الدكتوراه إلكترونياً.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">8</span>
                            <div>شهادة اللغة الإنكليزية من المعهد العالي للغات + شهادة ICDL معتمدة من الجمعية المعلوماتية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">9</span>
                            <div>صور عن جوازات السفر بالإضافة لجوازات السفر الأصلية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">10</span>
                            <div>نموذج إثبات إقامة (تعطى من مكتب التعادل) أو وثيقة حركة الهجرة والجوازات.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">11</span>
                            <div>السيرة الذاتية للمرشح متضمنة عنوان إقامته وعنوانه ورقم هاتفه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">12</span>
                            <div>إيصال تسديد رسم تعادل 125,000 ل.س من حملة درجة الدكتوراه.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <span class="badge rounded-circle bg-success p-2 text-white fw-bold" style="min-width: 28px; text-align: center;">13</span>
                            <div>الشهادات الأصلية للشهادات كافة.</div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-4">
                    <div class="bg-success-subtle p-3.5 rounded border border-success" style="border-right: 4px solid #16a34a !important;">
                        <h6 class="fw-bold mb-2 text-success">
                            <i class="fa-solid fa-book-bookmark me-1"></i> ثانياً: وثائق فحص الإنتاج العلمي (تقدم مع الملف بآن واحد):
                        </h6>
                        <p class="fs-7 text-dark mb-3">للتعيين بوظيفة مدرس من حملة الدكتوراه غير السورية:</p>
                        <ol class="fs-7 text-dark mb-0 ps-3">
                            <li class="mb-2"><strong>ثلاث نسخ</strong> عن أطروحة الدكتوراه.</li>
                            <li><strong>ثلاث ملخصات</strong> عن أطروحة الدكتوراه باللغة العربية لا يقل عدد صفحات كل ملخص عن <strong>(25) صفحة</strong>.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Government University Faculty Members -->
    <div class="tab-pane fade" id="gov-faculty" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid #d97706 !important;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--imperial-navy);">
                        <i class="fa-solid fa-building-columns me-2 text-amber" style="color: #d97706;"></i>
                        الوثائق المطلوبة لأعضاء الهيئة التدريسية بالجامعات الحكومية السورية
                    </h5>
                    <p class="text-muted small mb-0">حملة الدكتوراه من أعضاء الهيئة التدريسية بالجامعات الحكومية للمرشحين للتعيين بالجامعات الخاصة</p>
                </div>
                <div class="badge bg-success-subtle text-success fs-7 px-3 py-2 border border-success-subtle rounded-pill">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> رسم التعادل المطلوب: <strong>125,000 ل.س</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <ul class="list-group list-group-flush fs-7 p-0 m-0">
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">1</span>
                            <div><strong>كتاب صادر عن الجامعة الخاصة:</strong> يتضمن طلب تقويم درجاته العلمية.</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">2</span>
                            <div><strong>نسخة مصدقة أصولاً عن شهادة الدكتوراه.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">3</span>
                            <div><strong>بيان وضع أو بطاقة ذاتية رسمية صادر عن الجامعة الحكومية.</strong></div>
                        </li>
                        <li class="list-group-item d-flex align-items-start gap-3 py-2.5">
                            <span class="badge rounded-circle bg-warning text-dark p-2 fw-bold" style="min-width: 28px; text-align: center;">4</span>
                            <div><strong>إيصال تسديد رسم تعادل:</strong> بقيمة 125,000 ل.س من حملة درجة الدكتوراه.</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. General Rules for Foreign Documents -->
    <div class="tab-pane fade" id="general-rules" role="tabpanel">
        <div class="bg-white p-4 rounded shadow-sm border" style="border-top: 4px solid #dc2626 !important;">
            <h5 class="fw-bold mb-3 text-danger">
                <i class="fa-solid fa-shield-halved me-2"></i>
                الشروط القانونية والتصديقات الإلزامية لجميع الوثائق والشهادات غير السورية
            </h5>
            <p class="text-muted fs-7 mb-4">يجب الالتزام بالضوابط التالية قبل مسح ورفع أي وثيقة غير سورية على المنظومة الإلكترونية:</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3.5 rounded border bg-light h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-stamp text-danger me-1"></i> 1. التوثيق والتصديق الرسمي:</h6>
                        <p class="fs-7 text-muted mb-0">
                            يجب أن تكون جميع الوثائق والشهادات غير السورية مصدقة أصولاً من <strong>وزارة الخارجية والمغتربين في الجمهورية العربية السورية</strong>.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3.5 rounded border bg-light h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-language text-danger me-1"></i> 2. الترجمة القانونية المعتمدة:</h6>
                        <p class="fs-7 text-muted mb-0">
                            يجب أن تكون الوثائق المكتوبة بلغة غير العربية <strong>مترجمة وموثقة أصولاً من وزارة العدل</strong> بالجمهورية العربية السورية.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3.5 rounded border bg-light h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-passport text-danger me-1"></i> 3. جوازات السفر وثيقة حركة الإقامة:</h6>
                        <p class="fs-7 text-muted mb-0">
                            إرفاق صورة جواز السفر بالإضافة لإبراز الجواز الأصلي وتقديم نموذج إثبات إقامة صادر عن مكتب التعادل أو وثيقة حركة هجرة وجوازات قانونية.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3.5 rounded border bg-light h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-file-check text-danger me-1"></i> 4. المطابقة الميدانية والأصلية:</h6>
                        <p class="fs-7 text-muted mb-0">
                            يلتزم مدير الموارد البشرية بالجامعة بإبراز كافة الشهادات والوثائق الأصلية للجنة التعادل بمجلس التعليم العالي عند طلبها للمطابقة.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Footer Actions Row -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white p-3 rounded shadow-sm border">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-print text-secondary fs-5"></i>
        <span class="fs-7 text-muted">يمكن طباعة هذه اللائحة المعتمدة مرجعياً لمكاتب الموارد البشرية بالجامعات.</span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fa-solid fa-print me-1"></i> طباعة اللائحة الرسمية
        </button>
        <a href="{{ route('university.apply.options') }}" class="btn btn-solid-navy btn-sm fw-bold shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> تقديم معاملة جديدة الآن
        </a>
    </div>
</div>
@endsection

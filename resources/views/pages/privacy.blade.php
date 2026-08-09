@extends(Auth::check() && optional(Auth::user()->role)->name === 'admin' ? 'layouts.admin' : 'layouts.university')
@section('title', 'سياسة الخصوصية وأمن البيانات الأكاديمية')

@section('content')
<!-- Hero Title Header -->
<div class="mb-4 text-center py-4 rounded shadow-sm position-relative overflow-hidden" 
     style="background: linear-gradient(135deg, #091728 0%, #152B47 50%, #0B1C33 100%); border-bottom: 3px solid var(--heritage-gold); color: white;">
    <div class="position-relative z-1 py-2">
        <div class="d-inline-flex align-items-center justify-content-center p-2 rounded-circle bg-white shadow-sm mb-2" style="border: 2px solid var(--heritage-gold); width: 64px; height: 64px;">
            <i class="fa-solid fa-shield-halved fs-2" style="color: var(--imperial-navy);"></i>
        </div>
        <h1 class="h3 fw-bold mb-1">سياسة الخصوصية وحماية البيانات الأكاديمية</h1>
        <p class="text-white-50 small mb-0">المعايير الرسمية لحماية وتدقيق معاملات تعادل الشهادات العلمية بمجلس التعليم العالي</p>
    </div>
</div>

<div class="row g-4 mb-5 justify-content-center">
    <div class="col-lg-10">
        <div class="bg-white p-4 p-md-5 rounded shadow-sm border border-secondary-subtle">
            
            <!-- Section 1 -->
            <div class="mb-4 pb-3 border-bottom">
                <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-lock me-2" style="color: var(--heritage-gold);"></i>
                    1. التزام سرية البيانات الشخصية والأكاديمية
                </h5>
                <p class="text-muted lh-base fs-7">
                    تلتزم وزارة التعليم العالي والبحث العلمي ومجلس التعليم العالي بأقصى معايير السرية والأمان في التعامل مع كافة البيانات الواردة في طلبات معادلة الشهادات العلمية، بما يشمل معلومات المرشحين، السجلات الوظيفية، والوثائق الأكاديمية المرفقة.
                </p>
            </div>

            <!-- Section 2 -->
            <div class="mb-4 pb-3 border-bottom">
                <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-file-shield me-2" style="color: var(--heritage-gold);"></i>
                    2. جمع وتوظيف الوثائق والشهادات
                </h5>
                <p class="text-muted lh-base fs-7">
                    يقتصر جمع المستندات المرفقة (شهادات الإجازة، الماجستير، الدكتوراه، وأطروحات البحث العلمي) على الأغراض الرسمية المتمثلة في الدراسة الفنية والتقييم الأكاديمي الصادر عن اللجان المتخصصة بمجلس التعليم العالي، ولا يتم استخدام هذه البيانات خارج النطاق التنظيمي والقانوني المحدد.
                </p>
            </div>

            <!-- Section 3 -->
            <div class="mb-4 pb-3 border-bottom">
                <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-key me-2" style="color: var(--heritage-gold);"></i>
                    3. صيانة الحسابات وتدابير الأمان التكنولوجي
                </h5>
                <ul class="text-muted lh-base fs-7 ps-3">
                    <li class="mb-2">تتم جميع العمليات والمراسلات داخل المنظومة عبر قنوات مشفرة وفق بروتوكولات الأمان القياسية (SSL/TLS).</li>
                    <li class="mb-2">يتحمل كل حساب جامعي مسجل مسؤولية الحفاظ على السرية التامة لبيانات الاعتماد وكلمات المرور الخاصة به.</li>
                    <li>يتم حفظ السجلات الرقمية والقرارات الصادرة بقواعد بيانات مركزية مؤمنة ومحمية ضد الوصول غير المصرح به.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="mb-2">
                <h5 class="fw-bold mb-3" style="color: var(--imperial-navy);">
                    <i class="fa-solid fa-handshake me-2" style="color: var(--heritage-gold);"></i>
                    4. حقوق الجامعات والمؤسسات المسجلة
                </h5>
                <p class="text-muted lh-base fs-7 mb-0">
                    يحق للمؤسسات والجامعات المسجلة الاطلاع المباشر على حالة الطلبات المقدمة من قبلها، واستخراج نسخ القرارات الرسمية الصادرة، والتواصل المباشر مع مديرية المعادلة عبر مركز المحادثات المعتمد بالنظام.
                </p>
            </div>

        </div>
    </div>
</div>
@endsection

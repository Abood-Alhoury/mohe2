<!-- EXECUTIVE INSTITUTIONAL FOOTER COMPONENT -->
<footer class="mohe-footer-executive mt-auto text-white">
    <div class="footer-top-section py-5" style="background: linear-gradient(180deg, #081729 0%, #040c17 100%); border-top: 3px solid var(--heritage-gold);">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row g-4 justify-content-between">
                
                <!-- Col 1: Institutional Identity & Logo -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="mohe-emblem-ring" style="width: 54px; height: 54px;">
                            <img src="{{ asset('assets/logo.jpg') }}" alt="وزارة التعليم العالي" onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
                        </div>
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 1.05rem;">الجمهورية العربية السورية</h6>
                            <p class="small mb-0" style="color: #FED488; font-size: 0.82rem; font-weight: 700;">وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي</p>
                        </div>
                    </div>
                    <p class="text-white-50 small lh-base mb-3" style="font-size: 0.84rem;">
                        البوابة الإلكترونية الرسمية المعتمدة لإدارة ورفع ومتابعة طلبات معادلة الشهادات العلمية والدرجات الأكاديمية الصادرة عن الجامعات العربية والأجنبية.
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-navy-subtle border border-gold-light text-gold px-2.5 py-1.5" style="font-size: 0.75rem; background: rgba(197, 160, 89, 0.15); color: #FED488; border-color: rgba(197, 160, 89, 0.35) !important;">
                            <i class="fa-solid fa-shield-halved me-1" style="color: var(--heritage-gold);"></i> نظام مشفر ومدقق أصولاً
                        </span>
                        <span class="badge bg-navy-subtle border border-secondary text-white-50 px-2.5 py-1.5" style="font-size: 0.75rem; background: rgba(255, 255, 255, 0.08);">
                            <i class="fa-solid fa-certificate me-1 text-info"></i> الإصدار الرسمي v2.5
                        </span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3 pb-2 text-white border-bottom border-secondary-subtle" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-compass me-1" style="color: var(--heritage-gold);"></i> روابط وتنقلات
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.86rem;">
                        @if(Auth::check() && optional(Auth::user()->role)->name === 'admin')
                            <li><a href="{{ route('admin.dashboard') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> الرئيسية والأداء</a></li>
                            <li><a href="{{ route('admin.applications.index') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> سجل المعاملات</a></li>
                            <li><a href="{{ route('admin.messages.index') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> مركز المحادثات</a></li>
                            <li><a href="{{ route('admin.reports.index') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> التقارير الإحصائية</a></li>
                        @else
                            <li><a href="{{ route('university.dashboard') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> الرئيسية والمعاملات</a></li>
                            <li><a href="{{ route('university.apply.options') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> تقديم طلب معادلة جديد</a></li>
                            <li><a href="{{ route('university.messages') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> مركز المراسلات والإشعارات</a></li>
                        @endif
                        <li><a href="{{ route('contact') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> تواصل معنا والتعليمات</a></li>
                        <li><a href="{{ route('privacy') }}" class="footer-link"><i class="fa-solid fa-angle-left me-1 text-gold"></i> سياسة الخصوصية والشروط</a></li>
                    </ul>
                </div>

                <!-- Col 3: Official Contact Channels -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3 pb-2 text-white border-bottom border-secondary-subtle" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-headset me-1" style="color: var(--heritage-gold);"></i> قنوات التواصل الرسمية
                    </h6>
                    <div class="d-flex flex-column gap-2 text-white-50" style="font-size: 0.85rem;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-location-dot mt-1 text-gold"></i>
                            <span>دمشق - المزة - اتستراد المزة - مبنى وزارة التعليم العالي والبحث العلمي</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-phone text-gold"></i>
                            <span dir="ltr" class="text-end">+963 11 211 4500 / +963 11 211 4501</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-envelope text-gold"></i>
                            <span>equivalence@mohe.gov.sy</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-gold"></i>
                            <span>أوقات الدوام الرسمي: الأحد - الخميس (08:00 ص - 03:30 م)</span>
                        </div>
                    </div>
                </div>

                <!-- Col 4: Trust & Verification Seal -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3 pb-2 text-white border-bottom border-secondary-subtle" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-stamp me-1" style="color: var(--heritage-gold);"></i> الاعتمادية والأمان
                    </h6>
                    <div class="p-3 rounded border shadow-sm mb-3" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(197, 160, 89, 0.3) !important;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-building-columns fs-5" style="color: var(--heritage-gold);"></i>
                            <span class="fw-bold text-white fs-7">مجلس التعليم العالي</span>
                        </div>
                        <small class="text-white-50 d-block fs-8">جميع القرارات والمعاملات الصادرة عن هذا النظام موثقة ومحفوظة بالسجلات الرسمية للوزارة.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright Ribbon -->
    <div class="footer-bottom-bar py-3" style="background: #02070e; border-top: 1px solid rgba(197, 160, 89, 0.2); font-size: 0.82rem;">
        <div class="container-fluid px-4 px-lg-5 d-flex flex-wrap justify-content-between align-items-center gap-2 text-white-50">
            <div>
                جميع الحقوق محفوظة © {{ date('Y') }} - <span class="text-white fw-semibold">وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي</span> | الجمهورية العربية السورية
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('privacy') }}" class="footer-sublink">سياسة الخصوصية والأمان</a>
                <span>|</span>
                <a href="{{ route('contact') }}" class="footer-sublink">الدعم الفني والتواصل</a>
            </div>
        </div>
    </div>
</footer>

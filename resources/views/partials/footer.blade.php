<footer class="mt-16 bg-[#0e1e38] text-white border-t-2 border-[#c59b27] pt-8 pb-6 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center pb-8 border-b border-white/10">
            
            <!-- الطرف الأيمن: الشعار والصفة الرسمية -->
            <div class="flex items-center gap-4">
                <img src="{{ asset('assets/logo.jpg') }}" alt="شعار مجلس التعليم العالي" class="w-16 h-16 rounded-full border-2 border-[#c59b27] shadow-lg object-contain bg-white p-0.5 shrink-0">
                <div>
                    <h4 class="text-base font-extrabold text-white tracking-wide m-0">الجمهورية العربية السورية</h4>
                    <h5 class="text-xs font-bold text-[#c59b27] mt-1 mb-1.5">وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي</h5>
                    <p class="text-[11px] text-gray-300 m-0 leading-relaxed max-w-md">
                        البوابة الإلكترونية الرسمية المعتمدة لإدارة ورفع ومتابعة طلبات معادلة الشهادات العلمية والدرجات الأكاديمية الصادرة عن الجامعات العربية والأجنبية.
                    </p>
                </div>
            </div>

            <!-- الطرف الأيسر: قنوات التواصل الرسمية وأوقات الدوام -->
            <div class="md:text-left text-xs text-gray-300 space-y-1.5 flex flex-col md:items-end">
                <div class="flex items-center gap-2">
                    <span>دمشق - المزة - اتستراد المزة - مبنى وزارة التعليم العالي والبحث العلمي</span>
                    <i class="fa-solid fa-location-dot text-[#c59b27] text-xs"></i>
                </div>
                <div class="flex items-center gap-2 font-mono" dir="ltr">
                    <i class="fa-solid fa-phone text-[#c59b27] text-xs"></i>
                    <span>+963 11 211 4500 / +963 11 211 4501</span>
                </div>
                <div class="flex items-center gap-2 font-mono" dir="ltr">
                    <i class="fa-solid fa-envelope text-[#c59b27] text-xs"></i>
                    <a href="mailto:equivalence@mohe.gov.sy" class="hover:text-[#c59b27] transition">equivalence@mohe.gov.sy</a>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-gray-400 pt-0.5">
                    <span>أوقات الدوام الرسمي: الأحد - الخميس (08:00 ص - 03:00 م)</span>
                    <i class="fa-regular fa-clock text-[#c59b27] text-xs"></i>
                </div>
            </div>

        </div>

        <!-- الشريط السفلي: حقوق الحفظ الرسمية فقط -->
        <div class="pt-4 flex flex-col sm:flex-row justify-between items-center text-[11px] text-gray-400 gap-2">
            <div>
                جميع الحقوق محفوظة &copy; {{ date('Y') }} - وزارة التعليم العالي والبحث العلمي - مجلس التعليم العالي | الجمهورية العربية السورية
            </div>
            <div class="flex items-center gap-2 text-[#c59b27] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-[#c59b27]"></span>
                بوابة الجامعات الخاصة المعتمدة
            </div>
        </div>
    </div>
</footer>

<!-- OFFICIAL DECISION DOCUMENT PAPER SNIPPET (A4 PROPORTIONS 210mm x 297mm) -->
<div class="decision-paper-wrapper" style="direction: rtl; font-family: 'Traditional Arabic', 'IBM Plex Sans Arabic', 'Segoe UI', serif; font-size: 16px; line-height: 1.85; color: #000000; background: #ffffff; width: 210mm; min-height: 297mm; padding: 20mm 22mm 18mm 22mm; margin: 0 auto; box-shadow: 0 10px 35px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; border-radius: 2px; position: relative; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">

    <!-- TOP & MIDDLE CONTENT AREA -->
    <div class="paper-body-content" style="flex: 1 0 auto;">

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/logo.jpg') }}" alt="شعار مجلس التعليم العالي" style="width: 88px; height: 88px; object-fit: contain; border-radius: 50%;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #111111;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- DECISION TITLE & NUMBER (INTERACTIVE EDITABLE FIELD - CLEAN WITHOUT DASHED UNDERLINE) -->
        <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px;">
            القرار رقم / &nbsp;<span contenteditable="true" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 40px; text-align: center; color: #000000; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span>&nbsp; / ل.م
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85;">
            <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div>وقرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15</div>
            <div>وقرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16</div>
            <div>وكتاب {{ $uniName }} رقم /{{ $uniReqNo }}/ تاريخ {{ $uniReqDate }}</div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <!-- ARTICLE 1 (ONLY THIS PARAGRAPH IS EDITABLE AS REQUESTED) -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص المادة الأولى والبيانات" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15; outline: none;">
            @if($decisionType === 'applied_master')
                <strong>المادة 1-</strong> تعد درجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} للسيد {{ $candidateName }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baSpec }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، محققةً لشروط الشهادة والاختصاص من أجل تدريس الجوانب التطبيقية في اختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
            @else
                <strong>المادة 1-</strong> الموافقة على تكليف السيد {{ $candidateName }}، الحائز درجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baSpec }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، بتدريس المقررات النظرية في اختصاص {{ $teachingDept }} في الجامعات الخاصة السورية على أن يكون تفرغه فيها كلياً، وألا يقل نصابه التدريسي عن /12/ ساعة أسبوعياً.
            @endif
        </div>

        <!-- ARTICLE 2 -->
        <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px;">
            <strong>المادة 2-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
        </div>

        <!-- OFFICIAL SIGNATURES SECTION (EXACT HORIZONTAL ALIGNMENT BETWEEN AMIN AND RA'IS) -->
        <table class="signatures-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; border: none !important; outline: none !important; box-shadow: none !important;" border="0">
            <tr>
                <!-- Right Column (50%): Director of Equivalence -->
                <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 15px; font-weight: bold; line-height: 1.5;">
                    <div>مدير التعادل والإنتاج العلمي</div>
                    <div style="margin-top: 8px; font-size: 16px;">المهندس عمار هلال</div>
                </td>

                <!-- Left Column (50%): Secretary General & Chairman (EXACT SAME RIGHT-ALIGNMENT STARTING POINT) -->
                <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 15px; font-weight: bold; line-height: 1.5;">
                    <!-- Top Block: Secretary General -->
                    <div>
                        <div>أمين مجلس التعليم العالي</div>
                        <div style="margin-top: 8px; font-size: 16px;">الدكتور علي الجاسم</div>
                    </div>

                    <!-- Bottom Block: Chairman / Vice Minister (Directly underneath with vertical gap & exact aligned start) -->
                    <div style="margin-top: 28px; line-height: 1.35;">
                        <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                        <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                        <div style="margin-top: 8px; font-size: 16.5px;">الدكتور عبد الحميد الخالد</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    <!-- BOTTOM FOOTER: COPIES TO SECTION (PINNED AT THE BOTTOM OF THE A4 PAGE) -->
    <div class="copies-div" style="font-size: 14px; line-height: 1.7; page-break-inside: avoid; border-top: 1px solid #cbd5e1; padding-top: 12px; margin-top: auto;">
        <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">صورة إلى:</div>
        <div>- مجلس التعليم العالي: مكتب التعادل – الديوان</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
    </div>

</div>

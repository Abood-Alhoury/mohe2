<!-- OFFICIAL DECISION DOCUMENT PAPER SNIPPET (A4 PROPORTIONS 210mm x 297mm) -->
<div class="decision-paper-wrapper" style="direction: rtl; font-family: 'Traditional Arabic', 'IBM Plex Sans Arabic', 'Segoe UI', serif; font-size: 16px; line-height: 1.85; color: #000000; background: #ffffff; width: 210mm; min-height: 297mm; padding: 18mm 22mm 18mm 22mm; margin: 0 auto; box-shadow: 0 10px 35px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; border-radius: 2px; position: relative; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">

    <!-- TOP & MIDDLE CONTENT AREA -->
    <div class="paper-body-content" style="flex: 1 0 auto;">

@if(($docType ?? 'equivalence') === 'eligibility')

    @if(!empty($isDoctorate))
        {{-- =========================================================================
             1. FORMAT A1: DOCTORATE ELIGIBILITY DECISION (قرار أهلية الدكتوراه السورية)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header with Number & Date -->
                <td style="width: 40%; text-align: right; vertical-align: top; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ: <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: top;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- ADDRESSEE TITLE -->
        <div class="addressee-div" style="text-align: center; font-size: 19px; font-weight: bold; margin: 25px 0 25px; color: #000000;">
            السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 16px; margin-bottom: 22px; text-align: justify; line-height: 1.9; color: #000000;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
            <div>والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
            <div>وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 2022/2/16.</div>
            <div>وقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '678' !!}</span>/ تاريخ <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>.</div>
            <div>وما قررته لجنة الأهلية المنعقدة بتاريخ <span contenteditable="true" style="color: #000000; font-weight: bold; outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 18px 0 20px; text-decoration: underline; color: #000000;">
            نفيدكم بالآتي:
        </div>

        <!-- DECREE BODY -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 17px; margin-bottom: 30px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            - إن {{ $titlePrefix ?? 'السيد الدكتور' }} <strong style="color: #000000;">{{ $candidateName }}</strong> {{ $genderAttrs['promotionWord'] ?? 'يرقى' }} للتدريس في الجامعات السورية الخاصة عملاً بالقرارات الواردة أعلاه
        </div>

    @else
        {{-- =========================================================================
             1. FORMAT A2: MASTER ELIGIBILITY DECISION (قرار أهلية للماجستير)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: top; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ: <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: top;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- ADDRESSEE TITLE -->
        <div class="addressee-div" style="text-align: center; font-size: 19px; font-weight: bold; margin: 25px 0 25px; color: #000000;">
            السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 16px; margin-bottom: 22px; text-align: justify; line-height: 1.9; color: #000000;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 وتعديلاته.</div>
            <div>وبالمرسوم التشريعي رقم /36/ لعام 2001 وتعديلاته ومستلزماته.</div>
            <div>وبقرار مجلس التعليم العالي لجنة التأهيل ومعادلة الدرجات العلمية بالإجماع القرار رقم /170/ تاريخ 2022/6/16.</div>
            <div>وبقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '89' !!}</span>/ تاريخ <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>.</div>
            <div>وبناءً على ثبوت الأهلية العلمية بتاريخ <span contenteditable="true" style="color: #000000; font-weight: bold; outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 18px 0 20px; text-decoration: underline; color: #000000;">
            المشار بالآتي:
        </div>

        <!-- DECREE BODY -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 17px; margin-bottom: 30px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            - {{ $titlePrefix ?? 'إن السيد/السيدة' }} <strong style="color: #000000;">{{ $candidateName }}</strong> {{ $qualifiedWord ?? 'مؤهل/ة' }} لتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
        </div>

    @endif

@else
    {{-- =========================================================================
         2. FORMAT B: EQUIVALENCE DECISION (قرار المعادلة والتعادل)
    ========================================================================= --}}

    @if(!empty($isFacultyPermission))
        {{-- =========================================================================
             2. FORMAT B0: FACULTY MEMBER TEACHING PERMISSION (قرار السماح بالتدريس)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- DECISION TITLE & NUMBER -->
        <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px; color: #000000;">
            القرار رقم / &nbsp;<span contenteditable="true" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 60px; text-align: center; color: #000000; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span>&nbsp; / ل . م
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85; color: #000000;">
            <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div>وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline; color: #000000;">
            يقرر ما يأتي:
        </div>

        <!-- ARTICLE 1 -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            <strong>المادة 1-</strong> السماح {{ $candidateTitlePrep ?? 'للسيد الدكتور' }} <strong>{{ $candidateName }}</strong> (عضو الهيئة التدريسية في {{ $govFaculty }} بجامعة {{ $govUni }}) بالتدريس باختصاص <strong>{{ $teachingDept }}</strong> في الجامعات الخاصة السورية.
        </div>

        <!-- ARTICLE 2 -->
        <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px; color: #000000;">
            <strong>المادة 2-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
            <div style="margin-top: 15px; margin-right: 40px; color: #000000;">دمشق في <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span></div>
        </div>

    @elseif(!empty($isResearchCenter))
        {{-- =========================================================================
             2. FORMAT B-RES: RESEARCH CENTER SCIENTIST DECISION (قرار باحث في مراكز البحوث)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- DECISION TITLE & NUMBER -->
        <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px; color: #000000;">
            القرار رقم / &nbsp;<span contenteditable="true" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 60px; text-align: center; color: #000000; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span>&nbsp; / ل . م
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85; color: #000000;">
            <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div>وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline; color: #000000;">
            يقرر ما يأتي:
        </div>

        <!-- ARTICLE 1 -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <strong>{{ $phdSpec }}</strong> الممنوحة {{ $candidateTitlePrep ?? 'للدكتور' }} <strong>{{ $candidateName }}</strong> ({{ $appointedResearcherWord ?? 'والمعين باحث' }} لدى {{ $rcCenterName ?? 'مركز الدراسات والبحوث العلمية' }}) عام <strong>{{ $phdYear }}</strong> من <strong>{{ $phdUni }}</strong>، مؤهلة للتدريس باختصاص <strong>{{ $teachingDept }}</strong> بالجامعات الخاصة السورية.
        </div>

        <!-- ARTICLE 2 -->
        <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px; color: #000000;">
            <strong>المادة 2-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
            <div style="margin-top: 15px; margin-right: 40px; color: #000000;">دمشق في <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span></div>
        </div>

    @elseif(!empty($isDoctorate))
        {{-- =========================================================================
             2. FORMAT B1: DOCTORATE EQUIVALENCE DECISION (قرار تعادل الدكتوراه السورية)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- DECISION TITLE & NUMBER -->
        <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px; color: #000000;">
            القرار رقم / &nbsp;<span contenteditable="true" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 60px; text-align: center; color: #000000; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span>&nbsp; / ل . م
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85; color: #000000;">
            <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div>وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div>وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline; color: #000000;">
            يقرر ما يأتي:
        </div>

        <!-- ARTICLE 1 -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <strong>{{ $phdSpec }}</strong> الممنوحة {{ $candidateTitlePrep ?? 'للدكتور' }} <strong>{{ $candidateName }}</strong> عام <strong>{{ $phdYear }}</strong> من جامعة <strong>{{ $phdUni }}</strong>، والمسبوقة بدرجة الماجستير في <strong>{{ $masterSpec }}</strong> الممنوحة عام <strong>{{ $masterYear }}</strong> من جامعة <strong>{{ $masterUni }}</strong>، ودرجة الإجازة في <strong>{{ $baGeneral }}{{ $baSection ? '/' . $baSection : '' }}</strong> الممنوحة عام <strong>{{ $baYear }}</strong> من جامعة <strong>{{ $baUni }}</strong>، مؤهلة للتعيين والتدريس باختصاص <strong>{{ $teachingDept }}</strong> بالجامعات الخاصة السورية.
        </div>

        <!-- ARTICLE 2 -->
        <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px; color: #000000;">
            <strong>المادة 2-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
            <div style="margin-top: 15px; margin-right: 40px; color: #000000;">دمشق في <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span></div>
        </div>

    @else
        {{-- =========================================================================
             2. FORMAT B2: MASTER EQUIVALENCE DECISION (قرار تعادل الماجستير سوري / تطبيقي)
        ========================================================================= --}}

        <!-- TOP HEADER -->
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 22px;" border="0">
            <tr>
                <!-- Right: Arabic Header -->
                <td style="width: 40%; text-align: right; vertical-align: middle; font-weight: bold; font-size: 15px; line-height: 1.5; color: #000000;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية
                </td>

                <!-- Center: Official Emblem Logo -->
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 95px; height: 95px; object-fit: contain;">
                </td>

                <!-- Left: English Header -->
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-weight: bold; font-size: 13px; line-height: 1.4; color: #000000;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <!-- DECISION TITLE & NUMBER -->
        <div class="decision-title-div" style="text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0 22px; color: #000000;">
            القرار رقم / &nbsp;<span contenteditable="true" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 60px; text-align: center; color: #000000; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span>&nbsp; / ل.م
        </div>

        <!-- PREAMBLE -->
        <div class="preamble-div" style="font-size: 15.5px; margin-bottom: 18px; text-align: justify; line-height: 1.85; color: #000000;">
            <div style="font-weight: bold; margin-bottom: 8px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div>وقرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15</div>
            <div>وقرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16</div>
            <div>وكتاب {{ $uniName }} رقم /<span contenteditable="true" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <!-- DECISION DECREE HEADER -->
        <div class="decree-header-div" style="text-align: center; font-size: 18px; font-weight: bold; margin: 16px 0 20px; text-decoration: underline; color: #000000;">
            يقرر ما يأتي:
        </div>

        <!-- ARTICLE 1 -->
        <div class="article-div" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16.5px; margin-bottom: 22px; text-align: justify; line-height: 2.15; color: #000000; outline: none;">
            @if($decisionType === 'applied_master')
                <strong>المادة -1</strong> تعد درجة الماجستير في <strong>{{ $masterSpec }}</strong> الممنوحة عام <strong>{{ $masterYear }}</strong> {{ $candidateTitlePrep ?? 'للسيد' }} <strong>{{ $candidateName }}</strong> من جامعة <strong>{{ $masterUni }}</strong>، والمسبوقة بدرجة الإجازة في <strong>{{ $baSpec }}</strong> الممنوحة عام <strong>{{ $baYear }}</strong> من جامعة <strong>{{ $baUni }}</strong>، محققةً لشروط الشهادة والاختصاص من أجل تدريس الجوانب التطبيقية في اختصاص <strong>{{ $teachingDept }}</strong> بالجامعات الخاصة السورية.
            @else
                <strong>المادة -1</strong> الموافقة على {{ $assignWord ?? 'تكليف' }} {{ $candidateTitle ?? 'السيد' }} <strong>{{ $candidateName }}</strong>، {{ $qualifierHolderWord ?? 'الحائز' }} درجة الماجستير في <strong>{{ $masterGeneral }}</strong>{!! $masterExact ? ' اختصاص <strong>' . $masterExact . '</strong>' : '' !!} الممنوحة عام <strong>{{ $masterYear }}</strong> من جامعة <strong>{{ $masterUni }}</strong>، والمسبوقة بدرجة الإجازة في <strong>{{ $baGeneral }}</strong>{!! $baSection ? ' قسم <strong>' . $baSection . '</strong>' : '' !!} الممنوحة عام <strong>{{ $baYear }}</strong> من جامعة <strong>{{ $baUni }}</strong>، بتدريس المقررات النظرية في اختصاص <strong>{{ $teachingDept }}</strong> في الجامعات الخاصة السورية على أن يكون {{ $fullTimeWord ?? 'تفرغه' }} فيها كلياً، وألا يقل {{ $quotaWord ?? 'نصابه' }} التدريسي عن /12/ ساعة أسبوعياً.
            @endif
        </div>

        <!-- ARTICLE 2 -->
        <div class="article-div" style="font-size: 16.5px; margin-bottom: 25px; color: #000000;">
            <strong>المادة -2</strong> يبلغ هذا القرار من يلزم لتنفيذه.
            <div style="margin-top: 15px; margin-right: 40px; color: #000000;">دمشق في <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span></div>
        </div>

    @endif

@endif

    <!-- OFFICIAL SIGNATURES SECTION (3 SIGNATURES - SHARED ACROSS ALL DECISIONS) -->
    <div class="signatures-div" style="margin-top: 35px; margin-bottom: 20px; page-break-inside: avoid; color: #000000;">
        <table style="width: 100%; border-collapse: collapse;" border="0">
            <tr>
                <!-- Right Column: Director of Equivalence -->
                <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 15px; font-size: 15px; font-weight: bold; line-height: 1.6; color: #000000;">
                    <div>مدير التعادل والإنتاج العلمي</div>
                    <div style="height: 40px; margin: 10px 0;"></div>
                    <div style="font-size: 16px;">المهندس عمار هلال</div>
                </td>

                <!-- Left Column: Amin & Chairman -->
                <td style="width: 50%; text-align: right; vertical-align: top; padding-right: 25px; font-size: 15px; font-weight: bold; line-height: 1.6; color: #000000;">
                    <div>
                        <div>أمين مجلس التعليم العالي</div>
                        <div style="height: 40px; margin: 10px 0;"></div>
                        <div style="font-size: 16px;">الدكتور علي الجاسم</div>
                    </div>

                    <div style="margin-top: 35px; line-height: 1.4; color: #000000;">
                        <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                        <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                        <div style="height: 40px; margin: 10px 0;"></div>
                        <div style="font-size: 16.5px;">الدكتور عبد الحميد الخالد</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    </div>

    <!-- BOTTOM FOOTER: COPIES TO SECTION -->
    <div class="copies-div" style="font-size: 14px; line-height: 1.8; page-break-inside: avoid; border-top: 1px solid #cbd5e1; padding-top: 12px; margin-top: auto; color: #000000;">
        <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">صورة إلى:</div>
        <div>- مجلس التعليم العالي: مكتب التعادل – الديوان.</div>
        <div>- المؤسسات التعليمية الخاصة.</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني).</div>
    </div>

</div>

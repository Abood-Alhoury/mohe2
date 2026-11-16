<style>
    /* ====================================================
       1. إعدادات الطباعة على صفحة A4 واحدة فقط
       ==================================================== */
    @page {
        size: A4 portrait;
        margin: 10mm 14mm 10mm 14mm;
    }

    @media print {
        html, body {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ترويض الحاوية وقت الطباعة لتملأ ورقة A4 واحدة بدقة */
        .decision-paper-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 274mm !important;
            height: 274mm !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            overflow: visible !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }

        .header-table, .preamble-div, .article-div, .signatures-div, .copies-div {
            page-break-inside: avoid !important;
            width: 100% !important;
        }
    }
</style>

<!-- OFFICIAL DECISION DOCUMENT PAPER (SINGLE A4 PAGE 210mm x 297mm) -->
<div class="decision-paper-wrapper" style="direction: rtl; color: #000000; background: #ffffff; width: 210mm; min-height: 297mm; padding: 11mm 15mm; margin: 0 auto; box-shadow: 0 10px 35px rgba(0,0,0,0.12); border: 1px solid #cbd5e1; box-sizing: border-box; position: relative; display: flex; flex-direction: column; justify-content: space-between;">
<div class="decision-main-content">

@if(($docType ?? 'equivalence') === 'rejection' || !empty($isRejected))
    {{-- ====================================================
         قرار / كتاب عدم الموافقة (الطلبات المرفوضة - ورقة A4)
         ==================================================== --}}
    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 14px;" border="0">
        <tr>
            <td style="width: 40%; text-align: right; vertical-align: top; font-size: 14.5px; line-height: 1.5; font-weight: 700;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية<br>
                الرقم: <span contenteditable="true" id="live-dec-no" style="display: inline-block; min-width: 55px; outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                التاريخ: <span contenteditable="true" style="display: inline-block; min-width: 75px; outline: none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 120px; max-height: 78px; object-fit: contain;">
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-size: 13.5px; line-height: 1.45; font-weight: 700;">
                Syrian Arab Republic<br>
                Council of Higher Education
            </td>
        </tr>
    </table>

    <div class="addressee-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 14px 0 14px;">
        السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span>
    </div>

    <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 12px; text-align: justify; line-height: 1.65;">
        <div style="margin-bottom: 4px;">بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
        <div style="margin-bottom: 4px;">والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
        <div style="margin-bottom: 4px;">وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 16/2/2022.</div>
    </div>

    <div class="decree-header-div rejection-decree-header" style="text-align: right; font-size: 15.5px; font-weight: 700; margin: 10px 0 10px;">
        نفيدكم بالآتي:
    </div>

    <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص كتاب عدم الموافقة مباشرة" style="font-size: 15.5px; margin-bottom: 16px; text-align: justify; line-height: 1.95; outline: none;">
        "عدم الموافقة على تعيين <span contenteditable="true" style="outline: none;">{{ $rejectionTargetTitle ?? ($isFemale ? 'السيدة' : 'السيد') }}</span> <strong><span contenteditable="true" style="outline: none;">{{ $candidateName }}</span></strong> <span id="live-rej-reason" contenteditable="true" style="outline: none;">{{ $rejectionReasonText ?? 'نظراً لعدم تحقيق شروط معادلة الشهادات الواردة في قرارات مجلس التعليم العالي .' }}</span>"
    </div>

    <div style="text-align: center; font-size: 15.5px; font-weight: 700; margin: 10px 0 18px;">
        يرجى الاطلاع
    </div>

@elseif(($docType ?? 'equivalence') === 'eligibility')

    @if(!empty($isDoctorate))
        {{-- قرار أهلية الدكتوراه السورية --}}
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 14px;" border="0">
            <tr>
                <td style="width: 40%; text-align: right; vertical-align: top; font-size: 14.5px; line-height: 1.5; font-weight: 700;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" id="live-dec-no" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ:
                </td>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 120px; max-height: 78px; object-fit: contain;">
                </td>
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-size: 13.5px; line-height: 1.45; font-weight: 700;">
                    Syrian Arab Republic<br>
                    Council of Higher Education
                </td>
            </tr>
        </table>

        <div class="addressee-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 14px 0;">
            السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم
        </div>

        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 12px; text-align: justify; line-height: 1.65;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
            <div>والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
            <div>وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 2022/2/16.</div>
            <div>وقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;"></span>/ تاريخ <span contenteditable="true" style="outline: none;"></span></div>
            <div>وما قررته لجنة الأهلية المنعقدة بتاريخ <span contenteditable="true" style="outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 15.5px; font-weight: 700; margin: 10px 0 12px; text-decoration: underline;">
            نفيدكم بالآتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 24px; text-align: justify; line-height: 1.95; outline: none;">
            - <span contenteditable="true" style="outline: none;">{{ $g['doctor'] ?? ($candidateTitle ?? 'الدكتور') }}</span> <strong><span contenteditable="true" style="outline: none;">{{ $candidateName }}</span></strong> <span contenteditable="true" style="outline: none;">{{ $g['promoted'] ?? ($genderAttrs['promotionWord'] ?? 'يرقى') }}</span> للتدريس في الجامعات السورية الخاصة عملاً بالقرارات الواردة أعلاه.
        </div>
    @else
        {{-- قرار أهلية للماجستير --}}
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 12px;" border="0">
            <tr>
                <td style="width: 40%; text-align: right; vertical-align: top; font-size: 14.5px; line-height: 1.5; font-weight: 700;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" id="live-dec-no" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ:
                </td>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 120px; max-height: 78px; object-fit: contain;">
                </td>
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-size: 13.5px; line-height: 1.45; font-weight: 700;">
                    Syrian Arab Republic<br>
                    Council of Higher Education
                </td>
            </tr>
        </table>

        <div class="addressee-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 12px 0;">
            السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم
        </div>

        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 12px; text-align: justify; line-height: 1.65;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 وتعديلاته.</div>
            <div>وبالمرسوم التشريعي رقم /36/ لعام 2001 وتعديلاته ومستلزماته.</div>
            <div>وبقرار مجلس التعليم العالي لجنة التأهيل ومعادلة الدرجات العلمية بالإجماع القرار رقم /170/ تاريخ 2022/6/16.</div>
            <div>وبقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '89' !!}</span>/ تاريخ <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>.</div>
            <div>وبناءً على ثبوت الأهلية العلمية بتاريخ <span contenteditable="true" style="outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 15.5px; font-weight: 700; margin: 10px 0 12px; text-decoration: underline;">
            المشار بالآتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 24px; text-align: justify; line-height: 1.95; outline: none;">
            - <span contenteditable="true" style="outline: none;">{{ $g['title'] ?? ($candidateTitle ?? 'السيد') }}</span> <strong><span contenteditable="true" style="outline: none;">{{ $candidateName }}</span></strong> <span contenteditable="true" style="outline: none;">{{ $isFemale ? 'مؤهلة' : 'مؤهل' }}</span> للتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
        </div>
    @endif

@else
    {{-- ====================================================
         قرارات المعادلة والتعادل الموحدة والسماح بالتدريس
         ==================================================== --}}

    {{-- الترويسة الموحدة للقرارات --}}
    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 10px;" border="0">
        <tr>
            <td style="width: 40%; text-align: right; vertical-align: middle; font-size: 14.5px; line-height: 1.5; font-weight: 700;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 120px; max-height: 78px; object-fit: contain;">
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-size: 13.5px; line-height: 1.45; font-weight: 700;">
                Syrian Arab Republic<br>
                Council of Higher Education
            </td>
        </tr>
    </table>

    <div class="decision-title-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 8px 0 12px;">
        القرار رقم / <span contenteditable="true" id="live-dec-no" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 55px; text-align: center; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span> / ل.م
    </div>

    @if(!empty($isFacultyPermission))
        {{-- نموذج السماح بالتدريس --}}
        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 10px; text-align: justify; line-height: 1.65;">
            <div style="margin-bottom: 3px; font-weight: 700; font-size: 15px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
            <div style="margin-bottom: 3px;">وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 10px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 12px; text-align: justify; line-height: 1.95; outline: none;">
            <strong>المادة 1-</strong> السماح <span contenteditable="true" style="outline:none;">{{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للسيد الدكتور') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> (عضو الهيئة التدريسية في <span contenteditable="true" style="outline:none;">{{ $govFaculty }}</span> بجامعة <span contenteditable="true" style="outline:none;">{{ $govUni }}</span>) بالتدريس باختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> في الجامعات الخاصة السورية.
        </div>

    @elseif(!empty($isResearchCenter))
        {{-- نموذج باحث في مراكز البحوث --}}
        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 10px; text-align: justify; line-height: 1.65;">
            <div style="margin-bottom: 3px; font-weight: 700; font-size: 15px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
            <div style="margin-bottom: 3px;">وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 10px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 12px; text-align: justify; line-height: 1.95; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <span contenteditable="true" style="outline:none;">{{ $phdFormattedSpec ?? $phdSpec }}</span> الممنوحة <span contenteditable="true" style="outline:none;">{{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> (<span contenteditable="true" style="outline:none;">{{ $g['appointed_researcher'] ?? ($appointedResearcherWord ?? 'والمعين باحث') }}</span> لدى <span contenteditable="true" style="outline:none;">{{ $rcCenterName }}</span>) عام <span contenteditable="true" style="outline:none;">{{ $phdYear }}</span> <span contenteditable="true" style="outline:none;">{{ $phdUniCountry ?? ('من ' . $phdUni) }}</span>، مؤهلة للتدريس باختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> بالجامعات الخاصة السورية.
        </div>

    @elseif(!empty($isForeignDoctorate) || $decisionType === 'foreign_doctorate')
        {{-- نموذج تعادل دكتوراه غير سورية (خارجية) - قرار موحد مع الأهلية --}}
        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 10px; text-align: justify; line-height: 1.65;">
            <div style="margin-bottom: 3px; font-weight: 700; font-size: 15px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
            <div style="margin-bottom: 3px;">وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 3px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في <span contenteditable="true" style="outline:none;">{{ $committeeDate ?? ($decisionDate ?? '') }}</span>.</div>
            <div style="margin-bottom: 3px;">وعلى قرار الأهلية رقم / <span contenteditable="true" id="live-elig-decision-no" style="outline:none;">{!! $eligibilityDecisionNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> / تاريخ <span contenteditable="true" id="live-elig-decision-date" style="outline:none;">{!! $eligibilityDecisionDate ?: '&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 2px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 10px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 12px; text-align: justify; line-height: 1.95; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <span contenteditable="true" style="outline:none;">{{ $phdFormattedSpec ?? ($phdDepartment ?: ($phdFaculty ?: $phdSpec)) }}</span> الممنوحة <span contenteditable="true" style="outline:none;">{{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> عام <span contenteditable="true" style="outline:none;">{{ $phdYear }}</span> <span contenteditable="true" style="outline:none;">{{ $phdUniCountry }}</span>، والمسبوقة بدرجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، ودرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، معادلة ومؤهلة لدرجة الدكتوراه المطلوبة للتعيين والتدريس باختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> بالجامعات الخاصة السورية.
        </div>

    @elseif(!empty($isDoctorate))
        {{-- نموذج تعادل الدكتوراه السورية (داخلي) - قرار موحد مع الأهلية --}}
        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 10px; text-align: justify; line-height: 1.65;">
            <div style="margin-bottom: 3px; font-weight: 700; font-size: 15px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
            <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
            <div style="margin-bottom: 3px;">وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 3px;">وعلى قرار الأهلية رقم / <span contenteditable="true" id="live-elig-decision-no" style="outline:none;">{!! $eligibilityDecisionNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> / تاريخ <span contenteditable="true" id="live-elig-decision-date" style="outline:none;">{!! $eligibilityDecisionDate ?: '&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 2px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 10px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 12px; text-align: justify; line-height: 1.95; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <span contenteditable="true" style="outline:none;">{{ $phdFormattedSpec ?? $phdSpec }}</span> الممنوحة <span contenteditable="true" style="outline:none;">{{ $g['for_doctor'] ?? ($candidateTitlePrep ?? 'للدكتور') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> عام <span contenteditable="true" style="outline:none;">{{ $phdYear }}</span> <span contenteditable="true" style="outline:none;">{{ $phdUniCountry }}</span>، والمسبوقة بدرجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، ودرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، مؤهلة للتعيين والتدريس باختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> بالجامعات الخاصة السورية.
        </div>

    @else
        {{-- نموذج تعادل الماجستير (تطبيقي / سوري نظري / خارجي نظري وتطبيقي) --}}
        <div class="preamble-div" style="font-size: 14.5px; margin-bottom: 10px; text-align: justify; line-height: 1.65;">
            <div style="margin-bottom: 3px; font-weight: 700; font-size: 15px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 3px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            @if($decisionType === 'applied_master' || $decisionType === 'foreign_master_applied' || (!empty($isApplied) && empty($isDoctorate)))
                <div style="margin-bottom: 3px;">وقرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007</div>
                <div style="margin-bottom: 3px;">وقرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022</div>
                <div style="margin-bottom: 3px;">وكتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            @else
                <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 15/7/2007.</div>
                <div style="margin-bottom: 3px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 16/6/2022 وتعديلاته.</div>
                <div style="margin-bottom: 3px;">وعلى كتاب {{ $uniName }} رقم /<span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>/ تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            @endif

            {{-- إضافة مستند قرار اللجنة للماجستير الخارجي بنوعيه (نظري أو تطبيقي) --}}
            @if($isForeignMaster || $decisionType === 'foreign_master_theoretical' || $decisionType === 'foreign_master_applied')
                <div style="margin-bottom: 3px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في <span contenteditable="true" style="outline:none;">{{ $committeeDate ?? ($decisionDate ?? '') }}</span></div>
            @endif

            {{-- إدراج قرار الأهلية في الديباجة للمسار النظري (داخلي وخارجي) --}}
            @if(!empty($requiresEligibility))
                <div style="margin-bottom: 3px;">وعلى قرار الأهلية رقم / <span contenteditable="true" id="live-elig-decision-no" style="outline:none;">{!! $eligibilityDecisionNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> / تاريخ <span contenteditable="true" id="live-elig-decision-date" style="outline:none;">{!! $eligibilityDecisionDate ?: '&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            @endif
            <div style="margin-bottom: 2px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 10px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 15.5px; margin-bottom: 12px; text-align: justify; line-height: 1.95; outline: none;">
            @if($decisionType === 'foreign_master_theoretical')
                {{-- 1. الماجستير الخارجي - مسار نظري --}}
                <strong>المادة 1-</strong> الموافقة على تكليف <span contenteditable="true" style="outline:none;">{{ $g['title'] ?? ($genderTitle ?? 'السيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong>، <span contenteditable="true" style="outline:none;">{{ $g['holder'] ?? ($genderDegreeHolder ?? 'الحائز') }}</span> درجة الماجستير{{ !empty($masterIsResearch) ? ' البحثي' : '' }} في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، بتدريس المقررات النظرية في اختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> في الجامعات الخاصة السورية على أن يكون <span contenteditable="true" style="outline:none;">{{ $g['dedication'] ?? ($genderCommitment ?? 'تفرغه') }}</span> كلياً فيها، وألا يقل <span contenteditable="true" style="outline:none;">{{ $g['quota'] ?? ($genderQuota ?? 'نصابه') }}</span> التدريسي عن /12/ ساعة أسبوعياً.

            @elseif($decisionType === 'foreign_master_applied')
                {{-- 2. الماجستير الخارجي - مسار تطبيقي (عضو هيئة فنية) --}}
                <strong>المادة 1-</strong> تعد درجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $g['title_prep'] ?? ($candidateTitlePrep ?? 'للسيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، محققتين لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية (عضو هيئة فنية)</strong> في اختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> بالجامعات الخاصة السورية.

            @elseif($decisionType === 'applied_master' || (!empty($isApplied) && empty($isDoctorate)))
                {{-- 3. الماجستير الداخلي التطبيقي --}}
                <strong>المادة 1-</strong> تعد درجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $g['title_prep'] ?? ($candidateTitlePrep ?? 'للسيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، محققتين لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية</strong> في اختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> بالجامعات الخاصة السورية.

            @else
                {{-- 4. الماجستير السوري الداخلي النظري --}}
                <strong>المادة 1-</strong> الموافقة على تكليف <span contenteditable="true" style="outline:none;">{{ $g['title'] ?? ($candidateTitle ?? 'السيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong>، <span contenteditable="true" style="outline:none;">{{ $g['holder'] ?? ($qualifierHolderWord ?? 'الحائز') }}</span> درجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> <span contenteditable="true" style="outline:none;">{{ $masterUniCountry }}</span>، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baFormattedSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> <span contenteditable="true" style="outline:none;">{{ $baUniCountry }}</span>، بتدريس المقررات النظرية في اختصاص <span contenteditable="true" style="outline:none;">{{ $finalTeachingSpec ?? $teachingDept }}</span> في الجامعات الخاصة السورية على أن يكون <span contenteditable="true" style="outline:none;">{{ $g['dedication'] ?? ($fullTimeWord ?? 'تفرغه') }}</span> كلياً فيها، وألا يقل <span contenteditable="true" style="outline:none;">{{ $g['quota'] ?? ($quotaWord ?? 'نصابه') }}</span> التدريسي عن /12/ ساعة أسبوعياً.
            @endif
        </div>
    @endif

    {{-- المادة 2 وتاريخ دمشق --}}
    <div class="article-div" style="font-size: 15.5px; margin-bottom: 10px;">
        <strong>المادة 2-</strong> يبلغ هذا القرار من يلزم لتنفيذه.
        <div style="text-align: center; margin-top: 6px; padding-left: 110px;">دمشق في <span contenteditable="true" style="display: inline-block; min-width: 85px; outline: none;">&nbsp;</span></div>
    </div>

@endif

    <!-- قسم التوقيعات الرسمية (قابل للتعديل أو الإخفاء حسب الحاجة للطباعة اليدوية) -->
    <div class="signatures-div" style="margin-top: 18px; margin-bottom: 8px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;" border="0">
            <tr id="sig-row-main">
                <td id="sig-col-director" style="width: 50%; text-align: center; vertical-align: top; font-size: 14.5px; line-height: 1.45; font-weight: 700;">
                    <div contenteditable="true" style="outline: none;">مدير التعادل والإنتاج العلمي</div>
                    <div style="height: 30px; margin: 3px 0;"></div>
                    <div contenteditable="true" class="signer-name" id="live-signer-director" style="outline: none;">المهندس عمار هلال</div>
                </td>
                <td id="sig-col-secretary" style="width: 50%; text-align: center; vertical-align: top; font-size: 14.5px; line-height: 1.45; font-weight: 700;">
                    <div contenteditable="true" style="outline: none;">أمين مجلس التعليم العالي</div>
                    <div style="height: 30px; margin: 3px 0;"></div>
                    <div contenteditable="true" class="signer-name" id="live-signer-secretary" style="outline: none;">الدكتور علي الجاسم</div>
                </td>
            </tr>
            <tr id="sig-row-minister">
                <td style="width: 50%;"></td>
                <td id="sig-col-minister" style="width: 50%; text-align: center; vertical-align: top; font-size: 14.5px; line-height: 1.4; padding-top: 18px; font-weight: 700;">
                    <div contenteditable="true" style="outline: none;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                    <div contenteditable="true" style="margin-top: 2px; outline: none;">معاون وزير التعليم العالي والبحث العلمي</div>
                    <div style="height: 28px; margin: 3px 0;"></div>
                    <div contenteditable="true" class="signer-name" id="live-signer-minister" style="outline: none;">الدكتور عبد الحميد الخالد</div>
                </td>
            </tr>
        </table>
    </div>

</div>

    <!-- قسم صورة إلى (مثبت أسفل الورقة تحت السطر الأسفل على صفحة A4 واحدة) -->
    <div class="copies-div" style="font-size: 12px; line-height: 1.5; page-break-inside: avoid; margin-top: auto; padding-top: 8px; border-top: 1.5px solid #1e293b; text-align: right;">
        <div style="text-decoration: underline; margin-bottom: 2px; font-weight: 700;">صورة إلى:</div>
        <div>- مكتب التعادل – الديوان</div>
        @if(($docType ?? 'equivalence') === 'rejection' || !empty($isRejected))
            <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
        @else
            <div>- المؤسسات التعليمية الخاصة</div>
            <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني)</div>
        @endif
    </div>

</div>

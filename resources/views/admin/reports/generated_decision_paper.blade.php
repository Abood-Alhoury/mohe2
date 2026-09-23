<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        /* ====================================================
           1. إعدادات الطباعة فقط (لن تؤثر إطلاقاً على المعاينة)
           ==================================================== */
        @page {
            size: A4 portrait;
            /* هوامش فيزيائية آمنة لطابعات HP تمنع قص النص من اليمين */
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

            /* ترويض الحاوية وقت الطباعة لتملأ الصفحة بأمان تام */
            .decision-paper-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important; /* الاعتماد كلياً على هوامش @page لمنع العرض الزائد */
                border: none !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
                overflow: visible !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }

            /* منع انقسام أي فقرة أو توقيع */
            .header-table, .preamble-div, .article-div, .signatures-div, .copies-div {
                page-break-inside: avoid !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background: #f1f5f9;">

<!-- OFFICIAL DECISION DOCUMENT PAPER (A4 PROPORTIONS 210mm x 297mm) -->
<div class="decision-paper-wrapper" style="direction: rtl; color: #000000; background: #ffffff; width: 210mm; min-height: 297mm; padding: 12mm 16mm; margin: 0 auto; box-shadow: 0 10px 35px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; box-sizing: border-box; position: relative;">

@if(($docType ?? 'equivalence') === 'eligibility')

    @if(!empty($isDoctorate))
        {{-- قرار أهلية الدكتوراه السورية --}}
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 25px;" border="0">
            <tr>
                <td style="width: 40%; text-align: right; vertical-align: top; font-size: 15px; line-height: 1.5; font-weight: bold;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" id="live-dec-no" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ: 
                </td>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 150px; height: 90px; object-fit: contain;">
                </td>
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-size: 16px; line-height: 1.5; font-weight: bold;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <div class="addressee-div" style="text-align: center; font-size: 15px; margin: 20px 0;">
            <strong>السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم</strong>
        </div>

        <div class="preamble-div" style="font-size: 15px; margin-bottom: 12px; text-align: justify; line-height: 1.7;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006، ولائحته التنفيذية.</div>
            <div>والمرسوم التشريعي رقم /36/ لعام 2001 وتعليماته التنفيذية وتعديلاتهما.</div>
            <div>وقرارات مجلس التعليم العالي ولجنة التأهيل ومعادلة الدرجات العلمية ولاسيما القرار رقم /175/ تاريخ 2022/2/16.</div>
            <div>وقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '678' !!}</span>/ تاريخ <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>.</div>
            <div>وما قررته لجنة الأهلية المنعقدة بتاريخ <span contenteditable="true" style="outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 15px; font-weight: bold; margin: 10px 0 12px; text-decoration: underline;">
            نفيدكم بالآتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 14px; margin-bottom: 50px; text-align: justify; line-height: 1.85; outline: none;">
            - {{ $titlePrefix ?? 'السيد الدكتور' }} <strong>{{ $candidateName }}</strong> {{ $genderAttrs['promotionWord'] ?? 'يرقى' }} للتدريس في الجامعات السورية الخاصة عملاً بالقرارات الواردة أعلاه.
        </div>

    @else
        {{-- قرار أهلية للماجستير --}}
        <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 12px;" border="0">
            <tr>
                <td style="width: 40%; text-align: right; vertical-align: top; font-size: 15px; line-height: 1.5; font-weight: bold;">
                    الجمهورية العربية السورية<br>
                    مجلس التعليم العالي<br>
                    لجنة التأهيل ومعادلة الدرجات العلمية<br>
                    الرقم: <span contenteditable="true" id="live-dec-no" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span><br>
                    التاريخ: 
                </td>
                <td style="width: 20%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 150px; height: 90px; object-fit: contain;">
                </td>
                <td dir="ltr" style="width: 40%; text-align: left; vertical-align: top; font-size: 16px; line-height: 1.5;font-weight: bold;">
                    Syrian Arab Republic<br>
                    council of Higher Education
                </td>
            </tr>
        </table>

        <div class="addressee-div" style="text-align: center; font-size: 16px; margin: 12px 0;">
           <strong> السيد الدكتور رئيس <span contenteditable="true" style="outline: none;">{{ $uniName }}</span> المحترم</strong>
        </div>

        <div class="preamble-div" style="font-size: 15px; margin-bottom: 15px; text-align: justify; line-height: 1.7;">
            <div>بناءً على أحكام قانون تنظيم الجامعات رقم /6/ لعام 2006 وتعديلاته.</div>
            <div>وبالمرسوم التشريعي رقم /36/ لعام 2001 وتعديلاته ومستلزماته.</div>
            <div>وبقرار مجلس التعليم العالي لجنة التأهيل ومعادلة الدرجات العلمية بالإجماع القرار رقم /170/ تاريخ 2022/6/16.</div>
            <div>وبقرار لجنة التأهيل ومعادلة الدرجات العلمية رقم /<span contenteditable="true" style="outline: none;">{!! ($decisionNo && $decisionNo !== '---') ? $decisionNo : '89' !!}</span>/ تاريخ <span contenteditable="true" style="outline: none;">{{ $decisionDate }}</span>.</div>
            <div>وبناءً على ثبوت الأهلية العلمية بتاريخ <span contenteditable="true" style="outline: none;">{{ $eligibilityDate }}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 15px; font-weight: bold; margin: 10px 0 12px; text-decoration: underline;">
            المشار بالآتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16px; margin-bottom: 30px; text-align: justify; line-height: 1.85; outline: none;">
            - {{ $titlePrefix ?? 'إن السيد/السيدة' }} <strong>{{ $candidateName }} </strong>{{ $qualifiedWord ?? 'مؤهل/ة' }} لتدريس في الجامعات السورية الخاصة عملاً بالمقررات الواردة أعلاه.
        </div>
    @endif

@else
    {{-- قرارات المعادلة والتعادل --}}

    {{-- الترويسة الموحدة للقرارات --}}
    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 12px;" border="0">
        <tr>
            <td style="width: 40%; text-align: right; vertical-align: middle; font-size: 15px; line-height: 1.5; font-weight: bold;">
                الجمهورية العربية السورية<br>
                مجلس التعليم العالي<br>
                لجنة التأهيل ومعادلة الدرجات العلمية
            </td>
            <td style="width: 20%; text-align: center; vertical-align: middle;">
                <img src="{{ asset('assets/report_logo.png') }}" alt="شعار الجمهورية العربية السورية" style="width: 150px; height: 90px; object-fit: contain;">
            </td>
            <td dir="ltr" style="width: 40%; text-align: left; vertical-align: middle; font-size: 15px; line-height: 1.5; font-weight: bold;">
                Syrian Arab Republic<br>
                council of Higher Education
            </td>
        </tr>
    </table>

    <div class="decision-title-div" style="text-align: center; font-size: 16px; font-weight: bold; margin: 12px 0;">
        القرار رقم /<span contenteditable="true" id="live-dec-no" title="انقر هنا لكتابة أو تعديل رقم القرار" style="display: inline-block; min-width: 40px; text-align: center; border: none; outline: none; padding: 0 4px;">{{ ($decisionNo && $decisionNo !== '---') ? $decisionNo : '' }}</span> / ل.م
    </div>

    @if(!empty($isFacultyPermission))
        {{-- نموذج السماح بالتدريس --}}
        <div class="preamble-div" style="font-size: 15px; margin-bottom: 12px; text-align: justify; line-height: 1.6;">
            <div style="margin-bottom: 5px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 5px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div style="margin-bottom: 5px;">وعلى كتاب {{ $uniName }} رقم <span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0 12px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16px; margin-bottom: 14px; text-align: justify; line-height: 2.2; outline: none;">
            <strong>المادة 1-</strong> السماح {{ $candidateTitlePrep ?? 'للسيد الدكتور' }} {{ $candidateName }} (عضو الهيئة التدريسية في {{ $govFaculty }} بجامعة {{ $govUni }}) بالتدريس باختصاص {{ $teachingDept }} في الجامعات الخاصة السورية.
        </div>

   

    @elseif(!empty($isForeignDoctorate) || $decisionType === 'foreign_doctorate')
        {{-- نموذج تعادل دكتوراه غير سورية (خارجية) مطابق للوثيقة الرسمية --}}
        <div class="preamble-div" style="font-size: 15px; margin-bottom: 12px; text-align: justify; line-height: 1.6;">
            <div style="margin-bottom: 5px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 5px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div style="margin-bottom: 5px;">وعلى كتاب {{ $uniName }} رقم <span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 5px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في <span contenteditable="true" style="outline:none;">{{ $committeeDate ?? ($decisionDate ?? '') }}</span>.</div>
            <div style="margin-bottom: 5px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0 12px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16px; margin-bottom: 14px; text-align: justify; line-height: 2.2; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في <span contenteditable="true" style="outline:none;">{{ $phdDepartment ?: ($phdFaculty ?: $phdSpec) }}</span> شعبة <span contenteditable="true" style="outline:none;">{{ $phdExact ?: $phdSpec }}</span> الممنوحة <span contenteditable="true" style="outline:none;">{{ $candidateTitlePrep ?? 'للدكتور' }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong> عام <span contenteditable="true" style="outline:none;">{{ $phdYear }}</span> من <span contenteditable="true" style="outline:none;">{{ preg_match('/^(جامعة|جامعه)\s+/u', $phdUni) ? $phdUni : 'جامعة ' . $phdUni }}</span> في <span contenteditable="true" style="outline:none;">{{ $phdCountry ?: '---' }}</span>، والمسبوقة بدرجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterDepartment ?: ($masterFaculty ?: $masterGeneral) }}</span> اختصاص <span contenteditable="true" style="outline:none;">{{ $masterExact ?: $masterSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> من <span contenteditable="true" style="outline:none;">{{ preg_match('/^(جامعة|جامعه)\s+/u', $masterUni) ? $masterUni : 'جامعة ' . $masterUni }}</span>{{ !empty($masterCountry) ? ' في ' . $masterCountry : '' }}، ودرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baSpec ?: ($baFaculty ?: $baGeneral) }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> من <span contenteditable="true" style="outline:none;">{{ preg_match('/^(جامعة|جامعه)\s+/u', $baUni) ? $baUni : 'جامعة ' . $baUni }}</span>{{ (!empty($baCountry) && $baCountry !== 'سوريا') ? ' في ' . $baCountry : '' }}، معادلة ومؤهلة لدرجة الدكتوراه المطلوبة للتعيين والتدريس باختصاص <span contenteditable="true" style="outline:none;">{{ $teachingDept }}</span> بالجامعات الخاصة السورية.
        </div>

    @elseif(!empty($isDoctorate))
        {{-- نموذج تعادل الدكتوراه السورية --}}
        <div class="preamble-div" style="font-size: 15px; margin-bottom: 12px; text-align: justify; line-height: 1.6;">
            <div style="margin-bottom: 5px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
            <div style="margin-bottom: 5px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15.</div>
            <div style="margin-bottom: 5px;">وعلى قرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16 وتعديلاته.</div>
            <div style="margin-bottom: 5px;">وعلى كتاب {{ $uniName }} رقم <span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
            <div style="margin-bottom: 5px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0 12px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16px; margin-bottom: 14px; text-align: justify; line-height: 2.2; outline: none;">
            <strong>المادة 1-</strong> تعدّ درجة الدكتوراه في {{ $phdSpec }} الممنوحة {{ $candidateTitlePrep }} <strong>{{ $candidateName }}</strong> عام {{ $phdYear }} من جامعة {{ $phdUni }}، والمسبوقة بدرجة الماجستير في {{ $masterSpec }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، ودرجة الإجازة في {{ $baFaculty ?: $baGeneral }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، مؤهلة للتعيين والتدريس باختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.
        </div>

    @else
 {{-- نموذج تعادل الماجستير (سوري / تطبيقي / خارجي) --}}
<div class="preamble-div" style="font-size: 15px; margin-bottom: 12px; text-align: justify; line-height: 1.6;">
    <div style="margin-bottom: 5px;">رئيس لجنة التأهيل ومعادلة الدرجات العلمية.</div>
    <div style="margin-bottom: 5px;">بناءً على أحكام قانون تنظيم الجامعات رقم 6 لعام 2006 ولائحته التنفيذية وتعديلاتهما.</div>
    <div style="margin-bottom: 5px;">وقرار مجلس التعليم العالي رقم /236/ تاريخ 2007/7/15</div>
    <div style="margin-bottom: 5px;">وقرار مجلس التعليم العالي رقم /175/ تاريخ 2022/6/16</div>
    <div style="margin-bottom: 5px;">وكتاب {{ $uniName }} رقم <span contenteditable="true" id="live-uni-req-no" style="outline:none;">{!! $uniReqNo ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span> تاريخ <span contenteditable="true" id="live-uni-req-date" style="outline:none;">{!! $uniReqDate ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span></div>
    
    {{-- إضافة مستند قرار اللجنة للماجستير الخارجي بنوعيه (نظري أو تطبيقي) --}}
    @if($isForeignMaster || $decisionType === 'foreign_master_theoretical' || $decisionType === 'foreign_master_applied')
        <div style="margin-bottom: 5px;">وعلى قرار لجنة التأهيل ومعادلة الدرجات العلمية المنعقدة في <span contenteditable="true" style="outline:none;">{{ $committeeDate ?? ($decisionDate ?? '') }}</span></div>
            @endif
            <div style="margin-bottom: 5px;" contenteditable="true"></div>
        </div>

        <div class="decree-header-div" style="text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0 12px; text-decoration: underline;">
            يقرر ما يأتي:
        </div>

        <div class="article-div" id="live-article-body" contenteditable="true" title="انقر هنا لتعديل نص القرار مباشرة" style="font-size: 16px; margin-bottom: 14px; text-align: justify; line-height: 2.2; outline: none;">
            @if($decisionType === 'foreign_master_theoretical')
                {{-- 1. الماجستير الخارجي - مسار نظري (وفق الصياغة المعتمدة الجديدة) --}}
                <strong>المادة 1-</strong> الموافقة على تكليف <span contenteditable="true" style="outline:none;">{{ $genderTitle ?? ($candidateTitle ?? 'السيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong>، <span contenteditable="true" style="outline:none;">{{ $genderDegreeHolder ?? ($qualifierHolderWord ?? 'الحائز') }}</span> درجة الماجستير البحثي في <span contenteditable="true" style="outline:none;">{{ $masterSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> من جامعة <span contenteditable="true" style="outline:none;">{{ $masterUni }}</span>{{ !empty($masterCountry) ? ' في ' . $masterCountry : '' }}، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> من جامعة <span contenteditable="true" style="outline:none;">{{ $baUni }}</span>، بتدريس المقررات النظرية في اختصاص <span contenteditable="true" style="outline:none;">{{ $teachingDept }}</span> في الجامعات الخاصة السورية على أن يكون <span contenteditable="true" style="outline:none;">{{ $genderCommitment ?? ($fullTimeWord ?? 'تفرغه') }}</span> كلياً فيها، وألا يقل <span contenteditable="true" style="outline:none;">{{ $genderQuota ?? ($quotaWord ?? 'نصابه') }}</span> التدريسي عن /12/ ساعة أسبوعياً.

            @elseif($decisionType === 'foreign_master_applied')
                {{-- 2. الماجستير الخارجي - مسار تطبيقي (عضو هيئة فنية) --}}
                <strong>المادة-1</strong> تعدّ درجة الماجستير في <span contenteditable="true" style="outline:none;">{{ $masterSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $masterYear }}</span> من جامعة <span contenteditable="true" style="outline:none;">{{ $masterUni }}</span>{{ !empty($masterCountry) ? ' في ' . $masterCountry : '' }} <span contenteditable="true" style="outline:none;">{{ $candidateTitlePrep ?? ($isFemale ? 'للسيدة' : 'للسيد') }}</span> <strong><span contenteditable="true" style="outline:none;">{{ $candidateName }}</span></strong>، والمسبوقة بدرجة الإجازة في <span contenteditable="true" style="outline:none;">{{ $baSpec }}</span> الممنوحة عام <span contenteditable="true" style="outline:none;">{{ $baYear }}</span> من جامعة <span contenteditable="true" style="outline:none;">{{ $baUni }}</span>، محققةً لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية (عضو هيئة فنية)</strong> في اختصاص <span contenteditable="true" style="outline:none;">{{ $teachingDept }}</span> بالجامعات الخاصة السورية.

            @elseif($decisionType === 'applied_master' || (!empty($isApplied) && empty($isDoctorate)))
                {{-- 3. الماجستير الداخلي التطبيقي --}}
                <strong>المادة-1</strong> تعدّ درجة الماجستير في {{ $masterFaculty ?: $masterGeneral }}{!! $masterSpec ? ' اختصاص ' . $masterSpec : '' !!} الممنوحة عام {{ $masterYear }} {{ $candidateTitlePrep ?? 'للسيد' }} <strong>{{ $candidateName }}</strong> من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baGeneral }}{{ $baSection ? ' قسم ' . $baSection : '' }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، محققةً لشروط الشهادة والاختصاص من أجل تدريس <strong>الجوانب التطبيقية</strong> في اختصاص {{ $teachingDept }} بالجامعات الخاصة السورية.

            @else
                {{-- 4. الماجستير السوري الداخلي النظري --}}
                <strong>المادة 1-</strong> الموافقة على {{ $assignWord ?? 'تكليف' }} {{ $candidateTitle ?? 'السيد' }} <strong>{{ $candidateName }}</strong>، {{ $qualifierHolderWord ?? 'الحائز' }} درجة الماجستير في {{ $masterGeneral }}{{ $masterExact ? ' اختصاص ' . $masterExact : '' }} الممنوحة عام {{ $masterYear }} من جامعة {{ $masterUni }}، والمسبوقة بدرجة الإجازة في {{ $baGeneral }}{{ $baSection ? ' قسم ' . $baSection : '' }} الممنوحة عام {{ $baYear }} من جامعة {{ $baUni }}، بتدريس المقررات النظرية في اختصاص {{ $teachingDept }} في الجامعات الخاصة السورية على أن يكون {{ $fullTimeWord ?? 'تفرغه' }} كلياً فيها، وألا يقل {{ $quotaWord ?? 'نصابه' }} التدريسي عن /12/ ساعة أسبوعياً.
            @endif
    </div>
    @endif

    {{-- المادة 2 وتاريخ دمشق --}}
    <div class="article-div" style="font-size: 16px; margin-bottom: 14px;">
        <strong>المادة-2</strong> يبلغ هذا القرار من يلزم لتنفيذه.
        <div style="text-align: right; margin-top: 8px; margin-bottom: 6px;">دمشق في </div>
    </div>

@endif

    <!-- قسم التوقيعات الرسمية -->
    <div class="signatures-div" style="margin-top: 30px; margin-bottom: 10px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;" border="0">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top; font-size: 15px; line-height: 1.5; font-weight: bold;">
                    <div>مدير التعادل والإنتاج العلمي</div>
                    <div style="height: 35px; margin: 4px 0;"></div>
                    <div class="signer-name" id="live-signer-director" style="outline: none;">المهندس عمار هلال</div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; font-size: 15px; line-height: 1.5; font-weight: bold;">
                    <div>أمين مجلس التعليم العالي</div>
                    <div style="height: 35px; margin: 4px 0;"></div>
                    <div class="signer-name" id="live-signer-secretary" style="outline: none;">الدكتور علي جاسم</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center; vertical-align: top; font-size: 15px; line-height: 1.4; padding-top: 25px; font-weight: bold;">
                    <div>رئيس لجنة التأهيل ومعادلة الدرجات العلمية</div>
                    <div style="margin-top: 2px;">معاون وزير التعليم العالي والبحث العلمي</div>
                    <div style="height: 30px; margin: 4px 0;"></div>
                    <div class="signer-name" id="live-signer-minister" style="outline: none;">الدكتور عبد الحميد الخالد</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- قسم صورة إلى -->
    <div class="copies-div" style="font-size: 12px; line-height: 1.5; page-break-inside: avoid; margin-top: 60px; text-align: right;">
        <div style="text-decoration: underline; margin-bottom: 3px; font-weight: bold;">صورة إلى:</div>
        <div>- مجلس التعليم العالي: مكتب التعادل – الديوان.</div>
        <div>- أمانة سر المجلس (للتعميم على الجامعة المعنية عبر البريد الالكتروني).</div>
        <div>- مؤسسات التعليمية الخاصة.</div>
    </div>

</div>
</body>
</html>
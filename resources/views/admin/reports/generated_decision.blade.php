@php
    $wordLogoPath = file_exists(public_path('assets/report_logo.png'))
        ? public_path('assets/report_logo.png')
        : (file_exists(public_path('report_logo.png')) ? public_path('report_logo.png') : null);
    $wordLogoBase64 = $wordLogoPath ? trim(chunk_split(base64_encode(file_get_contents($wordLogoPath)), 76, "\r\n")) : '';
@endphp
@extends('layouts.admin')
@section('title', 'توليد قرار التعادل الرسمي الموحد - ' . ($candidateName ?? ''))

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap');

/* الخط الرسمي للقرارات الوزارية */
.decision-paper-wrapper,
.decision-paper-wrapper * {
    font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', 'Simplified Arabic', serif !important;
}

.decision-paper-wrapper [dir="ltr"],
.decision-paper-wrapper [dir="ltr"] * {
    font-family: 'Times New Roman', 'Amiri', Georgia, serif !important;
}

[contenteditable="true"] {
    transition: background-color 0.2s ease, border-color 0.2s ease;
    border-radius: 3px;
    padding: 0 2px;
}
[contenteditable="true"]:hover {
    background-color: #f8fafc !important;
    outline: 1px dashed #94a3b8 !important;
    cursor: text;
}
[contenteditable="true"]:focus {
    background-color: #ffffff !important;
    outline: 1.5px solid #0e1e38 !important;
    box-shadow: 0 0 0 2px rgba(14, 30, 56, 0.12) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column align-items-center w-100">
        
        <!-- شريط أدوات القرار (التحكم بالتواقيع وزر الطباعة/PDF الموحد) -->
        <div class="w-100 mb-3 no-print p-3 bg-white shadow-sm rounded border d-flex flex-wrap justify-content-between align-items-center gap-3" style="max-width: 850px;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy fw-bold px-3 btn-sm">
                    <i class="fa-solid fa-arrow-right me-1"></i> عودة للطلبات
                </a>
                @if(!empty($isRejected) || ($docType ?? '') === 'rejection')
                    <a href="{{ route('admin.rejected_decisions.index', ['app_id' => $application->id]) }}" class="btn btn-outline-danger fw-bold px-3 btn-sm">
                        <i class="fa-solid fa-stamp me-1"></i> إرفاق المختوم بإصدار القرارات
                    </a>
                @endif

                @if(!empty($isRejected) || ($docType ?? '') === 'rejection')
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1.5 fs-8 fw-bold">
                        <i class="fa-solid fa-file-circle-xmark me-1"></i> مسودة كتاب عدم الموافقة (A4)
                    </span>
                @elseif(!$isFacultyPermission && !$isApplied)
                    <span class="badge bg-primary-subtle text-primary border border-primary px-2.5 py-1.5 fs-8 fw-bold">
                        <i class="fa-solid fa-file-signature me-1"></i> قرار التعادل والأهلية الموحد (ورقة واحدة)
                    </span>
                @elseif($isApplied)
                    <span class="badge bg-primary-subtle text-primary border border-primary px-2.5 py-1.5 fs-8 fw-bold">
                        <i class="fa-solid fa-briefcase me-1"></i> مسودة قرار تعادل ماجستير تطبيقي (A4)
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1.5 fs-8 fw-bold">
                        <i class="fa-solid fa-stamp me-1"></i> قرار السماح بالتدريس (رسمي)
                    </span>
                @endif
            </div>

            <!-- خيارات إظهار / إخفاء التواقيع الثلاثة قبل الطباعة -->
            <div class="d-flex align-items-center flex-wrap gap-2 bg-light px-2.5 py-1.5 rounded border" style="font-size: 0.78rem;">
                <span class="fw-bold text-dark me-1"><i class="fa-solid fa-signature text-primary me-1"></i> التواقيع:</span>
                <label class="form-check form-check-inline mb-0 d-inline-flex align-items-center gap-1" style="cursor: pointer;">
                    <input class="form-check-input m-0 sig-toggle-chk" type="checkbox" checked data-target="sig-col-director" onchange="toggleSignerVisibility()">
                    <span>م. عمار هلال</span>
                </label>
                <label class="form-check form-check-inline mb-0 d-inline-flex align-items-center gap-1" style="cursor: pointer;">
                    <input class="form-check-input m-0 sig-toggle-chk" type="checkbox" checked data-target="sig-col-secretary" onchange="toggleSignerVisibility()">
                    <span>د. علي الجاسم</span>
                </label>
                <label class="form-check form-check-inline mb-0 d-inline-flex align-items-center gap-1" style="cursor: pointer;">
                    <input class="form-check-input m-0 sig-toggle-chk" type="checkbox" checked data-target="sig-col-minister" onchange="toggleSignerVisibility()">
                    <span>د. عبد الحميد الخالد</span>
                </label>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" style="font-size: 0.72rem;" onclick="toggleAllSigners(false)" title="إخفاء جميع التواقيع للتوقيع اليدوي">إخفاء الكل</button>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1.5" style="font-size: 0.72rem;" onclick="toggleAllSigners(true)" title="إظهار جميع التواقيع">الكل</button>
            </div>

            <!-- أزرار الطباعة/PDF وتحميل Word مع الحفاظ على التعديلات الحية -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" onclick="downloadAsWord()" class="btn btn-outline-primary fw-bold px-3 py-1.5 btn-sm shadow-xs" title="تحميل المسودة كملف Word قابل للتعديل الكامل مع الحفاظ على بياناتك وتعديلاتك">
                    <i class="fa-solid fa-file-word me-1"></i> تحميل بصيغة Word
                </button>
                <button type="button" onclick="printOrSavePdf()" class="btn btn-solid-navy fw-bold px-3.5 py-1.5 btn-sm shadow-xs" title="طباعة أو تصدير PDF مطابق للتعديلات على ورقة A4 واحدة">
                    <i class="fa-solid fa-print me-1"></i> طباعة مسودة A4 / PDF
                </button>
            </div>
        </div>

        @if(!empty($isRejected) && !empty($recordedRejectionReason))
            <div class="w-100 mb-3 no-print px-3 py-2 bg-danger-subtle border border-danger-subtle rounded d-flex flex-wrap justify-content-between align-items-center gap-2" style="max-width: 850px; font-size: 0.82rem;">
                <div class="text-danger-emphasis">
                    <strong><i class="fa-solid fa-note-sticky me-1"></i> سبب عدم الموافقة المسجّل في الطلب:</strong>
                    <span id="recorded-rej-reason-text">{{ $recordedRejectionReason }}</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-xs btn-outline-danger py-1 px-2 fw-bold" style="font-size: 0.76rem;" onclick="setRejectionReasonText('recorded')">
                        إدراج السبب المسجّل في النص
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 fw-bold" style="font-size: 0.76rem;" onclick="setRejectionReasonText('standard')">
                        استعادة الصيغة الوزارية القياسية
                    </button>
                </div>
            </div>
        @endif

        <!-- ورقة القرار الرسمية المعروضة -->
        <div class="d-flex justify-content-center w-100 overflow-auto py-2">
            @include('admin.reports.generated_decision_paper')
        </div>

    </div>
</div>

<script>
function toggleSignerVisibility() {
    const checkboxes = document.querySelectorAll('.sig-toggle-chk');
    const state = {};
    let anyVisible = false;

    checkboxes.forEach(chk => {
        const targetId = chk.dataset.target;
        state[targetId] = !!chk.checked;
        const el = document.getElementById(targetId);
        if (el) {
            if (chk.checked) {
                el.style.visibility = 'visible';
                el.style.display = '';
                anyVisible = true;
            } else {
                el.style.visibility = 'hidden';
            }
        }
    });

    const rowMain = document.getElementById('sig-row-main');
    if (rowMain) {
        const showMainRow = !!(state['sig-col-director'] || state['sig-col-secretary']);
        rowMain.style.display = showMainRow ? '' : 'none';
    }

    const rowMinister = document.getElementById('sig-row-minister');
    if (rowMinister) {
        rowMinister.style.display = state['sig-col-minister'] ? '' : 'none';
    }

    const sigContainer = document.querySelector('.signatures-div');
    if (sigContainer) {
        sigContainer.style.display = anyVisible ? 'block' : 'none';
    }
}

function toggleAllSigners(show) {
    document.querySelectorAll('.sig-toggle-chk').forEach(chk => {
        chk.checked = !!show;
    });
    toggleSignerVisibility();
}

function printOrSavePdf() {
    // 1. تحديد حاوية الورقة المعروضة حالياً على الشاشة
    const paper = document.querySelector('.decision-paper-wrapper');
    if (!paper) {
        window.print();
        return;
    }

    // 2. التقاط كود الـ HTML الحي للورقة بكافة التعديلات اليدوية (رقم القرار، النصوص، التواقيع)
    const paperContent = paper.innerHTML;

    // 3. تجهيز اسم الملف التلقائي عند اختيار "Save as PDF"
    const rawName = String(@json($candidateName ?? '')).replace(/[/\\?%*:|"<>]/g, '').trim();
    const appNo = String(@json($application->application_no ?? $application->id)).replace(/[/\\?%*:|"<>]/g, '').trim();
    const docPrefix = @json((!empty($isRejected) || ($docType ?? '') === 'rejection') ? 'كتاب_عدم_موافقة_' : 'قرار_معادلة_');
    const targetTitle = docPrefix + rawName + '_طلب_' + appNo;

    // 4. إنشاء إطار طباعة معزول (Iframe) لمنع أي تداخل مع باقي عناصر الصفحة
    let printFrame = document.getElementById('secure-print-frame');
    if (!printFrame) {
        printFrame = document.createElement('iframe');
        printFrame.id = 'secure-print-frame';
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);
    }

    const frameDoc = printFrame.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>${targetTitle}</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap');
                
                @page {
                    size: A4 portrait;
                    margin: 10mm 14mm;
                }
                
                html, body {
                    font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', 'Simplified Arabic', serif !important;
                    direction: rtl;
                    text-align: right;
                    color: #000000;
                    background: #ffffff;
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    height: 100%;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                
                * {
                    box-sizing: border-box;
                }

                .decision-paper-wrapper,
                .decision-paper-wrapper * {
                    font-family: 'Noto Naskh Arabic', 'Amiri', 'Traditional Arabic', 'Simplified Arabic', serif !important;
                }

                .decision-paper-wrapper [dir="ltr"],
                .decision-paper-wrapper [dir="ltr"] * {
                    font-family: 'Times New Roman', 'Amiri', Georgia, serif !important;
                }

                .decision-paper-wrapper {
                    width: 100% !important;
                    min-height: 274mm !important;
                    height: 274mm !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    page-break-inside: avoid !important;
                    page-break-after: avoid !important;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                td {
                    vertical-align: top;
                }

                [contenteditable="true"] {
                    outline: none !important;
                    border: none !important;
                    background: transparent !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                }
            </style>
        </head>
        <body>
            <div class="decision-paper-wrapper">
                ${paperContent}
            </div>
        </body>
        </html>
    `);
    frameDoc.close();

    // 5. تشغيل نافذة الطباعة بعد تحميل الخطوط والصور داخل الإطار
    setTimeout(() => {
        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();
    }, 450);
}

function downloadAsWord() {
    const paper = document.querySelector('.decision-paper-wrapper');
    if (!paper) return;

    const clone = paper.cloneNode(true);
    const wordFontFamily = '"Traditional Arabic", "Simplified Arabic", serif';

    const applyArabicTypography = (el, sizePt, lineHeight, extra = {}) => {
        if (!el) return;
        el.style.fontFamily = wordFontFamily;
        el.style.fontSize = sizePt + 'pt';
        if (lineHeight) {
            el.style.lineHeight = String(lineHeight);
        }
        Object.entries(extra).forEach(([k, v]) => {
            el.style[k] = v;
        });
    };

    // 1. معالجة التواقيع المخفية وضبط خطوط التواقيع لتطابق النسخة المطبوعة
    const sigContainer = clone.querySelector('.signatures-div');
    if (sigContainer) {
        if (sigContainer.style.display === 'none') {
            sigContainer.remove();
        } else {
            const rowMain = clone.querySelector('#sig-row-main');
            if (rowMain && rowMain.style.display === 'none') {
                rowMain.remove();
            }
            const rowMinister = clone.querySelector('#sig-row-minister');
            if (rowMinister && rowMinister.style.display === 'none') {
                rowMinister.remove();
            }

            const sigTable = sigContainer.querySelector('table');
            if (sigTable) {
                sigTable.setAttribute('dir', 'rtl');
                sigTable.setAttribute('border', '0');
                sigTable.setAttribute('cellpadding', '0');
                sigTable.setAttribute('cellspacing', '0');
                sigTable.style.width = '100%';
                sigTable.style.borderCollapse = 'collapse';
            }

            sigContainer.querySelectorAll('td').forEach(td => {
                td.style.padding = td.id === 'sig-col-minister' ? '8pt 0 0 0' : '0';
                td.style.border = 'none';
                td.style.verticalAlign = 'top';
                td.style.textAlign = 'center';
                applyArabicTypography(td, 14, 1.15, { fontWeight: 'bold' });
                if (td.id === 'sig-col-director' || td.id === 'sig-col-secretary' || td.id === 'sig-col-minister') {
                    td.setAttribute('width', '50%');
                    td.setAttribute('align', 'center');
                    td.style.width = '50%';
                }
            });

            ['sig-col-director', 'sig-col-secretary', 'sig-col-minister'].forEach(id => {
                const cell = clone.querySelector('#' + id);
                if (cell) {
                    if (cell.style.visibility === 'hidden' || cell.style.display === 'none') {
                        cell.innerHTML = '&nbsp;';
                        cell.style.visibility = 'visible';
                    } else {
                        cell.querySelectorAll('div').forEach(d => {
                            if (d.textContent.trim() === '') {
                                d.innerHTML = '&nbsp;';
                                d.style.height = '16pt';
                                d.style.lineHeight = '16pt';
                                d.style.fontSize = '1pt';
                                d.style.margin = '1pt 0';
                            } else {
                                applyArabicTypography(d, 14, 1.15, { fontWeight: 'bold', textAlign: 'center' });
                            }
                        });
                    }
                }
            });
            sigContainer.style.marginTop = '8pt';
            sigContainer.style.marginBottom = '2pt';
        }
    }

    // 2. تنظيف سمات التحرير المباشر وتثبيت الفراغات والخطوط في العناصر الداخلية
    clone.querySelectorAll('[contenteditable]').forEach(el => {
        el.removeAttribute('contenteditable');
        el.removeAttribute('title');
        el.style.outline = 'none';
        el.style.border = 'none';
        el.style.boxShadow = 'none';
        el.style.backgroundColor = 'transparent';
        el.style.padding = '0';
        if (el.id === 'live-dec-no' && el.textContent.trim() === '') {
            el.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
    });

    // إزالة الحقول الفارغة العرضية داخل الديباجة حتى لا تسبب فراغات زائدة في Word
    clone.querySelectorAll('.preamble-div > div').forEach(div => {
        if (div.textContent.replace(/\u00a0/g, '').trim() === '' && !div.querySelector('span')) {
            div.remove();
        }
    });

    // توحيد خطوط الترويسة العلوية والشعار بالنسبة الصحيحة
    clone.querySelectorAll('.header-table').forEach(tbl => {
        tbl.setAttribute('dir', 'rtl');
        tbl.setAttribute('border', '0');
        tbl.setAttribute('cellpadding', '0');
        tbl.setAttribute('cellspacing', '0');
        tbl.style.width = '100%';
        tbl.style.borderCollapse = 'collapse';
        tbl.style.marginBottom = '4pt';

        const tds = tbl.querySelectorAll('td');
        tds.forEach(td => {
            td.style.padding = '0';
            td.style.border = 'none';
            if (td.getAttribute('dir') === 'ltr') {
                td.classList.add('word-en-header');
                td.setAttribute('width', '40%');
                td.style.width = '40%';
                td.style.textAlign = 'left';
                td.style.fontFamily = '"Times New Roman", Georgia, serif';
                td.style.fontSize = '11pt';
                td.style.fontWeight = 'bold';
                td.style.lineHeight = '1.2';
            } else if (td.querySelector('img')) {
                td.setAttribute('width', '20%');
                td.setAttribute('align', 'center');
                td.style.width = '20%';
                td.style.textAlign = 'center';
                td.style.verticalAlign = 'middle';
            } else {
                td.classList.add('word-ar-header');
                td.setAttribute('width', '40%');
                td.style.width = '40%';
                td.style.textAlign = 'right';
                applyArabicTypography(td, 14, 1.2, { fontWeight: 'bold' });
            }
        });
    });

    // 3. تهيئة الشعار بالنسبة الأصلية (259x192 -> 92x68) ليعمل مضمناً داخل مستند Word على صفحة واحدة
    const logoBase64 = @json($wordLogoBase64 ?? '');
    clone.querySelectorAll('img').forEach(img => {
        if (logoBase64) {
            img.setAttribute('src', 'logo.png');
        }
        img.setAttribute('width', '92');
        img.setAttribute('height', '68');
        img.setAttribute('border', '0');
        img.style.width = '92px';
        img.style.height = '68px';
    });

    // ضبط أحجام الخطوط والأبعاد لتتطابق مع النسخة المطبوعة وتبقى ضمن صفحة A4 واحدة
    clone.querySelectorAll('.decision-title-div, .addressee-div').forEach(el => {
        applyArabicTypography(el, 16, 1.2, {
            fontWeight: 'bold',
            textAlign: 'center',
            marginTop: '4pt',
            marginBottom: '6pt'
        });
        el.querySelectorAll('span, strong').forEach(sp => {
            sp.style.fontFamily = '';
            sp.style.fontSize = '';
        });
    });

    clone.querySelectorAll('.preamble-div').forEach(el => {
        applyArabicTypography(el, 14, 1.2, {
            textAlign: 'justify',
            marginBottom: '5pt'
        });
        const children = el.querySelectorAll(':scope > div');
        children.forEach((child, idx) => {
            const isFirstBoldHeading = idx === 0 && (child.style.fontWeight === '700' || child.style.fontWeight === 'bold' || child.textContent.includes('رئيس لجنة'));
            applyArabicTypography(child, isFirstBoldHeading ? 14.5 : 14, 1.2, {
                fontWeight: isFirstBoldHeading ? 'bold' : 'normal',
                marginBottom: '1pt',
                textAlign: 'justify'
            });
            child.querySelectorAll('span, strong').forEach(sp => {
                sp.style.fontFamily = '';
                sp.style.fontSize = '';
            });
        });
    });

    clone.querySelectorAll('.decree-header-div').forEach(el => {
        const isRejHeader = el.classList.contains('rejection-decree-header');
        applyArabicTypography(el, 15.5, 1.2, {
            fontWeight: 'bold',
            textAlign: isRejHeader ? 'right' : 'center',
            textDecoration: isRejHeader ? 'none' : 'underline',
            marginTop: '5pt',
            marginBottom: '5pt'
        });
    });

    clone.querySelectorAll('.article-div').forEach(el => {
        applyArabicTypography(el, 15, 1.32, {
            textAlign: 'justify',
            marginBottom: '5pt'
        });
        el.querySelectorAll('span, strong').forEach(sp => {
            sp.style.fontFamily = '';
            sp.style.fontSize = '';
        });
        el.querySelectorAll('div').forEach(sub => {
            if (sub.textContent.includes('دمشق في')) {
                applyArabicTypography(sub, 15, 1.25, {
                    textAlign: 'center',
                    marginTop: '2pt',
                    paddingLeft: '82pt'
                });
            }
        });
    });

    // ضبط عبارة "يرجى الاطلاع" في كتب عدم الموافقة
    clone.querySelectorAll('.decision-main-content > div').forEach(el => {
        if (el.textContent.trim() === 'يرجى الاطلاع') {
            applyArabicTypography(el, 15.5, 1.2, {
                fontWeight: 'bold',
                textAlign: 'center',
                marginTop: '6pt',
                marginBottom: '10pt'
            });
        }
    });

    // 4. بناء تذييل (صورة إلى) وتثبيته في أسفل الصفحة A4 عبر جدول التخطيط الكامل (ضمن صفحة واحدة)
    const copiesDiv = clone.querySelector('.copies-div');
    let copiesRowHtml = '';
    if (copiesDiv) {
        let copiesLinesHtml = '';
        copiesDiv.querySelectorAll(':scope > div').forEach((line, idx) => {
            const isHeader = idx === 0 || line.textContent.includes('صورة إلى');
            const lineStyle = [
                `font-family: ${wordFontFamily}`,
                'mso-ascii-font-family: Traditional Arabic',
                'mso-hansi-font-family: Traditional Arabic',
                'mso-bidi-font-family: Traditional Arabic',
                'font-size: 11pt',
                'mso-bidi-font-size: 11pt',
                'line-height: 1.18',
                'text-align: right',
                'direction: rtl',
                isHeader ? 'font-weight: bold; text-decoration: underline; margin: 0 0 1pt 0;' : 'font-weight: normal; margin: 0;'
            ].join('; ') + ';';
            copiesLinesHtml += `<p dir="rtl" align="right" style="${lineStyle}">${line.innerHTML}</p>`;
        });

        copiesRowHtml = `
        <tr>
            <td valign="bottom" align="right" style="vertical-align: bottom; text-align: right; direction: rtl; padding: 4pt 0 0 0; border: none; border-top: 1.5pt solid #1e293b;">
                ${copiesLinesHtml}
            </td>
        </tr>`;
        copiesDiv.remove();
    }

    // استخراج المحتوى الرئيسي وإدراجه في الصف العلوي بارتفاع متوازن يبقي المستند كاملاً في صفحة A4 واحدة
    const mainContent = clone.querySelector('.decision-main-content');
    const mainInnerHtml = mainContent ? mainContent.innerHTML : clone.innerHTML;

    const wordInnerHtml = `
    <table class="word-outer-layout" border="0" cellpadding="0" cellspacing="0" dir="rtl" style="width: 100%; border-collapse: collapse; border: none; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
        <tr style="height: 630pt; mso-height-rule: atLeast;">
            <td valign="top" style="vertical-align: top; padding: 0; border: none;">
                ${mainInnerHtml}
            </td>
        </tr>
        ${copiesRowHtml}
    </table>
    <p style="font-size: 1pt; line-height: 1pt; margin: 0; padding: 0;">&nbsp;</p>`;

    const htmlBody = `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40"
      dir="rtl" lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="utf-8">
<meta name="ProgId" content="Word.Document">
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
 <o:OfficeDocumentSettings>
  <o:AllowPNG/>
 </o:OfficeDocumentSettings>
</xml>
<![endif]-->
<style>
    @page WordSection1 {
        size: 210mm 297mm;
        margin: 9mm 13mm 8mm 13mm;
        mso-header-margin: 0mm;
        mso-footer-margin: 0mm;
        mso-paper-source: 0;
    }
    div.WordSection1 {
        page: WordSection1;
        direction: rtl;
    }
    body {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-ascii-font-family: "Traditional Arabic";
        mso-hansi-font-family: "Traditional Arabic";
        mso-bidi-font-family: "Traditional Arabic";
        direction: rtl;
        text-align: right;
        color: #000000;
        font-size: 14.5pt;
        mso-bidi-font-size: 14.5pt;
        line-height: 1.25;
        margin: 0;
        padding: 0;
    }
    p {
        margin: 0;
        padding: 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
    }
    td {
        vertical-align: top;
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
    }
    td.word-ar-header, td.word-ar-header * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-ascii-font-family: "Traditional Arabic";
        mso-hansi-font-family: "Traditional Arabic";
        mso-bidi-font-family: "Traditional Arabic";
        font-size: 14pt;
        mso-bidi-font-size: 14pt;
        font-weight: bold;
    }
    td.word-en-header, td.word-en-header *, [dir="ltr"], [dir="ltr"] * {
        font-family: "Times New Roman", Georgia, serif;
        mso-ascii-font-family: "Times New Roman";
        mso-hansi-font-family: "Times New Roman";
        font-size: 11pt;
    }
    .decision-title-div, .decision-title-div *, .addressee-div, .addressee-div * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
        text-align: center;
        font-size: 16pt;
        mso-bidi-font-size: 16pt;
        font-weight: bold;
    }
    .preamble-div, .preamble-div * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
        font-size: 14pt;
        mso-bidi-font-size: 14pt;
        line-height: 1.2;
        text-align: justify;
    }
    .decree-header-div, .decree-header-div * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
        text-align: center;
        font-size: 15.5pt;
        mso-bidi-font-size: 15.5pt;
        font-weight: bold;
        text-decoration: underline;
    }
    .decree-header-div.rejection-decree-header, .decree-header-div.rejection-decree-header * {
        text-align: right;
        text-decoration: none;
    }
    .article-div, .article-div * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
        font-size: 15pt;
        mso-bidi-font-size: 15pt;
        line-height: 1.32;
        text-align: justify;
    }
    .signatures-div td, .signatures-div td * {
        font-family: "Traditional Arabic", "Simplified Arabic", serif;
        mso-bidi-font-family: "Traditional Arabic";
        font-size: 14pt;
        mso-bidi-font-size: 14pt;
        font-weight: bold;
        text-align: center;
        line-height: 1.15;
    }
</style>
</head>
<body dir="rtl" lang="AR-SY">
<div class="WordSection1" dir="rtl">
${wordInnerHtml}
</div>
</body>
</html>`;

    const boundary = '----=_NextPart_MoHE_Decision_Word';
    let mhtmlParts = [
        'MIME-Version: 1.0',
        `Content-Type: multipart/related; boundary="${boundary}"`,
        '',
        `--${boundary}`,
        'Content-Type: text/html; charset="utf-8"',
        'Content-Location: file:///C:/mohe/decision.htm',
        '',
        htmlBody
    ];

    if (logoBase64) {
        mhtmlParts.push(
            '',
            `--${boundary}`,
            'Content-Location: file:///C:/mohe/logo.png',
            'Content-Type: image/png',
            'Content-Transfer-Encoding: base64',
            '',
            logoBase64
        );
    }

    mhtmlParts.push('', `--${boundary}--`, '');
    const mhtmlContent = mhtmlParts.join('\r\n');

    const rawName = String(@json($candidateName ?? '')).replace(/[/\\?%*:|"<>]/g, '').trim();
    const appNo = String(@json($application->application_no ?? $application->id)).replace(/[/\\?%*:|"<>]/g, '').trim();
    const wordPrefix = @json((!empty($isRejected) || ($docType ?? '') === 'rejection') ? 'كتاب_عدم_موافقة_' : 'قرار_معادلة_');
    const fileName = `${wordPrefix}${rawName}_طلب_${appNo}.doc`;

    const blob = new Blob([mhtmlContent], { type: 'application/msword;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function setRejectionReasonText(mode) {
    const target = document.getElementById('live-rej-reason');
    if (!target) return;
    if (mode === 'recorded') {
        const recEl = document.getElementById('recorded-rej-reason-text');
        if (recEl && recEl.textContent.trim() !== '') {
            target.textContent = recEl.textContent.trim();
        }
    } else {
        target.textContent = 'نظراً لعدم تحقيق شروط معادلة الشهادات الواردة في قرارات مجلس التعليم العالي .';
    }
}
</script>
@endsection
<!-- MOZHAKKARA DOCUMENT PAPER SNIPPET -->
<div class="moz-wrapper">
    <div class="moz-header">
        <div class="moz-header-text text-start">
            <div class="ar">الجمهورية العربية السورية</div>
            <div class="ar">وزارة التعليم العالي والبحث العلمي</div>
            <div class="en">MINISTRY OF HIGHER EDUCATION AND SCIENTIFIC RESEARCH</div>
        </div>
        <div class="mohe-emblem-ring">
            <img src="{{ asset('assets/logo.jpg') }}" alt="شعار الوزارة" onerror="this.onerror=null; this.src='{{ asset('images/mohe_logo.jpg') }}';">
        </div>
    </div>
    
    @if($application->request_type == 'تحويل قرار المعادلة')
        <div class="moz-title" style="color: #1E40AF;">(مذكرة عرض - تحويل قرار المعادلة ونقل التكليف)</div>
    @elseif($application->request_type == 'إضافة مقررات دراسية')
        <div class="moz-title" style="color: #059669;">(مذكرة عرض - إضافة مقررات دراسية جديدة)</div>
    @else
        <div class="moz-title">(مذكرة العرض)</div>
    @endif

    {{-- 1. البيانات الشخصية --}}
    <div class="moz-section"><i class="fa-solid fa-user me-1"></i> البيانات الشخصية للمرشح :</div>
    <table class="mt">
        <tr>
            <td class="l">نوع الطلب :</td>
            <td style="font-weight:bold; color: var(--imperial-navy);">
                @if($application->request_type == 'تحويل قرار المعادلة')
                    <span style="color: #1E40AF; font-weight: bold;"><i class="fa-solid fa-right-left me-1"></i> تحويل قرار المعادلة</span>
                @elseif($application->request_type == 'إضافة مقررات دراسية')
                    <span style="color: #059669; font-weight: bold;"><i class="fa-solid fa-book-medical me-1"></i> إضافة مقررات دراسية</span>
                @else
                    {{ $application->request_type ?? 'تعادل' }}
                @endif
            </td>
            <td class="l">ID :</td>
            <td class="fw-bold">{{ $candidate->id }}</td>
        </tr>
    </table>
    
    <div class="cname">اسم المرشح : {{ $candidate->full_name }}</div>
    
    <table class="mt">
        <tr><td class="l">الرقم الوطني :</td><td class="fw-bold">{{ $candidate->national_id }}</td><td class="l">الجنسية :</td><td>{{ $candidate->is_syrian ? 'سورية' : 'غير سورية' }}</td></tr>
        <tr><td class="l">تاريخ الميلاد :</td><td>{{ $candidate->dob }}</td><td class="l">الوظيفة :</td><td>{{ $candidate->job_title }}</td></tr>
        <tr><td class="l">رقم الهاتف :</td><td>{{ $candidate->phone }}</td><td class="l">رقم الجوال :</td><td>{{ $candidate->mobile }}</td></tr>
        <tr><td class="l">البريد الإلكتروني :</td><td colspan="3" style="color: var(--imperial-navy); font-weight: 600;">{{ $candidate->email }}</td></tr>
        <tr><td class="l">العنوان :</td><td colspan="3">{{ $candidate->address }}</td></tr>
    </table>
    
    @if($application->request_type == 'تحويل قرار المعادلة')
    @php
        $parentApp = $application->parentApplication;
        $parentDecision = $parentApp ? $parentApp->latestDecision : null;
    @endphp
    <!-- TRANSFER EQUIVALENCE COMPARISON BLOCK -->
    <div style="margin: 15px 0; border: 2px dashed #3B82F6; border-radius: 6px; padding: 12px; background: #F0F9FF;">
        <div style="font-weight: bold; color: #1E40AF; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #BAE6FD; padding-bottom: 5px;">
            <i class="fa-solid fa-right-left me-1"></i> تفاصيل تحويل قرار المعادلة ونقل التكليف بين الجامعات:
        </div>
        <div class="row g-3" style="display: flex; gap: 15px; flex-wrap: wrap;">
            <!-- Previous University & Decision -->
            <div style="flex: 1; min-width: 280px; background: #FFFFFF; border: 1px solid #CBD5E1; border-right: 4px solid #EF4444; padding: 10px 14px; border-radius: 4px;">
                <div style="font-weight: bold; color: #991B1B; font-size: 13px; margin-bottom: 6px;">
                    <i class="fa-solid fa-building-columns me-1"></i> 1. جهة التكليف والقرار السابق (المعادلة الأولى):
                </div>
                <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 3px 0; color: #475569; width: 120px;"><b>الجامعة السابقة:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #0F172A;">{{ optional(optional($parentApp)->workUniversity)->name ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #475569;"><b>الكلية والقسم:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #0F172A;">{{ optional($parentApp)->work_faculty ?? '---' }} / {{ optional($parentApp)->work_department ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #475569;"><b>قرار المعادلة السابق:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #B91C1C;">
                            {{ $parentDecision ? 'رقم (' . $parentDecision->decision_no . ') بتاريخ ' . $parentDecision->decision_date : 'تم الصدور أصولاً' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- New University & Request -->
            <div style="flex: 1; min-width: 280px; background: #FFFFFF; border: 1px solid #CBD5E1; border-right: 4px solid #10B981; padding: 10px 14px; border-radius: 4px;">
                <div style="font-weight: bold; color: #065F46; font-size: 13px; margin-bottom: 6px;">
                    <i class="fa-solid fa-graduation-cap me-1"></i> 2. جهة التكليف الجديدة والمراد النقل إليها (المعادلة المحوّلة):
                </div>
                <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 3px 0; color: #475569; width: 130px;"><b>الجامعة الجديدة:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #065F46;">{{ optional($application->workUniversity)->name ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #475569;"><b>الكلية والقسم الجديد:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #0F172A;">{{ $application->work_faculty ?? '---' }} / {{ $application->work_department ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #475569;"><b>كتاب الجامعة الجديدة:</b></td>
                        <td style="padding: 3px 0; font-weight: bold; color: #047857;">
                            {{ $application->new_uni_request_no ? 'رقم (' . $application->new_uni_request_no . ') بتاريخ ' . $application->new_uni_request_date : 'مرفق بالطلب' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    @elseif($application->request_type == 'إضافة مقررات دراسية')
    @php
        $parentApp = $application->parentApplication;
        $parentDecision = $parentApp ? $parentApp->latestDecision : null;
    @endphp
    <!-- ADD COURSES BLOCK -->
    <div style="margin: 15px 0; border: 2px dashed #10B981; border-radius: 6px; padding: 12px; background: #ECFDF5;">
        <div style="font-weight: bold; color: #065F46; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #A7F3D0; padding-bottom: 5px;">
            <i class="fa-solid fa-book-medical me-1"></i> تفاصيل طلب إضافة مقررات دراسية جديدة:
        </div>
        <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
            <tr>
                <td style="padding: 3px 0; color: #475569; width: 140px;"><b>الجامعة والكلية والقسم:</b></td>
                <td style="padding: 3px 0; font-weight: bold; color: #065F46;">
                    {{ optional($application->workUniversity)->name }} - {{ $application->work_faculty }} / {{ $application->work_department }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; color: #475569;"><b>قرار المعادلة السابق:</b></td>
                <td style="padding: 3px 0; font-weight: bold; color: #0F172A;">
                    {{ $parentDecision ? 'رقم (' . $parentDecision->decision_no . ') بتاريخ ' . $parentDecision->decision_date : 'تم الصدور أصولاً' }}
                </td>
            </tr>
        </table>
    </div>
    @else
    <div class="wblock" style="background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 4px; margin-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 13.5px; direction: rtl;">
            <tr>
                <td style="width: 34%; border: none; padding: 2px; text-align: right;">
                    <b>المرشح للعمل في قسم:</b> {{ $application->work_department ?? '---' }}
                </td>
                <td style="width: 33%; border: none; padding: 2px; text-align: right;">
                    <b>في كلية:</b> {{ $application->work_faculty ?? '---' }}
                </td>
                <td style="width: 33%; border: none; padding: 2px; text-align: right;">
                    <b>في جامعة:</b> {{ optional($application->workUniversity)->name ?? '---' }}
                </td>
            </tr>
        </table>
        <div style="color: #64748B; font-size: 11.5px; margin-top: 5px; text-align: right;">
            التي تطلب الجامعة تكليفه بتدريسها استناداً إلى قرار معادلة شهادته العلمية.
        </div>
    </div>
    @endif

    {{-- 2. المقررات --}}
    @if($application->request_type == 'تحويل قرار المعادلة')
        @php
            $parentApp = $application->parentApplication;
            $oldUniName = optional(optional($parentApp)->workUniversity)->name ?? 'الجامعة السابقة';
            $newUniName = optional($application->workUniversity)->name ?? 'الجامعة الجديدة';
        @endphp
        <div class="moz-section"><i class="fa-solid fa-book-bookmark me-1"></i> المقررات الدراسية (مقارنة المقررات بين الجامعة السابقة والجديدة) :</div>
        
        <!-- Previous University Courses -->
        <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; color: #991B1B; font-size: 13px;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> أولاً: المقررات التي تم تكليفه بها سابقاً في ({{ $oldUniName }}) :
        </div>
        <table class="ct" style="margin-bottom: 15px;">
            <thead>
                <tr style="background: #991B1B;">
                    <th style="background: #991B1B; width: 40px;">#</th>
                    <th style="background: #991B1B;">اسم المقرر الدراسي (في الجامعة السابقة)</th>
                    <th style="background: #991B1B;">الكلية والفرع</th>
                    <th style="background: #991B1B;">حالة المقرر</th>
                </tr>
            </thead>
            <tbody>
                @if($parentApp && $parentApp->courses && $parentApp->courses->count() > 0)
                    @foreach($parentApp->courses as $idx => $c)
                    <tr>
                        <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                        <td style="font-weight:bold; color: #991B1B;">{{ $c->course_name }}</td>
                        <td>{{ $c->faculty ?? $parentApp->work_faculty }} - {{ $c->department ?? $parentApp->work_department }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $c->course_status ?? 'مستوفى' }}</span></td>
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" style="color: #64748B; text-align: center;">لم تكن هناك مقررات دراسية مطالب بها في القرار السابق.</td></tr>
                @endif
            </tbody>
        </table>

        <!-- New University Courses -->
        <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; color: #065F46; font-size: 13px;">
            <i class="fa-solid fa-plus-circle me-1"></i> ثانياً: المقررات الدراسية المحدّثة المطلوب تكليفه بها في ({{ $newUniName }}) :
        </div>
        <table class="ct" style="margin-bottom: 15px;">
            <thead>
                <tr style="background: #065F46;">
                    <th style="background: #065F46; width: 40px;">#</th>
                    <th style="background: #065F46;">اسم المقرر الدراسي (في الجامعة الجديدة)</th>
                    <th style="background: #065F46;">الكلية والفرع</th>
                    <th style="background: #065F46;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($application->courses as $idx => $c)
                <tr>
                    <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="font-weight:bold; color: #065F46;">{{ $c->course_name }}</td>
                    <td>{{ $application->work_faculty }} - {{ $application->work_department }}</td>
                    <td><span class="badge bg-success-subtle text-success border border-success fw-bold">مطالب به</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات جديدة مضافة في التكليف الجديد.</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($application->request_type == 'إضافة مقررات دراسية')
        @php
            $parentApp = $application->parentApplication;
        @endphp
        <div class="moz-section" style="background-color: #065F46;"><i class="fa-solid fa-book-medical me-1"></i> المقررات الدراسية (المقررات السابقة والمقررات المضافة حديثاً) :</div>
        
        <!-- Previous Courses -->
        <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; color: #475569; font-size: 13px;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> أولاً: المقررات التي تم تكليفه بها سابقاً بموجب القرار الصادر :
        </div>
        <table class="ct" style="margin-bottom: 15px;">
            <thead>
                <tr style="background: #475569;">
                    <th style="background: #475569; width: 40px;">#</th>
                    <th style="background: #475569;">اسم المقرر الدراسي (في القرار السابق)</th>
                    <th style="background: #475569;">الكلية والفرع</th>
                    <th style="background: #475569;">حالة المقرر</th>
                </tr>
            </thead>
            <tbody>
                @if($parentApp && $parentApp->courses && $parentApp->courses->count() > 0)
                    @foreach($parentApp->courses as $idx => $c)
                    <tr>
                        <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                        <td style="font-weight:bold; color: #334155;">{{ $c->course_name }}</td>
                        <td>{{ $c->faculty ?? $parentApp->work_faculty }} - {{ $c->department ?? $parentApp->work_department }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $c->course_status ?? 'مستوفى' }}</span></td>
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" style="color: #64748B; text-align: center;">لم تكن هناك مقررات دراسية مطالب بها في القرار السابق.</td></tr>
                @endif
            </tbody>
        </table>

        <!-- New Added Courses -->
        <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; color: #065F46; font-size: 13px;">
            <i class="fa-solid fa-plus-circle me-1"></i> ثانياً: المقررات الدراسية الجديدة المضافة بطلب الإضافة الحالي :
        </div>
        <table class="ct" style="margin-bottom: 15px;">
            <thead>
                <tr style="background: #065F46;">
                    <th style="background: #065F46; width: 40px;">#</th>
                    <th style="background: #065F46;">اسم المقرر الدراسي الجديد المضاف</th>
                    <th style="background: #065F46;">الكلية والفرع</th>
                    <th style="background: #065F46;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($application->courses as $idx => $c)
                <tr>
                    <td style="font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="font-weight:bold; color: #065F46;">{{ $c->course_name }}</td>
                    <td>{{ $application->work_faculty }} - {{ $application->work_department }}</td>
                    <td><span class="badge bg-success-subtle text-success border border-success fw-bold">مُضاف حديثاً</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات مضافة في هذا الطلب.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <div class="moz-section"><i class="fa-solid fa-book-bookmark me-1"></i> المقررات التي يدرسها بموجب قرار لجنة التأهيل ومعادلة الدرجات العلمية :</div>
        <table class="ct">
            <thead>
                <tr>
                    <th>اسم المقرر</th>
                    <th>القسم</th>
                    <th>الكلية</th>
                    <th>حالة المقرر</th>
                </tr>
            </thead>
            <tbody>
                @forelse($application->courses as $c)
                <tr>
                    <td style="font-weight:bold; color: var(--imperial-navy);">{{ $c->course_name }}</td>
                    <td>{{ $c->department }}</td>
                    <td>{{ $c->faculty }}</td>
                    <td>{{ $c->course_status }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:#b91c1c; font-weight:bold; text-align:center;">لا توجد مقررات تطلبها الجامعة</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 3. الشهادات --}}
    <div class="moz-section"><i class="fa-solid fa-award me-1"></i> الشهادات التي يحملها المرشح :</div>

    {{-- ثانوية --}}
    <div class="dblock">
        <div class="dblock-h">الشهادة الثانوية :</div>
        <table class="mt">
            <tr>
                <td class="l">الدولة المانحة :</td><td>{{ optional(optional($highSchoolEd)->country)->name ?? '---' }}</td>
                <td class="l">القسم :</td><td>{{ optional($highSchoolEd)->section_name ?? '---' }}</td>
                <td class="l">تاريخ المنح :</td><td>{{ optional($highSchoolEd)->grant_date ?? '---' }}</td>
            </tr>
        </table>
    </div>

    {{-- إجازة جامعية --}}
    @if($bachelorEd)
    <div class="dblock">
        <div class="dblock-h">شهادة الإجازة الجامعية :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($bachelorEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($bachelorEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $bachelorEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $bachelorEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $bachelorEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $bachelorEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td colspan="3">{{ $bachelorEd->grant_date ?? '---' }}</td></tr>
        </table>
    </div>
    @endif

    {{-- ماجستير --}}
    @if($masterEd)
    <div class="dblock">
        <div class="dblock-h">شهادة ماجستير {{ optional(optional($masterEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($masterEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($masterEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $masterEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $masterEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $masterEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $masterEd->registration_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $masterEd->grant_date ?? '---' }}</td><td class="l">اسم المشرف :</td><td>{{ $masterEd->supervisor_name ?? '---' }}</td></tr>
            <tr><td class="l">عنوان الأطروحة :</td><td colspan="5">{{ $masterEd->thesis_title ?? '---' }}</td></tr>
        </table>
    </div>
    @endif

    {{-- دكتوراه --}}
    @if($phdEd)
    <div class="dblock">
        <div class="dblock-h">شهادة دكتوراه {{ optional(optional($phdEd)->country)->name == 'سوريا' ? 'سوريا' : 'غير سورية' }} :</div>
        <table class="mt">
            <tr><td class="l">الدولة المانحة :</td><td>{{ optional($phdEd->country)->name ?? '---' }}</td><td class="l">الجهة المانحة :</td><td colspan="3">{{ optional($phdEd->university)->name ?? '---' }}</td></tr>
            <tr><td class="l">التخصص العام :</td><td>{{ $phdEd->general_specialization ?? '---' }}</td><td class="l">التخصص الدقيق :</td><td>{{ $phdEd->exact_specialization ?? '---' }}</td><td class="l">المرتبة :</td><td>{{ $phdEd->rank ?? '---' }}</td></tr>
            <tr><td class="l">تاريخ التسجيل :</td><td>{{ $phdEd->registration_date ?? '---' }}</td><td class="l">تاريخ المناقشة :</td><td>{{ $phdEd->defense_date ?? '---' }}</td><td class="l">تاريخ المنح :</td><td>{{ $phdEd->grant_date ?? '---' }}</td></tr>
            <tr><td class="l">اسم المشرف :</td><td>{{ $phdEd->supervisor_name ?? '---' }}</td><td class="l">عنوان الأطروحة :</td><td colspan="3">{{ $phdEd->thesis_title ?? '---' }}</td></tr>
        </table>
    </div>
    @endif

    {{-- 4. معلومات إضافية --}}
    <div class="moz-section"><i class="fa-solid fa-circle-info me-1"></i> معلومات إضافية :</div>
    <div class="dblock">
        <table class="mt">
            <tr>
                <td style="color: #1A2A44; font-weight: bold;">{{ $candidate->is_syrian ? 'نعم' : 'لا' }}</td>
                <td class="l" style="width: 70%;">هل المرشح جنسيته السورية :</td>
            </tr>
            <tr>
                <td style="color: #1A2A44; font-weight: bold;">{{ $application->has_previous_degree ? 'نعم' : 'لا' }}</td>
                <td class="l" style="width: 70%;">هل المرشح حاصل على مؤهل علمي قبل المؤهل الأخير :</td>
            </tr>
            <tr>
                <td style="color: #1A2A44; font-weight: bold;">{{ $application->study_system ?? 'فصلي' }}</td>
                <td class="l" style="width: 70%;">نظام دراسة المرشح :</td>
            </tr>
        </table>
    </div>
</div>

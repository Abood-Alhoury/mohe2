<table class="modern-table" id="dashboard-table">
    <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>اسم المرشح (الطالب)</th>
            <th>نوع التعادل</th>
            <th>القسم المرشح له</th>
            <th>حالة الطلب الحالية</th>
            <th>تاريخ التقديم</th>
            <th style="text-align: center;">الإجراءات والخدمات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($recentApplications as $app)
            @php
                $reqType = (string)$app->request_type;
                $reqTypeId = (int)$app->request_type_id;
                $isDoc = $reqTypeId === 3 || $reqTypeId === 7 || str_contains($reqType, 'دكتور') || str_contains($reqType, 'دكتورة');
                $isMasterTheo = $reqTypeId === 2 || $reqTypeId === 6 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'نظري'));
                $isMasterApp = $reqTypeId === 1 || $reqTypeId === 5 || (str_contains($reqType, 'ماجستير') && str_contains($reqType, 'تطبيقي'));
                $isFac = $reqTypeId === 4 || str_contains($reqType, 'سماح') || str_contains($reqType, 'تدريس');
            @endphp
            <tr>
                <td style="font-family: monospace; font-weight: 800; color: #475569;">
                    {{ $app->application_no ?? ('#' . $app->id) }}
                </td>
                <td>
                    <strong style="font-size: 0.95rem; color: var(--secondary-navy);">{{ optional($app->candidate)->full_name ?? 'غ/م' }}</strong>
                    <span style="display: block; font-size: 0.75rem; color: #94a3b8; font-family: monospace;">{{ optional($app->candidate)->national_id ?? optional($app->candidate)->id_number ?? '—' }}</span>
                </td>
                <td>
                    @if($isDoc)
                        <span class="pill-degree"><i class="fa-solid fa-graduation-cap"></i> {{ $app->request_type ?? 'دكتوراه داخلي' }}</span>
                    @elseif($isMasterTheo)
                        <span class="pill-degree" style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;"><i class="fa-solid fa-book-open"></i> {{ $app->request_type }}</span>
                    @elseif($isMasterApp)
                        <span class="pill-degree" style="background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4;"><i class="fa-solid fa-briefcase"></i> {{ $app->request_type }}</span>
                    @elseif($isFac)
                        <span class="pill-degree" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a;"><i class="fa-solid fa-chalkboard-user"></i> {{ $app->request_type }}</span>
                    @else
                        <span class="pill-degree"><i class="fa-solid fa-graduation-cap"></i> {{ $app->request_type ?? 'تعادل' }}</span>
                    @endif
                </td>
                <td style="font-weight: 600; color: #334155;">
                    {{ $app->work_faculty ?? 'غير محدد' }} {{ $app->work_department ? '- ' . $app->work_department : '' }}
                </td>
                <td>
                    @if($app->status == 'تم الصدور' || $app->status == 'موافقة')
                        <span class="status-badge-issued">
                            <i class="fa-solid fa-circle-check"></i> تم الصدور
                        </span>
                    @elseif($app->status == 'تحت التدقيق الأولي' || $app->status == 'قيد الدراسة')
                        <span class="status-badge-study">
                            <i class="fa-solid fa-magnifying-glass"></i> تحت التدقيق الأولي
                        </span>
                    @elseif($app->status == 'بانتظار الوثائق')
                        <span class="status-badge-paper">
                            <i class="fa-solid fa-file-circle-exclamation"></i> بانتظار الوثائق
                        </span>
                    @else
                        <span class="status-badge-suspended">
                            <i class="fa-solid fa-pause"></i> {{ $app->status }}
                        </span>
                    @endif
                </td>
                <td style="color: var(--text-muted); font-size: 0.88rem;">
                    {{ $app->created_at ? $app->created_at->format('d/m/Y') : 'غ/م' }}
                </td>
                <td style="text-align: center;">
                    <div class="action-cell-cluster">
                        {{-- 1. The Smart Stamp Icon (Downloads Zip bundle or single PDF) --}}
                        @if($app->status == 'تم الصدور' && $app->latestDecision)
                            <a href="{{ route('university.applications.view_decision_pdf', $app->id) }}" target="_blank" class="btn-stamp-action" title="تحميل وتنزيل وثائق القرار الصادر الرسمية">
                                <i class="fa-solid fa-stamp"></i>
                            </a>
                        @endif

                        {{-- 2. Edit Action (for drafts or waiting for documents) --}}
                        @if(in_array($app->status, ['بانتظار الوثائق', 'مسودة']) || in_array($app->status_id, [1, 3, 4]))
                            <a href="{{ route('university.applications.edit', $app->id) }}" class="btn-soft-action" style="color: #c59b27 !important;" title="تعديل الطلب واستكمال الوثائق">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        @endif

                        {{-- 3. View Dossier 360 --}}
                        <a href="{{ route('university.applications.show', $app->id) }}" class="btn-soft-action" title="معاينة إضبارة المعاملة كاملة">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>

                        {{-- 4. Direct Message / Notification --}}
                        <a href="{{ route('university.messages') }}?application_id={{ $app->id }}" class="btn-soft-action" title="سجل المراسلات مع الوزارة">
                            <i class="fa-solid fa-comment-dots text-muted"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 opacity-50 mb-2 d-block"></i>
                    <span>لا توجد طلبات مسجلة حالياً تابعة للجامعة.</span>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

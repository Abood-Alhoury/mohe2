<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: var(--primary-container); text-decoration: none;">لوحة الإدارة</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.applications.index') }}" style="color: var(--primary-container); text-decoration: none;">سجلات وطلبات المعادلة</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">تعديل الطلب ({{ $wizardTitle ?? $draft->request_type }})</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; border-right: 4px solid var(--heritage-gold) !important; background: linear-gradient(135deg, #ffffff 0%, #faf8f2 100%);">
            <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="headline-md text-prestigious mb-0" style="font-size: 1.38rem;">
                            <i class="fa-solid fa-pen-to-square me-1.5" style="color: var(--heritage-gold);"></i>
                            تعديل إداري عبر خطوات الطلب: {{ $wizardTitle ?? ($draft->request_type ?? '') }}
                        </h3>
                        <span class="badge px-2.5 py-1.5 fs-7 fw-bold" style="background-color: #FAF6EE; color: #8A651E; border: 1px solid #D9C394;">
                            #{{ $draft->application_no ?? $draft->id }}
                        </span>
                        <span class="badge bg-info-subtle text-primary border border-info-subtle px-2.5 py-1.5 fs-7 fw-bold">
                            <i class="fa-solid fa-lock me-1"></i> الحالة الحالية ثابتة: {{ $draft->status }}
                        </span>
                    </div>
                    <p class="text-muted fs-7 mb-0">
                        يمكنك التنقل بحرية بين أي خطوة لتعديل البيانات أو استبدال المرفقات في مواقعها مباشرة؛ سيبقى الطلب بنفس حالته الحالية والمرفقات التي لا تستبدلها تبقى محفوظة.
                    </p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div style="min-width: 210px;">
                        <label class="form-label mb-1 fw-bold" style="font-size: 0.72rem; color: var(--imperial-navy);">
                            <i class="fa-solid fa-building-columns me-1" style="color: var(--heritage-gold);"></i> الجامعة الطالبة للتقويم:
                        </label>
                        <select name="work_university_id" form="wizard-form" class="form-select form-select-sm fw-bold academic-input" style="font-size: 0.82rem;">
                            @foreach($universities as $uni)
                                @if((isset($syriaId) && $uni->country_id == $syriaId) || $uni->id == $draft->work_university_id)
                                    <option value="{{ $uni->id }}" {{ old('work_university_id', $draft->work_university_id) == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-end gap-2 mt-3 mt-md-0">
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-navy btn-sm fw-bold px-3 py-2">
                            <i class="fa-solid fa-arrow-right me-1"></i> عودة للطلبات
                        </a>
                        <button type="submit" form="wizard-form" formnovalidate name="action" value="admin_save" class="btn btn-gold-cta btn-sm fw-bold px-3.5 py-2 shadow-xs">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ التعديلات الآن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

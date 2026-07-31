@extends('layouts.admin')

@section('title', 'إعدادات الموقع - إضافات وحسابات')

@section('content')
<div class="mohe-card">
    <div class="mohe-card-header bg-light">
        <h5 class="mohe-card-title text-center w-100">
            <i class="fa-solid fa-sliders me-2 text-warning"></i> إضافات موقع التعادل وإدارة الحسابات
        </h5>
    </div>
    <div class="card-body">

        <!-- Legacy ASPX Navigation Tabs Container -->
        <div class="legacy-tab-bar mb-4">
            <a href="{{ route('admin.settings', ['tab' => 'add_admin']) }}" class="legacy-tab-btn {{ $activeTab == 'add_admin' ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus me-1"></i> إضافة مدير
            </a>
            <a href="{{ route('admin.settings', ['tab' => 'add_university']) }}" class="legacy-tab-btn {{ $activeTab == 'add_university' ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns me-1"></i> إضافة جامعة
            </a>
            <a href="{{ route('admin.settings', ['tab' => 'uni_accounts']) }}" class="legacy-tab-btn {{ $activeTab == 'uni_accounts' ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear me-1"></i> حساب الجامعات
            </a>
            <a href="{{ route('admin.settings', ['tab' => 'add_country']) }}" class="legacy-tab-btn {{ $activeTab == 'add_country' ? 'active' : '' }}">
                <i class="fa-solid fa-globe me-1"></i> إضافة دول
            </a>
            <a href="{{ route('admin.settings', ['tab' => 'add_level']) }}" class="legacy-tab-btn {{ $activeTab == 'add_level' ? 'active' : '' }}">
                <i class="fa-solid fa-award me-1"></i> إضافة مرتبة علمية
            </a>
            <a href="{{ route('admin.settings', ['tab' => 'site_lock']) }}" class="legacy-tab-btn {{ $activeTab == 'site_lock' ? 'active' : '' }}">
                <i class="fa-solid fa-power-off me-1"></i> إغلاق الموقع
            </a>
        </div>

        <!-- TAB CONTENT 1: ADD ADMIN -->
        @if($activeTab == 'add_admin')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border border-primary-subtle shadow-sm mb-4">
                    <div class="card-header bg-primary text-white font-bold">
                        <i class="fa-solid fa-user-shield me-2"></i> إضافة مدير نظام جديد (Admin)
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.store_admin') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم المدير :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="اسم المدير الكامل" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم المستخدم / البريد :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="email" class="form-control" placeholder="اسم المستخدم أو الإيميل" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">كلمة المرور :</label>
                                <div class="col-sm-9">
                                    <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="fa-solid fa-plus me-1"></i> Insert</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin Users Table -->
                <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-table-list me-2"></i> قائمة مدراء النظام المسجلين</h6>
                <table class="table mohe-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>اسم المدير</th>
                            <th>اسم المستخدم / البريد</th>
                            <th>تاريخ الإضافة</th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $adm)
                        <tr>
                            <td class="fw-bold">{{ $adm->id }}</td>
                            <td class="fw-bold text-dark">{{ $adm->name }}</td>
                            <td class="text-primary">{{ $adm->email }}</td>
                            <td class="fs-7 text-muted">{{ $adm->created_at ? $adm->created_at->format('Y-m-d') : 'قديم' }}</td>
                            <td>
                                <form action="{{ route('admin.settings.delete_user', $adm->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من حذف حساب المدير هذا؟');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CONTENT 2: ADD UNIVERSITY -->
        @elseif($activeTab == 'add_university')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border border-info-subtle shadow-sm mb-4">
                    <div class="card-header bg-info text-white font-bold">
                        <i class="fa-solid fa-building-columns me-2"></i> إضافة جامعة جديدة إلى دليل الجامعات المعترف بها
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.store_university') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم الجامعة :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="اسم الجامعة الكامل" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">الدولة :</label>
                                <div class="col-sm-9">
                                    <select name="country_id" class="form-select">
                                        <option value="">-- اختر الدولة --</option>
                                        @foreach($countries as $cnt)
                                            <option value="{{ $cnt->id }}">{{ $cnt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-info text-white px-5 fw-bold"><i class="fa-solid fa-plus me-1"></i> Insert</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-info"><i class="fa-solid fa-list me-2"></i> دليل الجامعات المضافة</h6>
                <table class="table mohe-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>اسم الجامعة</th>
                            <th>الدولة</th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($universities as $uni)
                        <tr>
                            <td class="fw-bold">{{ $uni->id }}</td>
                            <td class="fw-bold text-dark">{{ $uni->name }}</td>
                            <td>{{ $uni->country->name ?? 'سوريا' }}</td>
                            <td>
                                <form action="{{ route('admin.settings.delete_university', $uni->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من الحذف؟');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CONTENT 3: UNIVERSITY ACCOUNTS (Add, Freeze, Yellow Card) -->
        @elseif($activeTab == 'uni_accounts')
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card border border-success-subtle shadow-sm mb-4">
                    <div class="card-header bg-success text-white font-bold">
                        <i class="fa-solid fa-users-gear me-2"></i> إنشاء حساب جديد لجامعة سورية وتسليم بيانات الدخول
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.store_uni_account') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم الجامعة :</label>
                                <div class="col-sm-9">
                                    <select name="university_id" class="form-select" required>
                                        <option value="">-- اختر الجامعة --</option>
                                        @foreach($universities as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم المستخدم :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="مثل: uni_damascus" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">البريد الإلكتروني :</label>
                                <div class="col-sm-9">
                                    <input type="email" name="email" class="form-control" placeholder="إيميل دخول الجامعة الرسمي" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">كلمة المرور :</label>
                                <div class="col-sm-9">
                                    <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success px-5 fw-bold"><i class="fa-solid fa-plus me-1"></i> Insert</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-success"><i class="fa-solid fa-shield-halved me-2"></i> إدارة حسابات الجامعات (تفعيل - تجميد - بطاقة صفراء)</h6>
                <table class="table mohe-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>اسم الجامعة</th>
                            <th>اسم المستخدم</th>
                            <th>الحالة والبطاقات</th>
                            <th style="width: 240px;">التحكم بالحساب (تفعيل/تجميد/صفراء)</th>
                            <th style="width: 90px;">حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($universityAccounts as $uAcc)
                        <tr>
                            <td class="fw-bold">{{ $uAcc->id }}</td>
                            <td class="fw-bold text-dark">{{ $uAcc->university->name ?? $uAcc->name }}</td>
                            <td class="text-primary">{{ $uAcc->email }}</td>
                            <td>
                                @if($uAcc->card_status == 'frozen')
                                    <span class="badge bg-danger fs-7"><i class="fa-solid fa-snowflake me-1"></i> حساب مجمّد</span>
                                @elseif($uAcc->card_status == 'yellow_card')
                                    <span class="badge bg-warning text-dark fs-7"><i class="fa-solid fa-triangle-exclamation me-1"></i> بطاقة صفراء 🟨</span>
                                @else
                                    <span class="badge bg-success fs-7"><i class="fa-solid fa-circle-check me-1"></i> نشط مفعل</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.settings.update_uni_status', $uAcc->id) }}" method="POST" class="d-flex gap-1 justify-content-center">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" name="status" value="normal" class="btn btn-xs btn-outline-success {{ $uAcc->card_status == 'normal' ? 'active' : '' }}" title="تفعيل نشط">
                                        تفعيل
                                    </button>
                                    <button type="submit" name="status" value="yellow_card" class="btn btn-xs btn-outline-warning text-dark {{ $uAcc->card_status == 'yellow_card' ? 'active' : '' }}" title="بطاقة صفراء">
                                        🟨 صفراء
                                    </button>
                                    <button type="submit" name="status" value="frozen" class="btn btn-xs btn-outline-danger {{ $uAcc->card_status == 'frozen' ? 'active' : '' }}" title="تجميد الحساب">
                                        تجميد
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.settings.delete_user', $uAcc->id) }}" method="POST" onsubmit="return confirm('حذف حساب هذه الجامعة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CONTENT 4: ADD COUNTRY -->
        @elseif($activeTab == 'add_country')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border border-warning-subtle shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark font-bold">
                        <i class="fa-solid fa-globe me-2"></i> إضافة دولة جديدة لدليل الدول المعترف بشهاداتها
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.store_country') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم الدولة :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="اسم الدولة باللغة العربية" required>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-warning px-5 fw-bold"><i class="fa-solid fa-plus me-1"></i> Insert</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-flag me-2"></i> دليل الدول المضافة</h6>
                <table class="table mohe-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>اسم الدولة</th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($countries as $c)
                        <tr>
                            <td class="fw-bold">{{ $c->id }}</td>
                            <td class="fw-bold text-dark">{{ $c->name }}</td>
                            <td>
                                <form action="{{ route('admin.settings.delete_country', $c->id) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من الحذف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CONTENT 5: ADD EDUCATION LEVEL -->
        @elseif($activeTab == 'add_level')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border border-secondary-subtle shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white font-bold">
                        <i class="fa-solid fa-award me-2"></i> إضافة مرتبة علمية جديدة
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.store_level') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-end fw-bold">اسم المرتبة العلمية :</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="مثل: دكتوراه فخرية / ماجستير تخصص" required>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-secondary px-5 fw-bold"><i class="fa-solid fa-plus me-1"></i> Insert</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-graduation-cap me-2"></i> المراتب والدرجات العلمية المعرفة</h6>
                <table class="table mohe-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>اسم الدرجة / المرتبة العلمية</th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($educationLevels as $lvl)
                        <tr>
                            <td class="fw-bold">{{ $lvl->id }}</td>
                            <td class="fw-bold text-dark">{{ $lvl->name }}</td>
                            <td>
                                <form action="{{ route('admin.settings.delete_level', $lvl->id) }}" method="POST" onsubmit="return confirm('حذف هذه المرتبة العلمية؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB CONTENT 6: SITE LOCK / MAINTENANCE -->
        @elseif($activeTab == 'site_lock')
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border border-danger-subtle shadow-sm mb-4">
                    <div class="card-header bg-danger text-white font-bold">
                        <i class="fa-solid fa-power-off me-2"></i> إعدادات إغلاق / إتاحة الموقع للجامعات السورية
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.toggle_site_lock') }}" method="POST">
                            @csrf
                            <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                                <input class="form-check-input ms-0 me-3" type="checkbox" name="site_locked" id="siteLockedCheck" value="1" {{ $siteLocked ? 'checked' : '' }} style="width: 3em; height: 1.5em;">
                                <label class="form-check-label fw-bold text-danger fs-6" for="siteLockedCheck">
                                    تفعيل حالة إغلاق الموقع أمام الجامعات لتلقي طلبات جديدة (وضع الصيانة والتدقيق)
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">تنبيه أو رسالة تظهر للجامعات أثناء الإغلاق :</label>
                                <textarea name="site_notice" class="form-control" rows="3">{{ $siteNotice }}</textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-danger px-5 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> حفظ إعدادات إغلاق الموقع</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

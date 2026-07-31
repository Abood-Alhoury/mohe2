<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\LookupUniversity;
use App\Models\LookupCountry;
use App\Models\LookupEducationLevel;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'add_admin');

        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->orWhereNull('role_id')->get();

        $universityAccounts = User::whereHas('role', function($q) {
            $q->where('name', 'university');
        })->orWhereNotNull('university_id')->with('university')->get();

        $universities = LookupUniversity::with('country')->get();
        $countries = LookupCountry::all();
        $educationLevels = LookupEducationLevel::all();

        $siteLocked = SiteSetting::get('site_locked', '0') === '1';
        $siteNotice = SiteSetting::get('site_notice', '');

        return view('admin.settings.index', compact(
            'activeTab',
            'admins',
            'universityAccounts',
            'universities',
            'countries',
            'educationLevels',
            'siteLocked',
            'siteNotice'
        ));
    }

    // Tab 1: Add Admin
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        User::create([
            'role_id' => $adminRole->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('admin.settings', ['tab' => 'add_admin'])->with('success', 'تمت إضافة حساب مدير نظام جديد بنجاح');
    }

    // Tab 2: Add University
    public function storeUniversity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'nullable|exists:lookup_countries,id',
        ]);

        LookupUniversity::create([
            'name' => $request->name,
            'country_id' => $request->country_id,
        ]);

        return redirect()->route('admin.settings', ['tab' => 'add_university'])->with('success', 'تمت إضافة الجامعة بنجاح إلى القوائم');
    }

    // Tab 3: University Account (Add, Freeze, Yellow Card, Activate)
    public function storeUniAccount(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:lookup_universities,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $uniRole = Role::firstOrCreate(['name' => 'university']);

        User::create([
            'role_id' => $uniRole->id,
            'university_id' => $request->university_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'card_status' => 'normal',
        ]);

        return redirect()->route('admin.settings', ['tab' => 'uni_accounts'])->with('success', 'تمت إضافة حساب جامعة جديد بنجاح');
    }

    public function updateUniStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $status = $request->input('status'); // 'normal', 'yellow_card', 'frozen'

        if (in_array($status, ['normal', 'yellow_card', 'frozen'])) {
            $user->card_status = $status;
            $user->is_active = ($status !== 'frozen');
            $user->save();
        }

        return redirect()->route('admin.settings', ['tab' => 'uni_accounts'])->with('success', 'تم تحديث حالة حساب الجامعة بنجاح (تفعيل / تجميد / بطاقة صفراء)');
    }

    // Tab 4: Add Country
    public function storeCountry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        LookupCountry::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.settings', ['tab' => 'add_country'])->with('success', 'تمت إضافة الدولة بنجاح');
    }

    // Tab 5: Add Education Level
    public function storeEducationLevel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        LookupEducationLevel::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.settings', ['tab' => 'add_level'])->with('success', 'تمت إضافة المرتبة العلمية بنجاح');
    }

    // Tab 6: Site Lock Toggle
    public function toggleSiteLock(Request $request)
    {
        $locked = $request->has('site_locked') ? '1' : '0';
        SiteSetting::set('site_locked', $locked);
        SiteSetting::set('site_notice', $request->input('site_notice', ''));

        return redirect()->route('admin.settings', ['tab' => 'site_lock'])->with('success', 'تم حفظ إعدادات إغلاق/تفعيل الموقع بنجاح');
    }

    // Delete item helper
    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'تم حذف الحساب بنجاح');
    }

    public function deleteUniversity($id)
    {
        LookupUniversity::findOrFail($id)->delete();
        return back()->with('success', 'تم حذف الجامعة بنجاح');
    }

    public function deleteCountry($id)
    {
        LookupCountry::findOrFail($id)->delete();
        return back()->with('success', 'تم حذف الدولة بنجاح');
    }

    public function deleteEducationLevel($id)
    {
        LookupEducationLevel::findOrFail($id)->delete();
        return back()->with('success', 'تم حذف المرتبة العلمية بنجاح');
    }
}

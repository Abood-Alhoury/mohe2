<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\LookupUniversity;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $uniId = $user->university_id;

        if (!$uniId) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'هذا المستخدم ليس مرتبطاً بأي جامعة.']);
        }

        $universityName = $user->university ? $user->university->name : 'الجامعة';

        $totalApps = Application::where('work_university_id', $uniId)->count();
        $underStudyCount = Application::where('work_university_id', $uniId)->where('status', 'تحت التدقيق الأولي')->count();
        
        $approvedCount = Application::where('work_university_id', $uniId)
            ->where('status', 'تم الصدور')
            ->count();

        $recentApplications = Application::where('work_university_id', $uniId)
            ->with(['candidate'])
            ->latest()
            ->take(10)
            ->get();

        // Get unread notifications for the notifications center
        $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->get();

        return view('university.dashboard', compact(
            'totalApps',
            'underStudyCount',
            'approvedCount',
            'recentApplications',
            'notifications',
            'universityName',
            'user'
        ));
    }

    public function messages()
    {
        $user = Auth::user();
        $uniId = $user->university_id;

        // Fetch all conversations/messages for this university's applications
        $messages = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->with(['application.candidate', 'sender'])
            ->latest()
            ->paginate(15);

        // Mark all as read when visiting messages list
        ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                $q->where('work_university_id', $uniId);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get unread notifications again (should be empty now)
        $notifications = collect();

        return view('university.messages.index', compact('messages', 'notifications'));
    }

    public function replyMessage(Request $request, $appId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $app = Application::where('id', $appId)
            ->where('work_university_id', Auth::user()->university_id)
            ->firstOrFail();

        ApplicationMessage::create([
            'application_id' => $app->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'تم إرسال ردكم إلى مدير التعادل بنجاح.');
    }
}

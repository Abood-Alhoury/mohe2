<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\ApplicationMessage;

class PageController extends Controller
{
    public function contact()
    {
        $userApplications = collect();
        if (Auth::check() && Auth::user()->university_id) {
            $userApplications = Application::where('work_university_id', Auth::user()->university_id)
                ->with('candidate')
                ->latest()
                ->get();
        }

        return view('pages.contact', compact('userApplications'));
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:5',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        if (Auth::check()) {
            $user = Auth::user();
            $uniId = $user->university_id;
            $appId = $request->input('application_id');

            // If no specific application selected, get latest application for this university
            if (!$appId && $uniId) {
                $latestApp = Application::where('work_university_id', $uniId)->latest()->first();
                $appId = $latestApp ? $latestApp->id : null;
            }

            if ($appId) {
                ApplicationMessage::create([
                    'application_id' => $appId,
                    'sender_id' => $user->id,
                    'message' => "📬 [استفسار مؤسسي - {$request->subject}]: {$request->message}",
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'تم إرسال استفساركم أصولاً وإشعار مديرية التعادل بمجلس التعليم العالي بنجاح.');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }
}

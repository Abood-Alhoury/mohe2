<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ApplicationMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share unread notifications with Admin Layout
        View::composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $adminId = auth()->id();
                $adminNotifications = ApplicationMessage::where('sender_id', '!=', $adminId)
                    ->where('is_read', false)
                    ->with(['application.candidate', 'application.workUniversity', 'sender'])
                    ->latest()
                    ->take(10)
                    ->get();
                $view->with('adminNotifications', $adminNotifications);
            }
        });

        // Share unread notifications with University Layout
        View::composer('layouts.university', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $uniId = $user->university_id;
                $notifications = ApplicationMessage::whereHas('application', function($q) use ($uniId) {
                        $q->where('work_university_id', $uniId);
                    })
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->with(['application.candidate', 'sender'])
                    ->latest()
                    ->take(10)
                    ->get();
                $view->with('notifications', $notifications);
            }
        });
    }
}

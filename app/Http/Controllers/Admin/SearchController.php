<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\LookupUniversity;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'name_faculty'); // 'name_faculty', 'name_university', 'name_qualification', 'number_date'

        $name = $request->query('name');
        $faculty = $request->query('faculty');
        $universityId = $request->query('university_id');
        $appNo = $request->query('app_no');

        $query = Application::with(['candidate', 'workUniversity', 'educations.level']);

        $hasSearched = false;

        if ($name || $faculty || $universityId || $appNo) {
            $hasSearched = true;

            if ($name) {
                $query->whereHas('candidate', function($q) use ($name) {
                    $q->where('full_name', 'like', "%{$name}%");
                });
            }

            if ($faculty) {
                $query->where('work_faculty', 'like', "%{$faculty}%");
            }

            if ($universityId) {
                $query->where('work_university_id', $universityId);
            }

            if ($appNo) {
                $query->where('application_no', 'like', "%{$appNo}%");
            }
        }

        $results = $hasSearched ? $query->latest()->get() : collect();
        $universities = LookupUniversity::all();

        return view('admin.search.index', compact(
            'mode',
            'name',
            'faculty',
            'universityId',
            'appNo',
            'hasSearched',
            'results',
            'universities'
        ));
    }
}

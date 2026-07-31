<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\EquivalenceProfile;
use App\Models\Education;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupEducationLevel;

class EditApplicationController extends Controller
{
    public function edit($id)
    {
        $application = Application::with([
            'candidate',
            'workUniversity',
            'courses',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences'
        ])->findOrFail($id);

        $countries = LookupCountry::all();
        $universities = LookupUniversity::all();
        $educationLevels = LookupEducationLevel::all();

        // Categorize Educations for Form
        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd = $application->educations->where('level.name', 'دكتوراه')->first();

        return view('admin.applications.edit', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd',
            'countries',
            'universities',
            'educationLevels'
        ));
    }

    // Update Personal Info Section
    public function updateCandidate(Request $request, $id)
    {
        $app = Application::findOrFail($id);
        $candidate = $app->candidate;

        $candidate->update($request->only([
            'full_name',
            'national_id',
            'dob',
            'gender',
            'job_title',
            'phone',
            'mobile',
            'email',
            'address'
        ]));

        return redirect()->back()->with('success', 'تم تحديث البيانات الشخصية للمرشح بنجاح');
    }

    // Update or Create Education Section (Secondary, Master, PhD, etc.)
    public function updateEducation(Request $request, $appId)
    {
        $app = Application::findOrFail($appId);
        $educationId = $request->input('education_id');
        $levelId = $request->input('education_level_id');

        $data = $request->only([
            'country_id',
            'university_id',
            'section_name',
            'general_specialization',
            'exact_specialization',
            'registration_date',
            'grant_date',
            'defense_date',
            'rank',
            'supervisor_name',
            'thesis_title',
            'envoy_decision',
            'envoy_date',
            'experience_from_year',
            'experience_to_year',
            'notes',
        ]);

        if ($educationId) {
            $ed = Education::findOrFail($educationId);
            $ed->update($data);
        } else {
            $data['application_id'] = $app->id;
            $data['education_level_id'] = $levelId;
            Education::create($data);
        }

        return redirect()->back()->with('success', 'تم تحديث المؤهل العلمي للشهادة بنجاح');
    }
}

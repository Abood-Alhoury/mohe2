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
            'educations.residences',
            'educations.attachments.attachmentType'
        ])->findOrFail($id);

        $countries = LookupCountry::all();
        $universities = LookupUniversity::all();
        $educationLevels = LookupEducationLevel::all();

        // Categorize Educations for Form
        $candidate = $application->candidate;

        $isFacultyPermission = str_contains($application->request_type ?? '', 'سماح') || str_contains($application->request_type ?? '', 'تدريسية');

        $govEd = $application->educations->first(function($e) {
            return $e->thesis_title === 'عضو هيئة تدريسية في جامعة حكومية' || (optional($e->level)->name && str_contains(optional($e->level)->name, 'حكومية'));
        });

        $phdEd = $application->educations->first(function($e) {
            return $e->thesis_title === 'شهادة الدكتوراه' || (optional($e->level)->name == 'دكتوراه' && $e->thesis_title !== 'عضو هيئة تدريسية في جامعة حكومية');
        });

        $masterEd = $application->educations->first(function($e) {
            return $e->thesis_title === 'شهادة الماجستير' || (optional($e->level)->name == 'ماجستير' && $e->thesis_title !== 'عضو هيئة تدريسية في جامعة حكومية');
        });

        $highSchoolEd = $application->educations->first(function($e) {
            return optional($e->level)->name && str_contains(optional($e->level)->name, 'ثانوية');
        });

        $bachelorEd = $application->educations->first(function($e) {
            return optional($e->level)->name && str_contains(optional($e->level)->name, 'إجازة');
        });

        $diplomaEd = $application->educations->first(function($e) {
            return optional($e->level)->name && str_contains(optional($e->level)->name, 'دبلوم');
        });

        return view('admin.applications.edit', compact(
            'application',
            'candidate',
            'isFacultyPermission',
            'govEd',
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

    // Update Application & Private University Request Info
    public function updateApplicationDetails(Request $request, $id)
    {
        $app = Application::findOrFail($id);
        $app->update($request->only([
            'new_uni_request_no',
            'new_uni_request_date',
            'work_university_id',
            'work_faculty',
            'work_department',
            'study_system'
        ]));

        return redirect()->back()->with('success', 'تم تحديث بيانات كتاب الجامعة والطلب بنجاح');
    }

    // Update Personal Info Section
    public function updateCandidate(Request $request, $id)
    {
        $app = Application::findOrFail($id);
        $candidate = $app->candidate;

        $candidate->update($request->only([
            'full_name',
            'father_name',
            'mother_name',
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

    // Update or Create Education Section (Secondary, Master, PhD, Public Uni, etc.)
    public function updateEducation(Request $request, $appId)
    {
        $app = Application::findOrFail($appId);
        $educationId = $request->input('education_id');
        $levelId = $request->input('education_level_id');

        $data = array_filter($request->only([
            'country_id',
            'university_id',
            'faculty',
            'department',
            'section_name',
            'general_specialization',
            'exact_specialization',
            'registration_date',
            'graduation_date',
            'grant_date',
            'defense_date',
            'rank',
            'supervisor',
            'supervisor_name',
            'thesis_title',
            'envoy_decision',
            'envoy_date',
            'experience_from_year',
            'experience_to_year',
            'notes',
        ]), function($v) { return $v !== null; });

        if ($educationId) {
            $ed = Education::findOrFail($educationId);
            $ed->update($data);
        } else {
            $data['application_id'] = $app->id;
            $data['education_level_id'] = $levelId;
            $ed = Education::create($data);
        }

        // Handle attachment upload if provided
        if ($request->hasFile('new_attachment')) {
            $file = $request->file('new_attachment');
            $path = $file->store('attachments/' . $app->id, 'public');
            \App\Models\EducationAttachment::create([
                'education_id' => $ed->id,
                'attachment_type_id' => 3,
                'file_path' => $path,
                'notes' => $request->input('attachment_notes', 'وثيقة مرفقة حديثاً من صفحة التعديل الإداري'),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث البيانات والمؤهل العلمي بنجاح');
    }
}

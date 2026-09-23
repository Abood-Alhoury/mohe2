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

        // 1. معالجة اسم الدولة إذا أُرسلت كـ Text Box (للإجازة)
        if ($request->filled('country_name')) {
            $cName = trim($request->input('country_name'));
            $country = LookupCountry::where('name', $cName)->first()
                ?: LookupCountry::create(['name' => $cName]);
            $data['country_id'] = $country->id;
        }

        // 2. معالجة اسم الجامعة إذا أُرسلت كـ Text Box (لماجستير الدكتوراه أو للإجازة)
        if ($request->filled('university_name')) {
            $uniName = trim($request->input('university_name'));
            $uni = LookupUniversity::where('name', $uniName)->first()
                ?: LookupUniversity::where('name', 'like', "%{$uniName}%")->first();

            if ($uni) {
                $data['university_id'] = $uni->id;
                $data['university_other'] = null;
            } else {
                $data['university_id'] = null;
                $data['university_other'] = $uniName;
                $data['university_text'] = $uniName;
            }
        } elseif ($request->filled('university_id')) {
            $data['university_other'] = null;
        }

        if ($educationId) {
            $ed = Education::findOrFail($educationId);
            $ed->update($data);
        } else {
            $data['application_id'] = $app->id;
            $data['education_level_id'] = $levelId;
            $ed = Education::create($data);
        }

        // 3. معالجة المرفق الجديد وحفظه بالرقم المعياري 23 (مرفقات ووثائق أخرى)
        if ($request->hasFile('new_attachment')) {
            $file = $request->file('new_attachment');
            $path = $file->store('attachments/' . $app->id, 'public');

            $levelName = optional($ed->level)->name ?? '';
            $defaultNote = 'وثيقة مرفقة حديثاً من صفحة التعديل الإداري';
            if (str_contains($levelName, 'ماجستير') || $ed->education_level_id == 2) {
                $defaultNote = 'مرفق إضافي - درجة الماجستير';
            } elseif (str_contains($levelName, 'إجازة') || $ed->education_level_id == 1) {
                $defaultNote = 'مرفق إضافي - الإجازة الجامعية';
            } elseif (str_contains($levelName, 'دكتوراه') || $ed->education_level_id == 3) {
                $defaultNote = 'مرفق إضافي - درجة الدكتوراه';
            }

            \App\Models\EducationAttachment::create([
                'education_id'       => $ed->id,
                'attachment_type_id' => 23,
                'file_path'          => $path,
                'notes'              => $request->input('attachment_notes', $defaultNote),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث البيانات والمؤهل العلمي بنجاح');
    }
    /**
     * استعراض ملف المرفق مباشرة في المتصفح بصيغة PDF
     */
  /**
     * معاينة واستعراض ملف المرفق في المتصفح مباشرة
     */
    public function viewAttachment($id)
    {
        $attachment = \App\Models\EducationAttachment::findOrFail($id);
        $filePath = $attachment->file_path;

        $fullPath = storage_path('app/public/' . $filePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $filePath);
        }

        if (!file_exists($fullPath)) {
            abort(404, 'عذراً، الملف غير موجود على الخادم.');
        }

        // قراءة محتوى الملف
        $fileContent = file_get_contents($fullPath);

        // إجبار اسم الملف أن ينتهي دائماً بـ .pdf ليتعرف عليه عارض المتصفح
        $displayName = 'preview_' . $attachment->id . '.pdf';

        return response($fileContent, 200, [
            'Content-Type'              => 'application/pdf',
            'Content-Disposition'       => 'inline; filename="' . $displayName . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Accept-Ranges'             => 'bytes',
        ]);
    }
}
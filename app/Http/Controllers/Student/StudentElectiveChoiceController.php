<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentElectiveChoice;
use App\Models\StudentEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentElectiveChoiceController extends Controller
{
    public function store(Request $request, Student $student, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless((int) $enrollment->student_id === (int) $student->student_id, 404);

        $data = $request->validate([
            'group_id' => [
                'required',
                'exists:elective_groups,group_id',
                Rule::unique('student_elective_choices', 'group_id')->where('enrollment_id', $enrollment->enrollment_id),
            ],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
        ]);

        $enrollment->electiveChoices()->create($data);

        return redirect()->route('students.show', $student)->with('status', 'Elective choice saved.');
    }

    public function destroy(Student $student, StudentEnrollment $enrollment, StudentElectiveChoice $choice): RedirectResponse
    {
        abort_unless((int) $enrollment->student_id === (int) $student->student_id, 404);
        abort_unless((int) $choice->enrollment_id === (int) $enrollment->enrollment_id, 404);

        $choice->delete();

        return redirect()->route('students.show', $student)->with('status', 'Elective choice removed.');
    }
}

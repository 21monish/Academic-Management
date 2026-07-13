<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentEnrollmentController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'semester_id' => [
                'required',
                'exists:semesters,semester_id',
                Rule::unique('student_enrollments', 'semester_id')->where('student_id', $student->student_id),
            ],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'enrolled_on' => ['nullable', 'date'],
            'status' => ['required', 'in:Active,Detained,PassedOut,Withdrawn'],
        ]);

        $student->enrollments()->create($data);

        return redirect()->route('students.show', $student)->with('status', 'Student enrolled.');
    }

    public function update(Request $request, Student $student, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless((int) $enrollment->student_id === (int) $student->student_id, 404);

        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'enrolled_on' => ['nullable', 'date'],
            'status' => ['required', 'in:Active,Detained,PassedOut,Withdrawn'],
        ]);

        $enrollment->update($data);

        return redirect()->route('students.show', $student)->with('status', 'Enrollment updated.');
    }

    public function destroy(Student $student, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless((int) $enrollment->student_id === (int) $student->student_id, 404);

        $enrollment->delete();

        return redirect()->route('students.show', $student)->with('status', 'Enrollment deleted.');
    }
}

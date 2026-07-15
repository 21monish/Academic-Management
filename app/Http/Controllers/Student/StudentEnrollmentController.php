<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Exceptions\HttpResponseException;
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

        $semester = Semester::query()->findOrFail($data['semester_id']);

        $studentType = $student->student_type ?: (in_array($student->admission_type, ['D2D', 'C2D'], true) ? $student->admission_type : 'Regular');

        if (in_array($studentType, ['D2D', 'C2D'], true) && (int) $semester->semester_no < 3) {
            throw new HttpResponseException(
                back()
                    ->withInput()
                    ->withErrors(['semester_id' => $studentType.' students must be enrolled from Semester 3 or higher.'])
            );
        }

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

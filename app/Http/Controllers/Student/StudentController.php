<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Category;
use App\Models\College;
use App\Models\ElectiveGroup;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Services\AccessScopeService;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
        protected AccessScopeService $accessScope
    )
    {
        // Authorization is enforced in each action using authorization policies.
    }

    public function index(Request $request): View
    {
        $students = collect();
        [$students, $filters] = $this->studentService->searchAndFilter($request);

        $colleges = $this->accessScope->applyToColleges(College::query(), $request->user())
            ->orderBy('name')
            ->get(['college_id', 'name']);
        $departments = $this->accessScope->applyToDepartments(\App\Models\Department::query(), $request->user())
            ->orderBy('name')
            ->get(['dept_id', 'name']);
        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), $request->user())
            ->orderBy('name')
            ->get(['programme_id', 'name']);
        $semesters = $this->accessScope->applyToSemesters(Semester::query(), $request->user())
            ->orderBy('semester_no')
            ->get(['semester_id', 'programme_id', 'semester_no', 'academic_year']);
        $categories = Category::query()->orderBy('name')->get(['category_id', 'name']);

        $academicYears = $this->accessScope->applyToAcademicYears(AcademicYear::query(), $request->user())
            ->orderBy('label')
            ->get(['academic_year_id', 'college_id', 'label', 'status', 'is_current']);

        return view('student.index', compact(
            'students',
            'filters',
            'colleges',
            'departments',
            'programmes',
            'semesters',
            'categories',
            'academicYears'
        ));
    }

    public function create(): View
    {
        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']);
        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']);
        $categories = Category::query()->orderBy('name')->get(['category_id', 'name']);
        return view('student.create', compact('colleges', 'programmes', 'categories'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = $this->studentService->create($request->validated(), $request);
        $defaultPassword = $student->dob?->format('dmY') ?: $student->enrollment_no;

        return redirect()->route('students.show', $student)->with(
            'status',
            "Student created. Login account created with username {$student->enrollment_no} and first password {$defaultPassword}. The student must change this password on first login."
        );
    }

    public function show(Student $student): View
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

        $student->load([
            'college',
            'programme',
            'category',
            'userAccount',
            'enrollments.semester',
            'enrollments.academicYear',
            'enrollments.electiveChoices.electiveGroup',
            'enrollments.electiveChoices.subject',
        ]);

        $semesters = Semester::query()
            ->where('programme_id', $student->programme_id)
            ->orderBy('semester_no')
            ->get(['semester_id', 'programme_id', 'semester_no', 'academic_year']);
        $academicYears = AcademicYear::query()
            ->where('college_id', $student->college_id)
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get(['academic_year_id', 'college_id', 'label', 'status', 'is_current']);
        $electiveGroups = ElectiveGroup::query()
            ->with(['curriculum.programme', 'curriculum.semester', 'curriculum.subject'])
            ->whereHas('curriculum', fn ($query) => $query->where('programme_id', $student->programme_id))
            ->orderBy('group_name')
            ->get();
        $subjects = Subject::query()->orderBy('code')->get(['subject_id', 'code', 'name']);

        return view('student.show', compact('student', 'semesters', 'academicYears', 'electiveGroups', 'subjects'));
    }

    public function edit(Student $student): View
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']);
        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']);
        $categories = Category::query()->orderBy('name')->get(['category_id', 'name']);
        return view('student.edit', compact('student', 'colleges', 'programmes', 'categories'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->studentService->update($student, $request->validated(), $request);

        return redirect()->route('students.show', $student)->with('status', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->studentService->delete($student);

        return redirect()->route('students.index')->with('status', 'Student deleted.');
    }

    public function activate(Student $student): RedirectResponse
    {
        $this->studentService->setActive($student, true);

        return redirect()->route('students.index')->with('status', 'Student activated.');
    }

    public function deactivate(Student $student): RedirectResponse
    {
        $this->studentService->setActive($student, false);

        return redirect()->route('students.index')->with('status', 'Student deactivated.');
    }
}




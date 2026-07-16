<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Curriculum;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SubjectService
{
    public function __construct(
        protected AccessScopeService $accessScope,
        protected DataIntegrityService $integrity
    )
    {
    }

    public function index(Request $request): array
    {
        $query = $this->accessScope->applyToSubjects(
            Subject::with(['department', 'curriculum.semester', 'curriculum.programme']),
            $request->user()
        );

        $filters = $this->filters($request, $query);

        $sortable = [
            'code' => 'Code',
            'name' => 'Name',
            'type' => 'Type',
            'subject_category' => 'Category',
            'credits' => 'Credits',
            'is_active' => 'Status',
        ];

        $sort = $request->query('sort', 'code');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (array_key_exists($sort, $sortable)) {
            $query->orderBy($sort, $direction);
        }

        $subjects = $query->paginate(10)->withQueryString();

        return [$subjects, $filters, $sortable];
    }

    public function createViewData(): array
    {
        $departments = $this->accessScope->applyToDepartments(Department::query(), request()->user())->orderBy('name')->get(['dept_id', 'name']);
        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), request()->user())
            ->orderBy('name')
            ->get(['programme_id', 'dept_id', 'code', 'name']);
        $semesters = $this->accessScope->applyToSemesters(Semester::query(), request()->user())
            ->orderBy('semester_no')
            ->get(['semester_id', 'programme_id', 'semester_no']);

        return [
            'departments' => $departments,
            'programmes' => $programmes,
            'semesters' => $semesters,
            'subjectTypes' => ['Theory' => 'Theory', 'Lab' => 'Lab', 'Tutorial' => 'Tutorial'],
            'categories' => ['Core' => 'Core', 'Elective' => 'Elective', 'Open Elective' => 'Open Elective', 'Audit' => 'Audit'],
            'statuses' => ['1' => 'Active', '0' => 'Inactive'],
        ];
    }

    public function editViewData(Subject $subject): array
    {
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($subject->subject_id), request()->user())->exists(), 403);

        $subject->loadMissing('curriculum.semester', 'curriculum.programme');
        $curriculum = $subject->curriculum->first();

        $programmes = $this->accessScope->applyToProgrammes(Programme::where('dept_id', $subject->dept_id), request()->user())
            ->orderBy('name')
            ->get();

        $semesters = $this->accessScope->applyToSemesters(Semester::where('programme_id', $curriculum?->programme_id ?? 0), request()->user())
            ->orderBy('semester_no')
            ->get();

        $data = $this->createViewData();
        $data['programmes'] = $programmes;
        $data['semesters'] = $semesters;
        $data['selectedProgrammeId'] = $curriculum?->programme_id;
        $data['selectedSemesterId'] = $curriculum?->semester_id;

        return $data;
    }

    public function show(Subject $subject): Subject
    {
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($subject->subject_id), request()->user())->exists(), 403);

        return $subject->load(['department', 'curriculum.semester', 'curriculum.programme', 'staffAssignments', 'timetableSlots']);
    }

    public function store(array $data): Subject
    {
        $data = $this->integrity->lockSubjectData($data, request());
        abort_unless($this->accessScope->applyToDepartments(Department::whereKey($data['department_id']), request()->user())->exists(), 403);
        $this->authorizeCurriculumSelection($data);

        return 
            tap(Subject::create([
                'dept_id' => $data['department_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'],
                'subject_category' => $data['category'],
                'credits' => $data['credits'] ?? null,
                'theory_hours' => $data['theory_hours'] ?? null,
                'lab_hours' => $data['practical_hours'] ?? null,
                'tutorial_hours' => $data['tutorial_hours'] ?? null,
                'is_active' => true,
            ]), function ($subject) use ($data) {
                $this->syncCurriculum($subject, $data);
            });
    }

    public function update(Subject $subject, array $data): Subject
    {
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($subject->subject_id), request()->user())->exists(), 403);
        $data = $this->integrity->lockSubjectData($data, request());
        abort_unless($this->accessScope->applyToDepartments(Department::whereKey($data['department_id']), request()->user())->exists(), 403);
        $this->authorizeCurriculumSelection($data);

        $subject->update([
            'dept_id' => $data['department_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'subject_category' => $data['category'],
            'credits' => $data['credits'] ?? null,
            'theory_hours' => $data['theory_hours'] ?? null,
            'lab_hours' => $data['practical_hours'] ?? null,
            'tutorial_hours' => $data['tutorial_hours'] ?? null,
        ]);

        $this->syncCurriculum($subject, $data);

        return $subject;
    }

    public function delete(Subject $subject): void
    {
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($subject->subject_id), request()->user())->exists(), 403);
        $this->integrity->protectSubjectDelete($subject);

        $subject->delete();
    }

    public function setActive(Subject $subject, bool $active): void
    {
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($subject->subject_id), request()->user())->exists(), 403);

        $subject->update(['is_active' => $active]);
    }

    private function filters(Request $request, Builder $query): array
    {
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function (Builder $qQuery) use ($q) {
                $qQuery->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('dept_id')) {
            $query->where('dept_id', $request->query('dept_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->query('is_active') === '1');
        }

        return [
            'q' => $request->query('q'),
            'dept_id' => $request->query('dept_id'),
            'type' => $request->query('type'),
            'is_active' => $request->query('is_active'),
        ];
    }

    private function authorizeCurriculumSelection(array $data): void
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), request()->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(
            Semester::whereKey($data['semester_id'])->where('programme_id', $data['programme_id']),
            request()->user()
        )->exists(), 403);
    }

    private function syncCurriculum(Subject $subject, array $data): void
    {
        Curriculum::query()
            ->where('subject_id', $subject->subject_id)
            ->delete();

        Curriculum::query()->create([
            'programme_id' => $data['programme_id'],
            'semester_id' => $data['semester_id'],
            'subject_id' => $subject->subject_id,
            'is_mandatory' => $data['category'] === 'Core',
            'max_marks' => 100,
            'min_passing_marks' => 35,
        ]);
    }
}


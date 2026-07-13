<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Subject;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(Request $request): View
    {
        $curriculumItems = $this->accessScope
            ->applyToCurriculum(Curriculum::with(['programme', 'semester', 'subject']), $request->user())
            ->when($request->filled('programme_id'), fn ($query) => $query->where('programme_id', $request->integer('programme_id')))
            ->when($request->filled('semester_id'), fn ($query) => $query->where('semester_id', $request->integer('semester_id')))
            ->orderBy('programme_id')
            ->orderBy('semester_id')
            ->paginate(10)
            ->withQueryString();

        return view('academic.curriculum.index', $this->viewData() + compact('curriculumItems'));
    }

    public function create(): View
    {
        return view('academic.curriculum.create', $this->viewData(new Curriculum()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->authorizeScope($data);
        Curriculum::create($data);

        return redirect()->route('academic.curriculum.index')->with('status', 'Curriculum item created.');
    }

    public function edit(Curriculum $curriculum): View
    {
        abort_unless($this->accessScope->applyToCurriculum(Curriculum::whereKey($curriculum->curriculum_id), request()->user())->exists(), 403);

        return view('academic.curriculum.edit', $this->viewData($curriculum));
    }

    public function update(Request $request, Curriculum $curriculum): RedirectResponse
    {
        abort_unless($this->accessScope->applyToCurriculum(Curriculum::whereKey($curriculum->curriculum_id), $request->user())->exists(), 403);
        $data = $this->validated($request, $curriculum);
        $this->authorizeScope($data);
        $curriculum->update($data);

        return redirect()->route('academic.curriculum.index')->with('status', 'Curriculum item updated.');
    }

    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        abort_unless($this->accessScope->applyToCurriculum(Curriculum::whereKey($curriculum->curriculum_id), request()->user())->exists(), 403);

        $curriculum->delete();

        return redirect()->route('academic.curriculum.index')->with('status', 'Curriculum item deleted.');
    }

    private function viewData(?Curriculum $curriculum = null): array
    {
        return [
            'curriculum' => $curriculum,
            'programmes' => $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']),
            'semesters' => $this->accessScope->applyToSemesters(Semester::query(), request()->user())->orderBy('semester_no')->get(['semester_id', 'programme_id', 'semester_no', 'academic_year']),
            'subjects' => $this->accessScope->applyToSubjects(Subject::query(), request()->user())->orderBy('code')->get(['subject_id', 'code', 'name']),
        ];
    }

    private function authorizeScope(array $data): void
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), request()->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), request()->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($data['subject_id']), request()->user())->exists(), 403);
    }

    private function validated(Request $request, ?Curriculum $curriculum = null): array
    {
        $ignoreId = $curriculum?->curriculum_id;

        return $request->validate([
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'subject_id' => [
                'required',
                'exists:subjects,subject_id',
                Rule::unique('curriculum', 'subject_id')
                    ->where('programme_id', $request->input('programme_id'))
                    ->where('semester_id', $request->input('semester_id'))
                    ->ignore($ignoreId, 'curriculum_id'),
            ],
            'is_mandatory' => ['boolean'],
            'max_marks' => ['nullable', 'integer', 'min:0'],
            'min_passing_marks' => ['nullable', 'integer', 'min:0', 'lte:max_marks'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\ElectiveGroup;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectiveGroupController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(): View
    {
        $electiveGroups = ElectiveGroup::query()
            ->with(['curriculum.programme', 'curriculum.semester', 'curriculum.subject'])
            ->whereHas('curriculum', fn ($curriculum) => $this->accessScope->applyToCurriculum($curriculum, request()->user()))
            ->orderBy('group_name')
            ->paginate(10);

        return view('academic.elective-groups.index', compact('electiveGroups'));
    }

    public function create(): View
    {
        return view('academic.elective-groups.create', $this->viewData(new ElectiveGroup()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->authorizeCurriculum($data['curriculum_id']);
        ElectiveGroup::create($data);

        return redirect()->route('academic.elective-groups.index')->with('status', 'Elective group created.');
    }

    public function edit(ElectiveGroup $electiveGroup): View
    {
        $this->authorizeCurriculum($electiveGroup->curriculum_id);

        return view('academic.elective-groups.edit', $this->viewData($electiveGroup));
    }

    public function update(Request $request, ElectiveGroup $electiveGroup): RedirectResponse
    {
        $this->authorizeCurriculum($electiveGroup->curriculum_id);
        $data = $this->validated($request);
        $this->authorizeCurriculum($data['curriculum_id']);
        $electiveGroup->update($data);

        return redirect()->route('academic.elective-groups.index')->with('status', 'Elective group updated.');
    }

    public function destroy(ElectiveGroup $electiveGroup): RedirectResponse
    {
        $this->authorizeCurriculum($electiveGroup->curriculum_id);

        $electiveGroup->delete();

        return redirect()->route('academic.elective-groups.index')->with('status', 'Elective group deleted.');
    }

    private function viewData(ElectiveGroup $electiveGroup): array
    {
        return [
            'electiveGroup' => $electiveGroup,
            'curriculumItems' => $this->accessScope->applyToCurriculum(Curriculum::with(['programme', 'semester', 'subject']), request()->user())
                ->where('is_mandatory', false)
                ->orderBy('programme_id')
                ->orderBy('semester_id')
                ->get(),
        ];
    }

    private function authorizeCurriculum(int $curriculumId): void
    {
        abort_unless($this->accessScope->applyToCurriculum(Curriculum::whereKey($curriculumId), request()->user())->exists(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'curriculum_id' => ['required', 'exists:curriculum,curriculum_id'],
            'group_name' => ['required', 'string', 'max:100'],
            'select_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);
    }
}

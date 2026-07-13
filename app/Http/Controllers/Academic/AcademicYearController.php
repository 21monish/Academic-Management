<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\College;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(Request $request): View
    {
        $academicYears = $this->accessScope
            ->applyToAcademicYears(AcademicYear::with('college'), $request->user())
            ->when($request->filled('q'), fn ($query) => $query->where('label', 'like', '%' . $request->string('q') . '%'))
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('academic.academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        return view('academic.academic-years.create', $this->viewData(new AcademicYear()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);

        if (! empty($data['is_current'])) {
            AcademicYear::where('college_id', $data['college_id'])->update(['is_current' => false]);
        }

        AcademicYear::create($data);

        return redirect()->route('academic.academic-years.index')->with('status', 'Academic year created.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        abort_unless($this->accessScope->applyToAcademicYears(AcademicYear::whereKey($academicYear->academic_year_id), request()->user())->exists(), 403);

        return view('academic.academic-years.edit', $this->viewData($academicYear));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $this->validated($request);
        abort_unless($this->accessScope->applyToAcademicYears(AcademicYear::whereKey($academicYear->academic_year_id), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);

        if (! empty($data['is_current'])) {
            AcademicYear::where('college_id', $data['college_id'])
                ->whereKeyNot($academicYear->academic_year_id)
                ->update(['is_current' => false]);
        }

        $academicYear->update($data);

        return redirect()->route('academic.academic-years.index')->with('status', 'Academic year updated.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        abort_unless($this->accessScope->applyToAcademicYears(AcademicYear::whereKey($academicYear->academic_year_id), request()->user())->exists(), 403);

        $academicYear->delete();

        return redirect()->route('academic.academic-years.index')->with('status', 'Academic year deleted.');
    }

    private function viewData(AcademicYear $academicYear): array
    {
        return [
            'academicYear' => $academicYear,
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'statuses' => ['Upcoming', 'Active', 'Closed'],
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'label' => ['required', 'string', 'max:10'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'status' => ['required', 'in:Upcoming,Active,Closed'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreSemesterRequest;
use App\Http\Requests\Academic\UpdateSemesterRequest;
use App\Models\Programme;
use App\Models\Semester;
use App\Services\AccessScopeService;
use App\Services\SemesterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    protected SemesterService $semesterService;

    public function __construct(SemesterService $semesterService, protected AccessScopeService $accessScope)
    {
        $this->semesterService = $semesterService;
    }

    /**
     * Display a listing of semesters.
     */
    public function index(Request $request): View
    {
        $semesters = $this->semesterService->paginate($request);

        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), $request->user())->orderBy('name')->get();

        return view('semester.index', compact(
            'semesters',
            'programmes'
        ));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get();

        return view('semester.create', compact('programmes'));
    }

    /**
     * Store new semester.
     */
    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $this->semesterService->create($request->validated());

        return redirect()
            ->route('academic.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    /**
     * Display semester.
     */
    public function show(Semester $semester): View
    {
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($semester->semester_id), request()->user())->exists(), 403);

        $semester->load('programme');

        return view('semester.show', compact('semester'));
    }

    /**
     * Show edit form.
     */
    public function edit(Semester $semester): View
    {
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($semester->semester_id), request()->user())->exists(), 403);

        $programmes = $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get();

        return view('semester.edit', compact(
            'semester',
            'programmes'
        ));
    }

    /**
     * Update semester.
     */
    public function update(
        UpdateSemesterRequest $request,
        Semester $semester
    ): RedirectResponse {

        $this->semesterService->update(
            $semester,
            $request->validated()
        );

        return redirect()
            ->route('academic.semesters.index')
            ->with('success', 'Semester updated successfully.');
    }

    /**
     * Delete semester.
     */
    public function destroy(Semester $semester): RedirectResponse
    {
        $this->semesterService->delete($semester);

        return redirect()
            ->route('academic.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    /**
     * Activate semester.
     */
    public function activate(Semester $semester): RedirectResponse
    {
        $this->semesterService->setActive($semester, true);

        return back()->with(
            'success',
            'Semester activated successfully.'
        );
    }

    /**
     * Deactivate semester.
     */
    public function deactivate(Semester $semester): RedirectResponse
    {
        $this->semesterService->setActive($semester, false);

        return back()->with(
            'success',
            'Semester deactivated successfully.'
        );
    }
}

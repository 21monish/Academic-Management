<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use App\Models\University;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(Request $request): View
    {
        $scope = $this->accessScope->forUser($request->user());

        $departments = $this->accessScope
            ->applyToDepartments(Department::with('college', 'hod'), $request->user())
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(! $scope['university_id'] && ! $scope['college_id'] && ! $scope['dept_id'] && $request->filled('university_id'), function ($query) use ($request) {
                $query->whereHas('college', fn ($college) => $college->where('university_id', $request->integer('university_id')));
            })
            ->when(! $scope['college_id'] && ! $scope['dept_id'] && $request->filled('college_id'), fn ($query) => $query->where('college_id', $request->integer('college_id')))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $universities = $this->accessScope
            ->applyToUniversities(University::query(), $request->user())
            ->orderBy('name')
            ->get();

        $colleges = $this->accessScope
            ->applyToColleges(College::query(), $request->user())
            ->when(! $scope['university_id'] && ! $scope['college_id'] && ! $scope['dept_id'] && $request->filled('university_id'), fn ($query) => $query->where('university_id', $request->integer('university_id')))
            ->orderBy('name')
            ->get();

        return view('departments.index', compact('departments', 'colleges', 'universities'));
    }

    public function create(): View
    {
        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get();

        return view('departments.create', compact('colleges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'code' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'hod_staff_id' => ['nullable', 'exists:staff,staff_id'],
        ]);

        abort_unless($this->accessScope->applyToColleges(College::whereKey($validated['college_id']), $request->user())->exists(), 403);

        Department::create($validated);

        return redirect()->route('departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        abort_unless($this->accessScope->applyToDepartments(Department::whereKey($department->dept_id), request()->user())->exists(), 403);

        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get();

        return view('departments.edit', compact('department', 'colleges'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'code' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'hod_staff_id' => ['nullable', 'exists:staff,staff_id'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToDepartments(Department::whereKey($department->dept_id), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($validated['college_id']), $request->user())->exists(), 403);

        $department->update($validated);

        return redirect()->route('departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        abort_unless($this->accessScope->applyToDepartments(Department::whereKey($department->dept_id), request()->user())->exists(), 403);

        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Department deleted.');
    }
}

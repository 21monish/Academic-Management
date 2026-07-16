<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use App\Services\AccessScopeService;
use App\Services\DataIntegrityService;
use App\Support\ValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollegeController extends Controller
{
    public function __construct(
        protected AccessScopeService $accessScope,
        protected DataIntegrityService $integrity
    )
    {
    }

    public function index(Request $request): View
    {
        $scope = $this->accessScope->forUser($request->user());

        $colleges = $this->accessScope
            ->applyToColleges(College::with('university')->withCount('departments'), $request->user())
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('principal_name', 'like', "%{$search}%");
                });
            })
            ->when(! $scope['university_id'] && $request->filled('university_id'), fn ($query) => $query->where('university_id', $request->integer('university_id')))
            ->when($request->filled('affiliation_type'), fn ($query) => $query->where('affiliation_type', $request->string('affiliation_type')))
            ->when($request->filled('college_type'), fn ($query) => $query->where('college_type', $request->string('college_type')))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $universities = $this->scopedUniversities($request);

        return view('colleges.index', compact('colleges', 'universities'));
    }

    public function create(): View
    {
        $universities = $this->scopedUniversities(request());

        return view('colleges.create', compact('universities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'university_id' => ['required', 'exists:universities,university_id'],
            'code' => ['required', 'string', 'max:10', 'unique:colleges,code'],
            'name' => ValidationRules::shortText(true, 200),
            'address' => ['nullable', 'string'],
            'phone' => ValidationRules::phone(),
            'email' => ValidationRules::email(false, 150),
            'principal_name' => ['nullable', 'string', 'max:150'],
            'affiliation_type' => ['nullable', 'in:Autonomous,Affiliated,Constituent'],
            'college_type' => ['nullable', 'in:Government,Private,Grant-in-Aid'],
            'affiliated_on' => ['nullable', 'date'],
        ]);

        if ($request->user()?->university_id) {
            $validated['university_id'] = $request->user()->university_id;
        }

        College::create($validated);

        return redirect()->route('colleges.index')->with('status', 'College created.');
    }

    public function edit(College $college): View
    {
        abort_unless($this->accessScope->applyToColleges(College::whereKey($college->college_id), request()->user())->exists(), 403);

        $universities = $this->scopedUniversities(request());

        return view('colleges.edit', compact('college', 'universities'));
    }

    public function update(Request $request, College $college): RedirectResponse
    {
        $validated = $request->validate([
            'university_id' => ['required', 'exists:universities,university_id'],
            'code' => ['required', 'string', 'max:10', 'unique:colleges,code,'.$college->college_id.',college_id'],
            'name' => ValidationRules::shortText(true, 200),
            'address' => ['nullable', 'string'],
            'phone' => ValidationRules::phone(),
            'email' => ValidationRules::email(false, 150),
            'principal_name' => ['nullable', 'string', 'max:150'],
            'affiliation_type' => ['nullable', 'in:Autonomous,Affiliated,Constituent'],
            'college_type' => ['nullable', 'in:Government,Private,Grant-in-Aid'],
            'affiliated_on' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToColleges(College::whereKey($college->college_id), $request->user())->exists(), 403);

        if ($request->user()?->university_id) {
            $validated['university_id'] = $request->user()->university_id;
        }

        $college->update($validated);

        return redirect()->route('colleges.index')->with('status', 'College updated.');
    }

    public function destroy(College $college): RedirectResponse
    {
        abort_unless($this->accessScope->applyToColleges(College::whereKey($college->college_id), request()->user())->exists(), 403);
        $this->integrity->protectCollegeDelete($college);

        $college->delete();

        return redirect()->route('colleges.index')->with('status', 'College deleted.');
    }

    private function scopedUniversities(Request $request)
    {
        return University::query()
            ->when($request->user()?->university_id, fn ($query, $universityId) => $query->where('university_id', $universityId))
            ->orderBy('name')
            ->get();
    }
}

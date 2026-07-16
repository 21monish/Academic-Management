<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniversityRequest;
use App\Models\LicensePlan;
use App\Models\University;
use App\Services\AccessScopeService;
use App\Services\DataIntegrityService;
use App\Services\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function __construct(
        protected AccessScopeService $accessScope,
        protected UploadService $uploads,
        protected DataIntegrityService $integrity
    )
    {
    }

    public function index(Request $request): View
    {
        $universities = $this->accessScope
            ->applyToUniversities(University::with(['licensePlan'])->withCount('colleges'), $request->user())
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('universities.index', compact('universities'));
    }

    public function create(): View
    {
        return view('universities.create', [
            'licensePlans' => $this->licensePlans(),
        ]);
    }

    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        University::create($this->validatedDataWithLogo($request));

        return redirect()->route('universities.index')->with('status', 'University created.');
    }

    public function edit(University $university): View
    {
        abort_unless($this->accessScope->applyToUniversities(University::whereKey($university->university_id), request()->user())->exists(), 403);

        return view('universities.edit', [
            'university' => $university,
            'licensePlans' => $this->licensePlans(),
        ]);
    }

    public function update(StoreUniversityRequest $request, University $university): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUniversities(University::whereKey($university->university_id), $request->user())->exists(), 403);

        $university->update($this->validatedDataWithLogo($request, $university));

        return redirect()->route('universities.index')->with('status', 'University updated.');
    }

    public function destroy(University $university): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUniversities(University::whereKey($university->university_id), request()->user())->exists(), 403);
        $this->integrity->protectUniversityDelete($university);

        $university->delete();

        return redirect()->route('universities.index')->with('status', 'University deleted.');
    }

    private function validatedDataWithLogo(StoreUniversityRequest $request, ?University $university = null): array
    {
        $data = $request->validated();
        unset($data['logo']);

        if (! $this->canManageLicense($request)) {
            unset($data['license_plan_id'], $data['license_status'], $data['license_expires_on']);
        }

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $this->uploads->storePublicUpload($request->file('logo'), 'uploads/logos');
        } elseif ($university) {
            $data['logo_url'] = $university->logo_url;
        }

        return $data;
    }

    private function licensePlans()
    {
        return LicensePlan::query()
            ->where('is_active', true)
            ->orderBy('monthly_price')
            ->orderBy('name')
            ->get();
    }

    private function canManageLicense(Request $request): bool
    {
        return strcasecmp($request->user()?->role?->role_name ?? '', 'Super Admin') === 0;
    }
}

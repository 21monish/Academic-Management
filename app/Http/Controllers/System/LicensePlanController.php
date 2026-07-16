<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\LicensePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LicensePlanController extends Controller
{
    private const FEATURES = [
        'core' => 'Core Access',
        'institution' => 'Institution Setup',
        'people' => 'People',
        'academic' => 'Academic',
        'attendance' => 'Attendance',
        'exams' => 'Exams',
        'fees' => 'Fees',
        'leave' => 'Leave',
        'notices' => 'Notices',
        'reports' => 'Reports',
        'certificates' => 'Certificates',
        'chatbot' => 'Chatbot',
        'system' => 'System Tools',
        '*' => 'All Features',
    ];

    public function index(): View
    {
        return view('system.plans', [
            'plans' => LicensePlan::query()
                ->withCount('universities')
                ->orderBy('monthly_price')
                ->orderBy('name')
                ->get(),
            'features' => self::FEATURES,
            'canCreate' => hasPermission('license_plan.create'),
            'canUpdate' => hasPermission('license_plan.update'),
            'canDelete' => hasPermission('license_plan.delete'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        LicensePlan::query()->create($this->validatedData($request));

        return redirect()->route('system.plans.index')->with('status', 'Plan created successfully.');
    }

    public function update(Request $request, LicensePlan $plan): RedirectResponse
    {
        $plan->update($this->validatedData($request, $plan));

        return redirect()->route('system.plans.index')->with('status', 'Plan updated successfully.');
    }

    public function destroy(LicensePlan $plan): RedirectResponse
    {
        if ($plan->universities()->exists()) {
            return redirect()
                ->route('system.plans.index')
                ->with('error', 'This plan is assigned to clients. Deactivate it instead of deleting.');
        }

        $plan->delete();

        return redirect()->route('system.plans.index')->with('status', 'Plan deleted successfully.');
    }

    private function validatedData(Request $request, ?LicensePlan $plan = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('license_plans', 'code')->ignore($plan?->plan_id, 'plan_id'),
            ],
            'name' => ['required', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:9999999'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in(array_keys(self::FEATURES))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $features = collect($validated['features'] ?? [])
            ->filter(fn ($feature) => array_key_exists($feature, self::FEATURES))
            ->unique()
            ->values()
            ->all();

        if (in_array('*', $features, true)) {
            $features = ['*'];
        }

        return [
            'code' => strtolower($validated['code']),
            'name' => $validated['name'],
            'monthly_price' => $validated['monthly_price'],
            'max_students' => $validated['max_students'] ?? null,
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

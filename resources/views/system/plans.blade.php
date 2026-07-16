<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Manage Plans</h2>
                <p class="mt-1 text-sm text-slate-500">Create subscription plans, set pricing, and choose which modules each client can use.</p>
            </div>
            <span class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2 text-sm font-black text-cyan-800">
                {{ $plans->count() }} plan{{ $plans->count() === 1 ? '' : 's' }}
            </span>
        </div>
    </x-slot>

    @php
        $featureDescriptions = [
            'core' => 'Dashboard, profile, password and base access.',
            'institution' => 'Universities, colleges, departments, users and roles.',
            'people' => 'Staff, students, categories and assignments.',
            'academic' => 'Academic years, programmes, semesters, subjects and curriculum.',
            'attendance' => 'Timetable, lectures, summaries and defaulters.',
            'exams' => 'Exam setup, marks, results, hall tickets and seating.',
            'fees' => 'Fee setup, ledgers, collection, receipts and concessions.',
            'leave' => 'Leave applications, balances, approvals and holidays.',
            'notices' => 'Notice publishing, audience, attachments and acknowledgements.',
            'reports' => 'Student, attendance, result, fee and activity reports.',
            'certificates' => 'Bonafide, leaving, transfer and fee certificates.',
            'chatbot' => 'Chatbot ask and teach features.',
            'system' => 'System settings, health and owner tools.',
            '*' => 'Unlock every current and future feature.',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <h3 class="text-sm font-black text-slate-950">Create Plan</h3>
                <p class="mt-1 text-xs font-semibold text-slate-500">Use this for packages like Basic, Pro, Premium, or custom college deals.</p>
            </div>

            <form method="POST" action="{{ route('system.plans.store') }}" class="p-4">
                @csrf

                <div class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <x-input-label for="code" value="Plan Code" />
                        <input id="code" name="code" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('code') }}" placeholder="basic" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="name" value="Plan Name" />
                        <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('name') }}" placeholder="Basic" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="monthly_price" value="Monthly Price" />
                        <input id="monthly_price" name="monthly_price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('monthly_price') }}" placeholder="2999" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('monthly_price')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="max_students" value="Max Students" />
                        <input id="max_students" name="max_students" type="number" min="1" step="1" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('max_students') }}" placeholder="Leave blank for unlimited" @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('max_students')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-slate-900">Features</p>
                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($features as $key => $label)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 transition hover:border-cyan-200 hover:bg-cyan-50">
                                <input type="checkbox" name="features[]" value="{{ $key }}" class="mt-1 rounded border-slate-300 text-cyan-700 focus:ring-cyan-600" @checked(in_array($key, old('features', []), true)) @disabled(! $canUpdate)>
                                <span>
                                    <span class="block text-sm font-black text-slate-900">{{ $label }}</span>
                                    <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">{{ $featureDescriptions[$key] ?? '' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('features')" class="mt-2" />
                    <x-input-error :messages="$errors->get('features.*')" class="mt-2" />
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600" checked @disabled(! $canUpdate)>
                        Active plan
                    </label>

                    @if($canUpdate)
                        <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-cyan-800">Create Plan</button>
                    @else
                        <p class="text-sm font-semibold text-slate-500">You can view plans, but you do not have update permission.</p>
                    @endif
                </div>
            </form>
        </section>

        <section class="grid gap-4">
            @forelse($plans as $plan)
                @php
                    $planFeatures = collect($plan->features ?? [])->filter()->values()->all();
                    $hasAllFeatures = in_array('*', $planFeatures, true);
                @endphp

                <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <h3 class="text-base font-black text-slate-950">{{ $plan->name }}</h3>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ $plan->code }} · INR {{ number_format((float) $plan->monthly_price, 2) }}/month ·
                                {{ $plan->max_students ? number_format($plan->max_students).' students' : 'Unlimited students' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-md {{ $plan->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }} px-2 py-1 text-xs font-black">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="rounded-md bg-cyan-50 px-2 py-1 text-xs font-black text-cyan-800">
                                {{ $plan->universities_count }} client{{ $plan->universities_count === 1 ? '' : 's' }}
                            </span>
                        </div>
                    </div>

                    <form id="plan-update-{{ $plan->plan_id }}" method="POST" action="{{ route('system.plans.update', $plan) }}" class="p-4">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 lg:grid-cols-4">
                            <div>
                                <x-input-label for="code_{{ $plan->plan_id }}" value="Plan Code" />
                                <input id="code_{{ $plan->plan_id }}" name="code" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('code', $plan->code) }}" required @disabled(! $canUpdate)>
                            </div>
                            <div>
                                <x-input-label for="name_{{ $plan->plan_id }}" value="Plan Name" />
                                <input id="name_{{ $plan->plan_id }}" name="name" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('name', $plan->name) }}" required @disabled(! $canUpdate)>
                            </div>
                            <div>
                                <x-input-label for="monthly_price_{{ $plan->plan_id }}" value="Monthly Price" />
                                <input id="monthly_price_{{ $plan->plan_id }}" name="monthly_price" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('monthly_price', $plan->monthly_price) }}" required @disabled(! $canUpdate)>
                            </div>
                            <div>
                                <x-input-label for="max_students_{{ $plan->plan_id }}" value="Max Students" />
                                <input id="max_students_{{ $plan->plan_id }}" name="max_students" type="number" min="1" step="1" class="mt-1 block w-full rounded-md border-2 border-slate-300 px-3 py-2 text-sm font-semibold shadow-sm focus:border-cyan-600 focus:ring-cyan-600" value="{{ old('max_students', $plan->max_students) }}" placeholder="Unlimited" @disabled(! $canUpdate)>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            @foreach($features as $key => $label)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                                    <input type="checkbox" name="features[]" value="{{ $key }}" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600" @checked($hasAllFeatures ? $key === '*' : in_array($key, $planFeatures, true)) @disabled(! $canUpdate)>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                            <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600" @checked($plan->is_active) @disabled(! $canUpdate)>
                                Active plan
                            </label>

                            <div class="flex flex-wrap gap-2">
                                @if($canUpdate)
                                    <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-cyan-800">Save Plan</button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($canDelete)
                        <div class="border-t border-slate-100 bg-white px-4 py-3 text-right">
                            <form method="POST" action="{{ route('system.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan? Assigned client plans cannot be deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-black text-red-600 hover:text-red-700">Delete Plan</button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="text-sm font-black text-slate-800">No plans configured.</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Create your first subscription plan above.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-app-layout>

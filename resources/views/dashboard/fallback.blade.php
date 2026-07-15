<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">
                {{ $roleName ?? 'User' }} Dashboard
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Basic dashboard mode is active while the advanced dashboard data is unavailable.
            </p>
        </div>
    </x-slot>

    @php
        $canSeePermission = function ($permissions = null): bool {
            if (empty($permissions)) {
                return true;
            }

            foreach ((array) $permissions as $permission) {
                if (function_exists('hasPermission') && hasPermission($permission)) {
                    return true;
                }
            }

            return false;
        };

        $visibleSections = collect($pageSections ?? [])
            ->map(function ($items) use ($canSeePermission) {
                return collect($items)
                    ->filter(fn ($item) => ! empty($item['route']) && Route::has($item['route']) && $canSeePermission($item['permission'] ?? null))
                    ->values();
            })
            ->filter(fn ($items) => $items->isNotEmpty());
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm">
            <p class="text-sm font-black uppercase tracking-wide">Dashboard running in safe mode</p>
            <h3 class="mt-2 text-2xl font-black">Welcome, {{ $user?->name ?? 'User' }}</h3>
            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-amber-800">
                The detailed dashboard charts could not load on this request. The system logged the exact error, and your other modules can still be opened below.
            </p>
        </section>

        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($visibleSections as $section => $items)
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-950">{{ $section }}</h4>
                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-black text-slate-500">{{ $items->count() }}</span>
                    </div>

                    <div class="grid gap-2">
                        @foreach($items as $item)
                            <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-cyan-50 hover:text-cyan-800">
                                <span>{{ $item['label'] }}</span>
                                <span aria-hidden="true">-&gt;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-5 text-sm font-semibold text-slate-600 shadow-sm">
                    No dashboard modules are visible for this account yet.
                </div>
            @endforelse
        </section>
    </div>
</x-app-layout>

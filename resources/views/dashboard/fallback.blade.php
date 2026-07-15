<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">
                    {{ $roleName ?? 'User' }} Dashboard
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    A permission-aware workspace for your campus operations.
                </p>
            </div>

            <div class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2 text-right">
                <p class="text-xs font-bold uppercase text-cyan-700">Signed in as</p>
                <p class="text-sm font-semibold text-cyan-950">{{ $user?->name }}</p>
            </div>
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

        $quickLinks = $visibleSections
            ->flatten(1)
            ->take(6)
            ->values();

        $visibleModuleCount = $visibleSections->count();
        $visiblePageCount = $visibleSections->sum(fn ($items) => $items->count());
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid gap-4 lg:grid-cols-[1fr_360px]">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Campus Snapshot</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-950">
                            Welcome back, {{ $user?->name ?? 'User' }}
                        </h3>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Your dashboard adapts to your role and direct permissions, keeping your available work areas in one place.
                        </p>
                    </div>

                    <div class="grid gap-2 text-right sm:min-w-44">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-bold uppercase text-slate-400">Workspace</p>
                            <p class="mt-1 text-sm font-black text-slate-900">{{ now()->format('d M Y') }}</p>
                        </div>
                        <div class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2">
                            <p class="text-xs font-bold uppercase text-cyan-700">Access Surface</p>
                            <p class="mt-1 text-sm font-black text-cyan-950">{{ $visiblePageCount }} pages</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-cyan-100 bg-cyan-50 p-4 text-cyan-900">
                        <p class="text-xs font-bold uppercase tracking-wide opacity-70">Modules</p>
                        <p class="mt-2 text-2xl font-black">{{ $visibleModuleCount }}</p>
                    </div>
                    <div class="rounded-lg border border-teal-100 bg-teal-50 p-4 text-teal-900">
                        <p class="text-xs font-bold uppercase tracking-wide opacity-70">Pages</p>
                        <p class="mt-2 text-2xl font-black">{{ $visiblePageCount }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-emerald-900">
                        <p class="text-xs font-bold uppercase tracking-wide opacity-70">Shortcuts</p>
                        <p class="mt-2 text-2xl font-black">{{ $quickLinks->count() }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-4 text-slate-900">
                        <p class="text-xs font-bold uppercase tracking-wide opacity-70">Role</p>
                        <p class="mt-2 truncate text-2xl font-black">{{ $roleName ?? 'User' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-950">Quick Links</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Common work areas</p>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                        {{ $quickLinks->count() }}
                    </span>
                </div>

                <div class="mt-4 grid gap-2">
                    @forelse($quickLinks as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition-all duration-200 ease-out hover:translate-x-0.5 hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800">
                            <span>{{ $item['label'] }}</span>
                            <span aria-hidden="true">-&gt;</span>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                            No quick links are available for your current permissions.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase text-cyan-700">Module Launchpad</p>
                    <h3 class="mt-1 text-lg font-black text-slate-950">Open Work Areas</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Grouped by the same permissions that control your sidebar.</p>
                </div>
                <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $visibleModuleCount }} groups</span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($visibleSections as $section => $items)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-black text-slate-950">{{ $section }}</h4>
                            <span class="rounded-md bg-white px-2 py-1 text-xs font-black text-slate-500">{{ $items->count() }}</span>
                        </div>

                        <div class="grid gap-2">
                            @foreach($items->take(5) as $item)
                                <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:text-cyan-800">
                                    <span class="truncate">{{ $item['label'] }}</span>
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
            </div>
        </section>
    </div>
</x-app-layout>

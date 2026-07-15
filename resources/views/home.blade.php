@extends('public.layout')

@section('title', app(\App\Services\SystemSettingService::class)->branding()['application_name'].' | Academic Management')

@section('content')
    @php($homeBranding = app(\App\Services\SystemSettingService::class)->branding())

    <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 opacity-95" aria-hidden="true">
            <div class="mx-auto grid h-full max-w-7xl grid-cols-12 gap-4 px-5 py-8 sm:px-8 lg:px-10">
                <div class="col-span-12 rounded-lg border border-white/10 bg-white/10 p-4 shadow-2xl shadow-slate-950/25 lg:col-span-7 lg:col-start-6 lg:mt-8">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <div>
                            <div class="h-2 w-28 rounded-full bg-cyan-300/80"></div>
                            <div class="mt-3 h-2 w-44 rounded-full bg-white/30"></div>
                        </div>
                        <div class="h-9 w-24 rounded-lg bg-emerald-300/80"></div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach([['Students', '4.2k', 'bg-cyan-300'], ['Attendance', '91%', 'bg-emerald-300'], ['Fees', '78%', 'bg-amber-300']] as [$label, $value, $color])
                            <div class="rounded-lg border border-white/10 bg-slate-950/55 p-4">
                                <div class="h-2 w-16 rounded-full bg-white/25"></div>
                                <div class="mt-4 text-2xl font-black">{{ $value }}</div>
                                <div class="mt-2 h-1.5 rounded-full bg-white/15">
                                    <div class="h-1.5 rounded-full {{ $color }}" style="width: {{ $loop->iteration === 1 ? '84' : ($loop->iteration === 2 ? '91' : '78') }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-lg border border-white/10 bg-white p-4 text-slate-950">
                        <div class="grid grid-cols-[72px_1fr_1fr_92px] gap-3 border-b border-slate-100 pb-3 text-xs font-black uppercase text-slate-400">
                            <span>Sr No</span><span>Module</span><span>Status</span><span>Action</span>
                        </div>
                        @foreach([['01', 'Student enrollments', 'Ready'], ['02', 'Subject assignments', 'Active'], ['03', 'Hall ticket checks', 'Queued']] as [$sr, $module, $status])
                            <div class="grid grid-cols-[72px_1fr_1fr_92px] gap-3 border-b border-slate-100 py-3 text-sm last:border-0">
                                <span class="font-bold text-slate-500">{{ $sr }}</span>
                                <span class="font-black">{{ $module }}</span>
                                <span class="font-semibold text-slate-500">{{ $status }}</span>
                                <span class="rounded-md bg-cyan-50 px-2 py-1 text-center text-xs font-black text-cyan-800">Open</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-150px)] max-w-7xl items-center px-5 py-14 sm:px-8 lg:px-10">
            <div class="max-w-3xl py-8">
                <div class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-sm font-black text-cyan-100 backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Campus operations, permissions, and reports in one workspace
                </div>
                <h1 class="mt-6 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                    {{ $homeBranding['application_name'] }}
                </h1>
                <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-slate-200 sm:text-lg">
                    A focused academic management system for universities and colleges: admissions, staff, curriculum, attendance, exams, fees, notices, and report workflows.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-lg bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-sm hover:bg-cyan-50">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center rounded-lg bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-sm hover:bg-cyan-50">Log In</a>
                    @endauth
                    <a href="{{ route('public.modules') }}" class="inline-flex justify-center rounded-lg border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur hover:bg-white/15">Explore Modules</a>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-white px-5 py-8 sm:px-8 lg:px-10">
        <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-4">
            @foreach([
                ['Institution', 'University, college, department, programme, semester, and subject hierarchy.'],
                ['People', 'Student and staff records with scoped logins and permission-aware access.'],
                ['Operations', 'Attendance, exams, hall tickets, fees, leave, notices, and approvals.'],
                ['Reports', 'Export-ready operational reports with college and programme filters.'],
            ] as [$title, $text])
                <article class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-sm font-black text-slate-950">{{ $title }}</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-600">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-slate-50 px-5 py-10 sm:px-8 lg:px-10">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <p class="text-sm font-black uppercase text-cyan-800">Built For Daily Administration</p>
                <h2 class="mt-3 text-2xl font-black text-slate-950">Fast workflows without losing hierarchy control.</h2>
                <p class="mt-3 max-w-xl text-sm font-medium leading-6 text-slate-600">
                    Every screen respects the logged-in user's university, college, department, and programme scope, so teams can work inside their own data boundary.
                </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach([
                    'Permission-based navigation',
                    'Student login from enrollment number',
                    'University-wise branding',
                    'Attendance and exam workflows',
                    'Fee ledger and receipt handling',
                    'Toast notifications and print layouts',
                ] as $feature)
                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 shadow-sm">{{ $feature }}</div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

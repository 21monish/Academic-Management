@extends('public.layout')

@section('title', app(\App\Services\SystemSettingService::class)->branding()['application_name'].' | Campus ERP')

@section('content')
    @php($homeBranding = app(\App\Services\SystemSettingService::class)->branding())

    <section class="bg-white">
        <div class="mx-auto grid min-h-[calc(100vh-78px)] max-w-7xl items-center gap-10 px-5 py-10 sm:px-8 lg:grid-cols-[0.92fr_1.08fr] lg:px-10">
            <div>
                <div class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Live campus operations workspace
                </div>
                <h1 class="mt-6 text-4xl font-black leading-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    {{ $homeBranding['application_name'] }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                    Manage admissions, staff, subject assignments, attendance, exams, fees, notices, and reports from one permission-aware ERP built for day-to-day college work.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-slate-800">Open Dashboard</a>
                    @else
                        <a href="{{ route('login', ['type' => 'staff']) }}" class="inline-flex justify-center rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-slate-800">Staff Login</a>
                    @endauth
                    <a href="{{ route('public.modules') }}" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-800 shadow-sm hover:border-cyan-200 hover:text-cyan-800">Explore Modules</a>
                </div>
                <dl class="mt-10 grid max-w-xl grid-cols-3 gap-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-bold text-slate-500">Modules</dt>
                        <dd class="mt-1 text-2xl font-black">12+</dd>
                    </div>
                    <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-4">
                        <dt class="text-xs font-bold text-cyan-700">Access</dt>
                        <dd class="mt-1 text-2xl font-black text-cyan-950">Role</dd>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <dt class="text-xs font-bold text-amber-700">Reports</dt>
                        <dd class="mt-1 text-2xl font-black text-amber-950">PDF</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-950 p-4 text-white shadow-2xl shadow-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-cyan-200">Academic Control Room</p>
                        <h2 class="mt-1 text-xl font-black">Today&apos;s campus snapshot</h2>
                    </div>
                    <div class="rounded-lg bg-white px-3 py-2 text-right text-slate-950">
                        <p class="text-xs font-bold text-slate-500">Attendance</p>
                        <p class="text-lg font-black">91.4%</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-[1.05fr_0.95fr]">
                    <div class="rounded-lg bg-white p-4 text-slate-950">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-black">Workflow Progress</h3>
                            <span class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-black text-emerald-700">On track</span>
                        </div>
                        <div class="mt-4 space-y-4">
                            @foreach([['Admissions', 78, 'bg-cyan-500'], ['Fee Collection', 64, 'bg-emerald-500'], ['Result Entry', 86, 'bg-amber-500']] as [$label, $value, $color])
                                <div>
                                    <div class="mb-1 flex justify-between text-xs font-bold text-slate-500">
                                        <span>{{ $label }}</span><span>{{ $value }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full {{ $color }}" style="width: {{ $value }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg bg-cyan-50 p-4 text-slate-950">
                        <h3 class="text-sm font-black">Fast Actions</h3>
                        <div class="mt-4 space-y-3">
                            @foreach(['Assign faculty subjects', 'Publish notices', 'Generate hall tickets'] as $action)
                                <div class="rounded-lg border border-cyan-100 bg-white p-3 text-sm font-bold">{{ $action }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach([['Students', '4.2k'], ['Staff', '248'], ['Notices', '5 active']] as [$label, $value])
                        <div class="rounded-lg bg-white/10 p-3">
                            <p class="text-xs font-bold text-slate-300">{{ $label }}</p>
                            <p class="mt-1 text-lg font-black">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-slate-50 px-5 py-10 sm:px-8 lg:px-10">
        <div class="mx-auto grid max-w-7xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Students', 'Admissions, profiles, categories, enrollments, and login accounts.'],
                ['Academics', 'Programmes, semesters, subjects, curriculum, and electives.'],
                ['Attendance', 'Subject assignments, timetable slots, lectures, and summaries.'],
                ['Exams & Fees', 'Marks, results, hall tickets, ledgers, receipts, and reports.'],
            ] as [$title, $text])
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-black text-slate-950">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection

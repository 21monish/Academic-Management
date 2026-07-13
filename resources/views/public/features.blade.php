@extends('public.layout')

@section('title', 'Features | Campus ERP')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">
        <div class="max-w-3xl">
            <p class="text-sm font-black uppercase text-cyan-700">Features</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950">Built for real academic workflows</h1>
            <p class="mt-4 text-base leading-7 text-slate-600">Every page is permission-aware, scoped to the user&apos;s university, college, department, programme, or assigned subjects.</p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach([
                ['Permission-aware dashboard', 'Users see only the modules, metrics, reports, and actions they can access.'],
                ['Subject assignments', 'Assign teaching staff to curriculum subjects by semester, college, academic year, and lecture type.'],
                ['Attendance operations', 'Create timetable slots, lectures, mark attendance, and monitor defaulters.'],
                ['Exam lifecycle', 'Manage exams, exam subjects, marks entry, grades, results, hall tickets, seating, and invigilation.'],
                ['Finance workflows', 'Track ledgers, concessions, scholarships, collections, receipts, and payment reports.'],
                ['Notices and acknowledgements', 'Publish notices by category and audience with attachments and acknowledgement tracking.'],
            ] as [$title, $text])
                <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-black text-slate-950">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection

@extends('public.layout')

@section('title', 'Modules | Campus ERP')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-3xl">
                <p class="text-sm font-black uppercase text-cyan-700">Modules</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950">One ERP, many coordinated departments</h1>
                <p class="mt-4 text-base leading-7 text-slate-600">A structured map of the working areas available after login.</p>
            </div>
            <a href="{{ route('login') }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-slate-800">Open ERP</a>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @foreach([
                ['Institution', ['Universities', 'Colleges', 'Departments', 'Users', 'Roles & Permissions']],
                ['People', ['Staff', 'Subject Assignments', 'Students', 'People Categories']],
                ['Academic', ['Academic Years', 'Programmes', 'Semesters', 'Subjects', 'Curriculum', 'Elective Groups']],
                ['Attendance', ['Timetable Slots', 'Lectures', 'Attendance Summary', 'Defaulters']],
                ['Exams', ['Exams', 'Exam Subjects', 'Marks Entry', 'Results', 'Hall Tickets', 'Seating']],
                ['Finance & Communication', ['Fee Collection', 'Receipts', 'Reports', 'Leave', 'Notices', 'Acknowledgements']],
            ] as [$section, $items])
                <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">{{ $section }}</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($items as $item)
                            <span class="rounded-md bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700">{{ $item }}</span>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection

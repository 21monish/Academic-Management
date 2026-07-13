@extends('public.layout')

@section('title', 'About | Campus ERP')

@section('content')
    <section class="mx-auto grid max-w-7xl gap-8 px-5 py-12 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:px-10">
        <div>
            <p class="text-sm font-black uppercase text-cyan-700">About</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950">A practical ERP for colleges that need control without clutter</h1>
            <p class="mt-5 text-base leading-7 text-slate-600">The system keeps academic administration connected: students, staff, departments, subjects, attendance, exams, fees, notices, and reports all work from one data model.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-950">Operating Principles</h2>
            <div class="mt-5 space-y-4">
                @foreach([
                    ['Scoped access', 'Every user works inside their assigned hierarchy and direct page permissions.'],
                    ['Daily usability', 'Screens prioritize repeated administrative work over marketing-style decoration.'],
                    ['Traceable output', 'Reports, receipts, hall tickets, notices, and records are built for audit-friendly workflows.'],
                    ['Expandable modules', 'The structure supports new departments, roles, permissions, and academic workflows.'],
                ] as [$title, $text])
                    <div class="border-l-4 border-cyan-600 pl-4">
                        <h3 class="text-sm font-black text-slate-950">{{ $title }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

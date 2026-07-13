@extends('public.layout')

@section('title', 'Contact | Campus ERP')

@section('content')
    @php($branding = app(\App\Services\SystemSettingService::class)->branding())
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_420px]">
            <div>
                <p class="text-sm font-black uppercase text-cyan-700">Contact</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950">Need access or support?</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Use your campus administrator account to sign in. For new roles, permissions, or department setup, contact the ERP administrator at your institution.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="inline-flex justify-center rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-slate-800">Log in</a>
                    <a href="{{ route('public.modules') }}" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-800 shadow-sm hover:border-cyan-200 hover:text-cyan-800">View Modules</a>
                </div>
            </div>
            <aside class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">{{ $branding['application_name'] }}</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-black text-slate-500">Application</dt>
                        <dd class="mt-1 font-bold text-slate-950">{{ $branding['application_short_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Access</dt>
                        <dd class="mt-1 font-bold text-slate-950">Role and permission based</dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Support Areas</dt>
                        <dd class="mt-1 leading-6 text-slate-600">Users, roles, colleges, departments, subjects, attendance, exams, fees, notices, and reports.</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </section>
@endsection

@php
    $publicBranding = app(\App\Services\SystemSettingService::class)->branding();
    $publicNav = [
        ['label' => 'Home', 'route' => 'home', 'url' => url('/')],
        ['label' => 'Features', 'route' => 'public.features', 'url' => route('public.features')],
        ['label' => 'Modules', 'route' => 'public.modules', 'url' => route('public.modules')],
        ['label' => 'About', 'route' => 'public.about', 'url' => route('public.about')],
        ['label' => 'Contact', 'route' => 'public.contact', 'url' => route('public.contact')],
    ];
    $publicFaviconUrl = $publicBranding['logo_url'] ?: asset('favicon.svg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $publicBranding['application_name'])</title>
    <link rel="icon" href="{{ $publicFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $publicFaviconUrl }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    @include('layouts.partials.vite')
</head>
<body class="public-shell bg-slate-50 text-slate-950 antialiased">
    <div class="min-h-screen">
        <header class="public-header sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:px-10">
                <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                    @if($publicBranding['logo_url'])
                        <img src="{{ $publicBranding['logo_url'] }}" alt="{{ $publicBranding['application_name'] }} logo" class="h-11 w-11 rounded-lg border border-slate-200 bg-white object-contain p-1 shadow-sm">
                    @else
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-slate-950 text-sm font-black text-white shadow-sm">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($publicBranding['application_name'], 0, 2)) }}</span>
                    @endif
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-black leading-5">{{ $publicBranding['application_name'] }}</span>
                        <span class="block truncate text-xs font-semibold text-cyan-700">{{ $publicBranding['application_short_name'] }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 lg:flex">
                    @foreach($publicNav as $item)
                        @php($active = $item['route'] === 'home' ? request()->is('/') : request()->routeIs($item['route']))
                        <a href="{{ $item['url'] }}" class="{{ $active ? 'bg-slate-950 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-bold transition">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-cyan-700 px-4 py-2.5 text-sm font-black text-white shadow-sm hover:bg-cyan-800">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm hover:border-cyan-200 hover:text-cyan-800">Log in</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="public-footer border-t border-slate-200/80 bg-white">
            <div class="mx-auto grid max-w-7xl gap-6 px-5 py-8 sm:px-8 md:grid-cols-[1fr_auto] lg:px-10">
                <div>
                    <p class="text-sm font-black text-slate-950">{{ $publicBranding['application_name'] }}</p>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">A focused ERP workspace for academic administration, student services, attendance, exams, fees, notices, and reporting.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($publicNav as $item)
                        <a href="{{ $item['url'] }}" class="rounded-md px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-950">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </footer>
    </div>
</body>
</html>

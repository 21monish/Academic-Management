@php
    $guestBranding = app(\App\Services\SystemSettingService::class)->branding();
    $guestInitials = collect(explode(' ', $guestBranding['application_name']))
        ->filter()
        ->take(2)
        ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
        ->join('') ?: 'GI';
    $guestFaviconUrl = $guestBranding['logo_url'] ?: asset('favicon.svg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $guestBranding['application_name'] }}</title>
        <link rel="icon" href="{{ $guestFaviconUrl }}">
        <link rel="shortcut icon" href="{{ $guestFaviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('layouts.partials.vite')
    </head>
    <body class="guest-shell font-sans text-gray-900 antialiased">
            @include('partials._toast')
            <div class="page-enter min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/" class="flex flex-col items-center gap-2">
                    @if($guestBranding['logo_url'])
                        <img src="{{ $guestBranding['logo_url'] }}" alt="{{ $guestBranding['application_name'] }} logo" class="h-16 w-16 rounded-lg border border-slate-200 bg-white object-contain p-1 shadow-sm">
                    @else
                        <span class="grid h-16 w-16 place-items-center rounded-lg bg-slate-950 text-lg font-bold text-white shadow-sm">{{ $guestInitials }}</span>
                    @endif
                    <span class="text-sm font-bold text-slate-800">{{ $guestBranding['application_name'] }}</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

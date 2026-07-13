@php
    $printBranding = app(\App\Services\SystemSettingService::class)->branding();
    $printFaviconUrl = $printBranding['logo_url'] ?: asset('favicon.svg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $printBranding['application_name'] }}</title>
    <link rel="icon" href="{{ $printFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $printFaviconUrl }}">
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>
<body>
    <main class="print-sheet">
        <header class="print-header">
            <div class="print-brand">
                <img src="{{ $printFaviconUrl }}" alt="{{ $printBranding['application_name'] }} logo" class="print-logo">
                <div>
                    <div class="print-app-name">{{ $printBranding['application_name'] }}</div>
                    <div class="print-subtitle">{{ $printBranding['application_short_name'] }}</div>
                </div>
            </div>
            <div class="print-meta">{{ now()->format('d M Y') }}</div>
        </header>

        {{ $slot ?? '' }}
    </main>
    @include('layouts.partials.table-srno')
</body>
</html>

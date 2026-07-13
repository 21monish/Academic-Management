@php
    $title = $title ?? 'Dashboard';
    $mainBranding = app(\App\Services\SystemSettingService::class)->branding();
    $mainFaviconUrl = $mainBranding['logo_url'] ?: asset('favicon.svg');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ $mainFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $mainFaviconUrl }}">
    @include('layouts.partials.vite')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen">
        {{ $slot ?? '' }}
    </div>
</body>
</html>


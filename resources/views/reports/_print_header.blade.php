@php
    $printHeaderBranding = app(\App\Services\SystemSettingService::class)->branding();
    $hasDocumentBrand = isset($brandName) || isset($logoUrl) || isset($subtitle);
    $printHeaderLogo = filled($logoUrl ?? null)
        ? $logoUrl
        : ($hasDocumentBrand ? asset('favicon.svg') : ($printHeaderBranding['logo_url'] ?? asset('favicon.svg')));
    $printHeaderLogo = \Illuminate\Support\Str::startsWith($printHeaderLogo, ['http://', 'https://', '/'])
        ? $printHeaderLogo
        : asset($printHeaderLogo);
    $printHeaderBrandName = $brandName ?? $printHeaderBranding['application_name'];
    $printHeaderTitle = $title ?? $printHeaderBranding['application_name'];
    $printHeaderSubtitle = $subtitle ?? $printHeaderBranding['application_short_name'];
    $printHeaderMeta = $meta ?? now()->format('d M Y');
@endphp

<div class="print-header">
    <div class="print-brand">
        <img src="{{ $printHeaderLogo }}" alt="{{ $printHeaderBrandName }} logo" class="print-logo">
        <div>
            <div class="print-app-name">{{ $printHeaderBrandName }}</div>
            @if($printHeaderSubtitle)
                <div class="print-subtitle">{{ $printHeaderSubtitle }}</div>
            @endif
        </div>
    </div>
    <div class="print-meta">{{ $printHeaderMeta }}</div>
</div>

<div class="topline">
    <div>
        <h1>{{ $printHeaderTitle }}</h1>
        @if($printHeaderSubtitle)
            <p class="muted">{{ $printHeaderSubtitle }}</p>
        @endif
    </div>
    <p>{{ $printHeaderMeta }}</p>
</div>

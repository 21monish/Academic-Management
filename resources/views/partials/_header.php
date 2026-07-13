@php
    $title = $title ?? '';
@endphp

<div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200">
    <div>
        <h1 class="text-lg font-semibold">{{ $title }}</h1>
    </div>
    <div class="text-sm text-gray-600">
        {{ auth()->user()?->name }}
    </div>
</div>


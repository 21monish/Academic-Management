@php
    $items = $items ?? [];
@endphp

@if(count($items))
    <nav class="mb-4" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center text-sm text-gray-600">
            @foreach($items as $index => $item)
                @if($index > 0)
                    <li class="mx-2 text-gray-400">/</li>
                @endif

                <li>
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}" class="text-blue-600 transition hover:text-blue-800 hover:underline">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-medium text-gray-800">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

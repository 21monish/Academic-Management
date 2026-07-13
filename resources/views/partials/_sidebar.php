@php
    use Illuminate\Support\Facades\Route;

    $role = $role ?? 'hod';
    $active = $active ?? '';

    $menusByRole = [
        'super_admin' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
            ['key' => 'universities.index', 'label' => 'Universities', 'route' => 'universities.index'],
        ],
        'college_admin' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
            ['key' => 'universities.index', 'label' => 'Universities', 'route' => 'universities.index'],
        ],
        'hod' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
            ['key' => 'universities.index', 'label' => 'Universities', 'route' => 'universities.index'],
        ],
        'staff' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
        ],
        'student' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
        ],
    ];

    $menu = $menusByRole[$role] ?? ($menusByRole['hod'] ?? []);
@endphp

<aside class="hidden md:block w-64 bg-white border-r border-gray-200">
    <div class="p-4 border-b border-gray-200">
        <div class="text-sm font-semibold text-gray-900">{{ ucwords(str_replace('_',' ', $role)) }}</div>
    </div>
    <nav class="p-2 space-y-1">
        @foreach($menu as $item)
            @php
                $routeName = $item['route'] ?? null;
                $key = $item['key'] ?? '';
            @endphp

            @if($routeName && Route::has($routeName))
                <a
                    href="{{ route($routeName) }}"
                    class="block px-3 py-2 rounded-md text-sm font-medium {{ $active === $key ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
                >
                    {{ $item['label'] ?? '' }}
                </a>
            @endif
        @endforeach
    </nav>
</aside>


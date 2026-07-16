<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Universities</h2>
            <a href="{{ route('universities.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add University</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6 p-4 border border-gray-100">
            <form method="GET" action="{{ route('universities.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-3">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="Name, email, phone, website" />
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                    @if(request()->hasAny(['q']))
                        <a href="{{ route('universities.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Logo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Colleges</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">License</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Established</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($universities as $university)
                        <tr>
                            <td class="px-4 py-2">
                                @if($university->logo_url)
                                    @php($logoSrc = \Illuminate\Support\Str::startsWith($university->logo_url, ['http://', 'https://', '/']) ? $university->logo_url : asset($university->logo_url))
                                    <img src="{{ $logoSrc }}" alt="{{ $university->name }} logo" class="h-10 w-10 rounded-md border border-slate-200 object-contain p-1">
                                @else
                                    <span class="text-xs text-gray-400">No logo</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $university->name }}</td>
                            <td class="px-4 py-2">{{ $university->colleges_count }}</td>
                            <td class="px-4 py-2">
                                <div class="text-sm font-semibold text-slate-800">{{ $university->licensePlan?->name ?? 'Unlimited' }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $university->license_status ?? 'Active' }}
                                    @if($university->license_expires_on)
                                        until {{ $university->license_expires_on->format('Y-m-d') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2">{{ $university->established_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('universities.edit', $university) }}" class="text-indigo-600 text-sm">Edit</a>
                                <form action="{{ route('universities.destroy', $university) }}" method="POST" class="inline" onsubmit="return confirm('Delete this university?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No universities found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $universities->links() }}</div>
    </div>
</x-app-layout>

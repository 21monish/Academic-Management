<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">User Accounts</h2>
                <p class="mt-1 text-sm text-slate-500">Manage login access, hierarchy scope, and page permissions.</p>
            </div>
            @if(hasPermission('user.create'))
                <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-cyan-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-cyan-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                    </svg>
                    <span>Add Account</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('users.index') }}" class="mb-5 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]">
                <div>
                    <label for="user-search" class="sr-only">Search users</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                        </svg>
                        <x-text-input id="user-search" name="search" placeholder="Search username, email, phone" class="w-full ps-10" :value="request('search')" />
                    </div>
                </div>
                <div>
                    <label for="role-filter" class="sr-only">Role</label>
                    <select id="role-filter" name="role_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->role_id }}" @selected((string) request('role_id') === (string) $role->role_id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status-filter" class="sr-only">Status</label>
                    <select id="status-filter" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('status') === '1')>Active</option>
                        <option value="0" @selected(request('status') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-bold text-white lg:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4"/>
                        </svg>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                <div>
                    <p class="text-sm font-bold text-slate-900">Accounts</p>
                    <p class="text-xs font-semibold text-slate-500">{{ $users->total() }} {{ Str::plural('record', $users->total()) }}</p>
                </div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-white">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-black uppercase text-slate-500">User</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Role</th>
                        <th class="min-w-72 px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Hierarchy</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Security</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Permissions</th>
                        <th class="px-4 py-3 text-right text-xs font-black uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-900 text-sm font-black text-white">
                                        {{ Str::upper(Str::substr($user->username ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-bold text-slate-950">{{ $user->username }}</div>
                                        <div class="truncate text-xs font-semibold text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex max-w-44 items-center truncate rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $user->role?->role_name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $user->university?->name ?? '-' }}</div>
                                @if ($user->college || $user->department || $user->programme)
                                    <div class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $user->college?->name ?? '-' }}
                                        @if ($user->department)
                                            / {{ $user->department->name }}
                                        @endif
                                        @if ($user->programme)
                                            / {{ $user->programme->name }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->is_verified ? 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-100' }}">
                                        {{ $user->is_verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                    @if ($user->must_change_password)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100">Password Change</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700 ring-1 ring-cyan-100">{{ $user->permissions_count ?? 0 }} selected</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                @if ($user->reference_type === 'Staff' && $user->reference_id)
                                    @if(hasPermission('staff.update'))
                                        <a href="{{ route('staff.edit', $user->reference_id) }}" class="rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100">Staff Record</a>
                                    @endif
                                @elseif ($user->reference_type === 'Student' && $user->reference_id)
                                    @if(hasPermission('student.update'))
                                        <a href="{{ route('students.edit', $user->reference_id) }}" class="rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100">Student Record</a>
                                    @endif
                                @elseif(hasPermission('user.update'))
                                    <a href="{{ route('users.edit', $user) }}" class="rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200">Edit</a>
                                    @if ($user->is_active)
                                        <form action="{{ route('users.deactivate', $user) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-md bg-amber-50 px-2.5 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-100">Deactivate</button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', $user) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100">Activate</button>
                                        </form>
                                    @endif
                                @endif
                                @if(hasPermission('user_permission.view') || hasPermission('user_permission.update'))
                                    <a href="{{ route('users.permissions.edit', $user) }}" class="rounded-md bg-cyan-50 px-2.5 py-1.5 text-xs font-bold text-cyan-700 hover:bg-cyan-100">Permissions</a>
                                @endif
                                @if(hasPermission('user.delete') && ! in_array($user->reference_type, ['Staff', 'Student'], true))
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">Delete</button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="mx-auto grid h-12 w-12 place-items-center rounded-lg bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 0 1-8 0m8 0c2.2.5 4 2 4 4v2H4v-2c0-2 1.8-3.5 4-4"/>
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm font-bold text-slate-700">No users found</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Try changing the filters or search text.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>

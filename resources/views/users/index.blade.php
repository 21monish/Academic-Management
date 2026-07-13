<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Accounts</h2>
            </div>
            @if(hasPermission('user.create'))
                <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add Account</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-md text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="GET" action="{{ route('users.index') }}" class="mb-4 bg-white shadow-sm rounded-lg p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <x-text-input name="search" placeholder="Search username, email, phone" class="w-full" :value="request('search')" />
            <select name="role_id" class="border-gray-300 rounded-md shadow-sm">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->role_id }}" @selected((string) request('role_id') === (string) $role->role_id)>{{ $role->role_name }}</option>
                @endforeach
            </select>
            <select name="status" class="border-gray-300 rounded-md shadow-sm">
                <option value="">All Status</option>
                <option value="1" @selected(request('status') === '1')>Active</option>
                <option value="0" @selected(request('status') === '0')>Inactive</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm">Reset</a>
            </div>
        </form>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hierarchy</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Security</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Page Permissions</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $user->username }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $user->role?->role_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div>{{ $user->university?->name ?? '-' }}</div>
                                @if ($user->college || $user->department || $user->programme)
                                    <div class="text-xs text-gray-500">
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
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $user->is_verified ? 'Verified' : 'Unverified' }}
                                @if ($user->must_change_password)
                                    <span class="ml-2 text-amber-700">Password change required</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="rounded-full bg-cyan-50 px-2 py-1 text-xs font-bold text-cyan-700">{{ $user->permissions_count ?? 0 }} selected</span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                @if ($user->reference_type === 'Staff' && $user->reference_id)
                                    @if(hasPermission('staff.update'))
                                        <a href="{{ route('staff.edit', $user->reference_id) }}" class="text-indigo-600 text-sm">Staff Record</a>
                                    @endif
                                @elseif ($user->reference_type === 'Student' && $user->reference_id)
                                    @if(hasPermission('student.update'))
                                        <a href="{{ route('students.edit', $user->reference_id) }}" class="text-indigo-600 text-sm">Student Record</a>
                                    @endif
                                @elseif(hasPermission('user.update'))
                                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 text-sm">Edit</a>
                                    @if ($user->is_active)
                                        <form action="{{ route('users.deactivate', $user) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-gray-600 text-sm">Deactivate</button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', $user) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-700 text-sm">Activate</button>
                                        </form>
                                    @endif
                                @endif
                                @if(hasPermission('user_permission.view') || hasPermission('user_permission.update'))
                                    <a href="{{ route('users.permissions.edit', $user) }}" class="text-cyan-700 text-sm">Permissions</a>
                                @endif
                                @if(hasPermission('user.delete') && ! in_array($user->reference_type, ['Staff', 'Student'], true))
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>

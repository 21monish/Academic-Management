<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles & Permissions</h2>
                <p class="mt-1 text-sm text-gray-500">Manage role labels and role default permissions. Final access is assigned directly on user accounts.</p>
            </div>
            @if(hasPermission('role.create'))
                <a href="{{ route('roles.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">+ Add Role</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Role</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">University</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Staff Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Users</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $role->role_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $role->description ?: 'No description' }}</div>
                                    @if ($role->is_system_role)
                                        <span class="mt-2 inline-flex rounded-md bg-cyan-50 px-2 py-1 text-xs font-semibold text-cyan-700">System role</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $role->university?->name ?? 'Global' }}</td>
                                <td class="px-4 py-3">{{ $role->staff_type ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $role->users_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $role->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(hasPermission('role.update'))
                                        <a href="{{ route('roles.edit', $role) }}" class="font-semibold text-indigo-600">Edit</a>
                                    @endif
                                    @if(hasPermission('role.delete') && ! $role->is_system_role && ! $role->users_count)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="ml-3 inline" onsubmit="return confirm('Delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-semibold text-red-600">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No roles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $roles->links() }}</div>
        </div>
    </div>
</x-app-layout>

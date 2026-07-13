<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="university_id" value="University" />
        <select id="university_id" name="university_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @disabled($role?->is_system_role || auth()->user()?->university_id)>
            <option value="">Global Role</option>
            @foreach($universities as $university)
                <option value="{{ $university->university_id }}" @selected((string) old('university_id', $role?->university_id ?? auth()->user()?->university_id) === (string) $university->university_id)>{{ $university->name }}</option>
            @endforeach
        </select>
        @if($role?->is_system_role)
            <input type="hidden" name="university_id" value="">
            <p class="mt-1 text-xs text-slate-500">System roles are global.</p>
        @elseif(auth()->user()?->university_id)
            <input type="hidden" name="university_id" value="{{ auth()->user()->university_id }}">
            <p class="mt-1 text-xs text-slate-500">New roles are limited to your university.</p>
        @endif
        <x-input-error :messages="$errors->get('university_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role_name" value="Role Name" />
        <x-text-input id="role_name" name="role_name" class="mt-1 block w-full" :value="old('role_name', $role?->role_name)" :disabled="$role?->is_system_role" required />
        @if ($role?->is_system_role)
            <input type="hidden" name="role_name" value="{{ $role->role_name }}">
            <p class="mt-1 text-xs text-slate-500">System role names are protected.</p>
        @endif
        <x-input-error :messages="$errors->get('role_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="staff_type" value="Staff Type" />
        <select id="staff_type" name="staff_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" @disabled($role?->is_system_role)>
            <option value="">Not a staff role</option>
            @foreach($staffTypes as $staffType)
                <option value="{{ $staffType }}" @selected(old('staff_type', $role?->staff_type) === $staffType)>{{ $staffType }}</option>
            @endforeach
        </select>
        @if ($role?->is_system_role)
            <input type="hidden" name="staff_type" value="{{ $role->staff_type }}">
            <p class="mt-1 text-xs text-slate-500">System role staff type is managed by the role seeder.</p>
        @endif
        <x-input-error :messages="$errors->get('staff_type')" class="mt-2" />
    </div>

    <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $role?->is_active ?? true)) @disabled($role?->role_name === 'Super Admin')>
        @if($role?->role_name === 'Super Admin')
            <input type="hidden" name="is_active" value="1">
        @endif
        <span>
            <span class="block text-sm font-semibold text-slate-900">Active</span>
            <span class="block text-xs text-slate-500">{{ $role?->role_name === 'Super Admin' ? 'Super Admin always stays active.' : 'Inactive roles cannot be selected for new users.' }}</span>
        </span>
    </label>
</div>

<div>
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $role?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="rounded-lg border border-cyan-100 bg-cyan-50 p-4 text-sm text-cyan-800">
    Permissions are assigned on each user account. Role permissions are used as defaults when creating or updating users.
</div>

<div class="flex justify-end gap-3 border-t border-slate-200 pt-5">
    <a href="{{ route('roles.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save Role</button>
</div>

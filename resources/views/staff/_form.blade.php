@php
    /** @var \App\Models\Staff|null $staff */
    $staff = $staff ?? null;
    $teaching = $teaching ?? null;
    $nonTeaching = $nonTeaching ?? null;
    $staffRoles = $staffRoles ?? collect();
    $staffRoleOptions = $staffRoles->map(function ($role) {
        return [
            'id' => (string) $role->role_id,
            'name' => $role->role_name,
            'staffType' => $role->staff_type,
        ];
    })->values();
    $accountRoleId = old('account_role_id', $accountRoleId ?? $staff?->userAccount?->role_id);

    $staffType = old('staff_type', $staff?->staff_type ?? 'Teaching');
    $selectedStaffRole = old('staff_role', match ($staffType) {
        'Teaching' => old('designation', $teaching?->designation),
        'Non-Teaching' => old('role', $nonTeaching?->role),
        default => old('designation', $teaching?->designation) ?: old('role', $nonTeaching?->role),
    });
@endphp

<div>
    <x-input-label for="college_id" value="College" />
    <select id="college_id" name="college_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">Select College</option>
        @foreach ($colleges as $college)
            <option value="{{ $college->college_id }}" @selected((string) old('college_id', $staff?->college_id) === (string) $college->college_id)>{{ $college->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="dept_id" value="Department" />
        <select id="dept_id" name="dept_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select Department (optional)</option>
            @foreach ($departments as $department)
                <option value="{{ $department->dept_id }}" @selected((string) old('dept_id', $staff?->dept_id) === (string) $department->dept_id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('dept_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="employee_code" value="Employee Code" />
        <x-text-input id="employee_code" name="employee_code" class="block mt-1 w-full" :value="old('employee_code', $staff?->employee_code)" required />
        <x-input-error :messages="$errors->get('employee_code')" class="mt-2" />
        @unless($staff)
            <p class="mt-1 text-xs text-gray-500">Staff login username will be this employee code.</p>
        @endunless
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="first_name" value="First Name" />
        <x-text-input id="first_name" name="first_name" class="block mt-1 w-full" :value="old('first_name', $staff?->first_name)" required />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="last_name" value="Last Name" />
        <x-text-input id="last_name" name="last_name" class="block mt-1 w-full" :value="old('last_name', $staff?->last_name)" required />
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="gender" value="Gender" />
        <select id="gender" name="gender" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select Gender (optional)</option>
            @foreach (['Male','Female','Other'] as $g)
                <option value="{{ $g }}" @selected(old('gender', $staff?->gender) === $g)>{{ $g }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="dob" value="Date of Birth" />
        <x-text-input id="dob" name="dob" type="date" class="block mt-1 w-full" :value="old('dob', $staff?->dob?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('dob')" class="mt-2" />
        @unless($staff)
            <p class="mt-1 text-xs text-gray-500">First password will be this DOB in ddmmyyyy format. If DOB is blank, employee code is used.</p>
        @endunless
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $staff?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $staff?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="address" value="Address" />
    <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $staff?->address) }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="photo" value="Photo" />
    @if($staff?->photo_url)
        @php($photoSrc = \Illuminate\Support\Str::startsWith($staff->photo_url, ['http://', 'https://', '/']) ? $staff->photo_url : asset($staff->photo_url))
        <div class="mt-2 flex items-center gap-3">
            <img src="{{ $photoSrc }}" alt="Current staff photo" class="h-14 w-14 rounded-full object-cover">
            <span class="text-xs text-gray-500">{{ $staff->photo_url }}</span>
        </div>
    @endif
    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="block mt-2 w-full rounded-md border border-gray-300 text-sm shadow-sm file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
    <p class="mt-2 text-xs text-gray-500">Accepted: JPG, PNG, WEBP. The file is stored in <code>uploads/photos</code> and its path is saved in <code>photo_url</code>.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <div>
        <x-input-label for="staff_type" value="Staff Type" />
        <select id="staff_type" name="staff_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['Teaching','Non-Teaching','Both'] as $t)
                <option value="{{ $t }}" @selected($staffType === $t)>{{ $t }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('staff_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="account_role_id" value="Role" />
        <select id="account_role_id" name="account_role_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" data-selected-role="{{ $accountRoleId }}" required>
            <option value="">Select Role</option>
        </select>
        <x-input-error :messages="$errors->get('account_role_id')" class="mt-2" />
        <p class="mt-1 text-xs text-gray-500">Role options come from Roles & Permissions where Staff Type is set.</p>
    </div>

    <div>
        <x-input-label for="employment_type" value="Employment Type" />
        <select id="employment_type" name="employment_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['Permanent','Contractual','Visiting'] as $t)
                <option value="{{ $t }}" @selected(old('employment_type', $staff?->employment_type) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="join_date" value="Join Date" />
        <x-text-input id="join_date" name="join_date" type="date" class="block mt-1 w-full" :value="old('join_date', $staff?->join_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('join_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contract_end_date" value="Contract End Date" />
        <x-text-input id="contract_end_date" name="contract_end_date" type="date" class="block mt-1 w-full" :value="old('contract_end_date', $staff?->contract_end_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('contract_end_date')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    @if ($staff)
        <label class="inline-flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $staff->is_active))>
            <span class="ms-2 text-sm text-gray-700">Active</span>
        </label>
    @else
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', true))>
            <span class="ms-2 text-sm text-gray-700">Active</span>
        </label>
    @endif
    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
</div>

<hr class="my-6" />

<div data-staff-profile="Teaching" class="{{ $staffType !== 'Teaching' ? 'hidden' : '' }}">
    <div class="text-gray-800 font-semibold">Teaching Profile</div>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="qualification" value="Qualification" />
            <x-text-input id="qualification" name="qualification" class="block mt-1 w-full" :value="old('qualification', $teaching?->qualification)" />
            <x-input-error :messages="$errors->get('qualification')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="specialization" value="Specialization" />
            <x-text-input id="specialization" name="specialization" class="block mt-1 w-full" :value="old('specialization', $teaching?->specialization)" />
            <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="experience_years" value="Experience (Years)" />
        <x-text-input id="experience_years" name="experience_years" type="number" min="0" class="block mt-1 w-full" :value="old('experience_years', $teaching?->experience_years)" />
        <x-input-error :messages="$errors->get('experience_years')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="research_area" value="Research Area" />
        <textarea id="research_area" name="research_area" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('research_area', $teaching?->research_area) }}</textarea>
        <x-input-error :messages="$errors->get('research_area')" class="mt-2" />
    </div>
</div>

<div data-staff-profile="Non-Teaching" class="{{ $staffType !== 'Non-Teaching' ? 'hidden' : '' }}">
    <div class="text-gray-800 font-semibold">Non-Teaching Profile</div>

    <div class="mt-4">
        <x-input-label for="department_section" value="Department Section" />
        <x-text-input id="department_section" name="department_section" class="block mt-1 w-full" :value="old('department_section', $nonTeaching?->department_section)" />
        <x-input-error :messages="$errors->get('department_section')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="grade" value="Grade" />
        <x-text-input id="grade" name="grade" class="block mt-1 w-full" :value="old('grade', $nonTeaching?->grade)" />
        <x-input-error :messages="$errors->get('grade')" class="mt-2" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const staffType = document.getElementById('staff_type');
        const accountRole = document.getElementById('account_role_id');
        const profiles = Array.from(document.querySelectorAll('[data-staff-profile]'));
        const roles = @json($staffRoleOptions);
        const allowedStaffRoleTypes = {
            Teaching: ['Teaching', 'Both'],
            'Non-Teaching': ['Non-Teaching', 'Both'],
            Both: ['Teaching', 'Non-Teaching', 'Both'],
        };

        if (! staffType) {
            return;
        }

        const syncStaffProfile = () => {
            const selectedRole = accountRole?.dataset.selectedRole ?? accountRole?.value ?? '';

            const toggleFields = (container, active) => {
                container.classList.toggle('hidden', ! active);
                container.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.disabled = ! active;
                });
            };

            profiles.forEach((profile) => {
                toggleFields(profile, staffType.value === 'Both' || profile.dataset.staffProfile === staffType.value);
            });

            if (! accountRole) {
                return;
            }

            accountRole.replaceChildren(new Option('Select Role', ''));
            roles
                .filter((role) => (allowedStaffRoleTypes[staffType.value] || []).includes(role.staffType))
                .forEach((role) => {
                    accountRole.add(new Option(`${role.name} (${role.staffType})`, role.id));
            });

            accountRole.value = Array.from(accountRole.options).some((option) => option.value === selectedRole) ? selectedRole : '';
            accountRole.dataset.selectedRole = accountRole.value;
        };

        staffType.addEventListener('change', () => {
            if (accountRole) {
                accountRole.dataset.selectedRole = '';
                accountRole.value = '';
            }

            syncStaffProfile();
        });
        accountRole?.addEventListener('change', () => {
            accountRole.dataset.selectedRole = accountRole.value;
        });
        syncStaffProfile();
    });
</script>


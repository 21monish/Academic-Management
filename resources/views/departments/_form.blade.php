@php $department = $department ?? null; @endphp

<div>
    <x-input-label for="college_id" value="College" />
    <select id="college_id" name="college_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">Select College</option>
        @foreach ($colleges as $college)
            <option value="{{ $college->college_id }}" @selected((string) old('college_id', $department?->college_id) === (string) $college->college_id)>{{ $college->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $department?->code)" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $department?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $department?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div>
    <x-input-label for="hod_staff_id" value="HOD Staff ID" />
    <x-text-input id="hod_staff_id" name="hod_staff_id" type="number" class="block mt-1 w-full" :value="old('hod_staff_id', $department?->hod_staff_id)" />
    <x-input-error :messages="$errors->get('hod_staff_id')" class="mt-2" />
</div>

@if ($department)
    <label class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $department->is_active))>
        <span class="ms-2 text-sm text-gray-700">Active</span>
    </label>
@endif

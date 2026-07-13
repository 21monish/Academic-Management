@php
    $isEdit = (bool) $programme;
    $pageTitle = $isEdit ? 'Edit Programme' : 'Add Programme';
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">{{ $pageTitle }}</h2>
        @if($isEdit)
            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">ID: {{ $programme->programme_id }}</span>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('academic.programmes.update', $programme) : route('academic.programmes.store') }}" class="mt-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="dept_id" value="Department" />
                <select id="dept_id" name="dept_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->dept_id }}" @selected((string)old('dept_id', $programme?->dept_id) === (string)$dept->dept_id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('dept_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="level" value="Level" />
                <select id="level" name="level" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Level</option>
                    @foreach(['UG','PG','Diploma','PhD'] as $lvl)
                        <option value="{{ $lvl }}" @selected(old('level', $programme?->level) === $lvl)>{{ $lvl }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('level')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="code" value="Code" />
                <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $programme?->code)" required />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $programme?->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="duration_semesters" value="Duration (Semesters)" />
                <x-text-input id="duration_semesters" name="duration_semesters" type="number" min="0" class="block mt-1 w-full" :value="old('duration_semesters', $programme?->duration_semesters)" />
                <x-input-error :messages="$errors->get('duration_semesters')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="total_credits" value="Total Credits" />
                <x-text-input id="total_credits" name="total_credits" type="number" min="0" class="block mt-1 w-full" :value="old('total_credits', $programme?->total_credits)" />
                <x-input-error :messages="$errors->get('total_credits')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6">
            @if ($programme)
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', (bool) $programme->is_active))>
                    <span class="ms-2 text-sm text-gray-700">Active</span>
                </label>
            @else
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', true))>
                    <span class="ms-2 text-sm text-gray-700">Active</span>
                </label>
            @endif
            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
        </div>

        <div class="mt-8 flex gap-3">
            <a href="{{ route('academic.programmes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Back</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm" onclick="this.disabled=true; this.textContent='Saving...';">
                {{ $isEdit ? 'Update Programme' : 'Create Programme' }}
            </button>
        </div>
    </form>
</div>


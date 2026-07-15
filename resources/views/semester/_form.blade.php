<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Programme --}}
    <div>
        <label for="programme_id" class="block text-sm font-medium text-gray-700 mb-2">
            Programme <span class="text-red-500">*</span>
        </label>

        <select
            id="programme_id"
            name="programme_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('programme_id') border-red-500 @enderror">

            <option value="">-- Select Programme --</option>

            @foreach($programmes as $programme)
                <option
                    value="{{ $programme->programme_id }}"
                    @selected(old('programme_id', $semester->programme_id ?? '') == $programme->programme_id)>
                    {{ $programme->name }}
                </option>
            @endforeach

        </select>

        @error('programme_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Semester Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Semester Name <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $semester->name ?? '') }}"
            placeholder="Semester 1"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">

        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Semester Number --}}
    <div>
        <label for="semester_no" class="block text-sm font-medium text-gray-700 mb-2">
            Semester Number <span class="text-red-500">*</span>
        </label>

        <input
            type="number"
            min="1"
            max="12"
            id="semester_no"
            name="semester_no"
            value="{{ old('semester_no', $semester->semester_no ?? '') }}"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('semester_no') border-red-500 @enderror">

        @error('semester_no')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
            Status
        </label>

        <select
            id="is_active"
            name="is_active"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            <option value="1"
                @selected(old('is_active', $semester->is_active ?? 1) == 1)>
                Active
            </option>

            <option value="0"
                @selected(old('is_active', $semester->is_active ?? 1) == 0)>
                Inactive
            </option>

        </select>
    </div>

</div>

<div class="mt-8 flex items-center gap-3">

    <button
        type="submit"
        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">

        {{ isset($semester) ? 'Update Semester' : 'Create Semester' }}

    </button>

    <a
        href="{{ route('academic.semesters.index') }}"
        class="inline-flex items-center px-5 py-2.5 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300">

        Cancel

    </a>

</div>

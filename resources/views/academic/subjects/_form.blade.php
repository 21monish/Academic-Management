@php
    /**
     * Expected variables:
     * - $departments
     * - $programmes (optional)
     * - $semesters (optional)
     * - $subjectTypes
     * - $categories
     * - $statuses
     * - $subject (optional)
     */

    $subject = $subject ?? null;

    $departmentId = old('department_id', $subject?->dept_id);
    $programmeId = old('programme_id', $selectedProgrammeId ?? $subject?->curriculum?->first()?->programme_id);
    $semesterId = old('semester_id', $selectedSemesterId ?? $subject?->curriculum?->first()?->semester_id);
@endphp

<form method="POST" action="{{ isset($actionRoute) ? $actionRoute : '' }}" id="subject-form" class="space-y-6">
    @csrf

    @if(isset($methodOverride))
        @method($methodOverride)
    @endif

    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                <x-input-label for="code" value="Subject Code" />
                <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $subject?->code)" required />
                @error('code')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-9">
                <x-input-label for="name" value="Subject Name" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $subject?->name)" required />
                @error('name')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="department_id" value="Department" />
                <select id="department_id" name="department_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->dept_id }}" @selected((string)$departmentId === (string)$dept->dept_id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="programme_id" value="Programme" />
                <select id="programme_id" name="programme_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Programme</option>
                    @foreach(($programmes ?? []) as $programme)
                        <option value="{{ $programme->programme_id ?? $programme->id }}" @selected((string)$programmeId === (string)($programme->programme_id ?? $programme->id))>
                            {{ $programme->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <x-input-label for="semester_id" value="Semester" />
                <select id="semester_id" name="semester_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Semester</option>
                    @foreach(($semesters ?? []) as $semester)
                        <option value="{{ $semester->semester_id ?? $semester->id }}" @selected((string)$semesterId === (string)($semester->semester_id ?? $semester->id))>
                            {{ $semester->name ?? $semester->semester_no }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <x-input-label for="type" value="Subject Type" />
                <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Type</option>
                    @foreach($subjectTypes as $value => $label)
                        <option value="{{ $value }}" @selected((string)old('type', $subject?->type) === (string)$value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="category" value="Subject Category" />
                <select id="category" name="category" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" @selected((string)old('category', $subject?->subject_category) === (string)$value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="credits" value="Credits" />
                <x-text-input id="credits" name="credits" type="number" min="0" step="1" class="block mt-1 w-full" :value="old('credits', $subject?->credits)" />
                @error('credits')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="theory_hours" value="Theory Hours" />
                <x-text-input id="theory_hours" name="theory_hours" type="number" min="0" step="1" class="block mt-1 w-full" :value="old('theory_hours', $subject?->theory_hours)" />
                @error('theory_hours')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="practical_hours" value="Practical Hours" />
                <x-text-input id="practical_hours" name="practical_hours" type="number" min="0" step="1" class="block mt-1 w-full" :value="old('practical_hours', $subject?->lab_hours)" />
                @error('practical_hours')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-4">
                <x-input-label for="tutorial_hours" value="Tutorial Hours" />
                <x-text-input id="tutorial_hours" name="tutorial_hours" type="number" min="0" step="1" class="block mt-1 w-full" :value="old('tutorial_hours', $subject?->tutorial_hours)" />
                @error('tutorial_hours')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-12">
                <x-input-label for="is_active" value="Status" />
                <div class="flex flex-wrap gap-3 items-center mt-1">
                    @php
                        $statusValue = old('is_active', $subject?->is_active ? '1' : '0');
                    @endphp
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_active" value="1" class="mr-2" @checked($statusValue === '1')>
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_active" value="0" class="mr-2" @checked($statusValue === '0')>
                        <span class="text-sm text-gray-700">Inactive</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <a href="{{ $cancelUrl ?? url()->previous() }}" class="text-gray-600 hover:text-gray-900">Cancel</a>

        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">
            {{ $submitLabel ?? 'Save' }}
        </button>
    </div>
</form>


@php($isEdit = $curriculum?->exists)

<form method="POST" action="{{ $isEdit ? route('academic.curriculum.update', $curriculum) : route('academic.curriculum.store') }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="programme_id" value="Programme" />
                <select id="programme_id" name="programme_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select programme</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->programme_id }}" @selected((string) old('programme_id', $curriculum?->programme_id) === (string) $programme->programme_id)>{{ $programme->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('programme_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="semester_id" value="Semester" />
                <select id="semester_id" name="semester_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select semester</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->semester_id }}" @selected((string) old('semester_id', $curriculum?->semester_id) === (string) $semester->semester_id)>Sem {{ $semester->semester_no }} {{ $semester->academic_year ? '(' . $semester->academic_year . ')' : '' }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="subject_id" value="Subject" />
                <select id="subject_id" name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->subject_id }}" @selected((string) old('subject_id', $curriculum?->subject_id) === (string) $subject->subject_id)>{{ $subject->code }} - {{ $subject->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="max_marks" value="Max Marks" />
                <x-text-input id="max_marks" name="max_marks" type="number" min="0" class="mt-1 block w-full" :value="old('max_marks', $curriculum?->max_marks)" />
                <x-input-error :messages="$errors->get('max_marks')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="min_passing_marks" value="Passing Marks" />
                <x-text-input id="min_passing_marks" name="min_passing_marks" type="number" min="0" class="mt-1 block w-full" :value="old('min_passing_marks', $curriculum?->min_passing_marks)" />
                <x-input-error :messages="$errors->get('min_passing_marks')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_mandatory" value="0">
                    <input type="checkbox" name="is_mandatory" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('is_mandatory', $curriculum?->is_mandatory ?? true))>
                    <span class="ms-2 text-sm text-slate-700">Mandatory subject</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('academic.curriculum.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Back</a>
        <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ $isEdit ? 'Update Curriculum' : 'Add Curriculum' }}</button>
    </div>
</form>

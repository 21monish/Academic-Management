@php($isEdit = $academicYear?->exists)

<form method="POST" action="{{ $isEdit ? route('academic.academic-years.update', $academicYear) : route('academic.academic-years.store') }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="college_id" value="College" />
                <select id="college_id" name="college_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select college</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->college_id }}" @selected((string) old('college_id', $academicYear?->college_id) === (string) $college->college_id)>{{ $college->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="label" value="Academic Year" />
                <x-text-input id="label" name="label" class="mt-1 block w-full" :value="old('label', $academicYear?->label)" placeholder="2026-27" required />
                <x-input-error :messages="$errors->get('label')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="start_date" value="Start Date" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', $academicYear?->start_date?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="end_date" value="End Date" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', $academicYear?->end_date?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $academicYear?->status ?? 'Upcoming') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_current" value="0">
                    <input type="checkbox" name="is_current" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('is_current', $academicYear?->is_current))>
                    <span class="ms-2 text-sm text-slate-700">Current academic year</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('academic.academic-years.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Back</a>
        <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ $isEdit ? 'Update Academic Year' : 'Create Academic Year' }}</button>
    </div>
</form>

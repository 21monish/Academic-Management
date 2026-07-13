@php($isEdit = $electiveGroup?->exists)

<form method="POST" action="{{ $isEdit ? route('academic.elective-groups.update', $electiveGroup) : route('academic.elective-groups.store') }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-input-label for="curriculum_id" value="Elective Curriculum Subject" />
                <select id="curriculum_id" name="curriculum_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select elective subject</option>
                    @foreach($curriculumItems as $item)
                        <option value="{{ $item->curriculum_id }}" @selected((string) old('curriculum_id', $electiveGroup?->curriculum_id) === (string) $item->curriculum_id)>
                            {{ $item->programme?->name }} / Sem {{ $item->semester?->semester_no }} / {{ $item->subject?->code }} - {{ $item->subject?->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('curriculum_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="group_name" value="Group Name" />
                <x-text-input id="group_name" name="group_name" class="mt-1 block w-full" :value="old('group_name', $electiveGroup?->group_name)" placeholder="Elective-I" required />
                <x-input-error :messages="$errors->get('group_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="select_count" value="Select Count" />
                <x-text-input id="select_count" name="select_count" type="number" min="1" class="mt-1 block w-full" :value="old('select_count', $electiveGroup?->select_count ?? 1)" required />
                <x-input-error :messages="$errors->get('select_count')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('academic.elective-groups.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Back</a>
        <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">{{ $isEdit ? 'Update Group' : 'Create Group' }}</button>
    </div>
</form>

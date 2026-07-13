@php($isEdit = $category?->exists)

<form method="POST" action="{{ $isEdit ? route('academic.categories.update', $category) : route('academic.categories.store') }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="code" value="Code" />
                <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $category?->code)" required />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $category?->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="reservation_pct" value="Reservation %" />
                <x-text-input id="reservation_pct" name="reservation_pct" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('reservation_pct', $category?->reservation_pct)" />
                <x-input-error :messages="$errors->get('reservation_pct')" class="mt-2" />
            </div>

            <div class="flex items-end gap-6">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_reserved" value="0">
                    <input type="checkbox" name="is_reserved" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('is_reserved', $category?->is_reserved))>
                    <span class="ms-2 text-sm text-slate-700">Reserved</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('is_active', $category?->is_active ?? true))>
                    <span class="ms-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $category?->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('academic.categories.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Back</a>
        <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">
            {{ $isEdit ? 'Update Category' : 'Create Category' }}
        </button>
    </div>
</form>

@php $college = $college ?? null; @endphp

<div>
    <x-input-label for="university_id" value="University" />
    <select id="university_id" name="university_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">Select University</option>
        @foreach ($universities as $university)
            <option value="{{ $university->university_id }}" @selected((string) old('university_id', $college?->university_id) === (string) $university->university_id)>{{ $university->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('university_id')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $college?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $college?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="address" value="Address" />
    <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $college?->address) }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $college?->phone)" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="10 digit phone number" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $college?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="principal_name" value="Principal Name" />
        <x-text-input id="principal_name" name="principal_name" class="block mt-1 w-full" :value="old('principal_name', $college?->principal_name)" />
        <x-input-error :messages="$errors->get('principal_name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="affiliated_on" value="Affiliated On" />
        <x-text-input id="affiliated_on" name="affiliated_on" type="date" class="block mt-1 w-full" :value="old('affiliated_on', $college?->affiliated_on?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('affiliated_on')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="affiliation_type" value="Affiliation Type" />
        <select id="affiliation_type" name="affiliation_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select Type</option>
            @foreach (['Autonomous', 'Affiliated', 'Constituent'] as $type)
                <option value="{{ $type }}" @selected(old('affiliation_type', $college?->affiliation_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('affiliation_type')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="college_type" value="College Type" />
        <select id="college_type" name="college_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Select Type</option>
            @foreach (['Government', 'Private', 'Grant-in-Aid'] as $type)
                <option value="{{ $type }}" @selected(old('college_type', $college?->college_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('college_type')" class="mt-2" />
    </div>
</div>

@if ($college)
    <label class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $college->is_active))>
        <span class="ms-2 text-sm text-gray-700">Active</span>
    </label>
@endif

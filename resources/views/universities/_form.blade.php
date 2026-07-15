{{-- Shared form partial used by create.blade.php and edit.blade.php --}}
@php $u = $university ?? null; @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $u?->name)" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="address" value="Address" />
    <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $u?->address) }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div>
    <x-input-label for="logo" value="Logo" />
    @if($u?->logo_url)
        @php($logoSrc = \Illuminate\Support\Str::startsWith($u->logo_url, ['http://', 'https://', '/']) ? $u->logo_url : asset($u->logo_url))
        <div class="mt-2 flex items-center gap-3">
            <img src="{{ $logoSrc }}" alt="Current university logo" class="h-16 w-16 rounded-md border border-slate-200 object-contain p-1">
            <span class="break-all text-xs text-gray-500">{{ $u->logo_url }}</span>
        </div>
    @endif
    <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="block mt-2 w-full rounded-md border border-gray-300 text-sm shadow-sm file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
    <p class="mt-2 text-xs text-gray-500">Accepted: JPG, PNG, WEBP, SVG. Max 2 MB.</p>
</div>

<div>
    <x-input-label value="University Theme" />
    <p class="mt-1 text-xs text-slate-500">This color theme is shown to all users of this university.</p>
    <div class="mt-3 grid gap-3 sm:grid-cols-3">
        @foreach ([
            'ocean' => ['Ocean', 'bg-cyan-600', 'Cyan'],
            'royal' => ['Royal', 'bg-indigo-600', 'Indigo'],
            'forest' => ['Forest', 'bg-emerald-600', 'Emerald'],
        ] as $value => [$label, $swatch, $color])
            <label class="cursor-pointer rounded-lg border border-slate-200 bg-white p-3 transition hover:border-slate-400 has-[:checked]:border-slate-900 has-[:checked]:ring-2 has-[:checked]:ring-slate-900/15">
                <input type="radio" name="theme" value="{{ $value }}" class="sr-only" @checked(old('theme', $u?->theme ?? 'ocean') === $value)>
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <span class="h-5 w-5 rounded-full {{ $swatch }}"></span>
                    {{ $label }}
                </span>
                <span class="mt-1 block text-xs text-slate-500">{{ $color }} accent</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('theme')" class="mt-2" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $u?->phone)" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="10 digit phone number" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $u?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
</div>

<div class="rounded-lg border border-cyan-100 bg-cyan-50 p-4">
    <h3 class="text-sm font-bold text-slate-900">UPI QR Payment Settings</h3>
    <p class="mt-1 text-xs text-cyan-800">These values are used when this university generates fee payment QR codes.</p>
    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="upi_id" value="UPI ID" />
            <x-text-input id="upi_id" name="upi_id" class="block mt-1 w-full" :value="old('upi_id', $u?->upi_id)" placeholder="college@bank" />
            <x-input-error :messages="$errors->get('upi_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="upi_name" value="Payee Name" />
            <x-text-input id="upi_name" name="upi_name" class="block mt-1 w-full" :value="old('upi_name', $u?->upi_name)" placeholder="University name" />
            <x-input-error :messages="$errors->get('upi_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="upi_note_prefix" value="Note Prefix" />
            <x-text-input id="upi_note_prefix" name="upi_note_prefix" class="block mt-1 w-full" :value="old('upi_note_prefix', $u?->upi_note_prefix)" placeholder="Fee Payment" />
            <x-input-error :messages="$errors->get('upi_note_prefix')" class="mt-2" />
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="website" value="Website" />
        <x-text-input id="website" name="website" class="block mt-1 w-full" :value="old('website', $u?->website)" />
        <x-input-error :messages="$errors->get('website')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="established_date" value="Established Date" />
        <x-text-input id="established_date" name="established_date" type="date" class="block mt-1 w-full" :value="old('established_date', $u?->established_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('established_date')" class="mt-2" />
    </div>
</div>

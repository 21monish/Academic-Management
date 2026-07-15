@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-black text-slate-700']) }}>
    {{ $value ?? $slot }}
</label>

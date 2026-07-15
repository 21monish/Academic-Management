@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-cyan-600 focus:ring-cyan-600 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500']) }}>

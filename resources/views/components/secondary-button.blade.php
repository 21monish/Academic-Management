<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-700 shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-900 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>

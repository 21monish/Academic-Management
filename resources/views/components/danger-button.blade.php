<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-red-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:translate-y-0']) }}>
    {{ $slot }}
</button>

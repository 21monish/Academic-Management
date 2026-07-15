<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-action inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-transparent bg-cyan-700 px-4 py-2 text-sm font-black text-white shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>

<form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
    <x-text-input name="q" :value="request('q')" placeholder="Search this list" class="flex-1" />
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    @if(request()->has('q'))
        <a href="{{ url()->current() }}" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-600">Clear</a>
    @endif
</form>

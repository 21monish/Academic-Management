<x-guest-layout>
    <div class="text-center">
        <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">403</p>
        <h1 class="mt-3 text-2xl font-bold text-slate-950">Access denied</h1>
        <p class="mt-2 text-sm text-slate-600">Your account does not have permission to open this page.</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Back to dashboard</a>
    </div>
</x-guest-layout>

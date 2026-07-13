<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Add Elective Group</h2>
            <a href="{{ route('academic.elective-groups.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">@include('academic.elective-groups._form')</div>
</x-app-layout>

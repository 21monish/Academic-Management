<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Hall Ticket Generation</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.logistics.tickets.generate') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3">
            @csrf
            <select name="config_id" class="rounded-md border-gray-300 md:col-span-2" required><option value="">Hall ticket config</option>@foreach($configsList as $config)<option value="{{ $config->config_id }}">{{ $config->exam?->exam_name }} / {{ $config->college?->name }}</option>@endforeach</select>
            <select name="exam_type" class="rounded-md border-gray-300"><option>Both</option><option>Theory</option><option>Practical</option></select>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-3">Generate / Recheck Eligibility</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Ticket No</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Eligibility</th><th class="px-4 py-3 text-left">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($tickets as $ticket)<tr><td class="px-4 py-3 font-semibold">{{ $ticket->hall_ticket_no }}</td><td class="px-4 py-3">{{ $ticket->student?->enrollment_no }} - {{ $ticket->student?->first_name }}</td><td class="px-4 py-3">{{ $ticket->config?->exam?->exam_name }}</td><td class="px-4 py-3">{{ $ticket->is_eligible ? 'Eligible' : ($ticket->ineligibility_reason ?? 'Pending') }}</td><td class="px-4 py-3">{{ $ticket->status }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No hall tickets.</td></tr>@endforelse</tbody></table>
        </div>
        <div class="mt-4">{{ $tickets->links() }}</div>
    </div>
</x-app-layout>

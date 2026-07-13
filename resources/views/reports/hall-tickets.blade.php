<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Hall Ticket Print</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="q" :value="request('q')" placeholder="Hall ticket or enrollment" class="flex-1" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Hall Ticket</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Eligibility</th><th class="px-4 py-3 text-left">Print</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($tickets as $ticket)<tr><td class="px-4 py-3 font-semibold">{{ $ticket->hall_ticket_no }}</td><td class="px-4 py-3">{{ $ticket->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $ticket->config?->exam?->exam_name }}</td><td class="px-4 py-3">{{ $ticket->is_eligible ? 'Eligible' : 'Blocked' }}</td><td class="px-4 py-3"><a class="font-semibold text-cyan-700" href="{{ route('reports.hall-tickets.print', $ticket) }}">Print/PDF</a></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No hall tickets found.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $tickets->links() }}</div>
    </div>
</x-app-layout>

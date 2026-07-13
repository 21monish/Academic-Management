<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Notice Acknowledgements</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('notices.acknowledgements.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="notice_id" class="rounded-md border-gray-300" required><option value="">Notice</option>@foreach($noticesList as $notice)<option value="{{ $notice->notice_id }}">{{ $notice->title }}</option>@endforeach</select>
            <select name="user_id" class="rounded-md border-gray-300" required><option value="">User</option>@foreach($users as $user)<option value="{{ $user->user_id }}">{{ $user->username }} - {{ $user->email }}</option>@endforeach</select>
            <x-text-input name="ip_address" placeholder="IP auto" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Acknowledge</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Notice</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Acknowledged</th><th class="px-4 py-3 text-left">IP</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($acknowledgements as $ack)<tr><td class="px-4 py-3">{{ $ack->notice?->title }}</td><td class="px-4 py-3">{{ $ack->user?->username }}</td><td class="px-4 py-3">{{ $ack->acknowledged_at }}</td><td class="px-4 py-3">{{ $ack->ip_address }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('notices.acknowledgements.destroy', $ack) }}" onsubmit="return confirm('Delete acknowledgement?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No acknowledgements.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $acknowledgements->links() }}</div>
    </div>
</x-app-layout>

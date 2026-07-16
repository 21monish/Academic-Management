<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">Approval Requests</h2>
            <p class="mt-1 text-sm text-slate-500">Review high-risk actions requested by users below your scope.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-sm font-bold text-slate-900">Recent Requests</p>
                <p class="text-xs font-semibold text-slate-500">{{ $approvalRequests->count() }} visible request(s)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Requested By</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-black uppercase text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($approvalRequests as $approval)
                            @php($canApprove = $workflow->canApprove(auth()->user(), $approval))
                            <tr>
                                <td class="px-4 py-4 font-bold text-slate-900">{{ $workflow->actionLabel($approval->action) }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-900">{{ $approval->requester?->username ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $approval->requester?->role?->role_name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    <div class="font-semibold">{{ class_basename($approval->subject_type) }} #{{ $approval->subject_id }}</div>
                                    @if(! empty($approval->payload))
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ collect($approval->payload)->take(3)->map(fn ($value, $key) => $key.': '.(is_scalar($value) ? $value : json_encode($value)))->implode(' / ') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $approval->status === \App\Models\ApprovalRequest::STATUS_PENDING ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-100' : ($approval->status === \App\Models\ApprovalRequest::STATUS_APPROVED ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-red-50 text-red-700 ring-1 ring-red-100') }}">
                                        {{ $approval->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $approval->requested_at?->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-4">
                                    @if($canApprove)
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form method="POST" action="{{ route('approvals.approve', $approval) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('approvals.reject', $approval) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="block text-right text-xs font-semibold text-slate-400">No action</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">No approval requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

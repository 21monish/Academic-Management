<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Notice Audience</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('notices.audiences.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="notice_id" class="rounded-md border-gray-300" required><option value="">Notice</option>@foreach($noticesList as $notice)<option value="{{ $notice->notice_id }}">{{ $notice->title }}</option>@endforeach</select>
            <select name="target_type" class="rounded-md border-gray-300" required><option>Department</option><option>Programme</option><option>Semester</option><option>Role</option><option>Individual</option></select>
            <x-text-input name="target_id" type="number" placeholder="Target ID" required />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Add Audience</button>
            <div class="md:col-span-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">Target IDs: Departments {{ $departments->pluck('name','dept_id')->map(fn($name,$id) => $id.': '.$name)->join(' | ') }}; Programmes {{ $programmes->pluck('name','programme_id')->map(fn($name,$id) => $id.': '.$name)->join(' | ') }}; Semesters {{ $semesters->pluck('semester_no','semester_id')->map(fn($no,$id) => $id.': Sem '.$no)->join(' | ') }}; Roles {{ $roles->pluck('role_name','role_id')->map(fn($name,$id) => $id.': '.$name)->join(' | ') }}</div>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Notice</th><th class="px-4 py-3 text-left">Target Type</th><th class="px-4 py-3 text-left">Target</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($audiences as $audience)<tr><td class="px-4 py-3">{{ $audience->notice?->title }}</td><td class="px-4 py-3">{{ $audience->target_type }}</td><td class="px-4 py-3">{{ $audience->target_label }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('notices.audiences.destroy', $audience) }}" onsubmit="return confirm('Delete audience?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No audience rows.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $audiences->links() }}</div>
    </div>
</x-app-layout>

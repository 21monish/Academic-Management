<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Notices</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="q" :value="request('q')" placeholder="Search notices" class="flex-1" />
            @if($scopedDepartment ?? null)
                <select class="rounded-md border-gray-300 bg-slate-100 text-slate-700" disabled>
                    <option>{{ $scopedDepartment->name }}</option>
                </select>
            @else
                <select name="dept_id" class="rounded-md border-gray-300">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @selected((string) $departmentFilter === (string) $department->dept_id)>
                            {{ $department->name }}{{ $department->college ? ' - '.$department->college->name : '' }}
                        </option>
                    @endforeach
                </select>
            @endif
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            @if(request()->hasAny(['q', 'dept_id']))
                <a href="{{ url()->current() }}" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-600">Clear</a>
            @endif
        </form>
        @if($canManageNotices)
            <form method="POST" action="{{ route('notices.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
                @csrf
                @if($scopedCollege ?? null)
                    <input type="hidden" name="college_id" value="{{ $scopedCollege->college_id }}">
                    <select class="rounded-md border-gray-300 bg-slate-100 text-slate-700" disabled>
                        <option>{{ $scopedCollege->name }}</option>
                    </select>
                @else
                    <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
                @endif
                @if($scopedDepartment ?? null)
                    <input type="hidden" name="dept_id" value="{{ $scopedDepartment->dept_id }}">
                    <select class="rounded-md border-gray-300 bg-slate-100 text-slate-700" disabled>
                        <option>{{ $scopedDepartment->name }}</option>
                    </select>
                @else
                    <select name="dept_id" class="rounded-md border-gray-300"><option value="">Department</option>@foreach($departments as $department)<option value="{{ $department->dept_id }}">{{ $department->name }}{{ $department->college ? ' - '.$department->college->name : '' }}</option>@endforeach</select>
                @endif
                <select name="notice_category_id" class="rounded-md border-gray-300"><option value="">Category</option>@foreach($categoriesList as $category)<option value="{{ $category->notice_category_id }}">{{ $category->name }}</option>@endforeach</select>
                <select name="priority" class="rounded-md border-gray-300" required>@foreach(['Normal','Low','High','Urgent'] as $priority)<option>{{ $priority }}</option>@endforeach</select>
                <select name="audience_type" class="rounded-md border-gray-300" required>@foreach(['All','College','Dept','Programme','Semester','Role'] as $type)<option>{{ $type }}</option>@endforeach</select>
                <x-text-input name="title" placeholder="Notice title" class="md:col-span-2" required />
                <x-text-input name="valid_from" type="date" />
                <x-text-input name="valid_until" type="date" />
                <textarea name="content" rows="3" class="rounded-md border-gray-300 md:col-span-4" placeholder="Notice content"></textarea>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_pinned" value="1" class="rounded border-gray-300"> Pinned</label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_published" value="1" class="rounded border-gray-300"> Published</label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="requires_acknowledgement" value="1" class="rounded border-gray-300"> Requires acknowledgement</label>
                <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Notice</button>
            </form>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Notice</th><th class="px-4 py-3 text-left">College</th><th class="px-4 py-3 text-left">Department</th><th class="px-4 py-3 text-left">Category</th><th class="px-4 py-3 text-left">Audience</th><th class="px-4 py-3 text-left">Status</th>@if(hasPermission('notice.delete'))<th></th>@endif</tr></thead><tbody class="divide-y divide-slate-100">@forelse($notices as $notice)<tr><td class="px-4 py-3"><div class="font-semibold">{{ $notice->title }}</div><div class="text-xs text-slate-500">{{ $notice->priority }} / {{ $notice->valid_from }} - {{ $notice->valid_until ?: 'Open' }}</div></td><td class="px-4 py-3">{{ $notice->college?->name ?? '-' }}</td><td class="px-4 py-3">{{ $notice->department?->name ?? '-' }}</td><td class="px-4 py-3">{{ $notice->category?->name ?? '-' }}</td><td class="px-4 py-3">{{ $notice->audience_type }}</td><td class="px-4 py-3">{{ $notice->is_published ? 'Published' : 'Draft' }}{{ $notice->is_pinned ? ' / Pinned' : '' }}</td>@if(hasPermission('notice.delete'))<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('notices.destroy', $notice) }}" onsubmit="return confirm('Delete notice?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td>@endif</tr>@empty<tr><td colspan="{{ hasPermission('notice.delete') ? 7 : 6 }}" class="px-4 py-6 text-center text-slate-500">No notices.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $notices->links() }}</div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Notice Attachments</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('notices.attachments.store') }}" enctype="multipart/form-data" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="notice_id" class="rounded-md border-gray-300" required><option value="">Notice</option>@foreach($noticesList as $notice)<option value="{{ $notice->notice_id }}">{{ $notice->title }}</option>@endforeach</select>
            <div class="md:col-span-2">
                <input name="attachment" type="file" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp" class="block w-full cursor-pointer rounded-md border border-slate-300 text-sm text-slate-700 file:mr-3 file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100" required />
                <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                <p class="mt-1 text-xs text-slate-500">PDF, DOC, DOCX, JPG, PNG or WEBP - max 5 MB. Stored in <code>uploads/notices/</code>.</p>
            </div>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Upload Attachment</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Notice</th><th class="px-4 py-3 text-left">File</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Size</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($attachments as $attachment)@php($fileUrl = \Illuminate\Support\Str::startsWith($attachment->file_url, ['http://', 'https://', '/']) ? $attachment->file_url : asset($attachment->file_url))<tr><td class="px-4 py-3">{{ $attachment->notice?->title }}</td><td class="px-4 py-3"><a href="{{ $fileUrl }}" class="font-semibold text-cyan-700" target="_blank" rel="noopener">{{ $attachment->file_name ?: $attachment->file_url }}</a><div class="text-xs text-slate-500">{{ $attachment->file_url }}</div></td><td class="px-4 py-3">{{ $attachment->file_type }}</td><td class="px-4 py-3">{{ $attachment->file_size_kb }} KB</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('notices.attachments.destroy', $attachment) }}" onsubmit="return confirm('Delete attachment?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No attachments.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $attachments->links() }}</div>
    </div>
</x-app-layout>

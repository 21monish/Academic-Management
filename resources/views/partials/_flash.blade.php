@if(session('status'))
    <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
        {{ session('status') }}
    </div>
@endif

@if(session('success'))
    <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
        <div class="font-semibold">Please fix the following:</div>
        <ul class="mt-2 list-disc space-y-1 ps-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

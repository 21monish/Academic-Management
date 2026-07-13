<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Staff</h2>
            <a href="{{ route('staff.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md text-sm">Back</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
            <form method="POST" action="{{ route('staff.update', $staff) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('staff._form', compact('staff', 'teaching', 'nonTeaching'))

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


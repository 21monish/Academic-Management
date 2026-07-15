@php
    /** @var \App\Models\Student|null $student */
    $student = $student ?? null;
    $isEdit = (bool) $student;

    $pageTitle = $isEdit ? 'Edit Student' : 'Add Student';
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">{{ $pageTitle }}</h2>
        @if($isEdit)
            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">ID: {{ $student->student_id }}</span>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('students.update', $student) : route('students.store') }}" enctype="multipart/form-data" class="mt-6" data-student-form>
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Personal Information --}}
        <div class="text-gray-800 font-semibold">Personal Information</div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" value="First Name" />
                <x-text-input id="first_name" name="first_name" class="block mt-1 w-full" :value="old('first_name', $student?->first_name)" required />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" value="Last Name" />
                <x-text-input id="last_name" name="last_name" class="block mt-1 w-full" :value="old('last_name', $student?->last_name)" required />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="gender" value="Gender" />
                <select id="gender" name="gender" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Gender (optional)</option>
                    @foreach (['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $student?->gender) === $g)>{{ $g }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="dob" value="Date of Birth" />
                <x-text-input id="dob" name="dob" type="date" class="block mt-1 w-full" :value="old('dob', $student?->dob?->format('Y-m-d'))" :required="! $isEdit" />
                <x-input-error :messages="$errors->get('dob')" class="mt-2" />
                @unless($isEdit)
                    <p class="mt-1 text-xs text-gray-500">Student login password will be this DOB in ddmmyyyy format.</p>
                @endunless
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="photo" value="Photo" />

            {{-- Show current photo when editing --}}
            @if($isEdit && $student?->photo_url)
                @php($photoSrc = \Illuminate\Support\Str::startsWith($student->photo_url, ['http://', 'https://', '/']) ? $student->photo_url : asset($student->photo_url))
                <div class="mt-2 mb-3 flex items-center gap-3">
                    <img src="{{ $photoSrc }}" alt="Current photo" class="h-16 w-16 rounded-full border border-gray-200 object-cover">
                    <span class="text-xs text-gray-500">Current photo - upload a new one to replace it</span>
                </div>
            @endif

            <input
                id="photo"
                name="photo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="mt-1 block w-full cursor-pointer text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
            >
            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">
                JPG, PNG or WEBP - max 2 MB. File is saved to <code>uploads/photos/</code> and the path is stored in the database.
            </p>
        </div>

        {{-- Contact Details --}}
        <div class="mt-6 text-gray-800 font-semibold">Contact Details</div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $student?->email)" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="Mobile" />
                <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $student?->phone)" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="10 digit mobile number" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="address" value="Current Address" />
            <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $student?->address) }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        {{-- Guardian Details --}}
        <div class="mt-6 text-gray-800 font-semibold">Guardian Details</div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="guardian_name" value="Guardian Name" />
                <x-text-input id="guardian_name" name="guardian_name" class="block mt-1 w-full" :value="old('guardian_name', $student?->guardian_name)" />
                <x-input-error :messages="$errors->get('guardian_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_phone" value="Guardian Mobile" />
                <x-text-input id="guardian_phone" name="guardian_phone" class="block mt-1 w-full" :value="old('guardian_phone', $student?->guardian_phone)" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="10 digit guardian mobile" />
                <x-input-error :messages="$errors->get('guardian_phone')" class="mt-2" />
            </div>
        </div>

        {{-- Academic Details --}}
        <div class="mt-6 text-gray-800 font-semibold">Academic Details</div>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="enrollment_no" value="Enrollment No" />
                <x-text-input id="enrollment_no" name="enrollment_no" class="block mt-1 w-full" :value="old('enrollment_no', $student?->enrollment_no)" :readonly="! $isEdit" placeholder="Auto generated on create" />
                <x-input-error :messages="$errors->get('enrollment_no')" class="mt-2" />
                @unless($isEdit)
                    <p class="mt-1 text-xs text-gray-500">Format: year + college code + department code + programme code + serial, e.g. 241043107001.</p>
                @endunless
            </div>

            <div>
                <x-input-label for="admission_date" value="Admission Date" />
                <x-text-input id="admission_date" name="admission_date" type="date" class="block mt-1 w-full" :value="old('admission_date', $student?->admission_date?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('admission_date')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="student_type" value="Student Type" />
                <select id="student_type" name="student_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach(['Regular','D2D','C2D'] as $type)
                        <option value="{{ $type }}" @selected(old('student_type', $student?->student_type ?? 'Regular') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('student_type')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">D2D and C2D students are lateral entry and start from Semester 3 or higher.</p>
            </div>

            <div>
                <x-input-label for="admission_type" value="Admission Type" />
                <select id="admission_type" name="admission_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Admission Type (optional)</option>
                    @foreach(['Direct','ACPC','Management'] as $t)
                        <option value="{{ $t }}" @selected(old('admission_type', $student?->admission_type) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('admission_type')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="college_id" value="College" />
                <select id="college_id" name="college_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Select College</option>
                    @foreach ($colleges as $college)
                        <option value="{{ $college->college_id }}" @selected((string) old('college_id', $student?->college_id) === (string) $college->college_id)>{{ $college->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="programme_id" value="Programme" />
            <select id="programme_id" name="programme_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="">Select Programme</option>
                @foreach ($programmes as $programme)
                    <option value="{{ $programme->programme_id }}" @selected((string) old('programme_id', $student?->programme_id) === (string) $programme->programme_id)>{{ $programme->name ?? $programme->programme_name ?? $programme->programme_id }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('programme_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="category_id" value="Category" />
            <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Select Category (optional)</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->category_id }}" @selected((string) old('category_id', $student?->category_id) === (string) $category->category_id)>{{ $category->name ?? $category->category_name ?? $category->category_id }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
        </div>

        <div class="mt-6">
            @if ($student)
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', (bool) $student->is_active))>
                    <span class="ms-2 text-sm text-gray-700">Active</span>
                </label>
            @else
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', true))>
                    <span class="ms-2 text-sm text-gray-700">Active</span>
                </label>
            @endif
            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
        </div>

        <div class="mt-8 flex gap-3">
            <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Back</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm" data-student-submit>
                {{ $isEdit ? 'Update Student' : 'Create Student' }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-student-form]');
        const submit = form?.querySelector('[data-student-submit]');

        if (! form || ! submit) {
            return;
        }

        form.addEventListener('submit', () => {
            submit.disabled = true;
            submit.textContent = 'Saving...';
        });
    });
</script>


<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Student Profile</h2>
            <div class="flex gap-2">
                <a href="{{ route('students.edit', $student) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Edit</a>
                <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @php($studentType = $student->student_type ?: (in_array($student->admission_type, ['D2D', 'C2D'], true) ? $student->admission_type : 'Regular'))
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    @if($student->photo_url)
                        @php($photoSrc = \Illuminate\Support\Str::startsWith($student->photo_url, ['http://', 'https://', '/']) ? $student->photo_url : asset($student->photo_url))
                        <img src="{{ $photoSrc }}" alt="Photo" class="h-16 w-16 rounded-full object-cover" />
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">-</div>
                    @endif
                    <div>
                        <div class="text-2xl font-semibold text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                        <div class="text-sm text-gray-500">Enrollment No: <span class="font-medium text-gray-700">{{ $student->enrollment_no }}</span></div>
                        <div class="text-sm text-gray-500">Status: <span class="font-medium text-gray-700">{{ $student->is_active ? 'Active' : 'Inactive' }}</span></div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-600">Gender: <span class="font-medium">{{ $student->gender ?? '-' }}</span></div>
                    <div class="text-sm text-gray-600">DOB: <span class="font-medium">{{ $student->dob?->format('d M Y') ?? '-' }}</span></div>
                    <div class="text-sm text-gray-600">Student Type: <span class="font-medium">{{ $studentType }}</span></div>
                    <div class="text-sm text-gray-600">Admission Type: <span class="font-medium">{{ $student->admission_type ?? '-' }}</span></div>
                </div>
            </div>

            <hr class="my-6" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm font-semibold text-gray-900">College & Programme</div>
                    <div class="mt-2 text-sm text-gray-600">College: <span class="font-medium">{{ $student->college?->name ?? '-' }}</span></div>
                    <div class="mt-2 text-sm text-gray-600">Programme: <span class="font-medium">{{ $student->programme?->name ?? $student->programme?->programme_name ?? '-' }}</span></div>
                    <div class="mt-2 text-sm text-gray-600">Category: <span class="font-medium">{{ $student->category?->name ?? $student->category?->category_name ?? '-' }}</span></div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-900">Contact</div>
                    <div class="mt-2 text-sm text-gray-600">Email: <span class="font-medium">{{ $student->email ?? '-' }}</span></div>
                    <div class="mt-2 text-sm text-gray-600">Mobile: <span class="font-medium">{{ $student->phone ?? '-' }}</span></div>
                </div>
            </div>

            <div class="mt-6">
                <div class="text-sm font-semibold text-gray-900">Address</div>
                <div class="mt-2 text-sm text-gray-600">{{ $student->address ?? '-' }}</div>
            </div>

            <div class="mt-6">
                <div class="text-sm font-semibold text-gray-900">Guardian</div>
                <div class="mt-2 text-sm text-gray-600">Name: <span class="font-medium">{{ $student->guardian_name ?? '-' }}</span></div>
                <div class="mt-2 text-sm text-gray-600">Mobile: <span class="font-medium">{{ $student->guardian_phone ?? '-' }}</span></div>
            </div>

            <div class="mt-6 flex gap-3">
                @if($student->is_active)
                    <form action="{{ route('students.deactivate', $student) }}" method="POST" onsubmit="return confirm('Deactivate this student?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md text-sm">Deactivate</button>
                    </form>
                @else
                    <form action="{{ route('students.activate', $student) }}" method="POST" onsubmit="return confirm('Activate this student?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-green-100 text-green-800 rounded-md text-sm">Activate</button>
                    </form>
                @endif

                <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('Delete this student?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-100 text-red-800 rounded-md text-sm">Delete</button>
                </form>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Student Enrollments</h3>
                    <span class="text-xs text-gray-500">{{ $student->enrollments->count() }} record(s)</span>
                </div>

                <div class="mt-2 text-sm text-gray-600">
                    Linked user account: <span class="font-medium">{{ $student->userAccount?->username ?? 'Not linked' }}</span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Semester</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Academic Year</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Electives</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($student->enrollments as $enrollment)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">Sem {{ $enrollment->semester?->semester_no ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $enrollment->academicYear?->label ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $enrollment->status }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        @forelse($enrollment->electiveChoices as $choice)
                                            <div class="mb-1 flex items-center justify-between gap-2 rounded bg-gray-50 px-2 py-1">
                                                <span>{{ $choice->electiveGroup?->group_name }}: {{ $choice->subject?->code }}</span>
                                                <form method="POST" action="{{ route('students.electives.destroy', [$student, $enrollment, $choice]) }}" onsubmit="return confirm('Remove elective choice?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-xs font-semibold text-red-600">Remove</button>
                                                </form>
                                            </div>
                                        @empty
                                            <span class="text-gray-400">No choices</span>
                                        @endforelse
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('students.enrollments.destroy', [$student, $enrollment]) }}" onsubmit="return confirm('Delete enrollment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-semibold text-red-600">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-3 bg-gray-50">
                                        <form method="POST" action="{{ route('students.electives.store', [$student, $enrollment]) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                            @csrf
                                            <div class="md:col-span-5">
                                                <select name="group_id" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" required>
                                                    <option value="">Elective group</option>
                                                    @foreach($electiveGroups as $group)
                                                        <option value="{{ $group->group_id }}">{{ $group->group_name }} / Sem {{ $group->curriculum?->semester?->semester_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="md:col-span-5">
                                                <select name="subject_id" class="block w-full rounded-md border-gray-300 text-sm shadow-sm" required>
                                                    <option value="">Subject choice</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->subject_id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="md:col-span-2">
                                                <button class="w-full rounded-md bg-cyan-700 px-3 py-2 text-sm font-semibold text-white">Save Choice</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">No enrollments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-900">Add Enrollment</h3>
                @if(in_array($studentType, ['D2D', 'C2D'], true))
                    <div class="mt-3 rounded-md border border-cyan-100 bg-cyan-50 px-3 py-2 text-xs font-medium text-cyan-800">
                        {{ $studentType }} student: enroll from Semester 3 or higher.
                    </div>
                @endif
                <form method="POST" action="{{ route('students.enrollments.store', $student) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="semester_id" value="Semester" />
                        <select id="semester_id" name="semester_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->semester_id }}" @disabled(in_array($studentType, ['D2D', 'C2D'], true) && (int) $semester->semester_no < 3)>
                                    Sem {{ $semester->semester_no }}{{ in_array($studentType, ['D2D', 'C2D'], true) && (int) $semester->semester_no < 3 ? ' - not for '.$studentType : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="academic_year_id" value="Academic Year" />
                        <select id="academic_year_id" name="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->academic_year_id }}">{{ $year->label }}{{ $year->is_current ? ' (Current)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="enrolled_on" value="Enrolled On" />
                        <x-text-input id="enrolled_on" name="enrolled_on" type="date" class="mt-1 block w-full" :value="date('Y-m-d')" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach(['Active','Detained','PassedOut','Withdrawn'] as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="w-full rounded-md bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Enroll Student</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


<x-app-layout>
    @php($canGenerateCertificates = hasPermission('certificate.generate'))

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900">Certificates</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="q" :value="request('q')" placeholder="Search enrollment or student name" class="flex-1" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Enrollment</th>
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Programme</th>
                        <th class="px-4 py-3 text-left">Current Semester</th>
                        <th class="px-4 py-3 text-left">Certificates</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php($currentEnrollment = $student->enrollments->sortByDesc(fn ($enrollment) => $enrollment->enrolled_on?->format('Ymd') ?? '00000000')->first())
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $student->enrollment_no }}</td>
                            <td class="px-4 py-3">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="px-4 py-3">{{ $student->programme?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $currentEnrollment?->semester ? 'Sem '.$currentEnrollment->semester->semester_no : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($canGenerateCertificates)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach (['bonafide' => 'Bonafide', 'leaving' => 'Leaving', 'fee' => 'Fee', 'transfer' => 'Transfer'] as $type => $label)
                                            <a href="{{ route('reports.certificates.print', [$student, $type]) }}" class="rounded-md bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700 ring-1 ring-cyan-100 hover:bg-cyan-100">
                                                {{ $label }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">No print permission</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $students->links() }}</div>
    </div>
</x-app-layout>

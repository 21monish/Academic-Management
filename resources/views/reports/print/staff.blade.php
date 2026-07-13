<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Staff Report</title>@include('reports._print_styles')</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @include('reports._print_header', ['title' => 'Staff Report', 'brandName' => $staff->college?->university?->name, 'logoUrl' => $staff->college?->university?->logo_url, 'subtitle' => $staff->college?->name, 'meta' => now()->format('d M Y')])
    <section class="grid">
        <p><strong>Employee Code:</strong> {{ $staff->employee_code }}</p>
        <p><strong>Name:</strong> {{ $staff->first_name }} {{ $staff->last_name }}</p>
        <p><strong>Department:</strong> {{ $staff->department?->name ?? '-' }}</p>
        <p><strong>Type:</strong> {{ $staff->staff_type }} / {{ $staff->employment_type }}</p>
        <p><strong>Email:</strong> {{ $staff->email }}</p>
        <p><strong>Phone:</strong> {{ $staff->phone ?? '-' }}</p>
        <p><strong>Join Date:</strong> {{ $staff->join_date ?? '-' }}</p>
        <p><strong>Status:</strong> {{ $staff->is_active ? 'Active' : 'Inactive' }}</p>
    </section>
    <h2>Subject Assignments</h2>
    <table><thead><tr><th>Subject</th><th>Lecture Type</th></tr></thead><tbody>@forelse($staff->subjectAssignments as $assignment)<tr><td>{{ $assignment->subject?->code }} - {{ $assignment->subject?->name }}</td><td>{{ $assignment->lecture_type }}</td></tr>@empty<tr><td colspan="2">No assignments.</td></tr>@endforelse</tbody></table>
    <h2>Leave Balances</h2>
    <table><thead><tr><th>Leave Type</th><th>Available</th><th>Used</th><th>Remaining</th></tr></thead><tbody>@forelse($staff->leaveBalances as $balance)<tr><td>{{ $balance->leaveType?->code }}</td><td>{{ $balance->total_available }}</td><td>{{ $balance->used }}</td><td>{{ $balance->remaining }}</td></tr>@empty<tr><td colspan="4">No leave balances.</td></tr>@endforelse</tbody></table>
</main>
</body>
</html>

<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Student Report</title>@include('reports._print_styles')</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @include('reports._print_header', ['title' => 'Student Report', 'brandName' => $student->college?->university?->name, 'logoUrl' => $student->college?->university?->logo_url, 'subtitle' => $student->college?->name, 'meta' => now()->format('d M Y')])
    <section class="grid">
        <p><strong>Enrollment:</strong> {{ $student->enrollment_no }}</p>
        <p><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Programme:</strong> {{ $student->programme?->name }}</p>
        <p><strong>Category:</strong> {{ $student->category?->name ?? '-' }}</p>
        <p><strong>Email:</strong> {{ $student->email ?? '-' }}</p>
        <p><strong>Phone:</strong> {{ $student->phone ?? '-' }}</p>
        <p><strong>Admission:</strong> {{ $student->admission_date?->format('d M Y') ?? '-' }}</p>
        <p><strong>Status:</strong> {{ $student->is_active ? 'Active' : 'Inactive' }}</p>
    </section>
    <h2>Enrollments</h2>
    <table><thead><tr><th>Academic Year</th><th>Semester</th><th>Status</th></tr></thead><tbody>@forelse($student->enrollments as $enrollment)<tr><td>{{ $enrollment->academicYear?->label }}</td><td>Sem {{ $enrollment->semester?->semester_no }}</td><td>{{ $enrollment->status }}</td></tr>@empty<tr><td colspan="3">No enrollments.</td></tr>@endforelse</tbody></table>
    <h2>Fee Summary</h2>
    <table><thead><tr><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>@forelse($student->feeLedgers as $ledger)<tr><td>{{ number_format($ledger->net_payable, 2) }}</td><td>{{ number_format($ledger->amount_paid, 2) }}</td><td>{{ number_format($ledger->balance_due, 2) }}</td><td>{{ $ledger->payment_status }}</td></tr>@empty<tr><td colspan="4">No fee ledgers.</td></tr>@endforelse</tbody></table>
</main>
</body>
</html>

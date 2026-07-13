<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Result Card</title>@include('reports._print_styles')</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @include('reports._print_header', ['title' => 'Result Card', 'brandName' => $student->college?->university?->name, 'logoUrl' => $student->college?->university?->logo_url, 'subtitle' => $student->college?->name, 'meta' => now()->format('d M Y')])
    <section class="grid">
        <p><strong>Enrollment:</strong> {{ $student->enrollment_no }}</p>
        <p><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Programme:</strong> {{ $student->programme?->name }}</p>
    </section>
    <h2>Marks</h2>
    <table><thead><tr><th>Exam</th><th>Subject</th><th>Theory</th><th>Practical</th><th>Internal</th><th>Total</th><th>Grade</th><th>Status</th></tr></thead><tbody>@forelse($results as $result)<tr><td>{{ $result->examSubject?->exam?->exam_name }}</td><td>{{ $result->examSubject?->subject?->code }} - {{ $result->examSubject?->subject?->name }}</td><td>{{ $result->theory_marks }}</td><td>{{ $result->practical_marks }}</td><td>{{ $result->internal_marks }}</td><td>{{ $result->total_marks }}</td><td>{{ $result->grade }}</td><td>{{ $result->result_status }}</td></tr>@empty<tr><td colspan="8">No published results.</td></tr>@endforelse</tbody></table>
</main>
</body>
</html>

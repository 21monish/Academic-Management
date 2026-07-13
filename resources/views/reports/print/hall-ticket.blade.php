<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Hall Ticket</title>@include('reports._print_styles')</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @php($studentPhotoUrl = $ticket->student?->photo_url ? (\Illuminate\Support\Str::startsWith($ticket->student->photo_url, ['http://', 'https://', '/']) ? $ticket->student->photo_url : asset($ticket->student->photo_url)) : null)
    @include('reports._print_header', ['title' => 'Hall Ticket', 'brandName' => $ticket->student?->college?->university?->name, 'logoUrl' => $ticket->student?->college?->university?->logo_url, 'subtitle' => $ticket->student?->college?->name, 'meta' => $ticket->hall_ticket_no])
    <section class="ticket-details">
        <div class="grid">
            <p><strong>Student:</strong> {{ $ticket->student?->enrollment_no }} - {{ $ticket->student?->first_name }} {{ $ticket->student?->last_name }}</p>
            <p><strong>Programme:</strong> {{ $ticket->student?->programme?->name }}</p>
            <p><strong>Semester:</strong> Sem {{ $ticket->enrollment?->semester?->semester_no }}</p>
            <p><strong>Exam:</strong> {{ $ticket->config?->exam?->exam_name }}</p>
            <p><strong>Type:</strong> {{ $ticket->exam_type }}</p>
            <p><strong>Status:</strong> {{ $ticket->status }} / {{ $ticket->is_eligible ? 'Eligible' : 'Not eligible' }}</p>
        </div>
        <div>
            @if($studentPhotoUrl)
                <img src="{{ $studentPhotoUrl }}" alt="Student photo" class="ticket-photo">
            @else
                <div class="ticket-photo-placeholder">Student<br>Photo</div>
            @endif
        </div>
    </section>
    @if($ticket->ineligibility_reason)<p><strong>Reason:</strong> {{ $ticket->ineligibility_reason }}</p>@endif
    <h2>Subjects</h2>
    <table><thead><tr><th>Subject</th><th>Type</th><th>Theory Date</th><th>Time</th><th>Room</th><th>Seat</th></tr></thead><tbody>@forelse($ticket->subjects as $subject)<tr><td>{{ $subject->subject?->code }} - {{ $subject->subject?->name }}</td><td>{{ $subject->subject_type }}</td><td>{{ $subject->theory_exam_date }}</td><td>{{ $subject->theory_exam_time }}</td><td>{{ $subject->theory_room_no }}</td><td>{{ $subject->theory_seat_no }}</td></tr>@empty<tr><td colspan="6">No subjects.</td></tr>@endforelse</tbody></table>
</main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student ID Cards</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e5e7eb; color: #0f172a; font-family: Arial, Helvetica, sans-serif; }
        .print-actions { position: sticky; top: 0; z-index: 10; display: flex; justify-content: space-between; gap: 12px; padding: 12px 18px; background: #ffffff; border-bottom: 1px solid #cbd5e1; }
        .print-actions button { border: 0; border-radius: 6px; background: #0f172a; color: #ffffff; padding: 9px 14px; font-weight: 700; cursor: pointer; }
        .print-actions p { margin: 0; font-size: 12px; color: #64748b; font-weight: 700; }
        .sheet { display: grid; grid-template-columns: repeat(2, 86mm); gap: 8mm; justify-content: center; padding: 12mm; }
        .card { width: 86mm; height: 54mm; overflow: hidden; border-radius: 4mm; background: #ffffff; border: 1px solid #cbd5e1; break-inside: avoid; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12); }
        .band { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 13mm; padding: 3mm 4mm; background: #0f766e; color: #ffffff; }
        .college { min-width: 0; }
        .college strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px; text-transform: uppercase; }
        .college span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px; font-size: 8px; opacity: 0.86; }
        .badge { flex: 0 0 auto; border-radius: 999px; background: #ffffff; color: #0f766e; padding: 3px 7px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
        .body { display: grid; grid-template-columns: 23mm 1fr; gap: 4mm; padding: 4mm; }
        .photo { width: 23mm; height: 28mm; overflow: hidden; border-radius: 3mm; border: 1px solid #cbd5e1; background: #f1f5f9; }
        .photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .initials { display: grid; width: 100%; height: 100%; place-items: center; color: #0f766e; font-size: 18px; font-weight: 900; }
        .name { margin: 0; font-size: 14px; line-height: 1.15; font-weight: 900; }
        .enrollment { margin: 2px 0 0; color: #0f766e; font-size: 10px; font-weight: 900; }
        .meta { margin-top: 4mm; display: grid; gap: 1.4mm; font-size: 8.5px; }
        .meta div { display: grid; grid-template-columns: 18mm 1fr; gap: 2mm; }
        .meta span { color: #64748b; font-weight: 700; }
        .meta strong { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 800; }
        .footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 0 4mm 3mm; color: #64748b; font-size: 7.5px; font-weight: 700; }
        .empty { grid-column: 1 / -1; border: 1px dashed #94a3b8; border-radius: 8px; background: #ffffff; padding: 24px; text-align: center; font-weight: 800; color: #64748b; }
        @media print {
            body { background: #ffffff; }
            .print-actions { display: none; }
            .sheet { padding: 0; gap: 5mm; }
            .card { box-shadow: none; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()">Print / Save PDF</button>
        <p>Generated {{ $generatedAt->format('d M Y, h:i A') }}</p>
    </div>

    <main class="sheet">
        @forelse($students as $student)
            @php
                $department = $student->programme?->department;
                $activeEnrollment = $student->enrollments->firstWhere('status', 'Active') ?? $student->enrollments->first();
                $photoUrl = $student->photo_url ? asset($student->photo_url) : null;
                $initials = strtoupper(substr((string) $student->first_name, 0, 1).substr((string) $student->last_name, 0, 1));
            @endphp
            <section class="card">
                <div class="band">
                    <div class="college">
                        <strong>{{ $student->college?->name ?? 'College' }}</strong>
                        <span>{{ $student->college?->university?->name ?? 'University' }}</span>
                    </div>
                    <span class="badge">Student</span>
                </div>

                <div class="body">
                    <div class="photo">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $student->first_name }} {{ $student->last_name }}">
                        @else
                            <div class="initials">{{ $initials ?: 'ID' }}</div>
                        @endif
                    </div>

                    <div>
                        <h2 class="name">{{ $student->first_name }} {{ $student->last_name }}</h2>
                        <p class="enrollment">{{ $student->enrollment_no }}</p>

                        <div class="meta">
                            <div><span>Programme</span><strong>{{ $student->programme?->name ?? '-' }}</strong></div>
                            <div><span>Department</span><strong>{{ $department?->name ?? '-' }}</strong></div>
                            <div><span>Semester</span><strong>{{ $activeEnrollment?->semester ? 'Sem '.$activeEnrollment->semester->semester_no : '-' }}</strong></div>
                            <div><span>Phone</span><strong>{{ $student->phone ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <span>Valid while student status is active</span>
                    <span>{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </section>
        @empty
            <div class="empty">No active students found for ID card generation.</div>
        @endforelse
    </main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $certificateTitle }}</title>
    @include('reports._print_styles')
    <style>
        .certificate-body { font-size: 15px; line-height: 1.8; margin-top: 28px; text-align: justify; }
        .certificate-title { font-size: 20px; font-weight: 800; letter-spacing: 0; margin: 20px 0; text-align: center; text-transform: uppercase; }
        .signature-row { display: flex; justify-content: space-between; gap: 24px; margin-top: 64px; }
        .signature-box { border-top: 1px solid #0f172a; font-size: 12px; font-weight: 700; padding-top: 8px; text-align: center; width: 180px; }
        .summary-box { border: 1px solid #cbd5e1; border-radius: 8px; margin-top: 18px; padding: 14px; }
    </style>
</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @include('reports._print_header', [
        'title' => $certificateTitle,
        'brandName' => $student->college?->university?->name,
        'logoUrl' => $student->college?->university?->logo_url,
        'subtitle' => $student->college?->name,
        'meta' => now()->format('d M Y'),
    ])

    @php
        $studentName = trim($student->first_name.' '.$student->last_name);
        $programmeName = $student->programme?->name ?? 'the programme';
        $departmentName = $student->programme?->department?->name;
        $semesterLabel = $latestEnrollment?->semester ? 'Semester '.$latestEnrollment->semester->semester_no : 'the current semester';
        $academicYear = $latestEnrollment?->academicYear?->label;
    @endphp

    <div class="certificate-title">{{ $certificateTitle }}</div>

    <section class="grid">
        <p><strong>Enrollment:</strong> {{ $student->enrollment_no }}</p>
        <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
        <p><strong>Student:</strong> {{ $studentName }}</p>
        <p><strong>Programme:</strong> {{ $programmeName }}</p>
        <p><strong>Department:</strong> {{ $departmentName ?? '-' }}</p>
        <p><strong>Semester:</strong> {{ $semesterLabel }}</p>
    </section>

    <section class="certificate-body">
        @if($type === 'bonafide')
            <p>This is to certify that <strong>{{ $studentName }}</strong>, enrollment number <strong>{{ $student->enrollment_no }}</strong>, is a bonafide student of <strong>{{ $student->college?->name }}</strong> studying in <strong>{{ $programmeName }}</strong>{{ $departmentName ? ' under '.$departmentName.' department' : '' }}{{ $academicYear ? ' for academic year '.$academicYear : '' }}.</p>
            <p>This certificate is issued on student request for official use.</p>
        @elseif($type === 'leaving')
            <p>This is to certify that <strong>{{ $studentName }}</strong>, enrollment number <strong>{{ $student->enrollment_no }}</strong>, was enrolled in <strong>{{ $programmeName }}</strong> at <strong>{{ $student->college?->name }}</strong>.</p>
            <p>As per the records available with the institute, the student status is <strong>{{ $student->is_active ? 'Active' : 'Inactive' }}</strong>. This leaving certificate is issued subject to completion of institutional formalities and dues clearance.</p>
        @elseif($type === 'fee')
            <p>This is to certify that <strong>{{ $studentName }}</strong>, enrollment number <strong>{{ $student->enrollment_no }}</strong>, has the following fee record in the institute system.</p>
            <div class="summary-box">
                <table>
                    <thead><tr><th>Total</th><th>Concession</th><th>Scholarship</th><th>Net Payable</th><th>Paid</th><th>Balance</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($feeSummary['total_amount'], 2) }}</td>
                            <td>{{ number_format($feeSummary['concession_amount'], 2) }}</td>
                            <td>{{ number_format($feeSummary['scholarship_amount'], 2) }}</td>
                            <td>{{ number_format($feeSummary['net_payable'], 2) }}</td>
                            <td>{{ number_format($feeSummary['amount_paid'], 2) }}</td>
                            <td>{{ number_format($feeSummary['balance_due'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p>This is to certify that <strong>{{ $studentName }}</strong>, enrollment number <strong>{{ $student->enrollment_no }}</strong>, is recorded as a student of <strong>{{ $student->college?->name }}</strong> in <strong>{{ $programmeName }}</strong>.</p>
            <p>This transfer certificate is issued on request and is valid subject to verification of academic records, identity, and dues clearance by the institute office.</p>
        @endif
    </section>

    <div class="signature-row">
        <div class="signature-box">Prepared By</div>
        <div class="signature-box">Office Seal</div>
        <div class="signature-box">Principal / Registrar</div>
    </div>
</main>
</body>
</html>

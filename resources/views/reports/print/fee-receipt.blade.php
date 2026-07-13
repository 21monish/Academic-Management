<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Fee Receipt</title>@include('reports._print_styles')</head>
<body>
<div class="print-actions"><button onclick="window.print()">Print / Save PDF</button></div>
<main class="sheet">
    @include('reports._print_header', ['title' => 'Fee Receipt', 'brandName' => $payment->student?->college?->university?->name, 'logoUrl' => $payment->student?->college?->university?->logo_url, 'subtitle' => $payment->student?->college?->name, 'meta' => $payment->receipt_no])
    <section class="grid">
        <p><strong>Student:</strong> {{ $payment->student?->enrollment_no }} - {{ $payment->student?->first_name }} {{ $payment->student?->last_name }}</p>
        <p><strong>Programme:</strong> {{ $payment->student?->programme?->name }}</p>
        <p><strong>Fee:</strong> {{ $payment->ledger?->feeStructure?->feeCategory?->name }}</p>
        <p><strong>Date:</strong> {{ $payment->payment_date }}</p>
        <p><strong>Mode:</strong> {{ $payment->payment_mode }}</p>
        <p><strong>Status:</strong> {{ $payment->payment_status }}</p>
        <p><strong>Transaction:</strong> {{ $payment->transaction_ref ?? '-' }}</p>
        <p><strong>Collected By:</strong> {{ $payment->collectedBy?->name ?? '-' }}</p>
    </section>
    <h2>Amount Paid</h2>
    <table><tbody><tr><th>Amount</th><td>{{ number_format($payment->amount_paid, 2) }}</td></tr><tr><th>Remarks</th><td>{{ $payment->remarks ?? '-' }}</td></tr></tbody></table>
</main>
</body>
</html>

<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Category;
use App\Models\College;
use App\Models\FeeCategory;
use App\Models\FeeConcession;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Programme;
use App\Models\Scholarship;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use App\Models\University;
use App\Services\AccessScopeService;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeModuleController extends Controller
{
    public function __construct(
        protected AccessScopeService $accessScope,
        protected ApprovalWorkflowService $approvalWorkflow
    )
    {
    }

    public function categories(): View
    {
        return view('fees.categories', array_merge($this->lookups(), [
            'categories' => FeeCategory::query()
                ->when(request('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%")->orWhere('fee_type', 'like', "%{$q}%"))
                ->latest('fee_category_id')
                ->paginate(15)
                ->withQueryString(),
        ]));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        FeeCategory::create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'fee_type' => ['required', 'in:Academic,Exam,Hostel,Transport,Misc'],
            'is_refundable' => ['boolean'],
            'is_mandatory' => ['boolean'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('status', 'Fee category saved.');
    }

    public function destroyCategory(FeeCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('status', 'Fee category deleted.');
    }

    public function structures(): View
    {
        return view('fees.structures', array_merge($this->lookups(), [
            'structures' => $this->accessScope->applyToFeeStructures(
                FeeStructure::with(['college', 'programme', 'academicYear', 'semester', 'feeCategory', 'studentCategory']),
                request()->user()
            )
                ->when(request('q'), fn ($query, $q) => $query->whereHas('feeCategory', fn ($inner) => $inner->where('name', 'like', "%{$q}%")))
                ->latest('fee_structure_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'fee_category_id' => ['required', 'exists:fee_categories,fee_category_id'],
            'student_category_id' => ['nullable', 'exists:categories,category_id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'late_fine_per_day' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        FeeStructure::create($data);

        return back()->with('status', 'Fee structure saved.');
    }

    public function destroyStructure(FeeStructure $structure): RedirectResponse
    {
        abort_unless($this->accessScope->applyToFeeStructures(FeeStructure::whereKey($structure->fee_structure_id), request()->user())->exists(), 403);

        $structure->delete();

        return back()->with('status', 'Fee structure deleted.');
    }

    public function ledgers(): View
    {
        return view('fees.ledgers', array_merge($this->lookups(), [
            'ledgers' => $this->accessScope->applyToFeeLedgers(
                StudentFeeLedger::with(['student', 'feeStructure.feeCategory', 'academicYear', 'semester']),
                request()->user()
            )
                ->when(request('q'), fn ($query, $q) => $query->whereHas('student', fn ($inner) => $inner->where('enrollment_no', 'like', "%{$q}%")->orWhere('first_name', 'like', "%{$q}%")))
                ->latest('ledger_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeLedger(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'fee_structure_id' => ['required', 'exists:fee_structures,fee_structure_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'concession_amount' => ['nullable', 'numeric', 'min:0'],
            'scholarship_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToFeeStructures(FeeStructure::whereKey($data['fee_structure_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        $data = $this->ledgerTotals($data);
        StudentFeeLedger::create($data);

        return back()->with('status', 'Student fee ledger saved.');
    }

    public function destroyLedger(StudentFeeLedger $ledger): RedirectResponse
    {
        abort_unless($this->accessScope->applyToFeeLedgers(StudentFeeLedger::whereKey($ledger->ledger_id), request()->user())->exists(), 403);

        $ledger->delete();

        return back()->with('status', 'Fee ledger deleted.');
    }

    public function collections(): View
    {
        return view('fees.collections', array_merge($this->lookups(), [
            'paymentQr' => $this->paymentQrData(request()),
            'payments' => $this->accessScope->applyToFeePayments(
                FeePayment::with(['ledger.feeStructure.feeCategory', 'student', 'collectedBy']),
                request()->user()
            )
                ->when(request('q'), fn ($query, $q) => $query->where('receipt_no', 'like', "%{$q}%")->orWhereHas('student', fn ($inner) => $inner->where('enrollment_no', 'like', "%{$q}%")))
                ->latest('payment_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeCollection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ledger_id' => ['required', 'exists:student_fee_ledgers,ledger_id'],
            'student_id' => ['required', 'exists:students,student_id'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'date'],
            'payment_mode' => ['required', 'in:Cash,Online,Cheque,DD,NEFT'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'receipt_no' => ['nullable', 'string', 'max:50', 'unique:fee_payments,receipt_no'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'cheque_no' => ['nullable', 'string', 'max:30'],
            'cheque_date' => ['nullable', 'date'],
            'payment_status' => ['required', 'in:Pending,Cleared,Bounced,Cancelled'],
            'remarks' => ['nullable', 'string'],
        ]);

        abort_unless($this->accessScope->applyToFeeLedgers(StudentFeeLedger::whereKey($data['ledger_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);

        $data['receipt_no'] = $data['receipt_no'] ?: $this->receiptNo();
        if ($data['payment_mode'] === 'Online' && blank($data['transaction_ref'])) {
            return back()
                ->withErrors(['transaction_ref' => 'Transaction reference or UTR is required for online / QR payments.'])
                ->withInput();
        }

        $data['collected_by'] = auth()->id();
        FeePayment::create($data);
        $this->syncLedger(StudentFeeLedger::findOrFail($data['ledger_id']));

        return back()->with('status', 'Fee collection saved.');
    }

    public function destroyCollection(FeePayment $payment): RedirectResponse
    {
        abort_unless($this->accessScope->applyToFeePayments(FeePayment::whereKey($payment->payment_id), request()->user())->exists(), 403);

        $ledger = $payment->ledger;
        $payment->delete();

        if ($ledger) {
            $this->syncLedger($ledger);
        }

        return back()->with('status', 'Payment deleted.');
    }

    public function receipts(): View
    {
        return view('fees.receipts', [
            'receipts' => $this->accessScope->applyToFeePayments(
                FeePayment::with(['student', 'ledger.feeStructure.feeCategory', 'collectedBy']),
                request()->user()
            )
                ->when(request('q'), fn ($query, $q) => $query->where('receipt_no', 'like', "%{$q}%")->orWhereHas('student', fn ($inner) => $inner->where('enrollment_no', 'like', "%{$q}%")))
                ->latest('payment_id')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function concessions(): View
    {
        return view('fees.concessions', array_merge($this->lookups(), [
            'concessions' => FeeConcession::with(['student', 'ledger', 'approvedBy'])
                ->whereHas('student', fn ($student) => $this->accessScope->applyToStudents($student, request()->user()))
                ->when(request('q'), fn ($query, $q) => $query->where('concession_type', 'like', "%{$q}%")->orWhereHas('student', fn ($inner) => $inner->where('enrollment_no', 'like', "%{$q}%")))
                ->latest('concession_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeConcession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'ledger_id' => ['required', 'exists:student_fee_ledgers,ledger_id'],
            'concession_type' => ['required', 'in:Merit,Sports,Staff Ward,Physically Challenged'],
            'concession_amount' => ['nullable', 'numeric', 'min:0'],
            'concession_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reason' => ['nullable', 'string'],
            'approved_on' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToFeeLedgers(StudentFeeLedger::whereKey($data['ledger_id']), $request->user())->exists(), 403);

        if ($this->approvalWorkflow->requiresApproval($request->user())) {
            $data['approved_by'] = null;
            $data['approved_on'] = null;
            $data['is_active'] = false;

            $concession = FeeConcession::create($data);
            $this->approvalWorkflow->request(
                $request->user(),
                ApprovalWorkflowService::FEE_CONCESSION,
                $concession,
                [
                    'student_id' => $concession->student_id,
                    'amount' => $concession->concession_amount,
                    'percent' => $concession->concession_pct,
                ]
            );

            return back()->with('status', 'Fee concession request sent for approval.');
        }

        $data['approved_by'] = auth()->id();
        $data['approved_on'] = $data['approved_on'] ?? now()->toDateString();
        $data['is_active'] = true;

        FeeConcession::create($data);
        $this->syncLedger(StudentFeeLedger::findOrFail($data['ledger_id']));

        return back()->with('status', 'Fee concession approved and saved.');
    }

    public function destroyConcession(FeeConcession $concession): RedirectResponse
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($concession->student_id), request()->user())->exists(), 403);

        $ledger = $concession->ledger;
        $concession->delete();

        if ($ledger) {
            $this->syncLedger($ledger);
        }

        return back()->with('status', 'Fee concession deleted.');
    }

    public function scholarships(): View
    {
        return view('fees.scholarships', array_merge($this->lookups(), [
            'scholarships' => Scholarship::with(['student', 'academicYear'])
                ->whereHas('student', fn ($student) => $this->accessScope->applyToStudents($student, request()->user()))
                ->when(request('q'), fn ($query, $q) => $query->where('scheme_name', 'like', "%{$q}%")->orWhere('provider', 'like', "%{$q}%")->orWhereHas('student', fn ($inner) => $inner->where('enrollment_no', 'like', "%{$q}%")))
                ->latest('scholarship_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeScholarship(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'scheme_name' => ['required', 'string', 'max:200'],
            'provider' => ['nullable', 'string', 'max:200'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:Applied,Approved,Disbursed,Rejected'],
            'applied_on' => ['nullable', 'date'],
            'approved_on' => ['nullable', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);

        Scholarship::create($data);

        return back()->with('status', 'Scholarship saved.');
    }

    public function destroyScholarship(Scholarship $scholarship): RedirectResponse
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($scholarship->student_id), request()->user())->exists(), 403);

        $scholarship->delete();

        return back()->with('status', 'Scholarship deleted.');
    }

    public function reports(): View
    {
        $ledgers = $this->accessScope->applyToFeeLedgers(StudentFeeLedger::query(), request()->user());
        $payments = $this->accessScope->applyToFeePayments(FeePayment::query(), request()->user());

        return view('fees.reports', [
            'totalDemand' => (float) (clone $ledgers)->sum('net_payable'),
            'totalCollected' => (float) (clone $payments)->where('payment_status', 'Cleared')->sum('amount_paid'),
            'totalBalance' => (float) $this->accessScope->applyToFeeLedgers(StudentFeeLedger::query(), request()->user())->sum('balance_due'),
            'overdueCount' => $this->accessScope->applyToFeeLedgers(StudentFeeLedger::query(), request()->user())->where('payment_status', 'Overdue')->count(),
            'statusRows' => $this->accessScope->applyToFeeLedgers(StudentFeeLedger::selectRaw('payment_status, count(*) as ledgers, sum(balance_due) as balance'), request()->user())
                ->groupBy('payment_status')
                ->orderBy('payment_status')
                ->get(),
            'recentPayments' => $this->accessScope->applyToFeePayments(FeePayment::with('student'), request()->user())->latest('payment_id')->limit(10)->get(),
        ]);
    }

    private function lookups(): array
    {
        return [
            'students' => $this->accessScope->applyToStudents(Student::query(), request()->user())->orderBy('enrollment_no')->get(['student_id', 'enrollment_no', 'first_name', 'last_name']),
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'programmes' => $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']),
            'academicYears' => $this->accessScope->applyToAcademicYears(AcademicYear::query(), request()->user())->orderByDesc('is_current')->get(['academic_year_id', 'label']),
            'semesters' => $this->accessScope->applyToSemesters(Semester::query(), request()->user())->orderBy('semester_no')->get(['semester_id', 'semester_no', 'academic_year']),
            'feeCategories' => FeeCategory::orderBy('name')->get(['fee_category_id', 'name', 'fee_type']),
            'studentCategories' => Category::orderBy('name')->get(['category_id', 'name']),
            'structuresList' => $this->accessScope->applyToFeeStructures(FeeStructure::with('feeCategory'), request()->user())->latest('fee_structure_id')->get(),
            'ledgersList' => $this->accessScope->applyToFeeLedgers(StudentFeeLedger::with('student'), request()->user())->latest('ledger_id')->get(),
        ];
    }

    private function ledgerTotals(array $data, float $paid = 0): array
    {
        $data['concession_amount'] = $data['concession_amount'] ?? 0;
        $data['scholarship_amount'] = $data['scholarship_amount'] ?? 0;
        $data['net_payable'] = max(0, $data['total_amount'] - $data['concession_amount'] - $data['scholarship_amount']);
        $data['amount_paid'] = $paid;
        $data['balance_due'] = max(0, $data['net_payable'] - $paid);
        $data['payment_status'] = $data['balance_due'] <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid');
        $data['is_hall_ticket_cleared'] = $data['balance_due'] <= 0;

        return $data;
    }

    private function syncLedger(StudentFeeLedger $ledger): void
    {
        $paid = (float) $ledger->payments()->where('payment_status', 'Cleared')->sum('amount_paid');
        $concessions = $ledger->concessions()
            ->where('is_active', true)
            ->get()
            ->sum(fn (FeeConcession $concession) => (float) ($concession->concession_amount ?: ($ledger->total_amount * ($concession->concession_pct ?? 0) / 100)));

        $ledger->update($this->ledgerTotals([
            'total_amount' => (float) $ledger->total_amount,
            'concession_amount' => $concessions,
            'scholarship_amount' => (float) $ledger->scholarship_amount,
        ], $paid));
    }

    private function receiptNo(): string
    {
        return 'RCPT-' . now()->format('Ymd') . '-' . str_pad((string) ((FeePayment::max('payment_id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    private function paymentQrData(Request $request): ?array
    {
        $ledgerId = $request->integer('qr_ledger_id');

        if (! $ledgerId) {
            return null;
        }

        $ledger = $this->accessScope->applyToFeeLedgers(
            StudentFeeLedger::with(['student.college.university', 'feeStructure.feeCategory'])->whereKey($ledgerId),
            $request->user()
        )->first();

        if (! $ledger) {
            return null;
        }

        $university = $this->paymentUniversity($ledger, $request);
        $upiId = $university?->upi_id;
        $upiName = $university?->upi_name ?: $university?->name ?: config('app.name', 'GTU ITR');
        $notePrefix = $university?->upi_note_prefix ?: 'Fee Payment';

        if (blank($upiId)) {
            return [
                'ledger' => $ledger,
                'amount' => (float) $ledger->balance_due,
                'upi_id' => null,
                'upi_name' => $upiName,
                'university' => $university,
                'upi_uri' => null,
                'qr_url' => null,
            ];
        }

        $amount = max(0.01, (float) ($request->input('qr_amount') ?: $ledger->balance_due));
        $note = trim($notePrefix.' '.$ledger->student?->enrollment_no.' '.$ledger->feeStructure?->feeCategory?->name);
        $params = [
            'pa' => $upiId,
            'pn' => $upiName,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => $note,
        ];
        $upiUri = 'upi://pay?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return [
            'ledger' => $ledger,
            'amount' => $amount,
            'upi_id' => $upiId,
            'upi_name' => $upiName,
            'university' => $university,
            'upi_uri' => $upiUri,
            'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.rawurlencode($upiUri),
        ];
    }

    private function paymentUniversity(StudentFeeLedger $ledger, Request $request): ?University
    {
        return $ledger->student?->college?->university
            ?: ($request->user()?->university_id ? University::find($request->user()->university_id) : null);
    }
}

<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\College;
use App\Models\HolidayCalendar;
use App\Models\LeaveApplication;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveCancellation;
use App\Models\LeaveSubstitute;
use App\Models\LeaveType;
use App\Models\Staff;
use App\Models\Subject;
use App\Services\AccessScopeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveModuleController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function types(): View
    {
        return view('leave.types', [
            'types' => LeaveType::query()
                ->when(request('q'), fn ($query, $q) => $query->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"))
                ->orderBy('code')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        LeaveType::create($request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:leave_types,code'],
            'name' => ['required', 'string', 'max:100'],
            'applicable_to' => ['required', 'in:Teaching,NonTeaching,Both'],
            'max_days_per_year' => ['nullable', 'integer', 'min:0'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:0'],
            'is_paid' => ['boolean'],
            'carry_forward_allowed' => ['boolean'],
            'max_carry_forward_days' => ['nullable', 'integer', 'min:0'],
            'requires_document' => ['boolean'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('status', 'Leave type saved.');
    }

    public function destroyType(LeaveType $type): RedirectResponse
    {
        $type->delete();

        return back()->with('status', 'Leave type deleted.');
    }

    public function balances(): View
    {
        return view('leave.balances', array_merge($this->lookups(), [
            'balances' => LeaveBalance::with(['staff', 'leaveType', 'academicYear'])
                ->when(request('q'), fn ($query, $q) => $query->whereHas('staff', fn ($inner) => $inner->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")->orWhere('employee_code', 'like', "%{$q}%")))
                ->latest('balance_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeBalance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:staff,staff_id'],
            'leave_type_id' => ['required', 'exists:leave_types,leave_type_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'total_allocated' => ['nullable', 'numeric', 'min:0'],
            'carry_forwarded' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['total_allocated'] = $data['total_allocated'] ?? 0;
        $data['carry_forwarded'] = $data['carry_forwarded'] ?? 0;
        $data['total_available'] = $data['total_allocated'] + $data['carry_forwarded'];
        $data['used'] = 0;
        $data['pending_approval'] = 0;
        $data['remaining'] = $data['total_available'];

        LeaveBalance::updateOrCreate(
            [
                'staff_id' => $data['staff_id'],
                'leave_type_id' => $data['leave_type_id'],
                'academic_year_id' => $data['academic_year_id'],
            ],
            $data
        );

        return back()->with('status', 'Leave balance saved.');
    }

    public function destroyBalance(LeaveBalance $balance): RedirectResponse
    {
        $balance->delete();

        return back()->with('status', 'Leave balance deleted.');
    }

    public function applications(): View
    {
        return view('leave.applications', array_merge($this->lookups(), [
            'applications' => LeaveApplication::with(['staff', 'leaveType', 'academicYear', 'reportingAuthority'])
                ->when(request('q'), fn ($query, $q) => $query->where('status', 'like', "%{$q}%")->orWhereHas('staff', fn ($inner) => $inner->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")))
                ->latest('application_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:staff,staff_id'],
            'leave_type_id' => ['required', 'exists:leave_types,leave_type_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'half_day_type' => ['required', 'in:None,Morning,Afternoon'],
            'reason' => ['nullable', 'string'],
            'document_url' => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'in:Draft,Pending,Approved,Rejected,Cancelled'],
            'applied_to_staff_id' => ['nullable', 'exists:staff,staff_id'],
            'applicant_remarks' => ['nullable', 'string'],
        ]);

        $data['total_days'] = $this->days($data['from_date'], $data['to_date'], $data['half_day_type']);
        $application = LeaveApplication::create($data);
        $this->syncBalance($application->staff_id, $application->leave_type_id, $application->academic_year_id);

        return back()->with('status', 'Leave application saved.');
    }

    public function destroyApplication(LeaveApplication $application): RedirectResponse
    {
        $staffId = $application->staff_id;
        $typeId = $application->leave_type_id;
        $yearId = $application->academic_year_id;
        $application->delete();
        $this->syncBalance($staffId, $typeId, $yearId);

        return back()->with('status', 'Leave application deleted.');
    }

    public function approvals(): View
    {
        return view('leave.approvals', array_merge($this->lookups(), [
            'approvals' => LeaveApproval::with(['application.staff', 'application.leaveType', 'approver'])
                ->when(request('q'), fn ($query, $q) => $query->where('decision', 'like', "%{$q}%")->orWhereHas('application.staff', fn ($inner) => $inner->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")))
                ->latest('approval_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeApproval(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_id' => ['required', 'exists:leave_applications,application_id'],
            'approver_staff_id' => ['required', 'exists:staff,staff_id'],
            'approval_level' => ['required', 'integer', 'min:1'],
            'decision' => ['required', 'in:Approved,Rejected,Forwarded'],
            'remarks' => ['nullable', 'string'],
        ]);

        $approval = LeaveApproval::create($data);
        $application = $approval->application;
        $application->update(['status' => $data['decision'] === 'Forwarded' ? 'Pending' : $data['decision']]);
        $this->syncBalance($application->staff_id, $application->leave_type_id, $application->academic_year_id);

        return back()->with('status', 'Leave approval saved.');
    }

    public function destroyApproval(LeaveApproval $approval): RedirectResponse
    {
        $application = $approval->application;
        $approval->delete();

        if ($application) {
            $this->syncBalance($application->staff_id, $application->leave_type_id, $application->academic_year_id);
        }

        return back()->with('status', 'Leave approval deleted.');
    }

    public function cancellations(): View
    {
        return view('leave.cancellations', array_merge($this->lookups(), [
            'cancellations' => LeaveCancellation::with(['application.staff', 'cancelledBy', 'approvedBy'])
                ->when(request('q'), fn ($query, $q) => $query->where('cancel_status', 'like', "%{$q}%")->orWhereHas('application.staff', fn ($inner) => $inner->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")))
                ->latest('cancel_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeCancellation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_id' => ['required', 'exists:leave_applications,application_id'],
            'cancelled_by' => ['required', 'exists:staff,staff_id'],
            'reason' => ['nullable', 'string'],
            'cancel_status' => ['required', 'in:Requested,Approved,Rejected'],
            'approved_by' => ['nullable', 'exists:staff,staff_id'],
        ]);

        $cancellation = LeaveCancellation::create($data);
        $application = $cancellation->application;

        if ($data['cancel_status'] === 'Approved') {
            $application->update(['status' => 'Cancelled']);
        }

        $this->syncBalance($application->staff_id, $application->leave_type_id, $application->academic_year_id);

        return back()->with('status', 'Leave cancellation saved.');
    }

    public function destroyCancellation(LeaveCancellation $cancellation): RedirectResponse
    {
        $application = $cancellation->application;
        $cancellation->delete();

        if ($application) {
            $this->syncBalance($application->staff_id, $application->leave_type_id, $application->academic_year_id);
        }

        return back()->with('status', 'Leave cancellation deleted.');
    }

    public function substitutes(): View
    {
        return view('leave.substitutes', array_merge($this->lookups(), [
            'substitutes' => LeaveSubstitute::with(['application.staff', 'substituteStaff', 'subject'])
                ->when(request('q'), fn ($query, $q) => $query->where('status', 'like', "%{$q}%")->orWhereHas('subject', fn ($inner) => $inner->where('code', 'like', "%{$q}%")))
                ->latest('substitute_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeSubstitute(Request $request): RedirectResponse
    {
        LeaveSubstitute::create($request->validate([
            'application_id' => ['required', 'exists:leave_applications,application_id'],
            'substitute_staff_id' => ['required', 'exists:staff,staff_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'class_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'lecture_type' => ['nullable', 'in:Theory,Lab'],
            'status' => ['required', 'in:Pending,Confirmed,Completed'],
            'remarks' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Leave substitute saved.');
    }

    public function destroySubstitute(LeaveSubstitute $substitute): RedirectResponse
    {
        $substitute->delete();

        return back()->with('status', 'Leave substitute deleted.');
    }

    public function holidays(): View
    {
        return view('leave.holidays', array_merge($this->lookups(), [
            'holidays' => HolidayCalendar::with(['college', 'academicYear'])
                ->when(request('q'), fn ($query, $q) => $query->where('holiday_name', 'like', "%{$q}%")->orWhere('holiday_type', 'like', "%{$q}%"))
                ->orderByDesc('holiday_date')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        HolidayCalendar::create($request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'holiday_name' => ['required', 'string', 'max:150'],
            'holiday_date' => ['required', 'date'],
            'holiday_type' => ['nullable', 'in:National,State,Regional,College'],
            'is_optional' => ['boolean'],
        ]));

        return back()->with('status', 'Holiday saved.');
    }

    public function destroyHoliday(HolidayCalendar $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('status', 'Holiday deleted.');
    }

    private function lookups(): array
    {
        return [
            'staffMembers' => $this->accessScope->applyToStaff(Staff::query(), request()->user())->orderBy('first_name')->get(['staff_id', 'first_name', 'last_name']),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('code')->get(['leave_type_id', 'code', 'name']),
            'academicYears' => $this->accessScope->applyToAcademicYears(AcademicYear::query(), request()->user())->orderByDesc('is_current')->get(['academic_year_id', 'label']),
            'applicationsList' => LeaveApplication::with(['staff', 'leaveType'])->latest('application_id')->get(),
            'subjects' => $this->accessScope->applyToSubjects(Subject::query(), request()->user())->orderBy('code')->get(['subject_id', 'code', 'name']),
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
        ];
    }

    private function days(string $from, string $to, string $halfDay): float
    {
        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;

        return $halfDay === 'None' ? $days : 0.5;
    }

    private function syncBalance(int $staffId, int $typeId, int $yearId): void
    {
        $balance = LeaveBalance::firstOrCreate(
            ['staff_id' => $staffId, 'leave_type_id' => $typeId, 'academic_year_id' => $yearId],
            ['total_allocated' => 0, 'carry_forwarded' => 0, 'total_available' => 0, 'used' => 0, 'pending_approval' => 0, 'remaining' => 0]
        );

        $used = LeaveApplication::where('staff_id', $staffId)
            ->where('leave_type_id', $typeId)
            ->where('academic_year_id', $yearId)
            ->where('status', 'Approved')
            ->sum('total_days');

        $pending = LeaveApplication::where('staff_id', $staffId)
            ->where('leave_type_id', $typeId)
            ->where('academic_year_id', $yearId)
            ->where('status', 'Pending')
            ->sum('total_days');

        $available = (float) $balance->total_allocated + (float) $balance->carry_forwarded;
        $balance->update([
            'total_available' => $available,
            'used' => $used,
            'pending_approval' => $pending,
            'remaining' => max(0, $available - $used - $pending),
        ]);
    }
}

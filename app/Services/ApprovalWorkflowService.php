<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\FeeConcession;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public const DELETE_USER = 'delete_user';
    public const DELETE_STUDENT = 'delete_student';
    public const FEE_CONCESSION = 'fee_concession';
    public const PUBLISH_RESULT = 'publish_result';

    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function requiresApproval(?User $actor): bool
    {
        return $actor?->role?->role_name !== 'Super Admin';
    }

    public function request(User $actor, string $action, Model $subject, array $payload = []): ApprovalRequest
    {
        if (! Schema::hasTable('approval_requests')) {
            throw ValidationException::withMessages([
                'approval' => 'Approval workflow table is missing. Please run migrations.',
            ]);
        }

        return ApprovalRequest::query()->firstOrCreate([
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'status' => ApprovalRequest::STATUS_PENDING,
        ], [
            'requested_by' => $actor->user_id,
            'payload' => $payload,
            'requested_at' => now(),
        ]);
    }

    public function canView(User $viewer, ApprovalRequest $approval): bool
    {
        return (int) $approval->requested_by === (int) $viewer->user_id
            || $this->canApprove($viewer, $approval);
    }

    public function canApprove(User $approver, ApprovalRequest $approval): bool
    {
        if ($approval->status !== ApprovalRequest::STATUS_PENDING) {
            return false;
        }

        if ((int) $approval->requested_by === (int) $approver->user_id) {
            return false;
        }

        $requester = $approval->requester;

        if ($requester && $this->scopeRank($approver) <= $this->scopeRank($requester)) {
            return false;
        }

        return $this->hasActionPermission($approver, $approval->action)
            && $this->subjectIsInScope($approver, $approval);
    }

    public function approve(ApprovalRequest $approval, User $approver, ?string $remarks = null): void
    {
        if (! $this->canApprove($approver, $approval)) {
            throw ValidationException::withMessages([
                'approval' => 'Only a higher scoped user with matching permission can approve this request.',
            ]);
        }

        DB::transaction(function () use ($approval, $approver, $remarks): void {
            $this->applyApprovedAction($approval, $approver);

            $approval->update([
                'status' => ApprovalRequest::STATUS_APPROVED,
                'approved_by' => $approver->user_id,
                'approved_at' => now(),
                'remarks' => $remarks,
            ]);
        });
    }

    public function reject(ApprovalRequest $approval, User $approver, ?string $remarks = null): void
    {
        if (! $this->canApprove($approver, $approval)) {
            throw ValidationException::withMessages([
                'approval' => 'Only a higher scoped user with matching permission can reject this request.',
            ]);
        }

        $approval->update([
            'status' => ApprovalRequest::STATUS_REJECTED,
            'approved_by' => $approver->user_id,
            'approved_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            self::DELETE_USER => 'Delete User',
            self::DELETE_STUDENT => 'Delete Student',
            self::FEE_CONCESSION => 'Fee Concession',
            self::PUBLISH_RESULT => 'Publish Result',
            default => str_replace('_', ' ', ucfirst($action)),
        };
    }

    private function applyApprovedAction(ApprovalRequest $approval, User $approver): void
    {
        match ($approval->action) {
            self::DELETE_USER => $this->deleteUser($approval, $approver),
            self::DELETE_STUDENT => $this->deleteStudent($approval, $approver),
            self::FEE_CONCESSION => $this->approveFeeConcession($approval, $approver),
            self::PUBLISH_RESULT => $this->publishResult($approval),
            default => throw ValidationException::withMessages([
                'approval' => 'Unknown approval action.',
            ]),
        };
    }

    private function deleteUser(ApprovalRequest $approval, User $approver): void
    {
        $user = User::query()->findOrFail($approval->subject_id);

        if ($approver->is($user)) {
            throw ValidationException::withMessages([
                'approval' => 'You cannot approve deletion of your own account.',
            ]);
        }

        if (in_array($user->reference_type, ['Staff', 'Student'], true) && filled($user->reference_id)) {
            throw ValidationException::withMessages([
                'approval' => 'Linked staff/student accounts must be managed from their profile record.',
            ]);
        }

        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), $approver)->exists(), 403);

        $user->delete();
    }

    private function deleteStudent(ApprovalRequest $approval, User $approver): void
    {
        $student = Student::query()->findOrFail($approval->subject_id);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), $approver)->exists(), 403);

        User::query()
            ->where('reference_type', 'Student')
            ->where('reference_id', $student->student_id)
            ->each(fn (User $user) => $user->delete());

        $student->delete();
    }

    private function approveFeeConcession(ApprovalRequest $approval, User $approver): void
    {
        $concession = FeeConcession::query()->findOrFail($approval->subject_id);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($concession->student_id), $approver)->exists(), 403);

        $concession->update([
            'approved_by' => $approver->user_id,
            'approved_on' => now()->toDateString(),
            'is_active' => true,
        ]);

        if ($concession->ledger) {
            $this->syncLedger($concession->ledger);
        }
    }

    private function publishResult(ApprovalRequest $approval): void
    {
        $result = Result::query()->findOrFail($approval->subject_id);

        $result->update([
            'is_published' => true,
            'declared_at' => $result->declared_at ?: now(),
        ]);
    }

    private function subjectIsInScope(User $user, ApprovalRequest $approval): bool
    {
        return match ($approval->action) {
            self::DELETE_USER => $this->accessScope->applyToUsers(User::whereKey($approval->subject_id), $user)->exists(),
            self::DELETE_STUDENT => $this->accessScope->applyToStudents(Student::whereKey($approval->subject_id), $user)->exists(),
            self::FEE_CONCESSION => FeeConcession::query()
                ->whereKey($approval->subject_id)
                ->whereHas('student', fn ($student) => $this->accessScope->applyToStudents($student, $user))
                ->exists(),
            self::PUBLISH_RESULT => $this->accessScope->applyToResults(Result::whereKey($approval->subject_id), $user)->exists(),
            default => false,
        };
    }

    private function hasActionPermission(User $user, string $action): bool
    {
        [$module, $permissionAction] = match ($action) {
            self::DELETE_USER => ['user', 'delete'],
            self::DELETE_STUDENT => ['student', 'delete'],
            self::FEE_CONCESSION => ['concession', 'approve'],
            self::PUBLISH_RESULT => ['result', 'approve'],
            default => [null, null],
        };

        if (! $module || ! Schema::hasTable('permissions') || ! Schema::hasTable('user_permissions')) {
            return false;
        }

        return $user->permissions()
            ->where('module_name', $module)
            ->where('action', $permissionAction)
            ->exists();
    }

    private function scopeRank(User $user): int
    {
        if ($user->role?->role_name === 'Super Admin') {
            return 100;
        }

        return match ($this->accessScope->forUser($user)['level'] ?? 'own') {
            'system' => 100,
            'university' => 80,
            'college' => 60,
            'programme' => 40,
            'subject_semester' => 30,
            default => 10,
        };
    }

    private function syncLedger(StudentFeeLedger $ledger): void
    {
        $paid = (float) $ledger->payments()->where('payment_status', 'Cleared')->sum('amount_paid');
        $concessions = $ledger->concessions()
            ->where('is_active', true)
            ->get()
            ->sum(fn (FeeConcession $concession) => (float) ($concession->concession_amount ?: ($ledger->total_amount * ($concession->concession_pct ?? 0) / 100)));

        $netPayable = max(0, (float) $ledger->total_amount - $concessions - (float) $ledger->scholarship_amount);
        $balanceDue = max(0, $netPayable - $paid);

        $ledger->update([
            'concession_amount' => $concessions,
            'net_payable' => $netPayable,
            'amount_paid' => $paid,
            'balance_due' => $balanceDue,
            'payment_status' => $balanceDue <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            'is_hall_ticket_cleared' => $balanceDue <= 0,
        ]);
    }
}

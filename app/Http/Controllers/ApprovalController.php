<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(protected ApprovalWorkflowService $workflow)
    {
    }

    public function index(Request $request): View
    {
        $approvalRequests = ApprovalRequest::query()
            ->with(['requester.role', 'approver'])
            ->latest('requested_at')
            ->limit(100)
            ->get()
            ->filter(fn (ApprovalRequest $approval) => $this->workflow->canView($request->user(), $approval))
            ->values();

        return view('approvals.index', [
            'approvalRequests' => $approvalRequests,
            'workflow' => $this->workflow,
        ]);
    }

    public function approve(Request $request, ApprovalRequest $approval): RedirectResponse
    {
        $data = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->approve($approval, $request->user(), $data['remarks'] ?? null);

        return back()->with('status', 'Approval request approved successfully.');
    }

    public function reject(Request $request, ApprovalRequest $approval): RedirectResponse
    {
        $data = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->reject($approval, $request->user(), $data['remarks'] ?? null);

        return back()->with('status', 'Approval request rejected.');
    }
}

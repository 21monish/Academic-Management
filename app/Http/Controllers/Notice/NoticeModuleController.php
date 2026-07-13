<?php

namespace App\Http\Controllers\Notice;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Notice;
use App\Models\NoticeAcknowledgement;
use App\Models\NoticeAttachment;
use App\Models\NoticeAudience;
use App\Models\NoticeCategory;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AccessScopeService;
use App\Services\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoticeModuleController extends Controller
{
    public function __construct(
        protected UploadService $uploads,
        protected AccessScopeService $accessScope
    )
    {
    }

    public function categories(): View
    {
        return view('notices.categories', [
            'categories' => NoticeCategory::query()
                ->when(request('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        NoticeCategory::create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color_code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('status', 'Notice category saved.');
    }

    public function destroyCategory(NoticeCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('status', 'Notice category deleted.');
    }

    public function notices(): View
    {
        $canManageNotices = hasPermission('notice.create')
            || hasPermission('notice.update')
            || hasPermission('notice.delete')
            || hasPermission('notice.approve');
        $user = auth()->user();
        $scopedCollegeId = $this->scopedCollegeId($user);
        $scopedDepartmentId = $this->scopedDepartmentId($user);
        $departmentFilter = $scopedDepartmentId ?: request()->integer('dept_id');

        return view('notices.index', array_merge($this->lookups(), [
            'notices' => Notice::with(['college', 'department', 'category', 'createdBy'])
                ->when($scopedCollegeId, fn ($query, int $collegeId) => $query->where('college_id', $collegeId))
                ->when($departmentFilter, fn ($query, int $deptId) => $query->where('dept_id', $deptId))
                ->when(request('q'), fn ($query, $q) => $query->where(function ($search) use ($q) {
                    $search->where('title', 'like', "%{$q}%")
                        ->orWhere('priority', 'like', "%{$q}%");
                }))
                ->when(! $canManageNotices, function ($query) use ($user) {
                    $today = now()->toDateString();

                    $query->where('is_published', true)
                        ->where(function ($validity) use ($today) {
                            $validity->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
                        })
                        ->where(function ($validity) use ($today) {
                            $validity->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
                        })
                        ->where(function ($audience) use ($user) {
                            $audience->where('audience_type', 'All')
                                ->when($user?->college_id, fn ($scope, $collegeId) => $scope->orWhere('college_id', $collegeId))
                                ->when($user?->dept_id, fn ($scope, $deptId) => $scope->orWhere('dept_id', $deptId))
                                ->orWhereHas('audiences', function ($scope) use ($user) {
                                    $scope->where(function ($target) use ($user) {
                                        $target->where('target_type', 'Role')
                                            ->where('target_id', $user?->role_id ?? 0);
                                    })->orWhere(function ($target) use ($user) {
                                        $target->where('target_type', 'Individual')
                                            ->where('target_id', $user?->user_id ?? 0);
                                    });
                                });
                        });
                })
                ->latest('notice_id')
                ->paginate(20)
                ->withQueryString(),
            'canManageNotices' => $canManageNotices,
            'scopedCollege' => $scopedCollegeId ? College::find($scopedCollegeId) : null,
            'scopedDepartment' => $scopedDepartmentId ? Department::find($scopedDepartmentId) : null,
            'departmentFilter' => $departmentFilter,
        ]));
    }

    public function storeNotice(Request $request): RedirectResponse
    {
        if ($collegeId = $this->scopedCollegeId($request->user())) {
            $request->merge(['college_id' => $collegeId]);
        }

        if ($deptId = $this->scopedDepartmentId($request->user())) {
            $request->merge(['dept_id' => $deptId]);
        }

        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'dept_id' => [
                'nullable',
                'required_if:audience_type,Dept',
                Rule::exists('departments', 'dept_id')->where(fn ($query) => $query->where('college_id', $request->input('college_id'))),
            ],
            'notice_category_id' => ['nullable', 'exists:notice_categories,notice_category_id'],
            'title' => ['required', 'string', 'max:300'],
            'content' => ['nullable', 'string'],
            'priority' => ['required', 'in:Low,Normal,High,Urgent'],
            'audience_type' => ['required', 'in:All,College,Dept,Programme,Semester,Role'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_pinned' => ['boolean'],
            'is_published' => ['boolean'],
            'requires_acknowledgement' => ['boolean'],
        ]);

        if (! empty($data['dept_id'])) {
            $data['college_id'] = Department::find($data['dept_id'])?->college_id ?? $data['college_id'];
        }

        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);

        if (! empty($data['dept_id'])) {
            abort_unless($this->accessScope->applyToDepartments(Department::whereKey($data['dept_id']), $request->user())->exists(), 403);
        }

        $data['created_by'] = auth()->id();
        $data['published_at'] = ! empty($data['is_published']) ? now() : null;
        Notice::create($data);

        return back()->with('status', 'Notice saved.');
    }

    public function destroyNotice(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return back()->with('status', 'Notice deleted.');
    }

    public function audiences(): View
    {
        $audiences = NoticeAudience::with('notice')
            ->when(request('q'), fn ($query, $q) => $query->where('target_type', 'like', "%{$q}%")->orWhereHas('notice', fn ($inner) => $inner->where('title', 'like', "%{$q}%")))
            ->latest('audience_id')
            ->paginate(20)
            ->withQueryString();

        $audiences->getCollection()->transform(function (NoticeAudience $audience) {
            $audience->target_label = $this->targetLabel($audience->target_type, $audience->target_id);
            return $audience;
        });

        return view('notices.audiences', array_merge($this->lookups(), [
            'audiences' => $audiences,
        ]));
    }

    public function storeAudience(Request $request): RedirectResponse
    {
        NoticeAudience::create($request->validate([
            'notice_id' => ['required', 'exists:notices,notice_id'],
            'target_type' => ['required', 'in:Department,Programme,Semester,Role,Individual'],
            'target_id' => ['required', 'integer', 'min:1'],
        ]));

        return back()->with('status', 'Notice audience saved.');
    }

    public function destroyAudience(NoticeAudience $audience): RedirectResponse
    {
        $audience->delete();

        return back()->with('status', 'Notice audience deleted.');
    }

    public function attachments(): View
    {
        return view('notices.attachments', array_merge($this->lookups(), [
            'attachments' => NoticeAttachment::with('notice')
                ->when(request('q'), fn ($query, $q) => $query->where('file_name', 'like', "%{$q}%")->orWhere('file_type', 'like', "%{$q}%")->orWhereHas('notice', fn ($inner) => $inner->where('title', 'like', "%{$q}%")))
                ->latest('attachment_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeAttachment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notice_id' => ['required', 'exists:notices,notice_id'],
            'attachment' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('attachment');
        $path = $this->uploads->storePublicUpload($file, 'uploads/notices');

        NoticeAttachment::create([
            'notice_id' => $data['notice_id'],
            'file_name' => Str::limit($file->getClientOriginalName(), 200, ''),
            'file_url' => $path,
            'file_type' => strtoupper($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'FILE'),
            'file_size_kb' => (int) ceil($file->getSize() / 1024),
        ]);

        return back()->with('status', 'Notice attachment saved.');
    }

    public function destroyAttachment(NoticeAttachment $attachment): RedirectResponse
    {
        if ($attachment->file_url && ! Str::startsWith($attachment->file_url, ['http://', 'https://', '/'])) {
            File::delete(public_path($attachment->file_url));
        }

        $attachment->delete();

        return back()->with('status', 'Notice attachment deleted.');
    }

    public function acknowledgements(): View
    {
        return view('notices.acknowledgements', array_merge($this->lookups(), [
            'acknowledgements' => NoticeAcknowledgement::with(['notice', 'user'])
                ->when(request('q'), fn ($query, $q) => $query->whereHas('notice', fn ($inner) => $inner->where('title', 'like', "%{$q}%"))->orWhereHas('user', fn ($inner) => $inner->where('username', 'like', "%{$q}%")))
                ->latest('ack_id')
                ->paginate(20)
                ->withQueryString(),
        ]));
    }

    public function storeAcknowledgement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notice_id' => ['required', 'exists:notices,notice_id'],
            'user_id' => ['required', 'exists:users,user_id'],
            'ip_address' => ['nullable', 'string', 'max:45'],
        ]);

        $data['ip_address'] = $data['ip_address'] ?: $request->ip();
        NoticeAcknowledgement::updateOrCreate(
            ['notice_id' => $data['notice_id'], 'user_id' => $data['user_id']],
            $data + ['acknowledged_at' => now()]
        );

        return back()->with('status', 'Notice acknowledgement saved.');
    }

    public function destroyAcknowledgement(NoticeAcknowledgement $acknowledgement): RedirectResponse
    {
        $acknowledgement->delete();

        return back()->with('status', 'Notice acknowledgement deleted.');
    }

    private function lookups(): array
    {
        return [
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'categoriesList' => NoticeCategory::where('is_active', true)->orderBy('name')->get(['notice_category_id', 'name', 'color_code']),
            'noticesList' => Notice::latest('notice_id')->get(['notice_id', 'title', 'audience_type']),
            'departments' => $this->accessScope->applyToDepartments(Department::with('college'), request()->user())->orderBy('name')->get(['dept_id', 'college_id', 'name']),
            'programmes' => $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']),
            'semesters' => $this->accessScope->applyToSemesters(Semester::query(), request()->user())->orderBy('semester_no')->get(['semester_id', 'semester_no']),
            'roles' => $this->accessScope->applyToRoles(UserRole::query(), request()->user())->orderBy('role_name')->get(['role_id', 'role_name']),
            'users' => $this->accessScope->applyToUsers(User::query(), request()->user())->orderBy('username')->get(['user_id', 'username', 'email']),
        ];
    }

    private function scopedCollegeId(?User $user): ?int
    {
        return $this->accessScope->forUser($user)['college_id'] ?? null;
    }

    private function scopedDepartmentId(?User $user): ?int
    {
        return $this->accessScope->forUser($user)['dept_id'] ?? null;
    }

    private function targetLabel(string $type, int $id): string
    {
        return match ($type) {
            'Department' => Department::find($id)?->name ?? "Department #{$id}",
            'Programme' => Programme::find($id)?->name ?? "Programme #{$id}",
            'Semester' => 'Sem ' . (Semester::find($id)?->semester_no ?? $id),
            'Role' => UserRole::find($id)?->role_name ?? "Role #{$id}",
            'Individual' => User::find($id)?->username ?? "User #{$id}",
            default => "Target #{$id}",
        };
    }
}

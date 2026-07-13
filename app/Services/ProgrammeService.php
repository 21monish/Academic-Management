<?php

namespace App\Services;

use App\Models\Programme;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgrammeService
{
    public function __construct(
        protected DatabaseManager $db,
        protected AccessScopeService $accessScope
    )
    {
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: array, 2: array}
     */
    public function index(Request $request): array
    {
        $query = $this->accessScope->applyToProgrammes(Programme::with('department'), $request->user());

        $filters = [
            'q' => $request->string('q')->toString(),
            'dept_id' => $request->string('dept_id')->toString(),
            'level' => $request->string('level')->toString(),
            'is_active' => $request->string('is_active')->toString(),
            'sort' => $request->string('sort')->toString(),
            'direction' => $request->string('direction')->toString(),
        ];

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('dept_id')) {
            $query->where('dept_id', $request->string('dept_id')->toString());
        }

        if ($request->filled('level')) {
            $query->where('level', $request->string('level')->toString());
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sortable = [
            'programme_id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'level' => 'Level',
            'duration_semesters' => 'Duration',
            'total_credits' => 'Credits',
            'is_active' => 'Status',
        ];

        $sort = in_array($request->string('sort')->toString(), array_keys($sortable), true)
            ? $request->string('sort')->toString()
            : 'programme_id';

        $direction = $request->string('direction')->toString();
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $programmes = $query
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return [$programmes, $filters, $sortable];
    }


    public function create(array $data): Programme
    {
        abort_unless($this->accessScope->applyToDepartments(\App\Models\Department::whereKey($data['dept_id']), request()->user())->exists(), 403);

        return $this->db->transaction(function () use ($data) {
            return Programme::query()->create([
                'dept_id' => $data['dept_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'level' => $data['level'],
                'duration_semesters' => $data['duration_semesters'] ?? null,
                'total_credits' => $data['total_credits'] ?? null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);
        });
    }

    public function update(Programme $programme, array $data): void
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($programme->programme_id), request()->user())->exists(), 403);
        abort_unless($this->accessScope->applyToDepartments(\App\Models\Department::whereKey($data['dept_id']), request()->user())->exists(), 403);

        $this->db->transaction(function () use ($programme, $data) {
            $programme->update([
                'dept_id' => $data['dept_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'level' => $data['level'],
                'duration_semesters' => $data['duration_semesters'] ?? null,
                'total_credits' => $data['total_credits'] ?? null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $programme->is_active,
            ]);
        });
    }

    public function delete(Programme $programme): void
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($programme->programme_id), request()->user())->exists(), 403);

        $this->db->transaction(function () use ($programme) {
            $programme->delete();
        });
    }

    public function setActive(Programme $programme, bool $active): void
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($programme->programme_id), request()->user())->exists(), 403);

        $this->db->transaction(function () use ($programme, $active) {
            $programme->update(['is_active' => $active]);
        });
    }

    public function createViewData(): array
    {
        return [
            'departments' => $this->accessScope->applyToDepartments(\App\Models\Department::query(), request()->user())->orderBy('name')->get(['dept_id', 'name']),
        ];
    }

    public function show(Programme $programme): Programme
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($programme->programme_id), request()->user())->exists(), 403);

        return $programme->load(['department']);
    }

    public function editViewData(): array
    {
        return $this->createViewData();
    }
}


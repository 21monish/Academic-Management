<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Programme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemesterService
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    /**
     * Get paginated semesters with search, filter and sorting.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $query = $this->accessScope->applyToSemesters(Semester::with('programme'), $request->user());

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('semester_no', 'like', "%{$search}%");
            });
        }

        // Programme Filter
        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->programme_id);
        }

        // Status Filter (only if column exists)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'semester_no');
        $sortDirection = $request->get('sort_direction', 'asc');

        $allowedSorts = [
            'semester_no',
            'name',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'semester_no';
        }

        $sortDirection = strtolower($sortDirection) === 'desc'
            ? 'desc'
            : 'asc';

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Create Semester.
     */
    public function create(array $data): Semester
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), request()->user())->exists(), 403);

        return DB::transaction(function () use ($data) {
            return Semester::create($data);
        });
    }

    /**
     * Update Semester.
     */
    public function update(Semester $semester, array $data): Semester
    {
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($semester->semester_id), request()->user())->exists(), 403);
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), request()->user())->exists(), 403);

        return DB::transaction(function () use ($semester, $data) {

            $semester->update($data);

            return $semester->refresh();
        });
    }

    /**
     * Delete Semester.
     */
    public function delete(Semester $semester): bool
    {
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($semester->semester_id), request()->user())->exists(), 403);

        return DB::transaction(function () use ($semester) {
            return (bool) $semester->delete();
        });
    }

    /**
     * Activate / Deactivate Semester.
     */
    public function setActive(Semester $semester, bool $status): Semester
    {
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($semester->semester_id), request()->user())->exists(), 403);

        // Skip if your table doesn't have is_active
        if (! array_key_exists('is_active', $semester->getAttributes())) {
            return $semester;
        }

        return DB::transaction(function () use ($semester, $status) {

            $semester->update([
                'is_active' => $status,
            ]);

            return $semester->refresh();
        });
    }
}

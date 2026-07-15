<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreSubjectRequest;
use App\Http\Requests\Academic\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct(protected SubjectService $subjectService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('subject.view');

        [$subjects, $filters, $sortable] = $this->subjectService->index($request);
        $departments = $this->subjectService->createViewData()['departments'];

        return view('academic.subjects.index', [
            'subjects' => $subjects,
            'filters' => $filters,
            'sortable' => $sortable,
            'departments' => $departments,
        ]);
    }

    public function create(): View
    {
        $this->authorize('subject.create');

        return view('academic.subjects.create', $this->subjectService->createViewData());
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $this->authorize('subject.create');

        $subject = $this->subjectService->store($request->validated());

        return redirect()
            ->route('academic.subjects.index')
            ->with('status', 'Subject '.$subject->name.' created successfully.');
    }

    public function show(Subject $subject): View
    {
        $this->authorize('subject.view');

        return view('academic.subjects.show', [
            'subject' => $this->subjectService->show($subject),
        ]);
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('subject.update');

        return view('academic.subjects.edit', [
            'subject' => $subject,
            'viewData' => $this->subjectService->editViewData($subject),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('subject.update');

        $this->subjectService->update($subject, $request->validated());

        return redirect()
            ->route('academic.subjects.show', $subject)
            ->with('status', 'Subject '.$subject->name.' updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('subject.delete');

        $subjectName = $subject->name;

        $this->subjectService->delete($subject);

        return redirect()
            ->route('academic.subjects.index')
            ->with('status', 'Subject '.$subjectName.' deleted successfully.');
    }

    public function activate(Subject $subject): RedirectResponse
    {
        $this->authorize('subject.activate');

        $this->subjectService->setActive($subject, true);

        return redirect()->route('academic.subjects.index')->with('status', 'Subject '.$subject->name.' activated successfully.');
    }

    public function deactivate(Subject $subject): RedirectResponse
    {
        $this->authorize('subject.deactivate');

        $this->subjectService->setActive($subject, false);

        return redirect()->route('academic.subjects.index')->with('status', 'Subject '.$subject->name.' deactivated successfully.');
    }
}


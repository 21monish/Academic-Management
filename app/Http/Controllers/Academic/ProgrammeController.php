<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreProgrammeRequest;
use App\Http\Requests\Academic\UpdateProgrammeRequest;
use App\Models\Programme;
use App\Services\ProgrammeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    public function __construct(protected ProgrammeService $programmeService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('programme.view');

        [$programmes, $filters, $sortable] = $this->programmeService->index($request);
        $departments = $this->programmeService->createViewData()['departments'];

        return view('programme.index', [
            'programmes' => $programmes,
            'filters' => $filters,
            'sortable' => $sortable,
            'departments' => $departments,
        ]);
    }





    public function create(): View
    {
        $this->authorize('programme.create');

        return view('programme.create', $this->programmeService->createViewData());
    }



    public function store(StoreProgrammeRequest $request): RedirectResponse
    {
        $this->authorize('programme.create');

        $programme = $this->programmeService->create($request->validated());

        return redirect()
            ->route('academic.programmes.show', $programme)
            ->with('status', 'Programme created.');
    }

    public function show(Programme $programme): View
    {
        $this->authorize('programme.profile');

        return view('programme.show', [
            'programme' => $this->programmeService->show($programme),
        ]);
    }


    public function edit(Programme $programme): View
    {
        $this->authorize('programme.edit');

        $departments = $this->programmeService->editViewData()['departments'];

        return view('programme.edit', [
            'programme' => $programme,
            'departments' => $departments,
        ]);
    }




    public function update(UpdateProgrammeRequest $request, Programme $programme): RedirectResponse
    {
        $this->authorize('programme.edit');

        $this->programmeService->update($programme, $request->validated());

        return redirect()
            ->route('academic.programmes.show', $programme)
            ->with('status', 'Programme updated.');
    }

    public function destroy(Programme $programme): RedirectResponse
    {
        $this->authorize('programme.delete');

        $this->programmeService->delete($programme);

        return redirect()
            ->route('academic.programmes.index')
            ->with('status', 'Programme deleted.');
    }

    public function activate(Programme $programme): RedirectResponse
    {
        $this->authorize('programme.edit');

        $this->programmeService->setActive($programme, true);

        return redirect()->route('academic.programmes.index')->with('status', 'Programme activated.');
    }

    public function deactivate(Programme $programme): RedirectResponse
    {
        $this->authorize('programme.edit');

        $this->programmeService->setActive($programme, false);

        return redirect()->route('academic.programmes.index')->with('status', 'Programme deactivated.');
    }
}


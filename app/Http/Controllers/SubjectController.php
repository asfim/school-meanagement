<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request): Response
    {
        $query = Subject::query()->with('program');

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->input('program_id'));
        }

        $subjects = $query->orderBy('name')->get();
        $programs = Program::orderBy('name')->get();

        return Inertia::render('subjects/Index', [
            'subjects' => $subjects,
            'programs' => $programs,
            'filters' => $request->only(['program_id']),
        ]);
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'Subject added successfully.');
    }

    /**
     * Update the specified subject.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,'.$subject->id,
            'code' => 'nullable|string|max:50|unique:subjects,code,'.$subject->id,
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->back()->with('success', 'Subject deleted successfully.');
    }
}

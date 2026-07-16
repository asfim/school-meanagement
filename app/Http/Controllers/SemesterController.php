<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\SemesterExam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SemesterController extends Controller
{
    public function index(): Response
    {
        $semesters = Semester::with('exams')->orderBy('sort_order')->get();

        return Inertia::render('semesters/Index', [
            'semesters' => $semesters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:semesters,name',
            'sort_order' => 'required|integer',
        ]);

        $semester = Semester::create($request->only('name', 'sort_order'));

        // Always create a "Final Semester Exam" as static default for the semester
        SemesterExam::create([
            'semester_id' => $semester->id,
            'name' => 'Final Semester Exam',
            'is_final' => true,
            'sort_order' => 99,
        ]);

        return redirect()->back()->with('success', 'Semester created successfully.');
    }

    public function update(Request $request, Semester $semester): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:semesters,name,'.$semester->id,
            'sort_order' => 'required|integer',
        ]);

        $semester->update($request->only('name', 'sort_order'));

        return redirect()->back()->with('success', 'Semester updated successfully.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        $semester->delete();

        return redirect()->back()->with('success', 'Semester deleted successfully.');
    }
}

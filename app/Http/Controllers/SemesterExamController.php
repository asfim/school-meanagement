<?php

namespace App\Http\Controllers;

use App\Models\SemesterExam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SemesterExamController extends Controller
{
    public function store(Request $request, $semesterId): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
        ]);

        SemesterExam::create([
            'semester_id' => $semesterId,
            'name' => $request->input('name'),
            'is_final' => false,
            'sort_order' => $request->input('sort_order'),
        ]);

        return redirect()->back()->with('success', 'Semester exam term created successfully.');
    }

    public function update(Request $request, SemesterExam $semesterExam): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer',
        ]);

        if ($semesterExam->is_final) {
            return redirect()->back()->withErrors(['name' => 'The Final Semester Exam cannot be renamed or modified.']);
        }

        $semesterExam->update([
            'name' => $request->input('name'),
            'sort_order' => $request->input('sort_order'),
        ]);

        return redirect()->back()->with('success', 'Semester exam term updated successfully.');
    }

    public function destroy(SemesterExam $semesterExam): RedirectResponse
    {
        if ($semesterExam->is_final) {
            return redirect()->back()->withErrors(['name' => 'The Final Semester Exam cannot be deleted.']);
        }

        $semesterExam->delete();

        return redirect()->back()->with('success', 'Semester exam term deleted successfully.');
    }
}

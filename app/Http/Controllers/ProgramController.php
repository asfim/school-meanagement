<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(): Response
    {
        $programs = Program::orderBy('name')->get();

        return Inertia::render('programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name',
            'code' => 'nullable|string|max:50|unique:programs,code',
            'description' => 'nullable|string|max:1000',
            'duration_years' => 'nullable|integer|min:1|max:10',
        ]);

        Program::create($validated);

        return redirect()->back()->with('success', 'Program added successfully.');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,'.$program->id,
            'code' => 'nullable|string|max:50|unique:programs,code,'.$program->id,
            'description' => 'nullable|string|max:1000',
            'duration_years' => 'nullable|integer|min:1|max:10',
        ]);

        $program->update($validated);

        return redirect()->back()->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->back()->with('success', 'Program deleted successfully.');
    }
}

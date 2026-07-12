<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of students with filters.
     */
    public function index(Request $request): Response
    {
        $query = Student::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name_en', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class')) {
            $query->where('class', $request->input('class'));
        }

        if ($request->filled('section')) {
            $query->where('section', $request->input('section'));
        }

        $students = $query->orderBy('class')
            ->orderBy('section')
            ->orderBy('roll_number')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'class', 'section']),
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): Response
    {
        return Inertia::render('students/CreateEdit', [
            'student' => null,
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_native' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string|in:male,female,other',
            'parent_name' => 'required|string|max:255',
            'parent_mobile' => 'required|string|max:20',
            'permanent_address' => 'required|string',
            'current_address' => 'required|string',
            'class' => 'required|string',
            'section' => 'required|string',
            'roll_number' => 'required|integer|min:1',
            'admission_date' => 'required|date',
            'blood_group' => 'nullable|string|max:5',
            'emergency_contact' => 'required|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Generate unique Student ID: STU-YYYY-XXXX
        $year = date('Y');
        $lastStudent = Student::where('student_id', 'like', "STU-{$year}-%")->orderBy('id', 'desc')->first();
        $nextNum = 1;
        if ($lastStudent !== null) {
            $parts = explode('-', $lastStudent->student_id);
            $nextNum = (int) end($parts) + 1;
        }
        $validated['student_id'] = 'STU-' . $year . '-' . sprintf('%04d', $nextNum);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student registered successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): Response
    {
        return Inertia::render('students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the student.
     */
    public function edit(Student $student): Response
    {
        return Inertia::render('students/CreateEdit', [
            'student' => $student,
        ]);
    }

    /**
     * Update the student in storage.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_native' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string|in:male,female,other',
            'parent_name' => 'required|string|max:255',
            'parent_mobile' => 'required|string|max:20',
            'permanent_address' => 'required|string',
            'current_address' => 'required|string',
            'class' => 'required|string',
            'section' => 'required|string',
            'roll_number' => 'required|integer|min:1',
            'admission_date' => 'required|date',
            'blood_group' => 'nullable|string|max:5',
            'emergency_contact' => 'required|string|max:20',
            'status' => 'required|string|in:active,transferred,inactive',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo_path !== null) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student information updated.');
    }

    /**
     * Issue Transfer Certificate (TC).
     */
    public function issueTc(Student $student): RedirectResponse
    {
        $student->update(['status' => 'transferred']);

        return redirect()->back()->with('success', 'Transfer Certificate issued. Student status set to Transferred.');
    }

    /**
     * Remove the student from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo_path !== null) {
            Storage::disk('public')->delete($student->photo_path);
        }
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student record deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): Response
    {
        $query = Teacher::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('teacher_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('full_name')->get();

        return Inertia::render('teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create(): Response
    {
        return Inertia::render('teachers/CreateEdit', [
            'teacher' => null,
            'subjectsList' => \App\Models\Subject::orderBy('name')->pluck('name')->toArray(),
        ]);
    }

    /**
     * Store a newly created teacher.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|unique:teachers,email',
            'qualifications' => 'required|string',
            'subjects' => 'required|array',
            'classes' => 'required|array',
            'date_of_joining' => 'required|date',
            'designation' => 'required|string|max:255',
            'salary_structure.base_salary' => 'required|numeric|min:0',
            'salary_structure.allowances' => 'required|numeric|min:0',
            'salary_structure.deductions' => 'required|numeric|min:0',
            'address' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Generate unique Teacher ID: TCH-YYYY-XXXX
        $year = date('Y');
        $lastTeacher = Teacher::where('teacher_id', 'like', "TCH-{$year}-%")->orderBy('id', 'desc')->first();
        $nextNum = 1;
        if ($lastTeacher !== null) {
            $parts = explode('-', $lastTeacher->teacher_id);
            $nextNum = (int) end($parts) + 1;
        }
        $validated['teacher_id'] = 'TCH-' . $year . '-' . sprintf('%04d', $nextNum);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('teachers', 'public');
            $validated['photo_path'] = $path;
        }

        Teacher::create($validated);

        return redirect()->route('teachers.index')->with('success', 'Teacher added successfully.');
    }

    /**
     * Show the form for editing the teacher.
     */
    public function edit(Teacher $teacher): Response
    {
        return Inertia::render('teachers/CreateEdit', [
            'teacher' => $teacher,
            'subjectsList' => \App\Models\Subject::orderBy('name')->pluck('name')->toArray(),
        ]);
    }

    /**
     * Update the teacher.
     */
    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'qualifications' => 'required|string',
            'subjects' => 'required|array',
            'classes' => 'required|array',
            'date_of_joining' => 'required|date',
            'designation' => 'required|string|max:255',
            'salary_structure.base_salary' => 'required|numeric|min:0',
            'salary_structure.allowances' => 'required|numeric|min:0',
            'salary_structure.deductions' => 'required|numeric|min:0',
            'address' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($teacher->photo_path !== null) {
                Storage::disk('public')->delete($teacher->photo_path);
            }
            $path = $request->file('photo')->store('teachers', 'public');
            $validated['photo_path'] = $path;
        }

        $teacher->update($validated);

        return redirect()->route('teachers.index')->with('success', 'Teacher information updated.');
    }

    /**
     * Show the attendance page.
     */
    public function attendance(Request $request): Response
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $teachers = Teacher::orderBy('full_name')->get();
        $attendances = TeacherAttendance::where('date', $date)->get()->keyBy('teacher_id');

        $teachersWithAttendance = $teachers->map(function ($teacher) use ($attendances) {
            $att = $attendances->get($teacher->id);
            return [
                'id' => $teacher->id,
                'teacher_id' => $teacher->teacher_id,
                'full_name' => $teacher->full_name,
                'designation' => $teacher->designation,
                'attendance_status' => $att ? $att->status : 'present',
                'remarks' => $att ? $att->remarks : '',
            ];
        });

        return Inertia::render('teachers/Attendance', [
            'teachers' => $teachersWithAttendance,
            'date' => $date,
        ]);
    }

    /**
     * Save/Update attendance records.
     */
    public function saveAttendance(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.id' => 'required|exists:teachers,id',
            'records.*.attendance_status' => 'required|string|in:present,absent,late,leave',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $date = $request->input('date');

        foreach ($request->input('records') as $record) {
            TeacherAttendance::updateOrCreate(
                [
                    'teacher_id' => $record['id'],
                    'date' => $date,
                ],
                [
                    'status' => $record['attendance_status'],
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance records saved successfully.');
    }

    /**
     * Remove the teacher.
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        if ($teacher->photo_path !== null) {
            Storage::disk('public')->delete($teacher->photo_path);
        }
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher record deleted.');
    }
}

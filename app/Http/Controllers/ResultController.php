<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\Program;
use App\Models\Semester;
use App\Models\SemesterExam;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends Controller
{
    /**
     * Display exam results tabulation.
     */
    public function index(Request $request): Response
    {
        $programName = $request->input('program_name');
        $section = $request->input('section');
        $search = $request->input('search');

        $query = Student::with(['semester', 'latestResult.semester', 'latestResult.semesterExam']);

        if ($request->filled('program_name')) {
            $query->where('program_name', $programName);
        }
        if ($request->filled('section')) {
            $query->where('section', $section);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_en', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('program_name')
            ->orderBy('section')
            ->orderBy('roll_number')
            ->paginate(15)
            ->withQueryString();

        $reportCardData = collect($students->items())->map(function ($student) {
            $res = $student->latestResult;

            return [
                'student_id' => $student->id,
                'student_uid' => $student->student_id,
                'full_name' => $student->full_name_en,
                'roll_number' => $student->roll_number,
                'section' => $student->section,
                'current_semester' => $student->semester ? $student->semester->name : 'N/A',
                'latest_exam_name' => $res && $res->semesterExam ? $res->semesterExam->name : ($res ? $res->exam_name : 'N/A'),
                'has_result' => $res !== null,
                'result_id' => $res ? $res->id : null,
                'gpa' => $res ? $res->gpa : null,
                'grade' => $res ? $res->grade : null,
                'pass_status' => $res ? $res->pass_status : null,
            ];
        });

        $reportCard = $students->setCollection($reportCardData);

        return Inertia::render('results/Index', [
            'reportCard' => $reportCard,
            'programs' => Program::orderBy('name')->pluck('name')->toArray(),
            'sections' => ['A', 'B', 'C'],
            'currentFilters' => [
                'program_name' => $programName ?? '',
                'section' => $section ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Show Marks Entry Sheet.
     */
    public function marksEntry(Request $request): Response
    {
        $programName = $request->input('program_name', '');
        $section = $request->input('section', 'A');
        $search = $request->input('search');

        $query = Student::with(['semester', 'examResults']);

        if ($programName !== '') {
            $query->where('program_name', $programName);
        }

        if ($section !== '') {
            $query->where('section', $section);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_en', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('roll_number')->paginate(10)->withQueryString();

        $subjects = Subject::orderBy('name')->pluck('name')->toArray();
        if (empty($subjects)) {
            $subjects = ['Mathematics', 'English', 'Science', 'Social Science'];
        }

        $semesters = Semester::with('exams')->orderBy('sort_order')->get();

        $mappedStudents = collect($students->items())->map(function ($s) {
            $examResults = $s->examResults
                ->map(function ($res) {
                    return [
                        'semester_id' => $res->semester_id,
                        'semester_exam_id' => $res->semester_exam_id,
                        'marks' => $res->marks,
                        'pass_status' => $res->pass_status,
                        'remarks' => $res->remarks,
                    ];
                })->values()->toArray();

            return [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'full_name_en' => $s->full_name_en,
                'roll_number' => $s->roll_number,
                'section' => $s->section,
                'program_name' => $s->program_name,
                'current_semester' => $s->semester ? $s->semester->name : 'N/A',
                'semester_id' => $s->semester_id,
                'exam_results' => $examResults,
            ];
        });

        $studentsPaginated = $students->setCollection($mappedStudents);

        return Inertia::render('results/MarksEntry', [
            'students' => $studentsPaginated,
            'subjects' => $subjects,
            'semesters' => $semesters,
            'programs' => Program::orderBy('name')->pluck('name')->toArray(),
            'sections' => ['A', 'B', 'C'],
            'program_name' => $programName,
            'section' => $section,
            'currentFilters' => [
                'program_name' => $programName,
                'section' => $section,
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Store student marks and calculate GPA/Grades.
     */
    public function saveMarks(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'semester_id' => 'required|exists:semesters,id',
            'semester_exam_id' => 'required|exists:semester_exams,id',
            'marks' => 'required|array',
            'remarks' => 'nullable|string',
        ]);

        $studentId = $request->input('student_id');
        $student = Student::findOrFail($studentId);
        $semesterId = $request->input('semester_id');
        $semesterExamId = $request->input('semester_exam_id');
        $marks = $request->input('marks');

        $semesterExam = SemesterExam::findOrFail($semesterExamId);

        $totalGp = 0;
        $failed = false;
        $subjectCount = 0;
        $parsedMarks = [];

        foreach ($marks as $subject => $score) {
            if ($score === '' || $score === null) {
                continue;
            }

            $score = (int) $score;
            $parsedMarks[$subject] = $score;
            $subjectCount++;

            // Grade Point logic
            if ($score >= 80) {
                $gp = 5.0;
            } elseif ($score >= 70) {
                $gp = 4.0;
            } elseif ($score >= 60) {
                $gp = 3.5;
            } elseif ($score >= 50) {
                $gp = 3.0;
            } elseif ($score >= 40) {
                $gp = 2.0;
            } elseif ($score >= 33) {
                $gp = 1.0;
            } else {
                $gp = 0.0;
                $failed = true;
            }

            $totalGp += $gp;
        }

        if ($subjectCount === 0) {
            return redirect()->back()->withErrors(['marks' => 'Please enter marks for at least one subject.']);
        }

        $gpa = $failed ? 0.0 : round($totalGp / $subjectCount, 2);

        // Grade calculation
        if ($gpa >= 5.0) {
            $grade = 'A+';
        } elseif ($gpa >= 4.0) {
            $grade = 'A';
        } elseif ($gpa >= 3.5) {
            $grade = 'A-';
        } elseif ($gpa >= 3.0) {
            $grade = 'B';
        } elseif ($gpa >= 2.0) {
            $grade = 'C';
        } elseif ($gpa >= 1.0) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        $result = ExamResult::updateOrCreate(
            [
                'student_id' => $studentId,
                'semester_id' => $semesterId,
                'semester_exam_id' => $semesterExamId,
            ],
            [
                'exam_name' => $semesterExam->name,
                'program_name' => $student->program_name,
                'section' => $student->section,
                'marks' => $parsedMarks,
                'gpa' => $gpa,
                'grade' => $grade,
                'pass_status' => $failed ? 'fail' : 'pass',
                'remarks' => $request->input('remarks'),
            ]
        );

        // Automatic promotion logic on Final Semester Exam pass
        if ($semesterExam->is_final && $result->pass_status === 'pass') {
            $currentSemester = $student->semester;
            if ($currentSemester) {
                $nextSemester = Semester::where('sort_order', '>', $currentSemester->sort_order)
                    ->orderBy('sort_order', 'asc')
                    ->first();
                if ($nextSemester) {
                    $student->update(['semester_id' => $nextSemester->id]);
                }
            }
        }

        return redirect()->back()->with('success', 'Marks entered and recorded successfully.');
    }

    /**
     * Display student result history.
     */
    public function show(Student $result): Response
    {
        $student = $result;
        $student->load(['semester']);

        $results = ExamResult::with(['semester', 'semesterExam'])
            ->where('student_id', $student->id)
            ->get();

        $history = $results->map(function ($res) {
            $totalMarks = array_sum($res->marks);
            $subjectCount = count($res->marks);
            $percentage = $subjectCount > 0 ? round(($totalMarks / ($subjectCount * 100)) * 100, 2) : 0;

            return [
                'id' => $res->id,
                'exam_name' => $res->semesterExam ? $res->semesterExam->name : $res->exam_name,
                'semester_name' => $res->semester ? $res->semester->name : 'N/A',
                'semester_id' => $res->semester_id,
                'marks' => $res->marks,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'gpa' => $res->gpa,
                'grade' => $res->grade,
                'pass_status' => $res->pass_status,
                'is_final' => $res->semesterExam ? (bool) $res->semesterExam->is_final : false,
                'date' => $res->created_at->format('Y-m-d'),
            ];
        });

        $groupedHistory = $history->groupBy('semester_name');

        return Inertia::render('results/StudentHistory', [
            'student' => $student,
            'groupedHistory' => $groupedHistory,
        ]);
    }

    /**
     * Render Printable Marksheet.
     */
    public function print(ExamResult $result): Response
    {
        $result->load('student');

        return Inertia::render('results/PrintMarksheet', [
            'result' => $result,
        ]);
    }
}

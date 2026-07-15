<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends Controller
{
    /**
     * Display exam results.
     */
    public function index(Request $request): Response
    {
        $programName = $request->input('program_name', 'Science');
        $section = $request->input('section', 'A');
        $examName = $request->input('exam_name', 'First Term Exam');
        $search = $request->input('search');

        $studentsQuery = Student::where('program_name', $programName)
            ->where('section', $section);

        if ($request->filled('search')) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('full_name_en', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('roll_number')
            ->paginate(15)
            ->withQueryString();

        $studentIds = collect($students->items())->pluck('id');
        $results = ExamResult::whereIn('student_id', $studentIds)
            ->where('exam_name', $examName)
            ->get()
            ->keyBy('student_id');

        $reportCardData = collect($students->items())->map(function ($student) use ($results) {
            $res = $results->get($student->id);

            return [
                'student_id' => $student->id,
                'student_uid' => $student->student_id,
                'full_name' => $student->full_name_en,
                'roll_number' => $student->roll_number,
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
            'examNames' => ['First Term Exam', 'Midterm Exam', 'Annual Exam'],
            'currentFilters' => [
                'program_name' => $programName,
                'section' => $section,
                'exam_name' => $examName,
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Show Marks Entry Sheet.
     */
    public function marksEntry(Request $request): Response
    {
        $programName = $request->input('program_name', 'Science');
        $section = $request->input('section', 'A');
        $examName = $request->input('exam_name', 'First Term Exam');

        $students = Student::where('program_name', $programName)
            ->where('section', $section)
            ->orderBy('roll_number')
            ->get();

        $studentIds = $students->pluck('id');
        $results = ExamResult::whereIn('student_id', $studentIds)
            ->where('exam_name', $examName)
            ->get()
            ->keyBy('student_id');

        // Let's load subjects dynamically from database
        $subjects = Subject::orderBy('name')->pluck('name')->toArray();
        if (empty($subjects)) {
            $subjects = ['Mathematics', 'English', 'Science', 'Social Science'];
        }

        $marksSheet = $students->map(function ($student) use ($results, $subjects) {
            $res = $results->get($student->id);
            $marks = $res ? $res->marks : array_fill_keys($subjects, '');

            return [
                'student_id' => $student->id,
                'student_uid' => $student->student_id,
                'full_name' => $student->full_name_en,
                'roll_number' => $student->roll_number,
                'marks' => $marks,
                'remarks' => $res ? $res->remarks : '',
            ];
        });

        return Inertia::render('results/MarksEntry', [
            'marksSheet' => $marksSheet,
            'subjects' => $subjects,
            'program_name' => $programName,
            'section' => $section,
            'exam_name' => $examName,
            'programs' => Program::orderBy('name')->pluck('name')->toArray(),
            'sections' => ['A', 'B', 'C'],
            'examNames' => ['First Term Exam', 'Midterm Exam', 'Annual Exam'],
        ]);
    }

    /**
     * Store bulk student marks and calculate GPA/Grades.
     */
    public function saveMarks(Request $request): RedirectResponse
    {
        $request->validate([
            'program_name' => 'required|string',
            'section' => 'required|string',
            'exam_name' => 'required|string',
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.marks' => 'required|array',
            'results.*.remarks' => 'nullable|string',
        ]);

        $examName = $request->input('exam_name');
        $programName = $request->input('program_name');
        $section = $request->input('section');

        foreach ($request->input('results') as $record) {
            $studentId = $record['student_id'];
            $marks = $record['marks']; // Subject -> Marks map

            $totalGp = 0;
            $failed = false;
            $subjectCount = 0;

            $parsedMarks = [];
            foreach ($marks as $subject => $score) {
                if ($score === '' || $score === null) {
                    continue; // Skip unfilled marks
                }

                $score = (int) $score;
                $parsedMarks[$subject] = $score;
                $subjectCount++;

                // Subject Grade Point
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
                continue; // no marks entered for this student
            }

            $gpa = $failed ? 0.0 : round($totalGp / $subjectCount, 2);

            // Overall Grade
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

            ExamResult::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'exam_name' => $examName,
                ],
                [
                    'program_name' => $programName,
                    'section' => $section,
                    'marks' => $parsedMarks,
                    'gpa' => $gpa,
                    'grade' => $grade,
                    'pass_status' => $failed ? 'fail' : 'pass',
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('results.index', [
            'program_name' => $programName,
            'section' => $section,
            'exam_name' => $examName,
        ])->with('success', 'Marks entered and GPA calculated successfully.');
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

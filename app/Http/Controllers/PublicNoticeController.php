<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\CampusLifeItem;
use App\Models\ExamResult;
use App\Models\FeePayment;
use App\Models\Notice;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicNoticeController extends Controller
{
    /**
     * Show the public homepage with notices, results, and fee query options.
     */
    public function welcome(Request $request): Response
    {
        // 1. Get active banners for the slider
        $banners = Banner::active()->get();

        // 2. Get active campus life gallery items
        $campusLifeItems = CampusLifeItem::active()->get();

        // 2. Get active notices
        $today = date('Y-m-d');
        $notices = Notice::where('status', 'active')
            ->where('publish_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $today);
            })
            ->orderBy('publish_date', 'desc')
            ->get();

        // Get programs with subjects
        $programs = Program::with('subjects')->orderBy('name')->get();

        // 2. Handle Result Query
        $resultData = null;
        $resultError = null;
        if ($request->filled('result_student_id') && $request->filled('result_exam_name')) {
            $studentUid = $request->input('result_student_id');
            $examName = $request->input('result_exam_name');

            $student = Student::where('student_id', $studentUid)->first();

            if ($student !== null) {
                $result = ExamResult::where('student_id', $student->id)
                    ->where('exam_name', $examName)
                    ->first();

                if ($result !== null) {
                    $resultData = [
                        'student' => $student,
                        'result' => $result,
                    ];
                } else {
                    $resultError = 'No result found for the specified exam.';
                }
            } else {
                $resultError = 'Student ID not found.';
            }
        }

        // 3. Handle Fee Query
        $feeData = null;
        $feeError = null;
        if ($request->filled('fee_student_id')) {
            $studentUid = $request->input('fee_student_id');
            $student = Student::where('student_id', $studentUid)->first();

            if ($student !== null) {
                $payments = FeePayment::where('student_id', $student->id)
                    ->orderBy('fee_month', 'desc')
                    ->get();

                $feeData = [
                    'student' => $student,
                    'payments' => $payments,
                ];
            } else {
                $feeError = 'Student ID not found.';
            }
        }

        return Inertia::render('Welcome', [
            'notices' => $notices,
            'banners' => $banners,
            'campusLifeItems' => $campusLifeItems,
            'programs' => $programs,
            'resultData' => $resultData,
            'resultError' => $resultError,
            'feeData' => $feeData,
            'feeError' => $feeError,
            'filters' => $request->only(['result_student_id', 'result_exam_name', 'fee_student_id']),
        ]);
    }

    /**
     * Show the dedicated public student result lookup page.
     */
    public function result(Request $request): Response
    {
        $semesters = Semester::with(['exams' => fn ($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get();
        $sections = ExamResult::select('section')->distinct()->orderBy('section')->pluck('section');

        $lookupType = $request->input('lookup_type', 'single');
        $resultData = null;
        $transcriptData = null;
        $resultError = null;

        if ($request->filled('student_id')) {
            $student = Student::where('student_id', $request->input('student_id'))->first();

            if ($student !== null) {
                if ($lookupType === 'transcript') {
                    // Get all ExamResults for this student
                    $results = ExamResult::with(['semester', 'semesterExam'])
                        ->where('student_id', $student->id)
                        ->get();

                    if ($results->isNotEmpty()) {
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

                        $transcriptData = [
                            'student' => $student,
                            'groupedHistory' => $groupedHistory,
                        ];
                    } else {
                        $resultError = 'No results found for this student across any semesters.';
                    }
                } else {
                    if ($request->filled('semester_exam_id')) {
                        $query = ExamResult::where('student_id', $student->id)
                            ->where('semester_exam_id', $request->input('semester_exam_id'));

                        if ($request->filled('section')) {
                            $query->where('section', $request->input('section'));
                        }

                        $result = $query->first();

                        if ($result !== null) {
                            $resultData = [
                                'student' => $student,
                                'result' => $result,
                            ];
                        } else {
                            $resultError = 'No result found for the specified filters. Please check and try again.';
                        }
                    } else {
                        $resultError = 'Please select an exam to query a single result.';
                    }
                }
            } else {
                $resultError = 'Student ID not found. Please double-check and try again.';
            }
        }

        return Inertia::render('PublicResult', [
            'semesters' => $semesters,
            'sections' => $sections,
            'resultData' => $resultData,
            'transcriptData' => $transcriptData,
            'resultError' => $resultError,
            'filters' => $request->only(['student_id', 'semester_id', 'semester_exam_id', 'section', 'lookup_type']),
        ]);
    }

    /**
     * Show notice details page.
     */
    public function show(string $slug): Response
    {
        $notice = Notice::where('slug', $slug)->firstOrFail();

        return Inertia::render('PublicNoticeDetails', [
            'notice' => $notice,
        ]);
    }
}

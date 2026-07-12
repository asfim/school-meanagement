<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\FeePayment;
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
        // 1. Get active notices
        $today = date('Y-m-d');
        $notices = Notice::where('status', 'active')
            ->where('publish_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', $today);
            })
            ->orderBy('publish_date', 'desc')
            ->get();

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
            'resultData' => $resultData,
            'resultError' => $resultError,
            'feeData' => $feeData,
            'feeError' => $feeError,
            'filters' => $request->only(['result_student_id', 'result_exam_name', 'fee_student_id']),
        ]);
    }
}

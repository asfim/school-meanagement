<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\CampusLifeItem;
use App\Models\ExamResult;
use App\Models\FeePayment;
use App\Models\Notice;
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
        $resultData = null;
        $resultError = null;

        if ($request->filled('student_id') && $request->filled('exam_name')) {
            $student = Student::where('student_id', $request->input('student_id'))->first();

            if ($student !== null) {
                $result = ExamResult::where('student_id', $student->id)
                    ->where('exam_name', $request->input('exam_name'))
                    ->first();

                if ($result !== null) {
                    $resultData = [
                        'student' => $student,
                        'result' => $result,
                    ];
                } else {
                    $resultError = 'No result found for the specified exam. Please check the exam name and try again.';
                }
            } else {
                $resultError = 'Student ID not found. Please double-check and try again.';
            }
        }

        return Inertia::render('PublicResult', [
            'resultData' => $resultData,
            'resultError' => $resultError,
            'filters' => $request->only(['student_id', 'exam_name']),
        ]);
    }
}

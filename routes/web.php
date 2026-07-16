<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CampusLifeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicNoticeController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SemesterExamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Models\FeePayment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicNoticeController::class, 'welcome'])->name('home');
Route::get('/result', [PublicNoticeController::class, 'result'])->name('public.result');
Route::get('/notice/{slug}', [PublicNoticeController::class, 'show'])->name('public.notice.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $studentCount = Student::count();
        $teacherCount = Teacher::count();
        $totalFees = (float) FeePayment::sum('amount_paid');

        $semestersData = [];
        $semesters = Semester::orderBy('id')->get();
        foreach ($semesters as $semester) {
            $students = Student::where('semester_id', $semester->id)
                ->with(['examResults.semesterExam'])
                ->get();

            $rankedStudents = $students->map(function ($student) {
                $cgpa = $student->calculateCgpa();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name_en,
                    'cgpa' => $cgpa,
                    'grade' => $student->calculateCgpaGrade($cgpa),
                ];
            })
                ->sortByDesc('cgpa')
                ->take(3)
                ->values()
                ->all();

            $semestersData[] = [
                'id' => $semester->id,
                'name' => $semester->name,
                'students' => $rankedStudents,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'student_count' => $studentCount,
                'teacher_count' => $teacherCount,
                'total_tuition_fees' => $totalFees,
            ],
            'semesters_best_students' => $semestersData,
        ]);
    })->name('dashboard');

    // Students
    Route::post('students/{student}/issue-tc', [StudentController::class, 'issueTc'])->name('students.issue-tc');
    Route::resource('students', StudentController::class);

    // Teachers
    Route::get('teachers/attendance', [TeacherController::class, 'attendance'])->name('teachers.attendance');
    Route::post('teachers/attendance', [TeacherController::class, 'saveAttendance'])->name('teachers.save-attendance');
    Route::resource('teachers', TeacherController::class);

    // Results
    Route::get('results/marks-entry', [ResultController::class, 'marksEntry'])->name('results.marks-entry');
    Route::post('results/marks-entry', [ResultController::class, 'saveMarks'])->name('results.save-marks');
    Route::get('results/{result}/print', [ResultController::class, 'print'])->name('results.print');
    Route::resource('results', ResultController::class)->only(['index', 'show']);

    // Semesters & Exams CRUD
    Route::resource('semesters', SemesterController::class);
    Route::post('semesters/{semester}/exams', [SemesterExamController::class, 'store'])->name('semesters.exams.store');
    Route::put('semester-exams/{semesterExam}', [SemesterExamController::class, 'update'])->name('semester-exams.update');
    Route::delete('semester-exams/{semesterExam}', [SemesterExamController::class, 'destroy'])->name('semester-exams.destroy');

    // Fees
    Route::get('fees/billing', [FeeController::class, 'billing'])->name('fees.billing');
    Route::post('fees/billing', [FeeController::class, 'generateBilling'])->name('fees.generate-billing');
    Route::post('fees/collect', [FeeController::class, 'collect'])->name('fees.collect');
    Route::get('fees/{payment}/receipt', [FeeController::class, 'printReceipt'])->name('fees.print-receipt');
    Route::resource('fees', FeeController::class)->only(['index']);

    // Notices
    Route::resource('notices', NoticeController::class);

    // Banners
    Route::resource('banners', BannerController::class)->except(['show']);

    // Campus Life
    Route::resource('campus-life', CampusLifeController::class)->except(['show']);

    // Subjects
    Route::resource('subjects', SubjectController::class);

    // Programs
    Route::resource('programs', ProgramController::class);

    // About Us Page Management
    Route::get('about-us', [AboutUsController::class, 'edit'])->name('about-us.edit');
    Route::post('about-us', [AboutUsController::class, 'update'])->name('about-us.update');

    // Contact Us Page Management
    Route::get('contact-us', [ContactController::class, 'edit'])->name('contact-us.edit');
    Route::post('contact-us', [ContactController::class, 'update'])->name('contact-us.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

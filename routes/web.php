<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\CampusLifeController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PublicNoticeController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicNoticeController::class, 'welcome'])->name('home');
Route::get('/result', [PublicNoticeController::class, 'result'])->name('public.result');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
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
    Route::resource('results', ResultController::class)->only(['index']);

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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

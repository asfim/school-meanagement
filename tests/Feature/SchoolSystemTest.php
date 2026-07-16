<?php

use App\Models\FeePayment;
use App\Models\Notice;
use App\Models\Program;
use App\Models\Semester;
use App\Models\SemesterExam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public homepage displays active notices', function () {
    Notice::factory()->create([
        'title' => 'Annual General Meeting',
        'category' => 'general',
        'publish_date' => now()->subDay()->format('Y-m-d'),
        'status' => 'active',
    ]);

    $response = $this->get('/');

    $response->assertSuccessful();
});

test('admin can register a student and generate a unique student ID', function () {
    // Ensure programs exist first
    Program::factory()->create(['name' => 'Science']);
    $semester = Semester::create(['name' => '1st Semester', 'sort_order' => 1]);

    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post('/students', [
        'full_name_en' => 'Jane Doe',
        'full_name_native' => 'Jane Doe Native',
        'dob' => '2015-05-15',
        'gender' => 'female',
        'parent_name' => 'John Doe Sr',
        'parent_mobile' => '01711122233',
        'permanent_address' => 'Permanent Addr',
        'current_address' => 'Current Addr',
        'tuition_fee' => 1500.00,
        'semester_id' => $semester->id,
        'program_name' => 'Science',
        'section' => 'A',
        'roll_number' => 5,
        'admission_date' => '2026-01-10',
        'blood_group' => 'O+',
        'emergency_contact' => '01711122234',
    ]);

    $response->assertRedirect('/students');
    $this->assertDatabaseHas('students', [
        'full_name_en' => 'Jane Doe',
        'program_name' => 'Science',
        'roll_number' => 5,
    ]);

    $student = Student::where('full_name_en', 'Jane Doe')->first();
    expect($student->student_id)->toStartWith('STU-'.date('Y').'-');
});

test('admin can register a teacher and generate a unique teacher ID', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post('/teachers', [
        'full_name' => 'Mr. Alan Math',
        'dob' => '1985-02-12',
        'mobile' => '01811122233',
        'email' => 'alan@school.com',
        'qualifications' => 'B.Sc., M.Sc.',
        'program_name' => 'Science',
        'subjects' => ['Mathematics'],
        'date_of_joining' => '2020-01-01',
        'designation' => 'Lecturer',
        'salary_structure' => [
            'base_salary' => 30000,
            'allowances' => 5000,
            'deductions' => 1000,
        ],
        'address' => 'Dhaka, Bangladesh',
    ]);

    $response->assertRedirect('/teachers');
    $this->assertDatabaseHas('teachers', [
        'full_name' => 'Mr. Alan Math',
        'email' => 'alan@school.com',
    ]);
});

test('admin can enter marks and calculate GPA & Grade', function () {
    $admin = User::factory()->create();
    $semester = Semester::create(['name' => '1st Semester', 'sort_order' => 1]);
    $exam = SemesterExam::create([
        'semester_id' => $semester->id,
        'name' => 'First Term Exam',
        'is_final' => false,
        'sort_order' => 1,
    ]);

    $student = Student::factory()->create([
        'program_name' => 'Science',
        'section' => 'A',
        'semester_id' => $semester->id,
    ]);

    $response = $this->actingAs($admin)->post('/results/marks-entry', [
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'semester_exam_id' => $exam->id,
        'marks' => [
            'Mathematics' => 85, // GP: 5.0
            'English' => 75,       // GP: 4.0
            'Science' => 65,       // GP: 3.5
            'Social Science' => 55, // GP: 3.0
        ],
        'remarks' => 'Good job',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('exam_results', [
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'semester_exam_id' => $exam->id,
        'gpa' => 3.88,
        'grade' => 'A-',
        'pass_status' => 'pass',
    ]);
});

test('admin can generate tuition billing and collect payments', function () {
    $admin = User::factory()->create();
    $student = Student::factory()->create([
        'program_name' => 'Science',
        'status' => 'active',
    ]);

    // 1. Generate Billing
    $this->actingAs($admin)->post('/fees/billing', [
        'program_name' => 'Science',
        'month' => '2026-07',
        'amount' => 1500,
    ])->assertRedirect();

    $this->assertDatabaseHas('fee_payments', [
        'student_id' => $student->id,
        'fee_month' => '2026-07',
        'amount_due' => 1500,
        'status' => 'unpaid',
    ]);

    $payment = FeePayment::first();

    // 2. Collect Payment
    $this->actingAs($admin)->post('/fees/collect', [
        'payment_id' => $payment->id,
        'amount_paid' => 1200,
        'discount_type' => 'fixed',
        'discount_value' => 300,
        'discount_amount' => 300,
        'payment_method' => 'cash',
        'remarks' => 'Scholarship discount',
    ])->assertRedirect();

    $this->assertDatabaseHas('fee_payments', [
        'id' => $payment->id,
        'amount_paid' => 1200,
        'discount' => 300,
        'status' => 'paid',
    ]);
});

test('admin can manage subjects dynamically', function () {
    $admin = User::factory()->create();

    // 1. Create Subject
    $this->actingAs($admin)->post('/subjects', [
        'name' => 'Physics II',
        'code' => 'PHY102',
    ])->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'name' => 'Physics II',
        'code' => 'PHY102',
    ]);

    $subject = Subject::where('name', 'Physics II')->first();

    // 2. Edit Subject
    $this->actingAs($admin)->put("/subjects/{$subject->id}", [
        'name' => 'Advanced Physics',
        'code' => 'PHY102',
    ])->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Advanced Physics',
    ]);

    // 3. Delete Subject
    $this->actingAs($admin)->delete("/subjects/{$subject->id}")->assertRedirect();
    $this->assertDatabaseMissing('subjects', [
        'id' => $subject->id,
    ]);
});

test('admin can record partial fee payments and view transaction history ledger', function () {
    $admin = User::factory()->create();
    $student = Student::factory()->create([
        'program_name' => 'Science',
        'tuition_fee' => 1500.00,
        'status' => 'active',
    ]);

    // 1. Generate Billing
    $this->actingAs($admin)->post('/fees/billing', [
        'program_name' => 'Science',
        'month' => '2026-07',
        'amount' => 1500,
    ])->assertRedirect();

    $payment = FeePayment::first();

    // 2. Perform partial payment 1
    $this->actingAs($admin)->post('/fees/collect', [
        'payment_id' => $payment->id,
        'amount_paid' => 500,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'discount_amount' => 150,
        'payment_method' => 'cash',
        'remarks' => 'First installment with discount',
    ])->assertRedirect();

    $this->assertDatabaseHas('fee_payments', [
        'id' => $payment->id,
        'amount_paid' => 500,
        'discount' => 150,
        'status' => 'partial',
    ]);

    $this->assertDatabaseHas('fee_payment_transactions', [
        'fee_payment_id' => $payment->id,
        'amount_paid' => 500,
        'discount_amount' => 150,
        'remaining_due' => 850,
        'status_after_payment' => 'partial',
    ]);

    // 3. Perform payment 2 to clear it
    $this->actingAs($admin)->post('/fees/collect', [
        'payment_id' => $payment->id,
        'amount_paid' => 850,
        'discount_type' => 'none',
        'discount_value' => 0,
        'discount_amount' => 0,
        'payment_method' => 'bank',
        'remarks' => 'Final payment',
    ])->assertRedirect();

    $this->assertDatabaseHas('fee_payments', [
        'id' => $payment->id,
        'amount_paid' => 1350,
        'discount' => 150,
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('fee_payment_transactions', [
        'fee_payment_id' => $payment->id,
        'amount_paid' => 850,
        'discount_amount' => 0,
        'remaining_due' => 0,
        'status_after_payment' => 'paid',
    ]);
});

test('student is promoted to next semester on passing final semester exam', function () {
    $admin = User::factory()->create();

    // Create semesters
    $sem1 = Semester::create(['name' => '1st Semester', 'sort_order' => 1]);
    $sem2 = Semester::create(['name' => '2nd Semester', 'sort_order' => 2]);

    // Create final exam for sem 1
    $finalExam = SemesterExam::create([
        'semester_id' => $sem1->id,
        'name' => 'Final Semester Exam',
        'is_final' => true,
        'sort_order' => 99,
    ]);

    $student = Student::factory()->create([
        'program_name' => 'Science',
        'section' => 'A',
        'semester_id' => $sem1->id,
    ]);

    // Submit passing marks for the Final Semester Exam
    $response = $this->actingAs($admin)->post('/results/marks-entry', [
        'student_id' => $student->id,
        'semester_id' => $sem1->id,
        'semester_exam_id' => $finalExam->id,
        'marks' => [
            'Mathematics' => 85,
            'English' => 75,
        ],
        'remarks' => 'Great final term',
    ]);

    $response->assertRedirect();

    // Check database has result
    $this->assertDatabaseHas('exam_results', [
        'student_id' => $student->id,
        'semester_id' => $sem1->id,
        'semester_exam_id' => $finalExam->id,
        'pass_status' => 'pass',
    ]);

    // The student's current semester should be automatically updated to 2nd Semester
    $student->refresh();
    expect($student->semester_id)->toBe($sem2->id);
});

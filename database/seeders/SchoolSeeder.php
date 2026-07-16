<?php

namespace Database\Seeders;

use App\Models\ExamResult;
use App\Models\FeePayment;
use App\Models\Notice;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create default admin if not exists
        if (! User::where('email', 'ipi.67055@gmail.com')->exists()) {
            User::create([
                'name' => 'School Admin',
                'email' => 'ipi.67055@gmail.com',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]);
        }

        // 1.5 Seed default programs
        $defaultPrograms = [
            ['name' => 'Science', 'code' => 'SCI', 'duration_years' => 2],
            ['name' => 'Arts', 'code' => 'ART', 'duration_years' => 2],
            ['name' => 'Commerce', 'code' => 'COM', 'duration_years' => 2],
            ['name' => 'Computer Science', 'code' => 'CSE', 'duration_years' => 4],
            ['name' => 'Business Administration', 'code' => 'BBA', 'duration_years' => 4],
        ];
        foreach ($defaultPrograms as $prog) {
            Program::updateOrCreate(['name' => $prog['name']], $prog);
        }

        $scienceProg = Program::where('code', 'SCI')->first();
        $artsProg = Program::where('code', 'ART')->first();

        // Seed default subjects
        $defaultSubjects = [
            ['name' => 'Mathematics', 'code' => 'MATH101', 'program_id' => $scienceProg->id],
            ['name' => 'English', 'code' => 'ENG101', 'program_id' => $artsProg->id],
            ['name' => 'Science', 'code' => 'SCI101', 'program_id' => $scienceProg->id],
            ['name' => 'Social Science', 'code' => 'SOC101', 'program_id' => $artsProg->id],
            ['name' => 'Physics', 'code' => 'PHY101', 'program_id' => $scienceProg->id],
            ['name' => 'Chemistry', 'code' => 'CHE101', 'program_id' => $scienceProg->id],
            ['name' => 'Biology', 'code' => 'BIO101', 'program_id' => $scienceProg->id],
            ['name' => 'History', 'code' => 'HIS101', 'program_id' => $artsProg->id],
            ['name' => 'Geography', 'code' => 'GEO101', 'program_id' => $artsProg->id],
        ];

        foreach ($defaultSubjects as $sub) {
            Subject::updateOrCreate(['name' => $sub['name']], $sub);
        }

        // 2. Create Teachers
        $teachersData = [
            [
                'full_name' => 'John Doe',
                'designation' => 'Senior Teacher (Mathematics)',
                'email' => 'john.math@school.com',
                'subjects' => ['Mathematics', 'General Science'],
                'program_name' => 'Science',
            ],
            [
                'full_name' => 'Sarah Connor',
                'designation' => 'Assistant Teacher (English)',
                'email' => 'sarah.english@school.com',
                'subjects' => ['English', 'Social Science'],
                'program_name' => 'Arts',
            ],
            [
                'full_name' => 'Alan Turing',
                'designation' => 'Lecturer (Physics)',
                'email' => 'alan.physics@school.com',
                'subjects' => ['Physics', 'Mathematics'],
                'program_name' => 'Science',
            ],
            [
                'full_name' => 'Marie Curie',
                'designation' => 'Senior Teacher (Chemistry)',
                'email' => 'marie.chemistry@school.com',
                'subjects' => ['Chemistry', 'Biology'],
                'program_name' => 'Science',
            ],
            [
                'full_name' => 'Albert Einstein',
                'designation' => 'Head of Science Department',
                'email' => 'albert.science@school.com',
                'subjects' => ['General Science', 'Physics'],
                'program_name' => 'Science',
            ],
        ];

        $teachers = [];
        foreach ($teachersData as $idx => $t) {
            $teacherId = 'TCH-'.date('Y').'-'.sprintf('%04d', $idx + 1);
            $existing = Teacher::where('teacher_id', $teacherId)->orWhere('email', $t['email'])->first();
            if ($existing) {
                $teachers[] = $existing;
            } else {
                $teachers[] = Teacher::factory()->create([
                    'full_name' => $t['full_name'],
                    'email' => $t['email'],
                    'designation' => $t['designation'],
                    'subjects' => $t['subjects'],
                    'program_name' => $t['program_name'],
                    'teacher_id' => $teacherId,
                ]);
            }
        }

        // 3. Create Students
        $programsList = ['Science', 'Arts', 'Commerce', 'Computer Science', 'Business Administration'];
        $sections = ['A', 'B'];
        $students = [];

        $studentCounter = 1;
        foreach ($programsList as $programName) {
            foreach ($sections as $section) {
                // Create 3 students per program & section
                for ($roll = 1; $roll <= 3; $roll++) {
                    $studentId = 'STU-'.date('Y').'-'.sprintf('%04d', $studentCounter++);
                    $existing = Student::where('student_id', $studentId)->first();
                    if ($existing) {
                        $students[] = $existing;
                    } else {
                        $students[] = Student::factory()->create([
                            'program_name' => $programName,
                            'section' => $section,
                            'roll_number' => $roll,
                            'student_id' => $studentId,
                        ]);
                    }
                }
            }
        }

        // 4. Create Exam Results
        $subjects = ['Mathematics', 'English', 'Science', 'Social Science'];

        foreach ($students as $student) {
            if (ExamResult::where('student_id', $student->id)->where('exam_name', 'First Term Exam')->exists()) {
                continue;
            }

            // Generate First Term Exam
            $marks = [];
            $totalGp = 0;
            $failed = false;

            foreach ($subjects as $sub) {
                $score = rand(40, 98); // ensure they pass at least
                if (rand(1, 20) === 20) {
                    $score = rand(20, 32); // rarely fail
                }

                $marks[$sub] = $score;

                // Subject GPA
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

            $gpa = $failed ? 0.0 : round($totalGp / count($subjects), 2);

            // Final Grade
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

            ExamResult::create([
                'student_id' => $student->id,
                'exam_name' => 'First Term Exam',
                'program_name' => $student->program_name,
                'section' => $student->section,
                'marks' => $marks,
                'gpa' => $gpa,
                'grade' => $grade,
                'pass_status' => $failed ? 'fail' : 'pass',
                'remarks' => $gpa >= 4.0 ? 'Excellent performance.' : ($gpa >= 2.0 ? 'Satisfactory. Keep it up.' : 'Needs improvement.'),
                'merit_position' => rand(1, 30),
            ]);
        }

        // 5. Create Fee Payments
        $months = ['2026-05', '2026-06', '2026-07'];
        $receiptCounter = 10001;

        foreach ($students as $student) {
            // Assign program monthly fee
            $monthlyFee = 1500;
            if ($student->program_name === 'Computer Science' || $student->program_name === 'Business Administration') {
                $monthlyFee = 2000;
            }

            // Month 1: May (Paid)
            if (! FeePayment::where('student_id', $student->id)->where('fee_month', '2026-05')->exists()) {
                FeePayment::create([
                    'student_id' => $student->id,
                    'fee_month' => '2026-05',
                    'amount_due' => $monthlyFee,
                    'amount_paid' => $monthlyFee,
                    'discount' => 0.00,
                    'payment_date' => date('Y-05-10'),
                    'payment_method' => 'cash',
                    'receipt_number' => 'REC-2026-'.($receiptCounter++),
                    'status' => 'paid',
                    'remarks' => 'Paid in full.',
                ]);
            } else {
                $receiptCounter++;
            }

            // Month 2: June (Paid with discount, or partial, or paid)
            if (! FeePayment::where('student_id', $student->id)->where('fee_month', '2026-06')->exists()) {
                $isPaid = rand(1, 10) > 2; // 80% paid
                if ($isPaid) {
                    $discount = rand(1, 10) === 10 ? 200.00 : 0.00;
                    FeePayment::create([
                        'student_id' => $student->id,
                        'fee_month' => '2026-06',
                        'amount_due' => $monthlyFee - $discount,
                        'amount_paid' => $monthlyFee - $discount,
                        'discount' => $discount,
                        'payment_date' => date('Y-06-12'),
                        'payment_method' => 'mobile_banking',
                        'receipt_number' => 'REC-2026-'.($receiptCounter++),
                        'status' => 'paid',
                        'remarks' => $discount > 0 ? 'Discount applied.' : 'Paid in full.',
                    ]);
                } else {
                    FeePayment::create([
                        'student_id' => $student->id,
                        'fee_month' => '2026-06',
                        'amount_due' => $monthlyFee,
                        'amount_paid' => 0,
                        'discount' => 0,
                        'payment_date' => null,
                        'payment_method' => null,
                        'receipt_number' => null,
                        'status' => 'unpaid',
                        'remarks' => 'Payment overdue.',
                    ]);
                }
            } else {
                $receiptCounter++;
            }

            // Month 3: July (Current Month - mostly Unpaid)
            if (! FeePayment::where('student_id', $student->id)->where('fee_month', '2026-07')->exists()) {
                FeePayment::create([
                    'student_id' => $student->id,
                    'fee_month' => '2026-07',
                    'amount_due' => $monthlyFee,
                    'amount_paid' => 0,
                    'discount' => 0,
                    'payment_date' => null,
                    'payment_method' => null,
                    'receipt_number' => null,
                    'status' => 'unpaid',
                    'remarks' => 'Monthly billing generated.',
                ]);
            }
        }

        // 6. Create Notices
        if (! Notice::where('title', 'First Term Exam Schedule 2026')->exists()) {
            Notice::create([
                'title' => 'First Term Exam Schedule 2026',
                'description' => 'The First Term Examination for all academic programs will commence from August 1, 2026. Detailed schedules have been distributed to classroom coordinators. Please ensure all tuition dues are cleared prior to collecting admit cards.',
                'category' => 'exam',
                'publish_date' => date('Y-07-01'),
                'expiry_date' => date('Y-08-15'),
                'target_audience' => 'all',
                'posted_by' => 'Exam Controller',
                'status' => 'active',
            ]);
        }

        if (! Notice::where('title', 'Summer Vacation Holiday Notice')->exists()) {
            Notice::create([
                'title' => 'Summer Vacation Holiday Notice',
                'description' => 'This is to inform all students, parents, and teachers that the school will remain closed from July 15, 2026, to July 25, 2026, on account of the Summer Vacation. Regular school operations will resume on July 26, 2026.',
                'category' => 'holiday',
                'publish_date' => date('Y-07-10'),
                'expiry_date' => date('Y-07-26'),
                'target_audience' => 'all',
                'posted_by' => 'Principal Office',
                'status' => 'active',
            ]);
        }

        if (! Notice::where('title', 'Teacher-Parent Meeting (PTM)')->exists()) {
            Notice::create([
                'title' => 'Teacher-Parent Meeting (PTM)',
                'description' => 'A parent-teacher meeting is scheduled for Saturday, July 18, 2026, at the school auditorium from 10:00 AM to 1:00 PM. Parents are requested to attend to discuss their child\'s progress and review first term performance reports.',
                'category' => 'event',
                'publish_date' => date('Y-07-12'),
                'expiry_date' => date('Y-07-19'),
                'target_audience' => 'parents',
                'posted_by' => 'Academic Coordinator',
                'status' => 'active',
            ]);
        }

        if (! Notice::where('title', 'Admission Notice: Session 2026-27')->exists()) {
            Notice::create([
                'title' => 'Admission Notice: Session 2026-27',
                'description' => 'Online registrations for the upcoming academic session are now open for Class 6 to 9. The admission form, eligibility criteria, and fee structure details are available in the public downloads section. Last date for form submission is September 10, 2026.',
                'category' => 'admission',
                'publish_date' => date('Y-07-05'),
                'expiry_date' => date('Y-09-10'),
                'target_audience' => 'all',
                'posted_by' => 'Admissions Office',
                'status' => 'active',
            ]);
        }
    }
}

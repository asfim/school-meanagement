<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeeController extends Controller
{
    /**
     * Display fee collection directory.
     */
    public function index(Request $request): Response
    {
        $programName = $request->input('program_name', 'Science');
        $section = $request->input('section', 'A');
        $month = $request->input('month', date('Y-m'));
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
        $payments = FeePayment::whereIn('student_id', $studentIds)
            ->where('fee_month', $month)
            ->get()
            ->keyBy('student_id');

        $feeSheetData = collect($students->items())->map(function ($student) use ($payments) {
            $pay = $payments->get($student->id);

            return [
                'student_id' => $student->id,
                'student_uid' => $student->student_id,
                'full_name' => $student->full_name_en,
                'roll_number' => $student->roll_number,
                'has_billing' => $pay !== null,
                'payment_id' => $pay ? $pay->id : null,
                'amount_due' => $pay ? $pay->amount_due : 0.00,
                'amount_paid' => $pay ? $pay->amount_paid : 0.00,
                'discount' => $pay ? $pay->discount : 0.00,
                'status' => $pay ? $pay->status : 'unbilled',
                'receipt_number' => $pay ? $pay->receipt_number : null,
                'payment_date' => $pay && $pay->payment_date ? $pay->payment_date->format('Y-m-d') : null,
            ];
        });

        $feeSheet = $students->setCollection($feeSheetData);

        // Summary metrics
        $totalCollected = FeePayment::where('fee_month', $month)->sum('amount_paid');
        $totalDues = FeePayment::where('fee_month', $month)->where('status', '!=', 'paid')->sum('amount_due');
        $totalDiscounts = FeePayment::where('fee_month', $month)->sum('discount');

        return Inertia::render('fees/Index', [
            'feeSheet' => $feeSheet,
            'programs' => Program::orderBy('name')->pluck('name')->toArray(),
            'sections' => ['A', 'B', 'C'],
            'currentFilters' => [
                'program_name' => $programName,
                'section' => $section,
                'month' => $month,
                'search' => $search ?? '',
            ],
            'summary' => [
                'total_collected' => $totalCollected,
                'total_dues' => $totalDues,
                'total_discounts' => $totalDiscounts,
            ],
        ]);
    }

    /**
     * Show Billing page for generating fees.
     */
    public function billing(): Response
    {
        return Inertia::render('fees/Billing', [
            'programs' => Program::orderBy('name')->pluck('name')->toArray(),
        ]);
    }

    /**
     * Generate Monthly Fees for a class.
     */
    public function generateBilling(Request $request): RedirectResponse
    {
        $request->validate([
            'program_name' => 'required|string',
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount' => 'required|numeric|min:0',
        ]);

        $programName = $request->input('program_name');
        $month = $request->input('month');
        $amount = $request->input('amount');

        $students = Student::where('program_name', $programName)->where('status', 'active')->get();

        $count = 0;
        foreach ($students as $student) {
            // Only create if not already exists
            $exists = FeePayment::where('student_id', $student->id)
                ->where('fee_month', $month)
                ->exists();

            if (! $exists) {
                FeePayment::create([
                    'student_id' => $student->id,
                    'fee_month' => $month,
                    'amount_due' => $amount,
                    'amount_paid' => 0.00,
                    'discount' => 0.00,
                    'status' => 'unpaid',
                    'remarks' => 'Monthly billing generated.',
                ]);
                $count++;
            }
        }

        return redirect()->route('fees.index', ['program_name' => $programName, 'month' => $month])
            ->with('success', "Billing generated successfully for {$count} students.");
    }

    /**
     * Collect Tuition Fee.
     */
    public function collect(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_id' => 'required|exists:fee_payments,id',
            'amount_paid' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,bank,mobile_banking',
            'remarks' => 'nullable|string|max:255',
        ]);

        $payment = FeePayment::findOrFail($request->input('payment_id'));

        $newDiscount = $request->input('discount');
        $newPaid = $request->input('amount_paid');

        // Calculate new totals
        $totalPaid = $payment->amount_paid + $newPaid;
        $totalDiscount = $payment->discount + $newDiscount;

        $status = 'unpaid';
        if ($totalPaid >= ($payment->amount_due - $totalDiscount)) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        // Generate receipt number if not already present
        $receiptNumber = $payment->receipt_number;
        if ($receiptNumber === null) {
            $year = date('Y');
            $lastPayment = FeePayment::whereNotNull('receipt_number')->orderBy('id', 'desc')->first();
            $nextNum = 10001;
            if ($lastPayment !== null && preg_match('/REC-\d{4}-(\d+)/', $lastPayment->receipt_number, $matches)) {
                $nextNum = (int) $matches[1] + 1;
            }
            $receiptNumber = "REC-{$year}-{$nextNum}";
        }

        $payment->update([
            'amount_paid' => $totalPaid,
            'discount' => $totalDiscount,
            'status' => $status,
            'payment_date' => now(),
            'payment_method' => $request->input('payment_method'),
            'receipt_number' => $receiptNumber,
            'remarks' => $request->input('remarks'),
        ]);

        return redirect()->back()->with('success', 'Fee collection entered successfully.');
    }

    /**
     * Render Printable Receipt.
     */
    public function printReceipt(FeePayment $payment): Response
    {
        $payment->load('student');

        return Inertia::render('fees/PrintReceipt', [
            'payment' => $payment,
        ]);
    }
}

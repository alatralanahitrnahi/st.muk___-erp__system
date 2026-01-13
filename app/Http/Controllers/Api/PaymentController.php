<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $razorpayKeyId;
    private $razorpayKeySecret;

    public function __construct()
    {
        $this->razorpayKeyId = env('RAZORPAY_KEY_ID', 'rzp_test_1234567890');
        $this->razorpayKeySecret = env('RAZORPAY_KEY_SECRET', 'test_secret_key');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $studentFee = StudentFee::with(['student.user'])->findOrFail($request->student_fee_id);
        
        if ($request->amount > $studentFee->balance_amount) {
            return response()->json(['error' => 'Amount exceeds balance'], 400);
        }

        // Create Razorpay order
        $orderData = [
            'receipt' => 'fee_' . $studentFee->id . '_' . time(),
            'amount' => $request->amount * 100, // Amount in paise
            'currency' => 'INR',
            'notes' => [
                'student_fee_id' => $studentFee->id,
                'student_name' => $studentFee->student->user->name,
                'student_email' => $studentFee->student->user->email,
            ]
        ];

        $order = $this->createRazorpayOrder($orderData);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'key' => $this->razorpayKeyId,
            'student_name' => $studentFee->student->user->name,
            'student_email' => $studentFee->student->user->email,
            'student_phone' => $studentFee->student->user->phone,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:1',
        ]);

        // Verify signature
        $signature = hash_hmac('sha256', 
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id, 
            $this->razorpayKeySecret
        );

        if ($signature !== $request->razorpay_signature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Verify payment with Razorpay
        $paymentDetails = $this->getPaymentDetails($request->razorpay_payment_id);
        
        if ($paymentDetails['status'] !== 'captured') {
            return response()->json(['error' => 'Payment not captured'], 400);
        }

        DB::beginTransaction();
        try {
            $studentFee = StudentFee::findOrFail($request->student_fee_id);
            
            // Record payment
            $payment = FeePayment::create([
                'student_fee_id' => $request->student_fee_id,
                'amount' => $request->amount,
                'payment_date' => now(),
                'payment_mode' => 'online',
                'transaction_id' => $request->razorpay_payment_id,
                'remarks' => 'Razorpay payment - Order: ' . $request->razorpay_order_id,
                'received_by' => $request->user()->id ?? 1,
            ]);

            // Update student fee
            $newPaidAmount = $studentFee->paid_amount + $request->amount;
            $newBalanceAmount = $studentFee->final_amount - $newPaidAmount;
            $status = $newBalanceAmount == 0 ? 'paid' : 'partial';

            $studentFee->update([
                'paid_amount' => $newPaidAmount,
                'balance_amount' => $newBalanceAmount,
                'status' => $status,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment verified and recorded successfully',
                'payment' => $payment,
                'fee_status' => $studentFee->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment verification failed'], 500);
        }
    }

    public function getPaymentHistory(Request $request, $studentId)
    {
        $payments = FeePayment::whereHas('studentFee', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
        ->with(['studentFee'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($payments);
    }

    public function refundPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:fee_payments,id',
            'amount' => 'nullable|numeric|min:1',
            'reason' => 'required|string',
        ]);

        $payment = FeePayment::findOrFail($request->payment_id);
        $refundAmount = $request->amount ?? $payment->amount;

        if ($refundAmount > $payment->amount) {
            return response()->json(['error' => 'Refund amount exceeds payment amount'], 400);
        }

        try {
            // Process refund with Razorpay
            $refund = $this->processRazorpayRefund($payment->transaction_id, $refundAmount * 100);

            // Update payment record
            $payment->update([
                'remarks' => $payment->remarks . ' | Refunded: ₹' . $refundAmount . ' - ' . $request->reason
            ]);

            return response()->json([
                'message' => 'Refund processed successfully',
                'refund_id' => $refund['id'],
                'amount' => $refundAmount,
            ]);

        } catch (\Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            return response()->json(['error' => 'Refund processing failed'], 500);
        }
    }

    private function createRazorpayOrder($orderData)
    {
        // Mock Razorpay order creation for demo
        return [
            'id' => 'order_' . uniqid(),
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'receipt' => $orderData['receipt'],
            'status' => 'created',
        ];
    }

    private function getPaymentDetails($paymentId)
    {
        // Mock payment details for demo
        return [
            'id' => $paymentId,
            'status' => 'captured',
            'amount' => 100000, // Amount in paise
            'currency' => 'INR',
        ];
    }

    private function processRazorpayRefund($paymentId, $amount)
    {
        // Mock refund processing for demo
        return [
            'id' => 'rfnd_' . uniqid(),
            'payment_id' => $paymentId,
            'amount' => $amount,
            'status' => 'processed',
        ];
    }
}
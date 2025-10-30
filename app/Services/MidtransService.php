<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Exception;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create Midtrans transaction and get Snap token
     */
    public function createTransaction($booking)
    {
        try {
            $transactionDetails = [
                'order_id' => 'BOOKING-' . $booking->id . '-' . time(),
                'gross_amount' => (int) $booking->price,
            ];

            $customerDetails = [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? '',
            ];

            $itemDetails = [
                [
                    'id' => 'SERVICE-' . $booking->service->id,
                    'price' => (int) $booking->service->price,
                    'quantity' => 1,
                    'name' => $booking->service->name,
                ]
            ];

            $payload = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
            ];

            $snapToken = Snap::getSnapToken($payload);
            
            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $transactionDetails['order_id'],
            ];
        } catch (Exception $e) {
            \Log::error('Midtrans transaction creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create payment transaction',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus($transactionId)
    {
        try {
            $status = Transaction::status($transactionId);
            return [
                'success' => true,
                'status' => $status,
            ];
        } catch (Exception $e) {
            \Log::error('Midtrans status check error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get transaction status',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handleNotification($notification)
    {
        try {
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $paymentType = $notification['payment_type'];
            $fraudStatus = $notification['fraud_status'] ?? null;

            \Log::info('Midtrans webhook received', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            // Extract booking ID from order_id (format: BOOKING-{id}-{timestamp})
            $bookingId = explode('-', $orderId)[1];

            $booking = \App\Models\Booking::find($bookingId);
            if (!$booking) {
                \Log::warning('Booking not found for order_id: ' . $orderId);
                return false;
            }

            // Handle transaction status
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus == 'challenge') {
                    // Challenge status - wait for manual verification
                    $booking->update([
                        'payment_status' => 'pending',
                        'transaction_id' => $orderId,
                    ]);
                    \Log::info('Booking payment challenged', ['booking_id' => $bookingId]);
                } else if ($fraudStatus == 'accept' || $fraudStatus == null) {
                    // Payment successful
                    $booking->update([
                        'payment_status' => 'paid',
                        'transaction_id' => $orderId,
                    ]);
                    
                    \Log::info('Booking payment successful', ['booking_id' => $bookingId]);
                    
                    // Send email to admin
                    \Mail::to(config('services.admin_email'))->send(
                        new \App\Mail\BookingPaymentNotification($booking)
                    );
                }
            } else if ($transactionStatus == 'pending') {
                $booking->update([
                    'payment_status' => 'pending',
                    'transaction_id' => $orderId,
                ]);
                \Log::info('Booking payment pending', ['booking_id' => $bookingId]);
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $booking->update([
                    'payment_status' => 'cancelled',
                    'transaction_id' => $orderId,
                ]);
                \Log::info('Booking payment cancelled/denied', ['booking_id' => $bookingId]);
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Midtrans webhook handling error: ' . $e->getMessage());
            return false;
        }
    }
}

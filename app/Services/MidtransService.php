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

            \Log::info('=== MIDTRANS WEBHOOK RECEIVED START ===');
            \Log::info('Full webhook data:', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'fraud_status' => $fraudStatus,
                'all_keys' => array_keys($notification),
            ]);

            // Extract booking ID from order_id (format: BOOKING-{id}-{timestamp})
            $bookingId = explode('-', $orderId)[1];
            
            \Log::info('Extracted booking ID:', ['booking_id' => $bookingId]);
            
            $booking = \App\Models\Booking::find($bookingId);
            if (!$booking) {
                \Log::error('Booking not found', ['booking_id' => $bookingId, 'order_id' => $orderId]);
                return false;
            }
            
            \Log::info('Booking found:', [
                'booking_id' => $booking->id,
                'current_status' => $booking->status,
                'current_payment_status' => $booking->payment_status,
            ]);

            // Handle transaction status
            \Log::info('Processing transaction status:', [
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'will_process_email' => ($transactionStatus == 'capture' || $transactionStatus == 'settlement') && ($fraudStatus == 'accept' || $fraudStatus == null),
            ]);
            
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
                    \Log::info('PAYMENT SUCCESSFUL - Starting email send process', [
                        'booking_id' => $bookingId,
                        'fraud_status' => $fraudStatus,
                    ]);
                    
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'transaction_id' => $orderId,
                    ]);
                    
                    \Log::info('Booking payment successful', ['booking_id' => $bookingId]);
                    
                    // Create notification
                    try {
                        \App\Models\Notification::create([
                            'booking_id' => $bookingId,
                            'type' => 'payment_confirmed',
                            'title' => 'Order Masuk - ' . $booking->user->name,
                            'message' => 'Booking dari ' . $booking->user->name . ' untuk ' . $booking->service->name . ' telah dikonfirmasi. Tanggal: ' . $booking->booking_date->format('d M Y') . ', Jam: ' . $booking->booking_time,
                            'is_read' => false,
                        ]);
                        \Log::info('Notification created successfully', ['booking_id' => $bookingId]);
                    } catch (\Exception $e) {
                        \Log::error('Error creating notification', [
                            'booking_id' => $bookingId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                    
                    // Send email to admin
                    try {
                        $adminEmail = config('services.admin_email');
                        \Log::info('Sending booking notification email', [
                            'booking_id' => $bookingId,
                            'admin_email' => $adminEmail,
                            'mail_mailer' => config('mail.mailer'),
                            'mail_host' => config('mail.host'),
                            'mail_from' => config('mail.from.address'),
                        ]);
                        
                        \Mail::to($adminEmail)->send(
                            new \App\Mail\BookingPaymentNotification($booking)
                        );
                        
                        \Log::info('Booking notification email sent successfully', [
                            'booking_id' => $bookingId,
                            'to_email' => $adminEmail,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send booking notification email', [
                            'booking_id' => $bookingId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            } else if ($transactionStatus == 'pending') {
                $booking->update([
                    'payment_status' => 'pending',
                    'transaction_id' => $orderId,
                ]);
                \Log::info('Booking payment pending', ['booking_id' => $bookingId]);
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                // Payment failed/expired/cancelled - set to unpaid and cancel booking
                $booking->update([
                    'payment_status' => 'unpaid',
                    'status' => 'cancelled',
                    'transaction_id' => $orderId,
                ]);
                
                // Rollback schedule capacity
                $schedule = \App\Models\Schedule::where('date', $booking->booking_date)
                    ->where('time_slot', $booking->booking_time)
                    ->first();
                
                if ($schedule && $booking->service) {
                    $this->decrementScheduleBooking($schedule, $booking->service);
                }
                
                \Log::info('Booking payment failed/expired', ['booking_id' => $bookingId, 'reason' => $transactionStatus]);
            }

            \Log::info('=== MIDTRANS WEBHOOK RECEIVED END (Success) ===');
            return true;
        } catch (Exception $e) {
            \Log::error('=== MIDTRANS WEBHOOK HANDLING ERROR ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get new Snap token for existing booking (retry payment)
     */
    public function getSnapToken($booking)
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
                    'price' => (int) $booking->price,
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
            
            // Update transaction_id
            $booking->update([
                'transaction_id' => $transactionDetails['order_id'],
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
            ];
        } catch (Exception $e) {
            \Log::error('Midtrans get snap token error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper method to decrement schedule booking count
     */
    private function decrementScheduleBooking($schedule, $service)
    {
        if (!$schedule) return;
        
        // Update new type-based system
        if ($service->type_id) {
            $schedule->decrementTypeBooking($service->type_id);
        }
        
        // Update legacy columns for backward compatibility
        if (!$service->type_id && $service->getAttribute('type')) {
            if ($service->getAttribute('type') === 'nails_art') {
                $schedule->decrement('nails_art_booked');
            } elseif ($service->getAttribute('type') === 'eyelash') {
                $schedule->decrement('eyelash_booked');
            }
        }
    }
}

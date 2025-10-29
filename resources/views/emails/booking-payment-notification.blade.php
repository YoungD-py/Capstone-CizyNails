<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #ec4899;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .booking-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #ec4899;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .payment-proof {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
        }
        .action-button {
            display: inline-block;
            background-color: #ec4899;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Booking Payment Received</h1>
        </div>
        
        <div class="content">
            <p>Hello Admin,</p>
            
            <p>A new booking payment has been submitted. Please review the details below:</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="label">Customer Name:</span>
                    <span class="value">{{ $booking->user->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Customer Email:</span>
                    <span class="value">{{ $booking->user->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Service:</span>
                    <span class="value">{{ $booking->service->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Booking Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Booking Time:</span>
                    <span class="value">{{ $booking->booking_time }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Duration:</span>
                    <span class="value">{{ $booking->total_duration_minutes }} minutes</span>
                </div>
                <div class="detail-row">
                    <span class="label">Removal Needed:</span>
                    <span class="value">{{ $booking->needs_removal ? 'Yes' : 'No' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Amount:</span>
                    <span class="value">Rp. {{ number_format($booking->price ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Notes:</span>
                    <span class="value">{{ $booking->notes ?? '-' }}</span>
                </div>
            </div>

            <div class="payment-proof">
                <p><strong>Payment Proof Submitted:</strong></p>
                <p>The customer has uploaded a payment proof. Please verify the payment and confirm the booking in the admin dashboard.</p>
            </div>

            <p>Please log in to the admin dashboard to verify the payment and confirm the booking.</p>
            
            <a href="{{ url('/admin/dashboard') }}" class="action-button">Go to Admin Dashboard</a>
        </div>
        
        <div class="footer">
            <p>Cizy Nails - Booking System</p>
            <p>This is an automated email. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

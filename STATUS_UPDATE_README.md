# Update Status Booking & Payment System

## 📋 Summary Perubahan

### 1. **Booking Status** (4 status):
- `pending` = Belum dibayar
- `confirmed` = Sudah bayar  
- `completed` = Sudah selesai layanan (diset oleh nail artist)
- `cancelled` = Dibatalkan (unpaid/timeout, admin cancel, customer cancel)

### 2. **Payment Status** (3 status):
- `pending` = Belum dibayar
- `paid` = Sudah dibayar (dari `verified`)
- `unpaid` = Gagal/expired (dari `rejected` dan `cancelled`)

### 3. **Flow Perubahan**:
```
Booking Baru → status: pending, payment: pending
↓
Payment Success (Midtrans) → status: confirmed, payment: paid
↓  
Layanan Selesai (Nail Artist) → status: completed, payment: paid

ATAU

Payment Timeout/Expired (Midtrans) → status: cancelled, payment: unpaid
Customer Cancel → status: cancelled, payment: (tetap apa adanya)
Admin Cancel → status: cancelled, payment: (tetap apa adanya)
```

## 🔧 File yang Diubah/Dibuat:

### Migration:
- `database/migrations/2026_01_04_100000_update_booking_payment_status.php` ✅ BARU
  - Update enum payment_status: `pending`, `paid`, `unpaid`
  - Convert data lama: `verified` → `paid`, `rejected/cancelled` → `unpaid`

### Controllers:
- `app/Http/Controllers/CustomerDashboardController.php` ✅ UPDATE
  - Added: `cancelBooking()` - Customer cancel (NO REFUND)
  - Updated: sync payment status menggunakan `unpaid` instead of `cancelled`

- `app/Http/Controllers/AdminDashboardController.php` ✅ UPDATE
  - Removed: `verifyPayment()`, `rejectPayment()` (tidak perlu, auto dari Midtrans)
  - Added: `cancelBooking()` - Admin cancel (NO REFUND)

### Services:
- `app/Services/MidtransService.php` ✅ UPDATE
  - Updated: `handleNotification()` 
  - Payment expire/deny/cancel → `payment_status: unpaid, status: cancelled`
  - Rollback schedule capacity saat payment failed

### Routes:
- `routes/web.php` ✅ UPDATE
  - Removed: verify-payment, reject-payment routes
  - Added: `POST /bookings/{id}/cancel` (customer)
  - Added: `POST /admin/bookings/{booking}/cancel` (admin)

### Helpers:
- `app/Helpers/StatusHelper.php` ✅ BARU
  - `getStatusBadge()` - Badge untuk booking status
  - `getPaymentStatusBadge()` - Badge untuk payment status
  - `getStatusColor()` - Color class untuk status
  - `getPaymentStatusColor()` - Color class untuk payment

- `composer.json` ✅ UPDATE
  - Autoload StatusHelper.php

## 🚀 Cara Menjalankan:

### 1. Run Composer Dump Autoload:
```bash
composer dump-autoload
```

### 2. Run Migration:
```bash
php artisan migrate
```

### 3. Clear Cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

## ⚠️ PENTING - Midtrans Webhook:

Webhook URL sudah ada di: `POST /api/midtrans/webhook`

Pastikan di Midtrans Dashboard, set:
- **Payment Notification URL**: `https://your-domain.com/api/midtrans/webhook`
- **Finish Redirect URL**: `https://your-domain.com/dashboard`
- **Unfinish Redirect URL**: `https://your-domain.com/dashboard`
- **Error Redirect URL**: `https://your-domain.com/dashboard`

## 📝 TODO - Update Views:

Setelah migration berhasil, perlu update tampilan di view files:

### Customer Dashboard (`resources/views/customer/dashboard.blade.php`):
- Replace status badge dengan `{!! getStatusBadge($booking->status) !!}`
- Replace payment badge dengan `{!! getPaymentStatusBadge($booking->payment_status) !!}`
- Tambah button "Cancel Booking" (jika status bukan cancelled/completed)
- Remove button "Verify Payment" / "Upload Proof"

### Admin Bookings (`resources/views/admin/bookings.blade.php`):
- Replace status badge dengan `{!! getStatusBadge($booking->status) !!}`
- Replace payment badge dengan `{!! getPaymentStatusBadge($booking->payment_status) !!}`
- Remove button "Verify" dan "Reject" 
- Tambah button "Cancel Booking" (jika status bukan cancelled/completed)

### Nail Artist Dashboard (`resources/views/nail-artist/dashboard.blade.php` & `bookings.blade.php`):
- Replace status badge dengan `{!! getStatusBadge($booking->status) !!}`
- Replace payment badge dengan `{!! getPaymentStatusBadge($booking->payment_status) !!}`

## 🧪 Testing:

1. **Test Payment Success Flow**:
   - Buat booking baru → payment: pending, status: pending
   - Bayar via Midtrans → payment: paid, status: confirmed
   - Nail artist complete → status: completed

2. **Test Payment Expired**:
   - Buat booking baru → pending
   - Tunggu expired (atau test via Midtrans simulator)
   - Check: payment: unpaid, status: cancelled
   - Schedule capacity harus rollback

3. **Test Customer Cancel**:
   - Booking dengan status pending/confirmed
   - Customer cancel via dashboard
   - Check: status: cancelled, payment_status tetap
   - No refund message muncul

4. **Test Admin Cancel**:
   - Booking dengan status pending/confirmed  
   - Admin cancel via admin panel
   - Check: status: cancelled, payment_status tetap
   - No refund message muncul

## 📊 Status Reference:

### Booking Status:
| Status | Arti | Set By |
|--------|------|---------|
| `pending` | Belum dibayar | System (booking baru) |
| `confirmed` | Sudah bayar | Midtrans (payment success) |
| `completed` | Selesai | Nail Artist |
| `cancelled` | Dibatalkan | Midtrans (timeout), Customer, Admin |

### Payment Status:
| Status | Arti | Set By |
|--------|------|---------|
| `pending` | Belum dibayar | System (booking baru) |
| `paid` | Sudah dibayar | Midtrans (payment success) |
| `unpaid` | Gagal/Expired | Midtrans (deny/expire/cancel) |

## 🔍 Log Monitoring:

Semua perubahan status tercatat di log:
```php
\Log::info('Customer cancelled booking', [...]);
\Log::info('Admin cancelled booking', [...]);
\Log::info('Booking payment failed/expired', [...]);
```

Check log file: `storage/logs/laravel.log`

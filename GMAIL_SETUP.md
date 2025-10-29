# Gmail SMTP Setup Guide

Untuk menggunakan Gmail SMTP untuk mengirim email notifikasi booking, ikuti langkah-langkah berikut:

## 1. Enable 2-Factor Authentication di Gmail

1. Buka https://myaccount.google.com/
2. Pilih "Security" di sidebar kiri
3. Scroll ke bawah dan cari "2-Step Verification"
4. Ikuti instruksi untuk mengaktifkan 2FA

## 2. Generate App Password

1. Buka https://myaccount.google.com/apppasswords
2. Pilih "Mail" dan "Windows Computer" (atau device yang sesuai)
3. Google akan generate password khusus untuk aplikasi
4. Copy password tersebut

## 3. Update .env File

Buka file `.env` di root project dan update konfigurasi email:

\`\`\`
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password-dari-step-2
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Cizy Nails"
\`\`\`

Ganti:
- `your-email@gmail.com` dengan email Gmail Anda
- `your-app-password-dari-step-2` dengan password yang di-generate di step 2

## 4. Test Email Configuration

Jalankan command berikut untuk test konfigurasi email:

\`\`\`bash
php artisan tinker
Mail::raw('Test email', function($message) { $message->to('test@example.com'); });
\`\`\`

## 5. Automatic Email Notification

Setelah setup selesai, sistem akan otomatis mengirim email ke admin (deruanggoro009@gmail.com) ketika:
- Customer mengupload bukti transfer pembayaran
- Email berisi detail booking dan link ke dashboard admin untuk verifikasi

## Troubleshooting

Jika email tidak terkirim:
1. Pastikan 2FA sudah diaktifkan
2. Pastikan app password sudah di-generate dengan benar
3. Pastikan .env sudah di-update dengan benar
4. Check Laravel logs di `storage/logs/laravel.log`

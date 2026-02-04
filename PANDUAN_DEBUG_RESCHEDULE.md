# 🔍 Panduan Debug Fitur Reschedule

## ✅ Status Perbaikan

- ✅ **ApiSessionAuth.php** - Sudah diperbaiki (tambahkan closing braces)
- ✅ **BookingController.php** - Sudah ditambahkan logging comprehensive
- ✅ File sudah diupload ke production
- ⏳ Menunggu proses cache clearing di server

## 📝 Apa Yang Diubah

### 1. **ApiSessionAuth Middleware** 
**File:** `app/Http/Middleware/ApiSessionAuth.php`

Ditambahkan logging yang mencatat:
- Nama cookie session dan kehadiran cookie
- Nilai session ID
- Status auth sebelum dan sesudah setup guard
- Semua cookies yang dikirim oleh request
- Data session lengkap

**Format Log:**
```
======== ApiSessionAuth START ========
[Informasi detail tentang session dan auth]
======== ApiSessionAuth END ========
```

### 2. **BookingController::reschedule()**
**File:** `app/Http/Controllers/BookingController.php` (line 518+)

Ditambahkan 6 level logging detail:

1. **Request & Session Details**
   - Path, method, session ID
   - Session driver (file/database/redis)
   - Session lifetime

2. **Booking Details**
   - ID booking
   - User ID owner
   - Status booking (pending/completed/cancelled)

3. **Authentication State**
   - Apakah user ada di request?
   - User ID dan nama
   - Guard yang dipakai
   - Status auth check

4. **Cookie & Session Details**
   - Nama session cookie
   - Ada atau tidak session cookie
   - Semua cookies yang dikirim
   - Semua data session yang tersimpan

5. **Direct Guard Check**
   - Coba load user langsung dari web guard
   - Apakah berhasil?

6. **User ID Comparison**
   - Compare booking user_id dengan request user_id
   - Apakah cocok?

**Format Log:**
```
=============== RESCHEDULE ATTEMPT ===============
1. Request & Session Details: {...}
2. Booking Details: {...}
3. Authentication State: {...}
4. Cookie & Session Details: {...}
5. Direct Guard Check: {...}
6. User ID Comparison: {...}
✓ AUTH PASSED - User is authenticated and authorized
```

---

## 🧪 Langkah-Langkah Testing

### **LANGKAH 1️⃣ - Clear Cache di Production**

Buka URL ini di browser (PENTING!):
```
https://cizynails-booking.web.id/clear_cache_prod.php
```

**Expected Result:**
Halaman menampilkan pesan:
```
Cache berhasil dihapus pada: 2026-01-26 XX:XX:XX
```

Ini mengaktifkan kode logging baru.

---

### **LANGKAH 2️⃣ - Test Debug Endpoint**

1. **Login** sebagai customer ke https://cizynails-booking.web.id
2. Buka **Developer Console** (tekan `F12` atau `Ctrl+Shift+I`)
3. Buka tab **Console**
4. Copy dan paste code berikut:

```javascript
// Test endpoint debug auth
fetch('/api/debug/auth', {
    credentials: 'include',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})
.then(r => r.json())
.then(d => {
    console.log('=== DEBUG AUTH RESULT ===');
    console.log(JSON.stringify(d, null, 2));
    console.log('=== END DEBUG ===');
})
.catch(e => console.error('Error:', e));
```

5. Tekan **Enter** dan lihat hasilnya

**Expected Result (Berhasil):**
```json
{
  "authenticated": true,
  "user": {
    "id": 2,
    "name": "Nama Customer",
    "email": "customer@email.com"
  },
  "session_id": "xxxxxxxxxxxxx"
}
```

**Jika Gagal (Bug):**
```json
{
  "authenticated": false,
  "user": null,
  "session_id": "xxxxxxxxxxxxx"
}
```

Jika `authenticated` adalah `false`, maka session TIDAK LOADING di API calls - **INI ADALAH BUGNYA!**

---

### **LANGKAH 3️⃣ - Coba Reschedule**

1. Di dashboard customer, **klik tombol Reschedule** pada salah satu booking
2. **Pilih tanggal dan jam** yang baru
3. **Klik tombol Konfirmasi**
4. **Lihat pesan error** yang muncul

**Catat error yang muncul:**
```
Error: Unauthorized
atau
Error: Unauthenticated
atau
Error lainnya
```

---

### **LANGKAH 4️⃣ - Download dan Periksa Log Production**

1. **Buka SFTP** di VS Code ke server production
2. Navigasi ke: `/home/cizynail/Cizy-Nails-Project/storage/logs/`
3. Download file: `laravel.log` (file paling baru)
4. Buka di text editor

---

## 🔍 Cara Membaca Log

### **Cari bagian ini di log:**
```
=============== RESCHEDULE ATTEMPT ===============
```

Kemudian lihat output lengkapnya.

### **Ada 3 kemungkinan hasil:**

#### **HASIL A: User Tidak Terautentikasi ❌**

```
1. Request & Session Details: {
  "request_path": "api/bookings/5/reschedule",
  "session_id": "abc123xyz"
}
2. Booking Details: {...}
3. Authentication State: {
  "request_user_is_null": true,
  "request_user_id": null,
  "auth_check": false
}
...
RESCHEDULE FAILED: request->user() is null (Unauthenticated)
```

**Penyebab:** Session cookie TIDAK DIMUAT atau TIDAK DIKIRIM
**Solusi:** Masalah dengan session middleware atau cookie transmission di HTTPS

---

#### **HASIL B: User Salah / ID Tidak Cocok ❌**

```
6. User ID Comparison: {
  "booking_user_id_int": 5,
  "request_user_id_int": 2,
  "ids_match": false
}
...
RESCHEDULE FAILED: User ID mismatch (Unauthorized)
booking_user_id: 5
request_user_id: 2
```

**Penyebab:** User yang login di session berbeda dengan owner booking
**Solusi:** Session corruption atau cache outdated

---

#### **HASIL C: Auth Passed, Tapi Error di Bagian Lain ✅**

```
3. Authentication State: {
  "request_user_is_null": false,
  "request_user_id": 5,
  "auth_check": true
}
6. User ID Comparison: {
  "booking_user_id_int": 5,
  "request_user_id_int": 5,
  "ids_match": true
}
...
✓ AUTH PASSED - User is authenticated and authorized
```

Kemudian muncul error yang berbeda (misal: "Booking sudah cancelled", dsb)

**Penyebab:** Auth sudah benar, error ada di business logic
**Solusi:** Periksa status booking, reschedule count, date validation, dsb

---

## 📋 Checklist Testing

- [ ] Sudah klik `/clear_cache_prod.php`?
- [ ] Sudah test `/api/debug/auth` endpoint?
- [ ] Sudah coba reschedule dan catat error?
- [ ] Sudah download laravel.log?
- [ ] Sudah cari "=============== RESCHEDULE ATTEMPT ==============="?
- [ ] Sudah identifikasi HASIL A, B, atau C?

---

## 🛠️ Troubleshooting

### **Q: Clear cache page menunjukkan error/blank**
A: Script tidak bisa dijalankan. Kemungkinan:
- File permissions tidak benar
- PHP executable path salah
- Coba jalankan via SSH jika tersedia

```bash
cd /home/cizynail/Cizy-Nails-Project
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### **Q: Debug auth endpoint juga menunjukkan false/error**
A: Ada masalah dengan session persistence di HTTPS:
- Cek `SESSION_SAME_SITE=none` di .env
- Cek `SESSION_SECURE_COOKIE=true` di .env
- Cek session file permissions (755)

### **Q: Log tidak berisi "=============== RESCHEDULE ATTEMPT ==============="**
A: Cache belum clear, atau error pada file syntax. Coba:
- Refresh browser (Ctrl+F5 hard refresh)
- Lihat error di browser console (F12)
- Pastikan file sudah diupload dengan benar

### **Q: File masih menunjukkan warna merah di VS Code**
A: Mungkin syntax highlighting outdated:
- Close file dan open lagi
- Atau close dan open VS Code

---

## 📞 Informasi Penting

**Production Server Settings (.env):**
```
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_COOKIE=cizy_nails_session
```

**Log Location:**
```
/home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log
```

**Reschedule Endpoint:**
```
POST /api/bookings/{booking_id}/reschedule
Middleware: ['api.session.auth', 'auth']
```

---

## 📌 Ringkasan Proses

1. ✅ Perbaiki ApiSessionAuth.php (tambah closing braces)
2. ✅ Upload ke production
3. 🔄 **SEKARANG:** Klik `/clear_cache_prod.php`
4. 🧪 Test dengan `/api/debug/auth` endpoint
5. 📊 Coba reschedule dan catat error
6. 📥 Download laravel.log
7. 🔍 Cari "=============== RESCHEDULE ATTEMPT ==============="
8. 📝 Identifikasi HASIL A / B / C
9. 🎯 Siap untuk perbaikan final berdasarkan root cause

---

**Gunakan Bahasa Indonesia untuk semua komunikasi error dan debugging! 🇮🇩**

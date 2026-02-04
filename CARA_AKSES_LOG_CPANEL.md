# 📍 Cara Menemukan & Mengakses Log di cPanel

## ❌ Masalah Sebelumnya
- "Value of type null is not callable" error ✅ **SUDAH DIPERBAIKI**
  - Masalah: Duplicate `return` statement di ApiSessionAuth.php
  - Solusi: Hapus yang duplicate, tinggal 1

## ✅ Status Sekarang
- ✅ ApiSessionAuth.php diperbaiki
- ✅ Cache clear script diupload
- ⏳ **SEKARANG:** Clear cache dan test lagi

---

## 🔧 LANGKAH BERIKUTNYA

### 1. Clear Cache
Buka URL ini:
```
https://cizynails-booking.web.id/clear_cache_prod.php
```

Tunggu sampai muncul: `Cache berhasil dihapus pada: 2026-01-26 XX:XX:XX`

### 2. Test Debug Endpoint Lagi
Buka Developer Console (F12) dan paste:
```javascript
fetch('/api/debug/auth', {
    credentials: 'include',
    headers: {'X-Requested-With': 'XMLHttpRequest'}
}).then(r => r.json()).then(d => console.log(JSON.stringify(d, null, 2)))
```

Harusnya sekarang tidak error (berhasil menampilkan JSON)!

### 3. Coba Reschedule

Kemudian test reschedule lagi di dashboard.

---

## 📋 Cara Menemukan Laravel Log di cPanel

### **CARA 1: Via File Manager di cPanel (MUDAH)**

1. **Login ke cPanel** di: `https://103.112.163.154:2083/`
   - Username: `cizynail`
   - Password: `lostamasta123`

2. **Cari File Manager** di menu (biasanya di bagian "Files")

3. **Navigasi ke folder:**
   ```
   /home/cizynail/Cizy-Nails-Project/storage/logs/
   ```

4. **Lihat file `laravel.log`** 
   - File ini berisi semua log application
   - Klik untuk download atau buka di viewer

### **CARA 2: Via FTP (JIKA File Manager Tidak Ada)**

1. **Buka SFTP di VS Code**
2. **Navigasi ke:**
   ```
   /home/cizynail/Cizy-Nails-Project/storage/logs/
   ```
3. **Download `laravel.log`**
4. **Buka di text editor lokal**

### **CARA 3: Via SSH (Jika Available)**

```bash
ssh cizynail@103.112.163.154
# Password: lostamasta123

# Lihat log real-time
tail -f /home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log

# Atau cari entry reschedule
grep "RESCHEDULE ATTEMPT" /home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log
```

---

## 🗂️ Struktur Folder Log

Semua log terletak di:
```
/home/cizynail/Cizy-Nails-Project/storage/logs/
```

Folder ini biasanya berisi:
- `laravel.log` - **Main log file** ← GUNAKAN INI
- `laravel-YYYY-MM-DD.log` - Log harian (jika enabled)

---

## 📖 Cara Membaca Log

### **Cari baris ini di log:**
```
=============== RESCHEDULE ATTEMPT ===============
```

Setelah baris ini akan ada info detail seperti:

```
[2026-01-26 14:30:45] local.INFO: =============== RESCHEDULE ATTEMPT =============== 
[2026-01-26 14:30:45] local.INFO: 1. Request & Session Details: {"request_path":"api/bookings/5/reschedule",...}
[2026-01-26 14:30:45] local.INFO: 2. Booking Details: {"booking_id":5,"booking_user_id":"2",...}
[2026-01-26 14:30:45] local.INFO: 3. Authentication State: {"request_user_is_null":true,...}
...
[2026-01-26 14:30:45] local.ERROR: RESCHEDULE FAILED: request->user() is null (Unauthenticated)
```

### **Identifikasi Masalah dari Log:**

**Scenario 1: User Null ❌**
```
"request_user_is_null": true
"request_user_id": null
...
RESCHEDULE FAILED: request->user() is null (Unauthenticated)
```
→ Session TIDAK DIMUAT, cookies tidak dikirim dengan benar

**Scenario 2: User Loaded tapi ID Salah ❌**
```
"booking_user_id_int": 5
"request_user_id_int": 2
"ids_match": false
...
RESCHEDULE FAILED: User ID mismatch (Unauthorized)
```
→ User login tidak cocok dengan owner booking

**Scenario 3: Auth Passed ✅**
```
"ids_match": true
...
✓ AUTH PASSED - User is authenticated and authorized
```
→ Auth OK, error ada di tempat lain

---

## 📌 Quick Reference

**Login cPanel:**
```
URL: https://103.112.163.154:2083/
Username: cizynail
Password: lostamasta123
```

**Log Location:**
```
/home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log
```

**Check Log via Browser (jika tidak ada CLI):**
```
1. File Manager → storage → logs → laravel.log
2. Download atau buka di viewer
```

---

## 🔍 Debug Tips

### Jika Log Tidak Berubah:
1. ✅ Sudah clear cache? (buka /clear_cache_prod.php)
2. ✅ Sudah hard refresh browser? (Ctrl+F5)
3. ✅ Error mungkin di tempat lain? (cek console browser)

### Jika Log File Terlalu Besar:
1. **Download file terakhir saja:**
   ```
   tail -100 laravel.log > recent_logs.txt
   ```
2. **Atau search spesifik:**
   ```
   grep "RESCHEDULE" laravel.log | tail -50
   ```

### Format Timestamp di Log:
```
[2026-01-26 14:30:45] - Format: [YYYY-MM-DD HH:MM:SS]
local.INFO - Channel dan level (INFO, ERROR, WARNING, dll)
```

---

## ✅ Checklist Sebelum Lanjut

- [ ] Sudah buka /clear_cache_prod.php?
- [ ] Sudah test /api/debug/auth endpoint?
- [ ] Sudah akses File Manager cPanel?
- [ ] Sudah navigasi ke storage/logs/?
- [ ] Sudah lihat laravel.log?
- [ ] Sudah cari "RESCHEDULE ATTEMPT"?

**Setelah semua done, share hasil log atau screenshot dan kita analisis bersama! 🎯**

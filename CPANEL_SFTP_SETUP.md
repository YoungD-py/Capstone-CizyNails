# Setup Koneksi SFTP/FTP ke cPanel

## Status Koneksi
❌ **Port 22 (SSH/SFTP)**: Connection Refused - Port SSH tidak tersedia
✅ **Port 21 (FTP)**: Terbuka dan siap digunakan

## Cara Setup

### 1. Cek Informasi FTP di cPanel

Login ke cPanel Anda dan cari informasi berikut:

**Di cPanel Dashboard:**
1. Buka **"FTP Accounts"** atau **"Account FTP"**
2. Lihat detail FTP account utama atau buat FTP account baru
3. Catat informasi:
   - **FTP Server**: Biasanya `ftp.yourdomain.com` atau IP `103.112.163.154`
   - **FTP Username**: Format bisa `username` atau `username@domain.com`
   - **FTP Password**: Password yang Anda set
   - **Port**: 21 (FTP) atau 21 dengan SSL/TLS

**Untuk SSH/SFTP (Port 22):**
- SSH mungkin dinonaktifkan oleh hosting provider
- Hubungi support hosting untuk mengaktifkan SSH access
- Atau gunakan FTP sebagai alternatif

### 2. Update File .vscode/sftp.json

Saya sudah update konfigurasi dengan 2 kemungkinan:

**Option 1: FTP Standard (No SSL)**
```json
{
    "name": "cizynails-server",
    "host": "103.112.163.154",
    "protocol": "ftp",
    "port": 21,
    "username": "cizynail@cizynails.my.id",
    "password": "YOUR_PASSWORD",
    "remotePath": "/public_html",
    "uploadOnSave": false,
    "secure": false,
    "ignore": [
        ".vscode",
        ".git",
        "node_modules",
        "vendor",
        ".env",
        "storage/logs/*",
        "storage/framework/cache/*"
    ]
}
```

**Option 2: FTPS (FTP with SSL/TLS)**
```json
{
    "name": "cizynails-server",
    "host": "103.112.163.154",
    "protocol": "ftp",
    "port": 21,
    "username": "cizynail@cizynails.my.id",
    "password": "YOUR_PASSWORD",
    "remotePath": "/public_html",
    "uploadOnSave": false,
    "secure": true,
    "secureOptions": {
        "rejectUnauthorized": false
    },
    "ignore": [...]
}
```

### 3. Cara Menggunakan Extension SFTP

Extension **natizyskunk.sftp** sudah terinstall di VS Code Anda.

**Command Palette (Ctrl+Shift+P):**
- `SFTP: Config` - Buka/edit konfigurasi
- `SFTP: List` - Lihat file di remote server
- `SFTP: Upload` - Upload file/folder
- `SFTP: Download` - Download file/folder
- `SFTP: Sync Local -> Remote` - Sync semua ke server
- `SFTP: Sync Remote -> Local` - Sync semua dari server
- `SFTP: Sync Both Directions` - Sync 2 arah

**Right-click pada file/folder:**
- Upload File/Folder
- Download File/Folder
- Sync Local -> Remote
- Sync Remote -> Local

### 4. Troubleshooting

#### Problem: "Connection Refused" on Port 22
**Solution:** 
- Port SSH tidak tersedia di server
- Gunakan FTP (port 21) sebagai alternatif
- Atau hubungi hosting provider untuk aktifkan SSH

#### Problem: "530 Not logged in" / Login Failed
**Solution:**
```
Coba format username yang berbeda:
1. cizynail
2. cizynail@cizynails.my.id
3. cizynail@103.112.163.154

Cek di cPanel → FTP Accounts untuk username yang benar
```

#### Problem: "Certificate Invalid"
**Solution:**
```json
{
    "secure": true,
    "secureOptions": {
        "rejectUnauthorized": false
    }
}
```
Atau gunakan `"secure": false` untuk FTP tanpa SSL.

#### Problem: Remote Path tidak ditemukan
**Solution:**
```
Coba path berikut:
- /public_html
- /home/cizynail/public_html
- /home/YOUR_USERNAME/public_html
- /

Login via FTP client (FileZilla) untuk cek path yang benar
```

### 5. Alternatif: Gunakan FileZilla untuk Test

Download FileZilla dan test koneksi dengan:
- Host: `103.112.163.154`
- Username: `cizynail` atau `cizynail@cizynails.my.id`
- Password: `lostamasta123`
- Port: `21`
- Protocol: FTP atau FTPS (FTP over TLS)

Jika berhasil di FileZilla, gunakan kredensial yang sama di sftp.json

### 6. Deploy Laravel Project ke cPanel

Setelah koneksi berhasil:

1. **Upload file Laravel** (kecuali yang di ignore)
2. **Set Document Root** di cPanel ke `/public_html/public`
3. **Upload .env** secara manual (jangan di-commit ke git)
4. **Run di server**:
   ```bash
   php artisan migrate
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Set permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### 7. Yang Perlu Anda Lakukan Sekarang

1. ✅ Login ke cPanel Anda
2. ✅ Buka **FTP Accounts**
3. ✅ Catat **username FTP yang benar** (cizynail atau cizynail@domain.com)
4. ✅ Pastikan password benar
5. ✅ Update `.vscode/sftp.json` dengan kredensial yang benar
6. ✅ Test koneksi: `Ctrl+Shift+P` → `SFTP: List`

### 8. Keamanan

⚠️ **PENTING**: File `.vscode/sftp.json` berisi password plaintext!

Tambahkan ke `.gitignore`:
```
.vscode/sftp.json
```

Atau gunakan method yang lebih aman:
```json
{
    "name": "cizynails-server",
    "host": "103.112.163.154",
    "protocol": "ftp",
    "port": 21,
    "username": "cizynail@cizynails.my.id",
    "passphrase": true,  // Akan prompt password setiap koneksi
    "remotePath": "/public_html"
}
```

---

## Contact Support

Jika masih gagal, hubungi hosting provider Anda dan tanyakan:
1. Apakah SSH/SFTP tersedia? Port berapa?
2. Format username FTP yang benar?
3. Path lengkap ke public_html?
4. Apakah FTP dengan SSL/TLS didukung?

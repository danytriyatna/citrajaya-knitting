# 🧵 CITRAJAYA.KNITTING (CJK) — WORKSPACE RULES & DEPLOYMENT SOP

> Dokumen ini dimuat otomatis oleh Antigravity setiap kali thread membuka workspace `CitraJaya.Knitting`.

---

## 📌 1. PROFIL SISTEM & INFRASTRUKTUR
* **Framework**: CodeIgniter 4 (PHP 8.3)
* **Web Server**: LiteSpeed Web Server
* **Server Host**: `103.102.153.192` (Cloud Linux VPS)
* **Web Root Dev**: `/pejuang/development/html` (Port: `81` — `http://citraknitt.com:81`)
* **Database**: **PostgreSQL 16** (`citra_knitt_db`)
  * Host: `103.102.153.192:5432`
  * User: `citradev`
  * Driver: Postgre DSN (CI4)

---

## 🛡️ 2. SOP MUTLAK SEBELUM UPDATE & DEPLOYMENT
Setiap kali ada perbaikan bug, penambahan fitur, atau migrasi di CJK, **DILARANG MELAKUKAN PULL LANGSUNG TANPA BACKUP**. SOP deployment wajib melalui file otomasi:
📂 `Deploy_Dev_Server.bat` (yang menjalankan `scripts/deploy_cjk.ps1`).

### Tahapan 4-Pilar Deployment CJK:
1. **[1/4] Mandatory Database Snapshot (pg_dump)**:
   * Sebelum satu baris kode pun ditarik, script wajib mengeksekusi dump PostgreSQL menggunakan format custom archive (`-F c`):
     ```powershell
     & "C:\Program Files\PostgreSQL\18\bin\pg_dump.exe" -h 103.102.153.192 -p 5432 -U citradev -d citra_knitt_db -F c -b -f "sql\backup_cjk_<timestamp>.dump"
     ```
   * File dump berukuran ~8.5 MB otomatis disimpan di folder `sql/`.
   * **Abort Policy**: Jika proses backup database gagal atau file corrupt, deployment seketika dibatalkan demi integritas data transaksi rajut/yarn.

2. **[2/4] Remote Git Pull Branch**:
   * Menjalankan penarikan kode terbaru di server (mengikuti branch aktif / `master`):
     ```bash
     cd /pejuang/development/html && git pull origin $(git rev-parse --abbrev-ref HEAD)
     ```

3. **[3/4] CI4 Cache Clear, OpCache Reset & Writable Permissions**:
   * Membersihkan view & routing cache lama, reset OpCache LiteSpeed, serta menjaga hak akses folder writable:
     ```bash
     cd /pejuang/development/html && php spark cache:clear && chmod -R 775 writable && touch .lsphp_restart.txt && (killall -9 lsphp 2>/dev/null || true)
     ```

4. **[4/4] Live Health Check Verification**:
   * Menguji respon HTTP ke `http://citraknitt.com:81/` dengan header `Host: citraknitt.com:81`.
   * Status harus mengembalikan `HTTP 200` atau `HTTP 307` (Redirect ke auth login).

---

## ⚡ 3. DOKTRIN ZERO-CLIENT-CACHE (USER ANTI CLEAR CACHE)
Setiap update fitur, perbaikan bug, atau perubahan layout **DILARANG MEMBEBANI USER UNTUK CLEAR CACHE/CTRL+F5**. Sistem dilindungi oleh 3 lapis proteksi cache otomatis:

1. **Auto-Version Static Assets (`?v=filemtime`)**:
   * Filter global `App\Filters\NoCacheFilter` otomatis memindai seluruh tag `<script src="...">` dan `<link href="...">` lokal pada output HTML dan menyematkan timestamp modifikasi file (`?v=filemtime`).
   * Setiap kali file JS/CSS dimodifikasi dan di-pull ke server, URL aset otomatis berubah sehingga browser langsung mengunduh versi terkini tanpa sisa cache lama.

2. **Global Anti-Cache HTTP Response Headers**:
   * Seluruh respon dinamis (HTML, API JSON, dan AJAX) otomatis dikirim dengan header HTTP:
     ```http
     Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0
     Pragma: no-cache
     Expires: 0
     ```

3. **Global AJAX Cache Buster**:
   * jQuery & Tabulator dikonfigurasi dengan `$.ajaxSetup({ cache: false })` di root `custom.js` dan `template.php`, sehingga setiap panggilan data GET AJAX otomatis diberi query unique timestamp (`_=<timestamp>`) yang kebal disk/memory cache browser.

---

## 🔑 3. AUTENTIKASI SSH & KREDENSIAL
* **Public Key Laptop Dany**: `~/.ssh/id_ed25519.pub`
* **Pendaftaran Key ke Server**: Cukup jalankan `Install_SSH_Key_CJK.bat` sekali untuk memasukkan public key ke `/root/.ssh/authorized_keys` di `103.102.153.192`.
* Ke depannya koneksi SSH dan deploy berjalan passwordless tanpa hambatan.

---

## 🚫 4. PANTANGAN WORKSPACE CJK
1. **Dilarang Commit File Dump**: File dump database (`*.dump`, `cjk_db.sql`) wajib masuk `.gitignore`.
2. **Dilarang Deploy Manual Parsial**: Selalu gunakan `Deploy_Dev_Server.bat` agar SOP backup dan cache clearing tidak pernah terlewat.

---

## 🚫 5. DOKTRIN ANTI-CLEAR-CACHE USER (ZERO-CLIENT-CACHE-FRICTION)
* **PANTANGAN MUTLAK**: Dilarang membuat update yang menuntut user / klien melakukan clear cache browser atau hard reload (Ctrl+Shift+R).
* **Mekanisme Otomatis**:
  1. Helper `auto_version($path)` wajib digunakan untuk semua asset CSS dan JavaScript (`assets/css/*`, `assets/js/*`). Helper ini otomatis menambahkan parameter query `?v=<filemtime>` berdasarkan timestamp file riil di server.
  2. Di `template.php`, regex auto-injection versi secara otomatis menempelkan `auto_version` ke seluruh `<script src="...">` lokal.
  3. Setiap deploy dev, `php spark cache:clear` otomatis dieksekusi oleh script deployment agar template view selalu segar dari server.


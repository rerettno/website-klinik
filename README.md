# **Website BK HOSPITAL**

**TUGAS UNTUK PROYEK BK**-- Website manajemen BK HOSPITAL menggunakan **PHP Native** dan **MySQL**. Aplikasi ini terdiri dari 3 user, yaitu Admin, Dokter, dan Pasien

---

## **Progress**

🚀 **PROGRESS FINISH!!!**

---

## **Fitur**
- Login Admin dan Dokter
- (Admin Section) CRUD Data Poli
- (Admin Section) CRUD Data Dokter
- (Admin Section) CRUD Data Obat
- (Admin Section) Pendaftaran Pasien
- (Dokter Section) Mengelola Data Diri
- (Dokter Section) Pemeriksa pasien dan riwayat pasien
- (Pasien Section) Login dan Register
- (Pasien Section) Pendaftaran Poli

**NOTES**
admin : **`user id`** = adm001, **`pass`** = 12345
dokter : **`pass awal`** = 12345
---

## **Tools**
- **PHP Native**: Backend tanpa framework untuk pengelolaan logika aplikasi.
- **Tailwind CSS**: Tampilan untuk Website
- **MySQL**: Database untuk menyimpan data pasien, poli, dokter, dan jadwal.

---

## **Database**
File database terdapat di folder `db/`. Gunakan file SQL yang tersedia untuk membuat struktur tabel di MySQL.

### **Tabel Utama**
1. **`poli`**: Menyimpan daftar poli.
2. **`dokter`**: Menyimpan daftar dokter beserta poli yang mereka layani.
3. **`jadwal_periksa`**: Menyimpan jadwal dokter.
4. **`pasien`**: Menyimpan data pasien
5. **`daftar_poli`**: Menyimpan data pendaftaran pasien.
6. **`periksa`**: Menyimpan data pemeriksaan pasien.
7. **`detail_periksa`**: Menyimpan detail pemeriksaan pasien.
8. **`obat`**: Menympan data obat.

---

## **Cara Menjalankan**
1. Clone repository ini:
   ```bash
   git clone https://github.com/rerettno/website-klinik.git
   ```
2. Import database:
   - Masuk ke folder `db/`.
   - Import file SQL ke MySQL menggunakan phpMyAdmin atau CLI:
     ```bash
     mysql -u username -p database_name < db_file.sql
     ```
3. Konfigurasi koneksi database:
   - Edit file `config.php`:
     ```php
     <?php
     $conn = new mysqli("host", "username", "password", "database_name");

     if ($conn->connect_error) {
         die("Connection failed: " . $conn->connect_error);
     }
     ?>
     ```
4. Jalankan aplikasi di server lokal:
   - Jika menggunakan XAMPP, letakkan file di folder `htdocs`.
   - Akses melalui browser:
     ```
     http://localhost/repository-name
     ```

---

## **Kontributor**
- **Hapsari Retno** – [GitHub Profile](https://github.com/rerettno)

---

## **Lisensi**
Proyek ini menggunakan lisensi MIT. Anda bebas menggunakan dan memodifikasi proyek ini sesuai kebutuhan.

---

Salin file ini dan letakkan di root proyek Anda dengan nama `README.md`. Jika Anda memiliki informasi tambahan, seperti screenshot atau demo, tambahkan ke bagian **Fitur** atau **Cara Menjalankan**. 😊

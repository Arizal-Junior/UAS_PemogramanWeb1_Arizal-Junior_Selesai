# 🍵 Matchify Cafe - System Point of Sale (POS)

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

**Matchify Cafe System** adalah aplikasi kasir berbasis web yang dirancang untuk mempermudah manajemen transaksi, stok produk, dan laporan penjualan pada sebuah cafe. Proyek ini dikembangkan sebagai tugas **Ujian Akhir Semester (UAS) Mata Kuliah Pemograman Web 1**.

---

## 🚀 Fitur Utama

- **Multi-user Role**: Pemisahan hak akses yang jelas antara **Admin** dan **Kasir**.
- **Manajemen Produk**: CRUD (Create, Read, Update, Delete) data menu cafe secara dinamis (Matcha Series, dll).
- **Sistem Transaksi**: Pencatatan pesanan secara real-time dengan antarmuka yang user-friendly.
- **Manajemen User**: Fitur khusus Admin untuk mengelola akun dan kredensial kasir.
- **Dashboard Statistik**: Ringkasan visual data produk dan total pengguna untuk memudahkan monitoring.
- **Keamanan**: Implementasi sistem login menggunakan enkripsi session dan validasi input.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, JavaScript (ES6), Bootstrap 5.3.8 |
| **Backend** | PHP (Native/Procedural) |
| **Database** | MySQL |
| **Assets** | Custom CSS, Matcha-themed Product Imagery |

---

## 🔗 Akses Cepat

- 🌐 **Website (Hosting):** https://aspel.cyou/arizaluas/
- 🎥 **Video Demo:** https://drive.google.com/drive/folders/1EkEnMQ_ubY4QtnRSSLLfV4tNs3Ac9EYz?usp=sharing

---

## 📸 Tampilan Web
Halaman Login:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034542" src="https://github.com/user-attachments/assets/a030223a-d927-40e9-930b-c95de3b62fd1" />

Halaman Register:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034550" src="https://github.com/user-attachments/assets/30de4ab2-1bd5-4f06-ae7c-d2af141bb14a" />

Halaman Dashboard (Admin):
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034607" src="https://github.com/user-attachments/assets/8c979f1a-cf21-4d0b-8200-0e35879d7257" />

Halaman Kategori Menu:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034617" src="https://github.com/user-attachments/assets/d1563615-5980-48ac-8dfe-3a3f44319ea4" />

Halaman Data Produk:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034625" src="https://github.com/user-attachments/assets/f4e96dd7-dbdd-4568-9305-957961347ee9" />

Halaman Manajemen User:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034642" src="https://github.com/user-attachments/assets/56ca6e83-6420-4fe0-9114-39b193d93454" />

Halaman Laporan Penjualan:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034655" src="https://github.com/user-attachments/assets/0695bf2d-cdbb-45c3-aa98-e976d79d9e49" />

Halaman Profile:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034705" src="https://github.com/user-attachments/assets/63751c36-e336-447f-9597-3ca8dbbf5728" />

Halaman Kasir:
<img width="1920" height="1080" alt="Screenshot 2026-02-04 034750" src="https://github.com/user-attachments/assets/742da5a1-f1a6-4d72-9b2d-3013b6706ab1" />

---

## 📂 Struktur Folder

```text
├── admin/          # Halaman fungsionalitas untuk Admin
├── assets/         # File pendukung (Gambar, CSS)
├── auth/           # Sistem autentikasi (Login & Logout)
├── bootstrap/      # Library framework Bootstrap
├── config/         # Konfigurasi koneksi database
└── kasir/          # Halaman fungsionalitas untuk Kasir

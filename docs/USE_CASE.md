# Use Case Diagram - MarketLabs

## Actors

| Actor | Description |
|-------|-------------|
| **User** | Pengguna biasa yang dapat mengakses semua fitur publik dan fitur berbasis profil |
| **Admin** | Administrator yang dapat mengelola data dan memproses pengajuan |
| **Laboran** | Teknisi laboratorium yang memproses pengujian dan pemeriksaan |
| **Superadmin** | Super administrator yang dapat mengelola semua termasuk user dan CMS |

---

## 1. Autentikasi & Profil

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A
    actor "Laboran" as L
    actor "Superadmin" as S

    package "Autentikasi" {
        usecase "Login" as UC1
        usecase "Register" as UC2
        usecase "Lupa Password" as UC3
        usecase "Reset Password" as UC4
        usecase "Logout" as UC5
        usecase "Verifikasi Email" as UC6
    }

    package "Profil" {
        usecase "Lihat Profil" as UC7
        usecase "Edit Profil" as UC8
        usecase "Ubah Password" as UC9
        usecase "Lengkapi Profil" as UC10
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10

    A --> UC1
    A --> UC5
    A --> UC7
    A --> UC8
    A --> UC9

    L --> UC1
    L --> UC5
    L --> UC7
    L --> UC8
    L --> UC9

    S --> UC1
    S --> UC5
    S --> UC7
    S --> UC8
    S --> UC9
```

---

## 2. Manajemen Alat & Peminjaman

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A

    package "Katalog Alat (Publik)" {
        usecase "Lihat Daftar Alat" as UC1
        usecase "Detail Alat" as UC2
        usecase "Cari Alat" as UC3
    }

    package "Peminjaman Alat" {
        usecase "Tambah ke Keranjang" as UC4
        usecase "Edit Keranjang" as UC5
        usecase "Buat Pengajuan Peminjaman" as UC6
        usecase "Lihat Status Peminjaman" as UC7
        usecase "Batalkan Peminjaman" as UC8
        usecase "Unduh Invoice" as UC9
        usecase "Unduh Dokumen" as UC10
        usecase "Export Riwayat" as UC11
    }

    package "Manajemen Alat (Admin)" {
        usecase "Tambah Alat" as UC12
        usecase "Edit Alat" as UC13
        usecase "Hapus Alat" as UC14
        usecase "Import Alat" as UC15
        usecase "Export Alat" as UC16
        usecase "Kelola Kategori" as UC17
    }

    package "Proses Peminjaman (Admin)" {
        usecase "Setujui Peminjaman" as UC18
        usecase "Tolak Peminjaman" as UC19
        usecase "Proses Pengembalian" as UC20
        usecase "Update Billing" as UC21
        usecase "Update Pembayaran" as UC22
        usecase "Lihat Notifikasi" as UC23
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10
    U --> UC11

    A --> UC12
    A --> UC13
    A --> UC14
    A --> UC15
    A --> UC16
    A --> UC17
    A --> UC18
    A --> UC19
    A --> UC20
    A --> UC21
    A --> UC22
    A --> UC23
```

---

## 3. Pengujian Sampel

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A
    actor "Laboran" as L

    package "Katalog Pengujian (Publik)" {
        usecase "Lihat Katalog Parameter" as UC1
        usecase "Lihat Detail Parameter" as UC2
    }

    package "Pengajuan Pengujian" {
        usecase "Tambah Parameter ke Keranjang" as UC3
        usecase "Hapus dari Keranjang" as UC4
        usecase "Checkout Pengujian" as UC5
        usecase "Submit Sampel" as UC6
        usecase "Lihat Status Pengujian" as UC7
        usecase "Batalkan Pengujian" as UC8
        usecase "Unduh Invoice" as UC9
        usecase "Unduh Hasil Pengujian" as UC10
    }

    package "Manajemen Parameter (Admin)" {
        usecase "Kelola Satuan Sampel" as UC11
        usecase "Kelola Parameter Pengujian" as UC12
        usecase "Kelola Bentuk Sampel" as UC13
        usecase "Kelola Jenis Sampel" as UC14
        usecase "Toggle Aktif Parameter" as UC15
    }

    package "Proses Pengujian (Admin/Laboran)" {
        usecase "Buat Pengujian Baru (Admin)" as UC16
        usecase "Edit Pengujian (Admin)" as UC17
        usecase "Assign Laboran" as UC18
        usecase "Update Status" as UC19
        usecase "Upload Hasil" as UC20
        usecase "Update Pembayaran" as UC21
        usecase "Cetak Surat Hasil" as UC22
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10

    A --> UC11
    A --> UC12
    A --> UC13
    A --> UC14
    A --> UC15
    A --> UC16
    A --> UC17
    A --> UC18
    A --> UC19
    A --> UC20
    A --> UC21
    A --> UC22

    L --> UC19
    L --> UC20
    L --> UC21
    L --> UC22
```

---

## 4. Proposal Riset

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A
    actor "Laboran" as L

    package "Pengajuan Riset" {
        usecase "Buat Proposal Riset" as UC1
        usecase "Tambah Anggota Tim" as UC2
        usecase "Tambah Alat Riset" as UC3
        usecase "Lihat Status Proposal" as UC4
        usecase "Batalkan Proposal" as UC5
        usecase "Unduh Invoice" as UC6
        usecase "Akses Dokumen" as UC7
    }

    package "Logbook Riset" {
        usecase "Buka Logbook" as UC8
        usecase "Tambah Log Harian" as UC9
        usecase "Hapus Log Harian" as UC10
        usecase "Cetak Logbook" as UC11
    }

    package "Manajemen Riset (Admin)" {
        usecase "Lihat Daftar Proposal" as UC12
        usecase "Review Proposal" as UC13
        usecase "Setujui Proposal" as UC14
        usecase "Tolak Proposal" as UC15
        usecase "Assign Laboran" as UC16
        usecase "Update Pembayaran" as UC17
        usecase "Kenakan Penalty" as UC18
        usecase "Export Data Riset" as UC19
    }

    package "Kelola Bench Fee (Admin)" {
        usecase "Lihat Tarif Bench Fee" as UC20
        usecase "Update Tarif Bench Fee" as UC21
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10
    U --> UC11

    A --> UC12
    A --> UC13
    A --> UC14
    A --> UC15
    A --> UC16
    A --> UC17
    A --> UC18
    A --> UC19
    A --> UC20
    A --> UC21
```

---

## 5. Pemeriksaan Kesehatan

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A
    actor "Laboran" as L

    package "Booking Kesehatan" {
        usecase "Lihat Katalog Pemeriksaan" as UC1
        usecase "Booking Pemeriksaan" as UC2
        usecase "Lihat Perkiraan Biaya" as UC3
        usecase "Lihat Status Booking" as UC4
        usecase "Lihat Antrian" as UC5
        usecase "Batalkan Booking" as UC6
        usecase "Unduh Invoice" as UC7
        usecase "Unduh Surat Hasil" as UC8
        usecase "Unduh Hasil" as UC9
    }

    package "Manajemen Jenis Pemeriksaan (Admin)" {
        usecase "Kelola Jenis Pemeriksaan" as UC10
    }

    package "Proses Pemeriksaan (Admin/Laboran)" {
        usecase "Lihat Daftar Booking" as UC11
        usecase "Update Status" as UC12
        usecase "Upload Hasil" as UC13
        usecase "Update Pembayaran" as UC14
    }

    package "Jadwal Pemeriksa (Admin)" {
        usecase "Kelola Jadwal Layanan" as UC15
        usecase "Kelola Jadwal Mingguan Pemeriksa" as UC16
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9

    A --> UC10
    A --> UC11
    A --> UC12
    A --> UC13
    A --> UC14
    A --> UC15
    A --> UC16

    L --> UC12
    L --> UC13
    L --> UC14
```

---

## 6. Event & Workshop

```mermaid
usecaseDiagram
    actor "User" as U
    actor "Admin" as A

    package "Event (Publik)" {
        usecase "Lihat Daftar Event" as UC1
        usecase "Detail Event" as UC2
    }

    package "Pendaftaran Event" {
        usecase "Daftar Event" as UC3
        usecase "Daftar untuk Teman" as UC4
        usecase "Cari Teman" as UC5
        usecase "Lihat Status Pendaftaran" as UC6
        usecase "Absensi Event" as UC7
        usecase "Unduh Sertifikat" as UC8
    }

    package "Manajemen Event (Admin)" {
        usecase "Buat Event" as UC9
        usecase "Edit Event" as UC10
        usecase "Hapus Event" as UC11
        usecase "Export Data Event" as UC12
        usecase "Export Peserta" as UC13
        usecase "Export Presensi" as UC14
        usecase "Kelola Template Sertifikat" as UC15
        usecase "Generate Sertifikat Massal" as UC16
        usecase "Update Status Pendaftaran" as UC17
        usecase "Tandai Kehadiran" as UC18
        usecase "Bulk Update Status" as UC19
    }

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8

    A --> UC9
    A --> UC10
    A --> UC11
    A --> UC12
    A --> UC13
    A --> UC14
    A --> UC15
    A --> UC16
    A --> UC17
    A --> UC18
    A --> UC19
```

---

## 7. CMS & Pengaturan

```mermaid
usecaseDiagram
    actor "Superadmin" as S

    package "Manajemen Pengguna" {
        usecase "Lihat Daftar User" as UC1
        usecase "Tambah User" as UC2
        usecase "Edit User" as UC3
        usecase "Hapus User" as UC4
        usecase "Import User" as UC5
        usecase "Export User" as UC6
    }

    package "Manajemen Menu" {
        usecase "Kelola Menu Sidebar" as UC7
        usecase "Tambah Item Menu" as UC8
        usecase "Edit Item Menu" as UC9
        usecase "Hapus Item Menu" as UC10
        usecase "Urutkan Menu" as UC11
        usecase "Toggle Aktif Menu" as UC12
        usecase "Kelola Branding" as UC13
    }

    package "CMS Landing Page" {
        usecase "Kelola Testimoni" as UC14
        usecase "Kelola FAQ" as UC15
        usecase "Kelola Footer Logo" as UC16
        usecase "Kelola Logo (Upload/Edit/Hapus)" as UC17
        usecase "Urutkan Logo" as UC18
        usecase "Kelola Section Landing Page" as UC19
    }

    package "Log Aktivitas" {
        usecase "Lihat Log Aktivitas" as UC20
        usecase "Filter Log" as UC21
    }

    package "Manajemen Laboratorium" {
        usecase "Kelola Laboratorium" as UC22
    }

    package "Pengaturan" {
        usecase "Kelola WhatsApp" as UC23
        usecase "Kelola Template Invoice" as UC24
    }

    package "Dokumen" {
        usecase "Lihat Unduhan Dokumen" as UC25
        usecase "Preview Dokumen" as UC26
        usecase "Download Dokumen" as UC27
    }

    S --> UC1
    S --> UC2
    S --> UC3
    S --> UC4
    S --> UC5
    S --> UC6
    S --> UC7
    S --> UC8
    S --> UC9
    S --> UC10
    S --> UC11
    S --> UC12
    S --> UC13
    S --> UC14
    S --> UC15
    S --> UC16
    S --> UC17
    S --> UC18
    S --> UC19
    S --> UC20
    S --> UC21
    S --> UC22
    S --> UC23
    S --> UC24
    S --> UC25
    S --> UC26
    S --> UC27
```

---

## 8. Dashboard & Laporan

```mermaid
usecaseDiagram
    actor "Admin" as A
    actor "Laboran" as L

    package "Dashboard" {
        usecase "Lihat Dashboard" as UC1
    }

    package "Laboran Dashboard" {
        usecase "Lihat Daftar Pengujian" as UC2
        usecase "Lihat Daftar Pemeriksaan" as UC3
        usecase "Update Status Pengujian" as UC4
        usecase "Update Status Pemeriksaan" as UC5
        usecase "Cetak Hasil" as UC6
    }

    A --> UC1

    L --> UC2
    L --> UC3
    L --> UC4
    L --> UC5
    L --> UC6
```

---

## Ringkasan Use Case per Aktor

### User (Pengguna Biasa)
| No | Use Case |
|----|----------|
| 1 | Login, Register, Lupa Password |
| 2 | Lengkapi & Edit Profil |
| 3 | Lihat Katalog Alat |
| 4 | Pinjam Alat (Keranjang → Checkout → Invoice) |
| 5 | Lihat Katalog Pengujian |
| 6 | Ajukan Pengujian Sampel |
| 7 | Lihat Katalog Pemeriksaan |
| 8 | Booking Pemeriksaan Kesehatan |
| 9 | Daftar Event |
| 10 | Absensi & Unduh Sertifikat Event |
| 11 | Buat Proposal Riset |
| 12 | Isi Logbook Riset |

### Admin
| No | Use Case |
|----|----------|
| 1 | Kelola Alat & Kategori |
| 2 | Proses Peminjaman (Setujui/Tolak/Pengembalian) |
| 3 | Kelola Parameter Pengujian |
| 4 | Proses Pengujian Sampel |
| 5 | Proses Proposal Riset |
| 6 | Kelola Jenis Pemeriksaan |
| 7 | Proses Pemeriksaan Kesehatan |
| 8 | Kelola Event & Sertifikat |
| 9 | Kelola Jadwal Layanan |
| 10 | Lihat Notifikasi |

### Laboran
| No | Use Case |
|----|----------|
| 1 | Proses Pengujian Sampel |
| 2 | Upload Hasil Pengujian |
| 3 | Proses Pemeriksaan Kesehatan |
| 4 | Upload Hasil Pemeriksaan |
| 5 | Cetak Hasil |

### Superadmin
| No | Use Case |
|----|----------|
| 1 | Kelola User (CRUD + Import/Export) |
| 2 | Kelola Menu Sidebar |
| 3 | Kelola CMS (Testimoni, FAQ, Footer, Landing Page) |
| 4 | Lihat Log Aktivitas |
| 5 | Kelola Laboratorium |
| 6 | Kelola Pengaturan (WhatsApp, Invoice) |
| 7 | Akses Semua Fitur Admin |

Bertindaklah sebagai Senior Full-Stack Developer yang ahli dalam menggunakan Laravel, MySQL, dan Tailwind CSS. 

Tugas Anda adalah membuatkan panduan lengkap, struktur database (migration & relasi model), controller, dan contoh kode view (Blade + Tailwind) untuk sebuah "Sistem Manajemen Pembayaran WiFi & Invoicing Custom".

Berikut adalah spesifikasi teknis dan fitur yang harus ada di dalam sistem:

### 1. Tech Stack
* Framework: Laravel (versi terbaru)
* Database: MySQL
* Styling: Tailwind CSS (gunakan Alpine.js atau jQuery jika diperlukan untuk interaksi form dinamis)

### 2. Struktur Database & Model (Tolong buatkan kode Migration dan Model-nya)
Sistem ini membutuhkan beberapa tabel utama dengan relasi sebagai berikut:
* `paket_harga`: id, nama_paket, harga, deskripsi, timestamps.
* `pelanggan`: id, nama, alamat, no_hp, paket_id (foreign key), timestamps.
* `pembayaran_wifi`: id, pelanggan_id (foreign key), bulan_tagihan (string/date), tahun_tagihan (year), total_tagihan, nominal_dibayar, sisa_tagihan, status (enum: 'Lunas', 'Cicilan'), timestamps.
* `cicilan_pembayaran` (opsional jika AI merasa perlu tabel terpisah untuk riwayat cicilan): id, pembayaran_wifi_id, tanggal_bayar, nominal, timestamps.
* `nota_custom`: id, nomor_nota, tanggal, nama_pembeli, total_harga, timestamps.
* `detail_nota_custom`: id, nota_custom_id (foreign key), nama_item, kuantitas, harga_satuan, subtotal, timestamps.

### 3. Fitur Utama & Menu Navigasi
Tolong buatkan logika Controller untuk fitur-fitur berikut:

* Menu Master Data:
  - Master Paket Harga: CRUD data paket internet.
  - Master Pelanggan: CRUD data pelanggan, saat menambah pelanggan harus memilih Paket Harga.

* Menu Pembayaran WiFi:
  - Form untuk memilih pelanggan, memilih bulan & tahun tagihan.
  - Sistem otomatis menarik data tagihan sesuai paket pelanggan.
  - Terdapat opsi input pembayaran: Jika nominal bayar == total tagihan, maka status "Lunas". Jika nominal bayar < total tagihan, maka status "Cicilan" dan sisa tagihan tercatat.
  - Jika pelanggan sudah punya tagihan "Cicilan" di bulan tertentu, form akan mengarah ke pelunasan sisa tagihan tersebut.

* Menu Cetak Kuitansi Penagihan (WiFi):
  - Halaman cetak/print dengan desain layout kuitansi (menggunakan Tailwind).
  - Harus ada Kop Surat (Logo, Nama Perusahaan, Alamat, Kontak).
  - Menampilkan identitas pelanggan, tagihan bulan apa, total tagihan.
  - Jika pembayaran berupa "Cicilan", tampilkan histori nominal yang sudah dibayar dan sisa yang belum dibayar ke dalam kuitansi penagihan tersebut.

* Menu Nota Custom (Non-WiFi):
  - Form transaksi dinamis (Multiple insert array).
  - Input: Nama Pembeli, Tanggal.
  - Input Dinamis (Bisa tambah baris/Add Row): Nama Barang/Jasa (custom text), Kuantitas, Harga Satuan. Subtotal akan otomatis terhitung.
  - Fitur simpan dan langsung Cetak Nota layout struk/A4 dengan Kop Surat.

### 4. Instruksi Output yang Diharapkan
Tolong berikan kode dengan urutan berikut:
1. Kode Migrations untuk semua tabel di atas.
2. Kode Models beserta relasinya (hasMany, belongsTo).
3. Kode `PembayaranWifiController` (Fokus pada logika pembuatan tagihan dan penanganan status Lunas/Cicilan).
4. Kode `NotaCustomController` (Fokus pada logika simpan master-detail item dinamis).
5. Contoh kode Blade View menggunakan Tailwind CSS untuk halaman "Cetak Kuitansi WiFi" yang rapi dan siap di-print.

Pastikan kode mengikuti best practice Laravel (Clean code, gunakan DB Transaction saat menyimpan Nota Custom, dan penamaan variabel yang jelas).
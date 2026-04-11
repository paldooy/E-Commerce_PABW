# Ecommerce Python + Vue

Dokumen ini adalah acuan utama implementasi agar konsisten dengan artefak analisis sistem:
- Use Case Diagram
- ERD
- Activity Diagram
- DFD

Seluruh pengembangan fitur berikutnya harus mengikuti aturan bisnis dan batasan di dokumen ini.

## 1. Tujuan Sistem

Membangun sistem e-commerce sederhana dengan 3 aktor utama:
- Pengguna (berperan sebagai pembeli dan juga penjual)
- Kurir
- Admin

Fokus sistem adalah alur transaksi barang, pengantaran, dan wallet internal sederhana tanpa integrasi payment gateway.

## 2. Batasan dan Asumsi Utama

1. Tidak ada COD.
2. Tidak ada topup otomatis dari aplikasi web.
3. Tidak ada cashout otomatis dari aplikasi web.
4. Tidak ada cancel mandiri oleh pembeli.
5. Tidak ada auto restock saat retur.
6. Admin tidak mengubah status item pesanan. Admin hanya mengelola listing etalase dan saldo wallet manual.

## 3. Aturan Wallet (Wajib Konsisten)

Wallet pada sistem ini bersifat sederhana dan semi-manual:

1. Topup berada di luar sistem web.
2. Pengguna atau kurir menyerahkan uang tunai kepada admin.
3. Admin menambahkan saldo wallet secara manual melalui endpoint admin.
4. Saat pengguna atau kurir ingin mencairkan saldo, admin memberikan uang tunai.
5. Admin mengurangi saldo wallet secara manual melalui endpoint admin.
6. Sistem tidak memproses payment gateway, virtual account, e-wallet provider, ataupun transfer bank otomatis.

Konsekuensi desain:
- Sumber kebenaran saldo adalah tabel wallet dan wallet_mutations.
- Setiap penambahan/pengurangan manual wajib tercatat sebagai mutasi bertipe admin_adjustment_plus atau admin_adjustment_minus.

## 4. Aktor dan Tanggung Jawab

### Pengguna
- Registrasi, login, lihat produk
- Kelola produk sendiri (tambah, ubah, hapus)
- Kelola keranjang
- Checkout
- Pantau status order sebagai pembeli
- Pantau status order sebagai penjual
- Proses order sebagai penjual (transisi status)
- Konfirmasi terima barang sebagai pembeli
- Ajukan komplain

### Kurir
- Login
- Lihat daftar tugas kirim/retur sesuai status yang visible
- Ambil tugas pengiriman
- Update status pengiriman
- Antar balik barang retur ke penjual

### Admin
- Login
- Kelola akun user dan kurir
- Tambah/kurangi saldo user dan kurir secara manual
- Monitor listing dan ubah status listing
- Hapus listing

## 5. Ringkasan Arsitektur

- Backend: FastAPI, SQLAlchemy, SQLite
- Frontend: Vue 3, Vite
- Auth: JWT Bearer token

Struktur folder:
- backend/: API server dan model domain
- frontend/: UI client

## 6. ERD Implementasi (Sinkron Dengan Kode)

Entitas utama:
- accounts
- wallets
- wallet_mutations
- products
- carts
- cart_items
- orders
- order_items
- order_item_status_logs

Relasi inti:
1. Satu account memiliki satu wallet.
2. Satu wallet memiliki banyak wallet_mutations.
3. Satu buyer memiliki satu cart.
4. Satu cart memiliki banyak cart_items.
5. Satu product dimiliki satu seller (account role user).
6. Satu order memiliki banyak order_items.
7. Setiap order_item mereferensikan buyer, seller, dan opsional courier.
8. Setiap perubahan status order_item dicatat di order_item_status_logs.

## 7. DFD Konseptual

### Context Level (L0)
Entitas eksternal:
- Pengguna
- Kurir
- Admin

Proses inti sistem:
1. Manajemen akun dan autentikasi
2. Manajemen katalog produk
3. Manajemen keranjang dan checkout
4. Manajemen status order dan pengiriman
5. Manajemen wallet dan mutasi

Data store:
- accounts, wallets, wallet_mutations, products, carts, cart_items, orders, order_items, order_item_status_logs

### Level 1 (Ringkas)
1. Auth Service: register/login/validasi token.
2. Catalog Service: CRUD produk user + moderasi listing admin.
3. Cart Service: tambah, ubah, hapus item keranjang.
4. Checkout Service: validasi stok, validasi saldo, debit wallet buyer, kurangi stok, buat order/item.
5. Fulfillment Service: transisi status seller, kurir, buyer.
6. Wallet Admin Service: penyesuaian saldo manual plus/minus.
7. Return Settlement Service: penyelesaian retur dan refund escrow sesuai alur status, tanpa ubah status manual oleh admin.

## 8. Activity Flow Inti

### A. Activity Pembelian
1. User login.
2. User lihat produk.
3. User tambah item ke keranjang.
4. User checkout.
5. Sistem validasi stok.
6. Sistem validasi saldo.
7. Jika valid: saldo buyer didebit, stok dikurangi, order dan order item dibuat.
8. User memantau status sampai barang diterima atau komplain.

### B. Activity Fulfillment Penjual
1. Seller melihat item dengan status menunggu_penjual.
2. Seller ubah ke diproses_penjual.
3. Seller ubah ke menunggu_kurir.

### C. Activity Kurir
1. Kurir melihat item visible (menunggu_kurir, sedang_dikirim, dikirim_balik).
2. Kurir ambil tugas, status menjadi sedang_dikirim.
3. Kurir konfirmasi sampai, status menjadi sampai_di_tujuan.
4. Jika retur: setelah pembeli komplain, sistem membuat tugas retur dengan status kembali menjadi menunggu_kurir, lalu kurir ubah ke dikirim_balik sampai kembali ke menunggu_penjual.

### D. Activity Penyelesaian
1. Jika buyer konfirmasi terima: status diterima_pembeli, saldo seller dikreditkan.
2. Jika buyer komplain: status dikomplain dan alasan tersimpan, lalu sistem menyiapkan retur dengan mengubah status kembali ke menunggu_kurir agar kurir bisa menjemput barang.
3. Jika retur selesai dan barang kembali ke penjual: item ditutup sebagai transaksi_gagal dan buyer direfund melalui escrow.

## 9. State dan Transisi Resmi Order Item

State yang digunakan:
- menunggu_penjual
- diproses_penjual
- menunggu_kurir
- sedang_dikirim
- sampai_di_tujuan
- diterima_pembeli
- dikomplain
- dikirim_balik
- transaksi_gagal

Transisi valid:
1. menunggu_penjual -> diproses_penjual (aktor: seller)
2. diproses_penjual -> menunggu_kurir (aktor: seller)
3. menunggu_kurir -> sedang_dikirim (aktor: kurir)
4. menunggu_kurir -> dikirim_balik (aktor: kurir)
5. sedang_dikirim -> sampai_di_tujuan (aktor: kurir)
6. sampai_di_tujuan -> diterima_pembeli (aktor: buyer)
7. sampai_di_tujuan -> dikomplain (aktor: buyer)
8. dikomplain -> menunggu_kurir (aktor: sistem)  
	(tugas retur dibuat agar kurir bisa melihat dan mengambil barang retur)
9. dikirim_balik -> menunggu_penjual (aktor: kurir)
10. menunggu_penjual -> transaksi_gagal (aktor: sistem, khusus penutupan retur)

Catatan:
- Setiap transisi wajib tercatat pada order_item_status_logs.
- transaksi_gagal dan diterima_pembeli diperlakukan sebagai final state untuk settlement escrow.

## 10. Aturan Escrow dan Settlement

1. Saat checkout, order dibuat dengan escrow_status funded.
2. Saat item diterima pembeli, seller menerima payout per item.
3. Saat item masuk transaksi_gagal (hasil penutupan retur), buyer menerima refund penuh per item.
4. Saat semua item dalam satu order sudah final:
	- Semua transaksi_gagal -> escrow_status menjadi refunded.
	- Campuran atau semua diterima_pembeli -> escrow_status menjadi released.

## 11. Mapping Use Case ke Endpoint

### Auth
- POST /auth/register
- POST /auth/login
- GET /me

### Wallet
- GET /me/wallet
- POST /admin/wallets/add
- POST /admin/wallets/deduct

### Produk
- GET /products
- POST /products
- PATCH /products/{product_id}
- DELETE /products/{product_id}
- PATCH /admin/products/{product_id}/status
- DELETE /admin/products/{product_id}

### Keranjang dan Checkout
- GET /cart
- POST /cart/items
- PATCH /cart/items/{item_id}
- DELETE /cart/items/{item_id}
- POST /checkout

### Order dan Status
- GET /orders/items/mine
- PATCH /seller/order-items/{item_id}/status
- GET /courier/order-items
- PATCH /courier/order-items/{item_id}/status
- PATCH /buyer/order-items/{item_id}/received
- PATCH /buyer/order-items/{item_id}/complain

### Admin User dan Kurir
- GET /admin/users
- POST /admin/users
- GET /admin/couriers
- POST /admin/couriers
- PATCH /admin/accounts/{account_id}
- DELETE /admin/accounts/{account_id}

## 12. Menjalankan Proyek

### Backend
```bash
cd backend
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8000
```

Akun admin default dibuat otomatis:
- username: admin
- password: admin123

### Frontend
```bash
cd frontend
npm install
npm run dev
```

Default API base URL frontend:
- http://127.0.0.1:8000

## 13. Aturan Konsistensi Pengembangan Selanjutnya

1. Jangan menambah status order di luar daftar resmi tanpa update dokumen ini.
2. Jangan menambah mekanisme topup/cashout otomatis sebelum ada perubahan requirement.
3. Setiap perubahan alur bisnis wajib menjaga kompatibilitas role-based access.
4. Setiap perubahan transaksi wallet wajib menghasilkan mutation record.
5. Jika menambah endpoint baru, update section Mapping Use Case ke Endpoint.
6. Jika menambah tabel/relasi, update section ERD Implementasi.
7. Jika mengubah alur proses, update section Activity Flow dan DFD Konseptual.

## 14. Out of Scope Saat Ini

1. Integrasi payment gateway.
2. Integrasi sistem logistik eksternal.
3. Auto reconcile transaksi bank.
4. Notifikasi real-time lintas kanal.
5. Multi-warehouse dan manajemen inventori lanjutan.

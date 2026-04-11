# State Diagram Proyek Ecommerce (Python + Vue)

Dokumen ini berisi kumpulan state diagram untuk domain utama sistem.
Referensi utama dokumen ini adalah **Analisis Kebutuhan Sistem Ecommerce** (dokumen kebutuhan yang disepakati).
Jika ada perbedaan dengan implementasi backend saat ini, perbedaannya dicatat di bagian akhir.

Catatan khusus wallet:
- Top up dan pencairan uang terjadi di luar aplikasi web.
- Admin hanya mencatat penambahan dan pengurangan saldo secara manual berdasarkan transaksi tunai.

## Referensi status barang

Status barang (listing dan barang yang sudah dibeli) dibatasi hanya pada:
- Listing: **stok kosong**, **stok tersedia**.
- Barang dibeli (order item): **menunggu penjual**, **diproses penjual**, **menunggu kurir**, **sedang dikirim**, **sampai di tujuan**, **diterima pembeli**, **dikomplain**, **dikirim balik**, **transaksi gagal**.

## Konvensi penulisan

- State adalah kondisi atau status sebuah entitas.
- Event adalah pemicu yang menyebabkan perpindahan antar state.
- Format label transisi: `Aktor melakukan aksi [Syarat] / Aksi`.
  - `[Syarat]` opsional untuk kondisi.
  - `/ Aksi` opsional untuk efek samping.
- Aktor ditulis eksplisit (Pengguna, Penjual, Kurir, Admin, Sistem).

Di backend, beberapa status disimpan sebagai enum dengan gaya penamaan teknis.
Di dokumen ini, label state ditulis dengan spasi untuk keterbacaan.

## 1. Registrasi akun

```mermaid
stateDiagram-v2
    state "Tamu" as Guest
    state "Isi form registrasi" as IsiFormRegistrasi
    state "Validasi data registrasi" as ValidasiRegistrasi
    state "Registrasi gagal" as RegistrasiGagal
    state "Akun dibuat" as AkunDibuat
    state "Wallet dibuat" as WalletDibuat
    state "Registrasi berhasil" as RegistrasiBerhasil

    [*] --> Guest

    Guest --> IsiFormRegistrasi : Pengguna membuka form registrasi
    IsiFormRegistrasi --> ValidasiRegistrasi : Pengguna mengirim registrasi

    ValidasiRegistrasi --> RegistrasiGagal : Sistem menolak registrasi [data tidak valid atau duplikat]
    RegistrasiGagal --> IsiFormRegistrasi : Pengguna memperbaiki data

    ValidasiRegistrasi --> AkunDibuat : Sistem menyimpan akun [validasi berhasil]
    AkunDibuat --> WalletDibuat : Sistem membuat wallet / saldo awal 0
    WalletDibuat --> RegistrasiBerhasil : Sistem menuntaskan registrasi

    RegistrasiBerhasil --> [*]
```

## 2. Autentikasi dan sesi login

```mermaid
stateDiagram-v2
    state "Tamu" as Guest
    state "Isi form login" as IsiFormLogin
    state "Autentikasi diproses" as AutentikasiDiproses
    state "Login gagal" as LoginGagal
    state "Terautentikasi" as Terautentikasi
    state "Sesi berakhir" as SesiBerakhir

    [*] --> Guest

    Guest --> IsiFormLogin : Pengguna membuka form login
    IsiFormLogin --> AutentikasiDiproses : Pengguna mengirim kredensial

    AutentikasiDiproses --> LoginGagal : Sistem menolak login [kredensial salah atau akun tidak aktif]
    LoginGagal --> IsiFormLogin : Pengguna coba lagi

    AutentikasiDiproses --> Terautentikasi : Sistem menerbitkan token
    Terautentikasi --> SesiBerakhir : Pengguna logout
    Terautentikasi --> SesiBerakhir : Sistem mendeteksi token kedaluwarsa atau tidak valid
    SesiBerakhir --> Guest : Sistem mengakhiri sesi
```

Catatan: pembatasan role terjadi saat mengakses endpoint tertentu. Jika role tidak sesuai, request ditolak (403) tanpa mengubah sesi.

## 3. Siklus hidup akun pengguna

```mermaid
stateDiagram-v2
    state "Akun aktif" as Aktif
    state "Akun nonaktif" as Nonaktif
    state "Akun dihapus" as Dihapus

    [*] --> Aktif : Admin membuat akun / sistem mengaktifkan akun [registrasi berhasil]

    Aktif --> Nonaktif : Admin menonaktifkan akun
    Nonaktif --> Aktif : Admin mengaktifkan akun

    Aktif --> Dihapus : Admin menghapus akun
    Nonaktif --> Dihapus : Admin menghapus akun

    Dihapus --> [*]
```

## 4. Siklus hidup akun kurir

```mermaid
stateDiagram-v2
    state "Kurir aktif" as Aktif
    state "Kurir nonaktif" as Nonaktif
    state "Kurir dihapus" as Dihapus

    [*] --> Aktif : Admin membuat akun kurir

    Aktif --> Nonaktif : Admin menonaktifkan kurir
    Nonaktif --> Aktif : Admin mengaktifkan kurir

    Aktif --> Dihapus : Admin menghapus kurir
    Nonaktif --> Dihapus : Admin menghapus kurir

    Dihapus --> [*]
```

## 5. Siklus hidup listing produk

```mermaid
stateDiagram-v2
    state "Stok tersedia" as StokTersedia
    state "Stok kosong" as StokKosong
    state "Listing dihapus" as ListingDihapus

    [*] --> StokTersedia : Penjual membuat produk [stok lebih dari 0]
    [*] --> StokKosong : Penjual membuat produk [stok sama dengan 0]

    StokTersedia --> StokKosong : Sistem mengurangi stok [terjual habis]
    StokKosong --> StokTersedia : Penjual menambah stok [stok lebih dari 0]

    StokTersedia --> StokTersedia : Penjual mengubah detail produk
    StokKosong --> StokKosong : Penjual mengubah detail produk

    StokTersedia --> StokKosong : Admin mengubah status listing
    StokKosong --> StokTersedia : Admin mengubah status listing

    StokTersedia --> ListingDihapus : Penjual atau admin menghapus produk
    StokKosong --> ListingDihapus : Penjual atau admin menghapus produk

    ListingDihapus --> [*]
```

## 6. Keranjang belanja

```mermaid
stateDiagram-v2
    state "Keranjang belum ada" as KeranjangBelumAda
    state "Keranjang kosong" as KeranjangKosong
    state "Keranjang berisi item" as KeranjangBerisi

    [*] --> KeranjangBelumAda
    KeranjangBelumAda --> KeranjangKosong : Pengguna membuka keranjang / sistem membuat keranjang jika belum ada

    KeranjangKosong --> KeranjangBerisi : Pengguna menambah item [stok cukup]
    KeranjangKosong --> KeranjangKosong : Sistem menolak penambahan item [stok tidak cukup]

    KeranjangBerisi --> KeranjangBerisi : Pengguna menambah item atau ubah jumlah [stok cukup]
    KeranjangBerisi --> KeranjangBerisi : Sistem menolak perubahan jumlah [stok tidak cukup]

    KeranjangBerisi --> KeranjangBerisi : Pengguna menghapus sebagian item
    KeranjangBerisi --> KeranjangKosong : Pengguna menghapus item terakhir

    KeranjangBerisi --> KeranjangKosong : Sistem menghapus item keranjang [checkout berhasil]
    KeranjangKosong --> KeranjangKosong : Sistem menolak checkout [keranjang kosong]
```

## 7. Proses checkout

```mermaid
stateDiagram-v2
    state "Mulai checkout" as Mulai
    state "Validasi keranjang" as ValidasiKeranjang
    state "Validasi produk dan stok" as ValidasiProdukStok
    state "Validasi saldo" as ValidasiSaldo
    state "Order dibuat (escrow funded)" as OrderDibuat
    state "Saldo buyer didebit dan mutasi dicatat" as DebitSaldo
    state "Order item dibuat dan stok dikurangi" as BuatItemDanStok
    state "Keranjang dikosongkan" as KeranjangDikosongkan
    state "Checkout berhasil" as CheckoutBerhasil

    state "Gagal: keranjang kosong" as GagalKeranjangKosong
    state "Gagal: produk tidak valid" as GagalProdukTidakValid
    state "Gagal: stok tidak cukup" as GagalStokTidakCukup
    state "Gagal: saldo tidak cukup" as GagalSaldoTidakCukup

    [*] --> Mulai
    Mulai --> ValidasiKeranjang : Pengguna memulai checkout

    ValidasiKeranjang --> GagalKeranjangKosong : Sistem menolak checkout [keranjang kosong]
    ValidasiKeranjang --> ValidasiProdukStok : Sistem memvalidasi produk dan stok

    ValidasiProdukStok --> GagalProdukTidakValid : Sistem menolak checkout [produk tidak ditemukan]
    ValidasiProdukStok --> GagalStokTidakCukup : Sistem menolak checkout [stok tidak cukup]
    ValidasiProdukStok --> ValidasiSaldo : Sistem memvalidasi saldo

    ValidasiSaldo --> GagalSaldoTidakCukup : Sistem menolak checkout [saldo tidak cukup]
    ValidasiSaldo --> OrderDibuat : Sistem membuat order

    OrderDibuat --> DebitSaldo : Sistem mendebit saldo buyer / catat mutasi checkout
    DebitSaldo --> BuatItemDanStok : Sistem membuat order item / kurangi stok / sinkron status produk
    BuatItemDanStok --> KeranjangDikosongkan : Sistem menghapus item keranjang
    KeranjangDikosongkan --> CheckoutBerhasil : Sistem menyelesaikan checkout

    GagalKeranjangKosong --> [*]
    GagalProdukTidakValid --> [*]
    GagalStokTidakCukup --> [*]
    GagalSaldoTidakCukup --> [*]
    CheckoutBerhasil --> [*]
```

## 8. Status order item (inti alur transaksi)

```mermaid
stateDiagram-v2
    state "Menunggu penjual" as MenungguPenjual
    state "Diproses penjual" as DiprosesPenjual
    state "Menunggu kurir" as MenungguKurir
    state "Sedang dikirim" as SedangDikirim
    state "Sampai di tujuan" as SampaiDiTujuan
    state "Diterima pembeli" as DiterimaPembeli
    state "Dikomplain" as Dikomplain
    state "Dikirim balik" as DikirimBalik
    state "Transaksi gagal" as TransaksiGagal

    [*] --> MenungguPenjual : Dibuat dari checkout

    MenungguPenjual --> DiprosesPenjual : Penjual memproses barang dan mengubah status barang
    DiprosesPenjual --> MenungguKurir : Penjual memanggil kurir dan mengubah status barang

    MenungguKurir --> SedangDikirim : Kurir mengambil barang dan mengubah status barang
    SedangDikirim --> DikirimBalik : Pesanan di cancel, kurir ubah status (retur)

    SedangDikirim --> SampaiDiTujuan : Barang diterima, kurir ubah status

    SampaiDiTujuan --> DiterimaPembeli : Pembeli konfirmasi diterima / saldo penjual bertambah
    SampaiDiTujuan --> Dikomplain : Pembeli ajukan komplain / simpan alasan
    Dikomplain --> MenungguKurir : Buat tugas retur
    MenungguKurir --> DikirimBalik : Kurir mengambil barang  return dan mengubah status barang

    DikirimBalik --> MenungguPenjual : Kurir mengembalikan barang dan ubah status

    MenungguPenjual --> TransaksiGagal : Retur diterima penjual [barang hasil retur] / refund item (escrow)

    DiterimaPembeli --> [*]
    TransaksiGagal --> [*]
```

## 9. Status escrow pada order

```mermaid
stateDiagram-v2
    state "Dana ditahan (funded)" as Funded
    state "Dana dilepas (released)" as Released
    state "Dana dikembalikan (refunded)" as Refunded

    [*] --> Funded : Sistem membuat escrow [checkout berhasil]

    Funded --> Funded : Sistem menunggu semua item final
    Funded --> Released : Sistem melepas escrow [minimal satu diterima]
    Funded --> Refunded : Sistem mengembalikan escrow [semua gagal]

    Released --> [*]
    Refunded --> [*]
```

## 10. Wallet saldo (level balance)

```mermaid
stateDiagram-v2
    state "Saldo nol" as SaldoNol
    state "Saldo positif" as SaldoPositif

    [*] --> SaldoNol : Sistem membuat wallet [saldo awal 0]

    SaldoNol --> SaldoPositif : Sistem mencatat kredit masuk [nominal lebih dari 0]
    SaldoPositif --> SaldoPositif : Sistem mencatat kredit masuk [nominal lebih dari 0]

    SaldoPositif --> SaldoPositif : Sistem mencatat debit valid [nominal kurang dari saldo]
    SaldoPositif --> SaldoNol : Sistem mencatat debit valid [nominal sama dengan saldo]

    SaldoNol --> SaldoNol : Sistem menolak debit [saldo tidak cukup]
    SaldoPositif --> SaldoPositif : Sistem menolak debit [saldo tidak cukup]
```

Catatan: diagram wallet saldo tidak memakai end state karena wallet bersifat berkelanjutan selama akun aktif.

## 11. Mutasi wallet manual oleh admin

```mermaid
stateDiagram-v2
    state "Permintaan penyesuaian masuk" as PermintaanMasuk
    state "Validasi admin" as ValidasiAdmin
    state "Mutasi kredit tercatat" as KreditTercatat
    state "Mutasi debit tercatat" as DebitTercatat
    state "Ditolak" as Ditolak
    state "Gagal karena saldo tidak cukup" as GagalSaldo
    state "Selesai" as Selesai

    [*] --> PermintaanMasuk
    PermintaanMasuk --> ValidasiAdmin : Admin menginput penyesuaian

    ValidasiAdmin --> Ditolak : Admin menolak [akun tidak valid atau nominal tidak valid]
    ValidasiAdmin --> KreditTercatat : Admin menyetujui cash in / saldo bertambah
    ValidasiAdmin --> DebitTercatat : Admin menyetujui cash out [saldo cukup] / saldo berkurang
    ValidasiAdmin --> GagalSaldo : Sistem menolak cash out [saldo tidak cukup]

    KreditTercatat --> Selesai
    DebitTercatat --> Selesai
    Ditolak --> Selesai
    GagalSaldo --> Selesai

    Selesai --> [*]
```

## 12. Intervensi admin pada listing etalase

```mermaid
stateDiagram-v2
    state "Listing dipantau" as MonitorListing
    state "Status stok tersedia" as StokTersedia
    state "Status stok kosong" as StokKosong
    state "Listing dihapus" as ListingDihapus
    state "Intervensi selesai" as IntervensiSelesai

    [*] --> MonitorListing

    MonitorListing --> StokTersedia : Admin mengubah status listing menjadi stok tersedia
    MonitorListing --> StokKosong : Admin mengubah status listing menjadi stok kosong
    MonitorListing --> ListingDihapus : Admin menghapus listing

    StokTersedia --> IntervensiSelesai
    StokKosong --> IntervensiSelesai
    ListingDihapus --> IntervensiSelesai

    IntervensiSelesai --> [*]
```

## 13. Alur penanggung jawab tindakan per peran

Diagram ini adalah ringkasan untuk melihat di tahap mana aktor utama biasanya melakukan aksi berikutnya.

```mermaid
stateDiagram-v2
    state "Tahap penjual" as TahapPenjual
    state "Tahap kurir" as TahapKurir
    state "Tahap pembeli" as TahapPembeli
    state "Final sukses" as FinalSukses
    state "Final gagal" as FinalGagal

    [*] --> TahapPenjual : Checkout selesai

    TahapPenjual --> TahapKurir : Penjual mengubah status menjadi menunggu kurir
    TahapKurir --> TahapPembeli : Kurir mengubah status menjadi sampai di tujuan

    TahapPembeli --> FinalSukses : Pembeli mengonfirmasi barang diterima
    TahapPembeli --> TahapKurir : Pembeli mengajukan komplain (retur)

    TahapKurir --> TahapPenjual : Kurir mengembalikan barang sampai menunggu penjual

    TahapPenjual --> FinalGagal : Sistem menutup retur / refund item (escrow)

    FinalSukses --> [*]
    FinalGagal --> [*]
```

## Catatan penting

1. Diagram 8 (status order item) adalah acuan utama untuk transisi status item.
2. Diagram 9 (escrow) diturunkan dari agregasi status final seluruh item pada satu order.
3. Diagram 10 (wallet saldo) dan 11 (mutasi wallet manual) sengaja dipisah karena fokusnya berbeda:
   - Wallet saldo membahas kondisi nilai saldo saat ini.
   - Mutasi wallet membahas jejak transaksi yang menyebabkan perubahan saldo.
4. Top up dan pencairan uang berada di luar aplikasi web, dan hanya dicatat sebagai penyesuaian manual oleh admin.

## Catatan perbedaan dengan backend saat ini

1. Penetapan kurir tidak memiliki endpoint terpisah. Di backend sekarang, kurir ditetapkan otomatis saat pertama kali kurir mengubah status dari menunggu kurir.
2. Daftar tugas kurir di backend saat ini hanya menampilkan item pada status menunggu kurir, sedang dikirim, dan dikirim balik. Item pada status sampai di tujuan dan dikomplain memang tidak muncul (kurir fokus pada barang yang sedang ditangani).
3. Admin dapat mengubah status listing produk tanpa memeriksa stok, sehingga status dan stok bisa tidak sinkron. Update produk oleh penjual selalu menyinkronkan status mengikuti stok.
4. Alur referensi menyebut bahwa setelah pembeli mengajukan retur, status kembali menjadi menunggu kurir agar kurir bisa melihat tugas penjemputan retur. Backend sekarang menyetel status menjadi dikomplain, sementara daftar tugas kurir tidak menampilkan status dikomplain. Jika ingin 100% sesuai referensi, perlu ada mekanisme untuk membuat status kembali menjadi menunggu kurir setelah komplain.
5. Alur referensi menyebut bahwa retur yang selesai (barang kembali ke penjual) berakhir menjadi transaksi gagal dan refund dilakukan melalui escrow tanpa perubahan status manual oleh admin. Backend sekarang belum mengotomasi finalisasi retur tersebut.

# Mengaktifkan Integrasi Email dan WhatsApp

Aplikasi memakai Gmail untuk mengirim tautan reset password. Notifikasi saat guru menyetujui atau menolak izin dikirim melalui template WhatsApp ke nomor wali pada profil siswa. Hasil pengiriman WhatsApp tersimpan di `notification_logs` dan dapat dikirim ulang dari menu **Notifikasi**.

## Email SMTP

Isi `.env` dengan kredensial SMTP dari penyedia email. Contoh format:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ALAMAT_EMAIL
MAIL_PASSWORD=APP_PASSWORD_GMAIL
MAIL_FROM_ADDRESS=ALAMAT_EMAIL
MAIL_FROM_NAME="SMA Negeri 1 Batu Ampar"
```

Gunakan `smtp.gmail.com`, port `587`, dan `MAIL_SCHEME=smtp`. Isi `MAIL_USERNAME` serta `MAIL_FROM_ADDRESS` dengan alamat Gmail pengirim yang sama. Buat App Password melalui pengaturan keamanan Akun Google dan isi hanya di `.env` sebagai `MAIL_PASSWORD`; jangan gunakan password login biasa. Setelah mengisi semua kredensial, ubah `MAIL_MAILER=log` menjadi `MAIL_MAILER=smtp`, lalu jalankan `php artisan config:clear`. Gmail hanya dipakai untuk email reset password.

## WhatsApp Cloud API

Isi variabel berikut di `.env` memakai data dari Meta WhatsApp Cloud API:

```dotenv
WHATSAPP_ACCESS_TOKEN=TOKEN_AKSES
WHATSAPP_PHONE_NUMBER_ID=ID_NOMOR_WHATSAPP_BUSINESS
WHATSAPP_GRAPH_VERSION=v27.0
WHATSAPP_TEMPLATE_NAME=NAMA_TEMPLATE_YANG_DISETUJUI
WHATSAPP_TEMPLATE_LANGUAGE=id
```

Buat dan minta persetujuan template di Meta sebelum digunakan. Isi body template harus memiliki satu variabel, misalnya `{{1}}`; aplikasi mengirim rincian keputusan izin melalui variabel tersebut. Nomor wali disimpan dalam format internasional, contohnya `62812…`.

Nomor wali pada profil siswa harus menggunakan format internasional tanpa tanda `+`, misalnya `6281234567890`. Pastikan penerima menyetujui penerimaan pesan WhatsApp.

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

Setelah konfigurasi, ubah status satu pengajuan percobaan dari akun guru. Periksa menu **Notifikasi**: status `terkirim` berarti penyedia menerima permintaan; status `gagal` menampilkan alasannya. Gunakan **Kirim ulang** setelah memperbaiki konfigurasi. Pastikan migrasi database sudah dijalankan dengan `php artisan migrate`.

Jangan masukkan token, App Password, atau rahasia SMTP ke Git atau chat. Simpan semuanya hanya di `.env` lokal/server.

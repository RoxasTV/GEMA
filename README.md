# 🕌 GEMA - Sistem Audio Tour Guide

Sistem audio streaming real-time untuk pembimbing Haji/Umrah. Jamaah cukup scan QR code tanpa install aplikasi.

## Tech Stack
- Laravel 12 + SQLite/MySQL
- LiveKit Cloud (WebRTC)
- TailwindCSS

## Struktur Project

```
app/
├── Http/Controllers/
│   ├── GuideController.php    # Dashboard & room management guide
│   └── RoomController.php     # Entry point & live room jamaah
├── Models/
│   ├── User.php               # Guide user
│   ├── Room.php               # Audio room
│   ├── Prayer.php             # Daftar doa
│   └── Participant.php        # Jamaah yang join room
├── Policies/
│   └── RoomPolicy.php         # Authorization room ownership
└── Services/
    └── LiveKitService.php     # LiveKit token & API wrapper

resources/views/
├── guide/
│   ├── dashboard.blade.php    # List room & create room
│   └── room.blade.php         # Control panel guide
└── room/
    ├── login.blade.php        # Form join jamaah
    ├── live.blade.php         # Live audio room jamaah
    ├── inactive.blade.php     # Room belum aktif
    └── closed.blade.php       # Room sudah ditutup

database/migrations/
├── create_users_table.php
├── create_prayers_table.php
├── create_rooms_table.php
└── create_participants_table.php
```

## Instalasi

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Build & Run
npm run build
php artisan serve
```

## Konfigurasi `.env`

```env
DB_DATABASE=gema
DB_USERNAME=root
DB_PASSWORD=

# LiveKit Cloud
LIVEKIT_API_KEY=xxx
LIVEKIT_API_SECRET=xxx
LIVEKIT_HOST=https://xxx.livekit.cloud
LIVEKIT_WS_URL=wss://xxx.livekit.cloud
```

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Guide | guide@gema.test | password |

## Cara Pakai

**Guide:**
1. Login → Buat Room → Aktifkan
2. Share QR code ke jamaah
3. Nyalakan mic untuk broadcast
4. Gunakan "Mute Semua" untuk kontrol jamaah
5. Pilih doa untuk ditampilkan ke jamaah

**Jamaah:**
1. Scan QR → Isi nama → Masuk Room
2. Dengarkan audio guide
3. Tahan tombol mic untuk bicara (push-to-talk)
4. Tombol "Keluar Room" untuk disconnect

## Fitur

**Audio & Komunikasi:**
- ✅ Audio dua arah (walkie-talkie style)
- ✅ Push-to-talk untuk jamaah
- ✅ Mute semua jamaah sekaligus
- ✅ Indikator siapa yang sedang bicara

**Room Management:**
- ✅ Buat/hapus room
- ✅ Real-time participant counter
- ✅ Auto-kick jamaah saat room dinonaktifkan
- ✅ QR code untuk join room

**UX:**
- ✅ UI ramah lansia (font besar, kontras tinggi)
- ✅ Tampilkan teks doa real-time
- ✅ Wake lock (layar tetap nyala)
- ✅ Auto-reconnect jika koneksi terputus
- ✅ Feedback jelas saat mic permission ditolak
- ✅ Konfirmasi sebelum nonaktifkan room

<p align="center">
  <h1 align="center">🕌 GEMA</h1>
  <p align="center">
    <strong>Aplikasi Audio Tour Guide untuk Haji & Umrah</strong><br>
    Real-time Audio Streaming Web App for Hajj/Umrah Pilgrims
  </p>
  <p align="center">
    <a href="#fitur">Fitur</a> •
    <a href="#demo">Demo</a> •
    <a href="#instalasi">Instalasi</a> •
    <a href="#penggunaan">Penggunaan</a> •
    <a href="#tech-stack">Tech Stack</a> •
    <a href="#kontribusi">Kontribusi</a>
  </p>
</p>

---

## 📖 Tentang GEMA

**GEMA** (Guide Audio Streaming) adalah sistem audio tour guide berbasis web yang dirancang khusus untuk pembimbing Haji dan Umrah. Jamaah dapat bergabung hanya dengan **scan QR code** - tanpa perlu login atau install aplikasi apapun.

### Mengapa GEMA?

- 🎯 **Tanpa Install** - Jamaah cukup scan QR code dan langsung terhubung
- 👴 **Ramah Lansia** - UI dengan tombol besar, font jelas, dan kontras tinggi
- 🔊 **Audio Dua Arah** - Komunikasi walkie-talkie antara guide dan jamaah
- 📿 **Tampilan Doa** - Teks doa real-time untuk diikuti jamaah
- 📱 **Mobile First** - Dioptimalkan untuk smartphone jamaah

---

## ✨ Fitur

### Untuk Guide (Pembimbing)
- ✅ Dashboard manajemen room
- ✅ Broadcast audio ke semua jamaah
- ✅ Mute/unmute semua jamaah sekaligus
- ✅ Tampilkan teks doa real-time
- ✅ QR code untuk share room
- ✅ Monitor jamaah online & active speaker
- ✅ Hapus room (soft delete dengan audit trail)

### Untuk Jamaah
- ✅ Join room via QR code scan
- ✅ Dengarkan audio guide
- ✅ Push-to-talk untuk bicara ke guide
- ✅ Lihat teks doa yang sedang dibacakan
- ✅ Wake lock (layar tetap nyala)
- ✅ Auto-reconnect jika koneksi terputus

### Teknis
- ✅ Real-time audio via WebRTC (LiveKit)
- ✅ Soft delete dengan tracking siapa yang menghapus
- ✅ Participant tracking (join/leave time)
- ✅ Policy-based authorization
- ✅ Responsive design

---

## 🎬 Demo

### Panel Guide
![Panel Guide](Screenshot%202026-01-01%20142235.png)

### Halaman Jamaah
![Halaman Jamaah](Screenshot%202026-01-01%20142301.png)

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + TailwindCSS |
| Real-time Audio | LiveKit Cloud (WebRTC) |
| Database | SQLite / MySQL |
| QR Code | SimpleSoftwareIO/QrCode |

---

## 📦 Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js 18+
- [LiveKit Cloud Account](https://livekit.io/) (gratis)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/username/gema.git
cd gema

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi LiveKit (lihat bagian Konfigurasi)

# 5. Setup database
php artisan migrate
php artisan db:seed

# 6. Build assets & jalankan
npm run build
php artisan serve
```

### Konfigurasi LiveKit

Ada 2 opsi untuk setup LiveKit:

#### Opsi 1: LiveKit Cloud (Recommended untuk Production)

1. Daftar di [LiveKit Cloud](https://cloud.livekit.io/) (gratis untuk development)
2. Buat project baru
3. Copy credentials ke `.env`:

```env
LIVEKIT_API_KEY=your_api_key
LIVEKIT_API_SECRET=your_api_secret
LIVEKIT_HOST=https://your-project.livekit.cloud
LIVEKIT_WS_URL=wss://your-project.livekit.cloud
```

#### Opsi 2: Self-Hosted dengan Docker (Development/On-Premise)

Untuk development lokal atau deployment on-premise, jalankan LiveKit server sendiri:

```bash
# 1. Jalankan LiveKit server
docker run --rm -p 7880:7880 -p 7881:7881 -p 7882:7882/udp \
  -e LIVEKIT_KEYS="devkey: secret" \
  livekit/livekit-server \
  --dev

# 2. Update .env
LIVEKIT_API_KEY=devkey
LIVEKIT_API_SECRET=secret
LIVEKIT_HOST=http://localhost:7880
LIVEKIT_WS_URL=ws://localhost:7880
```

Atau gunakan `docker-compose.yml` yang sudah disediakan:

```bash
docker-compose up -d livekit
```

> ⚠️ **Catatan:** Self-hosted LiveKit memerlukan HTTPS untuk production. Gunakan reverse proxy (nginx/traefik) dengan SSL certificate.

#### Perbandingan Opsi

| Aspek | LiveKit Cloud | Self-Hosted |
|-------|---------------|-------------|
| Setup | Mudah (5 menit) | Perlu konfigurasi |
| Biaya | Gratis (limit) / Berbayar | Gratis (server sendiri) |
| Maintenance | Tidak perlu | Perlu maintain server |
| Scalability | Auto-scale | Manual scaling |
| Latency | Optimal (CDN global) | Tergantung lokasi server |
| Cocok untuk | Production, MVP | Development, On-premise |

---

## 🚀 Penggunaan

### Login Guide

| Email | Password |
|-------|----------|
| `guide@gema.test` | `password` |

### Alur Penggunaan

**Guide:**
1. Login ke dashboard
2. Buat room baru
3. Aktifkan room
4. Share QR code ke jamaah
5. Nyalakan mic untuk broadcast
6. Pilih doa untuk ditampilkan

**Jamaah:**
1. Scan QR code dari guide
2. Masukkan nama/ID
3. Dengarkan audio guide
4. Tahan tombol mic untuk bicara (push-to-talk)

---

## 📁 Struktur Project

```
app/
├── Http/Controllers/
│   ├── GuideController.php    # Dashboard & room management
│   └── RoomController.php     # Entry point jamaah
├── Models/
│   ├── Room.php               # Audio room (soft delete)
│   ├── Prayer.php             # Daftar doa
│   └── Participant.php        # Tracking jamaah
├── Policies/
│   └── RoomPolicy.php         # Authorization
└── Services/
    └── LiveKitService.php     # LiveKit API wrapper

resources/views/
├── guide/
│   ├── dashboard.blade.php    # List & create room
│   └── room.blade.php         # Control panel guide
└── room/
    ├── login.blade.php        # Form join jamaah
    └── live.blade.php         # Live audio room
```

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b fitur/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur amazing'`)
4. Push ke branch (`git push origin fitur/AmazingFeature`)
5. Buat Pull Request

### Ideas untuk Kontribusi

- [ ] Multi-language support
- [ ] Recording audio session
- [ ] Offline mode untuk jamaah
- [ ] Push notification
- [ ] Admin panel untuk kelola guide
- [ ] Analytics dashboard

---

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - PHP Framework
- [LiveKit](https://livekit.io/) - Real-time Audio/Video
- [TailwindCSS](https://tailwindcss.com/) - CSS Framework
- [SimpleSoftwareIO/QrCode](https://github.com/SimpleSoftwareIO/simple-qrcode) - QR Code Generator

---

<p align="center">
  Made with ❤️ for Hajj & Umrah pilgrims
</p>

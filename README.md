# SmartBO - Sistem Panggilan Pengundi

Aplikasi web untuk pengurusan panggilan telefon kepada pengundi menggunakan Laravel, Filament, dan Livewire.

## 📋 Tentang Projek

SmartBO adalah sistem pengurusan yang membolehkan:
- Rekod panggilan telefon kepada pengundi
- Pengurusan pengguna dengan sistem kelulusan admin
- Dashboard dengan statistik masa nyata
- Kawalan akses berdasarkan status pengguna

## 🚀 Teknologi Yang Digunakan

- **Laravel 11** - Framework PHP
- **Filament v4** - Panel admin
- **Livewire** - Komponen dinamik
- **MySQL** - Database utama
- **Tailwind CSS** - Styling
- **Heroicons** - Ikon

## 📦 Keperluan Sistem

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL 8.0+
- Laravel Sail (untuk pembangunan)

## ⚡ Pemasangan

### 1. Clone Repository
```bash
git clone https://github.com/ashrafmisran/smartbo.git
cd smartbo
```

### 2. Setup Environment
```bash
cp .env.example .env
composer install
npm install
```

### 3. Database Setup
```bash
# Edit .env file dengan maklumat database anda
php artisan key:generate
php artisan migrate --seed
```

### 4. Jalankan Aplikasi
```bash
# Development
php artisan serve
npm run dev

# Atau dengan Laravel Sail
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

## 👥 Sistem Pengguna

### Status Pengguna:
- **Pending** - Menunggu kelulusan admin
- **Verified** - Disahkan dan boleh akses sistem
- **Suspended** - Digantung dari sistem

### Peranan Pengguna:
- **Pengguna Biasa** - Boleh rekod panggilan sendiri
- **Admin** - Boleh urus semua pengguna dan lihat statistik
- **Super Admin** - Akses penuh sistem

## 📊 Ciri-ciri Utama

### Dashboard Statistik
- Nombor telah dihubungi (untuk semua pengguna)
- Pengguna menunggu pengesahan (admin sahaja)
- Pengguna disahkan (admin sahaja)
- Pengguna digantung (admin sahaja)
- Jumlah pengguna (admin sahaja)

### Pengurusan Rekod Panggilan
- Rekod panggilan dengan kod cula
- Filter berdasarkan pengguna
- Carian global pengundi
- Nota panggilan

### Pengurusan Pengguna (Admin)
- Sahkan/tolak pendaftaran pengguna
- Gantung/aktifkan pengguna
- Lantik/pecat admin
- Lihat maklumat kawasan keahlian

## 🔧 Konfigurasi

### Database Connections
Sistem menyokong multiple database connections:
- **Default**: Database utama aplikasi
- **SSDP**: Database pengundi (read-only)

### Email Configuration
Setup email untuk notifikasi dalam `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

### Filament Configuration
Panel admin boleh diakses di `/bo` dengan kustomisasi:
- Tema gelap/terang
- Navigasi sidebar/topbar
- Widget dashboard

## 🛠️ Pembangunan

### Struktur Folder Utama
```
app/
├── Filament/           # Panel admin dan resources
├── Http/Controllers/   # Controllers
├── Livewire/          # Komponen Livewire
├── Models/            # Eloquent models
└── Services/          # Business logic

database/
├── migrations/        # Database migrations
└── seeders/          # Data seeders

resources/
├── css/              # Styling files
├── js/               # JavaScript files
└── views/            # Blade templates
```

### Artisan Commands
```bash
# Generate resources baru
php artisan make:filament-resource ModelName

# Generate widget
php artisan make:filament-widget WidgetName

# Generate page
php artisan make:filament-page PageName
```

## 🚦 Testing

```bash
# Jalankan semua test
php artisan test

# Test dengan coverage
php artisan test --coverage

# Test specific feature
php artisan test --filter=UserRegistrationTest
```

## 📝 API Documentation

API endpoints tersedia untuk:
- Authentication
- User management
- Call records
- Statistics

Dokumentasi lengkap boleh diakses di `/api/documentation` (jika diaktifkan).

## 🔒 Keselamatan

- Authentication menggunakan session Laravel
- Autoriti berdasarkan peranan pengguna
- Validation input pada semua form
- CSRF protection
- Rate limiting pada login

## 📈 Performance

- Database query optimization
- Lazy loading untuk relationships
- Cache untuk data yang kerap diakses
- Real-time updates dengan polling

## 🤝 Contributing

1. Fork repository
2. Cipta branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## 📞 Support

Jika ada sebarang masalah atau soalan:
- Buka [Issue](https://github.com/ashrafmisran/smartbo/issues)
- Email: [ashrafmisran@gmail.com](mailto:ashrafmisran@gmail.com)

## 📄 License

Projek ini adalah open source di bawah [MIT License](LICENSE).

---

**Versi:** 1.0.0  
**Status:** Active Development  
**Dibangunkan oleh:** Muhammad Ashraf bin Misran

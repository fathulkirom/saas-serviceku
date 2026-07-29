<div align="center">
  <h1>🔧 ServiceKU</h1>
  <p><strong>SaaS Multi-Tenant Service Center Management Platform</strong></p>
  <p>
    <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel" alt="Laravel 11">
    <img src="https://img.shields.io/badge/Vue_3-3.x-brightgreen?logo=vue.js" alt="Vue 3">
    <img src="https://img.shields.io/badge/Inertia.js-3.x-blue?logo=inertia" alt="Inertia.js">
    <img src="https://img.shields.io/badge/PHP-8.2+-purple?logo=php" alt="PHP 8.2+">
    <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License">
  </p>
  <p>
    <a href="https://github.com/fathulkirom/saas-serviceku/actions">
      <img src="https://github.com/fathulkirom/saas-serviceku/actions/workflows/tests.yml/badge.svg" alt="Tests">
    </a>
    <a href="https://github.com/fathulkirom/saas-serviceku/issues">
      <img src="https://img.shields.io/github/issues/fathulkirom/saas-serviceku" alt="Issues">
    </a>
  </p>
</div>

---

## 📋 Tentang ServiceKU

ServiceKU adalah platform manajemen pusat servis elektronik & handphone berbasis **SaaS Multi-Tenant**. Dibangun dengan **Laravel 11 + Vue 3 (Composition API) + Inertia.js**.

### Fitur Utama

| Modul | Fitur |
|---|---|
| 🔧 **Service/Repair** | Ticket lifecycle, foto (Google Drive), spare parts, checklist, transfer antar teknisi |
| 💰 **POS/Sales** | Cash register, invoice, payment tracking, multi-store |
| 👥 **Customer** | CRUD, member card, loyalty points, history servis |
| 📦 **Inventory** | Produk, stock mutation, low stock alert, purchase order, return, stock allocation |
| 💵 **Finance** | Expenses (dengan foto), daily deposit, commission, reconciliation, tax settings |
| 🏢 **Multi-Branch** | Multiple cabang, transfer stock antar cabang |
| 👨‍🔧 **HR** | Shift, absensi, multi-role users (Owner/Admin/CS/Teknisi/Kasir/Kurir) |
| 📊 **Reporting** | 9 jenis report: sales, services, finance, inventory, commissions, customer analytics, productivity, revenue comparison |
| 📖 **Knowledge Base** | SOP, quick reply CS, artikel knowledge base |
| 📱 **WhatsApp** | Notifikasi otomatis via WA Gateway (Fonnte) |
| 🔍 **Public Tracking** | Pelanggan bisa cek status servis via kode tracking |
| 🎓 **Onboarding** | Setup wizard untuk tenant baru |

### Pricing Plans

| Plan | Harga | Fitur |
|---|---|---|
| **Basic** | Rp 99.000/bulan | Read-only reports, single branch |
| **Pro** | Rp 199.000/bulan | Full features, multi-branch, transfer stock |
| **Enterprise** | Rp 499.000/bulan | Semua fitur Pro + prioritas support |

---

## 🏗️ Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | Laravel 11, PHP 8.2+ |
| **Frontend** | Vue 3 (Composition API), Inertia.js, Tailwind CSS |
| **Database (Central)** | MySQL 8.0 |
| **Database (Tenant)** | SQLite (dev) / MySQL (prod) |
| **Multi-Tenancy** | stancl/tenancy ^3.10 |
| **WebSocket** | Laravel Reverb |
| **API Auth** | Laravel Sanctum |
| **Social Auth** | Laravel Socialite (Google) |
| **Queue** | Laravel Queue (sync/redis) |
| **Storage** | Google Drive API (foto servis) |
| **WA Gateway** | Fonnte API |
| **PDF** | barryvdh/laravel-dompdf |
| **E2E Testing** | Playwright |
| **CI/CD** | GitHub Actions (Tests, Docker, Deploy) |
| **Infrastructure** | Docker, Nginx, Redis, Cloudflare Tunnel |

---

## 🚀 Quick Start (Development)

### Prasyarat
- PHP 8.2+, Composer, Node.js 22+, Docker Desktop

### 1. Clone & Install
```bash
git clone https://github.com/fathulkirom/saas-serviceku.git
cd saas-serviceku
cp .env.example .env
composer install
npm install
```

### 2. Generate Key
```bash
php artisan key:generate
```

### 3. Setup Database
```bash
# Central database
touch database/database.sqlite
php artisan migrate

# Tenant database
php artisan migrate --path=database/migrations/tenant

# Seed master data
php artisan db:seed --class=MasterDataSeeder
```

### 4. Buat Tenant
```bash
php artisan tinker
# Di tinker:
\App\Models\Tenant::create(['id' => 'dev', 'slug' => 'toko-servis-abc', 'data' => ['plan' => 'pro']]);
# Keluar tinker:
php artisan tenants:run "php artisan migrate --path=database/migrations/tenant"
```

### 5. Jalankan
```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite (frontend)
npm run dev

# Terminal 3 - Queue (opsional)
php artisan queue:listen
```

### Akses
- **Landing:** http://localhost:8000
- **Tenant:** http://toko-servis-abc.localhost:8000
- **Dev Login:** http://toko-servis-abc.localhost:8000/dev-login
- **Admin:** http://localhost:8000/admin

---

## 🐳 Docker (Production)

```bash
# Build & start
docker compose up -d

# Service:
# - Laravel app: http://localhost:80
# - phpMyAdmin: http://localhost:8080
# - Redis: port 6379
```

Lihat `deploy.sh` untuk deployment penuh.

---

## 🧪 Testing

```bash
# PHP Tests
php artisan test

# E2E (Playwright)
cd e2e && npx playwright test

# Code Style
vendor/bin/pint --test
```

### CI/CD (GitHub Actions)

| Workflow | Trigger | Deskripsi |
|---|---|---|
| **Tests** | push/PR ke main | PHPUnit + JS Lint + Build |
| **Docker** | push ke main + tag v* | Build & push Docker image |
| **Deploy** | push ke main | Auto-deploy via SSH ke server |
| **CodeQL** | push ke main | Security code scanning |

---

## 📂 Project Structure

```
├── app/
│   ├── Http/Controllers/    # 50+ Controllers
│   │   ├── Auth/            # Login, Register, OTP
│   │   ├── Admin/           # Superadmin panel
│   │   └── Tenant/          # Fitur per tenant
│   ├── Models/              # Central models
│   │   └── Tenant/          # 43+ Tenant models
│   ├── Services/            # WhatsApp, Google Drive, Payment
│   └── Policies/            # Authorization
├── config/
│   └── tenancy.php          # Multi-tenant config
├── database/
│   ├── migrations/          # Central migrations
│   │   └── tenant/          # Tenant migrations
│   └── tenant_*/            # Tenant SQLite databases (dev)
├── resources/js/
│   ├── Pages/               # 45+ Vue pages
│   └── Components/          # 19+ Shared components
├── routes/
│   ├── web.php              # Central routes
│   ├── tenant.php           # Tenant routes
│   ├── api.php              # API routes
│   └── admin.php            # Admin routes
├── tests/
│   ├── Feature/             # 7 feature tests
│   └── Unit/                # 3 unit tests
├── e2e/                     # Playwright E2E tests
└── docker/                  # Docker config
```

---

## 🔒 Security

- JWT/Sanctum untuk API
- Rate limiting di auth (6x/min, OTP 3x/min)
- Form validation policies
- Security headers middleware
- CodeQL analysis di CI
- Multi-tenant isolation via stancl/tenancy

---

## 📄 License

**MIT License** — Copyright (c) 2026 Fathul Kirom

---

<p align="center">
  Dibuat dengan ❤️ untuk para pejuang servis elektronik di Indonesia
</p>

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# PROJECT STRUCTURE — ServiceKU v0.9.0-beta

```
saas/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Events/           # Event classes (~75 events)
│   │   └── Entity/       # Domain events (13 files)
│   ├── Http/
│   │   ├── Controllers/  # Central controllers (6) + Tenant controllers (66)
│   │   │   ├── Admin/    # Super admin panel
│   │   │   ├── Api/      # API endpoints
│   │   │   ├── Auth/     # Authentication
│   │   │   └── Tenant/   # Tenant-scoped controllers (66 files)
│   │   └── Middleware/   # 11 middleware
│   ├── Jobs/             # Queue jobs (5)
│   ├── Mail/             # Email classes
│   ├── Models/           # Central models (11)
│   │   └── Tenant/       # Tenant models (~107)
│   │       └── Traits/   # HasCustomFields, HasOptimisticLocking, HasRoles
│   ├── Notifications/    # Notification classes
│   ├── Policies/         # Authorization policies (13)
│   ├── Providers/        # Service providers
│   └── Services/         # Business logic services (19)
│       └── Importers/    # CSV import handlers
├── bootstrap/            # Laravel bootstrap
├── config/               # Configuration files
├── database/
│   ├── migrations/       # 16 central + 52 tenant migrations
│   └── tenant_*/         # Tenant SQLite databases (dev)
├── docker/               # Docker configs
├── e2e/                  # End-to-end tests
├── lang/id/              # Indonesian translations
├── public/               # Web root
├── resources/
│   ├── js/
│   │   ├── Components/   # 45 reusable Vue components
│   │   │   ├── Services/ # Service-specific components (12)
│   │   │   └── ui/       # UI primitives (Button, Card, Input, Label)
│   │   ├── Composables/  # 5 composables
│   │   ├── Layouts/      # 7 layout files
│   │   │   └── Themes/   # Sidebar, HeaderBar, GlobalSearch
│   │   ├── Pages/        # 77 Vue pages (22 directories)
│   │   └── Utils/        # Utility modules
│   └── views/            # Blade views
├── routes/
│   ├── admin.php         # Super admin routes
│   ├── api.php           # API routes
│   ├── tenant.php        # Tenant routes (~200+)
│   └── web.php           # Central web routes
├── scripts/              # Shell scripts
├── storage/              # Laravel storage
├── tests/                # PHPUnit tests
├── vendor/               # Composer dependencies
├── ARCHITECTURE.md       # Architecture documentation
├── BUSINESS_TYPE_MATRIX.md
├── CHANGELOG.md
├── FEATURE_MATRIX.md
├── PROJECT_STRUCTURE.md  # ← this file
├── RELEASE_NOTES_v0.9.0.md
├── ROLE_MATRIX.md
├── SERVICE_FLOW.md
├── VERSION               # 0.9.0-beta
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── package.json
├── phpunit.xml
├── README.md
├── RUNBOOK.md
├── SECURITY.md
├── vite.config.js
└── tailwind.config.js
```

# ADR: Workspace Architecture Runtime Decision

## Context
Project ServiceKU memiliki implementasi `WorkspaceShell.vue` dan `WorkspaceMetaPresenter.php` beserta registry definition (seperti `customer.js`, dsb). Namun, audit runtime menunjukkan penggunaan `WorkspaceShell` = 0 di seluruh layer `resources/js/Pages`. Proses rendering halaman berjalan menggunakan standard architecture Vue/Inertia (seperti `Create.vue`, `Show.vue`, dll). Penggunaan custom event palsu (misal: `CustomEvent('service:create')`) pada Workspace registration tidak diproses oleh backend.

## Decision
**STATUS: SUPERSEDED / REQUIRES REVIEW**

Keputusan awal untuk men-deprecate Workspace Architecture dibatalkan. "0 runtime usage indicates incomplete migration, not architectural invalidity."
Workspace Engine (WorkspaceDefinition, WorkspaceRegistry, WorkspaceShell) adalah target arsitektur frontend ServiceKU. Ketidakhadirannya di runtime saat ini hanyalah indikasi migrasi yang belum selesai (broken link antara Controller dan WorkspaceShell), bukan kelemahan arsitektur.

## Alternatives
- **OPTION A - KEEP AND IMPLEMENT**: Ini akan mengharuskan refactor massive terhadap seluruh page existing dan UI flow untuk memasukkan `WorkspaceShell`, menghapus page-specific layouts, dan memaksa backend membangun metadata payload `WorkspaceMetaPresenter` di seluruh controller. Hal ini bertentangan dengan fakta bahwa page standard (seperti `Services/Create.vue`) sudah menangani workflow nyata (RECOVERY-01).

## Consequences
- **Maintenance**: Struktur kode menjadi jauh lebih mudah di-maintain. Flow aplikasi bisa di-trace secara linier (Route -> Controller -> Page Vue).
- **Dead Code**: Registri workspace (`resources/js/Enterprise/Workspace`) dapat ditandai sebagai `DEPRECATED` untuk dihapus dalam tahapan clean-up ke depannya.

## Migration Plan
1. **Restore Status**: Workspace architecture kembali menjadi target utama.
2. **Find Broken Link**: Analisa mengapa halaman tidak menggunakan WorkspaceShell.
3. **Proof of Concept**: Lakukan migrasi pada halaman Service Detail (`ServiceController@show`) untuk menggunakan `WorkspaceMetaPresenter` dan `WorkspaceShell`.

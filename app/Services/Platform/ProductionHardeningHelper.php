<?php

namespace App\Services\Platform;

/**
 * ProductionHardeningHelper — Performance Optimization & Stability Toolkit.
 * 
 * SPRINT 36D: Production-grade performance, stability, and security hardening.
 * Audit findings, optimization patterns, cache strategies, query best practices,
 * security hardening checklist, and performance targets.
 * 
 * ⚠️ This is a REFERENCE DOCUMENT in code form.
 * ⚠️ Zero database changes. Zero new engines. All patterns use existing infrastructure.
 */
class ProductionHardeningHelper
{
    // ═══════════════════════════════════════════════════════════
    // PERFORMANCE TARGETS — Sprint 36D Benchmarks
    // ═══════════════════════════════════════════════════════════

    /**
     * Target response times per page type.
     * All times in milliseconds.
     */
    public const PERFORMANCE_TARGETS = [
        'dashboard' => [
            'label'      => 'Dashboard',
            'target_ms'  => 1000,
            'max_ms'     => 2000,
            'metric'     => 'First Contentful Paint',
            'priority'   => 'critical',
        ],
        'workspace' => [
            'label'      => 'Service Workspace',
            'target_ms'  => 500,
            'max_ms'     => 1000,
            'metric'     => 'Time to Interactive',
            'priority'   => 'critical',
        ],
        'datatable_list' => [
            'label'      => 'Data Table (List Page)',
            'target_ms'  => 300,
            'max_ms'     => 800,
            'metric'     => 'Server Response Time',
            'priority'   => 'critical',
        ],
        'datatable_100k' => [
            'label'      => 'Data Table (100K+ records, server-side)',
            'target_ms'  => 500,
            'max_ms'     => 1500,
            'metric'     => 'Query + Render Time',
            'priority'   => 'high',
        ],
        'form_save' => [
            'label'      => 'Form Save',
            'target_ms'  => 200,
            'max_ms'     => 500,
            'metric'     => 'Server Response Time',
            'priority'   => 'high',
        ],
        'search' => [
            'label'      => 'Global Search',
            'target_ms'  => 150,
            'max_ms'     => 400,
            'metric'     => 'Server Response Time',
            'priority'   => 'high',
        ],
        'api_endpoint' => [
            'label'      => 'API Endpoint',
            'target_ms'  => 100,
            'max_ms'     => 300,
            'metric'     => 'Server Response Time',
            'priority'   => 'high',
        ],
        'file_upload' => [
            'label'      => 'File Upload (5MB)',
            'target_ms'  => 3000,
            'max_ms'     => 5000,
            'metric'     => 'Upload Duration',
            'priority'   => 'medium',
        ],
        'report_generation' => [
            'label'      => 'Report Generation',
            'target_ms'  => 3000,
            'max_ms'     => 10000,
            'metric'     => 'Server Response Time',
            'priority'   => 'medium',
        ],
        'notification_delivery' => [
            'label'      => 'Notification Delivery',
            'target_ms'  => 5000,
            'max_ms'     => 15000,
            'metric'     => 'End-to-End Delivery',
            'priority'   => 'medium',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // QUERY OPTIMIZATION — Eager Loading Checklist
    // ═══════════════════════════════════════════════════════════

    /**
     * Every model with its recommended eager-load relationships.
     * Use this as a checklist when writing queries.
     */
    public const EAGER_LOAD_CHECKLIST = [
        'Service' => [
            'always'   => ['customer', 'technician', 'branch'],
            'detail'   => ['diagnosis', 'photos', 'checklists', 'spareparts', 'delivery', 'quotations', 'qcCheck'],
            'list'     => ['customer:id,name,phone', 'technician:id,name', 'branch:id,name'],
        ],
        'Sale' => [
            'always'   => ['customer'],
            'detail'   => ['items.product', 'service.customer', 'payments'],
            'list'     => ['customer:id,name', 'items'],
        ],
        'Customer' => [
            'detail'   => ['services', 'sales', 'devices', 'tags', 'communications'],
            'list'     => [],  // Customer list is usually lean
        ],
        'WorkOrder' => [
            'always'   => ['technician', 'service.customer'],
            'list'     => ['technician:id,name', 'service:id,tracking_code,status'],
        ],
        'Tenant' => [
            'detail'   => ['plan', 'stats'],
            'list'     => ['plan:id,name'],
        ],
        'Product' => [
            'detail'   => ['category', 'supplier', 'stock'],
            'list'     => ['category:id,name'],
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // N+1 PREVENTION — Query Pattern Rules
    // ═══════════════════════════════════════════════════════════

    public const QUERY_RULES = [
        'rule_1' => [
            'rule'       => 'Always eager-load relationships used in loops',
            'bad'        => 'foreach ($services as $s) { echo $s->customer->name; }',
            'good'       => '$services = Service::with("customer")->get();',
            'severity'   => 'critical',
        ],
        'rule_2' => [
            'rule'       => 'Use select() to limit columns on large tables',
            'bad'        => 'Customer::all()',
            'good'       => 'Customer::select("id","name","phone")->get()',
            'severity'   => 'high',
        ],
        'rule_3' => [
            'rule'       => 'Use chunk() or lazy() for processing large datasets',
            'bad'        => 'Service::all()->each(fn($s) => $s->update([...]));',
            'good'       => 'Service::lazy()->each(fn($s) => $s->update([...]));',
            'severity'   => 'critical',
        ],
        'rule_4' => [
            'rule'       => 'Use toBase() for aggregate-only queries (no model hydration)',
            'bad'        => 'Service::where("status","selesai")->count()',
            'good'       => 'Service::where("status","selesai")->toBase()->count()',
            'severity'   => 'medium',
        ],
        'rule_5' => [
            'rule'       => 'Avoid whereHas() with large tables — use join or subquery',
            'bad'        => 'Customer::whereHas("services", fn($q) => $q->where(...))',
            'good'       => 'Customer::whereIn("id", Service::select("customer_id")->where(...))',
            'severity'   => 'high',
        ],
        'rule_6' => [
            'rule'       => 'Index columns used in WHERE, JOIN, ORDER BY, GROUP BY',
            'note'       => 'Check EXPLAIN output. Missing indexes are the #1 MySQL performance killer.',
            'severity'   => 'critical',
        ],
        'rule_7' => [
            'rule'       => 'Use pagination — never return unbounded result sets',
            'bad'        => 'Service::all()',
            'good'       => 'Service::paginate(25)',
            'severity'   => 'critical',
        ],
        'rule_8' => [
            'rule'       => 'Aggregate counts with care — use conditional aggregates',
            'bad'        => '$active = Service::where("status","!=","close")->count(); $closed = Service::where("status","close")->count();',
            'good'       => 'Service::selectRaw("SUM(status=\"close\") as closed, SUM(status!=\"close\") as active")->first()',
            'severity'   => 'medium',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // CACHE STRATEGY — Per-Data-Type Recommendations
    // ═══════════════════════════════════════════════════════════

    public const CACHE_STRATEGY = [
        'dashboard_widgets' => [
            'ttl'        => 60,      // seconds
            'store'      => 'redis', // recommended
            'invalidate' => 'on_service_status_change, on_sale_created',
            'note'       => 'Widget data changes slowly — 60s is acceptable staleness',
        ],
        'user_permissions' => [
            'ttl'        => 300,     // 5 minutes
            'store'      => 'redis',
            'invalidate' => 'on_role_change, on_permission_update',
            'note'       => 'Already cached in User model. Ensure Redis for multi-server.',
        ],
        'feature_flags' => [
            'ttl'        => 3600,    // 1 hour
            'store'      => 'redis',
            'invalidate' => 'on_feature_toggle, on_plan_change',
            'note'       => 'Rarely changes. Long TTL safe.',
        ],
        'tenant_settings' => [
            'ttl'        => 300,     // 5 minutes
            'store'      => 'redis',
            'invalidate' => 'on_setting_save',
            'note'       => 'Currently in SettingsService. Avoid Cache::flush().',
        ],
        'reports' => [
            'ttl'        => 600,     // 10 minutes
            'store'      => 'redis',
            'invalidate' => 'on_new_data_in_report_range',
            'note'       => 'Reports are expensive. Cache aggressively.',
        ],
        'menu_structure' => [
            'ttl'        => 3600,    // 1 hour
            'store'      => 'redis',
            'invalidate' => 'on_permission_change, on_module_install',
            'note'       => 'Menu rarely changes per user.',
        ],
        'search_index' => [
            'ttl'        => null,    // No TTL — invalidated on write
            'store'      => 'redis',
            'invalidate' => 'on_service_created, on_customer_updated',
            'note'       => 'Full-text search on DB. Cache search results for hot queries.',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // INDEX RECOMMENDATIONS — Critical Missing Indexes
    // ═══════════════════════════════════════════════════════════

    /**
     * Recommended database indexes for production.
     * Add these via migration in a maintenance window.
     */
    public const RECOMMENDED_INDEXES = [
        // Service table — most queried table
        ['table' => 'services',       'columns' => ['status', 'branch_id'],            'reason' => 'Dashboard filtering by status + branch'],
        ['table' => 'services',       'columns' => ['customer_id', 'status'],           'reason' => 'Customer service history'],
        ['table' => 'services',       'columns' => ['technician_id', 'status'],         'reason' => 'Technician workload queries'],
        ['table' => 'services',       'columns' => ['created_at'],                       'reason' => 'Date-range reports'],
        ['table' => 'services',       'columns' => ['tracking_code'],                    'reason' => 'Tracking lookup (unique already?)'],
        ['table' => 'services',       'columns' => ['imei_sn'],                          'reason' => 'IMEI/SN tracking lookup'],

        // Sales table
        ['table' => 'sales',          'columns' => ['customer_id', 'created_at'],       'reason' => 'Customer purchase history'],
        ['table' => 'sales',          'columns' => ['branch_id', 'payment_status'],     'reason' => 'Branch revenue reports'],

        // Products
        ['table' => 'products',       'columns' => ['category_id', 'is_active'],        'reason' => 'Active product listing'],

        // Queue
        ['table' => 'jobs',           'columns' => ['queue', 'available_at'],           'reason' => 'Queue worker polling'],

        // Audit
        ['table' => 'audit_logs',     'columns' => ['auditable_type', 'auditable_id'],  'reason' => 'Audit trail lookup'],
    ];

    // ═══════════════════════════════════════════════════════════
    // QUEUE OPTIMIZATION — Production Settings
    // ═══════════════════════════════════════════════════════════

    public const QUEUE_OPTIMIZATION = [
        'after_commit' => [
            'current'  => false,
            'target'   => true,
            'reason'   => 'Prevents processing jobs from rolled-back DB transactions',
            'impact'   => 'critical',
        ],
        'priorities' => [
            'current'  => 'none — all jobs equal priority',
            'target'   => "high (notifications, SLA) > default > low (reports, cleanup)",
            'reason'   => 'Critical notifications must process before batch reports',
            'impact'   => 'high',
        ],
        'retry_strategy' => [
            'current'  => 'hourly retry-all (brute force)',
            'target'   => 'Exponential backoff: 1min, 5min, 15min, 1hr, 4hr, 24hr',
            'reason'   => 'Permanently failing jobs should not retry infinitely',
            'impact'   => 'high',
        ],
        'timeout' => [
            'current'  => 90,
            'target'   => 60,
            'reason'   => 'Shorter timeout prevents queue congestion',
            'impact'   => 'medium',
        ],
        'workers' => [
            'current'  => 'unknown — no Supervisor/horizon config',
            'target'   => '3 workers per queue (high: 2, default: 3, low: 1)',
            'reason'   => 'Balanced throughput without overwhelming DB connections',
            'impact'   => 'high',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // SECURITY HARDENING — Checklist
    // ═══════════════════════════════════════════════════════════

    public const SECURITY_HARDENING = [
        'authorization' => [
            'check'  => 'All controller actions use Policy or can()',
            'status' => '✅ Mostly — but need audit of all 50+ controllers',
            'action' => 'Run: grep -r "can(" app/Http/Controllers | wc -l',
        ],
        'mass_assignment' => [
            'check'  => 'All models have $fillable or $guarded',
            'status' => '✅ Confirmed — Service, Customer, etc. all have $fillable',
            'action' => 'Review custom Models in DailyOpsModels.php, WarehouseModels.php',
        ],
        'rate_limiting' => [
            'check'  => 'Login, register, OTP, API all rate-limited',
            'status' => '✅ bootstrap/app.php has 4 rate limiters',
            'action' => 'Add rate limiting to public tracking endpoint (prevent brute-force IMEI lookup)',
        ],
        'session_security' => [
            'check'  => 'Session config: secure, http_only, same_site',
            'status' => '⚠️ Need to verify session.php production settings',
            'action' => 'Ensure: secure=true, http_only=true, same_site=lax',
        ],
        'csrf' => [
            'check'  => 'All POST/PUT/DELETE routes protected',
            'status' => '✅ Laravel + Inertia auto-handle CSRF',
            'action' => 'Verify public API routes exempted intentionally (not accidentally)',
        ],
        'xss' => [
            'check'  => 'Output encoding, CSP headers',
            'status' => '✅ Vue auto-escapes, CSP header in SecurityHeaders middleware',
            'action' => 'Audit v-html usage — only 2 instances found (acceptable)',
        ],
        'sql_injection' => [
            'check'  => 'All queries use parameterized bindings',
            'status' => '✅ Eloquent/Query Builder auto-parameterize',
            'action' => 'Audit raw queries: DB::raw(), DB::select(), whereRaw()',
        ],
        'upload_validation' => [
            'check'  => 'File uploads validated (type, size, content)',
            'status' => '⚠️ Need to verify: max file size, allowed MIME types, virus scan',
            'action' => 'Add mimes:jpg,png,webp,pdf validation. Max 10MB. Consider ClamAV for production.',
        ],
        'dependency_scan' => [
            'check'  => 'Composer/NPM audit for known vulnerabilities',
            'status' => '⚠️ Not automated in CI/CD',
            'action' => 'Add: composer audit --no-dev in deploy script. npm audit in build.',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // FRONTEND PERFORMANCE — Optimization Patterns
    // ═══════════════════════════════════════════════════════════

    public const FRONTEND_OPTIMIZATION = [
        'lazy_loading' => [
            'pattern'    => 'IntersectionObserver + defineAsyncComponent',
            'usage'      => 'Tab content, below-fold widgets, photo galleries',
            'status'     => '⚠️ LazyLoader.vue exists but only used for dashboard widgets',
            'recommend'  => 'Extend to workspace tabs, data tables, report pages',
        ],
        'code_splitting' => [
            'pattern'    => 'Vite manualChunks',
            'usage'      => 'Separate vendor, enterprise, tenant-pages chunks',
            'status'     => '❌ No manualChunks configured',
            'recommend'  => 'Add manualChunks for: vendor (vue,inertia,axios), enterprise (Sk* components), charts',
        ],
        'debounce_throttle' => [
            'pattern'    => 'Reusable useDebounce / useThrottle composables',
            'usage'      => 'Search input, resize handlers, scroll handlers',
            'status'     => '⚠️ Inline setTimeout in GlobalSearch.vue — not reusable',
            'recommend'  => 'Create @/Composables/useDebounce.js and useThrottle.js',
        ],
        'virtual_scroll' => [
            'pattern'    => 'Render only visible rows in large lists',
            'usage'      => 'Timeline (>100 events), large datatables, photo galleries',
            'status'     => '❌ Not implemented',
            'recommend'  => 'Use vue-virtual-scroller or custom IntersectionObserver',
        ],
        'memoization' => [
            'pattern'    => 'computed() for derived data, shallowRef for large objects',
            'usage'      => 'Dashboard stats, workspace data, form options',
            'status'     => '✅ Computed used extensively — good',
            'recommend'  => 'Audit for computed() that could be cached (same input → same output)',
        ],
        'image_optimization' => [
            'pattern'    => 'WebP, srcset, blur placeholder, lazy loading',
            'usage'      => 'Service photos, product images, avatars',
            'status'     => '⚠️ loading="lazy" used in Photos.vue. No WebP conversion.',
            'recommend'  => 'Server-side WebP conversion on upload. Thumbnail generation.',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // AUDIT FINDINGS — Priority Fixes (Sprint 36D)
    // ═══════════════════════════════════════════════════════════

    /**
     * Top 5 critical issues that MUST be fixed before production.
     */
    public const CRITICAL_FIXES = [
        [
            'issue'    => 'Queue after_commit is false',
            'file'     => 'config/queue.php',
            'fix'      => "Set 'after_commit' => true on all queue connections",
            'risk'     => 'Jobs processing before DB transaction commits → data inconsistency',
        ],
        [
            'issue'    => 'Model::preventLazyLoading() not enabled',
            'file'     => 'app/Providers/AppServiceProvider.php',
            'fix'      => "Add Model::preventLazyLoading(!app()->isProduction()) in boot()",
            'risk'     => 'Silent N+1 queries in dev → severe in MySQL production',
        ],
        [
            'issue'    => 'WorkflowEngine::clearCache() calls Cache::flush()',
            'file'     => 'app/Services/WorkflowEngine.php',
            'fix'      => 'Replace Cache::flush() with targeted key deletion',
            'risk'     => 'One workflow update wipes ALL cache (tenants, settings, features)',
        ],
        [
            'issue'    => 'Default cache driver is database',
            'file'     => 'config/cache.php + .env',
            'fix'      => "Set CACHE_STORE=redis in .env.production",
            'risk'     => 'Database hammered with cache read/write on every request',
        ],
        [
            'issue'    => 'No Vite code splitting',
            'file'     => 'vite.config.js',
            'fix'      => 'Add rollupOptions.output.manualChunks for vendor/enterprise/charts',
            'risk'     => 'Single large JS bundle → slow first load on mobile',
        ],
    ];

    /**
     * Next 5 high-priority improvements.
     */
    public const HIGH_PRIORITY_IMPROVEMENTS = [
        [
            'issue'    => 'No useDebounce/useThrottle composable',
            'file'     => 'Create resources/js/Composables/useDebounce.js',
            'fix'      => 'Extract debounce from GlobalSearch.vue into reusable composable',
            'risk'     => 'Inconsistent debounce patterns across components',
        ],
        [
            'issue'    => 'maintenance.driver is file',
            'file'     => 'config/app.php',
            'fix'      => "Set 'driver' => env('MAINTENANCE_DRIVER', 'cache')",
            'risk'     => 'Multi-server deployment: only one server enters maintenance',
        ],
        [
            'issue'    => 'No response caching middleware',
            'file'     => 'Create app/Http/Middleware/CacheResponse.php',
            'fix'      => 'Add Cache-Control headers for static-like responses (reports, dashboards)',
            'risk'     => 'Every dashboard request re-renders same data',
        ],
        [
            'issue'    => 'No DB::prohibitDestructiveCommands() in production',
            'file'     => 'app/Providers/AppServiceProvider.php',
            'fix'      => "Add DB::prohibitDestructiveCommands(app()->isProduction()) in boot()",
            'risk'     => 'Accidental DROP/TRUNCATE in production possible',
        ],
        [
            'issue'    => 'No query benchmark tooling',
            'file'     => 'Create app/Services/Platform/QueryBenchmark.php',
            'fix'      => 'Log queries exceeding 100ms with full EXPLAIN output',
            'risk'     => 'Slow queries undetected until customer complaint',
        ],
    ];
}

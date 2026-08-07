# DATABASE & MIGRATION AUDIT

## 1. Migration Inventory
- **Central Migrations:** 21 total. 20 have run, **1 is Pending** (`2026_07_31_000001_create_personal_access_tokens_table`).
- **Tenant Migrations:** 52 total.
- **Tenant Models:** 89 total.

## 2. Table to Model Alignment
Many migrations are monolithic (e.g., `create_tenant_core_tables`, `create_retail_pos_tables`, `create_customer_relationship_tables`). While this reduces file count, it increases the risk of models not fully aligning with their corresponding database structures (missing indexes, missing foreign keys, or mismatched casts).

- **event_logs & login_histories:** Tables exist and models exist.
- **device_id:** The `Device` model exists (`app/Models/Tenant/Device.php`), but `device_id` as a foreign key on related tables (like `Service`) often lacks strict physical constraints or proper JSON indexing if stored loosely.
- **Portal Tables:** Customer portal and technician portal tables are grouped into monolithic files but lack the specific pivot tables needed for secure access control.
- **Automation Tables:** Automation definitions exist but the runtime runner structures are minimal.

## 3. Discrepancies and Risks
- **Pending Migration:** The system currently has a pending central migration for `personal_access_tokens`. This implies API tokens might be broken or incomplete.
- **Model vs Migration Drift:** With 89 Tenant Models and heavily grouped migrations, many intermediate models lack distinct schema definitions, leading to potential ORM mapping failures at runtime.
- **JSON Queries:** Extensive use of unstructured data or JSON casting in `ServiceIntakeSnapshot` and `CustomField` without proper indexing will lead to significant performance bottlenecks in a multi-tenant environment.
- **Foreign Keys:** Because tenant logic relies heavily on `stancl/tenancy`, cross-tenant boundaries are safe, but internal referential integrity (e.g. deleting a `Branch` that has `WorkOrders`) lacks robust cascading rules in the database.

## 4. Rollback and Integrity
Given the monolithic nature of the 2026_08_02 migrations, a failure in one `down()` method will lock the entire database out of a safe rollback.

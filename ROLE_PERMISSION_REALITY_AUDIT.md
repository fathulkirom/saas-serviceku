# ROLE & PERMISSION REALITY AUDIT

## 1. Verified Official Roles vs Reality
The Blueprint dictates: Super Admin, Owner, Manager, Admin, CS, Kasir, Teknisi.
However, codebase analysis reveals:
- **custom**: Exists natively in migrations (`2024_01_01_000005_add_custom_role_to_users.php`). This is a **custom** alias that breaks strict role enforcements.
- **PartnerTeknisi**: Exists as a separate model (`PartnerTeknisi.php`). This creates a fragmented role system where internal technicians are `Users` but external partners are a separate entity, complicating the `ServiceTransfer` and workload suggestion logic.

## 2. Authorization Gap Analysis
- **Missing `authorize()` Calls:** With 104 controllers and only 14 defined Policies (`app/Policies`), the vast majority of controllers bypass Laravel's robust policy system. They likely rely on generic middleware (`check.plan.feature` or Spatie `role:admin`), which fails to secure record-level access (e.g., ensuring a Technician can only view *their* assigned WorkOrders).
- **Branch Scope:** Tenant models like `WorkOrder` and `Service` lack global branch scopes. If a tenant has multiple branches, a CS at Branch A can likely view/modify Intake records for Branch B unless explicitly filtered on every query.
- **Financial Visibility:** The system lacks strict gating for financial data. Without dedicated Policies on `Invoice`, `Payment`, and `DailyDeposit`, roles like Technician or CS might have unintended access to tenant-wide revenue.

## 3. Frontend vs Backend Mismatch
- The UI contains many components with `v-if="can('some_permission')"`.
- Because the backend relies heavily on `CustomEvent` and stubs for workspaces, the UI permissions are essentially cosmetic. A user could technically hit the API endpoints directly because the backend controllers lack the mirroring `Gate::authorize('some_permission')` checks.

## 4. Verdict
**Status: 🔴 HIGH RISK (BROKEN)**
The role system is visually defined but fundamentally insecure at the backend controller level. The presence of `custom` roles and un-policed controllers makes it unsafe for multi-branch or strict-role deployments.

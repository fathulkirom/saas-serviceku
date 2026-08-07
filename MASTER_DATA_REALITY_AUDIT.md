# MASTER DATA REALITY AUDIT

## 1. Master Data Coverage
Based on the repository analysis, master data is heavily consolidated rather than having dedicated tables and CRUD pages for every entity. 

- **Consolidated in `MasterData` (Key-Value / Type-based):**
  - Device type, Brand, Series, Model, Color, Storage, RAM, Processor, Damage type, Symptom.
- **Dedicated Models Exist For:**
  - `ChecklistTemplate`, `ChecklistItem`, `Supplier`, `TaxSetting`, `CashRegister`, `Product` (Sparepart), `MasterLaborService`.
- **Missing or Undefined in Core Scope:**
  - Repair type (likely mixed into LaborService), Diagnosis template, Bank, Invoice numbering settings, Receipt template, Warranty template, Label template, WhatsApp template.

## 2. API & UI Availability
- **Routing:** There is a generic `/master-data` and `/master-services` endpoint governed by `MasterDataController`.
- **UI Render:** The controller contains endpoints, but complex relational UI for cascading dropdowns (e.g., Brand -> Series -> Model) during Service Intake is not fully wired. 
- **Permission Check:** Uses a generic `check.plan.feature:master_data` middleware, but role-based granularity (e.g. allowing CS to add a new Model but not a new Tax rate) is absent.

## 3. Operational Status
| Data Type | Table/Model | Route | Controller | UI | Status |
|-----------|-------------|-------|------------|----|--------|
| **Device Specs** (Brand, Model) | `MasterData` | Yes | Yes | Basic | 🟡 PARTIALLY WORKING |
| **Repair & Labor** | `MasterLaborService` | Yes | Yes | Basic | 🟡 PARTIALLY WORKING |
| **Checklist** | `ChecklistTemplate` | Yes | No Dedicated | No | 🔴 BROKEN |
| **Sparepart (Product)** | `Product` | Yes | Yes | Basic | 🟡 PARTIALLY WORKING |
| **Supplier** | `Supplier` | Yes | Yes | Basic | 🟡 PARTIALLY WORKING |
| **Tax & Payment** | `TaxSetting` | Yes | No Dedicated | No | 🔴 BROKEN |
| **Templates (Invoice, WA)** | Missing | No | No | No | 🔴 BROKEN |

## 4. Master Data Score
**Score: 30%**
The backend has basic schema coverage through generic models, but the UI lacks the specific operational interfaces required for a real repair shop (like designing checklists or setting up receipt printers).

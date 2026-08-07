# CORE SERVICE TRACEABILITY & FLOW AUDIT

## Traceability Matrix

| Feature / Step | Model | Controller Endpoint | UI Component | Status | Notes |
|----------------|-------|---------------------|--------------|--------|-------|
| **A. Customer Intake** | `Customer` | `CustomerController` | Exists | 🟡 PARTIALLY WORKING | Cross-tenant behavior is protected by package, but duplicate detection is weak. |
| **B. Device** | `Device` | `DeviceController` | Exists | 🟡 PARTIALLY WORKING | `device_id` exists on models. Auto-detection of IMEI/SN is missing. |
| **C. Service Creation** | `Service`, `ServiceIntakeSnapshot` | `ServiceController` | Shell / Exists | 🟠 DEFINITION ONLY | Intake snapshot structure exists, but deep JSON mapping is fragile. Photo intake is just a frontend shell without storage binding. |
| **D. Technician Allocation** | `WorkOrder`, `PartnerTeknisi` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | UI dispatches events, no listeners. Cannot assign technicians cleanly. |
| **E. Diagnosis & Quotation** | `ServiceDiagnosis`, `ServiceQuotation` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | Definitions exist in DB, but the API and UI are disconnected. |
| **F. Sparepart (Part Request)** | `ServiceRequiredPart`, `Product` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | Indent and cross-branch stock logic are missing from controllers. Negative stock guards are not enforced. |
| **G. Repair Execution** | `WorkOrder`, `ServicePhoto` | Missing Dedicated | Technician Portal | 🟠 DEFINITION ONLY | Timer and worklog exist in UI as mockups, but state transitions lack robust backend controllers. |
| **H. QC** | `ServiceQcCheck` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | Mandatory QC role permissions are unverified because `authorize()` is not used. |
| **I. Invoice and Payment** | `Sale`, `Payment` | `PaymentController` | Exists | 🔴 BROKEN | Cannot complete a true checkout cycle to generate a valid `Invoice` with tax and discounts safely. |
| **J. Delivery and Pickup** | `PickupDelivery`, `ServiceDelivery` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | Cross-branch pickup is unhandled. |
| **K. Warranty** | `ServiceWarranty`, `ServiceWarrantyClaim` | Missing Dedicated | Workspace Shell | 🔴 BROKEN | Digital warranty generation and claim UI are stubs. |

## Core Service Flow Score
**Overall Score: 15%**
While the database schema robustly maps the entire physical repair lifecycle (as seen in the numerous models like `ServiceIntakeSnapshot` and `ServiceRequiredPart`), the controllers and UI are completely severed. The flow cannot be completed end-to-end.

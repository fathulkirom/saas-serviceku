# DATABASE MODULES — ServiceKU v0.9.0-beta

## Central Database (1 global)

| Table | Purpose |
|-------|---------|
| `tenants` | Tenant registry (name, subdomain, plan, subscription) |
| `plans` | Subscription plans (features, price, business_types) |
| `payment_transactions` | Tenant subscription payments |
| `vouchers` | Discount/promo vouchers |
| `google_drive_tokens` | OAuth tokens for GDrive integration |
| `registration_verifications` | OTP for tenant registration |
| `system_logs` | Central system logging |
| `system_settings` | Platform-wide settings |
| `tenant_otps` | Tenant OTP verification |
| `tenant_stats` | Aggregated tenant statistics |

## Tenant Database (per tenant, ~80 tables)

### Core Entities
| Table | Model |
|-------|-------|
| `users` | User (tenant) |
| `branches` | Branch |
| `customers` | Customer |
| `roles` | Role |
| `permissions` | Permission |
| `role_permission` | Pivot |
| `user_role` | Pivot |
| `tenant_settings` | TenantSetting |

### Service Module
| Table | Model |
|-------|-------|
| `services` | Service |
| `service_checklists` | ServiceChecklist |
| `service_checklist_results` | ServiceChecklistResult |
| `service_diagnoses` | ServiceDiagnosis |
| `service_diagnosis_histories` | ServiceDiagnosisHistory |
| `service_quotations` | ServiceQuotation |
| `service_required_parts` | ServiceRequiredPart |
| `service_spareparts` | ServiceSparepart |
| `service_photos` | ServicePhoto |
| `service_qc_checks` | ServiceQcCheck |
| `service_deliveries` | ServiceDelivery |
| `service_transfers` | ServiceTransfer |
| `service_warranties` | ServiceWarranty |
| `service_warranty_claims` | ServiceWarrantyClaim |
| `service_intake_snapshots` | ServiceIntakeSnapshot |
| `service_part_usages` | ServicePartUsage |
| `service_part_returns` | ServicePartReturn |
| `work_orders` | WorkOrder |
| `worklogs` | Worklog |
| `service_reopens` | ServiceReopen |
| `price_change_requests` | PriceChangeRequest |

### Customer 360 Module
| Table | Model |
|-------|-------|
| `devices` | Device |
| `device_health_histories` | DeviceHealthHistory |
| `customer_interactions` | CustomerInteraction |
| `customer_communications` | CustomerCommunication |
| `customer_notes` | CustomerNotes |
| `customer_complaints` | CustomerComplaint |
| `customer_tags` | CustomerTag |
| `customer_segments` | CustomerSegment |
| `customer_message_templates` | CustomerMessageTemplate |

### Inventory Module
| Table | Model |
|-------|-------|
| `products` | Product |
| `inventory_mutations` | InventoryMutation |
| `stock_locations` | StockLocation |
| `product_stock_by_locations` | ProductStockByLocation |
| `suppliers` | Supplier |
| `purchase_orders` | PurchaseOrder |
| `purchase_order_items` | PurchaseOrderItem |
| `purchases` | Purchase |
| `purchase_items` | PurchaseItem |
| `purchase_returns` | PurchaseReturn |
| `purchase_return_items` | PurchaseReturnItem |
| `stock_allocations` | StockAllocation |
| `damaged_stocks` | DamagedStock |
| `product_price_histories` | ProductPriceHistory |
| `stock_opnames` | StockOpname |
| `stock_opname_items` | StockOpnameItem |
| `stock_adjustments` | StockAdjustment |
| `product_serials` | ProductSerial |
| `technician_inventories` | TechnicianInventory |
| `stock_transfers` | StockTransfer |

### Retail / POS Module
| Table | Model |
|-------|-------|
| `sales` | Sale |
| `sale_items` | SaleItem |
| `cash_registers` | CashRegister |
| `cashier_shifts` | CashierShift |
| `discount_rules` | DiscountRule |
| `product_bundles` | ProductBundle |
| `product_bundle_items` | ProductBundleItem |
| `product_price_levels` | ProductPriceLevel |
| `sale_serials` | SaleSerial |
| `sale_returns` | SaleReturn |
| `sale_return_items` | SaleReturnItem |
| `promotions` | Promotion |

### Finance Module
| Table | Model |
|-------|-------|
| `expenses` | Expense |
| `daily_deposits` | DailyDeposit |
| `commissions` | Commission |
| `payment_reconciliations` | PaymentReconciliation |
| `tax_settings` | TaxSetting |

### Workflow Engine
| Table | Model |
|-------|-------|
| `workflows` | Workflow |
| `workflow_states` | WorkflowState |
| `workflow_transitions` | WorkflowTransition |
| `workflow_actions` | WorkflowAction |
| `workflow_histories` | WorkflowHistory |

### Automation & Monitoring
| Table | Model |
|-------|-------|
| `automation_rules` | AutomationRule |
| `automation_logs` | AutomationLog |
| `event_logs` | EventLog |
| `activity_logs` | ActivityLog |
| `system_alerts` | SystemAlert |

### Requests Module
| Table | Model |
|-------|-------|
| `requests` | Request |
| `request_histories` | RequestHistory |
| `request_timelines` | RequestTimeline |
| `request_idempotencies` | RequestIdempotency |

### Other
| Table | Model |
|-------|-------|
| `master_data` | MasterData |
| `master_labor_services` | MasterLaborService |
| `modules` | Module |
| `tenant_modules` | (Pivot, module activation) |
| `indents` | Indent |
| `checklist_templates` | ChecklistTemplate |
| `checklist_items` | ChecklistItem |
| `sops` | Sop |
| `sop_read_logs` | SopReadLog |
| `knowledge_bases` | KnowledgeBase |
| `partner_teknisis` | PartnerTeknisi |
| `pickup_deliveries` | PickupDelivery |
| `quick_replies` | QuickReply |
| `wa_gateway_configs` | WaGatewayConfig |
| `attendances` | Attendance |
| `shifts` | Shift |
| `login_histories` | LoginHistory |
| `import_logs` | ImportLog |
| `sla_configs` | SlaConfig |
| `custom_fields` | (Dynamic) |
| `part_bookings` | PartBooking |

> Total: ~80 tenant tables

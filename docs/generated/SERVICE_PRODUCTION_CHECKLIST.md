# Service Production Checklist — Sprint 36A

> Pre-go-live validation checklist for HP & Laptop service center operations.

---

## ✅ Workflow Validation

- [ ] Customer intake → diterima → diagnosa flow complete
- [ ] Diagnosis → konfirmasi → dikerjakan flow complete
- [ ] Indent part → dikerjakan flow complete
- [ ] On partner → selesai flow complete
- [ ] Repair → QC → siap_diambil flow complete
- [ ] Payment → close flow complete
- [ ] Cancel from any valid status
- [ ] No dead-end statuses exist

## ✅ Status Validation

- [ ] Close blocked without payment
- [ ] Ready blocked without QC pass
- [ ] Repair blocked without diagnosis
- [ ] Diagnosis blocked without checklist
- [ ] Diagnosis blocked without intake photo
- [ ] Invalid transitions rejected by backend

## ✅ Role Validation

- [ ] CS can create, assign, and view services
- [ ] Technician can diagnose, start, complete, add notes/photos/parts
- [ ] Manager can view all, cancel, close, access warranty
- [ ] Owner has full access
- [ ] Cashier can process payment and mark ready

## ✅ Permission Validation

- [ ] `assign_technician` respects role
- [ ] `work_on_services` respects role
- [ ] `manage_products` gates sparepart access
- [ ] `manage_sales` gates invoice access

## ✅ Feature Validation

- [ ] `services` feature gate works
- [ ] `denyBusinessTypes: ['retail_only']` blocks retail-only tenants
- [ ] Feature flags respect plan limits

## ✅ Dashboard Validation

- [ ] ServiceWidget shows accurate count
- [ ] ServiceCompletedWidget shows today's completed
- [ ] ServiceTrendWidget shows 7-day trend
- [ ] RecentServiceWidget shows latest services
- [ ] All widgets drill-down to service detail

## ✅ Report Validation

- [ ] Service daily report accurate
- [ ] Service status pie chart accurate
- [ ] Revenue report matches finance

## ✅ Automation Validation

- [ ] Status change triggers automation
- [ ] Service completed triggers WhatsApp notification
- [ ] Technician assigned triggers internal notification
- [ ] Warranty expiring triggers reminder

## ✅ Notification Validation

- [ ] Customer receives status updates
- [ ] Technician receives assignment notification
- [ ] Manager receives overdue alerts
- [ ] All 14 notification events fire correctly

## ✅ UI/UX Validation

- [ ] No "setengah jadi" pages
- [ ] Loading skeletons on all data sections
- [ ] Empty states with helpful CTAs
- [ ] Error states with retry
- [ ] Success toasts after actions
- [ ] Confirmation dialogs for destructive actions
- [ ] Keyboard shortcuts work
- [ ] Responsive on mobile/tablet
- [ ] Dark mode renders correctly

## ✅ Data Integrity

- [ ] Status transitions are atomic
- [ ] Optimistic updates rollback on failure
- [ ] No orphaned records on service delete
- [ ] Timeline events never deleted (append-only)

## ✅ Backward Compatibility

- [ ] Existing services with old statuses still accessible
- [ ] Legacy statuses (if any) handled gracefully
- [ ] No database schema changes

---

*Service Production Checklist — Sprint 36A*

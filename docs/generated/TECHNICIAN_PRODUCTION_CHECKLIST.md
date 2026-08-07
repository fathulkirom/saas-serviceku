# Technician Production Checklist — Sprint 36B

> Pre-go-live validation checklist for technician operations.

---

## ✅ Workflow Validation

- [ ] Technician can see assigned jobs on dashboard
- [ ] Technician can accept job → timer starts
- [ ] Technician can pause job → timer pauses
- [ ] Technician can resume job → timer resumes
- [ ] Technician can finish job → submitted for QC
- [ ] Timer calculates effective working time (minus pauses)
- [ ] Jobs appear in correct status categories

## ✅ Diagnosis Validation

- [ ] Diagnosis form saves all 15 fields
- [ ] AI assist returns relevant suggestions
- [ ] Diagnosis templates load for common issues
- [ ] Diagnosis history preserved (append-only)
- [ ] Estimated cost/time saved correctly

## ✅ Sparepart Validation

- [ ] Technician can search spareparts
- [ ] Barcode/QR scan works
- [ ] Request parts → inventory notified
- [ ] Parts used → stock deducted
- [ ] Parts returned → stock added back
- [ ] Parts usage recorded in service timeline

## ✅ Photo Validation

- [ ] Multi-upload works
- [ ] Drag-upload works
- [ ] Photos categorized correctly
- [ ] Preview and zoom work
- [ ] Compression applied
- [ ] Photos linked to correct service

## ✅ Measurement Validation

- [ ] Measurement form saves test point, expected, actual values
- [ ] All 5 modes supported (V, A, Ω, diode, °C)
- [ ] Measurements linked to service

## ✅ QC Validation

- [ ] All 22 QC items displayed
- [ ] QC pass → status: siap_diambil
- [ ] QC fail → status: kembali ke dikerjakan
- [ ] QC notes saved

## ✅ Internal Notes Validation

- [ ] Internal notes saved (not visible to customer)
- [ ] Tips, warnings, repeat issues logged
- [ ] Notes searchable

## ✅ AI Assist Validation

- [ ] AI returns probable causes
- [ ] AI returns diagnostic steps
- [ ] AI returns part recommendations
- [ ] AI shows similar past cases
- [ ] Low confidence (<70%) flagged

## ✅ KPI Validation

- [ ] Jobs completed count accurate
- [ ] Average repair time calculated correctly
- [ ] First time fix rate accurate
- [ ] Warranty return rate accurate
- [ ] Productivity score computed
- [ ] Revenue per technician accurate

## ✅ Performance Validation

- [ ] Workspace loads < 1 second
- [ ] Timer updates in real-time
- [ ] Photo upload < 3 seconds
- [ ] Lazy loading on tabs
- [ ] Partial reload (not full page)

## ✅ Integration Validation

- [ ] Status changes → Workflow Center
- [ ] Part requests → Inventory
- [ ] QC results → Timeline
- [ ] Job completion → Notification Center
- [ ] Timer data → Reporting Engine

---

*Technician Production Checklist — Sprint 36B*

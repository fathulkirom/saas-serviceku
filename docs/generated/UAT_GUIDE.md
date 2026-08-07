# UAT Guide — Sprint 36E (RC1)

> User Acceptance Testing guide for ServiceKU v1.0.0-rc1.

---

## 🎯 UAT Objectives

Validate that ServiceKU meets real-world HP & Laptop service center requirements:
- Complete service lifecycle from intake to warranty
- Multi-role workflow (CS, Teknisi, Kasir, Manager, Owner)
- Real-time dashboard and reporting
- Digital customer experience (tracking, approval, warranty)

---

## 🧪 UAT Scenarios

### Scenario 1: Walk-in Service (Most Common)
```
1. CS creates new service for walk-in customer
2. CS fills device info (brand, model, IMEI, problem)
3. CS completes intake checklist (Body, LCD, Charging)
4. CS takes 2 intake photos
5. CS assigns to available technician
6. Technician views assigned jobs → accepts
7. Technician runs diagnosis → saves findings
8. System generates estimation → sent to customer
9. Customer approves via WA link
10. Technician starts repair → timer runs
11. Technician adds sparepart (LCD replacement)
12. Technician finishes repair → submits for QC
13. QC technician runs 22-point checklist → all pass
14. System notifies customer: "Ready for pickup"
15. Customer arrives → pays → receives device
16. Warranty auto-activated (30d service, 90d parts)
```

### Scenario 2: Indent (Waiting Parts)
```
1. CS creates service
2. Technician diagnoses → needs rare part
3. Technician marks "Indent Part" → status: indent
4. Purchasing orders part
5. Part arrives → status updates to: dikerjakan
6. Continue normal flow
```

### Scenario 3: Customer Rejects Estimate
```
1. Technician diagnoses → sends estimate
2. Customer rejects (too expensive)
3. CS discusses alternatives with customer
4. Option A: Revised estimate → customer approves → continue
5. Option B: Customer cancels → status: cancel
```

### Scenario 4: QC Fail → Rework
```
1. Technician finishes repair → submits QC
2. QC technician finds fingerprint sensor not working
3. QC marked as FAIL → status back to: dikerjakan
4. Technician reworks → fixes fingerprint
5. Resubmits QC → PASS → status: siap_diambil
```

### Scenario 5: Warranty Claim
```
1. Customer returns 20 days after service
2. Reports same issue recurring
3. CS checks warranty → active (30 days service warranty)
4. CS opens warranty claim
5. Technician verifies → genuine warranty issue
6. Repair done under warranty → no charge
7. Warranty claim recorded
```

---

## ✅ UAT Pass Criteria

| Criterion | Threshold |
|-----------|-----------|
| All 5 scenarios pass | 5/5 |
| No unexpected errors | 0 |
| All notifications delivered | 15/15 events |
| All dashboard widgets update | Real-time |
| All reports generate | Correct data |
| Multi-role access correct | Per role matrix |

---

*UAT Guide — Sprint 36E*

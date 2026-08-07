# Digital Approval — Sprint 36C

> Customer self-service estimation approval workflow.

---

## ✅ Approval Actions

| Action | Description | Result |
|--------|-------------|--------|
| Setujui | Approve estimate → repair proceeds | Status: `dikerjakan` |
| Tolak | Reject estimate → service cancelled | Status: `cancel` |
| Minta Revisi | Request estimate revision | CS notified to revise |

---

## 📋 Approval Scenarios

| Scenario | Trigger | Customer Action |
|----------|---------|-----------------|
| Initial Estimate | Diagnosis complete | Approve/Reject/Revise |
| Additional Cost | Unexpected issue found | Approve additional cost |
| Additional Parts | More parts needed | Approve part replacement |
| Extended Time | Repair takes longer | Acknowledge extended timeline |

---

## 🔒 Audit Trail

Every approval action records:
- Who approved (customer identity verified)
- When approved (timestamp)
- What was approved (estimate version)
- Method (portal click, WhatsApp link, in-store signature)
- IP address and device info

---

*Digital Approval — Sprint 36C*

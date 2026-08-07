# HR Deprecation Strategy

> Plan for future HRM module iterations & migration.

---

## ❌ Deprecated in Sprint 21

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual employee tracking | Replaced by Employee Master table | `HRMDefinitions::employeeTable()` |
| Spreadsheet attendance | Replaced by Attendance Engine | `HRMDefinitions::attendanceTable()` |
| Paper leave forms | Replaced by Leave Management | `HRMDefinitions::leaveTable()` |
| Manual payroll calculation | Replaced by Payroll Engine | `HRMDefinitions::payrollTable()` |
| Ad-hoc performance review | Replaced by Performance Engine | `HRMDefinitions::performanceTable()` |
| Email-based training | Replaced by Training Engine | `HRMDefinitions::trainingTable()` |
| Manual recruitment tracking | Replaced by Recruitment Pipeline | `HRMDefinitions::recruitmentTable()` |
| Paper asset forms | Replaced by Asset Management | `HRMDefinitions::assetTable()` |

---

## 🔮 Future Enhancements (Sprint 22+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Employee Self-Service Portal | P2 | Own profile, leave, payslip access |
| Advanced Shift Scheduling (Drag & Drop) | P2 | Visual scheduler |
| Overtime Approval Workflow | P2 | Multi-level approval |
| Biometric Integration | P3 | Fingerprint/face scanner API |
| NFC/QR Attendance Hardware | P3 | Physical device integration |
| Advanced Payroll (THR, Bonus, PPh 21 detail) | P2 | Full tax compliance |
| 360° Feedback | P3 | Peer + subordinate + manager review |
| E-Learning Platform | P3 | Online courses, video, quiz |
| Succession Planning | P4 | Talent pool, replacement chart |
| Employee Satisfaction Survey | P4 | Anonymous surveys |
| Exit Interview | P3 | Offboarding process |
| Multi-Country Payroll | P4 | Different tax/BPJS rules |

---

*HR Deprecation — Sprint 21.0*

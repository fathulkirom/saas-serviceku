# Diagnosis Guide — Sprint 36B

> Complete diagnosis workflow with AI-assisted templates for HP & Laptop repair.

---

## 🩺 Diagnosis Components

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Device Category | Select | ✅ | Smartphone, Tablet, Laptop, Smartwatch, AirPods, Other |
| Brand | Select/Text | ✅ | Device manufacturer |
| Model | Text | ✅ | Device model number |
| IMEI/Serial | Text | ✅ | Device identifier |
| Symptoms | Multi-select/Text | ✅ | What customer reports |
| Damage Category | Select | ✅ | Physical, Liquid, Software, Component, Wear & Tear, Unknown |
| Root Cause | Text | ✅ | Identified root cause of issue |
| Component Affected | Multi-select | ✅ | Which components are damaged |
| Solution | Text | ✅ | Recommended repair approach |
| Estimated Time | Number | ✅ | Estimated repair minutes |
| Estimated Cost | Number | ✅ | Estimated repair cost (Rp) |
| Risk Level | Select | ✅ | Low, Medium, High |
| Repair Difficulty | Select | ✅ | Easy, Medium, Hard, Extreme |
| Repair Notes | Text | — | Notes visible to CS and customer |
| Internal Notes | Text | — | Technician-only notes |

---

## 🤖 AI-Assisted Diagnosis

The AI Knowledge Assist provides:
- Probable causes based on symptoms
- Suggested diagnostic steps
- Recommended spareparts
- Time estimates based on difficulty
- Similar past cases with solutions

AI prompts are pre-configured in `TechnicianWorkflowHelper::AI_ASSIST_PROMPTS`.

**AI hanya membantu — keputusan tetap di tangan teknisi.**

---

## 📋 Diagnosis Templates (18 templates)

Pre-built templates for common HP/Laptop issues:
- Power: No turn on, restart loop
- Display: No display, flicker, lines
- Touch: Not working
- Charging: Not working, battery drain
- Audio: No sound
- Camera: Not working
- Network: WiFi, Bluetooth
- Biometric: Face ID, Fingerprint
- Keyboard/Trackpad: Not working
- Mainboard: Water damage, short circuit

---

*Diagnosis Guide — Sprint 36B*

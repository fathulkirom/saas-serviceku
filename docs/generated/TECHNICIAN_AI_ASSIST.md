# Technician AI Assist — Sprint 36B

> AI-powered knowledge assistance for repair technicians.

---

## 🤖 AI Capabilities

| Capability | Description | Prompt Template |
|------------|-------------|----------------|
| Symptom → Cause | Given symptoms, AI suggests probable causes | `diagnose` |
| Cause → Solution | Given root cause, AI suggests repair approach | `solution` |
| Part Recommendation | AI recommends spareparts for repair | `parts` |
| Time Estimation | AI estimates repair time by difficulty | `estimate` |
| Similar Cases | AI finds past services with similar symptoms | `similar` |

---

## 📋 AI Assist Usage

```
Technician opens Diagnosis tab
  → Enters symptoms and device info
  → Clicks "AI Assist" button
  → AI returns:
    - Top 3 probable causes (with confidence %)
    - Suggested diagnostic steps
    - Recommended spareparts
    - Estimated time range
    - Link to similar past cases
  → Technician reviews and decides
```

---

## ⚠️ AI Limitations

- AI **membantu**, bukan mengganti keputusan teknisi
- AI based on historical data — may miss new/rare issues
- AI confidence <70% → marked as "low confidence"
- Final diagnosis always recorded by technician

---

## 🔗 Integration

- AI calls routed through AI Intelligence Layer
- Results cached per symptom set (1 hour TTL)
- AI usage logged for improvement tracking

---

*Technician AI Assist — Sprint 36B*

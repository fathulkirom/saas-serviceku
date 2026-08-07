# Measurement Guide — Sprint 36B

> Board-level measurement tracking for advanced repair technicians.

---

## 📐 Measurement Modes

| Mode | Unit | Use Case |
|------|------|----------|
| Voltage (V) | V | Check power rails, signal levels |
| Current (A) | A | Measure power consumption, short detection |
| Resistance (Ω) | Ω | Check component values, trace continuity |
| Diode Mode | V | Check semiconductor junctions, short to ground |
| Temperature (°C) | °C | Check IC/component temperature |

---

## 🔬 Pre-Defined Test Points (11 templates)

### Power Section
| Test Point | Expected Range | Mode |
|------------|---------------|------|
| VBAT (Battery Voltage) | 3.6V – 4.4V | Voltage |
| VBUS (Charger Input) | 4.8V – 5.2V | Voltage |
| VCC Main | 3.2V – 4.2V | Voltage |
| VCC Core (CPU) | 0.8V – 1.2V | Voltage |
| Current Consumption (Idle) | 0.01A – 0.10A | Current |

### Display Section
| Test Point | Expected Range | Mode |
|------------|---------------|------|
| VCC LCD | 1.7V – 3.3V | Voltage |
| Backlight Voltage | 15V – 25V | Voltage |

### Mainboard Section
| Test Point | Expected Range | Mode |
|------------|---------------|------|
| Short to Ground | 0.3V – 0.8V (diode) | Diode |
| Coil Resistance | 0.1Ω – 5.0Ω | Resistance |

### Thermal Section
| Test Point | Expected Range | Mode |
|------------|---------------|------|
| IC Power Temperature | 30°C – 60°C | Temperature |
| CPU Temperature | 35°C – 75°C | Temperature |

---

## 📝 Measurement Recording

Each measurement records:
- Test point name
- Expected min/max value
- Actual measured value
- Unit
- Mode used
- Notes (e.g., "tegangan tidak stabil", "short terdeteksi")

---

*Measurement Guide — Sprint 36B*

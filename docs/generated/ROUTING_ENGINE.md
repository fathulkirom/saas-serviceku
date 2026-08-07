# Routing Engine

> Operation sequence with timing, machine assignment, and version control.

---

## 🗺️ Routing Structure

| Operation | Sequence | Machine | Setup (min) | Run (min) | Move (min) | Queue (min) |
|-----------|----------|---------|-------------|-----------|------------|-------------|
| Op 10 | 1 | Machine A | 15 | 5 | 2 | 10 |
| Op 20 | 2 | Machine B | 10 | 8 | 2 | 15 |
| Op 30 | 3 | Machine C | 20 | 3 | 2 | 5 |

---

## ⏱️ Time Components

| Component | Description |
|-----------|-------------|
| Setup Time | Machine preparation |
| Run Time | Processing per unit |
| Move Time | Transfer between operations |
| Queue Time | Waiting before operation |
| Standard Time | Setup + (Run × Qty) + Move + Queue |

---

*Routing Engine — Sprint 25.0*

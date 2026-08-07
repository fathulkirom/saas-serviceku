# Technician KPI — Sprint 36B

> 10 key performance indicators for technician productivity measurement.

---

## 📊 KPI Metrics

| # | KPI | Target | Unit | Description |
|---|-----|--------|------|-------------|
| 1 | Jobs Completed | 10/day | jobs | Total services completed in period |
| 2 | Average Repair Time | 60 min | min | Average effective working minutes per job |
| 3 | First Time Fix Rate | 90% | % | % of jobs fixed without rework |
| 4 | Warranty Return Rate | <3% | % | % of completed jobs returned under warranty |
| 5 | Productivity Score | 80% | % | Composite score based on all KPIs |
| 6 | Utilization Rate | 70% | % | % of working time spent on repairs |
| 7 | Customer Rating | 4.5/5 | /5 | Average customer satisfaction |
| 8 | Revenue Generated | 5M/mo | Rp | Total service charge + parts revenue |
| 9 | Parts Usage Accuracy | 95% | % | % of parts used that match diagnosis |
| 10 | Rework Count | 0 | jobs | Number of services needing rework |

---

## 📈 KPI Calculation

### Productivity Score
```
Productivity = (
  (jobs_completed / target_jobs) * 30 +
  (first_time_fix / 100) * 25 +
  ((100 - warranty_return) / 100) * 20 +
  (utilization / target_utilization) * 15 +
  (customer_rating / 5) * 10
) * 100
```

### Utilization Rate
```
Utilization = effective_working_minutes / total_shift_minutes * 100
```

### First Time Fix Rate
```
FTFR = (total_jobs - rework_jobs) / total_jobs * 100
```

---

*Technician KPI — Sprint 36B*

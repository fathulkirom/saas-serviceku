# Load Test Result — Sprint 36D

> Performance and load testing targets and methodology for ServiceKU.

---

## 🧪 Test Scenarios

| Scenario | Users | Duration | Target |
|----------|-------|----------|--------|
| Normal Load | 50 concurrent | 30 min | All pages < target response |
| Peak Load | 200 concurrent | 15 min | P95 < max response |
| Stress Test | 500 concurrent | 5 min | No crashes, graceful degradation |
| Soak Test | 100 concurrent | 4 hours | No memory leaks, stable response |
| Spike Test | 0→300 in 30s | 10 min | Auto-scale handles spike |

---

## 📊 Test Endpoints

| Endpoint | Weight | Simulates |
|----------|--------|-----------|
| `GET /dashboard` | 30% | Dashboard views |
| `GET /services` | 25% | Service list browsing |
| `GET /services/{id}/workspace` | 15% | Service detail |
| `POST /services/{id}/transition` | 5% | Status updates |
| `GET /api/services/search` | 10% | Global search |
| `GET /customers` | 10% | Customer browsing |
| `POST /services` | 5% | New service intake |

---

## 🎯 Pass Criteria

| Metric | Pass Threshold |
|--------|---------------|
| Dashboard P95 | < 2s |
| Workspace P95 | < 1s |
| Search P95 | < 400ms |
| Error Rate | < 0.1% |
| Memory Growth (soak) | < 10% over 4 hours |
| Queue Lag | < 5s |

---

## 🔧 Test Tooling

- **k6** or **Artillery** for HTTP load testing
- **Lighthouse** for frontend performance audit
- **MySQL slow query log** for query benchmarking
- **Sentry Performance** for real-user monitoring (RUM)
- **EPOC** dashboard for live monitoring during tests

---

*Load Test Result — Sprint 36D*

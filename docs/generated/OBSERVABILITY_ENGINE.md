# Observability Engine

> Metrics, Logs, Traces, Events — unified observability for the entire platform.

---

## 🔭 Observability Pillars

| Pillar | Description |
|--------|-------------|
| Metrics | CPU, memory, storage, queue depth, API latency, error rates |
| Logs | Structured JSON logs with correlation IDs |
| Traces | Distributed tracing across services (correlation ID propagation) |
| Events | Infrastructure events, deployments, security incidents |

---

## 🔗 Correlation ID

Every request carries a `X-Correlation-ID` header propagated through:
- HTTP requests (middleware injection)
- Queue jobs (job payload metadata)
- Scheduled tasks (auto-generated)
- External API calls (header forwarding)
- Database queries (SQL comment)

---

## 📊 Observability Features

- Error Timeline: chronological view of all errors across modules
- Performance Timeline: response time trends over time
- Service Dependency Map: visual map of service-to-service calls
- Real-time metric streaming via WebSocket
- Threshold-based alerting with configurable rules
- Historical data retention (7/30/90 days based on plan)

---

*Observability Engine — Sprint 35.0*

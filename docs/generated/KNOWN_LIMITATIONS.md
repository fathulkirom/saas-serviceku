# Known Limitations — Sprint 36E (RC1)

> Documented limitations and deferred features for ServiceKU v1.0.0-rc1.

---

## 🚧 Known Limitations

### Portal UIs
| Limitation | Impact | Plan |
|------------|--------|------|
| Customer Portal: 13/14 tabs without Vue components | Portal works but limited to Overview | Build remaining tabs post-RC1 |
| Technician Portal: 14/15 tabs without Vue components | Portal works but limited to Overview | Build remaining tabs post-RC1 |
| Sidebar widgets not wired in portals | Sidebar shows empty | Wire up post-RC1 |

### Performance
| Limitation | Impact | Plan |
|------------|--------|------|
| No virtual scroll for large lists | Timeline >100 events may be slow | Add vue-virtual-scroller |
| No WebP auto-conversion | Images served as uploaded | Add server-side WebP conversion |
| Cache driver defaults to database | Production requires Redis | Documented in PRODUCTION_TUNING.md |

### Features
| Limitation | Impact | Plan |
|------------|--------|------|
| No offline mode | Requires internet connection | PWA service worker ready |
| No native mobile app | Web-only (mobile responsive) | PWA covers most use cases |
| Marketplace connectors limited | 52 connectors defined but few active | Activate per tenant need |
| AI features require API keys | OpenAI/Gemini keys needed per tenant | Self-hosted LLM option future |

### Infrastructure
| Limitation | Impact | Plan |
|------------|--------|------|
| Single-region deployment | No geo-redundancy | Multi-region post-RC1 |
| No horizontal auto-scaling | Manual scaling only | Kubernetes/auto-scaling future |
| Backup to same region | No off-site disaster recovery | Multi-region backup future |

---

## 📋 Deferred Features (Post-RC1)

| Feature | Priority | Sprint Target |
|---------|----------|---------------|
| Portal Vue components (all tabs) | High | 37 |
| Mobile PWA polish | Medium | 38 |
| Marketplace connector activation | Medium | 39 |
| Advanced AI/ML models | Low | 40 |
| Native mobile app | Low | 41+ |
| Multi-region deployment | Low | 42+ |

---

*Known Limitations — Sprint 36E*

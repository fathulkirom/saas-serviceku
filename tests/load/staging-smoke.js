// ============================================
// k6 Load Test — ServiceKU Staging Smoke Test
// ============================================
// Jalankan: k6 run tests/load/staging-smoke.js
// Atau via GitHub Actions: grafana/k6-action
// ============================================
import http from 'k6/http';
import { check, sleep } from 'k6';

// Threshold: p95 < 500ms dan error rate < 1%
export const options = {
  vus: __ENV.VUS ? Number(__ENV.VUS) : 50,
  duration: __ENV.DURATION || '60s',
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.STAGING_URL || 'http://localhost:8000';

export default function () {
  // 1. Landing page
  const home = http.get(BASE_URL + '/');
  check(home, {
    'homepage status 200': (r) => r.status === 200,
    'homepage has title': (r) => r.body && r.body.includes('ServiceKU'),
  });

  sleep(0.5);

  // 2. Health endpoint (jika ada)
  const health = http.get(BASE_URL + '/up');
  check(health, {
    'health status 200': (r) => r.status === 200,
  });

  sleep(0.5);

  // 3. Login page
  const login = http.get(BASE_URL + '/login');
  check(login, {
    'login page status 200': (r) => r.status === 200,
  });

  sleep(1);
}

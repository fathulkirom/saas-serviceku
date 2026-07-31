// ============================================
// k6 Load Test — ServiceKU Live (production)
// ============================================
// Jalankan:
//   k6 run tests/load/live-smoke.js            # smoke (10 VU, 15s)
//   VUS=100 DURATION=30s k6 run tests/load/live-smoke.js
// ============================================
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: __ENV.VUS ? Number(__ENV.VUS) : 10,
  duration: __ENV.DURATION || '15s',
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.TARGET_URL || 'https://serviceku.my.id';

export default function () {
  // 1. Landing page (home)
  const home = http.get(BASE_URL + '/');
  check(home, {
    'homepage status 200': (r) => r.status === 200,
    'homepage has title': (r) => r.body && r.body.includes('ServiceKU'),
  });

  sleep(0.3);

  // 2. Health endpoint Laravel (/up)
  const health = http.get(BASE_URL + '/up');
  check(health, {
    'health /up status 200': (r) => r.status === 200,
  });

  sleep(0.4);
}

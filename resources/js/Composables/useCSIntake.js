/**
 * ═══════════════════════════════════════════════════════════
 * useCSIntake — CS Intake Flow Composable
 * ═══════════════════════════════════════════════════════════
 * 
 * SERVICEKU v1.0: Customer search, IMEI auto-detect, device history.
 * 
 * Usage in Services/Create.vue:
 *   const intake = useCSIntake();
 *   await intake.lookupIMEI(imeiValue);
 *   // intake.deviceHistory populated with service/warranty/damage/part/technician history
 */

import { ref, computed } from 'vue';

export function useCSIntake() {
  const isSearching = ref(false);
  const deviceFound = ref(null);
  const customerFound = ref(null);
  const deviceHistory = ref({
    services: [],
    warranty: null,
    damages: [],
    parts: [],
    technicians: [],
  });
  const lookupError = ref('');

  /**
   * Search customer by name, phone, or IMEI.
   */
  async function searchCustomer(query, type = 'name') {
    isSearching.value = true;
    lookupError.value = '';
    try {
      const params = new URLSearchParams({ q: query, type });
      const response = await fetch(`/customers/search?${params}`);
      if (!response.ok) throw new Error('Customer search failed');
      const data = await response.json();
      return data.customers || data || [];
    } catch (e) {
      lookupError.value = e.message;
      return [];
    } finally {
      isSearching.value = false;
    }
  }

  /**
   * IMEI lookup — auto-detects device + customer.
   * Calls ServiceIntakeController::matchDevice().
   */
  async function lookupIMEI(imei) {
    if (!imei || imei.length < 5) return null;
    isSearching.value = true;
    lookupError.value = '';
    deviceFound.value = null;
    customerFound.value = null;
    deviceHistory.value = { services: [], warranty: null, damages: [], parts: [], technicians: [] };

    try {
      const response = await fetch(`/services/intake/match-device?imei=${encodeURIComponent(imei)}`);
      if (!response.ok) {
        if (response.status === 404) return null; // Not found — normal
        throw new Error('Device lookup failed');
      }
      const data = await response.json();

      deviceFound.value = data.device || null;
      customerFound.value = data.customer || null;

      // Populate device history from response
      if (data.history) {
        deviceHistory.value = {
          services: data.history.services || [],
          warranty: data.history.warranty || null,
          damages: data.history.damages || [],
          parts: data.history.parts || [],
          technicians: data.history.technicians || [],
        };
      }

      return { device: deviceFound.value, customer: customerFound.value, history: deviceHistory.value };
    } catch (e) {
      lookupError.value = e.message;
      return null;
    } finally {
      isSearching.value = false;
    }
  }

  /**
   * Create a new customer inline (fast create during intake).
   */
  async function fastCreateCustomer(customerData) {
    isSearching.value = true;
    lookupError.value = '';
    try {
      const response = await fetch('/customers/ajax-store', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify(customerData),
      });
      if (!response.ok) throw new Error('Failed to create customer');
      const data = await response.json();
      return data.customer || data;
    } catch (e) {
      lookupError.value = e.message;
      return null;
    } finally {
      isSearching.value = false;
    }
  }

  /**
   * Check if IMEI belongs to a blacklisted customer.
   */
  function isBlacklisted() {
    return customerFound.value?.blacklisted || customerFound.value?.tags?.includes('blacklist') || false;
  }

  /**
   * Check if device is still under warranty.
   */
  function isUnderWarranty() {
    if (!deviceHistory.value.warranty) return false;
    const until = new Date(deviceHistory.value.warranty.warranty_until);
    return until > new Date();
  }

  return {
    // State
    isSearching,
    deviceFound,
    customerFound,
    deviceHistory,
    lookupError,

    // Actions
    searchCustomer,
    lookupIMEI,
    fastCreateCustomer,

    // Computed helpers
    isBlacklisted,
    isUnderWarranty,
  };
}

import { computed } from 'vue';

export function useFormatter() {
  const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
  };

  const formatCurrency = (num) => {
    return 'Rp ' + formatNumber(num);
  };

  const formatDate = (dateStr, options = {}) => {
    if (!dateStr) return '-';
    const defaults = {
      day: 'numeric', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    };
    return new Date(dateStr).toLocaleDateString('id-ID', { ...defaults, ...options });
  };

  const currentDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });
  });

  const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 10) return 'Selamat Pagi';
    if (h < 15) return 'Selamat Siang';
    if (h < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  });

  const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
  };

  return { formatNumber, formatCurrency, formatDate, currentDate, greeting, getInitials };
}

export const SERVICE_STATUS_LABELS = {
  menunggu_alokasi: 'Menunggu Alokasi',
  diterima: 'Diterima',
  dikerjakan: 'Dikerjakan',
  indent: 'Waiting Parts',
  onpartner: 'Di Partner',
  menunggu_konfirmasi_pelanggan: 'Konfirmasi Pelanggan',
  menunggu_konfirmasi_internal: 'Konfirmasi Internal',
  siap_diambil: 'Siap Diambil',
  selesai: 'Selesai',
  cancel: 'Batal',
  diambil: 'Diambil',
  void: 'Void',
};

export const PAYMENT_STATUS_LABELS = {
  lunas: 'Lunas', paid: 'Lunas',
  dp: 'DP', partial: 'DP',
  belum_bayar: 'Belum Bayar', unpaid: 'Belum Bayar',
  draft: 'Draft', cancel: 'Batal',
};

export const STATUS_BADGE_VARIANT = {
  menunggu_alokasi: 'yellow', diterima: 'orange',
  dikerjakan: 'blue', indent: 'purple',
  onpartner: 'purple',
  menunggu_konfirmasi_pelanggan: 'red',
  menunggu_konfirmasi_internal: 'red',
  siap_diambil: 'green', selesai: 'green',
  diambil: 'green', cancel: 'red', void: 'default',
  lunas: 'green', paid: 'green', dp: 'yellow',
  partial: 'yellow', draft: 'yellow',
  belum_bayar: 'red', unpaid: 'red',
};

export function statusLabel(status, type = 'service') {
  const map = type === 'payment' ? PAYMENT_STATUS_LABELS : SERVICE_STATUS_LABELS;
  return map[status] || status || '-';
}

export function statusBadgeVariant(status) {
  return STATUS_BADGE_VARIANT[status] || 'default';
}

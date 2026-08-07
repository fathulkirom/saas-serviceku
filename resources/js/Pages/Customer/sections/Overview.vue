<template>
  <div class="space-y-5">
    <!-- Customer 360° Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Servis</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ data?.service_count || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Purchase</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(data?.total_spending) }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Lifetime Value</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">Rp {{ formatNumber(data?.lifetime_value) }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Last Visit</p><p class="text-sm font-bold mt-1" :style="{ color: 'var(--text-primary)' }">{{ formatDate(data?.last_visit) }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Devices</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--text-primary)' }">{{ data?.device_count || 0 }}</p></div>
    </div>

    <!-- Customer Info + Member Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Customer Info -->
      <SkCard title="Informasi Pelanggan" size="md" class="lg:col-span-2" v-if="data">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div><p class="sk-caption">Nama</p><p class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ data.name || '-' }}</p></div>
          <div><p class="sk-caption">Kode</p><p class="sk-code text-xs">{{ data.customer_code || '-' }}</p></div>
          <div><p class="sk-caption">Tipe</p><span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="typeStyle">{{ typeLabel }}</span></div>
          <div><p class="sk-caption">Telepon</p><p class="sk-label-sm">{{ data.phone || '-' }}</p></div>
          <div><p class="sk-caption">WhatsApp</p><p class="sk-label-sm">{{ data.whatsapp || data.phone || '-' }}</p></div>
          <div><p class="sk-caption">Email</p><p class="sk-label-sm">{{ data.email || '-' }}</p></div>
          <div><p class="sk-caption">Alamat</p><p class="sk-body-sm">{{ data.address || '-' }}</p></div>
          <div><p class="sk-caption">Sumber</p><p class="sk-label-sm">{{ data.source || '-' }}</p></div>
          <div><p class="sk-caption">Terdaftar</p><p class="sk-label-sm">{{ formatDate(data.created_at) }}</p></div>
        </div>
        <!-- Tags -->
        <div v-if="data.tags?.length" class="flex flex-wrap gap-1 mt-3 pt-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
          <span v-for="tag in data.tags" :key="tag" class="text-[10px] font-medium px-2 py-0.5 rounded-full" :style="{ background: tag === 'vip' ? 'var(--warning-soft)' : tag === 'blacklist' ? 'var(--danger-soft)' : 'var(--bg-hover)', color: tag === 'vip' ? 'var(--warning-text)' : tag === 'blacklist' ? 'var(--danger-text)' : 'var(--text-secondary)' }">{{ tag }}</span>
        </div>
      </SkCard>

      <!-- Member Card -->
      <SkCard title="Membership" size="md" v-if="data">
        <div class="text-center space-y-3">
          <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-2xl" :style="memberBgStyle">{{ memberIcon }}</div>
          <div>
            <p class="text-lg font-extrabold" :style="{ color: memberColor }">{{ memberLabel }}</p>
            <p class="sk-caption">Member since {{ formatDate(data.member_since) }}</p>
          </div>
          <div class="flex justify-center gap-3">
            <div><p class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">{{ data.points || 0 }}</p><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Points</p></div>
            <div class="w-px" :style="{ background: 'var(--border-light)' }"></div>
            <div><p class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">{{ data.referral_count || 0 }}</p><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Referrals</p></div>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- Favorites -->
    <SkCard title="Favorit" size="md" v-if="data">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-2 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Teknisi</p><p class="text-xs font-bold mt-0.5" :style="{ color: 'var(--text-primary)' }">{{ data.favorite_technician || '-' }}</p></div>
        <div class="p-2 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Produk</p><p class="text-xs font-bold mt-0.5" :style="{ color: 'var(--text-primary)' }">{{ data.favorite_product || '-' }}</p></div>
        <div class="p-2 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Merek</p><p class="text-xs font-bold mt-0.5" :style="{ color: 'var(--text-primary)' }">{{ data.favorite_brand || '-' }}</p></div>
        <div class="p-2 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Avg Ticket</p><p class="text-xs font-bold mt-0.5" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(data.avg_ticket) }}</p></div>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({ data: { type: Object, default: () => ({}) }, workspace: { type: Object, default: null } });
const { formatNumber, formatDate } = useFormatter();

const typeLabels = { personal:'Personal', corporate:'Corporate', walkin:'Walk In' };
const typeLabel = computed(() => typeLabels[props.data?.type] || props.data?.type || '-');
const typeStyle = computed(() => {
  const t = props.data?.type;
  return t === 'corporate' ? { background:'var(--primary-soft)', color:'var(--primary)' } : { background:'var(--bg-hover)', color:'var(--text-secondary)' };
});

const memberLevels = { regular:{label:'Regular',color:'var(--text-secondary)',bg:'var(--bg-hover)',icon:'⭐'},
  silver:{label:'Silver',color:'#6B7280',bg: 'var(--bg-hover)',icon:'🥈'},
  gold:{label:'Gold',color:'#D97706',bg:'var(--warning-soft)',icon:'🥇'},
  platinum:{label:'Platinum',color:'#7C3AED',bg:'#EDE9FE',icon:'💎'} };
const memberInfo = computed(() => memberLevels[props.data?.member_level] || memberLevels.regular);
const memberLabel = computed(() => memberInfo.value.label);
const memberColor = computed(() => memberInfo.value.color);
const memberBgStyle = computed(() => ({ background: memberInfo.value.bg, color: memberInfo.value.color }));
const memberIcon = computed(() => memberInfo.value.icon);
</script>

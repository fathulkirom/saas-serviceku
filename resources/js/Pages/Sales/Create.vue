<template>
  <AuthenticatedLayout>
    <!-- Use a specialized height for the POS so it feels like an app within an app -->
    <div class="flex flex-col h-[calc(100vh-64px)] overflow-hidden sk-bg-hover">
      
      <!-- POS Header -->
      <div class="flex items-center justify-between px-6 py-4 sk-bg-card border-b sk-border">
        <div>
          <h1 class="text-xl font-bold sk-text-primary tracking-tight">Point of Sale (POS)</h1>
          <p class="text-sm sk-text-muted">Kasir Penjualan Langsung</p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('keuangan.index')"
            class="px-4 py-2 text-sm font-semibold sk-text-secondary sk-bg-card border sk-border rounded-xl hover:sk-bg-hover transition-colors"
          >
            Kembali ke Keuangan
          </Link>
        </div>
      </div>

      <!-- POS Main Content -->
      <div class="flex flex-1 overflow-hidden">
        
        <!-- LEFT PANEL: Products Grid -->
        <div class="flex-1 flex flex-col min-w-0 sk-bg-card">
          <!-- Search Bar -->
          <div class="p-6 pb-4">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <SearchIcon class="w-5 h-5 sk-text-muted" />
              </div>
              <KInput 
                type="text"
                v-model="searchQuery"
                placeholder="Cari produk (Nama atau Kategori)..."
                class="w-full pl-10 pr-4 py-3 sk-bg-hover border-transparent rounded-xl sk-text-primary placeholder-zinc-400 focus:sk-bg-card focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all sm:text-sm font-medium" />
            </div>
          </div>

          <!-- Product Grid -->
          <div class="flex-1 overflow-y-auto p-6 pt-0">
            <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64 sk-text-muted sk-bg-hover rounded-3xl border sk-border-light border-dashed">
              <div class="w-16 h-16 sk-bg-card shadow-sm rounded-full flex items-center justify-center mb-4">
                <PackageIcon class="w-8 h-8 opacity-50" />
              </div>
              <p class="text-sm font-semibold">Produk tidak ditemukan</p>
              <p class="text-xs mt-1 opacity-70">Coba kata kunci pencarian yang lain.</p>
            </div>
            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                @click="addToCart(product)"
                class="group relative flex flex-col sk-bg-card border sk-border rounded-2xl p-4 cursor-pointer hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 transform hover:-translate-y-1"
              >
                <div class="flex-1">
                  <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center rounded-lg sk-bg-primary-soft px-2.5 py-1 text-[10px] font-bold sk-text-primary-brand ring-1 ring-inset sk-bg-primary/20 uppercase tracking-wider">
                      Stok: {{ product.stock_quantity }}
                    </span>
                  </div>
                  <h3 class="text-sm font-bold sk-text-primary leading-snug mb-1.5 group-hover:sk-text-primary-brand transition-colors line-clamp-2">
                    {{ product.name }}
                  </h3>
                  <p class="text-xs font-medium sk-text-muted line-clamp-1" v-if="product.category">{{ product.category }}</p>
                </div>
                <div class="mt-4 pt-4 border-t sk-border-light flex items-center justify-between">
                  <span class="text-sm font-black sk-text-primary tracking-tight">Rp {{ formatNumber(product.selling_price || product.price || 0) }}</span>
                  <div class="w-8 h-8 rounded-full sk-bg-hover flex items-center justify-center sk-text-muted group-hover:sk-bg-primary group-hover:text-white group-hover:rotate-90 transition-all duration-300">
                    <PlusIcon class="w-4 h-4" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT PANEL: Cart & Checkout -->
        <div class="w-[420px] flex flex-col sk-bg-hover border-l sk-border z-10 shadow-2xl relative">
          <!-- Customer Selection -->
          <div class="p-5 border-b sk-border sk-bg-card shadow-sm z-10">
            <label class="block text-[10px] font-black sk-text-muted mb-2 uppercase tracking-widest">Pelanggan</label>
            <div class="relative group">
              <KSelect 
                v-model="form.customer_id"
                class="w-full pl-11 pr-4 py-3 sk-bg-card border sk-border rounded-xl text-sm font-bold text-zinc-800 hover:border-indigo-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                <option :value="null">Walk-in Customer (Umum)</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.phone ? `(${c.phone})` : '' }}</option>
              </KSelect>
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none sk-text-muted group-hover:text-indigo-500 transition-colors">
                <UserIcon class="w-5 h-5" />
              </div>
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none sk-text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-5 space-y-3 sk-bg-hover">
            <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full sk-text-muted text-center">
              <div class="w-20 h-20 sk-bg-hover rounded-full flex items-center justify-center mb-4">
                <ShoppingCartIcon class="w-10 h-10 opacity-30 sk-text-secondary" />
              </div>
              <p class="text-sm font-bold sk-text-muted">Keranjang masih kosong</p>
              <p class="text-xs mt-1 font-medium opacity-70">Pilih produk dari daftar di sebelah kiri</p>
            </div>
            
            <div
              v-for="(item, index) in cart"
              :key="index"
              class="flex sk-bg-card border sk-border rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow group"
            >
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold sk-text-primary truncate">{{ item.name }}</h4>
                <div class="text-xs font-semibold sk-text-muted mt-1">Rp {{ formatNumber(item.price) }}</div>
              </div>
              <div class="flex flex-col items-end gap-3 ml-3">
                <div class="flex items-center sk-bg-hover border sk-border rounded-lg p-1 shadow-inner">
                  <KButton  @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center rounded-md sk-text-secondary sk-bg-card shadow-sm hover:sk-text-primary-brand hover:scale-105 transition-all" :disabled="item.quantity <= 1">
                    <MinusIcon class="w-3.5 h-3.5" />
                  </KButton>
                  <span class="w-10 text-center text-sm font-black sk-text-primary">{{ item.quantity }}</span>
                  <KButton  @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center rounded-md sk-text-secondary sk-bg-card shadow-sm hover:sk-text-primary-brand hover:scale-105 transition-all">
                    <PlusIcon class="w-3.5 h-3.5" />
                  </KButton>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-sm font-black sk-text-primary-brand tracking-tight">Rp {{ formatNumber(item.price * item.quantity) }}</span>
                  <KButton  @click="removeFromCart(index)" class="w-6 h-6 flex items-center justify-center rounded-md sk-text-muted hover:bg-rose-50 hover:text-rose-600 transition-colors">
                    <TrashIcon class="w-4 h-4" />
                  </KButton>
                </div>
              </div>
            </div>
          </div>

          <!-- Checkout Summary -->
          <div class="p-6 sk-bg-card border-t sk-border shadow-[0_-4px_24px_rgba(0,0,0,0.02)]">
            <div class="space-y-3 mb-6">
              <div class="flex justify-between items-center text-sm">
                <span class="sk-text-muted font-semibold">Subtotal</span>
                <span class="font-bold sk-text-primary">Rp {{ formatNumber(subtotal) }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="sk-text-muted font-semibold">Diskon (Rp)</span>
                <KInput 
                  type="number"
                  v-model.number="form.discount"
                  class="w-32 text-right py-1.5 px-3 text-sm font-bold sk-border rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all"
                  min="0"
                  placeholder="0" />
              </div>
              <div class="pt-4 border-t sk-border-light flex justify-between items-center">
                <span class="text-base font-black sk-text-primary uppercase tracking-widest">Total</span>
                <span class="text-2xl font-black sk-text-primary-brand tracking-tight">Rp {{ formatNumber(total) }}</span>
              </div>
            </div>

            <!-- Payment Details -->
            <div class="space-y-5 mb-6">
              <div>
                <label class="block text-[10px] font-black sk-text-muted mb-2 uppercase tracking-widest">Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-3">
                  <KButton 
                    type="button"
                    @click="form.payment_method = 'cash'"
                    :class="['py-3 px-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2', form.payment_method === 'cash' ? 'border-indigo-600 sk-bg-primary-soft sk-text-primary-brand shadow-sm' : 'sk-border-light sk-bg-card sk-text-muted hover:sk-bg-hover hover:sk-border']">
                    <BanknoteIcon class="w-4 h-4" /> Tunai
                  </KButton>
                  <KButton 
                    type="button"
                    @click="form.payment_method = 'transfer'"
                    :class="['py-3 px-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2', form.payment_method === 'transfer' ? 'border-indigo-600 sk-bg-primary-soft sk-text-primary-brand shadow-sm' : 'sk-border-light sk-bg-card sk-text-muted hover:sk-bg-hover hover:sk-border']">
                    <CreditCardIcon class="w-4 h-4" /> Transfer
                  </KButton>
                </div>
              </div>
              
              <div v-if="form.payment_method === 'cash'">
                <label class="block text-[10px] font-black sk-text-muted mb-2 uppercase tracking-widest">Jumlah Dibayar (Rp)</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-4 flex items-center sk-text-muted font-black">Rp</span>
                  <KInput 
                    type="number"
                    v-model.number="form.paid_amount"
                    class="w-full pl-12 pr-4 py-3 sk-bg-hover border sk-border rounded-xl sk-text-primary font-black focus:sk-bg-card focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all text-xl"
                    min="0" />
                </div>
                <!-- Quick Money Buttons -->
                <div class="flex gap-2 mt-3 overflow-x-auto pb-1 no-scrollbar">
                  <KButton  @click="form.paid_amount = total" class="whitespace-nowrap px-4 py-2 sk-bg-primary-soft hover:sk-bg-primary-soft border sk-border-primary rounded-lg text-xs font-bold sk-text-primary-brand transition-colors shadow-sm">Uang Pas</KButton>
                  <KButton  @click="addQuickMoney(50000)" class="whitespace-nowrap px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border rounded-lg text-xs font-bold sk-text-primary transition-colors shadow-sm">+50k</KButton>
                  <KButton  @click="addQuickMoney(100000)" class="whitespace-nowrap px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border rounded-lg text-xs font-bold sk-text-primary transition-colors shadow-sm">+100k</KButton>
                </div>
              </div>

              <!-- Change (Kembalian) -->
              <div v-if="form.payment_method === 'cash' && form.paid_amount > total" class="flex justify-between items-center bg-gradient-to-r from-emerald-500 to-emerald-400 text-white p-4 rounded-xl shadow-md shadow-emerald-500/20">
                <span class="text-sm font-bold">Kembalian</span>
                <span class="font-black text-xl tracking-tight">Rp {{ formatNumber(form.paid_amount - total) }}</span>
              </div>
            </div>

            <KButton 
              @click="submitSale"
              :disabled="!canSubmit"
              :class="[
                'w-full py-4 rounded-xl text-white font-black text-lg transition-all flex justify-center items-center gap-2',
                canSubmit ? 'sk-bg-primary hover:bg-indigo-500 hover:-translate-y-1 shadow-lg shadow-indigo-500/30' : 'bg-zinc-200 cursor-not-allowed sk-text-muted'
              ]">
              <CheckCircleIcon class="w-6 h-6" v-if="!form.processing && canSubmit" />
              <Loader2Icon class="w-6 h-6 animate-spin" v-else-if="form.processing" />
              {{ form.processing ? 'Memproses...' : 'BAYAR SEKARANG' }}
            </KButton>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';

import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFormatter } from '@/Composables/useFormatter';
import { 
  Search as SearchIcon, 
  Package as PackageIcon, 
  User as UserIcon, 
  ShoppingCart as ShoppingCartIcon,
  Plus as PlusIcon,
  Minus as MinusIcon,
  Trash2 as TrashIcon,
  Banknote as BanknoteIcon,
  CreditCard as CreditCardIcon,
  CheckCircle as CheckCircleIcon,
  Loader2 as Loader2Icon
} from 'lucide-vue-next';

const { formatNumber } = useFormatter();

const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  customers: {
    type: Array,
    required: true,
  }
});

const searchQuery = ref('');
const cart = ref([]);

const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products;
  const q = searchQuery.value.toLowerCase();
  return props.products.filter(p => 
    p.name.toLowerCase().includes(q) || 
    (p.category && p.category.toLowerCase().includes(q))
  );
});

const form = useForm({
  customer_id: null,
  sale_type: 'langsung',
  items: [],
  discount: 0,
  payment_method: 'cash',
  paid_amount: 0,
});

const addToCart = (product) => {
  const existing = cart.value.find(i => i.product_id === product.id);
  if (existing) {
    if (existing.quantity < product.stock_quantity) {
      existing.quantity++;
    }
  } else {
    cart.value.push({
      product_id: product.id,
      name: product.name,
      price: product.selling_price || product.price || 0,
      quantity: 1,
      item_type: 'sparepart', // default to sparepart/aksesoris based on logic
    });
  }
};

const updateQty = (index, delta) => {
  const item = cart.value[index];
  const newQty = item.quantity + delta;
  
  // Find original product to check stock limit
  const product = props.products.find(p => p.id === item.product_id);
  
  if (newQty > 0 && (!product || newQty <= product.stock_quantity)) {
    item.quantity = newQty;
  }
};

const removeFromCart = (index) => {
  cart.value.splice(index, 1);
};

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => {
  return Math.max(0, subtotal.value - (form.discount || 0));
});

const addQuickMoney = (amount) => {
  const current = Number(form.paid_amount) || 0;
  form.paid_amount = current + amount;
};

// Auto update paid_amount when total changes if it was previously matching
watch(total, (newTotal, oldTotal) => {
  if (form.paid_amount === oldTotal || form.paid_amount === 0) {
    form.paid_amount = newTotal;
  }
});

const canSubmit = computed(() => {
  return cart.value.length > 0 && form.paid_amount >= total.value && !form.processing;
});

const submitSale = () => {
  if (!canSubmit.value) return;

  // Prepare items for payload
  form.items = cart.value.map(item => ({
    product_id: item.product_id,
    item_type: 'sparepart', // Since we are selling products, they are considered sparepart or aksesoris
    quantity: item.quantity,
    price: item.price,
    description: item.name
  }));

  form.post(route('sales.store'), {
    preserveScroll: true,
    onSuccess: () => {
      cart.value = [];
      form.reset();
      form.paid_amount = 0;
      form.discount = 0;
    }
  });
};
</script>

<style scoped>
/* Hide scrollbar for quick money buttons */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

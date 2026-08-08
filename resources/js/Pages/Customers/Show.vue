<template>
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <!-- Header CRM Profile -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 sk-bg-primary-soft rounded-full flex items-center justify-center text-xl font-bold sk-text-primary-brand ring-4 sk-bg-card shadow-sm">
                        {{ getInitials(customer.name) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold sk-text-primary tracking-tight">{{ customer.name }}</h2>
                            <span v-if="customer.is_member" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold sk-bg-success-soft sk-text-success border sk-border-primary">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Member
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                :class="risk.level === 'high' ? 'sk-bg-danger-soft sk-text-danger sk-border-primary' : risk.level === 'medium' ? 'sk-bg-warning-soft sk-text-warning sk-border-primary' : 'sk-bg-success-soft sk-text-success sk-border-primary'">
                                {{ risk.icon }} {{ risk.label }}
                            </span>
                        </div>
                        <p class="text-sm sk-text-muted font-medium mt-1">Pelanggan sejak {{ formatDate(customer.created_at) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('customers.index')" class="px-4 py-2 sk-bg-card border sk-border rounded-xl sk-text-primary text-sm font-semibold hover:sk-bg-hover transition-colors shadow-sm">
                        ← Kembali
                    </Link>
                    <Link :href="route('customers.create')" class="px-4 py-2 sk-bg-primary text-white rounded-xl text-sm font-semibold hover:sk-bg-primary transition-colors shadow-sm">
                        Edit
                    </Link>
                </div>
            </div>

            <!-- Sprint 7.3: Stats Quick View -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm text-center">
                    <p class="text-xs sk-text-muted">Total Spending</p>
                    <p class="text-lg font-bold sk-text-primary-brand">Rp {{ formatNumber(props.stats.total_spending || 0) }}</p>
                </div>
                <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm text-center">
                    <p class="text-xs sk-text-muted">Servis</p>
                    <p class="text-lg font-bold sk-text-primary">{{ props.stats.service_count || 0 }}</p>
                </div>
                <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm text-center">
                    <p class="text-xs sk-text-muted">Pembelian</p>
                    <p class="text-lg font-bold sk-text-primary">{{ props.stats.sales_count || 0 }}</p>
                </div>
                <div class="sk-bg-card p-4 rounded-2xl border sk-border shadow-sm text-center">
                    <p class="text-xs sk-text-muted">Perangkat</p>
                    <p class="text-lg font-bold sk-text-primary">{{ props.stats.device_count || 0 }}</p>
                </div>
            </div>

            <!-- Sprint 7.3: Tabs -->
            <div class="flex border-b sk-border mb-6 overflow-x-auto">
                <button v-for="tab in [
                    {key:'overview',label:'Overview',icon:'👤'},
                    {key:'timeline',label:'Timeline',icon:'📅',count:timeline.length},
                    {key:'devices',label:'Perangkat',icon:'💻',count:devices.length},
                    {key:'communication',label:'Komunikasi',icon:'💬',count:communications.length},
                    {key:'notes',label:'Catatan',icon:'📝',count:notes.length},
                    {key:'complaints',label:'Komplain',icon:'🚨',count:complaints.length},
                ]" :key="tab.key" @click="activeTab = tab.key"
                    class="px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors flex items-center gap-1.5"
                    :class="activeTab === tab.key ? 'border-indigo-600 sk-text-primary-brand' : 'border-transparent sk-text-muted hover:sk-text-primary'">
                    {{ tab.icon }} {{ tab.label }}
                    <span v-if="tab.count !== undefined" class="text-xs sk-bg-hover px-1.5 py-0.5 rounded-full">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Sprint 7.3: Unified Timeline Tab -->
            <div v-if="activeTab === 'timeline'" class="mb-8">
                <div v-if="timeline.length === 0" class="text-center py-12 sk-text-muted">Belum ada aktivitas.</div>
                <div v-for="item in timeline" :key="`${item.type}-${item.id}`" class="flex gap-3 pl-4 border-l-2 pb-5 last:pb-0"
                    :class="{'border-blue-400': item.type === 'request', 'border-amber-400': item.type === 'service', 'border-green-400': item.type === 'sale'}">
                    <div class="text-lg shrink-0 mt-0.5">{{ {request:'📋',service:'🔧',sale:'🛒'}[item.type] || '📌' }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium sk-text-primary">{{ item.title }}</div>
                        <div v-if="item.description" class="text-xs sk-text-muted mt-0.5 line-clamp-2">{{ item.description }}</div>
                        <div class="text-xs sk-text-muted mt-1">{{ formatDateTime(item.created_at) }}</div>
                    </div>
                    <KBadge v-if="item.status" size="xs" :class="statusClass(item.status)">{{ item.status }}</KBadge>
                </div>
            </div>

            <!-- Sprint 7.3: Devices Tab -->
            <div v-if="activeTab === 'devices'" class="mb-8">
                <div v-if="devices.length === 0" class="text-center py-12 sk-text-muted">
                    <p class="font-medium">Belum ada perangkat terdaftar.</p>
                    <p class="text-sm mt-1">Perangkat ditambahkan saat membuat Request servis.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="d in devices" :key="d.id" class="sk-bg-card rounded-xl border sk-border p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold sk-text-primary">{{ d.brand }} {{ d.model }}</h4>
                            <span class="text-xs px-2 py-0.5 rounded-full" :class="d.status === 'active' ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-hover sk-text-muted'">{{ d.status }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                            <span class="sk-text-muted">Type:</span><span>{{ d.type || '-' }}</span>
                            <span class="sk-text-muted">IMEI:</span><span class="font-mono">{{ d.imei || '-' }}</span>
                            <span class="sk-text-muted">S/N:</span><span class="font-mono">{{ d.serial_number || '-' }}</span>
                            <span class="sk-text-muted">Garansi:</span><span>{{ d.warranty_until || '-' }}</span>
                            <span class="sk-text-muted">Servis:</span><span>{{ d.repair_count || 0 }}x</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sprint 7.3C: Communication Tab -->
            <div v-if="activeTab === 'communication'" class="mb-8">
                <!-- Send Form -->
                <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-6 mb-6">
                    <h3 class="font-bold sk-text-primary mb-4">Kirim Pesan</h3>
                    <form @submit.prevent="sendMessage" class="space-y-3">
                        <div class="flex gap-2">
                            <select v-model="commForm.type" class="px-3 py-2 border sk-border rounded-lg text-sm">
                                <option value="whatsapp">📱 WhatsApp</option>
                                <option value="email">📧 Email</option>
                            </select>
                            <select v-model="commForm.template_id" @change="applyTemplate" class="px-3 py-2 border sk-border rounded-lg text-sm flex-1">
                                <option value="">-- Pilih Template --</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }} ({{ t.channel }})</option>
                            </select>
                        </div>
                        <textarea v-model="commForm.message" rows="3" class="w-full px-3 py-2 border sk-border rounded-lg text-sm" placeholder="Tulis pesan... Gunakan {{customer_name}} untuk nama customer." required></textarea>
                        <div class="flex justify-between items-center">
                            <span class="text-xs sk-text-muted">
                                <template v-if="commForm.type === 'whatsapp'">Ke: {{ customer.phone || 'Tidak ada nomor' }}</template>
                                <template v-else>Ke: {{ customer.email || 'Tidak ada email' }}</template>
                            </span>
                            <button type="submit" :disabled="sending" class="px-4 py-2 sk-bg-primary text-white rounded-lg text-sm font-semibold hover:sk-bg-primary disabled:opacity-50">
                                {{ sending ? 'Mengirim...' : 'Kirim' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- History -->
                <div v-if="communications.length === 0" class="text-center py-12 sk-text-muted">
                    <p class="font-medium">Belum ada riwayat komunikasi.</p>
                </div>
                <div v-for="c in communications" :key="c.id" class="sk-bg-card rounded-xl border sk-border p-4 mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ c.type === 'whatsapp' ? '💬' : '📧' }}</span>
                            <span class="font-medium sk-text-primary text-sm">{{ c.type === 'whatsapp' ? 'WhatsApp' : 'Email' }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                :class="c.status === 'sent' ? 'sk-bg-success-soft sk-text-success' : c.status === 'failed' ? 'sk-bg-danger-soft sk-text-danger' : 'sk-bg-hover sk-text-muted'">
                                {{ c.status }}
                            </span>
                        </div>
                        <span class="text-xs sk-text-muted">{{ formatDateTime(c.created_at) }}</span>
                    </div>
                    <p class="text-sm sk-text-secondary mt-1 line-clamp-3">{{ c.message }}</p>
                    <div class="text-xs sk-text-muted mt-1">Ke: {{ c.recipient }} · {{ c.direction === 'outbound' ? 'Keluar' : 'Masuk' }}</div>
                </div>
            </div>

            <!-- Sprint 7.3D: Notes Tab -->
            <div v-if="activeTab === 'notes'" class="mb-8">
                <form @submit.prevent="addNote" class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 mb-4 space-y-3">
                    <div class="flex gap-2">
                        <select v-model="noteForm.type" class="px-3 py-2 border sk-border rounded-lg text-sm">
                            <option v-for="(label, key) in noteTypes" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <select v-model="noteForm.priority" class="px-3 py-2 border sk-border rounded-lg text-sm">
                            <option value="low">🟢 Low</option><option value="medium">🟡 Medium</option><option value="high">🔴 High</option>
                        </select>
                    </div>
                    <input v-model="noteForm.title" class="w-full px-3 py-2 border sk-border rounded-lg text-sm" placeholder="Judul (opsional)" />
                    <textarea v-model="noteForm.note" rows="2" class="w-full px-3 py-2 border sk-border rounded-lg text-sm" placeholder="Tulis catatan..." required></textarea>
                    <div class="flex justify-end"><button type="submit" class="px-4 py-2 sk-bg-primary text-white rounded-lg text-sm font-semibold">Simpan Catatan</button></div>
                </form>
                <div v-if="notes.length === 0" class="text-center py-8 sk-text-muted">Belum ada catatan.</div>
                <div v-for="n in notes" :key="n.id" class="sk-bg-card rounded-xl border p-3 mb-2"
                    :class="n.priority === 'high' ? 'sk-border-primary sk-bg-danger-soft/30' : n.type === 'warning' ? 'sk-border-primary sk-bg-warning-soft/30' : 'sk-border'">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full sk-bg-hover">{{ noteTypes[n.type] || n.type }}</span>
                            <span class="text-xs sk-text-muted">{{ formatDateTime(n.created_at) }}</span>
                        </div>
                        <span v-if="n.creator" class="text-xs sk-text-muted">{{ n.creator.name }}</span>
                    </div>
                    <p v-if="n.title" class="text-sm font-medium sk-text-primary">{{ n.title }}</p>
                    <p class="text-sm sk-text-secondary mt-0.5">{{ n.note }}</p>
                </div>
            </div>

            <!-- Sprint 7.3D: Complaints Tab -->
            <div v-if="activeTab === 'complaints'" class="mb-8">
                <form @submit.prevent="addComplaint" class="sk-bg-card rounded-2xl border sk-border shadow-sm p-4 mb-4 space-y-3">
                    <div class="flex gap-2">
                        <input v-model="complaintForm.title" class="flex-1 px-3 py-2 border sk-border rounded-lg text-sm" placeholder="Judul komplain" required />
                        <select v-model="complaintForm.priority" class="px-3 py-2 border sk-border rounded-lg text-sm">
                            <option value="low">🟢 Low</option><option value="medium">🟡 Medium</option><option value="high">🔴 High</option>
                        </select>
                    </div>
                    <textarea v-model="complaintForm.description" rows="2" class="w-full px-3 py-2 border sk-border rounded-lg text-sm" placeholder="Deskripsi..."></textarea>
                    <div class="flex justify-end"><button type="submit" class="px-4 py-2 sk-bg-danger text-white rounded-lg text-sm font-semibold">Catat Komplain</button></div>
                </form>
                <div v-if="complaints.length === 0" class="text-center py-8 sk-text-muted">Tidak ada komplain — customer baik! 🎉</div>
                <div v-for="c in complaints" :key="c.id" class="sk-bg-card rounded-xl border p-3 mb-2"
                    :class="c.status === 'open' ? 'sk-border-primary sk-bg-danger-soft/30' : c.status === 'resolved' ? 'sk-border-primary sk-bg-success-soft/30' : 'sk-border'">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full" :class="c.status === 'open' ? 'sk-bg-danger-soft sk-text-danger' : 'sk-bg-success-soft sk-text-success'">{{ complaintStatuses[c.status] || c.status }}</span>
                            <span class="text-xs sk-text-muted">{{ formatDateTime(c.created_at) }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-medium sk-text-primary">{{ c.title }}</p>
                    <p v-if="c.description" class="text-xs sk-text-muted mt-0.5">{{ c.description }}</p>
                    <div v-if="c.resolution" class="mt-2 text-xs sk-bg-success-soft border sk-border-primary rounded p-2 sk-text-success">✅ {{ c.resolution }}</div>
                    <button v-if="c.status !== 'resolved'" @click="resolveComplaint(c)" class="mt-2 text-xs sk-text-primary-brand hover:underline">Resolve</button>
                </div>
            </div>

            <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KIRI: Informasi Profil -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-6">
                        <h3 class="text-sm font-bold sk-text-primary mb-5 flex items-center gap-2">
                            <svg class="w-4 h-4 sk-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Informasi Kontak
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-1">Telepon</p>
                                <p class="text-sm font-medium sk-text-primary flex items-center gap-2">
                                    {{ customer.phone || '-' }}
                                    <a v-if="customer.phone" :href="'https://wa.me/' + cleanPhone(customer.phone)" target="_blank" class="sk-text-success hover:sk-text-success">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-1">Email</p>
                                <p class="text-sm font-medium sk-text-primary">{{ customer.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold sk-text-muted uppercase tracking-wider mb-1">Alamat</p>
                                <p class="text-sm font-medium sk-text-primary leading-relaxed">{{ customer.address || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Member Status Card -->
                    <div v-if="customer.is_member" class="bg-gradient-to-br from-zinc-900 to-zinc-800 rounded-2xl border border-zinc-700 shadow-lg p-6 relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-32 h-32 sk-bg-card/10 rounded-full blur-2xl"></div>
                        <h3 class="text-sm font-semibold sk-text-muted mb-6 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Member Aktif
                        </h3>
                        <div class="mb-4">
                            <p class="text-xs sk-text-muted uppercase tracking-wider mb-1">Nomor Kartu</p>
                            <p class="text-lg font-mono font-bold text-white tracking-widest">{{ customer.card_number || 'ACS' + customer.id }}</p>
                        </div>
                        <div class="flex justify-between items-end border-t border-zinc-700/50 pt-4 mt-2">
                            <div>
                                <p class="text-xs sk-text-muted mb-1">Total Poin</p>
                                <p class="text-xl font-bold text-emerald-400">{{ customer.points || 0 }} <span class="text-sm font-medium sk-text-success/70">pts</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Timeline / Riwayat -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Stats Quick View -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm">
                            <p class="text-sm font-semibold sk-text-muted mb-1">Total Tiket Servis</p>
                            <h3 class="text-2xl font-bold sk-text-primary">{{ customer.services?.length || 0 }}</h3>
                        </div>
                        <div class="sk-bg-card p-5 rounded-2xl border sk-border shadow-sm">
                            <p class="text-sm font-semibold sk-text-muted mb-1">Total Belanja (Penjualan)</p>
                            <h3 class="text-2xl font-bold sk-text-primary-brand">Rp {{ formatNumber(totalBelanja) }}</h3>
                        </div>
                    </div>

                    <!-- Riwayat Servis -->
                    <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b sk-border sk-bg-hover">
                            <h3 class="font-bold sk-text-primary">Riwayat Servis</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="customer.services?.length > 0" class="divide-y sk-border-light">
                                <div v-for="s in customer.services" :key="s.id" class="p-5 hover:sk-bg-hover transition-colors flex items-center justify-between group">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl sk-bg-info-soft sk-text-info flex items-center justify-center border border-blue-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <div>
                                            <Link :href="route('services.show', s.id)" class="text-sm font-bold sk-text-primary hover:sk-text-primary-brand transition-colors">Tiket #{{ s.id }}</Link>
                                            <p class="text-xs sk-text-muted mt-0.5">{{ formatDateTime(s.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg border" :class="statusClass(s.status)">
                                            {{ s.status }}
                                        </span>
                                        <Link :href="route('services.show', s.id)" class="opacity-0 group-hover:opacity-100 px-3 py-1.5 sk-bg-card border sk-border rounded-lg text-xs font-semibold sk-text-primary hover:sk-text-primary-brand hover:sk-border-primary transition-all">
                                            Lihat
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-10 text-center sk-text-muted">
                                <svg class="w-12 h-12 mx-auto sk-text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="text-sm font-medium">Belum ada riwayat servis.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Penjualan -->
                    <div class="sk-bg-card rounded-2xl border sk-border shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b sk-border sk-bg-hover">
                            <h3 class="font-bold sk-text-primary">Riwayat Pembelian POS</h3>
                        </div>
                        <div class="p-0">
                            <div v-if="customer.sales?.length > 0" class="divide-y sk-border-light">
                                <div v-for="s in customer.sales" :key="s.id" class="p-5 hover:sk-bg-hover transition-colors flex items-center justify-between group">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl sk-bg-success-soft sk-text-success flex items-center justify-center border border-emerald-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <div>
                                            <Link :href="route('sales.show', s.id)" class="text-sm font-bold sk-text-primary hover:sk-text-primary-brand transition-colors">Invoice #{{ s.id }}</Link>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs sk-text-muted">{{ formatDateTime(s.created_at) }}</span>
                                                <span class="w-1 h-1 rounded-full sk-border"></span>
                                                <span class="text-xs font-medium sk-text-secondary capitalize">{{ s.sale_type }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <p class="text-sm font-bold sk-text-primary">Rp {{ formatNumber(s.total) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-10 text-center sk-text-muted">
                                <svg class="w-12 h-12 mx-auto sk-text-muted mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-sm font-medium">Belum ada riwayat pembelian POS.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KBadge from '@/Components/KBadge.vue';

const props = defineProps({
    customer: { type: Object, required: true },
    timeline: { type: Array, default: () => [] },
    devices: { type: Array, default: () => [] },
    communications: { type: Array, default: () => [] },
    notes: { type: Array, default: () => [] },
    complaints: { type: Array, default: () => [] },
    risk: { type: Object, default: () => ({ level: 'low', label: 'Normal', icon: '🟢', factors: [] }) },
    noteTypes: { type: Object, default: () => ({}) },
    complaintStatuses: { type: Object, default: () => ({}) },
    templates: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const activeTab = ref('overview')
const sending = ref(false)
const commForm = ref({ type: 'whatsapp', message: '', template_id: '' })

function applyTemplate() {
    const t = props.templates.find(t => t.id == commForm.value.template_id)
    if (t) {
        commForm.value.message = t.body
            .replace('{{customer_name}}', props.customer.name)
            .replace('{{device}}', props.devices[0]?.brand + ' ' + props.devices[0]?.model || 'unit Anda')
            .replace('{{service_number}}', props.customer.services?.[0]?.id || '-')
            .replace('{{amount}}', '...')
            .replace('{{warranty_date}}', props.devices[0]?.warranty_until || '-')
    }
}

function sendMessage() {
    sending.value = true
    router.post(route('customers.communications.send', props.customer.id), {
        type: commForm.value.type,
        message: commForm.value.message,
        template_id: commForm.value.template_id || null,
    }, {
        preserveScroll: true,
        onSuccess: () => { commForm.value.message = ''; commForm.value.template_id = ''; sending.value = false },
        onError: () => { sending.value = false },
    })
}

// Sprint 7.3D: Notes
const noteForm = ref({ type: 'general', title: '', note: '', priority: 'medium' })
function addNote() {
    router.post(route('customers.notes.store', props.customer.id), noteForm.value, {
        preserveScroll: true,
        onSuccess: () => { noteForm.value.note = ''; noteForm.value.title = '' },
    })
}

// Sprint 7.3D: Complaints
const complaintForm = ref({ title: '', description: '', priority: 'medium' })
function addComplaint() {
    router.post(route('customers.complaints.store', props.customer.id), complaintForm.value, {
        preserveScroll: true,
        onSuccess: () => { complaintForm.value = { title: '', description: '', priority: 'medium' } },
    })
}
function resolveComplaint(c) {
    const resolution = prompt('Resolusi untuk komplain ini:')
    if (!resolution) return
    router.post(route('customers.complaints.resolve', { customer: props.customer.id, complaint: c.id }), { resolution }, { preserveScroll: true })
}

const totalBelanja = computed(() => {
    if (!props.customer.sales) return 0;
    return props.customer.sales.reduce((sum, s) => sum + Number(s.total), 0);
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const cleanPhone = (phone) => {
    if (!phone) return '';
    let cleaned = phone.replace(/\D/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    return cleaned;
};

const statusClass = (s) => {
    const map = {
        'menunggu_alokasi': 'sk-bg-warning-soft sk-text-warning sk-border-primary',
        'dikerjakan': 'sk-bg-info-soft sk-text-info border-blue-200',
        'selesai': 'sk-bg-success-soft sk-text-success sk-border-primary',
        'batal': 'sk-bg-danger-soft sk-text-danger sk-border-primary',
        'siap_diambil': 'sk-bg-primary-soft sk-text-primary-brand sk-border-primary',
    };
    return map[s] || 'sk-bg-hover sk-text-primary sk-border';
};
</script>

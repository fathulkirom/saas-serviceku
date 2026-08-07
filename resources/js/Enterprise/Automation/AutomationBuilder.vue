<template>
  <div class="space-y-5">
    <!-- ═══════════ HEADER ═══════════ -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="sk-heading-4">Automation Builder</h1>
        <p class="sk-caption mt-1">Bangun aturan otomatisasi untuk modul Anda.</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="$emit('save')" class="px-4 py-2 text-sm font-semibold rounded-xl text-white shadow-sm transition-all"
          :style="{ background: 'var(--primary)' }">
          💾 Simpan Automation
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

      <!-- ═══════════ LEFT: CANVAS ═══════════ -->
      <div class="lg:col-span-2 space-y-5">

        <!-- 1. TRIGGER -->
        <SkCard title="1️⃣ Trigger — Kapan automation ini berjalan?" size="md">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button
              v-for="trigger in visibleTriggers"
              :key="trigger.value"
              @click="rule.trigger = trigger.value"
              class="p-3 rounded-xl border text-center transition-all text-xs"
              :class="rule.trigger === trigger.value ? 'ring-2' : ''"
              :style="rule.trigger === trigger.value
                ? { borderColor: 'var(--primary)', background: 'var(--primary-soft)', color: 'var(--primary)' }
                : { borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }"
            >
              <div class="text-xl mb-1">{{ trigger.icon }}</div>
              <div class="font-semibold">{{ trigger.label }}</div>
            </button>
          </div>
        </SkCard>

        <!-- 2. CONDITIONS -->
        <SkCard title="2️⃣ Conditions — Kapan action dijalankan?" size="md">
          <div v-if="!rule.conditions.length" class="text-center py-4">
            <button @click="addCondition()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border"
              :style="{ borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }">
              ＋ Tambah Kondisi
            </button>
            <p class="sk-caption mt-2">Kosongkan untuk selalu jalan.</p>
          </div>

          <div v-else class="space-y-2">
            <div v-for="(cond, i) in rule.conditions" :key="i"
              class="flex items-center gap-2 p-3 rounded-xl border"
              :style="{ borderColor: 'var(--border-light)' }">

              <!-- AND/OR -->
              <select v-if="i > 0" v-model="cond.logicGate" class="w-16 px-1.5 py-1 text-xs rounded-lg border"
                :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
              </select>
              <span v-else class="w-16 text-center text-[10px] font-bold uppercase" :style="{ color: 'var(--text-muted)' }">WHERE</span>

              <!-- Field -->
              <input v-model="cond.field" placeholder="Field" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border outline-none"
                :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" />

              <!-- Operator -->
              <select v-model="cond.operator" class="px-2 py-1.5 text-xs rounded-lg border"
                :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                <option v-for="c in allConditions" :key="c.value" :value="c.value">{{ c.label }}</option>
              </select>

              <!-- Value -->
              <input v-model="cond.value" placeholder="Value" class="w-28 px-2.5 py-1.5 text-xs rounded-lg border outline-none"
                :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" />

              <!-- Remove -->
              <button @click="rule.conditions.splice(i, 1)" class="w-6 h-6 rounded flex items-center justify-center"
                :style="{ color: 'var(--danger)' }">×</button>
            </div>
            <button @click="addCondition()" class="text-xs font-semibold" :style="{ color: 'var(--primary)' }">＋ Tambah Kondisi</button>
          </div>
        </SkCard>

        <!-- 3. ACTIONS -->
        <SkCard title="3️⃣ Actions — Apa yang harus dilakukan?" size="md">
          <div class="space-y-3">
            <div v-for="(step, i) in rule.steps" :key="i"
              class="flex items-start gap-3 p-3 rounded-xl border"
              :style="{ borderColor: 'var(--border-light)', background: 'var(--bg-card)' }">

              <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                :style="{ background: 'var(--primary-soft)', color: 'var(--primary)' }">
                {{ i + 1 }}
              </div>

              <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <!-- Action Selector -->
                  <select v-model="step.action" class="px-2.5 py-1.5 text-xs rounded-lg border flex-1"
                    :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }">
                    <option value="">Pilih Action...</option>
                    <optgroup v-for="(group, cat) in groupedActions" :key="cat" :label="cat">
                      <option v-for="a in group" :key="a.value" :value="a.value">{{ a.icon }} {{ a.label }}</option>
                    </optgroup>
                  </select>

                  <!-- Delay -->
                  <div class="flex items-center gap-1 text-xs" :style="{ color: 'var(--text-muted)' }">
                    <span>Delay:</span>
                    <input v-model.number="step.delaySeconds" type="number" min="0" placeholder="0"
                      class="w-14 px-1.5 py-1 text-xs rounded-lg border text-center"
                      :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }" />
                    <span>detik</span>
                  </div>

                  <!-- Continue on error -->
                  <label class="flex items-center gap-1 text-xs cursor-pointer" :style="{ color: 'var(--text-muted)' }">
                    <input type="checkbox" v-model="step.continueOnError" class="rounded" />
                    Lanjut jika error
                  </label>
                </div>

                <!-- Config (JSON) -->
                <textarea v-model="step.configJson" rows="2" placeholder='{"message": "Hello {{subject.name}}" }'
                  class="w-full px-2.5 py-1.5 text-xs rounded-lg border font-mono outline-none"
                  :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-input)' }"></textarea>
              </div>

              <button @click="rule.steps.splice(i, 1)" class="w-6 h-6 rounded flex items-center justify-center flex-shrink-0"
                :style="{ color: 'var(--danger)' }">×</button>
            </div>

            <button @click="addStep()" class="w-full py-2.5 text-sm font-semibold rounded-xl border-2 border-dashed transition-colors"
              :style="{ borderColor: 'var(--border-color)', color: 'var(--text-muted)' }">
              ＋ Tambah Action Step
            </button>
          </div>
        </SkCard>
      </div>

      <!-- ═══════════ RIGHT: INFO PANEL ═══════════ -->
      <div class="space-y-4">
        <SkCard title="Ringkasan" size="sm">
          <div class="space-y-2 text-xs">
            <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Trigger</span><span class="font-semibold">{{ triggerLabel }}</span></div>
            <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Conditions</span><span class="font-semibold">{{ rule.conditions.length || 'Selalu' }}</span></div>
            <div class="flex justify-between"><span :style="{ color: 'var(--text-muted)' }">Actions</span><span class="font-semibold">{{ rule.steps.length }}</span></div>
          </div>
        </SkCard>

        <SkCard title="Execution History" size="sm">
          <div v-if="!history.length" class="py-4 text-center">
            <p class="sk-caption">Belum ada data eksekusi.</p>
          </div>
          <div v-else class="space-y-2 max-h-[300px] overflow-y-auto">
            <div v-for="(h, i) in history" :key="i"
              class="flex items-center gap-2 text-xs p-2 rounded-lg"
              :style="{ background: h.success ? 'var(--success-soft)' : 'var(--danger-soft)' }">
              <span>{{ h.success ? '✅' : '❌' }}</span>
              <span class="flex-1 truncate">{{ h.message }}</span>
              <span :style="{ color: 'var(--text-muted)' }">{{ h.time }}</span>
            </div>
          </div>
        </SkCard>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
import { automationRegistry } from '@/Enterprise/Automation/AutomationRegistry.js';

defineEmits(['save']);

const rule = reactive({
  trigger: '',
  conditions: [],
  steps: [],
});

const history = ref([]);

const allTriggers = computed(() => automationRegistry.allTriggers());
const allConditions = computed(() => automationRegistry.allConditions());
const allActions = computed(() => automationRegistry.allActions());

const visibleTriggers = computed(() => allTriggers.value.slice(0, 12));
const groupedActions = computed(() => {
  const groups = {};
  allActions.value.forEach(a => {
    const cat = a.category || 'General';
    if (!groups[cat]) groups[cat] = [];
    groups[cat].push(a);
  });
  return groups;
});

const triggerLabel = computed(() => {
  const t = allTriggers.value.find(t => t.value === rule.trigger);
  return t ? `${t.icon} ${t.label}` : 'Belum dipilih';
});

function addCondition() {
  rule.conditions.push({ field: '', operator: 'eq', value: '', logicGate: 'AND' });
}

function addStep() {
  rule.steps.push({ action: '', configJson: '{}', delaySeconds: 0, continueOnError: false });
}
</script>

<template>
  <div class="rounded-2xl bg-slate-900/90 border border-purple-500/30 p-5 shadow-xl shadow-purple-950/20 backdrop-blur-xl">
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <span class="text-lg">🔮</span>
        <div>
          <h3 class="text-sm font-bold text-white tracking-wide">"What-If" Counterfactual Sandbox</h3>
          <p class="text-[11px] text-slate-400">Tweak parameters to project divergence from Age {{ currentAge }}.</p>
        </div>
      </div>
      <span class="px-2 py-0.5 rounded-full bg-purple-950 border border-purple-800/80 text-[10px] font-mono text-purple-300 font-bold">
        Live Dynamic Engine
      </span>
    </div>

    <!-- Interactive Sliders / Toggles -->
    <div class="space-y-4 text-xs">
      <!-- 1. Current Assets / Net Worth Input & Quick Range -->
      <div class="p-3 rounded-xl bg-slate-950/80 border border-emerald-500/20">
        <div class="flex items-center justify-between text-slate-300 mb-1.5">
          <label class="font-bold text-emerald-400 flex items-center gap-1.5">
            <span>💎</span>
            <span>Current Assets / Savings Valuation</span>
          </label>
          <span class="font-mono text-emerald-300 font-extrabold text-sm">{{ formatDisplay(currentSavings) }}</span>
        </div>

        <div class="relative mt-1">
          <input
            v-model.number="currentSavings"
            type="number"
            step="any"
            min="0"
            placeholder="Enter asset amount"
            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white font-mono font-bold focus:outline-none focus:border-emerald-500 text-xs"
          />
        </div>

        <!-- Slider for quick asset adjustments -->
        <input
          v-model.number="currentSavings"
          type="range"
          :min="savingsMin"
          :max="savingsMax"
          :step="savingsStep"
          class="w-full accent-emerald-500 bg-slate-900 rounded-lg cursor-pointer mt-2"
        />
        <div class="flex justify-between text-[10px] text-slate-500 font-mono mt-0.5">
          <span>{{ formatDisplay(savingsMin) }}</span>
          <span>{{ formatDisplay(Math.round((savingsMin + savingsMax) / 2)) }}</span>
          <span>{{ formatDisplay(savingsMax) }}</span>
        </div>
      </div>

      <!-- 2. Monthly Investment Slider -->
      <div>
        <div class="flex items-center justify-between text-slate-300 mb-1">
          <span class="font-medium">Monthly Investment Target (/mo)</span>
          <span class="font-mono text-amber-400 font-bold">{{ formatDisplay(monthlyInvestment) }} / mo</span>
        </div>
        <input
          v-model.number="monthlyInvestment"
          type="range"
          :min="investmentMin"
          :max="investmentMax"
          :step="investmentStep"
          class="w-full accent-amber-500 bg-slate-950 rounded-lg cursor-pointer"
        />
        <div class="flex justify-between text-[10px] text-slate-500 font-mono mt-0.5">
          <span>{{ formatDisplay(investmentMin) }}</span>
          <span>{{ formatDisplay(Math.round((investmentMin + investmentMax)/2)) }}</span>
          <span>{{ formatDisplay(investmentMax) }}</span>
        </div>
      </div>

      <!-- 3. Target Retirement Age Slider -->
      <div>
        <div class="flex items-center justify-between text-slate-300 mb-1">
          <span class="font-medium">Target Retirement Age</span>
          <span class="font-mono text-purple-400 font-bold">Age {{ retirementAge }}</span>
        </div>
        <input
          v-model.number="retirementAge"
          type="range"
          min="40"
          max="70"
          step="1"
          class="w-full accent-purple-500 bg-slate-950 rounded-lg cursor-pointer"
        />
        <div class="flex justify-between text-[10px] text-slate-500 font-mono mt-0.5">
          <span>Age 40 (Super FIRE)</span>
          <span>Age 55</span>
          <span>Age 70</span>
        </div>
      </div>

      <!-- Quick Scenario Comparison Buttons -->
      <div class="pt-3 border-t border-slate-800/80">
        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2 font-mono">
          ⚡ Quick Scenario Simulations
        </label>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            @click="applyScenario('fire')"
            class="p-2 rounded-xl bg-slate-950 hover:bg-slate-800/80 border border-slate-800 hover:border-amber-500/50 text-left transition"
          >
            <div class="font-bold text-amber-300 text-[11px]">🔥 Aggressive FIRE</div>
            <div class="text-[10px] text-slate-400">High Assets · Retire @ 45</div>
          </button>

          <button
            type="button"
            @click="applyScenario('quit_smoking')"
            class="p-2 rounded-xl bg-slate-950 hover:bg-slate-800/80 border border-slate-800 hover:border-emerald-500/50 text-left transition"
          >
            <div class="font-bold text-emerald-300 text-[11px]">🚭 Zero Smoking</div>
            <div class="text-[10px] text-slate-400">+9.5 yrs life expectancy</div>
          </button>
        </div>
      </div>

      <!-- Apply & Recalculate Button -->
      <button
        type="button"
        @click="updateBaseline"
        :disabled="isUpdating"
        class="w-full mt-2 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-purple-600/30 transition flex items-center justify-center gap-2"
      >
        <span>{{ isUpdating ? 'Recalculating 120-Year Vector...' : 'Apply & Update Trajectory' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { formatMoney } from '@/Utils/currency';

const props = defineProps({
  currentAge: {
    type: Number,
    default: 25.0,
  },
  initialSavings: {
    type: Number,
    default: 18500,
  },
  initialInvestment: {
    type: Number,
    default: 650,
  },
  initialRetirementAge: {
    type: Number,
    default: 60,
  },
  currency: {
    type: String,
    default: 'USD',
  },
});

const emit = defineEmits(['recalculated']);

const isBDT = computed(() => props.currency === 'BDT');
const isINR = computed(() => props.currency === 'INR');

// Dynamic ranges scaled for currency
const savingsMin = computed(() => isBDT.value ? 50000 : (isINR.value ? 40000 : 1000));
const savingsMax = computed(() => isBDT.value ? 20000000 : (isINR.value ? 15000000 : 500000));
const savingsStep = computed(() => isBDT.value ? 50000 : (isINR.value ? 25000 : 2500));

const investmentMin = computed(() => isBDT.value ? 2000 : (isINR.value ? 1500 : 100));
const investmentMax = computed(() => isBDT.value ? 100000 : (isINR.value ? 80000 : 4000));
const investmentStep = computed(() => isBDT.value ? 1000 : (isINR.value ? 500 : 50));

const currentSavings = ref(props.initialSavings || (isBDT.value ? 500000 : 18500));
const monthlyInvestment = ref(props.initialInvestment || (isBDT.value ? 15000 : 650));
const retirementAge = ref(props.initialRetirementAge || 60);
const isUpdating = ref(false);

watch(
  () => props.initialSavings,
  (val) => {
    if (val !== undefined && val !== null) {
      currentSavings.value = val;
    }
  }
);

watch(
  () => props.initialInvestment,
  (val) => {
    if (val !== undefined && val !== null) {
      monthlyInvestment.value = val;
    }
  }
);

function formatDisplay(val) {
  return formatMoney(val, props.currency);
}

function applyScenario(scenario) {
  if (scenario === 'fire') {
    currentSavings.value = isBDT.value ? 3000000 : 80000;
    monthlyInvestment.value = isBDT.value ? 45000 : 1800;
    retirementAge.value = 45;
  } else if (scenario === 'quit_smoking') {
    monthlyInvestment.value = isBDT.value ? 20000 : 850;
    retirementAge.value = 55;
  }
}

async function updateBaseline() {
  isUpdating.value = true;
  try {
    const res = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        current_age: props.currentAge,
        current_savings: currentSavings.value,
        monthly_investment: monthlyInvestment.value,
        target_retirement_age: retirementAge.value,
        currency: props.currency,
      }),
    });

    const json = await res.json();
    emit('recalculated', json.simulation);
  } catch (e) {
    console.error('Failed to update profile:', e);
  } finally {
    isUpdating.value = false;
  }
}
</script>

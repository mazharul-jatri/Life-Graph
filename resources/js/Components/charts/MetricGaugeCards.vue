<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Biological vs Chronological Age -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900/90 to-emerald-950/40 border border-emerald-500/20 p-5 shadow-lg shadow-black/40 backdrop-blur-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 font-mono">Biological Age</span>
        <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-300 text-sm">♥</span>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-3xl font-extrabold text-white tracking-tight font-mono">
          {{ metrics.biological_age || currentAge }}
        </span>
        <span class="text-xs font-semibold text-emerald-400">
          ({{ bioAgeDiffText }})
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-400">
        Chronological: <span class="text-slate-200 font-medium font-mono">{{ currentAge }} yrs</span> · Fitness benefit detected
      </p>
    </div>

    <!-- 2. Projected Lifespan -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900/90 to-cyan-950/40 border border-cyan-500/20 p-5 shadow-lg shadow-black/40 backdrop-blur-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 font-mono">Projected Lifespan</span>
        <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-300 text-sm">★</span>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-3xl font-extrabold text-white tracking-tight font-mono">
          {{ metrics.projected_lifespan || 82.5 }}
        </span>
        <span class="text-xs text-slate-400 font-mono">years</span>
      </div>
      <p class="mt-2 text-xs text-slate-400">
        WHO Baseline: <span class="text-slate-200 font-medium font-mono">78.5 yrs</span> (<span class="text-cyan-400 font-bold">+{{ (metrics.projected_lifespan - 78.5).toFixed(1) }} yrs</span>)
      </p>
    </div>

    <!-- 3. Financial Freedom Age -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900/90 to-amber-950/40 border border-amber-500/20 p-5 shadow-lg shadow-black/40 backdrop-blur-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 font-mono">Financial Freedom (FIRE)</span>
        <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-300 text-sm">◆</span>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-3xl font-extrabold text-white tracking-tight font-mono">
          Age {{ calculatedFiAge }}
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-400">
        Target: <span class="text-slate-200 font-medium font-mono">Age {{ targetRetirementAge }}</span>
        <span
          class="ml-1.5 font-bold"
          :class="earlyYearsDiff > 0 ? 'text-emerald-400' : (earlyYearsDiff === 0 ? 'text-cyan-400' : 'text-rose-400')"
        >
          ({{ earlyYearsText }})
        </span>
      </p>
    </div>

    <!-- 4. Projected Net Worth @ Target Age -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900/90 to-purple-950/40 border border-purple-500/20 p-5 shadow-lg shadow-black/40 backdrop-blur-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-purple-400 font-mono">Net Worth @ Age {{ targetRetirementAge }}</span>
        <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-300 text-sm">▲</span>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-3xl font-extrabold text-white tracking-tight font-mono">
          {{ formatCurrency(metrics.projected_retirement_nw) }}
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-400">
        7.5% real annual return compounding
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatMoney } from '@/Utils/currency';

const props = defineProps({
  metrics: {
    type: Object,
    default: () => ({}),
  },
  currentAge: {
    type: Number,
    default: 25.0,
  },
  targetRetirementAge: {
    type: Number,
    default: 60,
  },
  currency: {
    type: String,
    default: 'USD',
  },
});

const calculatedFiAge = computed(() => {
  return props.metrics.financial_freedom_age || props.targetRetirementAge || 52;
});

const earlyYearsDiff = computed(() => {
  return props.targetRetirementAge - calculatedFiAge.value;
});

const earlyYearsText = computed(() => {
  const diff = earlyYearsDiff.value;
  if (diff > 0) return `${diff} yrs early`;
  if (diff === 0) return 'On target';
  return `${Math.abs(diff)} yrs after target`;
});

const bioAgeDiffText = computed(() => {
  const bio = props.metrics.biological_age ?? props.currentAge;
  const diff = (bio - props.currentAge).toFixed(1);
  if (diff < 0) return `${Math.abs(diff)} yrs younger`;
  if (diff > 0) return `${diff} yrs older`;
  return 'Equal to chronological';
});

function formatCurrency(val) {
  return formatMoney(val, props.currency);
}
</script>

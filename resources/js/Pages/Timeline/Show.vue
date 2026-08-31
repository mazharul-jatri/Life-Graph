<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col selection:bg-cyan-500 selection:text-white pb-16">
    <!-- Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-xl sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Brand Logo & Home Link -->
        <Link href="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-transform duration-200">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
              Lifecurv
              <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-bold">
                Simulator
              </span>
            </h1>
            <p class="text-[11px] text-slate-400">0–120 Lifespan Trajectory & Actuarial Simulator</p>
          </div>
        </Link>

        <!-- Right Controls: Currency Switcher + Log Activity + User Profile / Logout -->
        <div class="flex items-center space-x-3">
          <!-- Currency Selector Dropdown -->
          <div class="flex items-center gap-1.5 bg-slate-950 px-2.5 py-1.5 rounded-xl border border-slate-800 text-xs">
            <span class="text-amber-400 font-bold">Currency:</span>
            <select
              :value="currentCurrency"
              @change="changeCurrency($event.target.value)"
              class="bg-transparent text-white font-mono font-bold focus:outline-none cursor-pointer text-xs"
            >
              <option v-for="curr in currencies" :key="curr.code" :value="curr.code" class="bg-slate-900 text-white">
                {{ curr.code }} ({{ curr.symbol }})
              </option>
            </select>
          </div>

          <!-- Log Activity CTA -->
          <button
            @click="isLoggerModalOpen = true"
            class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/20 transition flex items-center gap-1.5"
          >
            <span>+</span>
            <span>Log Activity</span>
          </button>

          <!-- User Profile & Logout -->
          <div v-if="user" class="flex items-center space-x-2 text-xs bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-xl">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-slate-200 font-medium hidden sm:inline">{{ user.name }}</span>
            <span class="text-slate-500 font-mono text-[11px]">Age {{ userProfile.current_age }}</span>

            <Link
              href="/logout"
              method="post"
              as="button"
              class="ml-2 text-slate-500 hover:text-rose-400 font-bold transition text-[11px]"
              title="Sign Out"
            >
              Logout
            </Link>
          </div>
          <div v-else class="flex items-center gap-2">
            <Link href="/login" class="text-xs font-bold text-slate-300 hover:text-white px-2 py-1">
              Sign In
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-6">
      <!-- 1. Top Actuarial Metric Gauges -->
      <MetricGaugeCards
        :metrics="activeMetrics"
        :current-age="userProfile.current_age || 25.0"
        :target-retirement-age="userProfile.target_retirement_age || 60"
        :currency="currentCurrency"
      />

      <!-- 2. Chart View Mode Switcher & Overview -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60 border border-slate-800/80 p-4 rounded-2xl backdrop-blur-xl">
        <div>
          <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
            Life Trajectory Visualizer
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">
            Displaying net worth and investments in <span class="text-amber-400 font-bold font-mono">{{ currentCurrency }}</span>. Switch between continuous 0–120 simulation and decision fork lanes.
          </p>
        </div>

        <!-- View Mode Switcher -->
        <div class="flex items-center gap-2 bg-slate-950 p-1 rounded-xl border border-slate-800">
          <button
            @click="chartViewMode = 'lifespan'"
            class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
            :class="[
              chartViewMode === 'lifespan'
                ? 'bg-cyan-600 text-white shadow-md shadow-cyan-900/40'
                : 'text-slate-400 hover:text-slate-200'
            ]"
          >
            <span>📈</span>
            <span>0–120 Lifespan Curve</span>
          </button>

          <button
            @click="chartViewMode = 'decision_tree'"
            class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
            :class="[
              chartViewMode === 'decision_tree'
                ? 'bg-purple-600 text-white shadow-md shadow-purple-900/40'
                : 'text-slate-400 hover:text-slate-200'
            ]"
          >
            <span>🌿</span>
            <span>Decision Fork Lanes</span>
          </button>
        </div>
      </div>

      <!-- 3. Active Chart Component -->
      <section class="w-full">
        <LifespanTrajectoryChart
          v-if="chartViewMode === 'lifespan'"
          :simulation-data="currentSimulation"
        />
        <BranchingTimelineChart
          v-else
          :data="timelineData"
        />
      </section>

      <!-- 4. Lower Dual Panel: Activity Log & What-If Sandbox -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Logged Habits & Activities Table -->
        <div class="lg:col-span-2 rounded-2xl bg-slate-900/80 border border-slate-800 p-5 shadow-xl backdrop-blur-xl">
          <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
            <div class="flex items-center gap-2">
              <span class="text-base">📋</span>
              <div>
                <h3 class="text-sm font-bold text-white tracking-wide">Logged Activities & Habit Vector</h3>
                <p class="text-xs text-slate-400">Activities alter your biological age, health curve, and wealth.</p>
              </div>
            </div>
            <button
              @click="isLoggerModalOpen = true"
              class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-400 text-xs font-bold border border-slate-700 transition"
            >
              + Log Habit
            </button>
          </div>

          <!-- Activities List -->
          <div v-if="activities && activities.length > 0" class="divide-y divide-slate-800/60">
            <div
              v-for="act in activities"
              :key="act.id"
              class="py-3 flex items-center justify-between gap-3 text-xs"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold"
                  :class="getPillarBadgeBg(act.pillar)"
                >
                  {{ getPillarIcon(act.pillar) }}
                </span>
                <div>
                  <div class="font-bold text-slate-200">{{ act.title }}</div>
                  <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                    <span class="font-mono text-cyan-400 font-semibold">Age {{ act.start_age }}</span>
                    <span>·</span>
                    <span class="capitalize">{{ act.frequency }} ({{ act.duration_months }} mo)</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase"
                  :class="act.is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400'"
                >
                  {{ act.is_active ? 'Active' : 'Completed' }}
                </span>

                <button
                  @click="deleteActivity(act.id)"
                  class="text-slate-500 hover:text-rose-400 p-1 transition"
                  title="Remove activity"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-slate-500 text-xs">
            No activities logged yet. Click "+ Log Activity" to begin.
          </div>
        </div>

        <!-- Right 1 Col: What-If Sandbox Controls -->
        <div class="lg:col-span-1">
          <WhatIfScenarioDrawer
            :current-age="userProfile.current_age || 25.0"
            :initial-savings="userProfile.current_savings || 18500"
            :initial-investment="userProfile.monthly_investment || 650"
            :initial-retirement-age="userProfile.target_retirement_age || 60"
            :currency="currentCurrency"
            @recalculated="handleSimulationRecalculated"
          />
        </div>
      </div>
    </main>

    <!-- Activity Logger Modal -->
    <ActivityLoggerModal
      :is-open="isLoggerModalOpen"
      :current-age="userProfile.current_age || 25.0"
      @close="isLoggerModalOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { SUPPORTED_CURRENCIES } from '@/Utils/currency';
import LifespanTrajectoryChart from '@/Components/charts/LifespanTrajectoryChart.vue';
import BranchingTimelineChart from '@/Components/charts/BranchingTimelineChart.vue';
import MetricGaugeCards from '@/Components/charts/MetricGaugeCards.vue';
import ActivityLoggerModal from '@/Components/simulator/ActivityLoggerModal.vue';
import WhatIfScenarioDrawer from '@/Components/simulator/WhatIfScenarioDrawer.vue';

const props = defineProps({
  simulationData: {
    type: Object,
    default: () => ({}),
  },
  timelineData: {
    type: Object,
    required: true,
  },
  activities: {
    type: Array,
    default: () => [],
  },
  user: {
    type: Object,
    default: null,
  },
});

const currencies = SUPPORTED_CURRENCIES;

const chartViewMode = ref('lifespan');
const isLoggerModalOpen = ref(false);

const localSimulation = ref(null);

const currentSimulation = computed(() => {
  return localSimulation.value || props.simulationData || {};
});

const userProfile = computed(() => {
  return currentSimulation.value.user_profile || props.user?.profile || { current_age: 25.0, currency: 'USD' };
});

const currentCurrency = computed(() => {
  return userProfile.value.currency || 'USD';
});

const activeMetrics = computed(() => {
  return currentSimulation.value.baseline_metrics || {};
});

function getPillarIcon(pillar) {
  switch (pillar) {
    case 'health': return '♥';
    case 'wealth': return '◆';
    case 'career': return '▲';
    default: return '★';
  }
}

function getPillarBadgeBg(pillar) {
  switch (pillar) {
    case 'health': return 'bg-emerald-950 text-emerald-400 border border-emerald-800';
    case 'wealth': return 'bg-amber-950 text-amber-400 border border-amber-800';
    case 'career': return 'bg-blue-950 text-blue-400 border border-blue-800';
    default: return 'bg-purple-950 text-purple-400 border border-purple-800';
  }
}

async function changeCurrency(code) {
  try {
    const res = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        currency: code,
      }),
    });
    const json = await res.json();
    localSimulation.value = json.simulation;
  } catch (e) {
    console.error('Failed to update currency:', e);
  }
}

function deleteActivity(id) {
  if (confirm('Remove this activity from your life model?')) {
    router.delete(`/activities/${id}`);
  }
}

function handleSimulationRecalculated(newSim) {
  localSimulation.value = newSim;
}
</script>

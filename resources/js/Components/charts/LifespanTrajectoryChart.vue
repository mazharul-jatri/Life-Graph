<template>
  <div class="relative w-full rounded-2xl bg-slate-900/90 border border-slate-800/80 p-5 shadow-2xl backdrop-blur-xl">
    <!-- Header & Controls Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 border-b border-slate-800 pb-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
          <h3 class="text-base font-bold text-white tracking-wide uppercase font-mono text-xs">
            0–120 Lifespan Trajectory Engine
          </h3>
        </div>
        <p class="text-xs text-slate-400 mt-0.5">
          Actuarial projection calibrated to <span class="text-cyan-300 font-semibold">Age {{ currentAge }}</span> baseline with empirical risk modeling.
        </p>
      </div>

      <!-- Pillar Selector Tabs -->
      <div class="flex flex-wrap items-center gap-1.5 bg-slate-950/70 p-1 rounded-xl border border-slate-800/60">
        <button
          v-for="pillar in pillars"
          :key="pillar.id"
          @click="activePillar = pillar.id"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
          :class="[
            activePillar === pillar.id
              ? `${pillar.activeBg} text-white shadow-md shadow-cyan-950/40 font-bold`
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'
          ]"
        >
          <span>{{ pillar.icon }}</span>
          <span>{{ pillar.label }}</span>
        </button>
      </div>
    </div>

    <!-- Chart Canvas -->
    <div
      ref="chartRef"
      class="w-full h-[520px] transition-opacity duration-300"
    ></div>

    <!-- Chart Legend & Zone Explanations -->
    <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs">
      <div class="flex flex-wrap items-center gap-4 text-slate-400">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(6,182,212,0.8)]"></span>
          <span class="text-slate-300 font-medium">Primary Trajectory (Actual + Target)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-purple-400 shadow-[0_0_8px_rgba(192,132,252,0.8)]"></span>
          <span class="text-slate-300 font-medium">What-If Branch (Simulated)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rotate-45 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
          <span class="text-rose-400 font-medium">Critical Risk Inflection Point</span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-cyan-950/50 border border-cyan-800/50 text-[11px] text-cyan-300 font-mono">
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
          Age 0 → {{ currentAge }}: Historical Past
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-950/50 border border-purple-800/50 text-[11px] text-purple-300 font-mono">
          <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
          Age {{ currentAge }} → 120: Projected Future
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import * as echarts from 'echarts';
import { formatMoney } from '@/Utils/currency';

const props = defineProps({
  simulationData: {
    type: Object,
    required: true,
  },
});

const chartRef = ref(null);
let chartInstance = null;

const activePillar = ref('overall');

const pillars = [
  { id: 'overall', label: 'Overall Life Index', icon: '★', activeBg: 'bg-cyan-600' },
  { id: 'health', label: 'Health & Vitality', icon: '♥', activeBg: 'bg-emerald-600' },
  { id: 'wealth', label: 'Wealth & Net Worth', icon: '◆', activeBg: 'bg-amber-600' },
  { id: 'career', label: 'Career Mastery', icon: '▲', activeBg: 'bg-blue-600' },
];

const userCurrency = computed(() => {
  return props.simulationData?.user_profile?.currency || 'USD';
});

const currentAge = computed(() => {
  return props.simulationData?.user_profile?.current_age ?? 25.0;
});

function formatCurrency(val) {
  return formatMoney(val, userCurrency.value);
}

function buildOption() {
  const sim = props.simulationData;
  if (!sim || !sim.timelines) return {};

  const pillar = activePillar.value;
  const ageNow = sim.user_profile?.current_age ?? 25.0;

  const series = [];

  // 1. Mark Area to distinguish Past Reality (0 -> ageNow) vs Projected Future (ageNow -> 120)
  series.push({
    name: 'Life Zones',
    type: 'line',
    data: [],
    markArea: {
      silent: true,
      data: [
        [
          {
            name: 'Historical Past',
            xAxis: 0,
            itemStyle: {
              color: 'rgba(15, 23, 42, 0.4)',
            },
            label: {
              position: 'insideTopLeft',
              distance: 12,
              color: 'rgba(148, 163, 184, 0.4)',
              fontSize: 11,
              fontFamily: 'monospace',
              formatter: '← HISTORICAL PAST',
            },
          },
          {
            xAxis: ageNow,
          },
        ],
        [
          {
            name: 'Projected Future',
            xAxis: ageNow,
            itemStyle: {
              color: 'rgba(30, 41, 59, 0.25)',
            },
            label: {
              position: 'insideTopRight',
              distance: 12,
              color: 'rgba(192, 132, 252, 0.5)',
              fontSize: 11,
              fontFamily: 'monospace',
              formatter: 'SIMULATED PROJECTION →',
            },
          },
          {
            xAxis: 120,
          },
        ],
      ],
    },
    markLine: {
      symbol: ['none', 'none'],
      silent: false,
      lineStyle: {
        color: '#38bdf8',
        width: 2,
        type: 'dashed',
      },
      data: [
        {
          xAxis: ageNow,
          label: {
            show: true,
            position: 'end',
            formatter: `📍 YOU ARE HERE (Age ${ageNow})`,
            color: '#38bdf8',
            fontWeight: 'bold',
            fontSize: 12,
            backgroundColor: 'rgba(12, 74, 110, 0.9)',
            padding: [4, 8],
            borderRadius: 6,
            borderColor: '#38bdf8',
            borderWidth: 1,
          },
        },
      ],
    },
  });

  // 2. Trajectory Curves for each timeline
  sim.timelines.forEach((timeline, idx) => {
    const isPrimary = timeline.is_primary;
    const curvePoints = timeline.curve_data || [];

    const lineData = curvePoints.map((pt) => {
      let val = pt[pillar];
      if (pillar === 'wealth') {
        val = pt.wealth;
      }
      return [pt.age, val];
    });

    const lineColor = isPrimary ? '#06b6d4' : '#c084fc';

    series.push({
      name: timeline.name,
      type: 'line',
      smooth: 0.3,
      data: lineData,
      lineStyle: {
        width: isPrimary ? 3.5 : 2.5,
        color: lineColor,
        type: isPrimary ? 'solid' : 'dashed',
        shadowColor: isPrimary ? 'rgba(6, 182, 212, 0.5)' : 'rgba(192, 132, 252, 0.5)',
        shadowBlur: 10,
      },
      areaStyle: isPrimary
        ? {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: 'rgba(6, 182, 212, 0.25)' },
              { offset: 1, color: 'rgba(6, 182, 212, 0.0)' },
            ]),
          }
        : undefined,
      showSymbol: false,
      z: isPrimary ? 3 : 2,
    });

    // 3. Milestone Scatter Markers along the curve
    const milestoneScatter = (timeline.milestones || []).map((m) => {
      const matchPoint = curvePoints.find((p) => Math.round(p.age) === Math.round(m.age));
      let yVal = 0;
      if (matchPoint) {
        yVal = pillar === 'wealth' ? matchPoint.wealth : matchPoint[pillar];
      }

      const isRisk = Boolean(m.risk_alert || m.impact < 0);
      const isProjected = Boolean(m.is_projected);

      return {
        name: m.title,
        value: [m.age, yVal],
        symbol: isRisk ? 'diamond' : (isProjected ? 'triangle' : 'circle'),
        symbolSize: isRisk ? 22 : (16 + Math.abs(m.impact || 0) * 1.5),
        itemStyle: {
          color: isRisk ? '#f43f5e' : (isPrimary ? '#06b6d4' : '#c084fc'),
          borderColor: '#ffffff',
          borderWidth: 2,
          shadowColor: isRisk ? 'rgba(244,63,94,0.9)' : 'rgba(6,182,212,0.8)',
          shadowBlur: 14,
        },
        rawMilestone: m,
        timelineName: timeline.name,
      };
    });

    if (milestoneScatter.length > 0) {
      series.push({
        name: `${timeline.name} Milestones`,
        type: 'scatter',
        data: milestoneScatter,
        z: 5,
        label: {
          show: true,
          position: isPrimary ? 'top' : 'bottom',
          distance: 10,
          formatter: (params) => {
            const raw = params.data.rawMilestone;
            return `{title|${raw.title}}\n{age|Age ${raw.age}}`;
          },
          rich: {
            title: {
              color: '#f8fafc',
              fontSize: 10,
              fontWeight: 700,
              padding: [2, 6],
              backgroundColor: 'rgba(15, 23, 42, 0.92)',
              borderColor: isPrimary ? 'rgba(6, 182, 212, 0.4)' : 'rgba(192, 132, 252, 0.4)',
              borderWidth: 1,
              borderRadius: 4,
            },
            age: {
              color: '#38bdf8',
              fontSize: 9,
              fontFamily: 'monospace',
              fontWeight: 600,
              align: 'center',
            },
          },
        },
      });
    }
  });

  const isWealth = pillar === 'wealth';

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'axis',
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      borderColor: 'rgba(255, 255, 255, 0.12)',
      borderWidth: 1,
      padding: [12, 16],
      borderRadius: 12,
      textStyle: { color: '#f8fafc' },
      axisPointer: {
        type: 'cross',
        lineStyle: { color: '#64748b', type: 'dashed' },
        crossStyle: { color: '#64748b' },
      },
      formatter: (params) => {
        if (!params || !params.length) return '';
        const age = params[0].axisValue;
        let html = `
          <div style="min-width: 220px; font-family: system-ui, sans-serif;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 6px; margin-bottom: 8px;">
              <span style="font-size: 13px; font-weight: 800; color: #38bdf8;">Age: ${age} Years</span>
              <span style="font-size: 10px; color: ${age <= ageNow ? '#34d399' : '#c084fc'}; font-weight: 700; font-family: monospace;">
                ${age <= ageNow ? '● ACTUAL PAST' : '✦ SIMULATION'}
              </span>
            </div>
        `;

        params.forEach((item) => {
          if (item.seriesType === 'line' && item.seriesName !== 'Life Zones') {
            const val = item.value[1];
            const displayVal = isWealth ? formatCurrency(val) : `${val} / 100`;
            html += `
              <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px; font-size: 11px;">
                <span style="color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                  <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${item.color};"></span>
                  ${item.seriesName}
                </span>
                <span style="font-weight: 800; color: #f8fafc; font-family: monospace;">${displayVal}</span>
              </div>
            `;
          }
        });

        html += `</div>`;
        return html;
      },
    },
    grid: {
      top: 50,
      bottom: 50,
      left: 70,
      right: 40,
      containLabel: true,
    },
    xAxis: {
      type: 'value',
      min: 0,
      max: 120,
      interval: 10,
      axisLine: { lineStyle: { color: '#334155' } },
      axisTick: { lineStyle: { color: '#334155' } },
      splitLine: {
        show: true,
        lineStyle: { color: 'rgba(51, 65, 85, 0.4)', type: 'dashed' },
      },
      axisLabel: {
        color: '#94a3b8',
        fontFamily: 'monospace',
        fontSize: 11,
        formatter: (val) => `${val} yrs`,
      },
    },
    yAxis: {
      type: 'value',
      min: 0,
      max: isWealth ? undefined : 100,
      axisLine: { show: false },
      axisTick: { show: false },
      splitLine: {
        show: true,
        lineStyle: { color: 'rgba(51, 65, 85, 0.35)', type: 'solid' },
      },
      axisLabel: {
        color: '#94a3b8',
        fontFamily: 'monospace',
        fontSize: 11,
        formatter: (val) => (isWealth ? formatCurrency(val) : `${val}`),
      },
    },
    series: series,
  };
}

function renderChart() {
  if (!chartRef.value) return;

  const width = chartRef.value.clientWidth || 800;
  const height = chartRef.value.clientHeight || 520;

  if (!chartInstance) {
    chartInstance = echarts.init(chartRef.value, null, {
      renderer: 'svg',
      width: width,
      height: height,
    });
  }

  const option = buildOption();
  chartInstance.setOption(option, true);
  chartInstance.resize();
}

function handleResize() {
  if (chartInstance) {
    chartInstance.resize();
  }
}

watch(
  [() => props.simulationData, activePillar],
  () => {
    nextTick(() => renderChart());
  },
  { deep: true }
);

onMounted(() => {
  nextTick(() => {
    renderChart();
    window.addEventListener('resize', handleResize);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
  if (chartInstance) {
    chartInstance.dispose();
    chartInstance = null;
  }
});
</script>

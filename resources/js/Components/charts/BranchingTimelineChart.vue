<template>
  <div class="w-full relative flex flex-col">
    <!-- Chart Header & Legend -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 px-2">
      <div class="flex items-center space-x-3">
        <div class="w-3 h-3 rounded-full bg-cyan-400 animate-pulse"></div>
        <span class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Branching Trajectory Matrix</span>
      </div>

      <!-- Interactive Legend -->
      <div class="flex flex-wrap items-center gap-3 text-xs">
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-900/80 border border-cyan-500/30 text-cyan-300 font-medium">
          <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
          Actual Path (Lane 0)
        </div>
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-900/80 border border-purple-500/30 text-purple-300 font-medium">
          <span class="w-2.5 h-2.5 rounded-sm rotate-45 border border-purple-400 bg-purple-950"></span>
          Projected Branch (Lane 1)
        </div>
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-900/80 border border-slate-700 text-slate-400">
          <span class="w-4 h-0.5 border-t-2 border-dashed border-purple-400"></span>
          Decision Fork Divergence
        </div>
      </div>
    </div>

    <!-- Chart Container with explicit dimensions -->
    <div
      ref="chartContainerRef"
      style="width: 100%; height: 540px; min-height: 540px;"
      class="rounded-xl bg-slate-950/70 border border-slate-800/80 shadow-2xl relative overflow-hidden"
    >
      <!-- Background subtle grid effect -->
      <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-40 pointer-events-none"></div>
      
      <!-- ECharts DOM container -->
      <div
        ref="chartDomRef"
        style="width: 100%; height: 100%; min-height: 540px;"
      ></div>
    </div>

    <!-- Timeline Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
      <div
        v-for="timeline in data.timelines"
        :key="timeline.id"
        class="p-4 rounded-xl border transition-all duration-200"
        :class="[
          timeline.is_primary
            ? 'bg-slate-900/70 border-cyan-500/30 shadow-lg shadow-cyan-950/20'
            : 'bg-slate-900/70 border-purple-500/30 shadow-lg shadow-purple-950/20'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center space-x-2">
            <span
              class="px-2 py-0.5 text-xs font-semibold rounded"
              :class="timeline.is_primary ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30'"
            >
              {{ timeline.is_primary ? 'PRIMARY' : 'WHAT-IF BRANCH' }}
            </span>
            <h4 class="font-semibold text-slate-200 text-sm">{{ timeline.name }}</h4>
          </div>
          <span class="text-xs text-slate-400 font-mono">Lane {{ timeline.events[0]?.y ?? 0 }}</span>
        </div>

        <div v-if="timeline.branch_at_x" class="text-xs text-purple-300/90 mb-3 flex items-center gap-1.5 font-medium">
          <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
          </svg>
          Forks from parent at <span class="font-mono text-purple-200 font-bold bg-purple-950/60 px-1.5 py-0.5 rounded border border-purple-800/50">{{ timeline.branch_at_x }}</span>
        </div>

        <div class="space-y-1.5 mt-2">
          <div
            v-for="(event, idx) in timeline.events"
            :key="idx"
            class="flex items-center justify-between text-xs py-1.5 px-2.5 rounded bg-slate-950/60 border border-slate-800/80"
          >
            <div class="flex items-center gap-2">
              <span
                class="w-2.5 h-2.5 rounded-full"
                :style="{ backgroundColor: getCategoryColor(event.category) }"
              ></span>
              <span class="font-mono text-slate-400 font-semibold">{{ event.x }}</span>
              <span class="text-slate-200 font-medium">{{ event.title }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span
                v-if="event.is_projected"
                class="text-[10px] px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/40 uppercase font-semibold"
              >
                Projected
              </span>
              <span
                v-else
                class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase font-semibold"
              >
                Actual
              </span>
              <span class="text-[11px] font-mono text-slate-400">Impact: <span :class="event.impact > 0 ? 'text-emerald-400' : 'text-slate-400'">{{ event.impact > 0 ? '+' + event.impact : event.impact }}</span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import * as echarts from 'echarts';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

const chartDomRef = ref(null);
const chartContainerRef = ref(null);
let chartInstance = null;

// Category colors mapping
const categoryColors = {
  career: '#06b6d4',      // Cyan
  education: '#3b82f6',   // Blue
  relationship: '#ec4899',// Pink
  health: '#10b981',      // Emerald
  finance: '#f59e0b',     // Amber
  location: '#8b5cf6',    // Purple
  other: '#94a3b8',       // Slate
};

function getCategoryColor(cat) {
  return categoryColors[cat?.toLowerCase()] || categoryColors.other;
}

// Convert YYYY-MM into timestamp (milliseconds)
function parseYearMonthTimestamp(dateStr) {
  if (!dateStr) return 0;
  const parts = dateStr.split('-');
  const year = parseInt(parts[0], 10);
  const month = parseInt(parts[1] || '1', 10) - 1;
  return new Date(year, month, 1).getTime();
}

function formatYearMonth(timestamp) {
  if (!timestamp) return '';
  const d = new Date(timestamp);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  return `${y}-${m}`;
}

function buildChartOption(timelineData) {
  const timelines = timelineData?.timelines || [];
  if (!timelines.length) return {};

  // Gather min and max timestamps across all events and branch fork points
  let timestamps = [];
  timelines.forEach((t) => {
    if (t.branch_at_x) {
      timestamps.push(parseYearMonthTimestamp(t.branch_at_x));
    }
    t.events.forEach((e) => {
      if (e.x) {
        timestamps.push(parseYearMonthTimestamp(e.x));
      }
    });
  });

  if (!timestamps.length) return {};

  const minTime = Math.min(...timestamps);
  const maxTime = Math.max(...timestamps);

  // 6 months padding on each side
  const paddingMs = 180 * 24 * 3600 * 1000;
  const axisMin = minTime - paddingMs;
  const axisMax = maxTime + paddingMs;

  const series = [];

  // 1. Fork Connector Lines (cartesian2d lines connecting branch_at_x on parent lane to first event on branch lane)
  const forkLines = [];
  timelines.forEach((t) => {
    if (t.branch_at_x && t.parent_id !== null && t.events.length > 0) {
      const parentTimeline = timelines.find((pt) => pt.id === t.parent_id) || timelines[0];
      const parentLane = parentTimeline.events[0]?.y ?? 0;
      const branchLane = t.events[0]?.y ?? 1;
      const forkTs = parseYearMonthTimestamp(t.branch_at_x);
      const firstBranchTs = parseYearMonthTimestamp(t.events[0].x);

      forkLines.push({
        fromName: `Fork (${t.branch_at_x})`,
        toName: t.events[0].title,
        coords: [
          [forkTs, parentLane],
          [firstBranchTs, branchLane],
        ],
      });
    }
  });

  if (forkLines.length > 0) {
    series.push({
      name: 'Decision Fork',
      type: 'lines',
      coordinateSystem: 'cartesian2d',
      z: 1,
      lineStyle: {
        color: '#c084fc',
        width: 3,
        type: 'dashed',
        curveness: 0.25,
        opacity: 0.9,
      },
      data: forkLines,
    });
  }

  // 2. Build series for each timeline (continuous line + event scatter nodes)
  timelines.forEach((timeline, tIndex) => {
    const isPrimary = timeline.is_primary;
    const laneIndex = timeline.events[0]?.y ?? tIndex;
    const lineColor = isPrimary ? '#06b6d4' : '#a855f7';

    // Sort events chronologically
    const sortedEvents = [...timeline.events].sort((a, b) => {
      return parseYearMonthTimestamp(a.x) - parseYearMonthTimestamp(b.x);
    });

    const lineCoordinates = sortedEvents.map((e) => [
      parseYearMonthTimestamp(e.x),
      e.y,
    ]);

    // Continuous timeline line
    series.push({
      name: timeline.name,
      type: 'line',
      coordinateSystem: 'cartesian2d',
      data: lineCoordinates,
      smooth: false,
      z: 2,
      lineStyle: {
        width: isPrimary ? 4 : 3,
        color: lineColor,
        type: isPrimary ? 'solid' : 'dashed',
        shadowColor: isPrimary ? 'rgba(6, 182, 212, 0.4)' : 'rgba(168, 85, 247, 0.4)',
        shadowBlur: 10,
      },
      symbol: 'none',
      emphasis: {
        lineStyle: {
          width: 5,
        },
      },
    });

    // Event markers (Scatter)
    const scatterData = sortedEvents.map((e, idx) => {
      const isProjected = Boolean(e.is_projected);
      const catColor = getCategoryColor(e.category);
      const nodeSize = 18 + Math.max(-2, Math.min(5, e.impact || 0)) * 2;
      const labelPos = isPrimary
        ? (idx % 2 === 0 ? 'top' : 'bottom')
        : (idx % 2 === 0 ? 'bottom' : 'top');

      return {
        name: e.title,
        value: [parseYearMonthTimestamp(e.x), e.y, e.impact],
        symbol: isProjected ? 'diamond' : 'circle',
        symbolSize: nodeSize,
        itemStyle: {
          color: isProjected ? '#1e1b4b' : catColor,
          borderColor: isProjected ? '#c084fc' : '#ffffff',
          borderWidth: isProjected ? 3 : 2,
          shadowColor: isProjected ? 'rgba(192, 132, 252, 0.7)' : `${catColor}99`,
          shadowBlur: 14,
        },
        label: {
          show: true,
          position: labelPos,
          distance: 10,
          hideOverlap: false,
        },
        rawEvent: e,
        timelineName: timeline.name,
        isPrimary: timeline.is_primary,
      };
    });

    series.push({
      name: `${timeline.name} Events`,
      type: 'scatter',
      coordinateSystem: 'cartesian2d',
      data: scatterData,
      z: 3,
      label: {
        show: true,
        formatter: (params) => {
          const raw = params.data.rawEvent;
          return `{title|${raw.title}}\n{date|${raw.x}}`;
        },
        rich: {
          title: {
            color: '#f8fafc',
            fontSize: 11,
            fontWeight: 700,
            lineHeight: 14,
            padding: [3, 8],
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            borderColor: isPrimary ? 'rgba(6, 182, 212, 0.3)' : 'rgba(168, 85, 247, 0.3)',
            borderWidth: 1,
            borderRadius: 6,
          },
          date: {
            color: isPrimary ? '#38bdf8' : '#c084fc',
            fontSize: 10,
            fontFamily: 'monospace',
            fontWeight: 600,
            lineHeight: 14,
            align: 'center',
          },
        },
      },
      emphasis: {
        scale: 1.25,
        itemStyle: {
          shadowBlur: 25,
          shadowColor: '#38bdf8',
        },
      },
    });
  });

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'item',
      backgroundColor: 'rgba(15, 23, 42, 0.96)',
      borderColor: 'rgba(255, 255, 255, 0.15)',
      borderWidth: 1,
      padding: [14, 18],
      textStyle: {
        color: '#f8fafc',
        fontSize: 12,
      },
      extraCssText: 'box-shadow: 0 20px 50px rgba(0,0,0,0.7); backdrop-filter: blur(16px); border-radius: 12px;',
      formatter: (params) => {
        if (params.seriesType === 'lines') {
          return `
            <div style="font-weight: 700; color: #c084fc; font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif;">
              ⚡ Decision Fork Point
            </div>
            <div style="color: #cbd5e1; font-size: 11px; margin-top: 4px;">
              Divergence into hypothetical trajectory
            </div>
          `;
        }

        const data = params.data;
        if (!data || !data.rawEvent) return '';
        const e = data.rawEvent;
        const catColor = getCategoryColor(e.category);
        const impactBadge = e.impact > 0 ? `+${e.impact}` : `${e.impact}`;
        const impactColor = e.impact > 0 ? '#10b981' : e.impact < 0 ? '#f43f5e' : '#94a3b8';

        return `
          <div style="min-width: 240px; font-family: 'Plus Jakarta Sans', sans-serif;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
              <span style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: ${catColor}; background: ${catColor}20; border: 1px solid ${catColor}50; padding: 2px 8px; border-radius: 4px;">
                ${e.category || 'Event'}
              </span>
              <span style="font-size: 11px; font-family: monospace; color: #94a3b8; font-weight: 600;">${e.x}</span>
            </div>

            <div style="font-size: 14px; font-weight: 700; color: #ffffff; margin-bottom: 6px; line-height: 1.3;">
              ${e.title}
            </div>

            ${e.description ? `<div style="font-size: 11px; color: #94a3b8; margin-bottom: 8px; line-height: 1.4;">${e.description}</div>` : ''}

            <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 8px; margin-top: 6px; display: flex; align-items: center; justify-content: space-between; font-size: 11px;">
              <span style="color: #64748b;">Timeline Path</span>
              <span style="color: ${data.isPrimary ? '#38bdf8' : '#c084fc'}; font-weight: 700;">${data.timelineName}</span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; margin-top: 4px;">
              <span style="color: #64748b;">Status</span>
              <span style="font-weight: 700; color: ${e.is_projected ? '#c084fc' : '#34d399'};">
                ${e.is_projected ? '✦ Simulated Projection' : '● Actual Past'}
              </span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; margin-top: 4px;">
              <span style="color: #64748b;">Impact Score</span>
              <span style="font-weight: 800; color: ${impactColor}; font-family: monospace;">${impactBadge} / 5</span>
            </div>
          </div>
        `;
      },
    },
    grid: {
      top: 60,
      bottom: 60,
      left: 170,
      right: 60,
      containLabel: false,
    },
    xAxis: {
      type: 'time',
      min: axisMin,
      max: axisMax,
      axisLine: {
        lineStyle: {
          color: '#334155',
        },
      },
      axisTick: {
        lineStyle: {
          color: '#334155',
        },
      },
      splitLine: {
        show: true,
        lineStyle: {
          color: 'rgba(51, 65, 85, 0.4)',
          type: 'dashed',
        },
      },
      axisLabel: {
        color: '#94a3b8',
        fontFamily: 'monospace',
        fontSize: 11,
        formatter: (val) => formatYearMonth(val),
      },
    },
    yAxis: {
      type: 'value',
      inverse: true,
      min: -0.6,
      max: Math.max(1, timelines.length - 1) + 0.6,
      interval: 1,
      axisLine: {
        show: false,
      },
      axisTick: {
        show: false,
      },
      splitLine: {
        show: true,
        lineStyle: {
          color: 'rgba(51, 65, 85, 0.45)',
          type: 'solid',
        },
      },
      axisLabel: {
        formatter: (val) => {
          const idx = Math.round(val);
          const t = timelines[idx];
          if (!t) return '';
          return t.is_primary ? `★ ${t.name}` : `↳ ${t.name}`;
        },
        color: (val) => {
          const idx = Math.round(val);
          return idx === 0 ? '#38bdf8' : '#c084fc';
        },
        fontWeight: 'bold',
        fontSize: 12,
        margin: 18,
      },
    },
    series: series,
  };
}

function renderChart() {
  if (!chartDomRef.value) return;

  const width = chartDomRef.value.clientWidth || 800;
  const height = chartDomRef.value.clientHeight || 540;

  if (!chartInstance) {
    chartInstance = echarts.init(chartDomRef.value, null, {
      renderer: 'svg',
      width: width,
      height: height,
    });
  }

  const option = buildChartOption(props.data);
  chartInstance.setOption(option, true);
  chartInstance.resize();
}

function handleResize() {
  if (chartInstance) {
    chartInstance.resize();
  }
}

watch(
  () => props.data,
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

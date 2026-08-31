<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 selection:bg-cyan-500 selection:text-white flex flex-col">
    <!-- 1. Top Navigation Bar -->
    <header class="border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-cyan-500/25 group-hover:scale-105 transition-transform duration-300">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div>
            <div class="text-lg font-black tracking-tight text-white flex items-center gap-2">
              Lifecurv
              <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                v2.0 Actuarial
              </span>
            </div>
            <p class="text-[11px] text-slate-400 tracking-wide font-medium">Life Trajectory & Predictive Simulator</p>
          </div>
        </Link>

        <!-- Navigation Links & Auth Actions -->
        <div class="flex items-center space-x-4">
          <a href="#features" class="hidden md:inline-block text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
            Features
          </a>
          <a href="#actuarial-models" class="hidden md:inline-block text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
            Actuarial Science
          </a>
          <a href="#what-if" class="hidden md:inline-block text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
            "What-If" Branches
          </a>

          <div class="h-4 w-[1px] bg-slate-800 hidden md:block"></div>

          <template v-if="$page.props.auth?.user">
            <Link
              href="/simulator"
              class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/25 transition flex items-center gap-2"
            >
              <span>Go to Dashboard</span>
              <span>→</span>
            </Link>
          </template>
          <template v-else>
            <Link
              href="/login"
              class="text-xs font-bold text-slate-300 hover:text-white px-3 py-2 rounded-xl hover:bg-slate-900 transition"
            >
              Sign In
            </Link>

            <Link
              href="/register"
              class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/25 transition flex items-center gap-1.5"
            >
              <span>Get Started</span>
              <span>→</span>
            </Link>
          </template>
        </div>
      </div>
    </header>

    <!-- 2. Hero Section with Live Trajectory Teaser -->
    <section class="relative pt-16 pb-20 overflow-hidden border-b border-slate-900">
      <!-- Background Ambient Glows -->
      <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-gradient-to-tr from-cyan-500/15 via-indigo-500/10 to-purple-500/15 rounded-full blur-[120px] pointer-events-none"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-12">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-500/30 text-cyan-400 text-xs font-mono font-semibold mb-6 shadow-inner">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            Empirical Actuarial Life-Modeling Engine (Age 0–120)
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.12]">
            Simulate Your Entire Life Trajectory <span class="bg-gradient-to-r from-cyan-400 via-indigo-300 to-purple-400 bg-clip-text text-transparent">From Age 0 to 120</span>
          </h1>

          <p class="mt-6 text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
            Log your daily workouts, skill workshops, degrees, and investments. LifeCurv benchmarks your habits against <span class="text-white font-semibold">WHO, CDC & S&P 500 actuarial data</span> to project your biological lifespan, financial freedom age, and counterfactual <span class="text-purple-300 font-semibold">"What-If"</span> branches.
          </p>

          <!-- CTA Buttons -->
          <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              href="/register"
              class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-500 via-indigo-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-extrabold text-sm shadow-xl shadow-cyan-500/30 hover:scale-[1.02] transition-all duration-200"
            >
              Simulate Your Trajectory (Free)
            </Link>

            <Link
              href="/demo-login"
              class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700 text-slate-200 font-bold text-sm shadow-lg transition flex items-center justify-center gap-2"
            >
              <span>⚡ Try Interactive 25-Year-Old Demo</span>
            </Link>
          </div>

          <!-- Currency & Global Indicator -->
          <div class="mt-6 flex items-center justify-center gap-6 text-xs text-slate-400">
            <span class="flex items-center gap-1.5">
              <span class="text-emerald-400">✓</span> Multi-Currency Support ($ USD, ৳ BDT, € EUR, £ GBP, ₹ INR)
            </span>
            <span class="flex items-center gap-1.5">
              <span class="text-cyan-400">✓</span> No Credit Card Required
            </span>
          </div>
        </div>

        <!-- Interactive Hero Canvas Preview -->
        <div class="mt-6 rounded-3xl bg-slate-900/80 border border-slate-800/80 p-2 sm:p-4 shadow-2xl shadow-black/80 backdrop-blur-2xl">
          <div class="flex items-center justify-between px-4 py-2 border-b border-slate-800/80 text-xs text-slate-400">
            <div class="flex items-center gap-2 font-mono">
              <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
              <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
              <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
              <span class="ml-2 font-bold text-slate-300">Live Simulator Preview · Age 25 User Model</span>
            </div>
            <Link href="/simulator" class="text-cyan-400 hover:text-cyan-300 font-bold flex items-center gap-1">
              Launch Fullscreen ↗
            </Link>
          </div>

          <div class="p-2 sm:p-4">
            <LifespanTrajectoryChart :simulation-data="previewSimulation" />
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Four Core Pillars Section -->
    <section id="features" class="py-24 bg-slate-950 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-cyan-400 mb-2">
            The Multi-Pillar Vector Engine
          </h2>
          <h3 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            How Every Action Shapes Your 120-Year Horizon
          </h3>
          <p class="mt-4 text-sm text-slate-400">
            Small compounding daily habits create massive divergences over 20, 40, and 60-year trajectories.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Card 1: Health & Biological Age -->
          <div class="p-6 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-emerald-500/20 shadow-xl relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl font-bold mb-4 shadow-lg shadow-emerald-950/50">
              ♥
            </div>
            <h4 class="text-lg font-bold text-white mb-2">Health & Biological Age</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Gompertz mortality modeling. Consistent daily workouts decrease biological age by up to 4.5 years, while smoking elevates cardiac risk inflection points at Age ~52.
            </p>
            <div class="mt-4 text-[11px] font-mono text-emerald-400 font-bold">
              WHO Life Tables + Framingham
            </div>
          </div>

          <!-- Card 2: Wealth & S&P 500 Compounding -->
          <div class="p-6 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-amber-500/20 shadow-xl relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-amber-950/60 border border-amber-500/30 text-amber-400 flex items-center justify-center text-xl font-bold mb-4 shadow-lg shadow-amber-950/50">
              ◆
            </div>
            <h4 class="text-lg font-bold text-white mb-2">Wealth & FIRE Timeline</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              7.5% real inflation-adjusted compounding. Predicts the exact age your investment portfolio achieves the 4% safe withdrawal rule for complete financial independence.
            </p>
            <div class="mt-4 text-[11px] font-mono text-amber-400 font-bold">
              S&P 500 Historical ROI + 4% Rule
            </div>
          </div>

          <!-- Card 3: Career Velocity -->
          <div class="p-6 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-blue-500/20 shadow-xl relative overflow-hidden group hover:border-blue-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-blue-950/60 border border-blue-500/30 text-blue-400 flex items-center justify-center text-xl font-bold mb-4 shadow-lg shadow-blue-950/50">
              ▲
            </div>
            <h4 class="text-lg font-bold text-white mb-2">Career Velocity & Skills</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Degrees provide foundational salary tiers, while soft skill & technical workshops apply a 15–25% promotion velocity multiplier, shortening executive promotion cycles.
            </p>
            <div class="mt-4 text-[11px] font-mono text-blue-400 font-bold">
              BLS & OECD Career Studies
            </div>
          </div>

          <!-- Card 4: Counterfactual What-Ifs -->
          <div class="p-6 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-purple-500/20 shadow-xl relative overflow-hidden group hover:border-purple-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-purple-950/60 border border-purple-500/30 text-purple-400 flex items-center justify-center text-xl font-bold mb-4 shadow-lg shadow-purple-950/50">
              🌿
            </div>
            <h4 class="text-lg font-bold text-white mb-2">"What-If" Decision Forks</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Fork alternate timeline lanes to explore counterfactual decisions: "What if I switch careers?", "What if I quit smoking today?", or "What if I double monthly investments?".
            </p>
            <div class="mt-4 text-[11px] font-mono text-purple-400 font-bold">
              Multi-Lane Decision Tree
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Actuarial Public Data Comparison Section -->
    <section id="actuarial-models" class="py-20 bg-slate-900/40 border-y border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-cyan-950 border border-cyan-800 text-cyan-300 text-xs font-mono font-bold mb-4">
              Ground Truth Science
            </div>
            <h3 class="text-3xl font-extrabold text-white tracking-tight leading-snug">
              Powered by Verified Actuarial Life Tables & Meta-Analyses
            </h3>
            <p class="mt-4 text-sm text-slate-300 leading-relaxed">
              Unlike generic productivity apps that give arbitrary scores, LifeCurv utilizes mathematical models calibrated to peer-reviewed public health and financial datasets:
            </p>

            <div class="mt-6 space-y-4 text-xs">
              <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                <span class="text-emerald-400 text-base font-bold">✓</span>
                <div>
                  <div class="font-bold text-white">World Health Organization (WHO) Global Mortality Tables</div>
                  <div class="text-slate-400 mt-0.5">Provides country-specific baseline life expectancy and age-stratified morbidity risks.</div>
                </div>
              </div>

              <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                <span class="text-amber-400 text-base font-bold">✓</span>
                <div>
                  <div class="font-bold text-white">S&P 500 Historical Compounding & Trinity 4% Safe Withdrawal</div>
                  <div class="text-slate-400 mt-0.5">Calculates real asset growth accounting for 2.5% inflation and variable contribution cadences.</div>
                </div>
              </div>

              <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                <span class="text-purple-400 text-base font-bold">✓</span>
                <div>
                  <div class="font-bold text-white">Epidemiological Habit Meta-Analyses</div>
                  <div class="text-slate-400 mt-0.5">Quantifies exact pack-year cardiovascular risk thresholds and exercise longevity dividends.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actuarial Comparison Table -->
          <div class="rounded-2xl bg-slate-950 border border-slate-800 p-6 shadow-2xl">
            <h4 class="text-sm font-bold text-white uppercase tracking-wider font-mono mb-4 text-cyan-400">
              Sample Habit Trajectory Impact Matrix
            </h4>

            <div class="overflow-x-auto text-xs font-mono">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-slate-800 text-slate-400">
                    <th class="pb-2">User Habit / Action</th>
                    <th class="pb-2">Lifespan Delta</th>
                    <th class="pb-2">Wealth / Career Impact</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                  <tr>
                    <td class="py-2.5 font-bold text-white">🏋️ 6-Mo Daily Workout</td>
                    <td class="py-2.5 text-emerald-400 font-bold">+3.2 Years</td>
                    <td class="py-2.5 text-slate-400">-35% Cardiac Risk</td>
                  </tr>
                  <tr>
                    <td class="py-2.5 font-bold text-white">🚭 Smoking 1 Pack/Day</td>
                    <td class="py-2.5 text-rose-400 font-bold">-9.5 Years</td>
                    <td class="py-2.5 text-rose-400">Risk Spike @ Age 52</td>
                  </tr>
                  <tr>
                    <td class="py-2.5 font-bold text-white">📈 Invest $500/mo @ 25</td>
                    <td class="py-2.5 text-slate-400">—</td>
                    <td class="py-2.5 text-amber-400 font-bold">FIRE @ Age 52 ($1.2M)</td>
                  </tr>
                  <tr>
                    <td class="py-2.5 font-bold text-white">🗣️ Executive Workshop</td>
                    <td class="py-2.5 text-slate-400">—</td>
                    <td class="py-2.5 text-blue-400 font-bold">+22% Promo Velocity</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Call To Action Footer -->
    <footer class="py-16 bg-slate-950 border-t border-slate-800 text-center relative overflow-hidden">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
          Ready to Map Your Future?
        </h2>
        <p class="mt-4 text-sm text-slate-400 max-w-xl mx-auto">
          Create your account in under 60 seconds, choose your preferred currency, and begin exploring your 0–120 lifespan trajectory.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link
            href="/register"
            class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 font-extrabold text-white text-sm shadow-xl shadow-cyan-500/30 transition"
          >
            Start Your Simulation
          </Link>
          <Link
            href="/demo-login"
            class="px-6 py-3.5 rounded-2xl bg-slate-900 border border-slate-700 text-slate-300 font-bold text-sm hover:bg-slate-800 transition"
          >
            1-Click Demo Login
          </Link>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-900 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div>© 2026 Lifecurv. All rights reserved.</div>
          <div class="flex items-center gap-6 font-mono text-[11px]">
            <span>Laravel 13</span>
            <span>Vue 3</span>
            <span>Apache ECharts</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import LifespanTrajectoryChart from '@/Components/charts/LifespanTrajectoryChart.vue';

defineProps({
  previewSimulation: {
    type: Object,
    default: () => ({}),
  },
});
</script>

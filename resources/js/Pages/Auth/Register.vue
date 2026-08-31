<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-cyan-500 selection:text-white relative overflow-hidden">
    <!-- Ambient Glow -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-gradient-to-tr from-cyan-500/10 via-indigo-500/10 to-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-lg relative z-10">
      <!-- Logo -->
      <div class="flex justify-center">
        <Link href="/" class="flex items-center space-x-3 group">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 flex items-center justify-center shadow-xl shadow-cyan-500/25 group-hover:scale-105 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
        </Link>
      </div>

      <h2 class="mt-6 text-center text-3xl font-black tracking-tight text-white">
        Start Your Life Trajectory Model
      </h2>
      <p class="mt-2 text-center text-xs text-slate-400">
        Already have an account?
        <Link href="/login" class="font-bold text-cyan-400 hover:text-cyan-300 transition">
          Sign in
        </Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg relative z-10 px-4">
      <div class="rounded-3xl bg-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-2xl backdrop-blur-2xl">
        <!-- Registration Form -->
        <form @submit.prevent="submit" class="space-y-4 text-xs">
          <div>
            <label class="block text-slate-300 font-semibold mb-1">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Mazharul Islam"
              class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs transition"
              :class="{ 'border-rose-500': form.errors.name }"
            />
            <div v-if="form.errors.name" class="text-rose-400 text-[11px] mt-1 font-medium">
              {{ form.errors.name }}
            </div>
          </div>

          <div>
            <label class="block text-slate-300 font-semibold mb-1">Email address</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs transition"
              :class="{ 'border-rose-500': form.errors.email }"
            />
            <div v-if="form.errors.email" class="text-rose-400 text-[11px] mt-1 font-medium">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Age and Currency Setup (Crucial for 0-120 Simulation Calibration) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3.5 rounded-2xl bg-slate-950 border border-cyan-500/20">
            <div>
              <label class="block text-cyan-400 font-bold mb-1 flex items-center gap-1.5">
                <span>📍</span>
                <span>Current Age (Years)</span>
              </label>
              <input
                v-model.number="form.current_age"
                type="number"
                step="0.5"
                min="10"
                max="100"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono font-bold focus:outline-none focus:border-cyan-500 text-xs"
              />
              <p class="text-[10px] text-slate-500 mt-1">Anchor point for 0–120 X-axis</p>
            </div>

            <div>
              <label class="block text-amber-400 font-bold mb-1 flex items-center gap-1.5">
                <span>💰</span>
                <span>Preferred Currency</span>
              </label>
              <select
                v-model="form.currency"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono font-bold focus:outline-none focus:border-amber-500 text-xs"
              >
                <option v-for="curr in currencies" :key="curr.code" :value="curr.code">
                  {{ curr.label }}
                </option>
              </select>
              <p class="text-[10px] text-slate-500 mt-1">Used across net worth calculations</p>
            </div>
          </div>

          <!-- Current Starting Assets Valuation -->
          <div>
            <label class="block text-emerald-400 font-semibold mb-1 flex items-center gap-1.5">
              <span>💎</span>
              <span>Current Assets / Savings Valuation (Optional)</span>
            </label>
            <input
              v-model.number="form.current_savings"
              type="number"
              step="any"
              min="0"
              placeholder="e.g. 500000"
              class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-mono focus:outline-none focus:border-emerald-500 text-xs transition"
            />
            <p class="text-[11px] text-slate-500 mt-1">Starting baseline net worth from which compound growth begins.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-slate-300 font-semibold mb-1">Password</label>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs transition"
                :class="{ 'border-rose-500': form.errors.password }"
              />
              <div v-if="form.errors.password" class="text-rose-400 text-[11px] mt-1 font-medium">
                {{ form.errors.password }}
              </div>
            </div>

            <div>
              <label class="block text-slate-300 font-semibold mb-1">Confirm Password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs transition"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-5 py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 via-indigo-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/25 transition disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span>{{ form.processing ? 'Creating Your Life Model...' : 'Initialize My Trajectory Engine' }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { SUPPORTED_CURRENCIES } from '@/Utils/currency';

const currencies = SUPPORTED_CURRENCIES;

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  current_age: 25.0,
  current_savings: null,
  currency: 'USD',
  country_code: 'USA',
});

function submit() {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

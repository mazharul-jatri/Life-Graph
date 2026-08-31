<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-cyan-500 selection:text-white relative overflow-hidden">
    <!-- Ambient Glow -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-gradient-to-tr from-cyan-500/10 via-indigo-500/10 to-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
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
        Sign in to Lifecurv
      </h2>
      <p class="mt-2 text-center text-xs text-slate-400">
        Or
        <Link href="/register" class="font-bold text-cyan-400 hover:text-cyan-300 transition">
          create a new simulation account
        </Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
      <div class="rounded-3xl bg-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-2xl backdrop-blur-2xl">
        <!-- 1-Click Demo Login Banner -->
        <div class="mb-6 p-3.5 rounded-2xl bg-gradient-to-r from-purple-950/60 to-indigo-950/60 border border-purple-800/60 flex items-center justify-between gap-3">
          <div>
            <div class="text-xs font-bold text-purple-300 flex items-center gap-1.5">
              <span>⚡</span>
              <span>Instant Demo Account</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">Preloaded with 25-yr trajectory</div>
          </div>
          <Link
            href="/demo-login"
            class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/30 transition whitespace-nowrap"
          >
            1-Click Login
          </Link>
        </div>

        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-800"></div>
          </div>
          <div class="relative flex justify-center text-xs uppercase font-mono">
            <span class="bg-slate-900 px-3 text-slate-500">Or sign in with email</span>
          </div>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-4 text-xs">
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

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-slate-300 font-semibold">Password</label>
            </div>
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

          <div class="flex items-center justify-between text-slate-400 pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.remember"
                type="checkbox"
                class="rounded border-slate-700 bg-slate-950 text-cyan-500 focus:ring-0 cursor-pointer"
              />
              <span>Remember me</span>
            </label>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-4 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/25 transition disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span>{{ form.processing ? 'Signing In...' : 'Sign In to Dashboard' }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-md">
    <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl overflow-hidden">
      <!-- Modal Header -->
      <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-4">
        <div>
          <h3 class="text-lg font-bold text-white tracking-wide">Log Life Activity & Habit</h3>
          <p class="text-xs text-slate-400 mt-0.5">Vector input alters the 0–120 lifespan trajectory engine.</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition"
        >
          ✕
        </button>
      </div>

      <!-- Quick Preset Buttons -->
      <div class="mb-4">
        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2 font-mono">
          ⚡ Quick Presets
        </label>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <button
            type="button"
            @click="applyPreset('workout')"
            class="p-2.5 rounded-xl border border-emerald-500/30 bg-emerald-950/30 hover:bg-emerald-900/40 text-emerald-300 font-medium text-left transition flex items-center gap-2"
          >
            <span>🏋️</span>
            <div>
              <div class="font-bold">6-Mo Daily Workout</div>
              <div class="text-[10px] text-emerald-400/80">+3.2 yrs lifespan</div>
            </div>
          </button>

          <button
            type="button"
            @click="applyPreset('workshop')"
            class="p-2.5 rounded-xl border border-blue-500/30 bg-blue-950/30 hover:bg-blue-900/40 text-blue-300 font-medium text-left transition flex items-center gap-2"
          >
            <span>🗣️</span>
            <div>
              <div class="font-bold">Communication Workshop</div>
              <div class="text-[10px] text-blue-400/80">+22% promotion velocity</div>
            </div>
          </button>

          <button
            type="button"
            @click="applyPreset('degree')"
            class="p-2.5 rounded-xl border border-indigo-500/30 bg-indigo-950/30 hover:bg-indigo-900/40 text-indigo-300 font-medium text-left transition flex items-center gap-2"
          >
            <span>🎓</span>
            <div>
              <div class="font-bold">B.Sc. / M.Sc. Degree</div>
              <div class="text-[10px] text-indigo-400/80">Skill & Earnings baseline</div>
            </div>
          </button>

          <button
            type="button"
            @click="applyPreset('investment')"
            class="p-2.5 rounded-xl border border-amber-500/30 bg-amber-950/30 hover:bg-amber-900/40 text-amber-300 font-medium text-left transition flex items-center gap-2"
          >
            <span>📈</span>
            <div>
              <div class="font-bold">Invest $500/month</div>
              <div class="text-[10px] text-amber-400/80">Compound 7.5% ROI</div>
            </div>
          </button>
        </div>
      </div>

      <!-- Activity Form -->
      <form @submit.prevent="submitForm" class="space-y-4 text-xs">
        <div>
          <label class="block text-slate-300 font-medium mb-1">Activity / Habit Title</label>
          <input
            v-model="form.title"
            required
            type="text"
            placeholder="e.g. Daily Calisthenics & Cardio"
            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-slate-300 font-medium mb-1">Life Pillar</label>
            <select
              v-model="form.pillar"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
            >
              <option value="health">Health & Vitality</option>
              <option value="wealth">Wealth & Finances</option>
              <option value="career">Career & Mastery</option>
              <option value="personal">Personal / Life</option>
            </select>
          </div>

          <div>
            <label class="block text-slate-300 font-medium mb-1">Frequency</label>
            <select
              v-model="form.frequency"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="one_time">One-time Event</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-slate-300 font-medium mb-1">Start Age</label>
            <input
              v-model.number="form.start_age"
              type="number"
              step="0.5"
              min="0"
              max="120"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
            />
          </div>

          <div>
            <label class="block text-slate-300 font-medium mb-1">Duration (Months)</label>
            <input
              v-model.number="form.duration_months"
              type="number"
              min="1"
              max="600"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
            />
          </div>

          <div>
            <label class="block text-slate-300 font-medium mb-1">Amount / Intensity</label>
            <input
              v-model.number="form.intensity_or_amount"
              type="number"
              step="any"
              placeholder="$ / mins / score"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-500"
            />
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800 mt-5">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-5 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 font-bold text-white shadow-lg shadow-cyan-500/25 transition disabled:opacity-50"
          >
            {{ isSubmitting ? 'Simulating...' : 'Log & Recalculate' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  isOpen: Boolean,
  currentAge: {
    type: Number,
    default: 25.0,
  },
});

const emit = defineEmits(['close']);
const isSubmitting = ref(false);

const form = reactive({
  title: '',
  activity_type: 'workout',
  pillar: 'health',
  frequency: 'daily',
  intensity_or_amount: 45,
  start_age: props.currentAge || 25.0,
  duration_months: 6,
});

function applyPreset(type) {
  if (type === 'workout') {
    form.title = 'Daily Cardio & Strength Routine';
    form.activity_type = 'workout';
    form.pillar = 'health';
    form.frequency = 'daily';
    form.intensity_or_amount = 45;
    form.duration_months = 6;
  } else if (type === 'workshop') {
    form.title = 'Executive Communication & Negotiation Workshop';
    form.activity_type = 'workshop';
    form.pillar = 'career';
    form.frequency = 'one_time';
    form.intensity_or_amount = 1;
    form.duration_months = 2;
  } else if (type === 'degree') {
    form.title = 'B.Sc. Degree in Engineering';
    form.activity_type = 'education';
    form.pillar = 'career';
    form.frequency = 'daily';
    form.intensity_or_amount = 4;
    form.duration_months = 48;
  } else if (type === 'investment') {
    form.title = 'Monthly S&P 500 DCA Investment';
    form.activity_type = 'investment';
    form.pillar = 'wealth';
    form.frequency = 'monthly';
    form.intensity_or_amount = 500;
    form.duration_months = 36;
  }
}

function submitForm() {
  isSubmitting.value = true;
  router.post('/activities', form, {
    onSuccess: () => {
      isSubmitting.value = false;
      emit('close');
    },
    onError: () => {
      isSubmitting.value = false;
    },
  });
}
</script>

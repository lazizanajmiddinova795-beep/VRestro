<template>
  <div class="min-h-screen relative overflow-x-hidden">
    <AnimatedBackground />
    <router-view class="relative z-10" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import AnimatedBackground from '@/components/AnimatedBackground.vue';
import { useCashierStore } from '@/stores/cashier';
import { useSettingsStore } from '@/stores/settings';

onMounted(() => {
  try {
    const settingsStore = useSettingsStore();
    settingsStore.applySettings();
    settingsStore.fetchSettings();

    const cashierStore = useCashierStore();
    if (cashierStore && cashierStore.applyLocalSettings) {
      cashierStore.applyLocalSettings();
    }
  } catch (e) {
    console.warn('Initial settings apply:', e);
  }
});
</script>

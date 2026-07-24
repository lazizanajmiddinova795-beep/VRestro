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

onMounted(() => {
  try {
    const cashierStore = useCashierStore();
    if (cashierStore && cashierStore.applyLocalSettings) {
      cashierStore.applyLocalSettings();
    }
  } catch (e) {
    console.warn('Initial settings apply:', e);
  }
});
</script>

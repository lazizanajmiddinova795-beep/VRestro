<template>
  <div class="min-h-screen relative overflow-x-hidden">
    <AnimatedBackground />
    <router-view class="relative z-10" />
  </div>
</template>

<script setup>
import AnimatedBackground from '@/components/AnimatedBackground.vue';
</script>

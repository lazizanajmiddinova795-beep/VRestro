<template>
  <div 
    class="fixed inset-0 pointer-events-none z-0 overflow-hidden select-none transition-colors duration-700 ease-in-out"
    :class="isDark ? 'bg-[#060913]' : 'bg-[#eef2f7]'"
    @mousemove="handleMouseMove"
  >
    <!-- 1. SPATIAL UI: 3D Grid & Spatial Depth Perspective Layer -->
    <div 
      class="absolute inset-0 spatial-grid-pattern opacity-40 transition-transform duration-700 ease-out"
      :style="spatialTransform"
    ></div>

    <!-- Ambient Glowing Orbs / Mesh Blobs (Glassmorphism backdrop illumination) -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Indigo/Violet Primary Ambient Blob -->
      <div 
        class="blob blob-indigo transition-opacity duration-700"
        :class="isDark ? 'opacity-50' : 'opacity-25'"
      ></div>

      <!-- Cyan/Teal Secondary Ambient Blob -->
      <div 
        class="blob blob-cyan transition-opacity duration-700"
        :class="isDark ? 'opacity-45' : 'opacity-25'"
      ></div>

      <!-- Pink/Magenta Accent Blob -->
      <div 
        class="blob blob-pink transition-opacity duration-700"
        :class="isDark ? 'opacity-40' : 'opacity-25'"
      ></div>

      <!-- Amber/Orange Warm Blob -->
      <div 
        class="blob blob-amber transition-opacity duration-700"
        :class="isDark ? 'opacity-35' : 'opacity-30'"
      ></div>
    </div>

    <!-- 2. CLAYMORPHISM: Floating 3D Inflated Volumetric Clay Orbs -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <!-- Purple 3D Clay Orb -->
      <div 
        class="clay-orb clay-orb-purple animate-float-slow transition-opacity duration-700"
        :class="isDark ? 'opacity-90' : 'opacity-60'"
        :style="parallaxTransform(15)"
      ></div>

      <!-- Cyan 3D Clay Orb -->
      <div 
        class="clay-orb clay-orb-cyan animate-float-reverse transition-opacity duration-700"
        :class="isDark ? 'opacity-90' : 'opacity-60'"
        :style="parallaxTransform(-20)"
      ></div>

      <!-- Pink 3D Clay Orb -->
      <div 
        class="clay-orb clay-orb-pink animate-float-medium transition-opacity duration-700"
        :class="isDark ? 'opacity-90' : 'opacity-60'"
        :style="parallaxTransform(25)"
      ></div>
    </div>

    <!-- 4. GLASSMORPHISM: Frosted Glass Overlay Rings & Ambient Light Lines -->
    <div class="absolute inset-0 pointer-events-none">
      <div 
        class="absolute -top-32 -left-32 w-96 h-96 rounded-full border border-white/10 backdrop-blur-[2px] transition-all duration-700"
        :class="isDark ? 'border-white/10' : 'border-slate-300/40'"
      ></div>
      <div 
        class="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full border border-white/10 backdrop-blur-[2px] transition-all duration-700"
        :class="isDark ? 'border-indigo-500/10' : 'border-indigo-300/30'"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const mouseX = ref(0);
const mouseY = ref(0);
const isDark = ref(true);

let observer = null;

const checkTheme = () => {
  const html = document.documentElement;
  isDark.value = html.classList.contains('dark') || !html.classList.contains('light-theme');
};

const handleMouseMove = (e) => {
  const { innerWidth, innerHeight } = window;
  mouseX.value = (e.clientX / innerWidth - 0.5) * 2; // -1 to 1
  mouseY.value = (e.clientY / innerHeight - 0.5) * 2; // -1 to 1
};

const spatialTransform = computed(() => {
  const rotateX = mouseY.value * 4;
  const rotateY = -mouseX.value * 4;
  return `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
});

const parallaxTransform = (intensity) => {
  const tx = mouseX.value * intensity;
  const ty = mouseY.value * intensity;
  return {
    transform: `translate3d(${tx}px, ${ty}px, 0)`
  };
};

onMounted(() => {
  checkTheme();
  
  // Watch for class changes on <html> element for live theme changes
  observer = new MutationObserver(() => {
    checkTheme();
  });

  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
});

onUnmounted(() => {
  if (observer) {
    observer.disconnect();
  }
});
</script>

<style scoped>
/* ==========================================================================
   BLOB ANIMATIONS & GRADIENTS (Glassmorphism backdrop)
   ========================================================================== */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(130px);
  will-change: transform;
  animation: move-blob 24s infinite ease-in-out;
}

.blob-indigo {
  background: radial-gradient(circle, #4f46e5 0%, #312e81 100%);
  width: 55vw;
  height: 55vw;
  top: -15%;
  left: -15%;
  animation-duration: 28s;
}

.blob-cyan {
  background: radial-gradient(circle, #06b6d4 0%, #0e7490 100%);
  width: 48vw;
  height: 48vw;
  bottom: -15%;
  right: -10%;
  animation-duration: 22s;
  animation-delay: -6s;
}

.blob-pink {
  background: radial-gradient(circle, #ec4899 0%, #831843 100%);
  width: 42vw;
  height: 42vw;
  top: 25%;
  left: 45%;
  animation-duration: 32s;
  animation-delay: -12s;
}

.blob-amber {
  background: radial-gradient(circle, #f59e0b 0%, #b45309 100%);
  width: 38vw;
  height: 38vw;
  bottom: 10%;
  left: -10%;
  animation-duration: 26s;
  animation-delay: -18s;
}

@keyframes move-blob {
  0%, 100% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(6vw, -6vh) scale(1.08);
  }
  66% {
    transform: translate(-6vw, 6vh) scale(0.94);
  }
}

/* ==========================================================================
   CLAYMORPHISM: Volumetric 3D Inflated Orbs & Shapes
   ========================================================================== */
.clay-orb {
  position: absolute;
  border-radius: 50%;
  will-change: transform;
}

.clay-orb-purple {
  width: 140px;
  height: 140px;
  top: 15%;
  left: 12%;
  background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
  box-shadow: 
    12px 16px 28px rgba(99, 102, 241, 0.35),
    inset -8px -8px 16px rgba(30, 27, 75, 0.6),
    inset 8px 8px 16px rgba(255, 255, 255, 0.45);
}

.clay-orb-cyan {
  width: 110px;
  height: 110px;
  bottom: 20%;
  right: 15%;
  background: linear-gradient(135deg, #38bdf8 0%, #06b6d4 100%);
  box-shadow: 
    10px 14px 24px rgba(6, 182, 212, 0.35),
    inset -6px -6px 14px rgba(21, 94, 117, 0.6),
    inset 6px 6px 14px rgba(255, 255, 255, 0.5);
}

.clay-orb-pink {
  width: 90px;
  height: 90px;
  bottom: 35%;
  left: 20%;
  background: linear-gradient(135deg, #f472b6 0%, #e11d48 100%);
  box-shadow: 
    8px 12px 20px rgba(225, 29, 72, 0.35),
    inset -5px -5px 10px rgba(136, 19, 55, 0.6),
    inset 5px 5px 10px rgba(255, 255, 255, 0.5);
}

.clay-pill-light {
  background: #eef2f7;
  box-shadow: 
    8px 10px 20px rgba(166, 180, 200, 0.4),
    inset -4px -4px 8px rgba(0, 0, 0, 0.06),
    inset 4px 4px 8px rgba(255, 255, 255, 0.9);
}

.clay-pill-dark {
  background: #111827;
  box-shadow: 
    8px 10px 20px rgba(0, 0, 0, 0.6),
    inset -4px -4px 8px rgba(0, 0, 0, 0.7),
    inset 4px 4px 8px rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* ==========================================================================
   NEOMORPHISM & SKEUOMORPHISM CARDS
   ========================================================================== */
.neo-card-light {
  background: #eef2f7;
  box-shadow: 
    10px 10px 22px #d1d9e6,
    -10px -10px 22px #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.8);
}

.neo-card-dark {
  background: #0d1322;
  box-shadow: 
    10px 10px 24px rgba(0, 0, 0, 0.7),
    -10px -10px 24px rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.neo-inset-light {
  background: #e6ecf5;
  box-shadow: inset 3px 3px 6px #d1d9e6, inset -3px -3px 6px #ffffff;
}

.neo-inset-dark {
  background: #080c16;
  box-shadow: inset 3px 3px 6px rgba(0, 0, 0, 0.8), inset -3px -3px 6px rgba(255, 255, 255, 0.03);
}

.neo-groove-light {
  background: #e2e8f0;
  box-shadow: inset 2px 2px 5px #cbd5e1, inset -2px -2px 5px #ffffff;
}

.neo-groove-dark {
  background: #050811;
  box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.9), inset -2px -2px 5px rgba(255, 255, 255, 0.03);
}

/* Skeuomorphic Gloss Highlights */
.skeuo-gloss-light {
  background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0) 40%);
}

.skeuo-gloss-dark {
  background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 40%);
}

.skeuo-indicator {
  box-shadow: 
    0 2px 4px rgba(0, 0, 0, 0.2),
    inset 0 1px 1px rgba(255, 255, 255, 0.8),
    inset 0 -1px 2px rgba(0, 0, 0, 0.3);
}

/* ==========================================================================
   GLASSMORPHISM & SPATIAL UI COMBINED PANELS
   ========================================================================== */
.glass-spatial-light {
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.8);
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

.glass-spatial-dark {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 
    0 20px 50px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(99, 102, 241, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.clay-icon-box {
  box-shadow: 
    4px 6px 12px rgba(99, 102, 241, 0.4),
    inset -2px -2px 4px rgba(0, 0, 0, 0.3),
    inset 2px 2px 4px rgba(255, 255, 255, 0.5);
}

/* Floating Animations */
@keyframes float-slow {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-18px) rotate(6deg); }
}

@keyframes float-reverse {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(16px) rotate(-8deg); }
}

@keyframes float-medium {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-12px) scale(1.05); }
}

.animate-float-slow {
  animation: float-slow 8s infinite ease-in-out;
}

.animate-float-reverse {
  animation: float-reverse 10s infinite ease-in-out;
}

.animate-float-medium {
  animation: float-medium 7s infinite ease-in-out;
}
</style>

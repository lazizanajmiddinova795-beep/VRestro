<template>
  <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden select-none bg-slate-950">
    <!-- Fluid Glowing Mesh Blobs -->
    <div class="absolute inset-0">
      <!-- Indigo/Violet Blob -->
      <div class="blob blob-indigo"></div>
      
      <!-- Cyan/Teal Blob -->
      <div class="blob blob-cyan"></div>
      
      <!-- Pink/Magenta Blob -->
      <div class="blob blob-pink"></div>
      
      <!-- Purple Blob -->
      <div class="blob blob-purple"></div>
    </div>
  </div>
</template>

<style scoped>
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.22;
  animation: move-blob infinite ease-in-out;
  will-change: transform;
}

/* Bright Blob Colors & Initial Positions */
.blob-indigo {
  background: #4f46e5;
  width: 50vw;
  height: 50vw;
  top: -10%;
  left: -10%;
  animation-duration: 25s;
}

.blob-cyan {
  background: #06b6d4;
  width: 45vw;
  height: 45vw;
  bottom: -10%;
  right: -5%;
  animation-duration: 20s;
  animation-delay: -5s;
}

.blob-pink {
  background: #db2777;
  width: 40vw;
  height: 40vw;
  top: 30%;
  left: 50%;
  animation-duration: 28s;
  animation-delay: -10s;
}

.blob-purple {
  background: #7c3aed;
  width: 45vw;
  height: 45vw;
  bottom: 20%;
  left: -10%;
  animation-duration: 22s;
  animation-delay: -15s;
}

/* Fluid movement keyframes */
@keyframes move-blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(8vw, -8vh) scale(1.1);
  }
  66% {
    transform: translate(-8vw, 8vh) scale(0.95);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>

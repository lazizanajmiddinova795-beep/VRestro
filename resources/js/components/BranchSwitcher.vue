<template>
  <div class="relative" v-if="authStore.user?.is_superadmin">
    <button
      @click="open = !open"
      class="flex items-center space-x-2 px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200/60 transition duration-200 text-sm"
    >
      <Building2 class="w-4 h-4 text-indigo-600" />
      <span class="font-bold text-slate-700 max-w-[150px] truncate">
        {{ activeBranchName }}
      </span>
      <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" />
    </button>

    <!-- Dropdown -->
    <div
      v-if="open"
      class="absolute left-0 mt-2 w-64 rounded-2xl border border-slate-200 bg-white shadow-xl z-50 py-2 animate-fadeIn"
    >
      <!-- Global view option -->
      <button
        @click="handleClearContext"
        class="w-full text-left px-4 py-2.5 text-sm font-bold transition duration-150 flex items-center space-x-2"
        :class="!branchStore.activeBranch ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
      >
        <Globe class="w-4 h-4" />
        <span>Barcha filiallar</span>
      </button>

      <div class="border-t border-slate-100 my-1"></div>

      <!-- Branch list -->
      <button
        v-for="branch in branchStore.branches"
        :key="branch.id"
        @click="handleSwitch(branch)"
        class="w-full text-left px-4 py-2.5 text-sm font-bold transition duration-150 flex items-center justify-between"
        :class="branchStore.activeBranch?.id === branch.id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
      >
        <span class="flex items-center space-x-2">
          <Building2 class="w-4 h-4" />
          <span class="truncate max-w-[160px]">{{ branch.name }}</span>
        </span>
        <span
          v-if="branchStore.activeBranch?.id === branch.id"
          class="w-2 h-2 rounded-full bg-indigo-600"
        ></span>
      </button>

      <div class="border-t border-slate-100 my-1"></div>

      <!-- Manage branches link -->
      <router-link
        to="/branches"
        @click="open = false"
        class="w-full text-left px-4 py-2.5 text-xs font-extrabold text-indigo-600 hover:bg-indigo-50 transition duration-150 flex items-center space-x-2 block"
      >
        <Settings2 class="w-3.5 h-3.5" />
        <span>Filiallarni boshqarish</span>
      </router-link>
    </div>

    <!-- Click outside to close -->
    <div v-if="open" class="fixed inset-0 z-40" @click="open = false"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Building2, ChevronDown, Globe, Settings2 } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useBranchStore } from '@/stores/branch';

const authStore = useAuthStore();
const branchStore = useBranchStore();
const open = ref(false);

const activeBranchName = computed(() => {
  if (branchStore.activeBranch) {
    return branchStore.activeBranch.name;
  }
  return 'Barcha filiallar';
});

const handleSwitch = async (branch) => {
  open.value = false;
  await branchStore.switchBranch(branch.id);
  window.location.reload();
};

const handleClearContext = async () => {
  open.value = false;
  await branchStore.clearContext();
  window.location.reload();
};

onMounted(() => {
  if (authStore.user?.is_superadmin) {
    branchStore.fetchBranches();
  }
});
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.15s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

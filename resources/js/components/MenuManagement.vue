<template>
  <div class="flex-grow p-6 flex flex-col md:flex-row h-screen overflow-hidden gap-6">
    
    <!-- Left Column: Categories Management -->
    <aside class="w-full md:w-80 backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 flex flex-col justify-between shrink-0 h-full">
      <div class="space-y-6 overflow-hidden flex flex-col h-full">
        <!-- Header -->
        <div class="flex justify-between items-center shrink-0">
          <div>
            <h2 class="text-lg font-bold text-white tracking-wide">Kategoriyalar</h2>
            <p class="text-3xs text-slate-400">Menyu bo'limlarini boshqarish</p>
          </div>
          <button 
            @click="openCategoryModal()"
            class="p-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg transition"
            title="Kategoriya qo'shish"
          >
            <Plus class="w-4 h-4" />
          </button>
        </div>

        <!-- Categories List -->
        <div class="overflow-y-auto flex-grow pr-1 space-y-1.5">
          <!-- All Categories Selector -->
          <button 
            @click="selectCategory(null)"
            class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold flex justify-between items-center transition"
            :class="!menuStore.selectedCategoryId ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <span>Barchasi</span>
            <span 
              class="px-2 py-0.5 rounded-full text-3xs font-bold border"
              :class="!menuStore.selectedCategoryId ? 'bg-white/20 border-white/10 text-white' : 'bg-slate-800 border-slate-700 text-slate-400'"
            >
              {{ totalFoodsCount }}
            </span>
          </button>

          <!-- Category items -->
          <div 
            v-for="cat in menuStore.categories" 
            :key="cat.id" 
            class="group w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition hover:bg-white/5"
            :class="menuStore.selectedCategoryId === cat.id ? 'bg-white/5 border border-white/10' : ''"
          >
            <button 
              @click="selectCategory(cat.id)"
              class="text-left flex-grow text-sm font-semibold truncate"
              :class="menuStore.selectedCategoryId === cat.id ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'"
            >
              {{ cat.name }}
            </button>
            
            <div class="flex items-center space-x-1.5 shrink-0 ml-2">
              <span 
                class="px-2 py-0.5 rounded-full text-3xs font-bold border bg-slate-800 border-slate-700 text-slate-400"
              >
                {{ cat.foods_count || 0 }}
              </span>
              
              <!-- Inline edit actions -->
              <button 
                @click="openCategoryModal(cat)"
                class="p-1 rounded bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white opacity-0 group-hover:opacity-100 transition"
              >
                <Edit3 class="w-3.5 h-3.5" />
              </button>
              <button 
                @click="handleDeleteCategory(cat)"
                class="p-1 rounded bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white opacity-0 group-hover:opacity-100 transition"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Right Column: Foods Grid -->
    <main class="flex-grow flex flex-col h-full overflow-hidden">
      <!-- Search & Add Row -->
      <div class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
        <!-- Search bar -->
        <div class="relative w-full sm:w-80">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <Search class="w-4 h-4" />
          </span>
          <input 
            v-model="searchQuery"
            @input="triggerSearch"
            type="text" 
            placeholder="Taomlarni qidirish..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm placeholder-slate-500 text-white focus:outline-none transition"
          />
        </div>

        <!-- Add Food button -->
        <button 
          @click="openFoodModal()"
          class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.01] transition-all flex items-center justify-center space-x-2"
        >
          <Plus class="w-4.5 h-4.5" />
          <span>Yangi Taom Qo'shish</span>
        </button>
      </div>

      <!-- Foods List Grid -->
      <div v-if="menuStore.loading" class="flex-grow flex flex-col items-center justify-center space-y-4">
        <Loader2 class="w-10 h-10 text-indigo-500 animate-spin" />
        <p class="text-slate-400 text-xs font-medium animate-pulse">Menyu yuklanmoqda...</p>
      </div>

      <div v-else class="flex-grow overflow-y-auto pr-1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
          <!-- Food Card -->
          <div 
            v-for="food in menuStore.foods" 
            :key="food.id"
            class="backdrop-blur-md bg-slate-900/40 border border-white/5 rounded-3xl overflow-hidden hover:border-white/10 transition duration-300 flex flex-col justify-between"
          >
            <!-- Image Area -->
            <div class="h-44 relative bg-slate-950 flex items-center justify-center overflow-hidden border-b border-white/5">
              <img 
                v-if="food.image_url" 
                :src="food.image_url" 
                :alt="food.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-slate-600 flex flex-col items-center space-y-2">
                <ChefHat class="w-12 h-12 stroke-[1.2]" />
                <span class="text-3xs uppercase tracking-wider text-slate-500 font-semibold">Rasm yo'q</span>
              </div>
              
              <!-- Price Tag -->
              <div class="absolute right-4 bottom-4 px-3 py-1 rounded-xl bg-slate-950/80 border border-white/10 text-xs font-bold text-white backdrop-blur">
                {{ formatCurrency(food.price) }}
              </div>
            </div>

            <!-- Details -->
            <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
              <div class="space-y-1.5">
                <div class="flex justify-between items-start gap-2">
                  <h3 class="text-sm font-bold text-white leading-snug truncate" :title="food.name">
                    {{ food.name }}
                  </h3>
                  <span class="shrink-0 text-3xs bg-white/5 text-slate-400 border border-white/10 px-2 py-0.5 rounded-full font-medium">
                    {{ food.category?.name }}
                  </span>
                </div>
                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                  {{ food.description || 'Taomga izoh berilmagan.' }}
                </p>
              </div>

              <!-- Available Switch & Actions -->
              <div class="border-t border-white/5 pt-4 flex items-center justify-between">
                <!-- Toggle Availability -->
                <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                  <input 
                    type="checkbox" 
                    :checked="food.is_available" 
                    @change="handleToggleAvailable(food)"
                    class="sr-only peer"
                  />
                  <div class="w-8 h-4 bg-slate-800 border border-slate-700 rounded-full peer peer-checked:bg-emerald-600 peer-checked:border-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4 peer-checked:after:bg-white relative"></div>
                  <span class="text-3xs font-bold tracking-wider uppercase" :class="food.is_available ? 'text-emerald-400' : 'text-slate-500'">
                    {{ food.is_available ? 'Mavjud' : 'Tugagan' }}
                  </span>
                </label>

                <!-- Actions -->
                <div class="flex space-x-1.5">
                  <button 
                    @click="openFoodModal(food)"
                    class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-white transition duration-200"
                    title="Tahrirlash"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button 
                    @click="handleDeleteFood(food)"
                    class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition duration-200"
                    title="O'chirish"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Empty state -->
        <div v-if="menuStore.foods.length === 0" class="flex flex-col items-center justify-center py-24 space-y-3">
          <ChefHat class="w-12 h-12 text-slate-600" />
          <p class="text-slate-400 text-xs font-medium">Bu bo'limda taomlar mavjud emas</p>
        </div>
      </div>
    </main>

    <!-- Modal 1: Category Add/Edit -->
    <div 
      v-if="showCategoryModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showCategoryModal = false"
    >
      <div class="w-full max-w-sm backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-5 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-3">
          <h3 class="text-base font-bold text-white">
            {{ editingCategory ? 'Kategoriyani Tahrirlash' : 'Yangi Kategoriya' }}
          </h3>
          <button @click="showCategoryModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Kategoriya nomi</label>
            <input 
              v-model="categoryForm.name"
              type="text" 
              placeholder="Masalan, Milliy taomlar..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm placeholder-slate-500 text-white focus:outline-none transition"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
          <button @click="showCategoryModal = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
            Bekor qilish
          </button>
          <button 
            @click="submitCategoryForm"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>

    <!-- Modal 2: Food Add/Edit -->
    <div 
      v-if="showFoodModal" 
      class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center p-6"
      @click.self="showFoodModal = false"
    >
      <div class="w-full max-w-lg backdrop-blur-xl bg-slate-900/80 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6 animate-scaleIn">
        <div class="flex justify-between items-center border-b border-white/5 pb-4">
          <h3 class="text-base font-bold text-white">
            {{ editingFood ? 'Taomni Tahrirlash' : 'Yangi Taom Qo\'shish' }}
          </h3>
          <button @click="showFoodModal = false" class="p-1 rounded-lg bg-white/5 text-slate-400 hover:text-white transition">
            <X class="w-4.5 h-4.5" />
          </button>
        </div>

        <!-- Form fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[350px] overflow-y-auto pr-1">
          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Taom nomi</label>
            <input 
              v-model="foodForm.name"
              type="text" 
              placeholder="Masalan, Palov..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Narxi (UZS)</label>
            <input 
              v-model.number="foodForm.price"
              type="number" 
              placeholder="Narxi..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Kategoriya</label>
            <select 
              v-model="foodForm.category_id"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition appearance-none"
            >
              <option value="" disabled class="bg-slate-900 text-slate-500">Tanlang...</option>
              <option v-for="cat in menuStore.categories" :key="cat.id" :value="cat.id" class="bg-slate-900">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Mavjudlik holati</label>
            <div class="pt-2">
              <label class="flex items-center space-x-2.5 cursor-pointer">
                <input 
                  type="checkbox" 
                  v-model="foodForm.is_available" 
                  class="sr-only peer"
                />
                <div class="w-9 h-5 bg-slate-800 border border-slate-700 rounded-full peer peer-checked:bg-emerald-600 peer-checked:border-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4 peer-checked:after:bg-white relative"></div>
                <span class="text-xs font-medium text-slate-300">{{ foodForm.is_available ? 'Mavjud' : 'Mavjud emas' }}</span>
              </label>
            </div>
          </div>

          <div class="space-y-1.5 sm:col-span-2">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Taom haqida (Tavsif)</label>
            <textarea 
              v-model="foodForm.description"
              rows="2"
              placeholder="Tarkibi, tayyorlanish muddati yoki boshqa ma'lumotlar..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-950/40 border border-white/10 focus:border-indigo-500 text-sm text-white focus:outline-none transition"
            ></textarea>
          </div>

          <!-- Drag and drop image selector -->
          <div class="space-y-1.5 sm:col-span-2">
            <label class="text-3xs text-slate-400 font-bold uppercase tracking-wider">Taom rasmi</label>
            <div 
              @click="$refs.fileInput.click()"
              class="w-full border-2 border-dashed border-white/10 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer transition bg-white/2 hover:bg-white/5 space-y-2"
            >
              <input 
                ref="fileInput" 
                type="file" 
                class="hidden" 
                accept="image/*" 
                @change="handleFileChange"
              />
              <div v-if="imagePreview" class="w-24 h-24 rounded-lg overflow-hidden border border-white/10">
                <img :src="imagePreview" class="w-full h-full object-cover" />
              </div>
              <div v-else class="text-slate-500 flex flex-col items-center space-y-1">
                <UploadCloud class="w-8 h-8 stroke-[1.2]" />
                <span class="text-xxs font-medium">Rasm yuklash uchun bosing</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Actions -->
        <div class="border-t border-white/5 pt-4 flex justify-end space-x-2">
          <button @click="showFoodModal = false" class="px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold text-slate-300 transition">
            Bekor qilish
          </button>
          <button 
            @click="submitFoodForm"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Edit3, Trash2, Search, X, Loader2, ChefHat, UploadCloud 
} from 'lucide-vue-next';
import { useMenuStore } from '@/stores/menu';

const menuStore = useMenuStore();

// States
const searchQuery = ref('');
let searchTimeout = null;

const showCategoryModal = ref(false);
const editingCategory = ref(null);
const categoryForm = ref({ name: '' });

const showFoodModal = ref(false);
const editingFood = ref(null);
const foodForm = ref({
  name: '',
  price: 0,
  category_id: '',
  description: '',
  is_available: true
});
const imageFile = ref(null);
const imagePreview = ref(null);

// Lifecycle
onMounted(async () => {
  await menuStore.fetchCategories();
  await menuStore.fetchFoods();
});

// Computed Count calculations
const totalFoodsCount = computed(() => {
  return menuStore.categories.reduce((acc, c) => acc + (c.foods_count || 0), 0);
});

// Category actions
const selectCategory = async (id) => {
  menuStore.selectedCategoryId = id;
  await menuStore.fetchFoods({ search: searchQuery.value });
};

const triggerSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    await menuStore.fetchFoods({ search: searchQuery.value });
  }, 350);
};

const openCategoryModal = (cat = null) => {
  editingCategory.value = cat;
  categoryForm.value.name = cat ? cat.name : '';
  showCategoryModal.value = true;
};

const submitCategoryForm = async () => {
  if (!categoryForm.value.name.trim()) return;

  try {
    if (editingCategory.value) {
      await menuStore.updateCategory(editingCategory.value.id, categoryForm.value.name);
    } else {
      await menuStore.createCategory(categoryForm.value.name);
    }
    showCategoryModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleDeleteCategory = async (cat) => {
  if (!confirm(`"${cat.name}" kategoriyasini o'chirmoqchimisiz?`)) return;
  try {
    await menuStore.deleteCategory(cat.id);
  } catch (err) {
    alert(err.message);
  }
};

// Food actions
const openFoodModal = (food = null) => {
  editingFood.value = food;
  imageFile.value = null;
  imagePreview.value = food ? food.image_url : null;
  
  foodForm.value = food ? {
    name: food.name,
    price: parseFloat(food.price),
    category_id: food.category_id,
    description: food.description || '',
    is_available: food.is_available
  } : {
    name: '',
    price: '',
    category_id: menuStore.selectedCategoryId || '',
    description: '',
    is_available: true
  };
  
  showFoodModal.value = true;
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  imageFile.value = file;
  imagePreview.value = URL.createObjectURL(file);
};

const submitFoodForm = async () => {
  if (!foodForm.value.name.trim() || !foodForm.value.price || !foodForm.value.category_id) {
    alert('Barcha majburiy maydonlarni to\'ldiring.');
    return;
  }

  const formData = new FormData();
  formData.append('name', foodForm.value.name);
  formData.append('price', foodForm.value.price);
  formData.append('category_id', foodForm.value.category_id);
  formData.append('description', foodForm.value.description);
  formData.append('is_available', foodForm.value.is_available ? '1' : '0');
  
  if (imageFile.value) {
    formData.append('image', imageFile.value);
  }

  try {
    if (editingFood.value) {
      // Adding _method=PUT to bypass PHP file upload body limitations on PUT
      formData.append('_method', 'PUT');
      await menuStore.updateFood(editingFood.value.id, formData);
    } else {
      await menuStore.createFood(formData);
    }
    showFoodModal.value = false;
  } catch (err) {
    alert(err.message);
  }
};

const handleToggleAvailable = async (food) => {
  try {
    await menuStore.toggleFoodAvailability(food.id);
  } catch (err) {
    alert(err.message);
  }
};

const handleDeleteFood = async (food) => {
  if (!confirm(`"${food.name}" taomini menyudan o'chirmoqchimisiz?`)) return;
  try {
    await menuStore.deleteFood(food.id);
  } catch (err) {
    alert(err.message);
  }
};

// Utilities
const formatCurrency = (val) => {
  return new Intl.NumberFormat('uz-UZ').format(val) + ' UZS';
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.7rem;
}
.text-3xs {
  font-size: 0.6rem;
}
.animate-scaleIn {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>

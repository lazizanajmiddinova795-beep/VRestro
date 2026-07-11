<template>
  <div 
    class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm p-4"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-md bg-slate-900 border border-white/10 rounded-3xl p-6 space-y-6 shadow-2xl animate-slideUp overflow-y-auto max-h-[80vh] mb-20">
      <!-- Header Section -->
      <div class="flex justify-between items-start">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 rounded-xl bg-slate-950 flex items-center justify-center overflow-hidden border border-white/5 shrink-0">
            <img v-if="food.image_url" :src="food.image_url" :alt="food.name" class="w-full h-full object-cover" />
            <ChefHat v-else class="w-6 h-6 text-slate-600 stroke-[1.2]" />
          </div>
          <div>
            <h3 class="text-base font-bold text-white leading-snug">{{ food.name }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ food.description || t('food_desc_fallback') }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-2 rounded-xl bg-white/5 text-slate-400 hover:text-white">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Portion Size Section -->
      <div class="space-y-2">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t('select_portion') }}</h4>
        <div class="grid grid-cols-3 gap-2">
          <button 
            v-for="size in portionSizes" 
            :key="size.name"
            @click="selectedSize = size"
            class="py-2.5 rounded-xl border text-2xs font-bold transition-all duration-200"
            :class="selectedSize.name === size.name ? 'bg-violet-600/20 border-violet-500 text-violet-400' : 'bg-slate-950 border-white/5 text-slate-400 hover:border-slate-800'"
          >
            {{ translateSizeName(size.name) }}
          </button>
        </div>
      </div>

      <!-- Quick Modifiers (Checkboxes) -->
      <div class="space-y-2">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t('extra_wishes') }}</h4>
        <div class="flex flex-wrap gap-2">
          <button 
            v-for="mod in availableModifiers" 
            :key="mod"
            @click="toggleModifier(mod)"
            class="px-3.5 py-2 rounded-xl border text-3xs font-bold transition-all duration-200"
            :class="selectedModifiers.includes(mod) ? 'bg-violet-600/20 border-violet-500 text-violet-400' : 'bg-slate-950 border-white/5 text-slate-400 hover:border-slate-800'"
          >
            {{ selectedModifiers.includes(mod) ? '✓' : '+' }} {{ translateModifier(mod) }}
          </button>
        </div>
      </div>

      <!-- Custom Notes Area -->
      <div class="space-y-2">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t('kitchen_comment') }}</h4>
        <textarea 
          v-model="customNote"
          rows="2"
          :placeholder="t('comment_placeholder')"
          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-white/5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-violet-500 transition duration-150 resize-none"
        ></textarea>
      </div>

      <!-- Sticky Add/Save Button -->
      <button 
        @click="confirmAdd"
        class="w-full py-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 font-bold text-white text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 active:scale-95 transition-all duration-200 flex items-center justify-center space-x-2"
      >
        <span>{{ initialSizeName ? t('save_changes') : t('add_to_cart_btn') }} - {{ formatCurrency(totalPrice) }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ChefHat, X } from 'lucide-vue-next';

const props = defineProps({
  food: {
    type: Object,
    required: true
  },
  initialSizeName: {
    type: String,
    default: null
  },
  initialNotes: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['close', 'add']);

const currentLang = computed(() => localStorage.getItem('waiter_lang') || 'uz');

const dictionary = {
  uz: {
    food_desc_fallback: "Taom haqida batafsil ma'lumot...",
    select_portion: "Taom hajmini tanlang (Porsiya)",
    extra_wishes: "Qo'shimcha istaklar (Modifikatorlar)",
    kitchen_comment: "Oshxona uchun maxsus izoh",
    comment_placeholder: "Mijozning alohida xohishlarini yozing...",
    save_changes: "O'zgarishlarni saqlash",
    add_to_cart_btn: "Savatchaga qo'shish"
  },
  ru: {
    food_desc_fallback: "Подробная информация о блюде...",
    select_portion: "Выберите размер порции",
    extra_wishes: "Дополнительные пожелания (Модификаторы)",
    kitchen_comment: "Особый комментарий для кухни",
    comment_placeholder: "Напишите особые пожелания клиента...",
    save_changes: "Сохранить изменения",
    add_to_cart_btn: "Добавить в корзину"
  }
};

const t = (key) => {
  return dictionary[currentLang.value]?.[key] || key;
};

// Size translation map
const sizeTranslations = {
  uz: {
    'Standart': 'Standart',
    'Yarim': 'Yarim',
    'Katta': 'Katta'
  },
  ru: {
    'Standart': 'Стандарт',
    'Yarim': 'Пол-порции',
    'Katta': 'Большая'
  }
};

const translateSizeName = (name) => {
  if (!name) return '';
  let cleanName = name.replace('1.0', '').replace('0.5', '').replace('1.5', '').replace('(', '').replace(')', '').trim();
  const rootName = cleanName || 'Standart';
  const translated = sizeTranslations[currentLang.value]?.[rootName] || rootName;
  
  if (name.includes('0.5')) return `0.5 (${translated})`;
  if (name.includes('1.5')) return `1.5 (${translated})`;
  return `1.0 (${translated})`;
};

// Modifiers translation map
const modifierTranslations = {
  uz: {
    'Piyozsiz': 'Piyozsiz',
    'Achchiq bo\'lsin': 'Achchiq bo\'lsin',
    'Muz bilan': 'Muz bilan',
    'Yog\'siz': 'Yog\'siz',
    'Limon bilan': 'Limon bilan'
  },
  ru: {
    'Piyozsiz': 'Без лука',
    'Achchiq bo\'lsin': 'Острое',
    'Muz bilan': 'С льдом',
    'Yog\'siz': 'Без масла',
    'Limon bilan': 'С лимоном'
  }
};

const translateModifier = (mod) => {
  return modifierTranslations[currentLang.value]?.[mod] || mod;
};

// Parse portions/sizes relation
const portionSizes = computed(() => {
  if (props.food.sizes && Array.isArray(props.food.sizes) && props.food.sizes.length > 0) {
    return props.food.sizes;
  }
  // Fallback default size
  return [
    { name: '1.0 (Standart)', price: props.food.price }
  ];
});

// Pre-fill initial settings if present
const findInitialSize = () => {
  if (props.initialSizeName) {
    const size = portionSizes.value.find(s => s.name === props.initialSizeName);
    if (size) return size;
  }
  return portionSizes.value[0];
};

const selectedSize = ref(findInitialSize());

// Static/Quick modifiers setup
const availableModifiers = ['Piyozsiz', 'Achchiq bo\'lsin', 'Muz bilan', 'Yog\'siz', 'Limon bilan'];

// Extract quick modifiers from initial notes
const parseInitialModifiers = () => {
  if (!props.initialNotes) return [];
  const notes = props.initialNotes.split(',').map(n => n.trim());
  return notes.filter(n => availableModifiers.includes(n));
};

const parseInitialCustomNote = () => {
  if (!props.initialNotes) return '';
  const notes = props.initialNotes.split(',').map(n => n.trim());
  // Custom note is what remains that is not in the quick modifiers array
  const customNotes = notes.filter(n => !availableModifiers.includes(n));
  return customNotes.join(', ');
};

const selectedModifiers = ref(parseInitialModifiers());
const customNote = ref(parseInitialCustomNote());

const toggleModifier = (mod) => {
  const idx = selectedModifiers.value.indexOf(mod);
  if (idx > -1) {
    selectedModifiers.value.splice(idx, 1);
  } else {
    selectedModifiers.value.push(mod);
  }
};

const totalPrice = computed(() => {
  return parseFloat(selectedSize.value.price);
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('uz-UZ', { style: 'currency', currency: 'UZS', maximumFractionDigits: 0 }).format(value);
};

const confirmAdd = () => {
  // Combine quick modifiers checkmarks with custom notes textbox text
  const notesList = [...selectedModifiers.value];
  if (customNote.value.trim()) {
    notesList.push(customNote.value.trim());
  }
  
  emit('add', {
    size_name: selectedSize.value.name,
    price: parseFloat(selectedSize.value.price),
    notes: notesList.join(', ')
  });
};
</script>

<style scoped>
@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.animate-slideUp {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

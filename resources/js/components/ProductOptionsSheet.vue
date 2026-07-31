<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
    @click.self="$emit('close')"
  >
    <div class="modal-card w-full max-w-[480px]">

      <!-- Header -->
      <div class="modal-header">
        <div>
          <div class="food-title">{{ food.name }}</div>
          <div class="food-base-price">Asosiy narx: {{ formatCurrency(food.price) }}</div>
        </div>
        <button @click="$emit('close')" class="close-btn">
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">

        <!-- Portion Size Section -->
        <div class="section-group">
          <span class="section-label">{{ t('select_portion') }}</span>
          <div class="portion-grid">
            <button
              v-for="size in portionSizes"
              :key="size.name"
              @click="selectedSize = size"
              class="portion-btn"
              :class="{ active: selectedSize.name === size.name }"
            >
              {{ getSizeMultiplier(size.name) }}
              <span>{{ getSizeLabel(size.name) }}</span>
            </button>
          </div>
        </div>

        <!-- Quick Modifiers -->
        <div class="section-group" v-if="availableModifiers.length > 0">
          <span class="section-label">{{ t('extra_wishes') }}</span>
          <div class="modifier-grid">
            <button
              v-for="mod in availableModifiers"
              :key="mod"
              @click="toggleModifier(mod)"
              class="mod-btn"
              :class="{ active: selectedModifiers.includes(mod) }"
            >
              <span>+ {{ translateModifier(mod) }}</span>
            </button>
          </div>
        </div>

        <!-- Custom Notes -->
        <div class="section-group" style="margin-bottom: 0;">
          <span class="section-label">{{ t('kitchen_comment') }}</span>
          <textarea
            v-model="customNote"
            class="note-textarea"
            :placeholder="t('comment_placeholder')"
          ></textarea>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button @click="confirmAdd" class="submit-btn">
          <span>{{ initialSizeName ? t('save_changes') : t('add_to_cart_btn') }}</span>
          <span>{{ formatCurrency(totalPrice) }}</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';

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
  uz: { 'Standart': 'Standart', 'Yarim': 'Yarim', 'Katta': 'Katta' },
  ru: { 'Standart': 'Стандарт', 'Yarim': 'Пол-порции', 'Katta': 'Большая' }
};

const getSizeMultiplier = (name) => {
  if (!name) return '1.0';
  if (name.includes('0.5')) return '0.5';
  if (name.includes('1.5')) return '1.5';
  return '1.0';
};

const getSizeLabel = (name) => {
  if (!name) return 'Standart';
  let cleanName = name.replace('1.0', '').replace('0.5', '').replace('1.5', '').replace('(', '').replace(')', '').trim();
  const rootName = cleanName || 'Standart';
  return sizeTranslations[currentLang.value]?.[rootName] || rootName;
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

// Parse portions/sizes
const portionSizes = computed(() => {
  if (props.food.sizes && Array.isArray(props.food.sizes) && props.food.sizes.length > 0) {
    return props.food.sizes;
  }
  return [{ name: '1.0 (Standart)', price: props.food.price }];
});

const findInitialSize = () => {
  if (props.initialSizeName) {
    const size = portionSizes.value.find(s => s.name === props.initialSizeName);
    if (size) return size;
  }
  return portionSizes.value[0];
};

const selectedSize = ref(findInitialSize());

const availableModifiers = ['Piyozsiz', 'Achchiq bo\'lsin', 'Muz bilan', 'Yog\'siz', 'Limon bilan'];

const parseInitialModifiers = () => {
  if (!props.initialNotes) return [];
  const notes = props.initialNotes.split(',').map(n => n.trim());
  return notes.filter(n => availableModifiers.includes(n));
};

const parseInitialCustomNote = () => {
  if (!props.initialNotes) return '';
  const notes = props.initialNotes.split(',').map(n => n.trim());
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
  return new Intl.NumberFormat('uz-UZ').format(Math.round(value)) + ' so\'m';
};

const confirmAdd = () => {
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
.modal-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Header */
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.food-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1f2937;
}

.food-base-price {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 2px;
}

.close-btn {
  background: #f3f4f6;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}

.close-btn:hover {
  background: #e5e7eb;
  color: #1f2937;
}

/* Body */
.modal-body {
  padding: 24px;
  max-height: 65vh;
  overflow-y: auto;
}

.modal-body::-webkit-scrollbar { width: 5px; }
.modal-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }

/* Section label */
.section-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #9ca3af;
  text-transform: uppercase;
  margin-bottom: 10px;
  display: block;
}

.section-group {
  margin-bottom: 22px;
}

/* Portion Grid */
.portion-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.portion-btn {
  background: #f8f9fa;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px 8px;
  text-align: center;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  transition: all 0.15s ease;
  line-height: 1;
}

.portion-btn span {
  display: block;
  font-size: 0.72rem;
  font-weight: 500;
  color: #6b7280;
  margin-top: 4px;
}

.portion-btn.active {
  border-color: #5c30e6;
  background: #f0ebff;
  color: #5c30e6;
}

.portion-btn.active span {
  color: #5c30e6;
}

/* Modifier Grid */
.modifier-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.mod-btn {
  background: #ffffff;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 11px 14px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 4px;
  transition: all 0.15s ease;
  text-align: left;
}

.mod-btn.active {
  border-color: #5c30e6;
  background: #f0ebff;
  color: #5c30e6;
}

/* Textarea */
.note-textarea {
  width: 100%;
  height: 80px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 0.875rem;
  color: #1f2937;
  outline: none;
  resize: none;
  transition: border-color 0.2s;
  font-family: inherit;
}

.note-textarea::placeholder {
  color: #9ca3af;
}

.note-textarea:focus {
  border-color: #5c30e6;
}

/* Footer */
.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #ffffff;
}

.submit-btn {
  width: 100%;
  background: #5c30e6;
  color: #ffffff;
  border: none;
  padding: 15px 20px;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background 0.2s;
  font-family: inherit;
}

.submit-btn:hover {
  background: #4a24c2;
}

.submit-btn:active {
  transform: scale(0.98);
}
</style>

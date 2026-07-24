import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    // 1. Core Reactive States
    const theme = ref(localStorage.getItem('vrestro_theme') || 'light');
    const language = ref(localStorage.getItem('vrestro_language') || 'uz');
    const nightFilter = ref(localStorage.getItem('vrestro_night_filter') === 'true');
    const fontSize = ref(localStorage.getItem('vrestro_font_size') || 'medium');

    const branding = ref({
        name: localStorage.getItem('vrestro_brand_name') || 'VRestro Restaurant',
        phone: localStorage.getItem('vrestro_brand_phone') || '+998 (90) 123-45-67',
        address: localStorage.getItem('vrestro_brand_address') || 'Toshkent sh., Amir Temur ko\'chasi 15-uy',
        working_hours: localStorage.getItem('vrestro_brand_hours') || '09:00 - 23:00',
        logo_url: localStorage.getItem('vrestro_brand_logo') || '',
        primary_color: localStorage.getItem('vrestro_brand_color') || '#4f46e5',
    });

    // 2. Dictionary Translations for UZ / RU / EN
    const translations = {
        uz: {
            app_title: 'VRestro Restoran Boshqaruvi',
            menu: 'Menyu',
            orders: 'Buyurtmalar',
            ingredients: 'Masalliqlar',
            recipes: 'Retseptlar',
            warehouse: 'Ombor',
            tables: 'Stollar',
            staff: 'Xodimlar',
            customers: 'Mijozlar',
            payments: 'To\'lovlar',
            discounts: 'Chegirmalar',
            settings: 'Sozlamalar',
            logout: 'Chiqish',
            login: 'Tizimga kirish',
            download_app: 'Mobil Ilovani Yuklab Oling',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',
            save: 'Saqlash',
            cancel: 'Bekor qilish',
            delete: 'O\'chirish',
            edit: 'Tahrirlash',
            search: 'Qidirish...',
            status: 'Holat',
            active: 'Faol',
            inactive: 'Nofaol',
            available: 'Mavjud',
            unavailable: 'Mavjud emas',
            total: 'Jami',
        },
        ru: {
            app_title: 'VRestro Управление Рестораном',
            menu: 'Меню',
            orders: 'Заказы',
            ingredients: 'Ингредиенты',
            recipes: 'Рецепты',
            warehouse: 'Склад',
            tables: 'Столы',
            staff: 'Персонал',
            customers: 'Клиенты',
            payments: 'Оплата',
            discounts: 'Скидки',
            settings: 'Настройки',
            logout: 'Выйти',
            login: 'Войти в систему',
            download_app: 'Скачать мобильное приложение',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',
            save: 'Сохранить',
            cancel: 'Отмена',
            delete: 'Удалить',
            edit: 'Редактировать',
            search: 'Поиск...',
            status: 'Статус',
            active: 'Активный',
            inactive: 'Неактивный',
            available: 'Доступно',
            unavailable: 'Недоступно',
            total: 'Итого',
        },
        en: {
            app_title: 'VRestro Restaurant Management',
            menu: 'Menu',
            orders: 'Orders',
            ingredients: 'Ingredients',
            recipes: 'Recipes',
            warehouse: 'Warehouse',
            tables: 'Tables',
            staff: 'Staff',
            customers: 'Customers',
            payments: 'Payments',
            discounts: 'Discounts',
            settings: 'Settings',
            logout: 'Logout',
            login: 'Sign In',
            download_app: 'Download Mobile App',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',
            save: 'Save',
            cancel: 'Cancel',
            delete: 'Delete',
            edit: 'Edit',
            search: 'Search...',
            status: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            available: 'Available',
            unavailable: 'Unavailable',
            total: 'Total',
        }
    };

    const t = (key) => {
        const langDict = translations[language.value] || translations['uz'];
        return langDict[key] || key;
    };

    // 3. Apply settings directly to HTML DOM
    const applySettings = () => {
        const root = document.documentElement;

        // Theme (light / dark)
        if (theme.value === 'dark') {
            root.classList.add('dark');
            root.classList.remove('light');
        } else {
            root.classList.remove('dark');
            root.classList.add('light');
        }

        // Night filter (eye protection sepia overlay)
        if (nightFilter.value) {
            root.classList.add('night-filter');
        } else {
            root.classList.remove('night-filter');
        }

        // Font size scaling
        root.classList.remove('text-size-small', 'text-size-medium', 'text-size-large');
        root.classList.add(`text-size-${fontSize.value}`);
        
        let rootPx = '16px';
        if (fontSize.value === 'small') rootPx = '14px';
        if (fontSize.value === 'large') rootPx = '18px';
        root.style.fontSize = rootPx;

        // Brand Primary Color
        root.style.setProperty('--brand-primary', branding.value.primary_color || '#4f46e5');
    };

    // 4. Update and Persist Methods
    const setTheme = (val) => {
        theme.value = val;
        localStorage.setItem('vrestro_theme', val);
        applySettings();
    };

    const setLanguage = (val) => {
        language.value = val;
        localStorage.setItem('vrestro_language', val);
        applySettings();
    };

    const setNightFilter = (val) => {
        nightFilter.value = !!val;
        localStorage.setItem('vrestro_night_filter', nightFilter.value ? 'true' : 'false');
        applySettings();
    };

    const setFontSize = (val) => {
        fontSize.value = val;
        localStorage.setItem('vrestro_font_size', val);
        applySettings();
    };

    const updateBranding = (data) => {
        branding.value = { ...branding.value, ...data };
        localStorage.setItem('vrestro_brand_name', branding.value.name);
        localStorage.setItem('vrestro_brand_phone', branding.value.phone);
        localStorage.setItem('vrestro_brand_address', branding.value.address);
        localStorage.setItem('vrestro_brand_hours', branding.value.working_hours);
        localStorage.setItem('vrestro_brand_logo', branding.value.logo_url);
        localStorage.setItem('vrestro_brand_color', branding.value.primary_color);
        applySettings();
    };

    const fetchSettings = async () => {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) return;
            const res = await fetch('/api/settings', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.system_language) setLanguage(data.system_language);
                if (data.theme_mode) setTheme(data.theme_mode);
                if (data.night_filter !== undefined) setNightFilter(data.night_filter);
                if (data.font_size) setFontSize(data.font_size);

                if (data.restaurant_name || data.restaurant_phone) {
                    updateBranding({
                        name: data.restaurant_name || branding.value.name,
                        phone: data.restaurant_phone || branding.value.phone,
                        address: data.restaurant_address || branding.value.address,
                        working_hours: data.operating_hours || branding.value.working_hours,
                        logo_url: data.restaurant_logo || branding.value.logo_url,
                        primary_color: data.primary_color || branding.value.primary_color,
                    });
                }
            }
        } catch (e) {
            console.warn('Fetch settings error:', e);
        }
    };

    return {
        theme,
        language,
        nightFilter,
        fontSize,
        branding,
        settings: branding, // Alias for legacy components expecting settingStore.settings
        t,
        applySettings,
        setTheme,
        setLanguage,
        setNightFilter,
        setFontSize,
        updateBranding,
        fetchSettings,
        updateSettings: async (formData) => {
            // Helper for SettingsManagement
            const res = await fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Saqlashda xatolik');
            return data.message;
        }
    };
});

export const useSettingStore = useSettingsStore;

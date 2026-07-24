import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    // 1. Core Reactive States
    // One-time migration: force sessions that previously saved 'dark' (the old
    // default) back to the new minimalist white default theme.
    if (!localStorage.getItem('vrestro_theme_migrated_v2')) {
        localStorage.setItem('vrestro_theme', 'light');
        localStorage.setItem('vrestro_theme_migrated_v2', '1');
    }
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

    // Raw flat key-value settings exactly as returned by GET/POST /api/settings.
    // Components read fields like restaurant_name, tax_rate, receipt_header, etc.
    // directly off this object, so it must mirror the backend response 1:1.
    const rawSettings = ref({});

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

        // Theme (light / dark) — .light-theme is the class app.css actually styles
        if (theme.value === 'dark') {
            root.classList.remove('light-theme');
            root.classList.add('dark');
        } else {
            root.classList.add('light-theme');
            root.classList.remove('dark');
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
                rawSettings.value = data;

                if (data.system_language) setLanguage(data.system_language);
                if (data.theme_mode) setTheme(data.theme_mode);
                if (data.night_filter !== undefined) setNightFilter(data.night_filter);
                if (data.font_size) setFontSize(data.font_size);

                if (data.restaurant_name || data.restaurant_phone) {
                    updateBranding({
                        name: data.restaurant_name || branding.value.name,
                        phone: data.restaurant_phone || branding.value.phone,
                        address: data.restaurant_address || branding.value.address,
                        working_hours: data.restaurant_hours || branding.value.working_hours,
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
        settings: rawSettings, // Raw flat key-value settings (restaurant_name, tax_rate, receipt_header, ...)
        t,
        applySettings,
        setTheme,
        setLanguage,
        setNightFilter,
        setFontSize,
        updateBranding,
        fetchSettings,
        updateSettings: async (formData) => {
            if (!formData.has('system_language')) formData.append('system_language', language.value);
            if (!formData.has('theme_mode')) formData.append('theme_mode', theme.value);
            if (!formData.has('night_filter')) formData.append('night_filter', nightFilter.value ? 'true' : 'false');
            if (!formData.has('font_size')) formData.append('font_size', fontSize.value);
            if (!formData.has('primary_color')) formData.append('primary_color', branding.value.primary_color || '#4f46e5');

            const res = await fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Saqlashda xatolik');

            if (data.settings) {
                rawSettings.value = { ...rawSettings.value, ...data.settings };

                if (data.settings.system_language) setLanguage(data.settings.system_language);
                if (data.settings.theme_mode) setTheme(data.settings.theme_mode);
                if (data.settings.night_filter !== undefined) setNightFilter(data.settings.night_filter === 'true' || data.settings.night_filter === true);
                if (data.settings.font_size) setFontSize(data.settings.font_size);

                updateBranding({
                    name: data.settings.restaurant_name || branding.value.name,
                    phone: data.settings.restaurant_phone || branding.value.phone,
                    address: data.settings.restaurant_address || branding.value.address,
                    working_hours: data.settings.restaurant_hours || branding.value.working_hours,
                    logo_url: data.settings.restaurant_logo || branding.value.logo_url,
                    primary_color: data.settings.primary_color || branding.value.primary_color,
                });
            }

            return data.message;
        }
    };
});

export const useSettingStore = useSettingsStore;

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
            save: 'Saqlash',
            cancel: 'Bekor qilish',
            delete: 'O\'chirish',
            edit: 'Tahrirlash',
            add: 'Qo\'shish',
            search: 'Qidirish...',
            loading: 'Yuklanmoqda...',
            success: 'Muvaffaqiyatli',
            error: 'Xatolik yuz berdi',
            total: 'Jami',
            status: 'Holat',
            active: 'Faol',
            inactive: 'Nofaol',
            available: 'Mavjud',
            unavailable: 'Mavjud emas',
            action: 'Amallar',
            name: 'Nomi',
            phone: 'Telefon',
            price: 'Narx',
            quantity: 'Miqdor',
            date: 'Sana',
            filter: 'Filtrlash',
            refresh: 'Yangilash',
            close: 'Yopish',
            back: 'Orqaga',
            confirm: 'Tasdiqlash',
            yes: 'Ha',
            no: 'Yo\'q',
            view: 'Ko\'rish',
            print: 'Chop etish',
            select: 'Tanlang',
            download_app: 'Mobil Ilovani Yuklab Oling',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',

            // Navigation
            'nav.menu_mgmt': 'Menyu Boshqaruvi',
            'nav.service': 'Xizmat Ko\'rsatish',
            'nav.finance': 'Moliya va Tahlil',
            'nav.dashboard': 'Boshqaruv Paneli',
            'nav.analytics': 'Analitika',
            'nav.menu': 'Taomlar Menyusi',
            'nav.ingredients': 'Masalliqlar',
            'nav.recipes': 'Retseptlar',
            'nav.warehouse': 'Omborxona',
            'nav.tables': 'Stollar va Xonalar',
            'nav.staff': 'Xodimlar',
            'nav.customers': 'Mijozlar Bazasi',
            'nav.payments': 'To\'lovlar Tarixi',
            'nav.discounts': 'Chegirmalar va Promolar',
            'nav.settings': 'Tizim Sozlamalari',
            'nav.notifications': 'Bildirishnomalar',
            'nav.kitchen_monitor': 'Oshpaz Monitori',
            'nav.stop_list': 'Stop-List',
            'nav.pos_cashier': 'Kassa POS',
            'nav.waiter_panel': 'Afitsant Paneli',
            'nav.logout': 'Tizimdan Chiqish',

            // Staff
            'staff.title': 'Xodimlarni Boshqarish',
            'staff.subtitle': 'Restoran xodimlarini qo\'shish, lavozim va kirish huquqlarini boshqarish',
            'staff.add_button': 'Yangi Xodim Qo\'shish',
            'staff.edit_title': 'Xodimni Tahrirlash',
            'staff.role': 'Lavozimi',
            'staff.super_admin': 'Bosh Administrator',
            'staff.admin': 'Administrator',
            'staff.chef': 'Oshpaz',
            'staff.waiter': 'Afitsant',
            'staff.cashier': 'Kassir',
            'staff.warehouseman': 'Omborchi',
            'staff.passport': 'Pasport seriya / raqam',
            'staff.birth_date': 'Tug\'ilgan sana',
            'staff.address': 'Yashash manzili',
            'staff.avatar': 'Xodim rasmi',
            'staff.password': 'Parol',
            'staff.new_password': 'Yangi parol (ixtiyoriy)',
            'staff.password_hint': 'O\'zgartirmaslik uchun bo\'sh qoldiring',

            // Menu & Food
            'menu.title': 'Taomlar Menyusi',
            'menu.subtitle': 'Restoran taomlari, porsiyalar va retsept masalliqlarini boshqarish',
            'menu.add_food': 'Yangi Taom Qo\'shish',
            'menu.edit_food': 'Taomni Tahrirlash',
            'menu.food_name': 'Taom Nomi',
            'menu.category': 'Kategoriya',
            'menu.description': 'Tavsif',
            'menu.portions': 'Porsiya Variantlari',
            'menu.add_portion': 'Porsiya Qo\'shish',
            'menu.half_portion': 'Yarim porsiya (0.5x)',
            'menu.full_portion': 'To\'liq porsiya (1.0x)',
            'menu.ingredients': 'Retsept Masalliqlar (Grammaj)',
            'menu.select_ingredient': 'Masalliqni tanlang',
            'menu.image': 'Taom Rasmi',

            // Warehouse & Ingredients
            'warehouse.title': 'Omborxona va Masalliqlar',
            'warehouse.subtitle': 'Xom-ashyo qoldiqlari, kirim-chiqim harakatlari va to\'g\'ridan-to\'g\'ri sotuv',
            'warehouse.stock': 'Ombor Qoldiqlari',
            'warehouse.history': 'Kirim / Chiqim Tarixi',
            'warehouse.sell_raw': 'Masalliq Sotish',
            'warehouse.buyer': 'Sotib oluvchi nomi',
            'warehouse.min_stock': 'Minimal miqdor',
            'warehouse.current_stock': 'Joriy miqdor',
            'warehouse.unit': 'O\'lchov birligi',

            // Cashier & Payments
            'cashier.title': 'Kassa va Buyurtmalar',
            'cashier.current_order': 'Joriy Buyurtma',
            'cashier.pay_now': 'To\'lovni Qabul Qilish',
            'cashier.cash': 'Naqd pul',
            'cashier.card': 'Plastik karta',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Termal Chek',
            'payments.title': 'To\'lovlar Tarixi',
            'payments.method_filter': 'To\'lov turi bo\'yicha',

            // Kitchen
            'kitchen.monitor_title': 'Oshxona Buyurtmalar Monitori (KDS)',
            'kitchen.recipes_title': 'Taom Retseptlari Masalliqlar Kitobi',
            'kitchen.mark_ready': 'Tayyor deb belgilash',
            'kitchen.mark_cooking': 'Tayyorlanmoqda',

            // Settings
            'settings.title': 'Tizim Sozlamalari',
            'settings.subtitle': 'Til, ranglar mavzusi, tungi rejim, matn o\'lchami va brending sozlamalari',
            'settings.sys_lang': 'Tizim tili',
            'settings.theme_mode': 'Ranglar mavzusi',
            'settings.light_theme': 'Yorug\' (Oq rejim)',
            'settings.dark_theme': 'Qorong\'i (Tungi rejim)',
            'settings.night_filter': 'Ko\'z himoyasi (Tungi filtr)',
            'settings.font_size': 'Matn o\'lchami',
            'settings.small': 'Kichik',
            'settings.medium': 'O\'rtacha',
            'settings.large': 'Katta',
            'settings.branding': 'Restoran Brending Ma\'lumotlari',
            'settings.brand_name': 'Restoran Nomi',
            'settings.brand_phone': 'Telefon raqami',
            'settings.brand_address': 'Manzil',
            'settings.brand_hours': 'Ish vaqti',
            'settings.primary_color': 'Marka Asosiy Rangi',
        },
        ru: {
            app_title: 'VRestro Управление Рестораном',
            save: 'Сохранить',
            cancel: 'Отмена',
            delete: 'Удалить',
            edit: 'Редактировать',
            add: 'Добавить',
            search: 'Поиск...',
            loading: 'Загрузка...',
            success: 'Успешно',
            error: 'Произошла ошибка',
            total: 'Итого',
            status: 'Статус',
            active: 'Активный',
            inactive: 'Неактивный',
            available: 'Доступно',
            unavailable: 'Недоступно',
            action: 'Действия',
            name: 'Название',
            phone: 'Телефон',
            price: 'Цена',
            quantity: 'Количество',
            date: 'Дата',
            filter: 'Фильтр',
            refresh: 'Обновить',
            close: 'Закрыть',
            back: 'Назад',
            confirm: 'Подтвердить',
            yes: 'Да',
            no: 'Нет',
            view: 'Просмотр',
            print: 'Печать',
            select: 'Выберите',
            download_app: 'Скачать мобильное приложение',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',

            // Navigation
            'nav.menu_mgmt': 'Управление Меню',
            'nav.service': 'Обслуживание',
            'nav.finance': 'Финансы и Аналитика',
            'nav.dashboard': 'Панель Управления',
            'nav.analytics': 'Аналитика',
            'nav.menu': 'Меню Блюд',
            'nav.ingredients': 'Ингредиенты',
            'nav.recipes': 'Рецепты',
            'nav.warehouse': 'Склад',
            'nav.tables': 'Столы и Залы',
            'nav.staff': 'Персонал',
            'nav.customers': 'База Клиентов',
            'nav.payments': 'История Оплат',
            'nav.discounts': 'Скидки и Промо',
            'nav.settings': 'Настройки Системы',
            'nav.notifications': 'Уведомления',
            'nav.kitchen_monitor': 'Монитор Кухни',
            'nav.stop_list': 'Стоп-Лист',
            'nav.pos_cashier': 'Касса POS',
            'nav.waiter_panel': 'Панель Официанта',
            'nav.logout': 'Выйти из системы',

            // Staff
            'staff.title': 'Управление Персоналом',
            'staff.subtitle': 'Добавление сотрудников, управление ролями и правами доступа',
            'staff.add_button': 'Добавить Сотрудника',
            'staff.edit_title': 'Редактировать Сотрудника',
            'staff.role': 'Должность',
            'staff.super_admin': 'Главный Администратор',
            'staff.admin': 'Администратор',
            'staff.chef': 'Повар',
            'staff.waiter': 'Официант',
            'staff.cashier': 'Кассир',
            'staff.warehouseman': 'Кладовщик',
            'staff.passport': 'Паспорт серия / номер',
            'staff.birth_date': 'Дата рождения',
            'staff.address': 'Адрес проживания',
            'staff.avatar': 'Фото сотрудника',
            'staff.password': 'Пароль',
            'staff.new_password': 'Новый пароль (необязательно)',
            'staff.password_hint': 'Оставьте пустым, чтобы не изменять',

            // Menu & Food
            'menu.title': 'Меню Блюд',
            'menu.subtitle': 'Управление блюдами, порциями и ингредиентами рецептов',
            'menu.add_food': 'Добавить Блюдо',
            'menu.edit_food': 'Редактировать Блюдо',
            'menu.food_name': 'Название Блюда',
            'menu.category': 'Категория',
            'menu.description': 'Описание',
            'menu.portions': 'Варианты Порций',
            'menu.add_portion': 'Добавить Порцию',
            'menu.half_portion': 'Полпорции (0.5x)',
            'menu.full_portion': 'Полная порция (1.0x)',
            'menu.ingredients': 'Ингредиенты Рецепта (Граммаж)',
            'menu.select_ingredient': 'Выберите ингредиент',
            'menu.image': 'Фото Блюда',

            // Warehouse & Ingredients
            'warehouse.title': 'Склад и Ингредиенты',
            'warehouse.subtitle': 'Остатки сырья, история прихода/расхода и прямые продажи',
            'warehouse.stock': 'Остатки на Складе',
            'warehouse.history': 'История Движения',
            'warehouse.sell_raw': 'Продажа Ингредиента',
            'warehouse.buyer': 'Имя покупателя',
            'warehouse.min_stock': 'Мин. количество',
            'warehouse.current_stock': 'Текущее количество',
            'warehouse.unit': 'Ед. измерения',

            // Cashier & Payments
            'cashier.title': 'Касса и Заказы',
            'cashier.current_order': 'Текущий Заказ',
            'cashier.pay_now': 'Принять Оплату',
            'cashier.cash': 'Наличные',
            'cashier.card': 'Пластиковая карта',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Чек',
            'payments.title': 'История Оплат',
            'payments.method_filter': 'По типу оплаты',

            // Kitchen
            'kitchen.monitor_title': 'Монитор Заказов Кухни (KDS)',
            'kitchen.recipes_title': 'Книга Рецептов Ингредиентов',
            'kitchen.mark_ready': 'Отметить готовым',
            'kitchen.mark_cooking': 'Готовится',

            // Settings
            'settings.title': 'Настройки Системы',
            'settings.subtitle': 'Настройки языка, темы, ночного фильтра, шрифта и брендинга',
            'settings.sys_lang': 'Язык системы',
            'settings.theme_mode': 'Цветовая тема',
            'settings.light_theme': 'Светлая (Белая тема)',
            'settings.dark_theme': 'Тёмная (Ночная тема)',
            'settings.night_filter': 'Защита глаз (Ночной фильтр)',
            'settings.font_size': 'Размер шрифта',
            'settings.small': 'Маленький',
            'settings.medium': 'Средний',
            'settings.large': 'Большой',
            'settings.branding': 'Брендинг Ресторана',
            'settings.brand_name': 'Название Ресторана',
            'settings.brand_phone': 'Номер телефона',
            'settings.brand_address': 'Адрес',
            'settings.brand_hours': 'Часы работы',
            'settings.primary_color': 'Основной цвет бренда',
        },
        en: {
            app_title: 'VRestro Restaurant Management',
            save: 'Save',
            cancel: 'Cancel',
            delete: 'Delete',
            edit: 'Edit',
            add: 'Add',
            search: 'Search...',
            loading: 'Loading...',
            success: 'Success',
            error: 'An error occurred',
            total: 'Total',
            status: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            available: 'Available',
            unavailable: 'Unavailable',
            action: 'Actions',
            name: 'Name',
            phone: 'Phone',
            price: 'Price',
            quantity: 'Quantity',
            date: 'Date',
            filter: 'Filter',
            refresh: 'Refresh',
            close: 'Close',
            back: 'Back',
            confirm: 'Confirm',
            yes: 'Yes',
            no: 'No',
            view: 'View',
            print: 'Print',
            select: 'Select',
            download_app: 'Download Mobile App',
            download_ios: 'App Store (iOS)',
            download_android: 'Google Play (Android)',

            // Navigation
            'nav.menu_mgmt': 'Menu Management',
            'nav.service': 'Service',
            'nav.finance': 'Finance & Analytics',
            'nav.dashboard': 'Dashboard',
            'nav.analytics': 'Analytics',
            'nav.menu': 'Food Menu',
            'nav.ingredients': 'Ingredients',
            'nav.recipes': 'Recipes',
            'nav.warehouse': 'Warehouse',
            'nav.tables': 'Tables & Rooms',
            'nav.staff': 'Staff Members',
            'nav.customers': 'Customers',
            'nav.payments': 'Payment History',
            'nav.discounts': 'Discounts & Promos',
            'nav.settings': 'System Settings',
            'nav.notifications': 'Notifications',
            'nav.kitchen_monitor': 'Kitchen Display (KDS)',
            'nav.stop_list': 'Stop List',
            'nav.pos_cashier': 'Cashier POS',
            'nav.waiter_panel': 'Waiter Panel',
            'nav.logout': 'Logout',

            // Staff
            'staff.title': 'Staff Management',
            'staff.subtitle': 'Manage restaurant employees, roles, and access permissions',
            'staff.add_button': 'Add New Staff',
            'staff.edit_title': 'Edit Staff Member',
            'staff.role': 'Role',
            'staff.super_admin': 'Super Administrator',
            'staff.admin': 'Administrator',
            'staff.chef': 'Chef',
            'staff.waiter': 'Waiter',
            'staff.cashier': 'Cashier',
            'staff.warehouseman': 'Storekeeper',
            'staff.passport': 'Passport Series / Number',
            'staff.birth_date': 'Date of Birth',
            'staff.address': 'Residential Address',
            'staff.avatar': 'Staff Photo',
            'staff.password': 'Password',
            'staff.new_password': 'New Password (Optional)',
            'staff.password_hint': 'Leave blank to keep unchanged',

            // Menu & Food
            'menu.title': 'Food Menu',
            'menu.subtitle': 'Manage food items, portion sizes, and recipe ingredients',
            'menu.add_food': 'Add New Dish',
            'menu.edit_food': 'Edit Dish',
            'menu.food_name': 'Dish Name',
            'menu.category': 'Category',
            'menu.description': 'Description',
            'menu.portions': 'Portion Variants',
            'menu.add_portion': 'Add Portion',
            'menu.half_portion': 'Half portion (0.5x)',
            'menu.full_portion': 'Full portion (1.0x)',
            'menu.ingredients': 'Recipe Ingredients (Grammage)',
            'menu.select_ingredient': 'Select ingredient',
            'menu.image': 'Dish Photo',

            // Warehouse & Ingredients
            'warehouse.title': 'Warehouse & Ingredients',
            'warehouse.subtitle': 'Raw material stock, movements log, and direct sales',
            'warehouse.stock': 'Warehouse Stock',
            'warehouse.history': 'Movement History',
            'warehouse.sell_raw': 'Sell Raw Ingredient',
            'warehouse.buyer': 'Buyer Name',
            'warehouse.min_stock': 'Min Quantity',
            'warehouse.current_stock': 'Current Stock',
            'warehouse.unit': 'Unit',

            // Cashier & Payments
            'cashier.title': 'Cashier & POS',
            'cashier.current_order': 'Current Order',
            'cashier.pay_now': 'Accept Payment',
            'cashier.cash': 'Cash',
            'cashier.card': 'Card',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Receipt',
            'payments.title': 'Payment History',
            'payments.method_filter': 'By Payment Method',

            // Kitchen
            'kitchen.monitor_title': 'Kitchen Display System (KDS)',
            'kitchen.recipes_title': 'Recipe Ingredient Handbook',
            'kitchen.mark_ready': 'Mark as Ready',
            'kitchen.mark_cooking': 'Cooking',

            // Settings
            'settings.title': 'System Settings',
            'settings.subtitle': 'Language, theme mode, night filter, font size, and branding',
            'settings.sys_lang': 'System Language',
            'settings.theme_mode': 'Color Theme',
            'settings.light_theme': 'Light (White Theme)',
            'settings.dark_theme': 'Dark (Night Theme)',
            'settings.night_filter': 'Eye Protection (Night Filter)',
            'settings.font_size': 'Font Size',
            'settings.small': 'Small',
            'settings.medium': 'Medium',
            'settings.large': 'Large',
            'settings.branding': 'Restaurant Branding',
            'settings.brand_name': 'Restaurant Name',
            'settings.brand_phone': 'Phone Number',
            'settings.brand_address': 'Address',
            'settings.brand_hours': 'Operating Hours',
            'settings.primary_color': 'Brand Primary Color',
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

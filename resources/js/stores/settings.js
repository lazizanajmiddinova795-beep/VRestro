import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
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

    const rawSettings = ref({});

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
            no_data: 'Ma\'lumotlar mavjud emas',
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

            // Dashboard & Analytics
            'dashboard.title': 'Boshqaruv Paneli',
            'dashboard.subtitle': 'Tizim ko\'rsatkichlari va umumiy moliyaviy tahlil',
            'dashboard.today_revenue': 'Bugungi Tushum',
            'dashboard.today_orders': 'Bugungi Buyurtmalar',
            'dashboard.avg_order': 'O\'rtacha Chek',
            'dashboard.active_tables': 'Band Stollar',
            'dashboard.live_orders': 'Jonli Buyurtmalar Oqimi',
            'dashboard.top_foods': 'Eng Ko\'p Sotilgan Taomlar',
            'dashboard.popular_categories': 'Ommabop Kategoriyalar',
            'analytics.title': 'Kengaytirilgan Analitika',
            'analytics.subtitle': 'Savdo statistikasi va davriy hisobotlar',
            'analytics.daily_chart': 'Kunlik Savdo Dinamikasi',
            'analytics.payment_methods': 'To\'lov Turlari Bo\'yicha Tahlil',

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

            // Menu & Ingredients & Recipes
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
            'ingredients.title': 'Masalliqlar Tizimi',
            'ingredients.subtitle': 'Xom-ashyo masalliqlari va minimal chegara nazorati',
            'recipes.title': 'Taom Retseptlari',
            'recipes.subtitle': 'Taomlar retsepti va ingredient sarfi grammaji',

            // Warehouse
            'warehouse.title': 'Omborxona va Masalliqlar',
            'warehouse.subtitle': 'Xom-ashyo qoldiqlari, kirim-chiqim harakatlari va to\'g\'ridan-to\'g\'ri sotuv',
            'warehouse.stock': 'Ombor Qoldiqlari',
            'warehouse.history': 'Kirim / Chiqim Tarixi',
            'warehouse.sell_raw': 'Masalliq Sotish',
            'warehouse.buyer': 'Sotib oluvchi nomi',
            'warehouse.min_stock': 'Minimal miqdor',
            'warehouse.current_stock': 'Joriy miqdor',
            'warehouse.unit': 'O\'lchov birligi',

            // Orders & Payments & Discounts & Tables & Customers
            'orders.title': 'Buyurtmalar Ro\'yxati',
            'orders.subtitle': 'Restoran buyurtmalari monitoringi va tarixi',
            'orders.number': 'Buyurtma №',
            'orders.table': 'Stol',
            'orders.waiter': 'Afitsant',
            'orders.items': 'Taomlar',
            'orders.total_amount': 'Jami Summa',
            'orders.cancel_order': 'Buyurtmani Bekor Qilish',
            'payments.title': 'To\'lovlar Tarixi',
            'payments.subtitle': 'Kassa to\'lovlari va hisobotlar',
            'payments.method_filter': 'To\'lov turi bo\'yicha',
            'discounts.title': 'Chegirmalar va Promokodlar',
            'discounts.subtitle': 'Aksiya va chegirma tizimini sozlash',
            'tables.title': 'Stollar va Xonalar',
            'tables.subtitle': 'Restoran zallari va stollar joylashuvi',
            'customers.title': 'Mijozlar Bazasi',
            'customers.subtitle': 'Doimiy mijozlar va sodiqlik dasturi',

            // Cashier & Waiter & Kitchen
            'cashier.title': 'Kassa va Buyurtmalar',
            'cashier.current_order': 'Joriy Buyurtma',
            'cashier.pay_now': 'To\'lovni Qabul Qilish',
            'cashier.cash': 'Naqd pul',
            'cashier.card': 'Plastik karta',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Termal Chek',
            'kitchen.monitor_title': 'Oshxona Buyurtmalar Monitori (KDS)',
            'kitchen.recipes_title': 'Taom Retseptlari Masalliqlar Kitobi',
            'kitchen.pending': 'Kutilmoqda',
            'kitchen.cooking': 'Tayyorlanmoqda',
            'kitchen.ready': 'Tayyor',
            'kitchen.mark_ready': 'Tayyor deb belgilash',
            'kitchen.mark_cooking': 'Tayyorlanmoqda',
            'waiter.tables_title': 'Stollar Xaritasi',
            'waiter.new_order': 'Yangi Buyurtma',
            'waiter.order_status': 'Buyurtma Holatlari',
            'waiter.profile': 'Mening Profilim',

            // Settings & Notifications & Landing & Auth
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
            'notifications.title': 'Tizim Bildirishnomalari',
            'notifications.subtitle': 'Barcha muhim xabarlar va ogohlantirishlar',
            'landing.welcome': 'VRestro Tizimiga Xush Kelibsiz',
            'landing.subtitle': 'Zamonaviy restoran boshqaruvi, POS kassa va KDS oshxona ekrani',
            'login.title': 'Tizimga Kirish',
            'login.subtitle': 'Login va parolingizni kiriting',
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
            no_data: 'Нет данных',
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

            // Dashboard & Analytics
            'dashboard.title': 'Панель Управления',
            'dashboard.subtitle': 'Показатели системы и общий финансовый анализ',
            'dashboard.today_revenue': 'Выручка за Сегодня',
            'dashboard.today_orders': 'Заказов за Сегодня',
            'dashboard.avg_order': 'Средний Чек',
            'dashboard.active_tables': 'Занятые Столы',
            'dashboard.live_orders': 'Живой Поток Заказов',
            'dashboard.top_foods': 'Самые Продаваемые Блюда',
            'dashboard.popular_categories': 'Популярные Категории',
            'analytics.title': 'Расширенная Аналитика',
            'analytics.subtitle': 'Статистика продаж и периодические отчеты',
            'analytics.daily_chart': 'Динамика Дневных Продаж',
            'analytics.payment_methods': 'Анализ по Типам Оплаты',

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

            // Menu & Ingredients & Recipes
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
            'ingredients.title': 'Система Ингредиентов',
            'ingredients.subtitle': 'Сырьевые ингредиенты и контроль минимального остатка',
            'recipes.title': 'Рецепты Блюд',
            'recipes.subtitle': 'Рецептура блюд и расход граммажа ингредиентов',

            // Warehouse
            'warehouse.title': 'Склад и Ингредиенты',
            'warehouse.subtitle': 'Остатки сырья, история прихода/расхода и прямые продажи',
            'warehouse.stock': 'Остатки на Складе',
            'warehouse.history': 'История Движения',
            'warehouse.sell_raw': 'Продажа Ингредиента',
            'warehouse.buyer': 'Имя покупателя',
            'warehouse.min_stock': 'Мин. количество',
            'warehouse.current_stock': 'Текущее количество',
            'warehouse.unit': 'Ед. измерения',

            // Orders & Payments & Discounts & Tables & Customers
            'orders.title': 'Список Заказов',
            'orders.subtitle': 'Мониторинг и история заказов ресторана',
            'orders.number': 'Заказ №',
            'orders.table': 'Стол',
            'orders.waiter': 'Официант',
            'orders.items': 'Блюда',
            'orders.total_amount': 'Общая Сумма',
            'orders.cancel_order': 'Отменить Заказ',
            'payments.title': 'История Оплат',
            'payments.subtitle': 'Кассовые платежи и отчеты',
            'payments.method_filter': 'По типу оплаты',
            'discounts.title': 'Скидки и Промокоды',
            'discounts.subtitle': 'Настройка акций и системы скидок',
            'tables.title': 'Столы и Залы',
            'tables.subtitle': 'Схема залов и расположение столов',
            'customers.title': 'База Клиентов',
            'customers.subtitle': 'Постоянные клиенты и программа лояльности',

            // Cashier & Waiter & Kitchen
            'cashier.title': 'Касса и Заказы',
            'cashier.current_order': 'Текущий Заказ',
            'cashier.pay_now': 'Принять Оплату',
            'cashier.cash': 'Наличные',
            'cashier.card': 'Пластиковая карта',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Чек',
            'kitchen.monitor_title': 'Монитор Заказов Кухни (KDS)',
            'kitchen.recipes_title': 'Книга Рецептов Ингредиентов',
            'kitchen.pending': 'В ожидании',
            'kitchen.cooking': 'Готовится',
            'kitchen.ready': 'Готово',
            'kitchen.mark_ready': 'Отметить готовым',
            'kitchen.mark_cooking': 'Готовится',
            'waiter.tables_title': 'Карта Столов',
            'waiter.new_order': 'Новый Заказ',
            'waiter.order_status': 'Статусы Заказов',
            'waiter.profile': 'Мой Профиль',

            // Settings & Notifications & Landing & Auth
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
            'notifications.title': 'Уведомления Системы',
            'notifications.subtitle': 'Все важные сообщения и предупреждения',
            'landing.welcome': 'Добро Пожаловать в VRestro',
            'landing.subtitle': 'Современное управление рестораном, POS-касса и KDS экран кухни',
            'login.title': 'Вход в Систему',
            'login.subtitle': 'Введите ваш логин и пароль',
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
            no_data: 'No data available',
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

            // Dashboard & Analytics
            'dashboard.title': 'Dashboard',
            'dashboard.subtitle': 'System metrics and overall financial analysis',
            'dashboard.today_revenue': 'Today\'s Revenue',
            'dashboard.today_orders': 'Today\'s Orders',
            'dashboard.avg_order': 'Average Check',
            'dashboard.active_tables': 'Occupied Tables',
            'dashboard.live_orders': 'Live Orders Stream',
            'dashboard.top_foods': 'Top Selling Dishes',
            'dashboard.popular_categories': 'Popular Categories',
            'analytics.title': 'Advanced Analytics',
            'analytics.subtitle': 'Sales statistics and periodic reports',
            'analytics.daily_chart': 'Daily Sales Trends',
            'analytics.payment_methods': 'Analysis by Payment Method',

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

            // Menu & Ingredients & Recipes
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
            'ingredients.title': 'Ingredients System',
            'ingredients.subtitle': 'Raw material ingredients and low stock threshold control',
            'recipes.title': 'Food Recipes',
            'recipes.subtitle': 'Food recipes and ingredient grammage usage',

            // Warehouse
            'warehouse.title': 'Warehouse & Ingredients',
            'warehouse.subtitle': 'Raw material stock, movements log, and direct sales',
            'warehouse.stock': 'Warehouse Stock',
            'warehouse.history': 'Movement History',
            'warehouse.sell_raw': 'Sell Raw Ingredient',
            'warehouse.buyer': 'Buyer Name',
            'warehouse.min_stock': 'Min Quantity',
            'warehouse.current_stock': 'Current Stock',
            'warehouse.unit': 'Unit',

            // Orders & Payments & Discounts & Tables & Customers
            'orders.title': 'Orders List',
            'orders.subtitle': 'Restaurant orders monitoring and history',
            'orders.number': 'Order #',
            'orders.table': 'Table',
            'orders.waiter': 'Waiter',
            'orders.items': 'Dishes',
            'orders.total_amount': 'Total Amount',
            'orders.cancel_order': 'Cancel Order',
            'payments.title': 'Payment History',
            'payments.subtitle': 'Cashier payments and reports',
            'payments.method_filter': 'By Payment Method',
            'discounts.title': 'Discounts & Promos',
            'discounts.subtitle': 'Manage promotions and discount system',
            'tables.title': 'Tables & Rooms',
            'tables.subtitle': 'Room layouts and table positions',
            'customers.title': 'Customers Base',
            'customers.subtitle': 'Regular customers and loyalty program',

            // Cashier & Waiter & Kitchen
            'cashier.title': 'Cashier & POS',
            'cashier.current_order': 'Current Order',
            'cashier.pay_now': 'Accept Payment',
            'cashier.cash': 'Cash',
            'cashier.card': 'Card',
            'cashier.qr_click': 'QR / Click / Payme',
            'cashier.receipt': 'Receipt',
            'kitchen.monitor_title': 'Kitchen Display System (KDS)',
            'kitchen.recipes_title': 'Recipe Ingredient Handbook',
            'kitchen.pending': 'Pending',
            'kitchen.cooking': 'Cooking',
            'kitchen.ready': 'Ready',
            'kitchen.mark_ready': 'Mark as Ready',
            'kitchen.mark_cooking': 'Cooking',
            'waiter.tables_title': 'Tables Map',
            'waiter.new_order': 'New Order',
            'waiter.order_status': 'Order Statuses',
            'waiter.profile': 'My Profile',

            // Settings & Notifications & Landing & Auth
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
            'notifications.title': 'System Notifications',
            'notifications.subtitle': 'All important alerts and messages',
            'landing.welcome': 'Welcome to VRestro',
            'landing.subtitle': 'Modern restaurant management, POS cashier and KDS kitchen display',
            'login.title': 'Sign In',
            'login.subtitle': 'Enter your username and password',
        }
    };

    const t = (key) => {
        const langDict = translations[language.value] || translations['uz'];
        return langDict[key] || key;
    };

    const applyDOMTheme = () => {
        if (theme.value === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        document.documentElement.setAttribute('data-font-size', fontSize.value);
        if (nightFilter.value) {
            document.documentElement.classList.add('night-filter');
        } else {
            document.documentElement.classList.remove('night-filter');
        }
    };

    const setTheme = (newTheme) => {
        theme.value = newTheme;
        localStorage.setItem('vrestro_theme', newTheme);
        applyDOMTheme();
    };

    const setLanguage = (newLang) => {
        language.value = newLang;
        localStorage.setItem('vrestro_language', newLang);
    };

    const setNightFilter = (enabled) => {
        nightFilter.value = enabled;
        localStorage.setItem('vrestro_night_filter', enabled ? 'true' : 'false');
        applyDOMTheme();
    };

    const setFontSize = (newSize) => {
        fontSize.value = newSize;
        localStorage.setItem('vrestro_font_size', newSize);
        applyDOMTheme();
    };

    const updateBranding = (newBranding) => {
        branding.value = { ...branding.value, ...newBranding };
        if (newBranding.name) localStorage.setItem('vrestro_brand_name', newBranding.name);
        if (newBranding.phone) localStorage.setItem('vrestro_brand_phone', newBranding.phone);
        if (newBranding.address) localStorage.setItem('vrestro_brand_address', newBranding.address);
        if (newBranding.working_hours) localStorage.setItem('vrestro_brand_hours', newBranding.working_hours);
        if (newBranding.logo_url) localStorage.setItem('vrestro_brand_logo', newBranding.logo_url);
        if (newBranding.primary_color) localStorage.setItem('vrestro_brand_color', newBranding.primary_color);
    };

    const fetchSettings = async () => {
        try {
            const token = localStorage.getItem('vrestro_token');
            const res = await fetch('/api/settings', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': token ? `Bearer ${token}` : ''
                }
            });
            if (res.ok) {
                const data = await res.json();
                rawSettings.value = data;
                if (data.system_language) {
                    language.value = data.system_language;
                    localStorage.setItem('vrestro_language', data.system_language);
                }
                if (data.restaurant_name) {
                    branding.value.name = data.restaurant_name;
                }
            }
        } catch (e) {
            console.error('Settings fetch error:', e);
        }
    };

    const updateSettings = async (formData) => {
        try {
            const token = localStorage.getItem('vrestro_token');
            const res = await fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': token ? `Bearer ${token}` : ''
                },
                body: formData
            });
            if (res.ok) {
                const data = await res.json();
                await fetchSettings();
                return data.message || 'Settings saved successfully';
            }
            throw new Error('Failed to update settings');
        } catch (e) {
            console.error('Settings update error:', e);
            throw e;
        }
    };

    applyDOMTheme();

    return {
        theme,
        language,
        nightFilter,
        fontSize,
        branding,
        rawSettings,
        translations,
        t,
        setTheme,
        setLanguage,
        setNightFilter,
        setFontSize,
        updateBranding,
        fetchSettings,
        updateSettings,
    };
});

// Backward compatibility alias export
export const useSettingStore = useSettingsStore;

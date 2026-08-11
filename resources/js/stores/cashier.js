import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useCashierStore = defineStore('cashier', () => {
    // 1. Shift Management Session State
    const shiftOpenTime = ref(localStorage.getItem('vrestro_shift_open_time') || new Date().toLocaleString('uz-UZ'));
    const isShiftActive = ref(true);

    if (!localStorage.getItem('vrestro_shift_open_time')) {
        localStorage.setItem('vrestro_shift_open_time', shiftOpenTime.value);
    }

    // 2. POS Cart State
    const cart = ref([]);
    const crmCustomer = ref(null);
    const promoCode = ref('');
    const discountAmount = ref(0);

    // 3. Local Settings (Kassa Sozlamalari)
    const defaultSettings = {
        theme: 'light',
        nightFilter: false,
        fontSize: 'normal',
        zoomScale: 100,
        printerWidth: '80mm',
        soundEnabled: true,
        language: 'uz'
    };

    const localSettings = ref(
        JSON.parse(localStorage.getItem('vrestro_cashier_settings')) || { ...defaultSettings }
    );

    // One-time migration: force existing sessions saved with the old dark default
    // over to the new minimalist white default theme.
    if (!localStorage.getItem('vrestro_theme_migrated_v2')) {
        localSettings.value.theme = 'light';
        localStorage.setItem('vrestro_theme_migrated_v2', '1');
    }

    // Language translations dictionary
    const dictionary = {
        uz: {
            stollar_xaritasi: "Stollar xaritasi",
            tezkor_buyurtma: "Tezkor Buyurtma",
            cheklar_tarixi: "Cheklar tarixi",
            sozlamalar: "Sozlamalar",
            chiqish: "Chiqish",
            tizimdan_chiqish: "Tizimdan chiqish",
            smena_nazorati: "Smena nazorati",
            taom_tayyor_bildirishnomalar: "Tayyor bo'lgan buyurtmalar",
            hammasini_tozalash: "Hammasini tozalash",
            bildirishnoma_yoq: "Hozircha bildirishnoma yo'q",
            bo_sh: "Bo'sh",
            band: "Band",
            bron: "Bron",
            olib_ketish: "Olib ketish",
            jami: "Jami",
            yangi_buyurtma: "Yangi Buyurtma (Olib ketish)",
            yangilash: "Yangilash",
            savatcha: "Savatcha (Takeaway)",
            barchasi: "Barchasi",
            taom_qo_shish: "Taom qo'shish",
            oraliq_jami: "Oraliq jami",
            xizmat_haqi: "Xizmat haqi",
            qqs: "QQS",
            jami_to_lov: "JAMI TO'LOV",
            to_lovga_o_tish: "To'lovga o'tish",
            smena_yakunlash: "Smenani yakunlash (Z-Report & Chiqish)",
            ovozli_bildirishnoma: "Ovozli bildirishnomalar",
            ekran_rejimi: "Ekran Rejimi (Mavzu)",
            shrift_olchami: "Matn o'lchami",
            lupa_masshtab: "Lupa / Ekran Masshtabi",
            printer_sozlamalari: "Termal Printer Sozlamalari",
            chek_kengligi: "Chek kengligi (Qog'oz o'lchami)",
            printerni_sinash: "Printerni Sinab Ko'rish (Test Print)",
            tizim_tili: "Tizim Tili (Language)",
            stol: "Stol",
            
            // Settings descriptions & subtexts
            lokal_sozlamalar_desc: "Lokal sozlamalar brauzer xotirasida saqlanadi",
            tizim_tili_desc: "Tizim uchun qulay tilni tanlang (Language Settings)",
            ekran_rejimi_desc: "Interfeys uchun qulay rang rejimini tanlang",
            shrift_olchami_desc: "Matn hajmi va ekranning umumiy masshtabini o'zgartiring",
            printer_sozlamalari_desc: "Chop etiladigan chek kengligi shablonini sozlang",
            standart_qogoz: "80mm (Standart termal qog'oz)",
            kichik_qogoz: "58mm (Kichik kassa qog'ozi)",
            kichik: "Kichik",
            normal: "Normal",
            katta: "Katta",
            ovozli_signal_desc: "Interfaol harakatlarda ovozli signallarni yoqing",
            savat_signal_desc: "Savatga qo'shish va to'lovlarda signal berish",
            yoqilgan: "Yoqilgan",
            ochirilgan: "O'chirilgan",

            // Receipts History (Cheklar tarixi) labels
            cheklar_jurnali_desc: "Barcha yakunlangan va to'langan buyurtmalar jurnali",
            yangi_chek: "Yangi Chek",
            sana_vaqt: "Sana / Vaqt",
            chek_no: "Chek №",
            summa: "Summa",
            amallar: "Amallar",
            chop_etilgan: "Chop etilgan",
            navbat_cheki: "Navbat cheki (Pre-check)",
            tolov_cheki: "To'lov cheki (Invoice)",
            mix_chop: "Mix chop etish",
            chek_korinishi: "Chek ko'rinishi",
            select_receipt_prompt: "Chop etish yoki virtual ko'rish uchun chap tomondan chekni tanlang.",
            print_receipt: "Chekni Chop Etish (window.print)",
            nomi: "Nomi",
            soni: "Soni",
            chegirma: "Chegirma",
            tolov_shakli: "To'lov shakli",
            naqd: "Naqd pul",
            karta: "Plastik karta",
            qr_tolov: "QR to'lov",
            bonusdan: "Bonusdan",
            scan_to_verify: "Tekshirish uchun skanerlang",
            nomalum: "Noma'lum",
            mehmon: "Mehmon (Mijoz bog'lanmagan)",
            kassir: "Kassir",
            ofitsiant: "Ofitsiant",
            buyurtma_no: "Buyurtma №",
            sana: "Sana",
            buyurtma_tarkibi: "Buyurtma tarkibi",
            taom_tanlang: "Taomni tanlang",
            bekor_qilish: "Bekor qilish",
            tolovni_yakunlash: "To'lovni yakunlash",
            yangi_tolov_kiritish: "Yangi To'lov Kiritish",
            receipts_not_found: "Cheklar mavjud emas",
            select_table_placeholder: "Stolni tanlang...",
            table_occupied: "Band",
            table_free: "Bo'sh",
            order_loading: "Buyurtma yuklanmoqda...",
            select_food_placeholder: "Taomni tanlang...",
            unknown_item: "Noma'lum",
            no_items_yet: "Chekda hozircha hech narsa yo'q. Taom qo'shing.",
            customer_loyalty_label: "Mijoz (Sodiqlik tizimi)",
            bonus_label: "Bonus:",
            mixed_payment_option: "Aralash to'lov (Mixed)",
            cash_amount_label: "Naqd pul:",
            card_amount_label: "Plastik karta:",
            qr_amount_label: "QR to'lov:",
            bonus_amount_label: "Bonusdan:",
            split_warning_prefix: "Diqqat: To'lov summasi jami miqdorga to'g'ri kelmayapti. Kiritilgan summa:",
            split_warning_total: "Jami:",
            void_order_button: "Buyurtmani Bekor Qilish",
            void_modal_title: "Buyurtmani bekor qilish",
            void_modal_desc: "Buyurtmani bekor qilish sababini tanlang yoki batafsil izoh yozing. Ushbu harakat ombordagi masalliqlarni qayta hisoblaydi.",
            void_reason_label: "Sababni tanlang *",
            void_reason_placeholder: "-- Sababni tanlang --",
            void_reason_1: "Mijoz shoshilganligi sababli",
            void_reason_2: "Taom sifati tufayli",
            void_reason_3: "Operator xatosi",
            void_reason_custom: "Boshqa sabab (izohda yozish)...",
            void_extra_note_label: "Qo'shimcha izoh",
            void_extra_note_placeholder: "Sababni batafsil tushuntiring...",
            back_button: "Ortga",
            confirm_button: "Tasdiqlash",
            voiding_in_progress: "Bekor qilinmoqda...",
            void_error: "Bekor qilishda xatolik yuz berdi.",
            void_success: "Buyurtma bekor qilindi.",
            order_create_error: "Buyurtma yaratishda xatolik yuz berdi.",
            payment_success_title: "To'lov muvaffaqiyatli",
            payment_success_message: "To'lov tizimda qayd etildi. Chekni chop etishingiz mumkin.",
            paying_in_progress: "To'lanmoqda...",

            // Menu categories
            ichimliklar: "Ichimliklar",
            salatlar: "Salatlar",
            shirinliklar: "Shirinliklar",
            taomlar: "Taomlar",

            // Foods
            "achchiq-chuchuk": "Achchiq-chuchuk",
            "coca-cola 1.5l": "Coca-Cola 1.5L",
            "lag'mon": "Lag'mon",
            "limonli ko'k choy": "Limonli ko'k choy",
            "mol go'shtidan shashlik": "Mol go'shtidan shashlik",
            "muzqaymoq": "Muzqaymoq"
        },
        ru: {
            stollar_xaritasi: "Карта столов",
            tezkor_buyurtma: "Быстрый заказ",
            cheklar_tarixi: "История чеков",
            sozlamalar: "Настройки",
            chiqish: "Выход",
            tizimdan_chiqish: "Выйти из системы",
            smena_nazorati: "Контроль смены",
            taom_tayyor_bildirishnomalar: "Готовые заказы",
            hammasini_tozalash: "Очистить все",
            bildirishnoma_yoq: "Пока нет уведомлений",
            bo_sh: "Свободно",
            band: "Занято",
            bron: "Бронь",
            olib_ketish: "На вынос",
            jami: "Всего",
            yangi_buyurtma: "Новый заказ (С собой)",
            yangilash: "Обновить",
            savatcha: "Корзина (С собой)",
            barchasi: "Все",
            taom_qo_shish: "Добавить блюдо",
            oraliq_jami: "Подытог",
            xizmat_haqi: "Обслуживание",
            qqs: "НДС",
            jami_to_lov: "ИТОГО К ОПЛАТЕ",
            to_lovga_o_tish: "Перейти к оплате",
            smena_yakunlash: "Завершить смену (Z-отчет и выход)",
            ovozli_bildirishnoma: "Звуковые уведомления",
            ekran_rejimi: "Режим экрана (Тема)",
            shrift_olchami: "Размер шрифта",
            lupa_masshtab: "Масштаб экрана",
            printer_sozlamalari: "Настройки принтера",
            chek_kengligi: "Ширина чека (Размер бумаги)",
            printerni_sinash: "Проверить принтер (Тест-печать)",
            tizim_tili: "Язык системы (Language)",
            stol: "Стол",

            // Settings descriptions & subtexts
            lokal_sozlamalar_desc: "Локальные настройки сохраняются в памяти браузера",
            tizim_tili_desc: "Выберите язык интерфейса системы",
            ekran_rejimi_desc: "Выберите наиболее удобный цветовой режим экрана",
            shrift_olchami_desc: "Настройте размер шрифта и масштаб интерфейса",
            printer_sozlamalari_desc: "Выберите шаблон ширины чековой ленты принтера",
            standart_qogoz: "80мм (Стандартная термолента)",
            kichik_qogoz: "58мм (Узкая чековая лента)",
            kichik: "Мелкий",
            normal: "Обычный",
            katta: "Крупный",
            ovozli_signal_desc: "Звуковые сигналы при интерактивных действиях кассира",
            savat_signal_desc: "Сигнал при добавлении блюда в корзину и оплате",
            yoqilgan: "Включено",
            ochirilgan: "Выключено",

            // Receipts History (Cheklar tarixi) labels
            cheklar_jurnali_desc: "Журнал всех завершенных и оплаченных заказов",
            yangi_chek: "Новый чек",
            sana_vaqt: "Дата / Время",
            chek_no: "Чек №",
            summa: "Сумма",
            amallar: "Действия",
            chop_etilgan: "Распечатано",
            navbat_cheki: "Сервисный чек (Pre-check)",
            tolov_cheki: "Фискальный чек (Invoice)",
            mix_chop: "Смешанная печать",
            chek_korinishi: "Просмотр чека",
            select_receipt_prompt: "Выберите чек слева для печати или просмотра.",
            print_receipt: "Печать чека (window.print)",
            nomi: "Наименование",
            soni: "Кол-во",
            chegirma: "Скидка",
            tolov_shakli: "Способ оплаты",
            naqd: "Наличные",
            karta: "Пластиковая карта",
            qr_tolov: "QR-платеж",
            bonusdan: "Бонусы",
            scan_to_verify: "Сканируйте для проверки",
            nomalum: "Неизвестно",
            mehmon: "Гость (Клиент не привязан)",
            kassir: "Кассир",
            ofitsiant: "Официант",
            buyurtma_no: "Заказ №",
            sana: "Дата",
            buyurtma_tarkibi: "Состав заказа",
            taom_tanlang: "Выберите блюдо",
            bekor_qilish: "Отмена",
            tolovni_yakunlash: "Завершить оплату",
            yangi_tolov_kiritish: "Ввод нового платежа",
            receipts_not_found: "Чеки не найдены",
            select_table_placeholder: "Выберите стол...",
            table_occupied: "Занято",
            table_free: "Свободно",
            order_loading: "Загрузка заказа...",
            select_food_placeholder: "Выберите блюдо...",
            unknown_item: "Неизвестно",
            no_items_yet: "В чеке пока ничего нет. Добавьте блюдо.",
            customer_loyalty_label: "Клиент (Система лояльности)",
            bonus_label: "Бонус:",
            mixed_payment_option: "Смешанная оплата (Mixed)",
            cash_amount_label: "Наличные:",
            card_amount_label: "Пластиковая карта:",
            qr_amount_label: "QR-платеж:",
            bonus_amount_label: "Бонусы:",
            split_warning_prefix: "Внимание: сумма оплаты не соответствует общей сумме. Введено:",
            split_warning_total: "Итого:",
            void_order_button: "Отменить заказ",
            void_modal_title: "Отмена заказа",
            void_modal_desc: "Выберите причину отмены заказа или напишите подробный комментарий. Это действие пересчитает остатки на складе.",
            void_reason_label: "Выберите причину *",
            void_reason_placeholder: "-- Выберите причину --",
            void_reason_1: "Клиент торопился",
            void_reason_2: "Из-за качества блюда",
            void_reason_3: "Ошибка оператора",
            void_reason_custom: "Другая причина (написать в комментарии)...",
            void_extra_note_label: "Дополнительный комментарий",
            void_extra_note_placeholder: "Опишите причину подробно...",
            back_button: "Назад",
            confirm_button: "Подтвердить",
            voiding_in_progress: "Отмена...",
            void_error: "Произошла ошибка при отмене.",
            void_success: "Заказ отменён.",
            order_create_error: "Произошла ошибка при создании заказа.",
            payment_success_title: "Оплата прошла успешно",
            payment_success_message: "Оплата зарегистрирована в системе. Вы можете распечатать чек.",
            paying_in_progress: "Оплата...",

            // Menu categories
            ichimliklar: "Напитки",
            salatlar: "Салаты",
            shirinliklar: "Десерты",
            taomlar: "Блюда",

            // Foods
            "achchiq-chuchuk": "Аччик-чучук (Салат)",
            "coca-cola 1.5l": "Кока-Кола 1.5л",
            "lag'mon": "Лагман",
            "limonli ko'k choy": "Зеленый чай с лимоном",
            "mol go'shtidan shashlik": "Шашлык из говядины",
            "muzqaymoq": "Мороженое"
        },
        en: {
            stollar_xaritasi: "Tables Map",
            tezkor_buyurtma: "Fast POS Order",
            cheklar_tarixi: "Invoices History",
            sozlamalar: "Settings",
            chiqish: "Logout",
            tizimdan_chiqish: "Logout of System",
            smena_nazorati: "Shift Controls",
            taom_tayyor_bildirishnomalar: "Ready Orders",
            hammasini_tozalash: "Clear All",
            bildirishnoma_yoq: "No notifications yet",
            bo_sh: "Empty",
            band: "Occupied",
            bron: "Reserved",
            olib_ketish: "Takeaway",
            jami: "Total",
            yangi_buyurtma: "New Order (Takeaway)",
            yangilash: "Refresh",
            savatcha: "Shopping Cart (Takeaway)",
            barchasi: "All",
            taom_qo_shish: "Add Dish",
            oraliq_jami: "Subtotal",
            xizmat_haqi: "Service Charge",
            qqs: "VAT",
            jami_to_lov: "TOTAL DUE",
            to_lovga_o_tish: "Proceed to Payment",
            smena_yakunlash: "End Shift (Z-Report & Exit)",
            ovozli_bildirishnoma: "Sound Notifications",
            ekran_rejimi: "Screen Theme",
            shrift_olchami: "Font Size",
            lupa_masshtab: "Screen Zoom Scale",
            printer_sozlamalari: "Thermal Printer Settings",
            chek_kengligi: "Receipt Width (Paper Size)",
            printerni_sinash: "Print Test Page",
            tizim_tili: "System Language",
            stol: "Table",

            // Settings descriptions & subtexts
            lokal_sozlamalar_desc: "Local settings are saved in the browser storage",
            tizim_tili_desc: "Choose your preferred system display language",
            ekran_rejimi_desc: "Select a comfortable screen visual color mode",
            shrift_olchami_desc: "Configure font layout size and scale interface",
            printer_sozlamalari_desc: "Select standard printer paper layout width",
            standart_qogoz: "80mm (Standard thermal paper)",
            kichik_qogoz: "58mm (Narrow roll tape)",
            kichik: "Small",
            normal: "Normal",
            katta: "Large",
            ovozli_signal_desc: "Trigger audio sound beeps on cashier actions",
            savat_signal_desc: "Beep sounds when adding items to cart or checkout",
            yoqilgan: "Enabled",
            ochirilgan: "Disabled",

            // Receipts History (Cheklar tarixi) labels
            cheklar_jurnali_desc: "Log of all completed and paid orders",
            yangi_chek: "New Invoice",
            sana_vaqt: "Date / Time",
            chek_no: "Receipt №",
            summa: "Amount",
            amallar: "Actions",
            chop_etilgan: "Printed",
            navbat_cheki: "Service Ticket (Pre-check)",
            tolov_cheki: "Final Invoice (Receipt)",
            mix_chop: "Mix Print Mode",
            chek_korinishi: "Invoice Preview",
            select_receipt_prompt: "Select a receipt from the left sidebar to preview or print.",
            print_receipt: "Print Receipt (window.print)",
            nomi: "Item Name",
            soni: "Qty",
            chegirma: "Discount",
            tolov_shakli: "Payment Method",
            naqd: "Cash",
            karta: "Credit/Debit Card",
            qr_tolov: "QR Payment",
            bonusdan: "Bonus Points",
            scan_to_verify: "Scan to verify invoice",
            nomalum: "Unknown",
            mehmon: "Guest (Walk-in customer)",
            kassir: "Cashier",
            ofitsiant: "Waiter",
            buyurtma_no: "Order ID",
            sana: "Date",
            buyurtma_tarkibi: "Order Details",
            taom_tanlang: "Select a food item",
            bekor_qilish: "Cancel",
            tolovni_yakunlash: "Complete Payment",
            yangi_tolov_kiritish: "Add Ad-Hoc Receipt",
            receipts_not_found: "No receipts found",
            select_table_placeholder: "Select a table...",
            table_occupied: "Occupied",
            table_free: "Free",
            order_loading: "Loading order...",
            select_food_placeholder: "Select a dish...",
            unknown_item: "Unknown",
            no_items_yet: "Nothing added to this receipt yet. Add a dish.",
            customer_loyalty_label: "Customer (Loyalty System)",
            bonus_label: "Bonus:",
            mixed_payment_option: "Mixed Payment",
            cash_amount_label: "Cash:",
            card_amount_label: "Card:",
            qr_amount_label: "QR Payment:",
            bonus_amount_label: "Bonus:",
            split_warning_prefix: "Warning: the payment amount does not match the total. Entered:",
            split_warning_total: "Total:",
            void_order_button: "Cancel Order",
            void_modal_title: "Cancel Order",
            void_modal_desc: "Select a reason for cancelling the order or write a detailed note. This action will recalculate warehouse stock.",
            void_reason_label: "Select a reason *",
            void_reason_placeholder: "-- Select a reason --",
            void_reason_1: "Customer was in a hurry",
            void_reason_2: "Food quality issue",
            void_reason_3: "Operator error",
            void_reason_custom: "Other reason (write in note)...",
            void_extra_note_label: "Additional Note",
            void_extra_note_placeholder: "Explain the reason in detail...",
            back_button: "Back",
            confirm_button: "Confirm",
            voiding_in_progress: "Cancelling...",
            void_error: "An error occurred while cancelling.",
            void_success: "Order cancelled.",
            order_create_error: "An error occurred while creating the order.",
            payment_success_title: "Payment Successful",
            payment_success_message: "Payment has been recorded in the system. You may print the receipt.",
            paying_in_progress: "Processing payment...",

            // Menu categories
            ichimliklar: "Drinks",
            salatlar: "Salads",
            shirinliklar: "Desserts",
            taomlar: "Dishes",

            // Foods
            "achchiq-chuchuk": "Tomato Salad",
            "coca-cola 1.5l": "Coca-Cola 1.5L",
            "lag'mon": "Lagman",
            "limonli ko'k choy": "Lemon Green Tea",
            "mol go'shtidan shashlik": "Beef Kebab",
            "muzqaymoq": "Ice Cream"
        }
    };

    // Translation function
    // Reads the shared 'vrestro_language' key (set by the global settingsStore) so the
    // cashier dictionary stays in sync with the language chosen in Sozlamalar, instead of
    // its own disconnected localSettings.language copy which was never updated.
    const t = (key) => {
        const lang = localStorage.getItem('vrestro_language') || localSettings.value.language || 'uz';
        return dictionary[lang]?.[key] || dictionary['uz']?.[key] || key;
    };

    // Apply styles to document body
    const applyLocalSettings = () => {
        const settings = localSettings.value;
        
        // Theme
        const html = document.documentElement;
        if (settings.theme === 'light') {
            html.classList.add('light-theme');
            html.classList.remove('dark');
        } else {
            html.classList.remove('light-theme');
            html.classList.add('dark');
        }

        // Night Filter and Font Size are owned by settingsStore (Sozlamalar page) -
        // that store already applies both to <html> (night-filter class and
        // data-font-size attribute) as soon as it's instantiated and reactively
        // on every change. This store used to keep its own disconnected copies
        // and force them here via inline styles/classes, which always won over
        // settingsStore's changes and made the Sozlamalar buttons look broken.

        // Zoom scale
        const body = document.body;
        if (body && settings.zoomScale) {
            body.style.zoom = `${settings.zoomScale}%`;
        }
    };

    // Apply settings immediately on store load
    applyLocalSettings();

    // Auto-save localSettings when altered
    watch(localSettings, (newVal) => {
        localStorage.setItem('vrestro_cashier_settings', JSON.stringify(newVal));
        applyLocalSettings();
    }, { deep: true });

    // HTML5 synthesized beep audio tone generator
    const playNotificationBeep = () => {
        if (!localSettings.value.soundEnabled) return;
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
            gainNode.gain.setValueAtTime(0.08, audioCtx.currentTime); // soft volume

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.12); // short 120ms
        } catch (e) {
            console.error('Audio beep failed:', e);
        }
    };

    // "Taom tayyor" ready-order notifications - polled from the shared
    // SystemNotification feed (created by the kitchen when an order's
    // items are all marked ready). Two views on the same data:
    //   - readyToasts: transient floating cards, auto-disappear after 10s
    //     (just an attention-grabbing animation, doesn't affect read state)
    //   - notificationHistory: persists until the cashier explicitly
    //     dismisses it, so a notification missed while busy with a
    //     customer can still be found later via the bell dropdown.
    // The underlying SystemNotification stays unread server-side until the
    // cashier dismisses it from the history (not just when the toast fades).
    const readyToasts = ref([]);
    const notificationHistory = ref([]);
    let notificationPollInterval = null;

    const pollReadyNotifications = async () => {
        try {
            const token = localStorage.getItem('vrestro_token');
            const res = await fetch('/api/notifications?is_read=false&type=order_ready', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const notifications = await res.json();
            const list = Array.isArray(notifications) ? notifications : (notifications.data || []);
            const knownIds = new Set(notificationHistory.value.map(n => n.id));

            for (const notif of list) {
                if (knownIds.has(notif.id)) continue;

                const entry = {
                    id: notif.id,
                    title: notif.title,
                    orderNumber: notif.meta_data?.order_number || '',
                    message: notif.message,
                    created_at: notif.created_at
                };

                notificationHistory.value.unshift(entry);
                readyToasts.value.push(entry);
                playNotificationBeep();

                // Auto-dismiss only the floating card, not the history entry
                setTimeout(() => {
                    readyToasts.value = readyToasts.value.filter(t => t.id !== notif.id);
                }, 10000);
            }
        } catch (e) {
            console.error('Ready notification poll failed:', e);
        }
    };

    const dismissReadyToast = (id) => {
        readyToasts.value = readyToasts.value.filter(t => t.id !== id);
    };

    const clearNotification = async (id) => {
        notificationHistory.value = notificationHistory.value.filter(n => n.id !== id);
        readyToasts.value = readyToasts.value.filter(t => t.id !== id);
        try {
            const token = localStorage.getItem('vrestro_token');
            await fetch(`/api/notifications/${id}/read`, {
                method: 'PATCH',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
        } catch (e) {
            console.error('Failed to mark notification as read:', e);
        }
    };

    const clearAllNotifications = async () => {
        const ids = notificationHistory.value.map(n => n.id);
        notificationHistory.value = [];
        readyToasts.value = [];
        try {
            const token = localStorage.getItem('vrestro_token');
            await Promise.all(ids.map(id => fetch(`/api/notifications/${id}/read`, {
                method: 'PATCH',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            })));
        } catch (e) {
            console.error('Failed to mark notifications as read:', e);
        }
    };

    const startNotificationPolling = () => {
        if (notificationPollInterval) return;
        pollReadyNotifications();
        notificationPollInterval = setInterval(pollReadyNotifications, 8000);
    };

    const stopNotificationPolling = () => {
        if (notificationPollInterval) {
            clearInterval(notificationPollInterval);
            notificationPollInterval = null;
        }
    };

    // Cart operations
    const addToCart = (food, sizeName = null, price = null, notes = '', quantity = 1, is_existing = false) => {
        const finalPrice = price !== null ? parseFloat(price) : parseFloat(food.price);
        const finalQuantity = quantity > 0 ? quantity : 1;
        const existing = cart.value.find(item => item.food_id === food.id && (item.size_name || null) === sizeName && item.is_existing === is_existing);
        if (existing) {
            existing.quantity += finalQuantity;
        } else {
            cart.value.push({
                food_id: food.id,
                name: food.name,
                price: finalPrice,
                size_name: sizeName,
                notes: notes || '',
                quantity: finalQuantity,
                food: food,
                is_existing: is_existing
            });
        }
        playNotificationBeep();
    };

    const removeFromCart = (foodId, sizeName = null) => {
        cart.value = cart.value.filter(item => !(item.food_id === foodId && (item.size_name || null) === sizeName));
        playNotificationBeep();
    };

    const updateQuantity = (foodId, delta, sizeName = null) => {
        const item = cart.value.find(item => item.food_id === foodId && (item.size_name || null) === sizeName);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                removeFromCart(foodId, sizeName);
            } else {
                playNotificationBeep();
            }
        }
    };

    const editCartItem = (foodId, oldSizeName, newSizeName, newPrice, newNotes, newQuantity = null) => {
        const item = cart.value.find(item => item.food_id === foodId && (item.size_name || null) === oldSizeName);
        if (item) {
            item.size_name = newSizeName;
            item.price = parseFloat(newPrice);
            item.notes = newNotes || '';
            if (newQuantity > 0) {
                item.quantity = newQuantity;
            }
        }
    };

    const clearCart = () => {
        cart.value = [];
        crmCustomer.value = null;
        promoCode.value = '';
        discountAmount.value = 0;
    };

    const closeShift = () => {
        isShiftActive.value = false;
        localStorage.removeItem('vrestro_shift_open_time');
        clearCart();
    };

    return {
        // Shift
        shiftOpenTime,
        isShiftActive,
        closeShift,
        
        // Cart
        cart,
        crmCustomer,
        promoCode,
        discountAmount,
        addToCart,
        removeFromCart,
        updateQuantity,
        editCartItem,
        clearCart,

        // Settings & Sound & Translations
        localSettings,
        applyLocalSettings,
        playNotificationBeep,
        t,

        // "Taom tayyor" ready notifications
        readyToasts,
        notificationHistory,
        startNotificationPolling,
        stopNotificationPolling,
        dismissReadyToast,
        clearNotification,
        clearAllNotifications
    };
});

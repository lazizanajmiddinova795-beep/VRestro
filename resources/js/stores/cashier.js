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
        theme: 'dark',
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
            bo_sh: "Bo'sh",
            band: "Band",
            bron: "Bron",
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
            bo_sh: "Свободно",
            band: "Занято",
            bron: "Бронь",
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
            bo_sh: "Empty",
            band: "Occupied",
            bron: "Reserved",
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
    const t = (key) => {
        const lang = localSettings.value.language || 'uz';
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

        // Night Filter (Ko'z himoyasi)
        if (settings.nightFilter) {
            html.classList.add('night-filter');
        } else {
            html.classList.remove('night-filter');
        }

        // Font size scaling on root HTML element
        if (settings.fontSize === 'small') {
            html.style.fontSize = '14px';
        } else if (settings.fontSize === 'large') {
            html.style.fontSize = '18px';
        } else {
            html.style.fontSize = '16px';
        }

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

    // Cart operations
    const addToCart = (food) => {
        const existing = cart.value.find(item => item.food_id === food.id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.value.push({
                food_id: food.id,
                name: food.name,
                price: parseFloat(food.price),
                quantity: 1,
                food: food
            });
        }
        playNotificationBeep();
    };

    const removeFromCart = (foodId) => {
        cart.value = cart.value.filter(item => item.food_id !== foodId);
        playNotificationBeep();
    };

    const updateQuantity = (foodId, delta) => {
        const item = cart.value.find(item => item.food_id === foodId);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                removeFromCart(foodId);
            } else {
                playNotificationBeep();
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
        clearCart,

        // Settings & Sound & Translations
        localSettings,
        applyLocalSettings,
        playNotificationBeep,
        t
    };
});

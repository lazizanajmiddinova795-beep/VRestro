import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from '@/components/LandingPage.vue';
import LoginForm from '@/components/LoginForm.vue';
import { useAuthStore } from '@/stores/auth';
import SidebarLayout from '@/components/SidebarLayout.vue';

const routes = [
    {
        path: '/',
        name: 'landing',
        component: LandingPage,
    },
    {
        path: '/login',
        name: 'login',
        component: LoginForm,
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (authStore.isAuthenticated()) {
                next({ name: 'landing' }); // redirect if already logged in
            } else {
                next();
            }
        }
    },
    {
        path: '/kitchen',
        name: 'kitchen',
        component: () => import('@/components/KitchenMonitor.vue'),
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        }
    },
    {
        path: '/kitchen/stop-list',
        name: 'kitchen-stop-list',
        component: () => import('@/components/KitchenStopList.vue'),
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        }
    },
    {
        path: '/kitchen/settings',
        name: 'kitchen-settings',
        component: () => import('@/components/KitchenSettings.vue'),
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        }
    },
    {
        path: '/cashier',
        component: () => import('@/components/CashierLayout.vue'),
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        },
        children: [
            {
                path: 'tables',
                name: 'cashier-tables',
                component: () => import('@/components/CashierTables.vue')
            },
            {
                path: 'receipts',
                name: 'cashier-receipts',
                component: () => import('@/components/ReceiptPreview.vue')
            },
            {
                path: 'order',
                name: 'cashier-order',
                component: () => import('@/components/CashierOrder.vue')
            },
            {
                path: 'settings',
                name: 'cashier-settings',
                component: () => import('@/components/CashierSettings.vue')
            }
        ]
    },
    {
        path: '/waiter',
        component: () => import('@/components/WaiterLayout.vue'),
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        },
        children: [
            {
                path: 'tables',
                name: 'waiter-tables',
                component: () => import('@/components/WaiterTables.vue')
            },
            {
                path: 'order',
                name: 'waiter-order',
                component: () => import('@/components/WaiterOrder.vue')
            },
            {
                path: 'status',
                name: 'waiter-status',
                component: () => import('@/components/WaiterOrderStatus.vue')
            },
            {
                path: 'profile',
                name: 'waiter-profile',
                component: () => import('@/components/WaiterProfile.vue')
            }
        ]
    },
    {
        path: '/',
        component: SidebarLayout,
        beforeEnter: (to, from, next) => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated()) {
                next({ name: 'login' });
            } else {
                next();
            }
        },
        children: [
            {
                path: 'admin/dashboard',
                name: 'admin-dashboard',
                component: () => import('@/components/AdminDashboard.vue')
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('@/components/AnalyticsDashboard.vue')
            },
            {
                path: 'orders',
                name: 'orders',
                component: () => import('@/components/OrdersDashboard.vue')
            },
            {
                path: 'menu',
                name: 'menu',
                component: () => import('@/components/MenuManagement.vue')
            },
            {
                path: 'ingredients',
                name: 'ingredients',
                component: () => import('@/components/IngredientsManagement.vue')
            },
            {
                path: 'recipes',
                name: 'recipes',
                component: () => import('@/components/RecipeConfigurator.vue')
            },
            {
                path: 'warehouse',
                name: 'warehouse',
                component: () => import('@/components/WarehouseManagement.vue')
            },
            {
                path: 'tables',
                name: 'tables',
                component: () => import('@/components/TablesManagement.vue')
            },
            {
                path: 'staff',
                name: 'staff',
                component: () => import('@/components/StaffManagement.vue')
            },
            {
                path: 'customers',
                name: 'customers',
                component: () => import('@/components/CustomerManagement.vue')
            },
            {
                path: 'payments',
                name: 'payments',
                component: () => import('@/components/PaymentsManagement.vue')
            },
            {
                path: 'discounts',
                name: 'discounts',
                component: () => import('@/components/DiscountsManagement.vue')
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('@/components/NotificationsManagement.vue')
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/components/SettingsManagement.vue')
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

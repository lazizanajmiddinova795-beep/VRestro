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
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'login',
        component: LoginForm,
        meta: { guestOnly: true }
    },
    {
        path: '/kitchen',
        name: 'kitchen',
        component: () => import('@/components/KitchenMonitor.vue'),
        meta: { requiresAuth: true, roles: ['Admin', 'Chef'] }
    },
    {
        path: '/kitchen/stop-list',
        name: 'kitchen-stop-list',
        component: () => import('@/components/KitchenStopList.vue'),
        meta: { requiresAuth: true, roles: ['Admin', 'Chef'] }
    },
    {
        path: '/kitchen/recipes',
        name: 'kitchen-recipes',
        component: () => import('@/components/KitchenRecipes.vue'),
        meta: { requiresAuth: true, roles: ['Admin', 'Chef'] }
    },
    {
        path: '/kitchen/settings',
        name: 'kitchen-settings',
        component: () => import('@/components/KitchenSettings.vue'),
        meta: { requiresAuth: true, roles: ['Admin', 'Chef'] }
    },
    {
        path: '/cashier',
        component: () => import('@/components/CashierLayout.vue'),
        meta: { requiresAuth: true, roles: ['Admin', 'Cashier'] },
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
        meta: { requiresAuth: true, roles: ['Admin', 'Waiter'] },
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
        meta: { requiresAuth: true },
        children: [
            {
                path: 'admin/dashboard',
                name: 'admin-dashboard',
                component: () => import('@/components/AdminDashboard.vue'),
                meta: { roles: ['Admin'] }
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: () => import('@/components/AnalyticsDashboard.vue'),
                meta: { roles: ['Admin'] }
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
                component: () => import('@/components/StaffManagement.vue'),
                meta: { roles: ['Admin'] }
            },
            {
                path: 'customers',
                name: 'customers',
                component: () => import('@/components/CustomerManagement.vue')
            },
            {
                path: 'payments',
                name: 'payments',
                component: () => import('@/components/PaymentsManagement.vue'),
                meta: { roles: ['Admin', 'Cashier'] }
            },
            {
                path: 'discounts',
                name: 'discounts',
                component: () => import('@/components/DiscountsManagement.vue'),
                meta: { roles: ['Admin', 'Cashier'] }
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('@/components/NotificationsManagement.vue')
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/components/SettingsManagement.vue'),
                meta: { roles: ['Admin'] }
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Dynamic Route Guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated();

    // 1. If route is landing page or guest-only and user is already logged in, redirect to respective dashboard
    if ((to.path === '/' || to.matched.some(record => record.meta.guestOnly)) && isAuthenticated) {
        let dashboardName = 'admin-dashboard';
        const roles = authStore.user?.roles || [];
        if (roles.includes('Admin')) {
            dashboardName = 'admin-dashboard';
        } else if (roles.includes('Cashier')) {
            dashboardName = 'cashier-tables';
        } else if (roles.includes('Chef')) {
            dashboardName = 'kitchen';
        } else if (roles.includes('Waiter')) {
            dashboardName = 'waiter-tables';
        } else {
            dashboardName = 'orders';
        }
        return next({ name: dashboardName });
    }

    // 2. If route requires authentication and user is not logged in, force redirect to login
    if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
        return next({ name: 'login' });
    }

    // 3. Strict Role-based Authorization Guard
    const userRole = authStore.user?.roles?.[0] || 'Waiter';
    const matchedRoleRoute = to.matched.find(record => record.meta && record.meta.roles);
    if (matchedRoleRoute && matchedRoleRoute.meta.roles) {
        const allowedRoles = matchedRoleRoute.meta.roles;
        if (!allowedRoles.includes(userRole)) {
            let redirectName = 'admin-dashboard';
            if (userRole === 'Cashier') redirectName = 'cashier-tables';
            else if (userRole === 'Chef') redirectName = 'kitchen';
            else if (userRole === 'Waiter') redirectName = 'waiter-tables';
            return next({ name: redirectName });
        }
    }

    next();
});

export default router;

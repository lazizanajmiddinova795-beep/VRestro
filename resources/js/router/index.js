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
        meta: { requiresAuth: true, roles: ['Manager', 'Chef'] }
    },
    {
        path: '/kitchen/stop-list',
        name: 'kitchen-stop-list',
        component: () => import('@/components/KitchenStopList.vue'),
        meta: { requiresAuth: true, roles: ['Manager', 'Chef'] }
    },
    {
        path: '/kitchen/recipes',
        name: 'kitchen-recipes',
        component: () => import('@/components/KitchenRecipes.vue'),
        meta: { requiresAuth: true, roles: ['Manager', 'Chef'] }
    },
    {
        path: '/kitchen/settings',
        name: 'kitchen-settings',
        component: () => import('@/components/KitchenSettings.vue'),
        meta: { requiresAuth: true, roles: ['Manager', 'Chef'] }
    },
    {
        path: '/cashier',
        component: () => import('@/components/CashierLayout.vue'),
        meta: { requiresAuth: true, roles: ['Manager', 'Cashier'] },
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
        meta: { requiresAuth: true, roles: ['Manager', 'Waiter'] },
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
                meta: { roles: ['Manager'] }
            },
            {
                path: 'branches',
                name: 'branches',
                component: () => import('@/components/BranchManagement.vue'),
                meta: { requiresAuth: true }
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
                meta: { roles: ['Manager'] }
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
                meta: { roles: ['Manager', 'Cashier'] }
            },
            {
                path: 'discounts',
                name: 'discounts',
                component: () => import('@/components/DiscountsManagement.vue'),
                meta: { roles: ['Manager', 'Cashier'] }
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
                meta: { roles: ['Manager'] }
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

    // 1. If route is the landing page and user is authenticated, redirect to their dashboard
    if (to.path === '/' && isAuthenticated) {
        let dashboardName = 'admin-dashboard';
        const roles = authStore.user?.roles || [];
        if (roles.includes('Manager')) {
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

    // 1b. If user navigates to /login while already authenticated, log them out first
    //     so a different staff member can log in on the same device
    if (to.matched.some(record => record.meta.guestOnly) && isAuthenticated) {
        authStore.logout();
        return next();
    }

    // 2. If route requires authentication and user is not logged in, force redirect to login
    if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
        return next({ name: 'login' });
    }

    // 3. Robust Role-based Authorization Guard
    const user = authStore.user;
    let userRole = 'Manager';
    if (user) {
        if (Array.isArray(user.roles) && user.roles.length > 0) {
            userRole = user.roles[0];
        } else if (user.role) {
            userRole = user.role;
        }
    }

    const matchedRoleRoute = to.matched.find(record => record.meta && record.meta.roles);
    if (matchedRoleRoute && matchedRoleRoute.meta.roles) {
        const allowedRoles = matchedRoleRoute.meta.roles;
        const hasAccess = allowedRoles.some(r => r.toLowerCase() === userRole.toLowerCase());
        if (!hasAccess) {
            let redirectName = 'admin-dashboard';
            const normalizedRole = userRole.toLowerCase();
            if (normalizedRole === 'cashier') redirectName = 'cashier-tables';
            else if (normalizedRole === 'chef') redirectName = 'kitchen';
            else if (normalizedRole === 'waiter') redirectName = 'waiter-tables';
            return next({ name: redirectName });
        }
    }

    next();
});

export default router;

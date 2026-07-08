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
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

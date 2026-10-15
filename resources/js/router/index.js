import { createRouter, createWebHistory } from 'vue-router';

import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Profile from '../views/Profile.vue';
import Users from '../views/Users.vue';
import Contactus from '../views/Contactus.vue';
import Enquiry from '../views/Enquiry.vue';

import AdminLayout from '../components/admin/layouts/AdminLayout.vue';
import AdminLogin from '../components/admin/auth/AdminLogin.vue';
import Dashboard from '../components/admin/Dashboard.vue';
// import Categories from '../components/admin/Categories.vue';
// import Products from '../components/admin/Products.vue';

import { auth, loadUser } from '../auth';

const routes = [
    // -------------------------
    // USER PANEL
    // -------------------------
    {
        path: '/',
        name: 'home',
        component: Home,
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guestOnly: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true },
    },
    {
        path: '/users',
        name: 'users',
        component: Users,
        meta: { requiresAuth: true },
    },
    {
        path: '/contactus',
        name: 'contactus',
        component: Contactus,
        meta: { guestOnly: true },
    },
    {
        path: '/enquiry',
        name: 'enquiry',
        component: Enquiry,
        meta: { requiresAuth: true },
    },

    // -------------------------
    // ADMIN LOGIN
    // -------------------------
    {
        path: '/admin/login',
        name: 'admin.login',
        component: AdminLogin,
        meta: { guestOnly: true, adminLogin: true },
    },

    // -------------------------
    // ADMIN PANEL
    // -------------------------
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, requiresAdmin: true },
        redirect: { name: 'admin.dashboard' },
        children: [
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: Dashboard,
                meta: { requiresAuth: true, requiresAdmin: true },
            },
            // {
            //     path: 'categories',
            //     name: 'admin.categories',
            //     component: Categories,
            //     meta: { requiresAuth: true, requiresAdmin: true },
            // },
            // {
            //     path: 'products',
            //     name: 'admin.products',
            //     component: Products,
            //     meta: { requiresAuth: true, requiresAdmin: true },
            // },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    if (!auth.loaded) {
        await loadUser();
    }

    const isAdmin = auth.user?.role === 'admin';

    // Normal login and registration are only for guests.
    if (to.meta.guestOnly && auth.user) {
        if (to.meta.adminLogin) {
            return isAdmin
                ? { name: 'admin.dashboard' }
                : { name: 'home' };
        }

        return isAdmin
            ? { name: 'admin.dashboard' }
            : { name: 'home' };
    }

    // Any authenticated user must be an admin to enter admin routes.
    if (to.matched.some(record => record.meta.requiresAdmin)) {
        if (!auth.user) {
            return {
                name: 'admin.login',
                query: { redirect: to.fullPath },
            };
        }

        if (!isAdmin) {
            return { name: 'home' };
        }
    }

    // User protected routes.
    if (
        to.matched.some(record => record.meta.requiresAuth) &&
        !auth.user
    ) {
        return { name: 'login' };
    }

    return true;
});

export default router;
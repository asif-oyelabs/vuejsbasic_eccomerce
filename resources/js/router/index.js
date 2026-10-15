import { createRouter, createWebHistory } from 'vue-router';

import websiteRoutes from './website.routes';
import adminRoutes from './admin.routes';

import { auth, loadUser } from '../auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...websiteRoutes,
        ...adminRoutes,
    ],
});

router.beforeEach(async (to) => {
    // Load authentication state before rendering the destination.
    if (!auth.loaded) {
        await loadUser();
    }

    const isAuthenticated = !!auth.user;
    const isAdmin = auth.user?.role === 'admin';

    // Redirect authenticated users away from login/register pages.
    if (to.meta.guestOnly && isAuthenticated) {
        if (to.meta.adminLogin) {
            return isAdmin
                ? { name: 'admin.dashboard' }
                : { name: 'home' };
        }

        return isAdmin
            ? { name: 'admin.dashboard' }
            : { name: 'home' };
    }

    // Admin routes require an authenticated admin.
    if (to.matched.some(record => record.meta.requiresAdmin)) {
        if (!isAuthenticated) {
            return {
                name: 'admin.login',
                query: { redirect: to.fullPath },
            };
        }

        if (!isAdmin) {
            return { name: 'home' };
        }
    }

    // Website routes that require login.
    if (
        to.matched.some(record => record.meta.requiresAuth) &&
        !isAuthenticated
    ) {
        return { name: 'login' };
    }

    return true;
});

export default router;

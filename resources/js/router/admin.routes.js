import AdminLayout from '../Admin/layouts/AdminLayout.vue';
import AdminLogin from '../Admin/auth/AdminLogin.vue';
import Dashboard from '../Admin/views/Dashboard.vue';
import Categories from '../Admin/views/Master/Categories.vue';
import Products from '../Admin/views/Master/Products.vue';

export default [
    {
        path: '/admin/login',
        name: 'admin.login',
        component: AdminLogin,
        meta: {
            guestOnly: true,
            adminLogin: true,
        },
    },
    {
        path: '/admin',
        component: AdminLayout,
        redirect: { name: 'admin.dashboard' },
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        children: [
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: Dashboard,
                meta: {
                    requiresAuth: true,
                    requiresAdmin: true,
                },
            },
            {
                path: 'categories',
                name: 'admin.categories',
                component: Categories,
                meta: {
                    requiresAuth: true,
                    requiresAdmin: true,
                },
            },
            {
                path: 'products',
                name: 'admin.products',
                component: Products,
                meta: {
                    requiresAuth: true,
                    requiresAdmin: true,
                },
            },
        ],
    },
];

import WebsiteLayout from '../views/Weblayout/WebsiteLayout.vue';


import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Profile from '../views/Profile.vue';
import Users from '../views/Users.vue';
import Contactus from '../views/Contactus.vue';
import Enquiry from '../views/Enquiry.vue';
import productlist from '../views/productlist.vue';
import ProductDetail from '../views/ProductDetail.vue';

export default [
    {
        path: '/',
        component: WebsiteLayout,
        children: [
            {
                path: '',
                name: 'home',
                component: Home,
            },
            {
                path: 'login',
                name: 'login',
                component: Login,
                meta: { guestOnly: true },
            },
            {
                path: 'register',
                name: 'register',
                component: Register,
                meta: { guestOnly: true },
            },
            {
                path: 'profile',
                name: 'profile',
                component: Profile,
                meta: { requiresAuth: true },
            },
            {
                path: 'users',
                name: 'users',
                component: Users,
                meta: { requiresAuth: true },
            },
            {
                path: 'contactus',
                name: 'contactus',
                component: Contactus,
                meta: { guestOnly: true },
            },
            {
                path: 'enquiry',
                name: 'enquiry',
                component: Enquiry,
                meta: { requiresAuth: true },
            },
            {
                path: 'productlist',
                name: 'productlist',
                component: productlist
            },
            {
                path: 'product/:slug',
                name: 'productdetail',
                component: ProductDetail,
            },
        ],
    },
];

<script setup>
import { watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { auth } from '../../auth.js';
import AppFooter from './AppFooter.vue';
import { useCart } from '../composables/useCart.js';

const router = useRouter();
const { cartCount, refreshCart, clearCart } = useCart();
// Load cart count when the user logs in or the layout is mounted.
watch(
    () => auth.user,
    (user) => {
        if (user) {
            refreshCart();
        } else {
            clearCart();
        }
    },
    { immediate: true }
);


function goHome() {
    router.push('/');
}

async function logout() {
    try {
        await axios.post('/api/logout', {}, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        auth.user = null;
        await router.push('/login');
    } catch (error) {
        console.error('Logout failed:', error);
        alert(error.response?.data?.message || 'Unable to log out.');
    }
}
</script>

<template>
    <div class="website-layout">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid px-3 px-md-5">
                <a
                    href="/"
                    class="navbar-brand fw-bold text-success"
                    @click.prevent="goHome"
                >
                    Laravel Vue
                </a>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <router-link to="/" class="nav-link">
                            Home
                        </router-link>
                        <router-link to="/productlist" class="nav-link">
                            Products
                        </router-link>

                        <template v-if="auth.user">
                            <router-link to="/profile" class="nav-link">
                                My Profile
                            </router-link>
                           
                                <router-link
                                    :to="{ name: 'cart' }"
                                    class="nav-link cart-nav-link"
                                >
                                    <span class="cart-icon-wrap">
                                        <i class="bi bi-cart3"></i>

                                        <span
                                            v-if="cartCount > 0"
                                            class="cart-count-badge"
                                        >
                                            {{ cartCount > 99 ? '99+' : cartCount }}
                                        </span>
                                    </span>

                                    Cart
                                </router-link>

                            <router-link :to="{ name: 'myorders' }" class="nav-link">
                                <i class="bi bi-box-seam me-1"></i>
                                My Orders
                            </router-link>

                            <button
                                type="button"
                                class="btn btn-danger btn-sm ms-lg-2"
                                @click="logout"
                            >
                                Logout
                            </button>
                        </template>

                        <template v-else>
                            <router-link to="/contactus" class="nav-link">
                                Contact Us
                            </router-link>

                            <router-link to="/login" class="nav-link">
                                Login
                            </router-link>

                            <router-link
                                to="/register"
                                class="btn btn-success btn-sm ms-lg-2"
                            >
                                Register
                            </router-link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <main class="min-vh-100 bg-light">
            <router-view />
        </main>

        <AppFooter />
    </div>
</template>


<style scoped>
.cart-nav-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.cart-icon-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.cart-count-badge {
    position: absolute;
    top: -9px;
    right: -11px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50px;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    border: 2px solid #fff;
}
</style>

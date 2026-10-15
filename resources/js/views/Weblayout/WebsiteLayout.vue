<script setup>
import { useRouter } from 'vue-router';
import axios from 'axios';
import { auth } from '../../auth.js';
import AppFooter from './AppFooter.vue';

const router = useRouter();

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

                        <template v-if="auth.user">
                            <router-link to="/profile" class="nav-link">
                                My Profile
                            </router-link>

                            <router-link to="/users" class="nav-link">
                                Users
                            </router-link>

                            <router-link to="/enquiry" class="nav-link">
                                Enquiry
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

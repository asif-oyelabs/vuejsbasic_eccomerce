<script setup>
import { onMounted, watch, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { auth, loadUser } from './auth';
import AppFooter from './components/AppFooter.vue';


const router = useRouter();
const route = useRoute();
const isAdminRoute = computed(() => route.path.startsWith('/admin'));

// Load the currently authenticated user when the app starts.
onMounted(() => {
    loadUser();
});

// Refresh authentication state after navigation.
watch(() => route.fullPath, () => {
    loadUser();
});

function goHome() {
    router.push('/');
}

// Logout the current user.
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
    <div>
        <!-- Bootstrap Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm" v-if="!isAdminRoute">
            <div class="container-fluid px-3 px-md-5">

                <!-- Brand -->
                <a
                    href="/"
                    class="navbar-brand fw-bold text-success"
                    @click.prevent="goHome"
                >
                    Laravel Vue
                </a>

                <!-- Mobile menu toggle -->
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

                <!-- Navigation links -->
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                       <router-link
                            to="/"
                            class="nav-link"
                            exact-active-class="router-link-exact-active"
                        >
                            Home
                        </router-link>

                        <template v-if="auth.user">
                            <router-link
                                to="/profile"
                                class="nav-link"
                            >
                                My Profile
                            </router-link>

                            <router-link
                                to="/users"
                                class="nav-link"
                            >
                                Users
                            </router-link>

                             <router-link
                                to="/enquiry"
                                class="nav-link"
                            >
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
                            <router-link
                                to="/contactus"
                                class="nav-link"
                            >
                               Contact Us
                            </router-link>
                            <router-link
                                to="/login"
                                class="nav-link"
                            >
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

        <!-- Current route page -->
        <main class="min-vh-100 bg-light">
            <router-view />
        </main>

        
        <AppFooter v-if="!isAdminRoute" />

    </div>
</template>


<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';
import { auth, loadUser } from '../../auth.js';

const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

async function loginAdmin() {
    errorMessage.value = '';

    if (!email.value.trim() || !password.value) {
        errorMessage.value = 'Please enter your email and password.';
        return;
    }

    loading.value = true;

    try {
        // Get the CSRF cookie required by Sanctum SPA authentication.
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
        });

        // Authenticate with Laravel.
        await axios.post('/api/login', {
            email: email.value.trim(),
            password: password.value,
        }, {
            withCredentials: true,
            headers: {
                Accept: 'application/json',
            },
        });

        // Fetch the authenticated user's details.
        auth.loaded = false;
        await loadUser();

        // Only admin accounts may enter the admin panel.
        if (auth.user?.role !== 'admin') {
            try {
                await axios.post('/api/logout', {}, {
                    withCredentials: true,
                    headers: {
                        Accept: 'application/json',
                    },
                });
            } finally {
                auth.user = null;
                auth.loaded = true;
            }

            errorMessage.value =
                'Access denied. This account does not have admin privileges.';

            return;
        }

        // Allow redirects only to paths inside the admin panel.
        const requestedPath = route.query.redirect;

        const redirectPath =
            typeof requestedPath === 'string' &&
            requestedPath.startsWith('/admin/') &&
            !requestedPath.startsWith('//')
                ? requestedPath
                : '/admin/dashboard';

        await router.push(redirectPath);
    } catch (error) {
        console.error('Admin login error:', error);

        auth.user = null;
        auth.loaded = true;

        errorMessage.value =
            error.response?.data?.errors?.email?.[0] ||
            error.response?.data?.message ||
            'Unable to log in. Please check your credentials and try again.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="admin-login-page">
        <div class="container">
            <div class="row min-vh-100 align-items-center justify-content-center py-4">
                <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                    <div class="text-center mb-4">
                        <div class="admin-login-logo mx-auto mb-3">
                            <i class="bi bi-shop-window"></i>
                        </div>

                        <h3 class="fw-bold mb-2">
                            EasyCommerce
                        </h3>

                        <p class="text-app-muted mb-0">
                            Sign in to your administration panel
                        </p>
                    </div>

                    <div class="admin-login-card">
                        <div class="mb-4">
                            <h4 class="fw-bold mb-1">
                                Admin Login
                            </h4>

                            <p class="text-app-muted small mb-0">
                                Enter your administrator credentials.
                            </p>
                        </div>

                        <div
                            v-if="errorMessage"
                            class="alert alert-danger"
                            role="alert"
                        >
                            <i class="bi bi-exclamation-circle me-2"></i>
                            {{ errorMessage }}
                        </div>

                        <form @submit.prevent="loginAdmin">
                            <div class="mb-3">
                                <label
                                    for="adminEmail"
                                    class="form-label fw-semibold"
                                >
                                    Email Address
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-envelope text-app-muted"></i>
                                    </span>

                                    <input
                                        id="adminEmail"
                                        v-model="email"
                                        type="email"
                                        class="form-control"
                                        placeholder="admin@example.com"
                                        autocomplete="username"
                                        required
                                        :disabled="loading"
                                    />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label
                                    for="adminPassword"
                                    class="form-label fw-semibold"
                                >
                                    Password
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-lock text-app-muted"></i>
                                    </span>

                                    <input
                                        id="adminPassword"
                                        v-model="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="form-control"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        required
                                        :disabled="loading"
                                    />

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i
                                            :class="showPassword
                                                ? 'bi bi-eye-slash'
                                                : 'bi bi-eye'"
                                        ></i>
                                    </button>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100 py-2 fw-semibold"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                    aria-hidden="true"
                                ></span>

                                {{ loading ? 'Signing in...' : 'Sign In to Admin' }}

                                <i
                                    v-if="!loading"
                                    class="bi bi-arrow-right ms-2"
                                ></i>
                            </button>
                        </form>

                        <div class="admin-login-note mt-4">
                            <i class="bi bi-shield-lock me-2"></i>
                            Restricted access for administrators only.
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <RouterLink
                            to="/"
                            class="text-app-muted text-decoration-none small"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Back to website
                        </RouterLink>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-login-page {
    min-height: 100vh;
    background:
        radial-gradient(
            circle at top right,
            var(--app-primary-light),
            transparent 38%
        ),
        var(--app-bg);
}

.admin-login-logo {
    width: 60px;
    height: 60px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    background: var(--app-primary);
    color: var(--app-white);
    font-size: 28px;
    box-shadow: 0 8px 22px rgba(66, 184, 131, 0.2);
}

.admin-login-card {
    padding: 30px;
    background: var(--app-white);
    border: 1px solid var(--app-border);
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(31, 41, 55, 0.06);
}

.admin-login-card .form-control,
.admin-login-card .input-group-text {
    min-height: 46px;
    border-color: var(--app-border);
}

.admin-login-card .form-control:focus {
    border-color: var(--app-primary);
    box-shadow: 0 0 0 0.2rem var(--app-primary-light);
}

.admin-login-note {
    padding: 12px;
    background: var(--app-primary-light);
    border-radius: 8px;
    color: var(--app-primary-hover);
    font-size: 12px;
    text-align: center;
}

@media (max-width: 575.98px) {
    .admin-login-card {
        padding: 22px;
    }
}
</style>

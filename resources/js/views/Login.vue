<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');
const fieldErrors = ref({});

async function login() {
    loading.value = true;
    error.value = '';
    fieldErrors.value = {};

    try {
        // Get the CSRF cookie from Laravel Sanctum.
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
        });

        // Submit login credentials to Laravel.
        const response = await axios.post(
            '/api/login',
            {
                email: email.value,
                password: password.value,
            },
            {
                withCredentials: true,
                withXSRFToken: true,
                headers: {
                    Accept: 'application/json',
                },
            }
        );

        console.log('Logged in user:', response.data.user);

        // Redirect after successful login.
        await router.push('/');
    } catch (e) {
        if (e.response?.status === 422) {
            fieldErrors.value = e.response.data.errors || {};
            error.value = 'Please check your login details.';
        } else {
            error.value =
                e.response?.data?.message ||
                'Login failed. Please try again.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light px-3 py-5">

        <div class="card shadow-sm border-app rounded-4 w-100" style="max-width: 420px;">
            <div class="card-body p-4 p-md-5">

                <!-- Heading -->
                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold mb-2">
                        Welcome Back
                    </h1>

                    <p class="text-app-muted mb-0">
                        Login to your account
                    </p>
                </div>

                <!-- General Error -->
                <div
                    v-if="error"
                    class="alert alert-danger"
                    role="alert"
                >
                    {{ error }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="login" novalidate>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            Email Address
                        </label>

                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': fieldErrors.email }"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        />

                        <div
                            v-if="fieldErrors.email"
                            class="invalid-feedback"
                        >
                            {{ fieldErrors.email[0] }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Password
                        </label>

                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': fieldErrors.password }"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        />

                        <div
                            v-if="fieldErrors.password"
                            class="invalid-feedback"
                        >
                            {{ fieldErrors.password[0] }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn-success w-100 py-2 mt-2"
                        :disabled="loading"
                    >
                        <span
                            v-if="loading"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>

                        {{ loading ? 'Logging in...' : 'Login' }}
                    </button>

                </form>

                <!-- Register Link -->
                <p class="text-center text-app-muted mt-4 mb-0">
                    Don't have an account?

                    <router-link
                        to="/register"
                        class="text-app-primary fw-semibold text-decoration-none"
                    >
                        Register
                    </router-link>
                </p>

            </div>
        </div>

    </section>
</template>

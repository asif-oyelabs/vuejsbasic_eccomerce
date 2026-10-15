<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const error = ref('');
const success = ref('');
const errors = ref({});

let redirectTimer;

async function register() {
    error.value = '';
    success.value = '';
    errors.value = {};
    loading.value = true;

    try {
        const response = await axios.post('/api/register', form.value, {
            headers: {
                Accept: 'application/json',
            },
        });

        success.value = response.data.message || 'Registration successful.';

        form.value = {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
        };

        redirectTimer = setTimeout(() => {
            router.push('/login');
        }, 1500);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
            error.value = 'Please correct the errors below.';
        } else {
            error.value =
                e.response?.data?.message ||
                'Registration failed. Please try again.';
        }
    } finally {
        loading.value = false;
    }
}

onBeforeUnmount(() => {
    if (redirectTimer) {
        clearTimeout(redirectTimer);
    }
});
</script>

<template>
    <section class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light px-3 py-5">

        <div class="card border-app shadow-sm rounded-4 w-100"
             style="max-width: 420px;">

            <div class="card-body p-4 p-md-5">

                <!-- Heading -->
                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold mb-2">
                        Create Account
                    </h1>

                    <p class="text-app-muted mb-0">
                        Register as a new customer
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="success"
                    class="alert alert-success"
                    role="status"
                >
                    {{ success }} Redirecting to login...
                </div>

                <!-- General Error -->
                <div
                    v-if="error"
                    class="alert alert-danger"
                    role="alert"
                >
                    {{ error }}
                </div>

                <form @submit.prevent="register" novalidate>

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Full Name
                        </label>

                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            placeholder="Enter your name"
                            autocomplete="name"
                            maxlength="255"
                            required
                        />

                        <div v-if="errors.name" class="invalid-feedback">
                            {{ errors.name[0] }}
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            Email Address
                        </label>

                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': errors.email }"
                            placeholder="Enter your email"
                            autocomplete="email"
                            maxlength="255"
                            required
                        />

                        <div v-if="errors.email" class="invalid-feedback">
                            {{ errors.email[0] }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Password
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': errors.password }"
                            placeholder="Minimum 8 characters"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        />

                        <div v-if="errors.password" class="invalid-feedback">
                            {{ errors.password[0] }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label
                            for="password_confirmation"
                            class="form-label fw-semibold"
                        >
                            Confirm Password
                        </label>

                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="form-control"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        />
                    </div>

                    <!-- Register Button -->
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

                        {{ loading ? 'Registering...' : 'Register' }}
                    </button>

                </form>

                <!-- Login Link -->
                <p class="text-center text-app-muted mt-4 mb-0">
                    Already have an account?

                    <router-link
                        to="/login"
                        class="text-app-primary fw-semibold text-decoration-none"
                    >
                        Login
                    </router-link>
                </p>

            </div>
        </div>

    </section>
</template>

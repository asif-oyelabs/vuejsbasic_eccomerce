<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = ref({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
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
        const response = await axios.post('/api/contact', form.value, {
            headers: {
                Accept: 'application/json',
            },
        });

        success.value = response.data.message || 'Message sent successfully.';

        form.value = {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: '',
        };

        redirectTimer = setTimeout(() => {
            router.push('/contactus');
        }, 1500);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
            error.value = 'Please correct the errors below.';
        } else {
            error.value =
                e.response?.data?.message ||
                'Message sending failed. Please try again.';
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
             style="max-width: 600px;">

            <div class="card-body p-4 p-md-5">

                <!-- Heading -->
                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold mb-2">
                       Contact Us
                    </h1>

                    <p class="text-app-muted mb-0">
                        Get in touch with us
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="success"
                    class="alert alert-success"
                    role="status"
                >
                    {{ success }}
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
                        <label for="phone" class="form-label fw-semibold">
                            Phone
                        </label>

                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="form-control"
                            :class="{ 'is-invalid': errors.phone }"
                            placeholder="Enter your phone number"
                            autocomplete="tel"
                            pattern="[0-9]{10}"
                            minlength="10"
                            required
                        />

                        <div v-if="errors.phone" class="invalid-feedback">
                            {{ errors.phone[0] }}
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="mb-3">
                        <label
                            for="subject"
                            class="form-label fw-semibold"
                        >
                            Subject
                        </label>

                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            class="form-control"
                            placeholder="Enter subject"
                            autocomplete="subject"
                            required
                        />
                    </div>

                      <div class="mb-3">
                        <label
                            for="message"
                            class="form-label fw-semibold"
                        >
                            Message
                        </label>

                        <textarea
                            id="message"
                            v-model="form.message"
                            class="form-control"
                            placeholder="Enter message"
                            autocomplete="message"
                            required
                            rows="4"
                        ></textarea>
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

                        {{ loading ? 'Sending...' : 'Submit' }}
                    </button>

                </form>

            </div>
        </div>

    </section>
</template>

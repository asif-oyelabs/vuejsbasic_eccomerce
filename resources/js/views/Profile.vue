<script setup>
import { reactive, ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { auth, loadUser } from '../auth';

const form = reactive({
    name: '',
    email: '',
    profile_image: null,
});

const preview = ref(null);
const saving = ref(false);
const message = ref('');
const errorMessage = ref('');

let previewUrl = null;

onMounted(async () => {
    if (!auth.loaded || !auth.user) {
        await loadUser();
    }

    if (auth.user) {
        form.name = auth.user.name;
        form.email = auth.user.email;
        preview.value = auth.user.profile_image;
    }
});

function selectImage(event) {
    const file = event.target.files[0];

    if (!file) return;

    // Client-side image validation.
    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    if (!allowedTypes.includes(file.type)) {
        errorMessage.value = 'Please select a JPG, PNG, or WebP image.';
        event.target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Image size must not exceed 2 MB.';
        event.target.value = '';
        return;
    }

    // Release the previous temporary preview URL.
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
    }

    form.profile_image = file;
    previewUrl = URL.createObjectURL(file);
    preview.value = previewUrl;

    errorMessage.value = '';
    message.value = '';
}

async function updateProfile() {
    saving.value = true;
    message.value = '';
    errorMessage.value = '';

    const data = new FormData();
    data.append('name', form.name);
    data.append('email', form.email);

    if (form.profile_image) {
        data.append('profile_image', form.profile_image);
    }

    // Laravel method spoofing for multipart form data.
    data.append('_method', 'PUT');

    try {
        const response = await axios.post('/api/profile', data, {
            withCredentials: true,
            headers: {
                Accept: 'application/json',
            },
        });

        auth.user = response.data.user;

        form.profile_image = null;
        preview.value = response.data.user.profile_image;
        message.value = response.data.message || 'Profile updated successfully.';

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    } catch (error) {
        const errors = error.response?.data?.errors;

        errorMessage.value = errors
            ? Object.values(errors).flat().join(' ')
            : error.response?.data?.message || 'Profile update failed.';
    } finally {
        saving.value = false;
    }
}

onBeforeUnmount(() => {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
    }
});
</script>

<template>
    <section class="container py-4 py-md-5">

        <div class="card border-app shadow-sm rounded-4 mx-auto w-100"
             style="max-width: 520px;">

            <!-- Card Header -->
            <div class="card-body p-4 p-md-5">

                <div class="mb-4">
                    <h2 class="h3 fw-bold mb-2">
                        My Profile
                    </h2>

                    <p class="text-app-muted mb-0">
                        Manage your personal information.
                    </p>
                </div>

                <!-- Profile Image Preview -->
                <div class="text-center mb-4">

                    <img
                        v-if="preview"
                        :src="preview"
                        alt="Profile image"
                        class="rounded-circle border object-fit-cover"
                        style="width: 110px; height: 110px;"
                    />

                    <div
                        v-else
                        class="rounded-circle bg-app-primary text-white d-inline-flex justify-content-center align-items-center fw-bold"
                        style="width: 110px; height: 110px; font-size: 38px;"
                    >
                        {{ form.name.charAt(0).toUpperCase() || 'U' }}
                    </div>

                </div>

                <!-- Profile Form -->
                <form @submit.prevent="updateProfile">

                    <!-- Profile Image -->
                    <div class="mb-3">
                        <label for="profile_image" class="form-label fw-semibold">
                            Profile Image
                        </label>

                        <input
                            id="profile_image"
                            type="file"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp"
                            @change="selectImage"
                        />

                        <div class="form-text">
                            JPG, PNG or WebP. Maximum size: 2 MB.
                        </div>
                    </div>

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
                            maxlength="255"
                            required
                        />
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
                            maxlength="255"
                            required
                        />
                    </div>

                    <!-- Success Message -->
                    <div
                        v-if="message"
                        class="alert alert-success"
                        role="status"
                    >
                        {{ message }}
                    </div>

                    <!-- Error Message -->
                    <div
                        v-if="errorMessage"
                        class="alert alert-danger"
                        role="alert"
                    >
                        {{ errorMessage }}
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn-success w-100 py-2 mt-2"
                        :disabled="saving"
                    >
                        <span
                            v-if="saving"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>

                        {{ saving ? 'Saving...' : 'Update Profile' }}
                    </button>

                </form>
            </div>
        </div>
    </section>
</template>

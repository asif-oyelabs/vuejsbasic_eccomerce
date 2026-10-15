<script setup>
import { ref, watch } from 'vue';

// Receive data from Users.vue.
const props = defineProps({
    show: Boolean,
    editingId: [Number, String, null],
    form: { type: Object, required: true },
    imagePreview: String,
    errorMessage: String,
    saving: Boolean,
});

// Send events to the parent component.
const emit = defineEmits([
    'close',
    'submit',
    'select-image',
]);

// Reference to the file input.
const fileInput = ref(null);

// Clear the selected file when the modal closes.
watch(() => props.show, (visible) => {
    if (!visible && fileInput.value) {
        fileInput.value.value = '';
    }
});
</script>

<template>
    <!-- Modal Overlay -->
    <div
        v-if="show"
        class="modal d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        @click.self="emit('close')"
    >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        {{ editingId ? 'Edit User' : 'Add User' }}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        @click="emit('close')"
                    ></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">

                    <form @submit.prevent="emit('submit')">

                        <!-- Profile Image Preview -->
                        <div class="text-center mb-4">

                            <img
                                v-if="imagePreview"
                                :src="imagePreview"
                                class="rounded-circle object-fit-cover border"
                                alt="User profile"
                                style="width: 90px; height: 90px;"
                            />

                            <div
                                v-else
                                class="rounded-circle bg-app-primary text-white d-inline-flex justify-content-center align-items-center fw-bold"
                                style="width: 90px; height: 90px; font-size: 32px;"
                            >
                                {{ form.name.charAt(0).toUpperCase() || 'U' }}
                            </div>

                        </div>

                        <!-- Profile Image Upload -->
                        <div class="mb-3">
                            <label
                                for="profile_image"
                                class="form-label fw-semibold"
                            >
                                Profile Image
                            </label>

                            <input
                                ref="fileInput"
                                id="profile_image"
                                type="file"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                                @change="emit('select-image', $event)"
                            />

                            <div class="form-text">
                                JPG, PNG or WebP. Maximum 2 MB.
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="user_name" class="form-label fw-semibold">
                                Name
                            </label>

                            <input
                                id="user_name"
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                maxlength="255"
                                required
                            />
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="user_email" class="form-label fw-semibold">
                                Email
                            </label>

                            <input
                                id="user_email"
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                required
                            />
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="user_password" class="form-label fw-semibold">
                                Password

                                <span
                                    v-if="editingId"
                                    class="text-app-muted fw-normal small"
                                >
                                    (leave blank to keep unchanged)
                                </span>
                            </label>

                            <input
                                id="user_password"
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                :required="!editingId"
                                minlength="8"
                                :placeholder="editingId
                                    ? 'Optional new password'
                                    : 'Minimum 8 characters'"
                            />
                        </div>

                        <!-- API Error -->
                        <div
                            v-if="errorMessage"
                            class="alert alert-danger"
                            role="alert"
                        >
                            {{ errorMessage }}
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">

                            <button
                                type="button"
                                class="btn btn-outline-secondary order-2 order-sm-1"
                                :disabled="saving"
                                @click="emit('close')"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-success order-1 order-sm-2"
                                :disabled="saving"
                            >
                                <span
                                    v-if="saving"
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"
                                    aria-hidden="true"
                                ></span>

                                {{
                                    saving
                                        ? 'Saving...'
                                        : editingId
                                            ? 'Update User'
                                            : 'Save User'
                                }}
                            </button>

                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Dark background behind the modal -->
    <div
        v-if="show"
        class="modal-backdrop show"
    ></div>
</template>
